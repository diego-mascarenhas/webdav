<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg: #0a0a0a;
            --text: #ededed;
            --text-muted: rgba(237, 237, 237, 0.65);
            --glass-border: rgba(255, 255, 255, 0.1);
            --accent-magenta: #e040fb;
            --accent-red: #ff1744;
            --header-height: 73px;
        }

        body {
            font-family: 'Roboto', Arial, Helvetica, sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        .site-shell { position: relative; min-height: 100dvh; }

        .site-shell__background {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
        }

        .site-shell__background canvas {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
        }

        .grain {
            position: fixed;
            inset: 0;
            z-index: 1;
            pointer-events: none;
            opacity: 0.035;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
        }

        .site-header {
            position: sticky;
            top: 0;
            z-index: 50;
            border-bottom: 1px solid var(--glass-border);
            background: rgba(0, 0, 0, 0.28);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }

        .site-header__inner {
            max-width: 80rem;
            margin: 0 auto;
            padding: 0.75rem 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        @media (min-width: 640px) {
            .site-header__inner {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
                padding: 0.75rem 1.5rem;
            }
        }

        .brand { display: block; flex-shrink: 0; text-decoration: none; }

        .brand__logo {
            display: block;
            height: auto;
            width: 9.375rem;
        }

        @media (min-width: 640px) { .brand__logo { width: 11.25rem; } }
        @media (min-width: 1024px) { .brand__logo { width: 10.625rem; } }

        .nav {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            overflow-x: auto;
            border: 1px solid var(--glass-border);
            background: rgba(255, 255, 255, 0.06);
            border-radius: 9999px;
            padding: 0.25rem;
        }

        @media (min-width: 640px) { .nav { gap: 0.5rem; } }

        .nav a {
            flex-shrink: 0;
            border-radius: 9999px;
            padding: 0.5rem 0.875rem;
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            color: rgba(255, 255, 255, 0.8);
            transition: background 0.2s, color 0.2s;
            white-space: nowrap;
        }

        @media (min-width: 640px) { .nav a { padding: 0.5rem 1rem; } }

        .nav a:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        .nav a.is-active {
            background: #fff;
            color: #000;
        }

        .site-content { position: relative; z-index: 2; }

        .login-page {
            min-height: calc(100dvh - var(--header-height));
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        @media (min-width: 640px) { .login-page { padding: 2rem 1.5rem; } }

        .login-card {
            width: 100%;
            max-width: 24rem;
            border: 1px solid var(--glass-border);
            background: rgba(0, 0, 0, 0.35);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: 1.25rem;
            padding: 1.75rem;
            animation: fadeUp 0.6s ease forwards;
        }

        .login-card h1 {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .login-card .hint {
            margin-top: 0.5rem;
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        .login-card code {
            font-family: 'Roboto Mono', ui-monospace, monospace;
            font-size: 0.85em;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.08);
            padding: 0.15rem 0.45rem;
            border-radius: 0.375rem;
            color: #f48fb1;
        }

        .field {
            display: block;
            margin-top: 1.25rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: rgba(237, 237, 237, 0.9);
        }

        .field input[type="text"],
        .field input[type="password"] {
            display: block;
            width: 100%;
            margin-top: 0.375rem;
            padding: 0.65rem 0.875rem;
            border: 1px solid var(--glass-border);
            border-radius: 0.75rem;
            background: rgba(255, 255, 255, 0.06);
            color: var(--text);
            font: inherit;
            transition: border-color 0.2s, background 0.2s;
        }

        .field input:focus {
            outline: none;
            border-color: rgba(255, 255, 255, 0.25);
            background: rgba(255, 255, 255, 0.08);
        }

        .field--checkbox {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1rem;
            font-weight: 400;
            color: var(--text-muted);
            cursor: pointer;
        }

        .field--checkbox input {
            width: 1rem;
            height: 1rem;
            accent-color: var(--accent-magenta);
        }

        .error {
            color: #ff8a80;
            font-size: 0.8125rem;
            margin-top: 0.375rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            margin-top: 1.5rem;
            border: none;
            border-radius: 1rem;
            padding: 0.75rem 1.25rem;
            font-size: 0.875rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.15s;
        }

        .btn:active { transform: scale(0.98); }

        .btn--primary {
            background: #fff;
            color: #000;
        }

        .btn--primary:hover { opacity: 0.9; }

        .back-link {
            display: inline-block;
            margin-top: 1.5rem;
            font-size: 0.875rem;
            color: var(--text-muted);
            text-decoration: none;
            transition: color 0.2s;
        }

        .back-link:hover { color: #fff; }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(1.25rem); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (prefers-reduced-motion: reduce) {
            .login-card { animation: none; }
        }
    </style>
</head>
<body>
    <div class="site-shell">
        <div class="site-shell__background" aria-hidden="true">
            <canvas id="blobs"></canvas>
        </div>
        <div class="grain" aria-hidden="true"></div>

        <header class="site-header">
            <div class="site-header__inner">
                <a href="/" class="brand" aria-label="Ir al inicio">
                    <img
                        src="{{ asset('idoneo-dark.svg') }}"
                        alt="IDONEO"
                        class="brand__logo"
                        width="336"
                        height="125"
                    >
                </a>
                <nav class="nav">
                    <a href="/">Home</a>
                    <a href="{{ route('login') }}" class="is-active">Entrar</a>
                </nav>
            </div>
        </header>

        <div class="site-content">
            <main class="login-page">
                <div class="login-card">
                    <h1>Iniciar sesión</h1>
                    <p class="hint">Mismas credenciales que en el iPhone (email completo o usuario DAV, p. ej. <code>idoneo</code>).</p>

                    <form method="post" action="{{ route('login') }}">
                        @csrf
                        <label class="field">Email o usuario
                            <input type="text" name="login" value="{{ old('login') }}" required autofocus autocomplete="username">
                        </label>
                        @error('login')<p class="error">{{ $message }}</p>@enderror

                        <label class="field">Contraseña
                            <input type="password" name="password" required autocomplete="current-password">
                        </label>

                        <label class="field field--checkbox">
                            <input type="checkbox" name="remember">
                            Recordarme
                        </label>

                        <button type="submit" class="btn btn--primary">Entrar</button>
                    </form>

                    <a href="/" class="back-link">← Volver al inicio</a>
                </div>
            </main>
        </div>
    </div>

    <script>
        (function () {
            const canvas = document.getElementById('blobs');
            const ctx = canvas.getContext('2d');
            let w, h;

            const blobs = [
                { x: 0.12, y: 0.18, r: 0.22, color: 'rgba(224, 64, 251, 0.55)', vx: 0.00018, vy: 0.00012 },
                { x: 0.55, y: 0.35, r: 0.28, color: 'rgba(255, 23, 68, 0.5)', vx: -0.00014, vy: 0.00016 },
                { x: 0.88, y: 0.12, r: 0.2, color: 'rgba(255, 23, 68, 0.45)', vx: -0.0001, vy: 0.00008 },
                { x: 0.78, y: 0.78, r: 0.32, color: 'rgba(124, 77, 255, 0.45)', vx: 0.0001, vy: -0.00012 },
                { x: 0.25, y: 0.72, r: 0.18, color: 'rgba(124, 77, 255, 0.35)', vx: 0.00012, vy: -0.0001 },
            ];

            function resize() {
                w = canvas.width = window.innerWidth;
                h = canvas.height = window.innerHeight;
            }

            function draw() {
                ctx.clearRect(0, 0, w, h);
                ctx.filter = 'blur(80px)';

                blobs.forEach(function (b) {
                    b.x += b.vx;
                    b.y += b.vy;
                    if (b.x < -0.1 || b.x > 1.1) b.vx *= -1;
                    if (b.y < -0.1 || b.y > 1.1) b.vy *= -1;

                    ctx.beginPath();
                    ctx.arc(b.x * w, b.y * h, b.r * Math.min(w, h), 0, Math.PI * 2);
                    ctx.fillStyle = b.color;
                    ctx.fill();
                });

                ctx.filter = 'none';
                requestAnimationFrame(draw);
            }

            resize();
            window.addEventListener('resize', resize);
            draw();
        })();
    </script>
    @include('partials.analytics')
</body>
</html>
