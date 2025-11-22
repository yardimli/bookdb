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
    
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-base-100 text-base-content min-h-screen flex flex-col">

<!-- Sticky Navbar -->
<div class="navbar bg-base-100 shadow-sm sticky top-0 z-50">
    <div class="flex-1">
        <a href="{{ route('dashboard') }}" class="btn btn-ghost text-xl text-primary font-bold">BookDB</a>
    </div>
    <div class="flex-none gap-2">
        <!-- Search Redirect -->
        <a href="{{ route('books.search') }}" class="btn btn-ghost btn-circle">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
        </a>
        
        <!-- Theme Toggle -->
        <label class="swap swap-rotate btn btn-ghost btn-circle">
            <input type="checkbox" id="theme-toggle" />
            <!-- sun icon -->
            <svg class="swap-on fill-current w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M5.64,17l-.71.71a1,1,0,0,0,0,1.41,1,1,0,0,0,1.41,0l.71-.71A1,1,0,0,0,5.64,17ZM5,12a1,1,0,0,0-1-1H3a1,1,0,0,0,0,2H4A1,1,0,0,0,5,12Zm7-7a1,1,0,0,0,1-1V3a1,1,0,0,0-2,0V4A1,1,0,0,0,12,5ZM5.64,7.05a1,1,0,0,0,.7.29,1,1,0,0,0,.71-.29,1,1,0,0,0,0-1.41l-.71-.71A1,1,0,0,0,5.64,4.93ZM12,21a1,1,0,0,0,1-1V19a1,1,0,0,0-2,0v1A1,1,0,0,0,12,21Zm7-9a1,1,0,0,0-1-1H19.5a1,1,0,0,0,0,2h1A1,1,0,0,0,19,12Zm2.121,2.828l-.707.707a1,1,0,0,0,1.414,1.414l.707-.707a1,1,0,0,0-1.414-1.414ZM12,7a5,5,0,1,0,5,5A5,5,0,0,0,12,7Z"/></svg>
            <!-- moon icon -->
            <svg class="swap-off fill-current w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M21.64,13a1,1,0,0,0-1.05-.14,8.05,8.05,0,0,1-3.37.73A8.15,8.15,0,0,1,9.08,5.49a8.59,8.59,0,0,1,.25-2A1,1,0,0,0,8,2.36,10.14,10.14,0,1,0,22,14.05,1,1,0,0,0,21.64,13Z"/></svg>
        </label>
        
        <!-- User Dropdown -->
        <div class="dropdown dropdown-end">
            <div tabindex="0" role="button" class="btn btn-ghost btn-circle avatar">
                <div class="w-10 rounded-full">
                    @if(Auth::user()->avatar)
                        <img src="{{ asset('storage/' . Auth::user()->avatar) }}" />
                    @else
                        <img src="https://ui-avatars.com/api/?name={{ Auth::user()->name }}" />
                    @endif
                </div>
            </div>
            <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-200 rounded-box w-52">
                <li><a href="{{ route('profile.edit') }}">Profile</a></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit">Logout</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</div>

<!-- Page Content -->
<main class="flex-1 p-6">
    {{ $slot }}
</main>

<!-- Vanilla JS Scripts -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Theme Controller
        const themeToggle = document.getElementById('theme-toggle');
        const html = document.querySelector('html');
        
        // Check local storage
        const currentTheme = localStorage.getItem('theme') || 'light';
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
