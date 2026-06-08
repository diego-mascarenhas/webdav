#!/usr/bin/env bash
# Diagnose CardDAV vs CalDAV.
#
# Usage (recommended — one line, no export):
#   ./scripts/dav-diagnose.sh idoneo 'tu-contraseña'
#   ./scripts/dav-diagnose.sh idoneo 'tu-contraseña' principals/idoneo
#
# Production (Forge):
#   BASE_URL=https://webdav.idoneo.dev ./scripts/dav-diagnose.sh diego@email.com 'pass' principals/idoneo
#
# Or with env vars (each export on its own line):
#   export DAV_USER=idoneo
#   export DAV_PASS='tu-contraseña'
#   ./scripts/dav-diagnose.sh

set -euo pipefail

default_base_url() {
  if [[ -n "${BASE_URL:-}" ]]; then
    echo "${BASE_URL%/}"
    return
  fi

  if [[ -f .env ]]; then
    local app_url
    app_url="$(grep -E '^APP_URL=' .env | head -1 | cut -d= -f2- | tr -d '"' | tr -d "'")"
    if [[ -n "$app_url" ]]; then
      echo "${app_url%/}"
      return
    fi
  fi

  echo "https://webdav.idoneo.dev"
}

BASE_URL="$(default_base_url)"

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
  local code
  code="$(curl -sk "${AUTH[@]}" -o "$BODY_FILE" -w '%{http_code}' "$@" || echo "000")"
  echo "$code"
}

curl_headers() {
  curl -sk "${AUTH[@]}" "$@" 2>&1 || echo "(curl failed — check BASE_URL, DNS and HTTPS)"
}

section "0. Config"
echo "BASE_URL=$BASE_URL"
echo "USER=$USER"
echo "PRINCIPAL=$PRINCIPAL"
echo "PRINCIPAL_NAME=$PRINCIPAL_NAME (used in /calendars/ and /addressbooks/ URLs)"

section "1. OPTIONS (must include calendar-access + addressbook)"
curl_headers -X OPTIONS "${BASE_URL}/dav/" -D - -o /dev/null | grep -iE '^(HTTP|dav:|allow:)' || echo "(no response — wrong domain or server down)"

section "2. Well-known CalDAV redirect (Location must end with /dav/)"
curl -sk -I "${BASE_URL}/.well-known/caldav" 2>&1 | grep -iE '^(HTTP|location:)' || echo "(no response — wrong domain or server down)"

section "3. Principal discovery (root /dav/)"
PROPFIND_PRINCIPAL='<?xml version="1.0" encoding="utf-8"?>
<d:propfind xmlns:d="DAV:" xmlns:cal="urn:ietf:params:xml:ns:caldav" xmlns:card="urn:ietf:params:xml:ns:carddav">
  <d:prop>
    <d:current-user-principal/>
    <cal:calendar-home-set/>
    <card:addressbook-home-set/>
    <cal:schedule-inbox-URL/>
    <cal:schedule-outbox-URL/>
  </d:prop>
</d:propfind>'

code=$(http_code -X PROPFIND "${BASE_URL}/dav/" -H 'Depth: 0' "${XML_CT[@]}" --data "$PROPFIND_PRINCIPAL")
echo "HTTP $code"
if [[ "$code" == "000" ]]; then
  echo "ERROR: could not reach ${BASE_URL}/dav/"
else
  grep -E 'calendar-home-set|addressbook-home-set|schedule-inbox|schedule-outbox|current-user-principal|href|exception|error' "$BODY_FILE" | head -25 || head -30 "$BODY_FILE"
fi

section "3b. Principal detail (/dav/principals/${PRINCIPAL_NAME}/)"
code=$(http_code -X PROPFIND "${BASE_URL}/dav/principals/${PRINCIPAL_NAME}/" -H 'Depth: 0' "${XML_CT[@]}" --data "$PROPFIND_PRINCIPAL")
echo "HTTP $code"
if [[ "$code" == "000" ]]; then
  echo "ERROR: could not reach principal URL"
else
  grep -E 'calendar-home-set|schedule-inbox|schedule-outbox|calendar-proxy|href|NotAuthenticated|exception' "$BODY_FILE" | head -25 || head -30 "$BODY_FILE"
fi

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
if [[ "$code" != "000" ]]; then
  grep -E 'calendar|VTODO|VEVENT|displayname|NotFound|error|exception' "$BODY_FILE" | head -25 || head -40 "$BODY_FILE"
fi

section "5. Address book home (Depth 1)"
AB_HOME="${BASE_URL}/dav/addressbooks/${PRINCIPAL_NAME}/"
echo "Trying addressbook home: ${AB_HOME}"
code=$(http_code -X PROPFIND "${AB_HOME}" -H 'Depth: 1' "${XML_CT[@]}" --data "$PROPFIND_CAL")
echo "HTTP $code"
if [[ "$code" != "000" ]]; then
  grep -E 'addressbook|displayname|NotFound|error|exception' "$BODY_FILE" | head -25 || head -40 "$BODY_FILE"
fi

section "6. Interpretación rápida"
echo "- BASE_URL debe ser el sitio real (p. ej. https://webdav.idoneo.dev, NO carddav.idoneo.dev)"
echo "- OPTIONS con calendar-access + addressbook → servidor OK"
echo "- Principal PROPFIND → 207; en /dav/principals/USUARIO/ deben salir calendar-home-set + schedule-inbox/outbox (iOS)"
echo "- Calendar home → 207 y calendario default con VEVENT y VTODO"
echo "- Si calendar home → 404/403 → principal incorrecto o falta dav:setup en producción"
echo "- Si schedule-inbox/outbox faltan → desplegar CalDAVSchedulePlugin en DavServiceProvider"
