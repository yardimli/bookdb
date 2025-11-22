<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ config('app.name', 'BookDB') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-base-content antialiased bg-base-200 min-h-screen flex flex-col">

<!-- Sticky Navbar (Consistent with Welcome Page) -->
<div class="navbar bg-base-100/95 backdrop-blur supports-[backdrop-filter]:bg-base-100/60 sticky top-0 z-50 border-b border-base-200 px-4">
    <div class="navbar-start">
        <a href="/" class="btn btn-ghost text-xl font-bold text-primary">BookDB</a>
    </div>
    <div class="navbar-end gap-2">
        <!-- Theme Toggle -->
        <label class="swap swap-rotate btn btn-ghost btn-circle btn-sm">
            <input type="checkbox" id="theme-toggle" />
            <!-- sun icon -->
            <svg class="swap-on fill-current w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M5.64,17l-.71.71a1,1,0,0,0,0,1.41,1,1,0,0,0,1.41,0l.71-.71A1,1,0,0,0,5.64,17ZM5,12a1,1,0,0,0-1-1H3a1,1,0,0,0,0,2H4A1,1,0,0,0,5,12Zm7-7a1,1,0,0,0,1-1V3a1,1,0,0,0-2,0V4A1,1,0,0,0,12,5ZM5.64,7.05a1,1,0,0,0,.7.29,1,1,0,0,0,.71-.29,1,1,0,0,0,0-1.41l-.71-.71A1,1,0,0,0,5.64,4.93ZM12,21a1,1,0,0,0,1-1V19a1,1,0,0,0-2,0v1A1,1,0,0,0,12,21Zm7-9a1,1,0,0,0-1-1H19.5a1,1,0,0,0,0,2h1A1,1,0,0,0,19,12Zm2.121,2.828l-.707.707a1,1,0,0,0,1.414,1.414l.707-.707a1,1,0,0,0-1.414-1.414ZM12,7a5,5,0,1,0,5,5A5,5,0,0,0,12,7Z"/></svg>
            <!-- moon icon -->
            <svg class="swap-off fill-current w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M21.64,13a1,1,0,0,0-1.05-.14,8.05,8.05,0,0,1-3.37.73A8.15,8.15,0,0,1,9.08,5.49a8.59,8.59,0,0,1,.25-2A1,1,0,0,0,8,2.36,10.14,10.14,0,1,0,22,14.05,1,1,0,0,0,21.64,13Z"/></svg>
        </label>
        
        <!-- Navigation Links -->
        @if (Route::has('login'))
            @auth
                <a href="{{ url('/dashboard') }}" class="btn btn-sm btn-primary">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="btn btn-sm btn-ghost {{ request()->routeIs('login') ? 'btn-active' : '' }}">Log in</a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="btn btn-sm btn-ghost {{ request()->routeIs('register') ? 'btn-active' : '' }}">Sign up</a>
                @endif
            @endauth
        @endif
    </div>
</div>

<!-- Main Auth Card Content -->
<div class="flex-1 flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-base-200">
    <div class="w-full sm:max-w-md mt-6 px-6 py-8 bg-base-100 shadow-xl overflow-hidden sm:rounded-lg border border-base-300">
        <!-- Logo in Card (Optional) -->
        <div class="flex justify-center mb-6">
            <h2 class="text-2xl font-bold text-primary">
                {{ request()->routeIs('register') ? 'Create Account' : 'Welcome Back' }}
            </h2>
        </div>
        
        {{ $slot }}
    </div>
</div>

<!-- JS for Theme (Same as Welcome) -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const themeToggle = document.getElementById('theme-toggle');
        const html = document.querySelector('html');
        const savedTheme = localStorage.getItem('theme');
        const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const currentTheme = savedTheme || (systemDark ? 'dark' : 'light');
        
        html.setAttribute('data-theme', currentTheme);
        themeToggle.checked = currentTheme === 'dark';
        
        themeToggle.addEventListener('change', (e) => {
            const theme = e.target.checked ? 'dark' : 'light';
            html.setAttribute('data-theme', theme);
            localStorage.setItem('theme', theme);
        });
    });
</script>
</body>
</html>
