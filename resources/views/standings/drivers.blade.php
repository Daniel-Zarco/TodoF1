@extends('layouts.app')
@section('title', 'Driver Standings ' . $season)

@section('content')
<div class="page-header">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <h1 class="section-title">Driver Standings</h1>
                <p class="section-subtitle">{{ $season }} Formula 1 World Drivers' Championship</p>
            </div>

            {{-- Season selector --}}
            <form method="GET" action="{{ route('standings.drivers') }}" class="flex items-center gap-2">
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
        <div class="card p-6 mb-8 border-yellow-500/20 bg-yellow-500/3 flex flex-col sm:flex-row gap-5 items-center">
            <div class="font-oswald text-7xl font-bold text-yellow-400/30 select-none">1</div>

            @if($leader->photo_url)
                <img src="{{ $leader->photo_url }}" alt="{{ $leader->name }}"
                     class="w-20 h-20 rounded-full object-cover object-top ring-2 ring-yellow-500/40">
            @else
                <div class="w-20 h-20 rounded-full bg-yellow-500/20 flex items-center justify-center font-oswald text-2xl font-bold text-yellow-400">
                    {{ strtoupper(substr($leader->name, 0, 2)) }}
                </div>
            @endif

            <div class="flex-1 text-center sm:text-left">
                <div class="text-xs text-yellow-400/60 uppercase tracking-widest font-semibold mb-1">Championship Leader</div>
                <a href="{{ route('drivers.show', $leader) }}" class="font-oswald text-3xl font-bold text-white hover:text-yellow-400 transition-colors">
                    {{ $leader->name }}
                </a>
                <p class="text-white/40 text-sm mt-1">{{ $leader->team?->name ?? '—' }}</p>
            </div>

            <div class="text-center">
                <div class="font-oswald text-5xl font-bold text-yellow-400">{{ number_format($leader->season_points ?? 0, 1) }}</div>
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
                        <th>Driver</th>
                        <th class="hidden sm:table-cell">Team</th>
                        <th class="hidden md:table-cell text-center">Nationality</th>
                        <th class="text-right pr-6">Points</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($standings as $i => $driver)
                        @php $pos = $i + 1; @endphp
                        <tr class="{{ $pos <= 3 ? 'bg-yellow-500/3' : '' }}">
                            <td class="text-center">
                                <span class="font-oswald text-xl font-bold
                                    {{ $pos === 1 ? 'text-yellow-400' : ($pos === 2 ? 'text-slate-400' : ($pos === 3 ? 'text-orange-500' : 'text-white/20')) }}">
                                    {{ $pos }}
                                </span>
                            </td>
                            <td>
                                <div class="flex items-center gap-3">
                                    @if($driver->photo_url)
                                        <img src="{{ $driver->photo_url }}" alt="{{ $driver->name }}"
                                             class="w-9 h-9 rounded-full object-cover object-top flex-shrink-0">
                                    @else
                                        <div class="w-9 h-9 rounded-full bg-white/5 flex items-center justify-center text-white/30 text-xs font-bold flex-shrink-0">
                                            {{ strtoupper(substr($driver->name, 0, 2)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <a href="{{ route('drivers.show', $driver) }}"
                                           class="font-medium text-white hover:text-f1-red transition-colors text-sm">
                                            {{ $driver->name }}
                                        </a>
                                        @if($driver->number)
                                            <span class="text-white/20 text-xs ml-1">#{{ $driver->number }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="hidden sm:table-cell text-white/50 text-sm">
                                {{ $driver->team?->name ?? '—' }}
                            </td>
                            <td class="hidden md:table-cell text-center text-white/40 text-sm">
                                {{ $driver->nationality }}
                            </td>
                            <td class="text-right pr-6">
                                <span class="font-oswald text-2xl font-bold text-white">
                                    {{ number_format($driver->season_points ?? 0, 1) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-16 text-white/30">
                                No standings data available for {{ $season }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Link to constructor standings --}}
    <div class="mt-6 flex justify-end">
        <a href="{{ route('standings.teams') }}" class="btn-secondary">
            View Constructor Standings →
        </a>
    </div>
</div>
@endsection
