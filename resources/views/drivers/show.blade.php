@extends('layouts.app')
@section('title', $driver->name)

@section('content')
<div class="page-header">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row gap-6 items-start">
            {{-- Photo --}}
            <div class="w-28 h-28 rounded-2xl overflow-hidden bg-f1-card border border-white/10 flex-shrink-0">
                @if($driver->photo_url)
                    <img src="{{ $driver->photo_url }}" alt="{{ $driver->name }}" class="w-full h-full object-cover object-top">
                @else
                    <div class="w-full h-full flex items-center justify-center">
                        <span class="font-oswald text-3xl font-bold text-white/20">{{ strtoupper(substr($driver->name, 0, 2)) }}</span>
                    </div>
                @endif
            </div>

            <div class="flex-1">
                <div class="flex flex-wrap items-center gap-3 mb-2">
                    @if($driver->number)
                        <span class="font-oswald text-3xl font-bold text-f1-red opacity-60">#{{ $driver->number }}</span>
                    @endif
                    @if(!$driver->is_active)
                        <span class="badge-gray">Retired</span>
                    @else
                        <span class="badge-green">Active</span>
                    @endif
                </div>
                <h1 class="section-title">{{ $driver->name }}</h1>
                <p class="section-subtitle">{{ $driver->nationality }} · {{ $driver->currentTeam()?->name ?? 'No team' }}</p>
                @if($driver->date_of_birth)
                    <p class="text-white/30 text-sm mt-1">Born {{ $driver->date_of_birth->format('d M Y') }} · Age {{ $driver->age }}</p>
                @endif
            </div>

            @auth
                @if(auth()->user()->isAdmin())
                    <div class="flex gap-2 flex-shrink-0">
                        <a href="{{ route('drivers.edit', $driver) }}" class="btn-secondary">Edit</a>
                        <form method="POST" action="{{ route('drivers.destroy', $driver) }}" onsubmit="return confirm('Delete {{ $driver->name }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-danger">Delete</button>
                        </form>
                    </div>
                @endif
            @endauth
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left: Stats + Bio --}}
        <div class="space-y-5">
            {{-- Career stats --}}
            <div class="card p-6">
                <h2 class="font-oswald text-lg font-bold text-white uppercase tracking-wide mb-4">Career Stats</h2>
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-white/3 rounded-lg p-3 text-center">
                        <div class="font-oswald text-3xl font-bold text-f1-red">{{ $totalPoints }}</div>
                        <div class="text-xs text-white/40 uppercase tracking-widest mt-1">Points</div>
                    </div>
                    <div class="bg-white/3 rounded-lg p-3 text-center">
                        <div class="font-oswald text-3xl font-bold text-white">{{ $wins }}</div>
                        <div class="text-xs text-white/40 uppercase tracking-widest mt-1">Wins</div>
                    </div>
                    <div class="bg-white/3 rounded-lg p-3 text-center">
                        <div class="font-oswald text-3xl font-bold text-white">{{ $podiums }}</div>
                        <div class="text-xs text-white/40 uppercase tracking-widest mt-1">Podiums</div>
                    </div>
                    <div class="bg-white/3 rounded-lg p-3 text-center">
                        <div class="font-oswald text-3xl font-bold text-white">{{ $poles }}</div>
                        <div class="text-xs text-white/40 uppercase tracking-widest mt-1">Poles</div>
                    </div>
                </div>
            </div>

            {{-- Bio --}}
            @if($driver->bio)
            <div class="card p-6">
                <h2 class="font-oswald text-lg font-bold text-white uppercase tracking-wide mb-3">About</h2>
                <p class="text-sm text-white/60 leading-relaxed">{{ $driver->bio }}</p>
            </div>
            @endif

            {{-- Team --}}
            @if($driver->currentTeam())
                @php $currentTeam = $driver->currentTeam(); @endphp
            <div class="card p-6">
                <h2 class="font-oswald text-lg font-bold text-white uppercase tracking-wide mb-3">Current Team</h2>
                <a href="{{ route('teams.show', $currentTeam) }}" class="flex items-center gap-3 hover:text-f1-red transition-colors group">
                    @if($currentTeam->logo_url)
                        <img src="{{ $currentTeam->logo_url }}" alt="{{ $currentTeam->name }}" class="w-10 h-10 object-contain">
                    @endif
                    <div>
                        <div class="font-semibold text-white group-hover:text-f1-red transition-colors">{{ $currentTeam->name }}</div>
                        <div class="text-xs text-white/40">{{ $currentTeam->country }}</div>
                    </div>
                </a>
            </div>
            @endif
        </div>

        {{-- Right: Race results --}}
        <div class="lg:col-span-2 card">
            <div class="p-6 border-b border-white/5">
                <h2 class="font-oswald text-lg font-bold text-white uppercase tracking-wide">Race Results</h2>
            </div>
            @if($driver->raceResults->isEmpty())
                <div class="p-6 text-white/30 text-sm">No race results on record.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Race</th>
                                <th class="text-center">Pos</th>
                                <th class="text-center">Pts</th>
                                <th class="text-center">FL</th>
                                <th class="text-center">Pole</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($driver->raceResults->sortByDesc(fn($r) => $r->grandPrix->date) as $result)
                                <tr>
                                    <td>
                                        <a href="{{ route('grand-prix.show', $result->grandPrix) }}" class="text-white hover:text-f1-red transition-colors">
                                            {{ $result->grandPrix->name }}
                                        </a>
                                        <span class="text-white/30 text-xs ml-1">{{ $result->grandPrix->season }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($result->dnf)
                                            <span class="badge-red">DNF</span>
                                        @else
                                            <span class="{{ $result->position === 1 ? 'font-bold text-f1-red' : ($result->position <= 3 ? 'font-bold text-white' : '') }}">{{ $result->position ?? '—' }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center font-semibold">{{ $result->points }}</td>
                                    <td class="text-center">{{ $result->fastest_lap ? '⚡' : '—' }}</td>
                                    <td class="text-center">{{ $result->pole_position ? 'P' : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>

    <div class="mt-6">
        <a href="{{ route('drivers.index') }}" class="text-white/40 hover:text-white text-sm transition-colors">← Back to Drivers</a>
    </div>
</div>
@endsection
