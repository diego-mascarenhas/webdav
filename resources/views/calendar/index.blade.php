@extends('layouts.dav')

@section('title', 'Calendario')
@section('nav_active', 'calendar')

@section('content')
    <section class="page">
        <div class="page__inner">
            <header class="page-header">
                <div class="page-header__badge">
                    <span class="page-header__badge-dot"></span>
                    CalDAV
                </div>
                <h1>Eventos sincronizados</h1>
                <p class="page-header__meta">{{ $user->email }} · {{ $events->count() }} evento(s)</p>
            </header>

            <article class="card">
                @if ($events->isEmpty())
                    <p>No hay eventos en el servidor todavía. Crea uno en el iPhone en el calendario CalDAV y espera unos segundos.</p>
                @else
                    <table class="data-table">
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
                                            <span class="badge badge--muted">Todo el día</span>
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
            </article>

            <p class="api-hint">
                API: <code>GET {{ url('/api/events') }}?email={{ urlencode($user->email) }}</code>
                con <code>Authorization: Bearer …</code>.
            </p>
        </div>
    </section>
@endsection
