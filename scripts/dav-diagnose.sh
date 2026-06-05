#!/usr/bin/env bash
# Diagnose CardDAV vs CalDAV.
#
# Usage (recommended — one line, no export):
#   ./scripts/dav-diagnose.sh idoneo 'tu-contraseña'
#   ./scripts/dav-diagnose.sh idoneo 'tu-contraseña' principals/idoneo
#
# Or with env vars (each export on its own line):
#   export DAV_USER=idoneo
#   export DAV_PASS='tu-contraseña'
#   ./scripts/dav-diagnose.sh

set -euo pipefail

BASE_URL="${BASE_URL:-https://carddav.idoneo.dev}"

if [[ $# -ge 1 ]]; then
  DAV_USER="$1"
fi
if [[ $# -ge 2 ]]; then
  DAV_PASS="$2"
fi
if [[ $# -ge 3 ]]; then
  DAV_PRINCIPAL="$3"
fi

USER="${DAV_USER:-}"
PASS="${DAV_PASS:-}"

if [[ -z "$USER" || -z "$PASS" ]]; then
  echo "Usage: $0 <usuario-o-email> <contraseña> [principals/usuario]" >&2
  echo "Example: $0 idoneo 'Simplicity!' principals/idoneo" >&2
  exit 1
fi

PRINCIPAL="${DAV_PRINCIPAL:-principals/${USER#principals/}}"
# If login is email, default principal uses local part (same as dav:setup default)
if [[ "$USER" == *@* && "$PRINCIPAL" == "principals/${USER}" ]]; then
  PRINCIPAL="principals/${USER%%@*}"
fi
# Sabre paths use the principal *name* only: /calendars/idoneo/ not /calendars/principals/idoneo/
PRINCIPAL_NAME="${PRINCIPAL##*/}"

AUTH=(-u "${USER}:${PASS}")
XML_CT=(-H 'Content-Type: application/xml; charset=utf-8')
BODY_FILE="$(mktemp /tmp/dav-body.XXXXXX.xml)"
trap 'rm -f "$BODY_FILE"' EXIT

section() { echo; echo "========== $* =========="; }

http_code() {
  curl -sk "${AUTH[@]}" -o "$BODY_FILE" -w '%{http_code}' "$@"
}

section "0. Config"
echo "BASE_URL=$BASE_URL"
echo "USER=$USER"
echo "PRINCIPAL=$PRINCIPAL"
echo "PRINCIPAL_NAME=$PRINCIPAL_NAME (used in /calendars/ and /addressbooks/ URLs)"

section "1. OPTIONS (must include calendar-access + addressbook)"
curl -sk "${AUTH[@]}" -X OPTIONS "${BASE_URL}/dav/" -D - -o /dev/null | grep -iE '^(HTTP|dav:|allow:)' || true

section "2. Well-known CalDAV redirect (Location must end with /dav/)"
curl -sk -I "${BASE_URL}/.well-known/caldav" | grep -iE '^(HTTP|location:)' || true

section "3. Principal discovery"
PROPFIND_PRINCIPAL='<?xml version="1.0" encoding="utf-8"?>
<d:propfind xmlns:d="DAV:" xmlns:cal="urn:ietf:params:xml:ns:caldav" xmlns:card="urn:ietf:params:xml:ns:carddav">
  <d:prop>
    <d:current-user-principal/>
    <cal:calendar-home-set/>
    <card:addressbook-home-set/>
  </d:prop>
</d:propfind>'

code=$(http_code -X PROPFIND "${BASE_URL}/dav/" -H 'Depth: 0' "${XML_CT[@]}" --data "$PROPFIND_PRINCIPAL")
echo "HTTP $code"
grep -E 'calendar-home-set|addressbook-home-set|current-user-principal|href' "$BODY_FILE" | head -20 || head -30 "$BODY_FILE"

section "4. Calendar home (Depth 1)"
CAL_HOME="${BASE_URL}/dav/calendars/${PRINCIPAL_NAME}/"

PROPFIND_CAL='<?xml version="1.0" encoding="utf-8"?>
<d:propfind xmlns:d="DAV:" xmlns:cal="urn:ietf:params:xml:ns:caldav" xmlns:cs="http://calendarserver.org/ns/">
  <d:prop>
    <d:displayname/>
    <d:resourcetype/>
    <cal:supported-calendar-component-set/>
  </d:prop>
</d:propfind>'

echo "Trying calendar home: ${CAL_HOME}"
code=$(http_code -X PROPFIND "${CAL_HOME}" -H 'Depth: 1' "${XML_CT[@]}" --data "$PROPFIND_CAL")
echo "HTTP $code"
grep -E 'calendar|VTODO|VEVENT|displayname|NotFound|error|exception' "$BODY_FILE" | head -25 || head -40 "$BODY_FILE"

section "5. Address book home (Depth 1)"
AB_HOME="${BASE_URL}/dav/addressbooks/${PRINCIPAL_NAME}/"
echo "Trying addressbook home: ${AB_HOME}"
code=$(http_code -X PROPFIND "${AB_HOME}" -H 'Depth: 1' "${XML_CT[@]}" --data "$PROPFIND_CAL")
echo "HTTP $code"
grep -E 'addressbook|displayname|NotFound|error|exception' "$BODY_FILE" | head -25 || head -40 "$BODY_FILE"

section "6. Interpretación rápida"
echo "- OPTIONS con calendar-access + addressbook → servidor OK"
echo "- Principal PROPFIND → 207 y href con calendar-home-set"
echo "- Calendar home → 207 y calendario default con VEVENT (y VTODO si recordatorios)"
echo "- Si calendar home → 404/403 → falta dav:setup o principal incorrecto"
echo "- Si contactos OK pero calendario no → autodiscovery sin / final o sin calendarinstances en BD"
