<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-bs-theme="light" id="htmlRoot">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Global Supply Chain Risk Intelligence')</title>
    <meta name="description" content="@yield('meta_description', 'Real-time global supply chain risk intelligence platform.')">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
    <style>
        @keyframes blinker {
            50% { opacity: 0; }
        }
        /* TomSelect UI Overrides to match nb-select */
        .ts-wrapper.single .ts-control, .ts-wrapper .ts-control {
            background: var(--card-bg) !important;
            border: 1.5px solid var(--card-border) !important;
            border-radius: var(--r-md) !important;
            color: var(--text-dark) !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            padding: 8px 32px 8px 12px !important;
            font-size: 0.88rem !important;
            min-height: 40px !important;
            box-shadow: none !important;
        }
        [data-bs-theme="dark"] .ts-wrapper.single .ts-control, [data-bs-theme="dark"] .ts-wrapper .ts-control {
            background: rgba(255,255,255,0.05) !important;
        }
        .ts-wrapper.focus .ts-control {
            border-color: var(--primary) !important;
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.12) !important;
        }
        .ts-control input {
            color: var(--text-dark) !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            font-size: 0.88rem !important;
        }
        .ts-dropdown {
            background: var(--card-bg) !important;
            border: 1px solid var(--card-border) !important;
            border-radius: var(--r-md) !important;
            color: var(--text-dark) !important;
            box-shadow: var(--card-shadow-hover) !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            margin-top: 4px !important;
            z-index: 9999 !important;
            font-size: 0.88rem !important;
        }
        .ts-dropdown .option {
            padding: 8px 12px !important;
            color: var(--text-dark) !important;
            transition: background 0.1s;
        }
        .ts-dropdown .option:hover, .ts-dropdown .option.active {
            background-color: var(--bg) !important;
            color: var(--text-dark) !important;
        }

        /* Fix Leaflet Popups for Dark Mode */
        [data-bs-theme="dark"] .leaflet-popup-content-wrapper,
        [data-bs-theme="dark"] .leaflet-popup-tip {
            background: var(--card-bg) !important;
            color: var(--text-dark) !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.5) !important;
        }

        /* Complete Hide for Google Translate Top Banner, Icon Popup, Tooltips & Text Highlight */
        iframe.goog-te-banner-frame, .goog-te-banner-frame, body > .skiptranslate, iframe.skiptranslate, 
        #goog-gt-tt, .goog-te-balloon-frame, .goog-te-spinner-pos, .goog-tooltip, .goog-tooltip:hover,
        [class*="VIpgJd"], .VIpgJd-Z44Wfd-LgDevf, .VIpgJd-Z44Wfd-a91s4d-wUfd2e, .VIpgJd-Z44Wfd-a91s4d-O22p2e {
            display: none !important;
            visibility: hidden !important;
            height: 0 !important;
            width: 0 !important;
            opacity: 0 !important;
            pointer-events: none !important;
        }
        body { top: 0px !important; position: static !important; }
        .goog-text-highlight {
            background-color: transparent !important;
            background: transparent !important;
            box-shadow: none !important;
            border: none !important;
            pointer-events: none !important;
        }
        #google_translate_element, .goog-te-gadget { display: none !important; }

        /* High-Contrast Style for EN/ID Language Buttons */
        .sb-lang { display: flex; gap: 6px; width: 100%; margin-bottom: 8px; }
        .sb-lang-btn {
            flex: 1;
            font-size: 0.78rem;
            font-weight: 700;
            padding: 6px 10px;
            border-radius: var(--r-md);
            color: var(--text-dark, #cbd5e1);
            background: var(--card-bg, rgba(255, 255, 255, 0.08));
            border: 1px solid var(--card-border, rgba(255, 255, 255, 0.2));
            transition: all 0.2s ease;
            cursor: pointer;
            text-align: center;
        }
        .sb-lang-btn:hover {
            background: rgba(124, 58, 237, 0.2);
            color: #a78bfa;
            border-color: #7c3aed;
        }
        .sb-lang-btn.active {
            background: #7c3aed !important;
            color: #ffffff !important;
            border-color: #7c3aed !important;
            box-shadow: 0 2px 10px rgba(124, 58, 237, 0.4);
        }
    </style>
</head>
<body>

<div class="app-shell">
    <!-- ── SIDEBAR ─────────────────────────── -->
    <aside class="app-sidebar" id="appSidebar">
        <div class="sidebar-brand">
            <a href="{{ route('dashboard') }}" class="sb-brand-link">
                <div class="sb-brand-text">
                    <span class="sb-name">Go</span><span class="sb-accent">Supply</span>
                </div>
            </a>
            <button class="sb-collapse-btn" id="sidebarToggle" onclick="toggleSidebar()" title="Collapse">
                <i class="bi bi-layout-sidebar-reverse" id="sbCollapseIcon"></i>
            </button>
        </div>

        <nav class="sb-nav">
            <div class="sb-section-label">PLATFORM</div>

            <a href="{{ route('dashboard') }}"
               class="sb-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2-fill"></i>
                <span>{{ __('app.nav.dashboard') }}</span>
            </a>
            <a href="{{ route('compare') }}"
               class="sb-link {{ request()->routeIs('compare') ? 'active' : '' }}">
                <i class="bi bi-arrow-left-right"></i>
                <span>{{ __('app.nav.compare') }}</span>
            </a>
            <a href="{{ route('analytics.index') }}"
               title="{{ __('app.nav.analytics') }}"
               class="sb-link {{ request()->routeIs('analytics.*') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-line-fill"></i>
                <span>{{ __('app.nav.analytics') }}</span>
            </a>

            <div class="sb-section-label">OPERATIONS</div>

            <a href="{{ route('ports.index') }}"
               title="{{ __('app.nav.ports') }}"
               class="sb-link {{ request()->routeIs('ports.*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi" viewBox="0 0 16 16">
                  <path d="M7.5 11.5a.5.5 0 0 0 1 0v-4h-1v4z"/>
                  <path d="M8 0a.5.5 0 0 0-.5.5v1.077c-1.396.195-2.5 1.4-2.5 2.923a3 3 0 0 0 2.5 2.923V9h-3V7.5a.5.5 0 0 0-1 0v3.5a1.5 1.5 0 0 0 1.5 1.5h2.5v1.577C5.104 14.2 3 12.33 3 10a.5.5 0 0 0-1 0c0 3 2.686 5.5 6 5.5s6-2.5 6-5.5a.5.5 0 0 0-1 0c0 2.33-2.104 4.2-4.5 4.077V12.5h2.5A1.5 1.5 0 0 0 12 11V7.5a.5.5 0 0 0-1 0V9h-3V7.423A3 3 0 0 0 10.5 4.5c0-1.523-1.104-2.728-2.5-2.923V.5A.5.5 0 0 0 8 0zm0 2.5a2 2 0 1 1 0 4 2 2 0 0 1 0-4z"/>
                </svg>
                <span>{{ __('app.nav.ports') }}</span>
            </a>
            <a href="{{ route('news.index') }}"
               title="{{ __('app.nav.news') }}"
               class="sb-link {{ request()->routeIs('news.*') ? 'active' : '' }}">
                <i class="bi bi-newspaper"></i>
                <span>{{ __('app.nav.news') }}</span>
            </a>
            <a href="{{ route('currency.index') }}"
               title="{{ __('app.nav.currency') }}"
               class="sb-link {{ request()->routeIs('currency.*') ? 'active' : '' }}">
                <i class="bi bi-currency-exchange"></i>
                <span>{{ __('app.nav.currency') }}</span>
            </a>

            @auth
            <div class="sb-section-label">MY ACCOUNT</div>

            <a href="{{ route('watchlist.index') }}"
               title="{{ __('app.nav.watchlist') }}"
               class="sb-link {{ request()->routeIs('watchlist.*') ? 'active' : '' }}">
                <i class="bi bi-star-fill"></i>
                <span>{{ __('app.nav.watchlist') }}</span>
            </a>

            @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.index') }}"
               title="{{ __('app.nav.admin') }}"
               class="sb-link sb-link-admin {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                <i class="bi bi-shield-lock-fill"></i>
                <span>{{ __('app.nav.admin') }}</span>
            </a>
            @endif
            @endauth
        </nav>

        <div class="sb-footer">
            <div class="sb-lang mb-1 notranslate" translate="no">
                <button type="button" class="sb-lang-btn notranslate" translate="no" id="btnLangEN" onclick="switchGoogleLanguage('en')"><span class="notranslate" translate="no">EN</span></button>
                <button type="button" class="sb-lang-btn notranslate" translate="no" id="btnLangID" onclick="switchGoogleLanguage('id')"><span class="notranslate" translate="no">ID</span></button>
            </div>
            <button class="sb-theme-btn" id="themeToggle" onclick="toggleTheme()" title="Toggle theme">
                <i class="bi bi-moon-fill" id="themeIcon"></i>
                <span id="themeLabel">Dark</span>
            </button>
            @auth
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="sb-logout-btn" title="{{ __('app.nav.logout') }}">
                    <i class="bi bi-box-arrow-right"></i> <span>{{ __('app.nav.logout') }}</span>
                </button>
            </form>
            @else
            <div class="sb-footer-auth d-flex gap-2">
                <a href="{{ route('login') }}" class="nb-btn nb-btn-outline btn-sm flex-fill justify-content-center" style="font-size:0.76rem">{{ __('app.nav.login') }}</a>
                <a href="{{ route('register') }}" class="nb-btn nb-btn-primary btn-sm flex-fill justify-content-center" style="font-size:0.76rem">{{ __('app.nav.register') }}</a>
            </div>
            @endauth
        </div>
    </aside>

    <!-- ── MAIN CONTENT ────────────────────── -->
    <div class="app-content" id="appContent">
        <!-- top bar -->
        <header class="app-topbar">
            <button class="mobile-menu-btn d-md-none" onclick="toggleSidebar()">
                <i class="bi bi-list"></i>
            </button>
            <div class="topbar-title" id="pageTitle">@yield('page_title', 'Dashboard')</div>
            <div class="topbar-right">
                <div class="topbar-time" id="liveTime"></div>
                @auth
                <div class="topbar-user">
                    <i class="bi bi-person-circle"></i>
                    <span>{{ auth()->user()->name }}</span>
                </div>
                @endauth
            </div>
        </header>

        <!-- Real-Time Supply Chain Intelligence Ticker -->
        <div style="background: var(--card-bg); border-bottom: 1px solid var(--card-border); padding: 6px 16px; overflow: hidden; white-space: nowrap; box-sizing: border-box; display: flex; align-items: center; gap: 10px; font-size: 0.76rem; font-weight: 700; color: var(--text-dark); z-index: 10;">
            <span style="background: var(--nb-red); color: #fff; padding: 2px 8px; border-radius: 4px; font-size: 0.65rem; text-transform: uppercase; flex-shrink: 0; display: inline-flex; align-items: center; gap: 4px;">
                <span style="width:6px; height:6px; background:#fff; border-radius:50%; display:inline-block; animation: blinker 1s linear infinite;"></span> LIVE SIGNAL
            </span>
            <marquee scrollamount="4" style="flex: 1;" onmouseover="this.stop();" onmouseout="this.start();">
                 {!! $liveSignals ?? 'Loading live signals...' !!}
            </marquee>
        </div>

        <main class="app-main">
            @if(session('success'))
                <div class="nb-alert nb-alert-success mb-3">
                    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="nb-alert nb-alert-danger mb-3">
                    <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>

        <footer class="app-footer">
            <span>&copy; {{ date('Y') }} <strong>GoSupply</strong> {{ __('app.footer') }}</span>
        </footer>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="{{ asset('js/app.js') }}"></script>
<script>
function applyTheme(t, save) {
    const html = document.getElementById('htmlRoot');
    html.setAttribute('data-bs-theme', t);
    const icon  = document.getElementById('themeIcon');
    const label = document.getElementById('themeLabel');
    if (icon)  icon.className  = t === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
    if (label) label.textContent = t === 'dark' ? 'Light' : 'Dark';
    if (save !== false) localStorage.setItem('sciqTheme', t);
    if (typeof Chart !== 'undefined') {
        const dark = t === 'dark';
        Chart.defaults.color       = dark ? 'rgba(224,231,255,0.72)' : 'rgba(71,85,105,0.85)';
        Chart.defaults.borderColor = dark ? 'rgba(99,102,241,0.15)'  : 'rgba(203,213,225,0.50)';
        Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
        Chart.defaults.plugins.legend.labels.color = dark ? 'rgba(224,231,255,0.80)' : 'rgba(30,27,75,0.80)';
    }
}

function toggleTheme() {
    const cur = document.getElementById('htmlRoot').getAttribute('data-bs-theme');
    applyTheme(cur === 'light' ? 'dark' : 'light', true);
}

let sidebarCollapsed = false;

function collapseSidebar() {
    const sb = document.getElementById('appSidebar');
    sb.classList.add('collapsed');
    sidebarCollapsed = true;
}

function expandSidebar() {
    const sb = document.getElementById('appSidebar');
    sb.classList.remove('collapsed');
    sidebarCollapsed = false;
}

function toggleSidebar() {
    if (sidebarCollapsed) {
        expandSidebar();
        localStorage.removeItem('sciqSidebar');
    } else {
        collapseSidebar();
        localStorage.setItem('sciqSidebar', 'collapsed');
    }
}

// Mobile overlay close
document.addEventListener('click', function(e) {
    const sb = document.getElementById('appSidebar');
    if (window.innerWidth < 768 && sb && !sb.contains(e.target) && !e.target.closest('.mobile-menu-btn')) {
        sb.classList.remove('mobile-open');
    }
});

(function() {
    const saved = localStorage.getItem('sciqTheme') || 'light';
    applyTheme(saved, false);
    if (localStorage.getItem('sciqSidebar') === 'collapsed') collapseSidebar();

    function tick() {
        const el = document.getElementById('liveTime');
        if (!el) return;
        
        const now = new Date();
        const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        
        const dayName = days[now.getDay()];
        const dateNum = now.getDate();
        const monthName = months[now.getMonth()];
        const year = now.getFullYear();
        
        const timeStr = now.toLocaleTimeString('en-US', { hour12: true });
        
        const offsetMin = -now.getTimezoneOffset();
        const offsetHours = Math.floor(Math.abs(offsetMin) / 60);
        const offsetMins = Math.abs(offsetMin) % 60;
        const sign = offsetMin >= 0 ? '+' : '-';
        const utcStr = `UTC${sign}${offsetHours}${offsetMins > 0 ? ':' + (offsetMins < 10 ? '0' : '') + offsetMins : ''}`;
        
        el.textContent = `${dayName}, ${dateNum} ${monthName} ${year} • ${timeStr} (${utcStr})`;
    }
    tick();
    setInterval(tick, 1000);
})();
</script>
@stack('scripts')

@if(request()->routeIs('ports.*') || request()->routeIs('analytics.*'))
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endif
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll('.nb-select-country').forEach(function(el) {
            new TomSelect(el, {
                create: false,
                dropdownParent: "body",
                sortField: { field: "text", direction: "asc" },
                render: {
                    option: function(data, escape) {
                        return '<div class="d-flex align-items-center">' +
                            (data.src ? '<img src="' + data.src + '" width="20" style="margin-right: 8px; border-radius: 2px;">' : '') +
                            '<span>' + escape(data.text) + '</span>' +
                            '</div>';
                    },
                    item: function(data, escape) {
                        return '<div class="d-flex align-items-center">' +
                            (data.src ? '<img src="' + data.src + '" width="20" style="margin-right: 8px; border-radius: 2px;">' : '') +
                            '<span>' + escape(data.text) + '</span>' +
                            '</div>';
                    }
                }
            });
        });
    });
</script>

<div id="google_translate_element" style="display:none;"></div>
<script type="text/javascript">
function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
    return null;
}

function updateLangBtnState() {
    const googtrans = getCookie('googtrans');
    const btnEN = document.getElementById('btnLangEN');
    const btnID = document.getElementById('btnLangID');
    if (!btnEN || !btnID) return;
    
    if (googtrans && googtrans.includes('/id')) {
        btnID.classList.add('active');
        btnEN.classList.remove('active');
    } else {
        btnEN.classList.add('active');
        btnID.classList.remove('active');
    }
}

function switchGoogleLanguage(lang) {
    const targetVal = '/en/' + lang;
    const currentCookie = getCookie('googtrans');
    
    if (lang === 'en' && (!currentCookie || currentCookie.endsWith('/en'))) {
        return;
    }
    if (lang === 'id' && currentCookie && currentCookie.endsWith('/id')) {
        return;
    }
    
    document.cookie = "googtrans=" + targetVal + "; path=/;";
    document.cookie = "googtrans=" + targetVal + "; path=/; domain=" + window.location.hostname;
    
    const select = document.querySelector('.goog-te-combo');
    if (select) {
        select.value = lang;
        select.dispatchEvent(new Event('change'));
        updateLangBtnState();
    } else {
        window.location.reload();
    }
}

function googleTranslateElementInit() {
    new google.translate.TranslateElement({
        pageLanguage: 'en',
        includedLanguages: 'en,id',
        autoDisplay: false
    }, 'google_translate_element');
}

document.addEventListener('DOMContentLoaded', updateLangBtnState);
</script>
<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
</body>
</html>
