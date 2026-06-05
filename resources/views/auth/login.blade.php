<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión — {{ config('app.name') }}</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 24rem; margin: 4rem auto; padding: 0 1rem; }
        label { display: block; margin-top: 1rem; font-size: 0.875rem; }
        input { width: 100%; padding: 0.5rem; margin-top: 0.25rem; box-sizing: border-box; }
        button { margin-top: 1.5rem; padding: 0.6rem 1rem; width: 100%; cursor: pointer; }
        .error { color: #b91c1c; font-size: 0.875rem; margin-top: 0.5rem; }
        .hint { color: #6b7280; font-size: 0.875rem; }
    </style>
</head>
<body>
    <h1>Contactos CardDAV</h1>
    <p class="hint">Mismas credenciales que en el iPhone (email completo o usuario DAV, p. ej. <code>idoneo</code>).</p>

    <form method="post" action="{{ route('login') }}">
        @csrf
        <label>Email o usuario
            <input type="text" name="login" value="{{ old('login') }}" required autofocus autocomplete="username">
        </label>
        @error('login')<p class="error">{{ $message }}</p>@enderror

        <label>Contraseña
            <input type="password" name="password" required autocomplete="current-password">
        </label>

        <label>
            <input type="checkbox" name="remember"> Recordarme
        </label>

        <button type="submit">Entrar</button>
    </form>

    <p style="margin-top: 2rem; font-size: 0.875rem;"><a href="/">Volver</a></p>
    @include('partials.analytics')
</body>
</html>
