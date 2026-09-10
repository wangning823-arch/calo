<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        darkMode: 'class',
        theme: {
            extend: {
                colors: {
                    brand: {
                        50: '#ecfdf5',
                        100: '#d1fae5',
                        200: '#a7f3d0',
                        300: '#6ee7b7',
                        400: '#34d399',
                        500: '#10b981',
                        600: '#059669',
                        700: '#047857',
                        800: '#065f46',
                        900: '#064e3b',
                        950: '#022c22',
                    },
                    flame: {
                        50: '#fff7ed',
                        100: '#ffedd5',
                        400: '#fb923c',
                        500: '#f97316',
                        600: '#ea580c',
                    },
                },
                fontFamily: {
                    sans: ['"PingFang SC"', '"Hiragino Sans GB"', '"Microsoft YaHei"', 'system-ui', '-apple-system', 'Segoe UI', 'sans-serif'],
                    number: ['"SF Pro Display"', '"DIN Alternate"', 'Bahnschrift', 'system-ui', 'sans-serif'],
                },
                boxShadow: {
                    card: '0 1px 2px rgba(16, 24, 40, 0.04), 0 8px 24px rgba(16, 24, 40, 0.06)',
                    soft: '0 2px 8px rgba(16, 24, 40, 0.05)',
                    lift: '0 4px 16px rgba(16, 24, 40, 0.10)',
                },
                borderRadius: {
                    '2xl': '1rem',
                    '3xl': '1.25rem',
                },
            },
        },
    };
</script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<style>
    :root {
        --calo-bg: #f4f7f5;
        --calo-surface: #ffffff;
        --calo-ink: #14201a;
        --calo-muted: #6b7c73;
        --calo-line: #e4ebe6;
        --calo-brand: #059669;
        --calo-brand-soft: #ecfdf5;
        --calo-flame: #f97316;
    }

    .dark {
        --calo-bg: #0c1210;
        --calo-surface: #151c19;
        --calo-ink: #eef6f1;
        --calo-muted: #9aada3;
        --calo-line: #243029;
        --calo-brand: #34d399;
        --calo-brand-soft: rgba(16, 185, 129, 0.12);
        --calo-flame: #fb923c;
    }

    html {
        -webkit-tap-highlight-color: transparent;
    }

    body {
        background:
            radial-gradient(1200px 480px at 10% -10%, rgba(16, 185, 129, 0.10), transparent 55%),
            radial-gradient(900px 420px at 100% 0%, rgba(249, 115, 22, 0.08), transparent 50%),
            var(--calo-bg);
        color: var(--calo-ink);
        font-feature-settings: 'ss01' on, 'cv01' on;
    }

    .dark body {
        background:
            radial-gradient(1200px 480px at 10% -10%, rgba(16, 185, 129, 0.08), transparent 55%),
            radial-gradient(900px 420px at 100% 0%, rgba(249, 115, 22, 0.05), transparent 50%),
            var(--calo-bg);
    }

    /* Card surface utility used across pages */
    .card {
        background: var(--calo-surface);
        border: 1px solid var(--calo-line);
        border-radius: 1rem;
        box-shadow: 0 1px 2px rgba(16, 24, 40, 0.03), 0 8px 24px rgba(16, 24, 40, 0.04);
    }

    .app-header {
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        background: rgba(244, 247, 245, 0.82);
        border-bottom: 1px solid var(--calo-line);
    }

    .dark .app-header {
        background: rgba(12, 18, 16, 0.82);
    }

    .progress-ring {
        transform: rotate(-90deg);
    }

    .progress-track {
        display: block;
        overflow: visible;
    }

    .progress-ring-circle {
        transition: stroke-dashoffset 0.7s cubic-bezier(0.22, 1, 0.36, 1);
        filter: drop-shadow(0 2px 6px rgba(5, 150, 105, 0.25));
    }

    .nav-item-active {
        background: var(--calo-brand-soft);
        color: var(--calo-brand);
    }

    .btn-primary {
        background: linear-gradient(180deg, #10b981 0%, #059669 100%);
        color: #fff;
        border-radius: 0.75rem;
        font-weight: 600;
        box-shadow: 0 8px 16px rgba(5, 150, 105, 0.22);
        transition: transform 0.15s ease, box-shadow 0.15s ease, filter 0.15s ease;
    }

    .btn-primary:hover {
        filter: brightness(1.03);
        box-shadow: 0 10px 20px rgba(5, 150, 105, 0.28);
    }

    .btn-primary:active {
        transform: translateY(1px);
    }

    .btn-primary:disabled {
        opacity: 0.45;
        box-shadow: none;
        cursor: not-allowed;
    }

    .input-field {
        width: 100%;
        border-radius: 0.75rem;
        border: 1px solid var(--calo-line);
        background: var(--calo-surface);
        color: var(--calo-ink);
        padding: 0.7rem 0.9rem;
        font-size: 0.95rem;
        outline: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .input-field::placeholder {
        color: var(--calo-muted);
    }

    .input-field:focus {
        border-color: var(--calo-brand);
        box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.15);
    }

    .safe-bottom {
        padding-bottom: calc(0.5rem + env(safe-area-inset-bottom, 0px));
    }

    @media (prefers-reduced-motion: reduce) {
        .progress-ring-circle,
        .btn-primary {
            transition: none;
        }
    }

    /* Desktop sidebar */
    @media (min-width: 768px) {
        .desktop-sidebar {
            display: flex !important;
            flex-direction: column;
        }
        .mobile-header-bar,
        .mobile-bottom-nav {
            display: none !important;
        }
        .page-content,
        .page-shell {
            padding-bottom: 0 !important;
        }
    }

    @media (max-width: 767px) {
        .mobile-header-bar {
            display: block !important;
        }
        .mobile-bottom-nav {
            display: none !important;
        }
        .desktop-sidebar {
            display: none !important;
        }
        /* App bar height offset for sticky page headers */
        .page-top-offset {
            top: 57px;
        }
    }
</style>
