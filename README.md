# CardDAV — servidor personal de contactos y calendario

Laravel + [SabreDAV](https://sabre.io/) expone un endpoint **CardDAV** (contactos) y **CalDAV** (calendario) para sincronizar con iPhone, iPad y Android (vía DAVx⁵ u otra app compatible).

## Requisitos

- PHP 8.4+ (Herd recomendado)
- Composer
- MySQL, PostgreSQL o SQLite

## Instalación

```bash
composer install
cp .env.example .env   # si aún no existe
php artisan key:generate
```

Configura la base en `.env` (MySQL o PostgreSQL):

```env
# MySQL
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=carddav
DB_USERNAME=root
DB_PASSWORD=tu_contraseña

# PostgreSQL
# DB_CONNECTION=pgsql
# DB_HOST=127.0.0.1
# DB_PORT=5432
# DB_DATABASE=carddav
# DB_USERNAME=postgres
# DB_PASSWORD=tu_contraseña
```

Luego:

```bash
php artisan migrate
php artisan dav:setup
```

Con Herd, el sitio suele estar en **https://carddav.test**. Ajusta `APP_URL` en `.env`:

```env
APP_URL=https://carddav.test
```

## Endpoint

| Recurso | URL |
|---------|-----|
| DAV (contactos + calendario) | `https://carddav.test/dav/` |
| Autodiscovery CardDAV | `https://carddav.test/.well-known/carddav` |
| Autodiscovery CalDAV | `https://carddav.test/.well-known/caldav` |

## Configurar clientes

Tras `dav:setup` obtendrás email, contraseña y URL. En iOS:

1. **Contactos:** Ajustes → Contactos → Cuentas → Añadir cuenta → CardDAV  
2. **Calendario:** Ajustes → Calendario → Cuentas → Añadir cuenta → CalDAV  

En **Android**, usa [DAVx⁵](https://www.davx5.com/) con la misma URL y credenciales.

## Comandos

```bash
# Crear usuario DAV (libreta + calendario por defecto)
php artisan dav:setup --email=tu@email.test --username=admin --password=secreto

# Cambiar contraseña
php artisan dav:password tu@email.test
php artisan dav:password tu@email.test --password=nueva-clave

# Explorar en local (navegador)
open https://carddav.test/dav/
```

## Stack

- [Laravel 13](https://laravel.com/)
- [monicahq/laravel-sabre](https://github.com/monicahq/laravel-sabre) — integración Sabre en Laravel
- [sabre/dav](https://github.com/sabre-io/dav) — implementación CardDAV/CalDAV

## Ver contactos y calendario en la web

Tras deploy, entra en `/login` con el mismo email/usuario y contraseña que CardDAV/CalDAV.

| Página | URL |
|--------|-----|
| Contactos | `/contacts` |
| Calendario | `/calendar` |

API (integraciones): `GET /api/contacts?email=...` y `GET /api/events?email=...` con `DAV_API_TOKEN`.

Para integrar **humano.app**, define `DAV_API_TOKEN` y usa `GET /api/contacts?email=...` (ver [docs/humano-sync.md](docs/humano-sync.md)).

## Notas

- `/dav/` en el navegador muestra XML de SabreDAV, no el listado de contactos.
- Autenticación **HTTP Basic** (compatible con iOS y DAVx⁵).
- Un usuario puede tener varias libretas/calendarios; `dav:setup` crea «Contacts» y «Calendar» por defecto.
- Para uso en red local o Internet, usa **HTTPS** (Let's Encrypt, Caddy, etc.).

## Licencia

MIT
