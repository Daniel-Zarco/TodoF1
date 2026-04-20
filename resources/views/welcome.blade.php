<!DOCTYPE html>
<html lang="en" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="TodoF1 — The ultimate Formula 1 database. Explore drivers, teams, circuits and championship standings.">
    <title>TodoF1 — Formula 1 Data Hub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;900&family=Oswald:wght@600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-f1-dark font-inter text-white antialiased">

    {{-- Navbar --}}
    <nav class="fixed top-0 left-0 right-0 z-50 bg-f1-dark/80 backdrop-blur-md border-b border-white/5" x-data="{ open: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-9 h-9 rounded-lg bg-f1-red font-oswald font-bold text-white text-sm">F1</span>
                    <span class="font-oswald text-xl font-bold tracking-wide">TodoF1</span>
                </a>
                <div class="hidden md:flex items-center gap-6 text-sm text-white/60">
                    <a href="{{ route('drivers.index') }}" class="hover:text-white transition-colors">Drivers</a>
                    <a href="{{ route('teams.index') }}" class="hover:text-white transition-colors">Teams</a>
                    <a href="{{ route('circuits.index') }}" class="hover:text-white transition-colors">Circuits</a>
                    <a href="{{ route('standings.drivers') }}" class="hover:text-white transition-colors">Standings</a>
                </div>
                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn-primary text-sm">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm text-white/60 hover:text-white transition-colors">Login</a>
                        <a href="{{ route('register') }}" class="btn-primary text-sm">Get Started</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    {{-- HERO ─────────────────────────────────────────────────────────────────── --}}
    <section class="relative min-h-screen flex items-center overflow-hidden">
        {{-- Background gradient --}}
        <div class="absolute inset-0">
            <div class="absolute inset-0 bg-gradient-to-br from-f1-dark via-f1-dark to-[#1a0505]"></div>
            <div class="absolute top-0 right-0 w-96 h-96 bg-f1-red/10 rounded-full blur-3xl translate-x-1/2 -translate-y-1/2"></div>
            <div class="absolute bottom-0 left-0 w-64 h-64 bg-f1-red/5 rounded-full blur-3xl -translate-x-1/2 translate-y-1/2"></div>
            {{-- Speed lines decoration --}}
            <div class="absolute inset-0 opacity-5" style="background-image: repeating-linear-gradient(91deg, transparent, transparent 40px, #E10600 40px, #E10600 41px);"></div>
        </div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-24 pb-16">
            <div class="max-w-3xl">
                {{-- Season badge --}}
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-f1-red/10 border border-f1-red/20 text-f1-red text-xs font-semibold uppercase tracking-widest mb-8">
                    <span class="w-1.5 h-1.5 rounded-full bg-f1-red animate-pulse"></span>
                    2024 Season
                </div>

                <h1 class="font-oswald text-6xl sm:text-7xl lg:text-8xl font-bold leading-none mb-6">
                    THE ULTIMATE<br>
                    <span class="text-f1-red">FORMULA 1</span><br>
                    DATABASE
                </h1>

                <p class="text-lg text-white/50 max-w-xl mb-10 leading-relaxed">
                    Explore drivers, teams, circuits, and championship standings.
                    Your complete reference for everything Formula 1.
                </p>

                <div class="flex flex-wrap gap-4 mb-16">
                    <a href="{{ route('drivers.index') }}" class="btn-primary px-6 py-3 text-base">
                        Explore Drivers
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </a>
                    <a href="{{ route('standings.drivers') }}" class="btn-secondary px-6 py-3 text-base">
                        View Standings
                    </a>
                </div>

                {{-- Quick stats --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    @php
                        $heroStats = [
                            ['label' => 'Drivers',  'value' => \App\Models\Driver::count()],
                            ['label' => 'Teams',    'value' => \App\Models\Team::count()],
                            ['label' => 'Circuits', 'value' => \App\Models\Circuit::count()],
                            ['label' => 'Races',    'value' => \App\Models\GrandPrix::count()],
                        ];
                    @endphp
                    @foreach($heroStats as $stat)
                        <div class="card p-4 text-center">
                            <div class="font-oswald text-4xl font-bold text-f1-red">{{ $stat['value'] }}</div>
                            <div class="text-xs text-white/40 uppercase tracking-widest mt-1">{{ $stat['label'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Scroll indicator --}}
        <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 text-white/20 animate-bounce">
            <span class="text-xs uppercase tracking-widest">Scroll</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </div>
    </section>

    {{-- MODULES SECTION ───────────────────────────────────────────────────────── --}}
    <section class="py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-14">
                <h2 class="font-oswald text-4xl font-bold text-white uppercase tracking-wide">Explore the Grid</h2>
                <p class="text-white/40 mt-3 text-sm">Everything you need to know about Formula 1</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                {{-- Drivers --}}
                <a href="{{ route('drivers.index') }}" class="group card-hover p-6 flex flex-col gap-4">
                    <div class="w-12 h-12 rounded-xl bg-f1-red/10 flex items-center justify-center group-hover:bg-f1-red/20 transition-colors">
                        <svg class="w-6 h-6 text-f1-red" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-oswald text-xl font-bold text-white tracking-wide group-hover:text-f1-red transition-colors">Drivers</h3>
                        <p class="text-sm text-white/40 mt-1">Profiles, stats, and race history for every driver on the grid.</p>
                    </div>
                    <div class="mt-auto flex items-center gap-1 text-f1-red text-sm font-medium group-hover:gap-2 transition-all">
                        View drivers <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>

                {{-- Teams --}}
                <a href="{{ route('teams.index') }}" class="group card-hover p-6 flex flex-col gap-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-500/10 flex items-center justify-center group-hover:bg-blue-500/20 transition-colors">
                        <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/></svg>
                    </div>
                    <div>
                        <h3 class="font-oswald text-xl font-bold text-white tracking-wide group-hover:text-blue-400 transition-colors">Teams</h3>
                        <p class="text-sm text-white/40 mt-1">The 10 constructors competing for the championship trophy.</p>
                    </div>
                    <div class="mt-auto flex items-center gap-1 text-blue-400 text-sm font-medium group-hover:gap-2 transition-all">
                        View teams <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>

                {{-- Circuits --}}
                <a href="{{ route('circuits.index') }}" class="group card-hover p-6 flex flex-col gap-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-500/10 flex items-center justify-center group-hover:bg-emerald-500/20 transition-colors">
                        <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                    </div>
                    <div>
                        <h3 class="font-oswald text-xl font-bold text-white tracking-wide group-hover:text-emerald-400 transition-colors">Circuits</h3>
                        <p class="text-sm text-white/40 mt-1">Track specs, lap records and history of iconic F1 venues.</p>
                    </div>
                    <div class="mt-auto flex items-center gap-1 text-emerald-400 text-sm font-medium group-hover:gap-2 transition-all">
                        View circuits <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>

                {{-- Grand Prix --}}
                <a href="{{ route('grand-prix.index') }}" class="group card-hover p-6 flex flex-col gap-4">
                    <div class="w-12 h-12 rounded-xl bg-yellow-500/10 flex items-center justify-center group-hover:bg-yellow-500/20 transition-colors">
                        <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-oswald text-xl font-bold text-white tracking-wide group-hover:text-yellow-400 transition-colors">Grand Prix</h3>
                        <p class="text-sm text-white/40 mt-1">Race calendar, results and podiums across every season.</p>
                    </div>
                    <div class="mt-auto flex items-center gap-1 text-yellow-400 text-sm font-medium group-hover:gap-2 transition-all">
                        View races <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>
            </div>
        </div>
    </section>

    {{-- STANDINGS PREVIEW ─────────────────────────────────────────────────────── --}}
    <section class="py-24 bg-f1-card/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-14">
                <h2 class="font-oswald text-4xl font-bold text-white uppercase tracking-wide">2024 Standings</h2>
                <p class="text-white/40 mt-3 text-sm">Top 5 in the Drivers Championship</p>
            </div>

            @php
                $topDrivers = \App\Models\Driver::with('team')
                    ->withSum('raceResults as total_points', 'points')
                    ->orderByDesc('total_points')
                    ->limit(5)
                    ->get();
            @endphp

            <div class="max-w-2xl mx-auto space-y-3">
                @foreach($topDrivers as $i => $driver)
                    <a href="{{ route('drivers.show', $driver) }}" class="flex items-center gap-4 card-hover p-4 group">
                        <span class="w-8 text-center font-oswald text-2xl font-bold {{ $i === 0 ? 'text-yellow-400' : ($i === 1 ? 'text-white/50' : ($i === 2 ? 'text-orange-400/80' : 'text-white/20')) }}">
                            {{ $i + 1 }}
                        </span>

                        @if($driver->photo_url)
                            <img src="{{ $driver->photo_url }}" alt="{{ $driver->name }}" class="w-12 h-12 rounded-full object-cover object-top bg-white/5">
                        @else
                            <div class="w-12 h-12 rounded-full bg-f1-red/20 flex items-center justify-center text-f1-red font-oswald font-bold">
                                {{ strtoupper(substr($driver->name, 0, 2)) }}
                            </div>
                        @endif

                        <div class="flex-1 min-w-0">
                            <div class="font-semibold text-white group-hover:text-f1-red transition-colors truncate">{{ $driver->name }}</div>
                            <div class="text-xs text-white/40">{{ $driver->team?->name ?? '—' }}</div>
                        </div>

                        <div class="text-right">
                            <div class="font-oswald text-2xl font-bold text-white">{{ $driver->total_points ?? 0 }}</div>
                            <div class="text-xs text-white/30">pts</div>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="text-center mt-8">
                <a href="{{ route('standings.drivers') }}" class="btn-secondary">View Full Standings</a>
            </div>
        </div>
    </section>

    {{-- CTA ──────────────────────────────────────────────────────────────────────── --}}
    @guest
    <section class="py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-block card p-12 max-w-xl w-full">
                <div class="w-14 h-14 rounded-xl bg-f1-red mx-auto mb-6 flex items-center justify-center">
                    <span class="font-oswald font-bold text-white text-xl">F1</span>
                </div>
                <h2 class="font-oswald text-3xl font-bold text-white mb-3 uppercase tracking-wide">Join TodoF1</h2>
                <p class="text-white/40 text-sm mb-8">Create an account to track your favourite drivers and access the full database.</p>
                <a href="{{ route('register') }}" class="btn-primary px-8 py-3 text-base w-full justify-center">Create Free Account</a>
                <p class="text-white/30 text-xs mt-4">Already have an account? <a href="{{ route('login') }}" class="text-f1-red hover:underline">Sign in</a></p>
            </div>
        </div>
    </section>
    @endguest

    <footer class="border-t border-white/5 py-10">
        <div class="max-w-7xl mx-auto px-4 text-center text-white/30 text-sm">
            <p class="font-oswald text-white/50 font-semibold mb-1">TodoF1 v2</p>
            <p>Formula 1 Data Hub — Built with Laravel &amp; Tailwind CSS</p>
        </div>
    </footer>

</body>
</html>
