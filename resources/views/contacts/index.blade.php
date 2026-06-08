@extends('layouts.dav')

@section('title', 'Contactos')
@section('nav_active', 'contacts')

@section('content')
    <section class="page">
        <div class="page__inner">
            <header class="page-header">
                <div class="page-header__badge">
                    <span class="page-header__badge-dot"></span>
                    CardDAV
                </div>
                <h1>Contactos sincronizados</h1>
                <p class="page-header__meta">{{ $user->email }} · {{ $contacts->count() }} contacto(s)</p>
            </header>

            <article class="card">
                @if ($contacts->isEmpty())
                    <p>No hay contactos en el servidor todavía. Créalos en el iPhone en esta cuenta CardDAV y espera unos segundos.</p>
                @else
                    <table class="data-table">
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
            </article>

            <p class="api-hint">
                API: <code>GET {{ url('/api/contacts') }}?email={{ urlencode($user->email) }}</code>
                con <code>Authorization: Bearer …</code> (<code>DAV_API_TOKEN</code>).
            </p>
        </div>
    </section>
@endsection
