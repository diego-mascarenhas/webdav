<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contactos — {{ config('app.name') }}</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 52rem; margin: 2rem auto; padding: 0 1rem; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; font-size: 0.9rem; }
        th, td { text-align: left; padding: 0.5rem; border-bottom: 1px solid #e5e7eb; }
        th { font-size: 0.75rem; text-transform: uppercase; color: #6b7280; }
        .meta { color: #6b7280; font-size: 0.875rem; }
        form.inline { display: inline; }
        button.link { background: none; border: none; color: #2563eb; cursor: pointer; text-decoration: underline; }
        nav { display: flex; gap: 1rem; margin-bottom: 1rem; font-size: 0.875rem; }
        nav a { color: #2563eb; text-decoration: none; }
        nav a.active { color: #111; font-weight: 600; }
    </style>
</head>
<body>
    <nav>
        <a href="{{ route('contacts.index') }}" class="active">Contactos</a>
        <a href="{{ route('calendar.index') }}">Calendario</a>
    </nav>

    <header style="display: flex; justify-content: space-between; align-items: baseline; gap: 1rem;">
        <div>
            <h1>Contactos sincronizados</h1>
            <p class="meta">{{ $user->email }} · {{ $contacts->count() }} contacto(s) en CardDAV</p>
        </div>
        <form class="inline" method="post" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="link">Cerrar sesión</button>
        </form>
    </header>

    @if ($contacts->isEmpty())
        <p>No hay contactos en el servidor todavía. Créalos en el iPhone en esta cuenta CardDAV y espera unos segundos.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($contacts as $contact)
                    <tr>
                        <td>{{ $contact['full_name'] ?: '—' }}</td>
                        <td>{{ $contact['email'] ?? '—' }}</td>
                        <td>{{ $contact['phone'] ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <p class="meta" style="margin-top: 2rem;">
        API para integraciones:
        <code>GET {{ url('/api/contacts') }}?email={{ urlencode($user->email) }}</code>
        con cabecera <code>Authorization: Bearer …</code> (<code>DAV_API_TOKEN</code>).
    </p>
</body>
</html>
