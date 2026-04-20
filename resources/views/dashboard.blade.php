@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="page-header">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="section-title">Dashboard</h1>
        <p class="section-subtitle">Welcome back, {{ auth()->user()->name }}.</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    {{-- Stats overview --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
        <div class="stat-card">
            <div class="stat-value text-f1-red">{{ $driverCount }}</div>
            <div class="stat-label">Drivers</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $teamCount }}</div>
            <div class="stat-label">Teams</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $circuitCount }}</div>
            <div class="stat-label">Circuits</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $gpCount }}</div>
            <div class="stat-label">Grand Prix</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Recent Results --}}
        <div class="lg:col-span-2 card p-6">
            <h2 class="font-oswald text-xl font-bold text-white uppercase tracking-wide mb-5">Recent Race Results</h2>
            @if($latestResults->isEmpty())
                <p class="text-white/40 text-sm">No race results yet.</p>
            @else
                <div class="space-y-4">
                    @foreach($latestResults as $gp)
                        <div class="border border-white/5 rounded-xl overflow-hidden">
                            <div class="flex items-center justify-between bg-white/3 px-4 py-2.5">
                                <div>
                                    <a href="{{ route('grand-prix.show', $gp) }}" class="font-semibold text-white hover:text-f1-red transition-colors text-sm">{{ $gp->name }}</a>
                                    <span class="text-white/30 text-xs ml-2">{{ $gp->date->format('d M Y') }}</span>
                                </div>
                                <span class="badge-green">{{ ucfirst($gp->status) }}</span>
                            </div>
                            @foreach($gp->raceResults->take(3) as $result)
                                <div class="flex items-center gap-3 px-4 py-2 border-t border-white/5">
                                    <span class="font-oswald font-bold text-lg w-6 text-center {{ $result->position == 1 ? 'text-yellow-400' : ($result->position == 2 ? 'text-white/50' : 'text-orange-400/80') }}">
                                        {{ $result->position }}
                                    </span>
                                    <span class="flex-1 text-sm text-white/80">{{ $result->driver->name }}</span>
                                    <span class="text-xs text-white/40">{{ $result->team->name }}</span>
                                    <span class="font-oswald font-bold text-sm text-white">{{ $result->points }}pts</span>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Quick links --}}
        <div class="space-y-4">
            <div class="card p-6">
                <h2 class="font-oswald text-xl font-bold text-white uppercase tracking-wide mb-4">Quick Links</h2>
                <div class="space-y-2">
                    <a href="{{ route('drivers.index') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-white/5 transition-colors group">
                        <span class="text-f1-red">→</span>
                        <span class="text-sm text-white/70 group-hover:text-white transition-colors">Browse Drivers</span>
                    </a>
                    <a href="{{ route('teams.index') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-white/5 transition-colors group">
                        <span class="text-f1-red">→</span>
                        <span class="text-sm text-white/70 group-hover:text-white transition-colors">Browse Teams</span>
                    </a>
                    <a href="{{ route('circuits.index') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-white/5 transition-colors group">
                        <span class="text-f1-red">→</span>
                        <span class="text-sm text-white/70 group-hover:text-white transition-colors">Browse Circuits</span>
                    </a>
                    <a href="{{ route('standings.drivers') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-white/5 transition-colors group">
                        <span class="text-f1-red">→</span>
                        <span class="text-sm text-white/70 group-hover:text-white transition-colors">Driver Standings</span>
                    </a>
                    <a href="{{ route('standings.teams') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-white/5 transition-colors group">
                        <span class="text-f1-red">→</span>
                        <span class="text-sm text-white/70 group-hover:text-white transition-colors">Constructor Standings</span>
                    </a>
                    @if(auth()->user()->isAdmin())
                        <div class="border-t border-white/5 my-2 pt-2">
                            <p class="text-xs text-white/30 uppercase tracking-widest mb-2 px-3">Admin</p>
                            <a href="{{ route('drivers.create') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-f1-red/10 transition-colors group">
                                <span class="text-f1-red">+</span>
                                <span class="text-sm text-white/70 group-hover:text-white transition-colors">Add Driver</span>
                            </a>
                            <a href="{{ route('teams.create') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-f1-red/10 transition-colors group">
                                <span class="text-f1-red">+</span>
                                <span class="text-sm text-white/70 group-hover:text-white transition-colors">Add Team</span>
                            </a>
                            <a href="{{ route('circuits.create') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-f1-red/10 transition-colors group">
                                <span class="text-f1-red">+</span>
                                <span class="text-sm text-white/70 group-hover:text-white transition-colors">Add Circuit</span>
                            </a>
                            <a href="{{ route('grand-prix.create') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-f1-red/10 transition-colors group">
                                <span class="text-f1-red">+</span>
                                <span class="text-sm text-white/70 group-hover:text-white transition-colors">Add Grand Prix</span>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
