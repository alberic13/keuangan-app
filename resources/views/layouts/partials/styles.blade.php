<style>
    body { font-family: 'Inter', sans-serif; }
    h1, h2, h3, .font-headline { font-family: 'Manrope', sans-serif; }
    .material-symbols-outlined {
        font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
    }
    .glass-header {
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
    }
    .app-sidebar,
    .app-main,
    .app-header,
    .app-sidebar-overlay {
        transition: transform 220ms ease, left 220ms ease, margin-left 220ms ease, opacity 220ms ease;
    }
    .app-content-shell {
        width: 100%;
        max-width: 1440px;
        margin-inline: auto;
    }
    .app-header-center {
        justify-content: flex-start;
        text-align: left;
        transition: justify-content 220ms ease, text-align 220ms ease, padding 220ms ease;
    }
    .app-header-title {
        display: block;
        max-width: min(100%, calc(100vw - 9rem));
        width: auto;
        padding: 0;
        background: transparent;
        box-shadow: none;
        transition: width 220ms ease, max-width 220ms ease;
    }
    .app-header-actions {
        min-width: 0;
    }
    .app-header-search {
        width: 15rem;
        transition: width 220ms ease, opacity 220ms ease;
    }
    .app-sidebar {
        transform: translateX(-100%);
    }
    .app-sidebar-overlay {
        opacity: 0;
        pointer-events: none;
    }
    html[data-sidebar-mobile="open"] .app-sidebar {
        transform: translateX(0);
    }
    html[data-sidebar-mobile="open"] .app-sidebar-overlay {
        opacity: 1;
        pointer-events: auto;
    }
    @media (min-width: 768px) {
        .app-sidebar {
            transform: translateX(0);
        }
        .app-main {
            margin-left: 18rem;
        }
        .app-header {
            left: 18rem;
        }
        .app-sidebar-overlay {
            display: none;
        }
        html[data-sidebar-desktop="closed"] .app-sidebar {
            transform: translateX(-100%);
        }
        html[data-sidebar-desktop="closed"] .app-main {
            margin-left: 0;
        }
        html[data-sidebar-desktop="closed"] .app-header {
            left: 0;
        }
        html[data-sidebar-desktop="closed"] .app-header-center {
            padding-left: 0;
        }
        html[data-sidebar-desktop="closed"] .app-header-title {
            width: auto;
            max-width: min(44rem, calc(100vw - 18rem));
        }
        html[data-sidebar-desktop="open"] .app-header-center {
            justify-content: center;
            text-align: center;
            padding-inline: clamp(0.5rem, 1.6vw, 1.25rem);
        }
        html[data-sidebar-desktop="open"] .app-header-title {
            width: min(21rem, calc(100vw - 33rem));
            max-width: min(21rem, calc(100vw - 33rem));
        }
        html[data-sidebar-desktop="open"] .app-header-search {
            width: 10.75rem;
        }
    }
    @media (min-width: 1280px) {
        .app-header-search {
            width: 17rem;
        }
        html[data-sidebar-desktop="open"] .app-header-search {
            width: 11.75rem;
        }
    }
    @media (max-width: 1023px) {
        .app-header-title {
            max-width: calc(100vw - 8rem);
        }
    }
</style>
