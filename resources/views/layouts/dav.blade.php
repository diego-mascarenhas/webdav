<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') — {{ config('app.name') }}</title>
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

        .site-shell {
            position: relative;
            min-height: 100vh;
            min-height: 100dvh;
        }

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

        .brand {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: inherit;
            flex-shrink: 0;
        }

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
            background: var(--glass);
            border-radius: 9999px;
            padding: 0.25rem;
        }

        @media (min-width: 640px) { .nav { gap: 0.5rem; } }

        .nav a,
        .nav-logout button {
            flex-shrink: 0;
            border-radius: 9999px;
            padding: 0.5rem 0.875rem;
            font-size: 0.875rem;
            font-weight: 500;
            font-family: inherit;
            text-decoration: none;
            color: rgba(255, 255, 255, 0.8);
            transition: background 0.2s, color 0.2s;
            white-space: nowrap;
            border: none;
            background: transparent;
            cursor: pointer;
        }

        @media (min-width: 640px) {
            .nav a,
            .nav-logout button { padding: 0.5rem 1rem; }
        }

        .nav a:hover,
        .nav-logout button:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        .nav a.is-active {
            background: #fff;
            color: #000;
        }

        .nav-logout { display: contents; }

        .site-content { position: relative; z-index: 2; }

        .page {
            padding: 2rem 1rem 4rem;
        }

        @media (min-width: 640px) { .page { padding: 2rem 1.5rem 4rem; } }

        .page__inner {
            max-width: 48rem;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .page-header {
            animation: fadeUp 0.6s ease forwards;
        }

        .page-header__badge {
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
        }

        .page-header__badge-dot {
            width: 0.45rem;
            height: 0.45rem;
            border-radius: 50%;
            background: var(--accent-red);
            box-shadow: 0 0 8px var(--accent-red);
        }

        .page-header h1 {
            margin-top: 1rem;
            font-size: clamp(1.75rem, 5vw, 2.5rem);
            font-weight: 900;
            letter-spacing: -0.03em;
            line-height: 1.1;
        }

        .page-header__meta {
            margin-top: 0.5rem;
            color: var(--text-muted);
            font-size: 0.925rem;
        }

        .card {
            border: 1px solid var(--glass-border);
            background: rgba(0, 0, 0, 0.35);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: 1.25rem;
            padding: 1.5rem;
            animation: fadeUp 0.6s ease 0.1s forwards;
            opacity: 0;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .card:hover {
            border-color: rgba(255, 255, 255, 0.18);
            box-shadow: 0 0 40px rgba(224, 64, 251, 0.06);
        }

        .card p {
            color: var(--text-muted);
            font-size: 0.925rem;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }

        .data-table th,
        .data-table td {
            text-align: left;
            padding: 0.75rem 0.5rem;
            border-bottom: 1px solid var(--glass-border);
            vertical-align: top;
        }

        .data-table th {
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--text-muted);
        }

        .data-table tbody tr:last-child td { border-bottom: none; }

        .data-table td { color: rgba(237, 237, 237, 0.9); }

        .badge {
            display: inline-block;
            font-size: 0.75rem;
            font-weight: 500;
            padding: 0.15rem 0.5rem;
            border-radius: 9999px;
        }

        .badge--muted {
            background: rgba(255, 255, 255, 0.08);
            color: var(--text-muted);
        }

        .badge--done {
            background: rgba(34, 197, 94, 0.15);
            color: #86efac;
        }

        .badge--pending {
            background: rgba(255, 255, 255, 0.08);
            color: var(--text-muted);
        }

        .description {
            color: var(--text-muted);
            font-size: 0.8125rem;
            margin-top: 0.35rem;
            white-space: pre-wrap;
        }

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

        .api-hint {
            font-size: 0.8rem;
            color: var(--text-muted);
            opacity: 0.75;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(1.25rem);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .page-header,
            .card {
                animation: none;
                opacity: 1;
                transform: none;
            }
        }
    </style>
    @stack('head')
</head>
<body>
    <div class="site-shell">
        @include('partials.site-blobs')

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
                @include('partials.dav-nav', ['active' => trim($__env->yieldContent('nav_active'))])
            </div>
        </header>

        <div class="site-content">
            @yield('content')
        </div>
    </div>

    @include('partials.analytics')
    @stack('scripts')
</body>
</html>
