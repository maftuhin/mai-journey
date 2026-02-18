<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mai Journey</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    @stack('head')
</head>
<body>
    <header>
        <h1 class="logo">
            Mai Journey
            <span>Stories from the heart</span>
        </h1>
        @auth
            <button type="button" class="site-menu-toggle" aria-label="Toggle menu" aria-controls="siteNav" aria-expanded="false">
                ☰ Menu
            </button>
            <nav class="site-nav" id="siteNav">
                <a href="{{ route('posts.index') }}" class="{{ request()->routeIs('posts.index', 'posts.show') ? 'active' : '' }}">Stories</a>
                <a href="{{ route('gallery.index') }}" class="{{ request()->routeIs('gallery.index') ? 'active' : '' }}">Gallery</a>
                <a href="{{ route('posts.editor') }}" class="write-link {{ request()->routeIs('posts.editor', 'posts.editor.edit') ? 'active' : '' }}">Write Story</a>
                <a href="{{ route('gallery.admin') }}" class="upload-link {{ request()->routeIs('gallery.admin') ? 'active' : '' }}">Upload Gallery</a>
                <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="site-menu-logout">Logout</button>
                </form>
            </nav>
        @endauth
    </header>
    <main>
        @if (session('success'))
            <div class="success-message" style="display: block;">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="error-message" style="display: block;">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif
        @yield('content')
    </main>
    <footer>
        <p>Made with <span class="heart">♥</span> Mai Journey © {{ date('Y') }}</p>
    </footer>
    @auth
        <script>
            (function () {
                const toggle = document.querySelector('.site-menu-toggle');
                const nav = document.getElementById('siteNav');
                if (!toggle || !nav) return;
                toggle.addEventListener('click', function () {
                    const isOpen = nav.classList.toggle('is-open');
                    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                });
            })();
        </script>
    @endauth
    @stack('scripts')
</body>
</html>
