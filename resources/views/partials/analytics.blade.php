@if (config('analytics.ga_id') || config('analytics.vercel'))
    <style>
        .cookie-consent {
            position: fixed;
            z-index: 55;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            border-radius: 1rem;
            padding: 0.75rem 1rem;
            left: 1rem;
            right: 1rem;
            bottom: 1rem;
            width: auto;
        }

        @media (min-width: 640px) {
            .cookie-consent {
                left: 1.5rem;
                right: auto;
                bottom: 1.5rem;
                width: 23.75rem;
            }
        }

        .cookie-consent p {
            font-size: 0.75rem;
            opacity: 0.8;
            color: #ededed;
            margin: 0;
        }

        .cookie-consent__actions {
            margin-top: 0.5rem;
            display: flex;
            gap: 0.5rem;
        }

        .cookie-consent__accept {
            flex: 1;
            cursor: pointer;
            border: none;
            border-radius: 0.75rem;
            background: #fff;
            color: #000;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .cookie-consent__accept:hover { opacity: 0.9; }

        .cookie-consent__reject {
            cursor: pointer;
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 0.75rem;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .cookie-consent__reject:hover { background: rgba(255, 255, 255, 0.15); }

        .cookie-consent__prefs {
            position: fixed;
            z-index: 55;
            cursor: pointer;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            border-radius: 9999px;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            color: #ededed;
            left: 1rem;
            bottom: 1rem;
        }

        @media (min-width: 640px) {
            .cookie-consent__prefs {
                left: 1.5rem;
                bottom: 1.5rem;
            }
        }

        .cookie-consent__prefs:hover { background: rgba(0, 0, 0, 0.5); }

        .cookie-consent[hidden],
        .cookie-consent__prefs[hidden] { display: none !important; }
    </style>

    <div id="cookie-consent" class="cookie-consent" role="dialog" aria-modal="false" hidden>
        <p>Usamos cookies de <b>analítica</b> para mejorar el sitio.</p>
        <div class="cookie-consent__actions">
            <button type="button" class="cookie-consent__accept" data-consent="granted">Aceptar</button>
            <button type="button" class="cookie-consent__reject" data-consent="denied">Rechazar</button>
        </div>
    </div>

    <button type="button" id="cookie-preferences" class="cookie-consent__prefs" aria-label="Preferencias de cookies" hidden>
        🍪 Cookies
    </button>

    <script>
        (function () {
            var gaId = @json(config('analytics.ga_id'));
            var vercelEnabled = @json((bool) config('analytics.vercel'));
            var consentKey = 'analytics_consent';

            function getConsent() {
                var match = document.cookie.match(new RegExp('(?:^|; )' + consentKey + '=([^;]*)'));
                return match ? decodeURIComponent(match[1]) : null;
            }

            function setConsent(value) {
                var secure = window.location.protocol === 'https:' ? '; Secure' : '';
                document.cookie = consentKey + '=' + value + '; Path=/; Max-Age=31536000; SameSite=Lax' + secure;
            }

            function loadGoogleAnalytics() {
                if (!gaId || window.__gaLoaded) {
                    return;
                }

                window.dataLayer = window.dataLayer || [];
                window.gtag = function () { window.dataLayer.push(arguments); };
                window.gtag('js', new Date());
                window.gtag('config', gaId);

                var script = document.createElement('script');
                script.async = true;
                script.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(gaId);
                document.head.appendChild(script);
                window.__gaLoaded = true;
            }

            function loadVercelAnalytics() {
                if (!vercelEnabled || window.__vercelAnalyticsLoaded) {
                    return;
                }

                var script = document.createElement('script');
                script.defer = true;
                script.src = '/_vercel/insights/script.js';
                script.dataset.sdkn = '@vercel/analytics';
                script.dataset.sdkv = '1.6.1';
                document.head.appendChild(script);
                window.__vercelAnalyticsLoaded = true;
            }

            function loadAnalytics() {
                loadGoogleAnalytics();
                loadVercelAnalytics();
            }

            function updateUi(consent) {
                var banner = document.getElementById('cookie-consent');
                var prefs = document.getElementById('cookie-preferences');

                if (!banner || !prefs) {
                    return;
                }

                if (consent === null) {
                    banner.hidden = false;
                    prefs.hidden = true;
                    return;
                }

                banner.hidden = true;
                prefs.hidden = false;

                if (consent === 'granted') {
                    loadAnalytics();
                }
            }

            function handleConsent(value) {
                setConsent(value);
                updateUi(value);
            }

            document.querySelectorAll('[data-consent]').forEach(function (button) {
                button.addEventListener('click', function () {
                    handleConsent(button.getAttribute('data-consent'));
                });
            });

            var prefsButton = document.getElementById('cookie-preferences');
            if (prefsButton) {
                prefsButton.addEventListener('click', function () {
                    var banner = document.getElementById('cookie-consent');
                    if (banner) {
                        banner.hidden = false;
                        prefsButton.hidden = true;
                    }
                });
            }

            var initialConsent = getConsent();
            updateUi(initialConsent);
        })();
    </script>
@endif
