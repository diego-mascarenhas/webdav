<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} — CardDAV / CalDAV</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg: #0a0a0a;
            --text: #ededed;
            --text-muted: rgba(237, 237, 237, 0.65);
            --glass: rgba(255, 255, 255, 0.06);
            --glass-border: rgba(255, 255, 255, 0.1);
            --accent-magenta: #e040fb;
            --accent-red: #ff1744;
            --accent-purple: #7c4dff;
            --header-height: 73px;
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Roboto', Arial, Helvetica, sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        .site-shell { 
            position: relative; 
            min-height: 100vh;
            min-height: 100dvh; 
        }

        .site-shell__background {
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            z-index: 0;
            pointer-events: none;
        }

        .site-shell__background canvas {
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        .grain {
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
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

        .brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: inherit;
            flex-shrink: 0;
        }

        .brand__logo {
            display: block;
            height: auto;
            width: 9.375rem;
        }

        @media (min-width: 640px) {
            .brand__logo { width: 11.25rem; }
        }

        @media (min-width: 1024px) {
            .brand__logo { width: 10.625rem; }
        }

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

        .hero {
            min-height: calc(100vh - var(--header-height));
            min-height: calc(100dvh - var(--header-height));
            display: flex;
            align-items: center;
            padding: 2rem 1rem 4rem;
        }

        @media (min-width: 640px) { .hero { padding: 2rem 1.5rem 4rem; } }

        .hero__inner {
            max-width: 64rem;
            margin: 0 auto;
            width: 100%;
            text-align: center;
        }

        @media (min-width: 768px) { .hero__inner { text-align: left; } }

        .hero__badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: 1px solid var(--glass-border);
            background: var(--glass);
            border-radius: 9999px;
            padding: 0.35rem 0.875rem;
            font-size: 0.75rem;
            font-weight: 500;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            opacity: 0;
            animation: fadeUp 0.7s ease forwards;
        }

        .hero__badge-dot {
            width: 0.45rem;
            height: 0.45rem;
            border-radius: 50%;
            background: var(--accent-red);
            box-shadow: 0 0 8px var(--accent-red);
            animation: pulse 2s ease infinite;
        }

        .hero h1 {
            margin-top: 1.25rem;
            font-size: clamp(2.5rem, 8vw, 5.5rem);
            font-weight: 900;
            letter-spacing: -0.03em;
            line-height: 1.05;
            opacity: 0;
            animation: fadeUp 0.7s ease 0.1s forwards;
        }

        .hero h1 span {
            background: linear-gradient(135deg, #fff 40%, rgba(255, 255, 255, 0.5));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero__subtitle {
            margin-top: 1.25rem;
            max-width: 32rem;
            font-size: clamp(1rem, 2.5vw, 1.25rem);
            opacity: 0;
            color: rgba(237, 237, 237, 0.8);
            animation: fadeUp 0.7s ease 0.2s forwards;
        }

        @media (min-width: 768px) { .hero__subtitle { margin-left: 0; margin-right: auto; } }

        .hero__actions {
            margin-top: 2rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            opacity: 0;
            animation: fadeUp 0.7s ease 0.3s forwards;
        }

        @media (min-width: 640px) {
            .hero__actions {
                flex-direction: row;
                justify-content: center;
            }
        }

        @media (min-width: 768px) { .hero__actions { justify-content: flex-start; } }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 1rem;
            padding: 0.75rem 1.25rem;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            transition: opacity 0.2s, background 0.2s, transform 0.15s;
            cursor: pointer;
            border: none;
        }

        .btn:active { transform: scale(0.98); }

        .btn--primary {
            background: #fff;
            color: #000;
        }

        .btn--primary:hover { opacity: 0.9; }

        .btn--ghost {
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        .btn--ghost:hover { background: rgba(255, 255, 255, 0.12); }

        .docs {
            max-width: 48rem;
            margin: 0 auto;
            padding: 0 1rem 6rem;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        @media (min-width: 640px) { .docs { padding: 0 1.5rem 6rem; } }

        .card {
            border: 1px solid var(--glass-border);
            background: rgba(0, 0, 0, 0.35);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: 1.25rem;
            padding: 1.5rem;
            opacity: 0;
            transform: translateY(1.5rem);
            -webkit-transform: translateY(1.5rem);
            transition: border-color 0.3s, box-shadow 0.3s;
            -webkit-transition: border-color 0.3s, box-shadow 0.3s;
        }

        .card.is-visible {
            opacity: 1;
            transform: translateY(0);
            -webkit-transform: translateY(0);
            transition: opacity 0.6s ease, transform 0.6s ease, border-color 0.3s, box-shadow 0.3s;
            -webkit-transition: opacity 0.6s ease, -webkit-transform 0.6s ease, border-color 0.3s, box-shadow 0.3s;
        }

        .card:hover {
            border-color: rgba(255, 255, 255, 0.18);
            box-shadow: 0 0 40px rgba(224, 64, 251, 0.06);
        }

        .card h2 {
            font-size: 1.1rem;
            font-weight: 700;
            letter-spacing: -0.01em;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card h2::before {
            content: '';
            width: 0.35rem;
            height: 0.35rem;
            border-radius: 50%;
            background: var(--accent-magenta);
            box-shadow: 0 0 6px var(--accent-magenta);
            flex-shrink: 0;
        }

        .card p { color: var(--text-muted); font-size: 0.925rem; }
        .card p + p { margin-top: 0.5rem; }

        .card ul {
            list-style: none;
            padding: 0;
            margin-top: 0.5rem;
        }

        .card li {
            color: var(--text-muted);
            font-size: 0.925rem;
            padding: 0.35rem 0;
            padding-left: 1rem;
            position: relative;
        }

        .card li::before {
            content: '→';
            position: absolute;
            left: 0;
            color: var(--accent-red);
            font-size: 0.8rem;
        }

        .card li strong { color: var(--text); font-weight: 500; }

        code {
            font-family: 'Roboto Mono', ui-monospace, monospace;
            font-size: 0.85em;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.08);
            padding: 0.15rem 0.45rem;
            border-radius: 0.375rem;
            color: #f48fb1;
            word-break: break-all;
        }

        .code-block {
            display: block;
            margin-top: 0.75rem;
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid var(--glass-border);
            border-radius: 0.75rem;
            padding: 1rem 1.25rem;
            color: rgba(237, 237, 237, 0.85);
            line-height: 1.7;
            overflow-x: auto;
            white-space: pre;
            font-family: 'Roboto Mono', ui-monospace, monospace;
            font-size: 0.85em;
        }

        .card a:not(.btn) {
            color: #fff;
            text-decoration: underline;
            text-decoration-color: rgba(255, 255, 255, 0.25);
            text-underline-offset: 3px;
            transition: text-decoration-color 0.2s;
        }

        .card a:not(.btn):hover { text-decoration-color: var(--accent-magenta); }

        .meta { font-size: 0.8rem; opacity: 0.5; }

        @keyframes fadeUp {
            from { 
                opacity: 0; 
                transform: translateY(1.25rem); 
                -webkit-transform: translateY(1.25rem);
            }
            to { 
                opacity: 1; 
                transform: translateY(0); 
                -webkit-transform: translateY(0);
            }
        }
        
        @-webkit-keyframes fadeUp {
            from { 
                opacity: 0; 
                -webkit-transform: translateY(1.25rem);
            }
            to { 
                opacity: 1; 
                -webkit-transform: translateY(0);
            }
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }

        @media (prefers-reduced-motion: reduce) {
            .hero__badge, .hero h1, .hero__subtitle, .hero__actions {
                opacity: 1;
                animation: none;
            }
            .card { opacity: 1; transform: none; }
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
                    <a href="/" class="is-active">Home</a>
                    <a href="{{ route('login') }}">Entrar</a>
                </nav>
            </div>
        </header>

        <div class="site-content">
            <section class="hero">
                <div class="hero__inner">
                    <div class="hero__badge">
                        <span class="hero__badge-dot"></span>
                        Sync sin fronteras
                    </div>
                    <h1><span>{{ config('app.name') }}</span></h1>
                    <p class="hero__subtitle">
                        Servidor CardDAV y CalDAV para sincronizar contactos y calendario en iPhone, iPad y Android.
                    </p>
                    <div class="hero__actions">
                        <a href="{{ route('contacts.index') }}" class="btn btn--primary">Ver contactos</a>
                        <a href="#setup" class="btn btn--ghost">Cómo conectar</a>
                    </div>
                </div>
            </section>

            <div class="docs" id="setup">
                <article class="card">
                    <h2>URL del servidor</h2>
                    <p><code>{{ $davUrl }}</code></p>
                    <p>Usa siempre la barra final. En producción, HTTPS es obligatorio para iOS.</p>
                </article>

                <article class="card">
                    <h2>Primer uso</h2>
                    <pre class="code-block">php artisan migrate
php artisan dav:setup
php artisan dav:password tu@email.com</pre>
                </article>

                <article class="card">
                    <h2>iPhone / iPad</h2>
                    <ul>
                        <li><strong>Contactos:</strong> Ajustes → Apps → Contactos → Cuentas de contactos → Añadir cuenta → Añadir otra cuenta → Añadir cuenta CardDAV</li>
                        <li><strong>Calendario:</strong> Ajustes → Apps → Calendario → Cuentas de Calendario → Añadir cuenta → Añadir otra cuenta → Cuenta CalDAV</li>
                    </ul>
                </article>

                <article class="card">
                    <h2>Android</h2>
                    <p>Instala una app compatible (p. ej. DAVx⁵) y apunta a la misma URL con usuario y contraseña de <code>dav:setup</code>.</p>
                </article>

                <article class="card">
                    <h2>Acceso web</h2>
                    <p><a href="{{ route('contacts.index') }}">Ver contactos y calendario en la web</a> — mismo usuario que CardDAV/CalDAV.</p>
                    <p class="meta">Si no has iniciado sesión, te pedirá usuario y contraseña.</p>
                    <p class="meta" style="margin-top: 0.5rem;"><a href="{{ $davUrl }}">Explorar DAV</a> — solo muestra XML técnico; no es el listado de contactos.</p>
                </article>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const canvas = document.getElementById('blobs');
            if (!canvas) return;
            
            const ctx = canvas.getContext('2d');
            if (!ctx) return;
            
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
                
                // Safari compatibility: check if filter is supported
                if ('filter' in ctx) {
                    ctx.filter = 'blur(80px)';
                }

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

                if ('filter' in ctx) {
                    ctx.filter = 'none';
                }
                
                requestAnimationFrame(draw);
            }

            resize();
            window.addEventListener('resize', resize);
            draw();

            // Enhanced Safari compatibility for IntersectionObserver
            if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                const cards = document.querySelectorAll('.card');
                if (window.IntersectionObserver) {
                    cards.forEach(function (card, i) {
                        card.style.transitionDelay = (i * 0.08) + 's';
                        new IntersectionObserver(function (entries, obs) {
                            entries.forEach(function (entry) {
                                if (entry.isIntersecting) {
                                    entry.target.classList.add('is-visible');
                                    obs.unobserve(entry.target);
                                }
                            });
                        }, { threshold: 0.15 }).observe(card);
                    });
                } else {
                    // Fallback for older browsers
                    cards.forEach(function (card) {
                        card.classList.add('is-visible');
                    });
                }
            } else {
                document.querySelectorAll('.card').forEach(function (card) {
                    card.classList.add('is-visible');
                });
            }
        })();
    </script>
    @include('partials.analytics')
</body>
</html>
