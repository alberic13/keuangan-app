<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>{{ $pageTitle ?? 'E-Keuangan MAN 2 Surakarta' }}</title>
    <script>
        (() => {
            try {
                const desktopState = localStorage.getItem('sidebarDesktopState');
                document.documentElement.dataset.sidebarDesktop = desktopState === 'closed' ? 'closed' : 'open';
            } catch (error) {
                document.documentElement.dataset.sidebarDesktop = 'open';
            }

            document.documentElement.dataset.sidebarMobile = 'closed';
        })();
    </script>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&amp;family=Inter:wght@300;400;500;600&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "surface-bright": "#f7f9ff",
                        "surface-variant": "#dfe3e8",
                        "on-surface-variant": "#3f4943",
                        "primary-container": "#005c42",
                        "surface-container-low": "#f1f4fa",
                        "secondary-container": "#cfe5d9",
                        "primary-fixed-dim": "#8bd6b4",
                        "surface-container-high": "#e5e8ee",
                        "surface": "#f7f9ff",
                        "background": "#f7f9ff",
                        "surface-container": "#ebeef4",
                        "error": "#ba1a1a",
                        "outline": "#6f7973",
                        "tertiary": "#622621",
                        "on-surface": "#181c20",
                        "secondary": "#4f6359",
                        "primary-fixed": "#a6f2d0",
                        "surface-container-lowest": "#ffffff",
                        "secondary-fixed": "#d2e7dc",
                        "on-background": "#181c20",
                        "on-primary": "#ffffff",
                        "error-container": "#ffdad6",
                        "surface-dim": "#d7dae0",
                        "primary": "#00422f"
                    },
                    borderRadius: {
                        DEFAULT: "0.125rem",
                        lg: "0.25rem",
                        xl: "0.5rem",
                        full: "0.75rem"
                    },
                    fontFamily: {
                        headline: ["Manrope"],
                        body: ["Inter"],
                        label: ["Inter"]
                    }
                },
            },
        };
    </script>
    @include('layouts.partials.styles')
    @livewireStyles
</head>
