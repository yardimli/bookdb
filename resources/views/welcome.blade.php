<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'BookDB') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    
    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-base-100 text-base-content min-h-screen flex flex-col">

<!-- Sticky Navbar -->
<div class="navbar bg-base-100/95 backdrop-blur supports-[backdrop-filter]:bg-base-100/60 sticky top-0 z-50 border-b border-base-200 px-4">
    <div class="navbar-start">
        <!-- Mobile Dropdown -->
        <div class="dropdown">
            <div tabindex="0" role="button" class="btn btn-ghost lg:hidden">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16" /></svg>
            </div>
            <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-52">
                @if (Route::has('login'))
                    @auth
                        <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                    @else
                        <li><a href="{{ route('login') }}">Log in</a></li>
                        @if (Route::has('register'))
                            <li><a href="{{ route('register') }}">Sign up</a></li>
                        @endif
                    @endauth
                @endif
            </ul>
        </div>
        <!-- Logo -->
        <a class="btn btn-ghost text-xl font-bold text-primary">BookDB</a>
    </div>
    
    <!-- Navbar End: Theme Toggle + Auth Buttons -->
    <div class="navbar-end gap-3">
        
        <!-- Theme Toggle -->
        <label class="swap swap-rotate btn btn-ghost btn-circle btn-sm">
            <input type="checkbox" id="theme-toggle" />
            <!-- sun icon -->
            <svg class="swap-on fill-current w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M5.64,17l-.71.71a1,1,0,0,0,0,1.41,1,1,0,0,0,1.41,0l.71-.71A1,1,0,0,0,5.64,17ZM5,12a1,1,0,0,0-1-1H3a1,1,0,0,0,0,2H4A1,1,0,0,0,5,12Zm7-7a1,1,0,0,0,1-1V3a1,1,0,0,0-2,0V4A1,1,0,0,0,12,5ZM5.64,7.05a1,1,0,0,0,.7.29,1,1,0,0,0,.71-.29,1,1,0,0,0,0-1.41l-.71-.71A1,1,0,0,0,5.64,4.93ZM12,21a1,1,0,0,0,1-1V19a1,1,0,0,0-2,0v1A1,1,0,0,0,12,21Zm7-9a1,1,0,0,0-1-1H19.5a1,1,0,0,0,0,2h1A1,1,0,0,0,19,12Zm2.121,2.828l-.707.707a1,1,0,0,0,1.414,1.414l.707-.707a1,1,0,0,0-1.414-1.414ZM12,7a5,5,0,1,0,5,5A5,5,0,0,0,12,7Z"/></svg>
            <!-- moon icon -->
            <svg class="swap-off fill-current w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M21.64,13a1,1,0,0,0-1.05-.14,8.05,8.05,0,0,1-3.37.73A8.15,8.15,0,0,1,9.08,5.49a8.59,8.59,0,0,1,.25-2A1,1,0,0,0,8,2.36,10.14,10.14,0,1,0,22,14.05,1,1,0,0,0,21.64,13Z"/></svg>
        </label>
        
        <!-- Desktop Auth Buttons -->
        @if (Route::has('login'))
            <div class="md:flex gap-2">
                @auth
                    <a href="{{ url('/dashboard') }}" class="btn btn-primary btn-sm">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-ghost btn-sm">Log in</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Sign up</a>
                    @endif
                @endauth
            </div>
        @endif
    </div>
</div>

<!-- Hero Section -->
<div class="hero bg-base-200 min-h-[80vh]">
    <div class="hero-content text-center">
        <div class="max-w-md">
            <h1 class="text-5xl font-bold">Organize Your Reading Life</h1>
            <p class="py-6">
                BookDB is the simplest way to track your book series. Search for books, build your collections, and never lose your place again.
            </p>
            @if (Route::has('register'))
                <a href="{{ route('register') }}" class="btn btn-primary btn-lg">Get Started</a>
            @endif
            
            <div class="mt-8 flex justify-center gap-4 opacity-50">
                <div class="w-12 h-16 bg-neutral rounded animate-pulse"></div>
                <div class="w-12 h-16 bg-neutral rounded animate-pulse delay-75"></div>
                <div class="w-12 h-16 bg-neutral rounded animate-pulse delay-150"></div>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="footer footer-center p-4 bg-base-300 text-base-content mt-auto">
    <aside>
        <p>Copyright © {{ date('Y') }} - All right reserved by BookDB SaaS</p>
    </aside>
</footer>

<!-- Theme Logic Script -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const themeToggle = document.getElementById('theme-toggle');
        const html = document.querySelector('html');
        
        // Check local storage or system preference
        const savedTheme = localStorage.getItem('theme');
        const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const currentTheme = savedTheme || (systemDark ? 'dark' : 'light');
        
        // Apply theme
        html.setAttribute('data-theme', currentTheme);
        themeToggle.checked = currentTheme === 'dark';
        
        // Toggle Listener
        themeToggle.addEventListener('change', (e) => {
            const theme = e.target.checked ? 'dark' : 'light';
            html.setAttribute('data-theme', theme);
            localStorage.setItem('theme', theme);
        });
    });
</script>
</body>
</html>
