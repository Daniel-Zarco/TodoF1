@extends('layouts.app')
@section('title', 'Constructor Standings ' . $season)

@section('content')
<div class="page-header">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <h1 class="section-title">Constructor Standings</h1>
                <p class="section-subtitle">{{ $season }} Formula 1 World Constructors' Championship</p>
            </div>

            {{-- Season selector --}}
            <form method="GET" action="{{ route('standings.teams') }}" class="flex items-center gap-2">
                <select name="season" onchange="this.form.submit()" class="form-select w-32">
                    @foreach($seasons as $s)
                        <option value="{{ $s }}" {{ $s == $season ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    {{-- Championship leader highlight --}}
    @if($standings->isNotEmpty())
        @php $leader = $standings->first(); @endphp
        <div class="card p-6 mb-8 border-f1-red/20 bg-f1-red/5 flex flex-col sm:flex-row gap-5 items-center">
            <div class="font-oswald text-7xl font-bold text-f1-red/20 select-none">1</div>

            <div class="w-28 h-14 flex items-center justify-center flex-shrink-0 relative">
                <div class="absolute inset-0 opacity-10 blur-xl rounded-full"
                     style="background: radial-gradient(ellipse, rgba(255,255,255,0.15), transparent 70%);"></div>
                @if($leader->logo_url)
                    <img src="{{ $leader->logo_url }}" alt="{{ $leader->name }}" class="w-full h-full object-contain relative z-10">
                @else
                    <div class="relative z-10 font-oswald text-3xl font-black text-f1-red/40 tracking-tighter">{{ strtoupper(substr($leader->name, 0, 3)) }}</div>
                @endif
            </div>

            <div class="flex-1 text-center sm:text-left">
                <div class="text-xs text-f1-red/80 uppercase tracking-widest font-semibold mb-1">Championship Leader</div>
                <a href="{{ route('teams.show', $leader) }}" class="font-oswald text-3xl font-bold text-white hover:text-f1-red transition-colors">
                    {{ $leader->name }}
                </a>
                <p class="text-white/40 text-sm mt-1">{{ $leader->country }} · {{ $leader->power_unit ?? '—' }}</p>
            </div>

            <div class="text-center">
                <div class="font-oswald text-5xl font-bold text-f1-red">{{ number_format($leader->season_points ?? 0, 1) }}</div>
                <div class="text-xs text-white/30 uppercase tracking-widest mt-1">Points</div>
            </div>
        </div>
    @endif

    {{-- Full standings table --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="text-center w-12">Pos</th>
                        <th>Constructor</th>
                        <th class="hidden sm:table-cell">Country</th>
                        <th class="hidden md:table-cell">Power Unit</th>
                        <th class="hidden lg:table-cell text-center">Drivers</th>
                        <th class="text-right pr-6">Points</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($standings as $i => $team)
                        @php $pos = $i + 1; @endphp
                        <tr class="{{ $pos === 1 ? 'bg-white/5' : '' }}">
                            <td class="text-center">
                                <span class="font-oswald text-xl font-bold
                                    {{ $pos === 1 ? 'text-f1-red' : ($pos <= 3 ? 'text-white' : 'text-white/20') }}">
                                    {{ $pos }}
                                </span>
                            </td>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="w-14 h-8 flex items-center justify-center flex-shrink-0">
                                        <img src="{{ $team->logo_url }}" alt="{{ $team->name }}"
                                             class="w-full h-full object-contain opacity-80 group-hover:opacity-100 transition-opacity">
                                    </div>
                                    <a href="{{ route('teams.show', $team) }}"
                                       class="font-medium text-white hover:text-f1-red transition-colors text-sm">
                                        {{ $team->name }}
                                    </a>
                                </div>
                            </td>
                            <td class="hidden sm:table-cell text-white/50 text-sm">{{ $team->country }}</td>
                            <td class="hidden md:table-cell text-white/40 text-sm">{{ $team->power_unit ?? '—' }}</td>
                            <td class="hidden lg:table-cell text-center">
                                <div class="flex justify-center -space-x-2">
                                    @foreach($team->drivers->take(2) as $driver)
                                        @if($driver->photo_url)
                                            <img src="{{ $driver->photo_url }}" alt="{{ $driver->name }}"
                                                 class="w-7 h-7 rounded-full object-cover object-top ring-1 ring-f1-dark"
                                                 title="{{ $driver->name }}">
                                        @else
                                            <div class="w-7 h-7 rounded-full bg-white/10 flex items-center justify-center text-xs text-white/40 ring-1 ring-f1-dark"
                                                 title="{{ $driver->name }}">
                                                {{ strtoupper(substr($driver->name, 0, 1)) }}
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </td>
                            <td class="text-right pr-6">
                                <span class="font-oswald text-2xl font-bold text-white">
                                    {{ number_format($team->season_points ?? 0, 1) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-16 text-white/30">
                                No standings data available for {{ $season }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Link to driver standings --}}
    <div class="mt-6 flex justify-end">
        <a href="{{ route('standings.drivers') }}" class="btn-secondary">
            View Driver Standings →
        </a>
    </div>
</div>
@endsection
