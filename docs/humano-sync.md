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

Crear p. ej. `php artisan carddav:import --email=...` que:

1. Llame a la API anterior con `DAV_API_TOKEN`.
2. Por cada ítem, `Contact::updateOrCreate` por `data->carddav_uid`.
3. Programe en el scheduler cada 15–60 min.

¿Quieres que implementemos ese comando dentro del repo **humano**?
