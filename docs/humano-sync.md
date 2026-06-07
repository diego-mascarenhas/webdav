# Sincronizar CardDAV con humano.app

## Arquitectura recomendada

```
iPhone (CardDAV)  →  carddav.idoneo.dev  →  tabla `cards` (vCard)
                                              ↓
                         GET /api/contacts   (job en Humano)
                                              ↓
                                    humano.app `contacts`
```

Humano **no expone** `POST /api/contacts` hoy; la sync conviene hacerla **desde Humano** (comando programado o integración nueva) leyendo la API de CardDAV.

## API CardDAV (lectura)

1. En CardDAV `.env`:

```env
DAV_API_TOKEN=genera-un-token-largo-aleatorio
```

2. Petición desde Humano (o `curl`):

```bash
curl -sS -H "Authorization: Bearer TU_TOKEN" \
  "https://carddav.idoneo.dev/api/contacts?email=usuario@ejemplo.com"
```

## API de usuarios (Humano → WebDAV)

| Método | Ruta | Uso |
|--------|------|-----|
| `POST` | `/api/users` | Crear usuario DAV |
| `POST` | `/api/users/link` | Validar credenciales existentes |
| `GET` | `/api/users?email=` | Consultar usuario |
| `PUT` | `/api/users/password` | Cambiar contraseña |

## API de sync (lectura/escritura)

| Recurso | GET | POST/PUT | DELETE |
|---------|-----|----------|--------|
| Contactos | `/api/contacts?email=` | `/api/contacts` | `/api/contacts/{uid}` |
| Eventos | `/api/events?email=` | `/api/events` | `/api/events/{uid}` |
| Tareas | `/api/tasks?email=` | `/api/tasks` | `/api/tasks/{uid}` |

Todas requieren `Authorization: Bearer {DAV_API_TOKEN}` y `?email=` del principal.

Respuesta JSON:

```json
{
  "data": [
    {
      "uid": "...",
      "full_name": "Ana García",
      "name": "Ana",
      "surname": "García",
      "email": "ana@example.com",
      "phone": "+34...",
      "updated_at": 1716990000
    }
  ],
  "meta": { "count": 1, "principal": "principals/admin" }
}
```

## Mapeo a `Contact` (Humano)

| CardDAV / API | Humano `contacts` |
|---------------|-------------------|
| `name` | `name` |
| `surname` | `surname` |
| `email` | `email` |
| `phone` | `phone` |
| `uid` | guardar en `data->carddav_uid` para evitar duplicados |

Campos obligatorios en Humano: `team_id`, `user_id` o `creator_id`, `status_id`, etc. — definirlos en el job según reglas de tu equipo.

## Web (usuario humano)

- https://carddav.idoneo.dev/login  
- Mismas credenciales que CardDAV en el iPhone  
- https://carddav.idoneo.dev/contacts  

## Próximo paso en Humano

Desde **Team Settings** (`/team/{team}/settings`):

1. Configura en Humano `.env`: `WEBDAV_BASE_URL` y `WEBDAV_API_TOKEN` (mismo token que `DAV_API_TOKEN`).
2. **Crear cuenta** o **Vincular existente** desde la tarjeta WebDAV.
3. Activa toggles de sync en el grupo `webdav`.
4. El scheduler ejecuta `webdav:sync-data` cada 15 minutos.
