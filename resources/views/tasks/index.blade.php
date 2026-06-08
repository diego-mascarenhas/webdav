@extends('layouts.dav')

@section('title', 'Tareas')
@section('nav_active', 'tasks')

@section('content')
    <section class="page">
        <div class="page__inner">
            <header class="page-header">
                <div class="page-header__badge">
                    <span class="page-header__badge-dot"></span>
                    VTODO
                </div>
                <h1>Tareas sincronizadas</h1>
                <p class="page-header__meta">{{ $user->email }} · {{ $tasks->count() }} tarea(s)</p>
            </header>

            <article class="card">
                @if ($tasks->isEmpty())
                    <p>No hay tareas en el servidor todavía. Créalas en Recordatorios del iPhone (misma cuenta CalDAV) o sincronízalas desde Humano.</p>
                @else
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Tarea</th>
                                <th>Vence</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tasks as $task)
                                <tr>
                                    <td>
                                        {{ $task['summary'] }}
                                        @if (! empty($task['description']))
                                            <div class="description">{{ $task['description'] }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $task['due_at'] ? \Carbon\Carbon::parse($task['due_at'])->locale('es')->isoFormat('D MMM YYYY HH:mm') : '—' }}</td>
                                    <td>
                                        @if ($task['completed'])
                                            <span class="badge badge--done">Completada</span>
                                        @else
                                            <span class="badge badge--pending">Pendiente</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </article>

            <p class="api-hint">
                API: <code>GET {{ url('/api/tasks') }}?email={{ urlencode($user->email) }}</code>
                con <code>Authorization: Bearer …</code>.
            </p>
        </div>
    </section>
@endsection
