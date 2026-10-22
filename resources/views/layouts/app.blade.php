<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', 'Anomalias ETEC Lab')</title>
        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <style>
            {!! preg_replace('/\A.*?(?=\.hero-lab\s*\{)/s', '', file_get_contents(resource_path('css/app.css'))) !!}
        </style>
        @stack('styles')
    </head>
    <body class="hero-lab">
        <header class="site-header">
            <nav class="site-nav" aria-label="Navegação principal">
                <a class="brand-mark" href="{{ route('home') }}">
                    <span class="brand-emblem">AEL</span>
                    <span>Anomalias ETEC Lab</span>
                </a>

                <div class="nav-actions">
                    <button class="nav-link" type="button" data-coming-soon>Explorar anomalias</button>
                    <a class="nav-link nav-link-primary" href="{{ route('hero.create') }}">Crie sua anomalia</a>
                </div>
            </nav>
        </header>

        <main class="site-main">
            @yield('content')
        </main>

        <footer class="site-footer">
            <span>Projeto acadêmico Anomalias ETEC Lab</span>
            <span>Blade, HTML5, CSS3 e JavaScript vanilla</span>
        </footer>

        <script>
            {!! file_get_contents(resource_path('js/app.js')) !!}
        </script>
        @stack('scripts')
    </body>
</html>
