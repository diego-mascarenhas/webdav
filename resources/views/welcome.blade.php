<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} — CardDAV / CalDAV</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 42rem; margin: 3rem auto; padding: 0 1rem; line-height: 1.6; color: #1a1a1a; }
        code { background: #f4f4f5; padding: 0.15rem 0.4rem; border-radius: 4px; font-size: 0.9em; }
        h1 { font-size: 1.5rem; }
        section { margin-top: 2rem; }
        ul { padding-left: 1.25rem; }
    </style>
</head>
<body>
    <h1>{{ config('app.name') }}</h1>
    <p>Servidor CardDAV (contactos) y CalDAV (calendario) para sincronizar iPhone y Android.</p>

    <section>
        <h2>URL del servidor</h2>
        <p><code>{{ $davUrl }}</code></p>
        <p>Usa siempre la barra final. En producción, HTTPS es obligatorio para iOS.</p>
    </section>

    <section>
        <h2>Primer uso</h2>
        <pre><code>php artisan migrate
php artisan dav:setup
php artisan dav:password tu@email.com</code></pre>
    </section>

    <section>
        <h2>iPhone / iPad</h2>
        <ul>
            <li><strong>Contactos:</strong> Ajustes → Contactos → Cuentas → Añadir cuenta → Otro → Añadir contactos CardDAV</li>
            <li><strong>Calendario:</strong> Ajustes → Calendario → Cuentas → Añadir cuenta → Otro → Añadir calendario CalDAV</li>
            <li>Servidor: <code>{{ parse_url($davUrl, PHP_URL_HOST) }}</code></li>
            <li>Ruta / descripción avanzada: <code>/dav/</code> si el cliente lo pide</li>
        </ul>
    </section>

    <section>
        <h2>Android</h2>
        <p>Instala una app compatible (p. ej. DAVx⁵) y apunta a la misma URL con usuario y contraseña de <code>dav:setup</code>.</p>
    </section>

    <section>
        <p><a href="{{ route('contacts.index') }}">Ver contactos y calendario en la web</a> (mismo usuario que CardDAV/CalDAV)</p>
        <p style="color: #6b7280; font-size: 0.875rem; margin-top: 0.5rem;">Si no has iniciado sesión, te pedirá usuario y contraseña.</p>
        <p class="meta" style="margin-top: 0.5rem;"><a href="{{ $davUrl }}">Explorar DAV</a> — solo muestra XML técnico; no es el listado de contactos.</p>
    </section>
</body>
</html>
