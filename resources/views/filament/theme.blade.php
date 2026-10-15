<style>
    /* Tema admin terang dengan aksen amber, mengikuti tampilan halaman login. */
    html:not(.dark) body.fi-body {
        background: #f3f6fa !important;
        color: #172033;
    }

    html:not(.dark) .fi-main-ctn {
        background: #f3f6fa;
    }

    html:not(.dark) .fi-sidebar {
        background: #fff !important;
        border-right: 1px solid #e5eaf0;
    }

    html:not(.dark) .fi-sidebar-header,
    html:not(.dark) .fi-body-has-topbar .fi-sidebar-header {
        background: #fff;
        border-bottom: 1px solid #eef1f5;
        box-shadow: none;
    }

    html:not(.dark) .fi-topbar {
        background: #fff;
        border-bottom: 1px solid #e5eaf0;
        box-shadow: none;
    }

    html:not(.dark) .fi-sidebar-group-label {
        color: #667085;
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .fi-sidebar-item-btn {
        border-radius: .65rem;
        transition: background-color .15s ease, color .15s ease;
    }

    html:not(.dark) .fi-sidebar-item:not(.fi-active) .fi-sidebar-item-btn:hover {
        background: #f3f6fa;
        color: #172033;
    }

    html:not(.dark) .fi-sidebar-item.fi-active .fi-sidebar-item-btn {
        background: #fff5e8;
        color: #b45309;
    }

    html:not(.dark) .fi-section {
        border-color: #e5eaf0;
        border-radius: 1rem;
        box-shadow: 0 1px 2px rgb(16 24 40 / 5%);
    }

    .fi-btn {
        border-radius: .65rem;
    }

    html:not(.dark) .fi-simple-layout {
        background: #f5f7fa;
    }

    html:not(.dark) .fi-simple-main {
        border: 1px solid #e5e7eb;
        border-radius: 1rem;
        box-shadow: 0 12px 32px rgb(16 24 40 / 6%);
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
</style>
