<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Calendario — {{ config('app.name') }}</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 52rem; margin: 2rem auto; padding: 0 1rem; }
        nav { display: flex; gap: 1rem; margin-bottom: 1rem; font-size: 0.875rem; }
        nav a { color: #2563eb; text-decoration: none; }
        nav a.active { color: #111; font-weight: 600; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; font-size: 0.9rem; }
        th, td { text-align: left; padding: 0.5rem; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        th { font-size: 0.75rem; text-transform: uppercase; color: #6b7280; }
        .meta { color: #6b7280; font-size: 0.875rem; }
        form.inline { display: inline; }
        button.link { background: none; border: none; color: #2563eb; cursor: pointer; text-decoration: underline; }
        .badge { font-size: 0.75rem; color: #6b7280; }
    </style>
</head>
<body>
    <nav>
        <a href="{{ route('contacts.index') }}">Contactos</a>
        <a href="{{ route('calendar.index') }}" class="active">Calendario</a>
    </nav>

    <header style="display: flex; justify-content: space-between; align-items: baseline; gap: 1rem;">
        <div>
            <h1>Eventos sincronizados</h1>
            <p class="meta">{{ $user->email }} · {{ $events->count() }} evento(s) en CalDAV</p>
        </div>
        <form class="inline" method="post" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="link">Cerrar sesión</button>
        </form>
    </header>

    @if ($events->isEmpty())
        <p>No hay eventos en el servidor todavía. Crea uno en el iPhone en el calendario CalDAV y espera unos segundos.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Evento</th>
                    <th>Inicio</th>
                    <th>Fin</th>
                    <th>Lugar</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($events as $event)
                    <tr>
                        <td>
                            {{ $event['summary'] }}
                            @if ($event['all_day'])
                                <span class="badge">Todo el día</span>
                            @endif
                        </td>
                        <td>{{ $event['starts_at'] ? \Carbon\Carbon::parse($event['starts_at'])->locale('es')->isoFormat('D MMM YYYY HH:mm') : '—' }}</td>
                        <td>{{ $event['ends_at'] ? \Carbon\Carbon::parse($event['ends_at'])->locale('es')->isoFormat('D MMM YYYY HH:mm') : '—' }}</td>
                        <td>{{ $event['location'] ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <p class="meta" style="margin-top: 2rem;">
        API: <code>GET {{ url('/api/events') }}?email={{ urlencode($user->email) }}</code>
        con <code>Authorization: Bearer …</code>.
    </p>
</body>
</html>
