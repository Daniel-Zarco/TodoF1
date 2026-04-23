<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="TodoF1 — Formula 1 Database: drivers, teams, circuits and standings.">

    <title>@yield('title', 'TodoF1') — Formula 1 Data Hub</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;900&family=Oswald:wght@600;700&display=swap" rel="stylesheet">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-f1-black font-inter text-white antialiased" x-data="{ mobileOpen: false }">

    <!-- Navigation -->
    <nav class="sticky top-0 z-50 bg-f1-black/95 backdrop-blur-md border-b border-f1-border">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">

                <!-- Logo -->
                <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                    <span class="flex items-center justify-center w-8 h-8 rounded-sm bg-f1-red font-oswald font-bold text-white text-xs tracking-wider group-hover:bg-red-700 transition-colors">F1</span>
                    <span class="font-oswald text-xl font-bold text-white tracking-wide">TodoF1</span>
                    <span class="hidden sm:block text-xs text-white/40 font-inter font-normal">v2</span>
                </a>

                <!-- Desktop Nav -->
                <div class="hidden md:flex items-center gap-1">
                    <a href="{{ route('drivers.index') }}" class="nav-link {{ request()->routeIs('drivers.*') ? 'nav-link-active' : '' }}">Drivers</a>
                    <a href="{{ route('teams.index') }}" class="nav-link {{ request()->routeIs('teams.*') ? 'nav-link-active' : '' }}">Teams</a>
                    <a href="{{ route('circuits.index') }}" class="nav-link {{ request()->routeIs('circuits.*') ? 'nav-link-active' : '' }}">Circuits</a>
                    <a href="{{ route('grand-prix.index') }}" class="nav-link {{ request()->routeIs('grand-prix.*') ? 'nav-link-active' : '' }}">Grand Prix</a>

                    <!-- Standings dropdown -->
                    <div class="relative" x-data="{ open: false }" @mouseenter="open=true" @mouseleave="open=false">
                        <button class="nav-link flex items-center gap-1 {{ request()->routeIs('standings.*') ? 'nav-link-active' : '' }}">
                            Standings
                            <svg class="w-3 h-3 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-transition class="absolute top-full left-0 mt-1 w-48 rounded-md bg-f1-card border border-f1-border shadow-2xl py-1">
                            <a href="{{ route('standings.drivers') }}" class="dropdown-item">Drivers Championship</a>
                            <a href="{{ route('standings.teams') }}" class="dropdown-item">Constructors Championship</a>
                        </div>
                    </div>
                </div>

                <!-- Auth area -->
                <div class="hidden md:flex items-center gap-3">
                    @auth
                        <div class="relative" x-data="{ open: false }" @click.outside="open=false">
                            <button @click="open=!open" class="flex items-center gap-2 text-sm text-white/70 hover:text-white transition-colors">
                                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-f1-red/20 text-f1-red font-semibold text-xs">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                                </span>
                                <span>{{ auth()->user()->name }}</span>
                                @if(auth()->user()->isAdmin())
                                    <span class="px-1.5 py-0.5 text-xs rounded bg-f1-red/20 text-f1-red font-medium">Admin</span>
                                @endif
                            </button>
                            <div x-show="open" x-transition class="absolute right-0 top-full mt-2 w-48 rounded-md bg-f1-card border border-f1-border shadow-2xl py-1">
                                <a href="{{ route('dashboard') }}" class="dropdown-item">Dashboard</a>
                                <a href="{{ route('profile.edit') }}" class="dropdown-item">Profile</a>
                                <div class="border-t border-white/10 my-1"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item w-full text-left text-red-400">Logout</button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-sm text-white/60 hover:text-white transition-colors">Login</a>
                        <a href="{{ route('register') }}" class="btn-primary text-sm px-4 py-2">Register</a>
                    @endauth
                </div>

                <!-- Mobile menu button -->
                <button @click="mobileOpen=!mobileOpen" class="md:hidden p-2 text-white/60 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path x-show="mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile menu -->
        <div x-show="mobileOpen" x-transition class="md:hidden bg-f1-black border-t border-f1-border">
            <div class="px-4 py-3 space-y-1">
                <a href="{{ route('drivers.index') }}" class="mobile-nav-link">Drivers</a>
                <a href="{{ route('teams.index') }}" class="mobile-nav-link">Teams</a>
                <a href="{{ route('circuits.index') }}" class="mobile-nav-link">Circuits</a>
                <a href="{{ route('grand-prix.index') }}" class="mobile-nav-link">Grand Prix</a>
                <a href="{{ route('standings.drivers') }}" class="mobile-nav-link">Driver Standings</a>
                <a href="{{ route('standings.teams') }}" class="mobile-nav-link">Constructor Standings</a>
                <div class="border-t border-white/10 pt-2 mt-2">
                    @auth
                        <a href="{{ route('dashboard') }}" class="mobile-nav-link">Dashboard</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="mobile-nav-link w-full text-left text-red-400">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="mobile-nav-link">Login</a>
                        <a href="{{ route('register') }}" class="mobile-nav-link">Register</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 4000)"
             class="fixed top-20 right-4 z-50 px-5 py-3 rounded-lg bg-green-500/20 border border-green-500/30 text-green-400 text-sm shadow-xl">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 5000)"
             class="fixed top-20 right-4 z-50 px-5 py-3 rounded-lg bg-f1-red/20 border border-f1-red/30 text-red-400 text-sm shadow-xl">
            {{ session('error') }}
        </div>
    @endif

    <!-- Main content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="border-t border-f1-border mt-20 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white/30 text-sm">
            <div class="flex items-center justify-center gap-2 mb-2">
                <span class="flex items-center justify-center w-6 h-6 rounded bg-f1-red font-oswald font-bold text-white text-xs">F1</span>
                <span class="font-oswald text-white/50 font-semibold">TodoF1 v2</span>
            </div>
            <p>Formula 1 Data Hub — Built with Laravel &amp; Tailwind CSS</p>
            <p class="mt-1">For educational and portfolio purposes only. F1 data is illustrative.</p>
        </div>
    </footer>

</body>
</html>
