@extends('layouts.app')
@section('title', $grandPrix->name)
@section('content')
<div class="page-header">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row gap-4 items-start">
            <div class="flex-1">
                <div class="text-f1-red text-xs font-semibold uppercase tracking-widest mb-1">Round {{ $grandPrix->round_number }} · {{ $grandPrix->season }}</div>
                <h1 class="section-title">{{ $grandPrix->name }}</h1>
                <p class="section-subtitle">{{ $grandPrix->circuit->name }} · {{ $grandPrix->circuit->country }} · {{ $grandPrix->date->format('d M Y') }}</p>
            </div>
            <div class="flex gap-2">
                <span class="badge-{{ $grandPrix->status === 'completed' ? 'green' : ($grandPrix->status === 'cancelled' ? 'red' : 'gray') }}">{{ ucfirst($grandPrix->status) }}</span>
                @auth @if(auth()->user()->isAdmin())
                    <a href="{{ route('grand-prix.edit', $grandPrix) }}" class="btn-secondary">Edit</a>
                    <form method="POST" action="{{ route('grand-prix.destroy', $grandPrix) }}" onsubmit="return confirm('Delete?')">
                        @csrf @method('DELETE')
                        <button class="btn-danger">Delete</button>
                    </form>
                @endif @endauth
            </div>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    @if($grandPrix->raceResults->isEmpty())
        <div class="card p-10 text-center text-white/30">No race results available.</div>
    @else
        {{-- Podium highlight --}}
        @php $podium = $grandPrix->raceResults->whereIn('position', [1,2,3])->sortBy('position'); @endphp
        @if($podium->count() >= 1)
        <div class="grid grid-cols-3 gap-4 mb-8 max-w-xl mx-auto">
            @foreach([2,1,3] as $pos)
                @php $r = $podium->firstWhere('position', $pos); @endphp
                @if($r)
                <div class="card p-4 text-center {{ $pos === 1 ? 'border-yellow-500/30 bg-yellow-500/5 row-start-1' : '' }} flex flex-col items-center gap-2 {{ $pos === 1 ? '' : 'mt-6' }}">
                    <div class="font-oswald text-4xl font-bold {{ $pos === 1 ? 'text-yellow-400' : ($pos === 2 ? 'text-white/50' : 'text-orange-400/80') }}">{{ $pos }}</div>
                    @if($r->driver->photo_url)
                        <img src="{{ $r->driver->photo_url }}" class="w-14 h-14 rounded-full object-cover object-top">
                    @endif
                    <div class="text-sm font-semibold text-white">{{ $r->driver->name }}</div>
                    <div class="text-xs text-white/40">{{ $r->team->name }}</div>
                    <div class="font-oswald text-xl font-bold text-f1-red">{{ $r->points }}pts</div>
                </div>
                @endif
            @endforeach
        </div>
        @endif

        {{-- Full results table --}}
        <div class="card overflow-hidden">
            <div class="p-5 border-b border-white/5"><h2 class="font-oswald text-lg font-bold text-white uppercase">Full Race Results</h2></div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="text-center">Pos</th>
                            <th>Driver</th>
                            <th>Team</th>
                            <th class="text-center">Pts</th>
                            <th class="text-center">Time</th>
                            <th class="text-center">FL</th>
                            <th class="text-center">Pole</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($grandPrix->raceResults as $result)
                            <tr class="{{ $result->position <= 3 ? 'bg-yellow-500/3' : '' }}">
                                <td class="text-center font-oswald text-lg font-bold {{ $result->position == 1 ? 'text-yellow-400' : ($result->position == 2 ? 'text-white/50' : ($result->position == 3 ? 'text-orange-400/80' : 'text-white/60')) }}">
                                    @if($result->dnf) <span class="badge-red text-xs">DNF</span> @else {{ $result->position ?? '—' }} @endif
                                </td>
                                <td>
                                    <a href="{{ route('drivers.show', $result->driver) }}" class="font-medium text-white hover:text-f1-red transition-colors">{{ $result->driver->name }}</a>
                                </td>
                                <td class="text-white/50">{{ $result->team->name }}</td>
                                <td class="text-center font-semibold text-white">{{ $result->points }}</td>
                                <td class="text-center font-mono text-xs text-white/50">{{ $result->total_time ?? '—' }}</td>
                                <td class="text-center">{{ $result->fastest_lap ? '⚡' : '' }}</td>
                                <td class="text-center">{{ $result->pole_position ? 'P' : '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="mt-6"><a href="{{ route('grand-prix.index') }}" class="text-white/40 hover:text-white text-sm transition-colors">← Back to Grand Prix</a></div>
</div>
@endsection
