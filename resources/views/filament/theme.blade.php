<style>
    :root {
        --app-navy: #11172f;
        --app-navy-soft: #1a2344;
        --app-indigo: #5146e5;
        --app-blue: #3478f6;
        --app-canvas: #f4f6fb;
        --app-border: #e6eaf2;
    }

    html:not(.dark) body.fi-body,
    html:not(.dark) .fi-main-ctn {
        background: var(--app-canvas) !important;
        color: #172033;
    }

    /* Sidebar navy dan menu gradient mengikuti referensi ClassTrack. */
    .fi-sidebar {
        color: #e7eaf5;
        background: var(--app-navy) !important;
        border-right: 1px solid rgb(255 255 255 / 7%);
    }

    .fi-sidebar-header,
    .fi-body-has-topbar .fi-sidebar-header {
        color: #fff;
        background: var(--app-navy) !important;
        border-bottom: 1px solid rgb(255 255 255 / 8%);
        box-shadow: none;
    }

    .fi-sidebar-header .fi-logo,
    .fi-sidebar-header .fi-brand-name {
        color: #fff;
    }

    .fi-sidebar-group-label {
        color: #8994b7 !important;
        font-size: .68rem;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
    }

    .fi-sidebar-item-btn {
        color: #c3c9df;
        border-radius: .8rem;
        transition: background-color .18s ease, color .18s ease, box-shadow .18s ease, transform .18s ease;
    }

    .fi-sidebar-item:not(.fi-active) .fi-sidebar-item-btn:hover {
        color: #fff !important;
        background: var(--app-navy-soft) !important;
        transform: translateX(2px);
    }

    .fi-sidebar-item.fi-active .fi-sidebar-item-btn {
        color: #fff !important;
        background: linear-gradient(110deg, var(--app-indigo), var(--app-blue)) !important;
        box-shadow: 0 8px 20px rgb(52 120 246 / 22%);
    }

    .fi-sidebar-item.fi-active .fi-sidebar-item-icon {
        color: #fff !important;
    }

    html:not(.dark) .fi-topbar {
        background: rgb(255 255 255 / 92%);
        border-bottom: 1px solid var(--app-border);
        box-shadow: 0 2px 10px rgb(18 30 58 / 3%);
        backdrop-filter: blur(14px);
    }

    html:not(.dark) .fi-section {
        border-color: var(--app-border);
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 2px 8px rgb(16 24 40 / 4%);
    }

    .fi-ta-ctn {
        overflow: hidden;
        border-radius: 1rem;
        transition: box-shadow .18s ease, border-color .18s ease;
    }

    html:not(.dark) .fi-ta-ctn {
        border-color: var(--app-border);
        background: #fff;
        box-shadow: 0 2px 8px rgb(16 24 40 / 4%);
    }

    html:not(.dark) .fi-ta-header-ctn {
        background: #fff;
        border-bottom-color: var(--app-border);
    }

    html:not(.dark) .fi-ta-table thead th {
        background: #f7f8fc;
        color: #536079;
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .055em;
        text-transform: uppercase;
    }

    html:not(.dark) .fi-ta-table tbody tr:hover {
        background: #f7f8ff;
    }

    html:not(.dark) .fi-wi-stats-overview-stat {
        border: 1px solid var(--app-border);
        border-radius: 1rem;
        background: linear-gradient(145deg, #fff, #fafbff);
        box-shadow: 0 3px 12px rgb(16 24 40 / 4%);
        transition: transform .18s ease, box-shadow .18s ease;
    }

    html:not(.dark) .fi-wi-stats-overview-stat:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 24px rgb(49 61 105 / 9%);
    }

    .fi-btn {
        border-radius: .7rem;
        transition: transform .16s ease, box-shadow .16s ease;
    }

    .fi-btn:active {
        transform: translateY(1px);
    }

    .fi-quick-link:focus-visible {
        outline: 2px solid rgb(245 158 11 / 75%);
        outline-offset: 2px;
    }

    .fi-btn {
        border-radius: .65rem;
    }

    @keyframes admin-page-enter {
        from {
            opacity: 0;
            transform: translateY(6px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .fi-page {
        animation: admin-page-enter 180ms ease-out both;
    }

    html:not(.dark) .fi-simple-layout {
        background:
            radial-gradient(ellipse at 8% 8%, rgb(99 102 241 / 12%), transparent 34%),
            radial-gradient(ellipse at 92% 92%, rgb(56 189 248 / 10%), transparent 36%),
            #f4f6fb;
    }

    html:not(.dark) .fi-simple-main {
        border: 1px solid var(--app-border);
        border-radius: 1.25rem;
        box-shadow: 0 24px 60px rgb(28 37 75 / 10%);
    }

    .fi-simple-header .fi-logo {
        max-height: 3rem;
        width: auto;
        object-fit: contain;
    }

    @media (max-width: 1023px) {
        .fi-sidebar.fi-sidebar-open {
            box-shadow: 0 16px 40px rgb(16 24 40 / 16%);
        }
    }

    @media (max-width: 640px) {
        .fi-page {
            padding-inline: .25rem;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        *,
        *::before,
        *::after {
            animation-duration: .01ms !important;
            animation-iteration-count: 1 !important;
            scroll-behavior: auto !important;
            transition-duration: .01ms !important;
        }
    }
</style>
