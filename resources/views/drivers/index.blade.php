@extends('layouts.app')
@section('title', 'Drivers')

@section('content')
<div class="page-header">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="section-title">Drivers</h1>
                <p class="section-subtitle">{{ $drivers->count() }} driver(s) in the database</p>
            </div>
            @auth
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('drivers.create') }}" class="btn-primary">+ Add Driver</a>
                @endif
            @endauth
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('drivers.index') }}" class="mt-6 flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search driver…" class="form-input w-full sm:w-56">
            <select name="team" class="form-select w-full sm:w-48">
                <option value="">All Teams</option>
                @foreach($teams as $team)
                    <option value="{{ $team->id }}" {{ request('team') == $team->id ? 'selected' : '' }}>{{ $team->name }}</option>
                @endforeach
            </select>
            <input type="text" name="nationality" value="{{ request('nationality') }}" placeholder="Nationality…" class="form-input w-full sm:w-40">
            <button type="submit" class="btn-primary">Filter</button>
            @if(request()->hasAny(['search','team','nationality']))
                <a href="{{ route('drivers.index') }}" class="btn-secondary">Clear</a>
            @endif
        </form>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        @forelse($drivers as $driver)
            <a href="{{ route('drivers.show', $driver) }}" class="card-hover group flex flex-col">
                {{-- Photo --}}
                <div class="relative h-48 bg-f1-black overflow-hidden">
                    @if($driver->photo_url)
                        <img src="{{ $driver->photo_url }}" alt="{{ $driver->name }}"
                             class="w-full h-full object-cover object-top opacity-80 group-hover:opacity-100 transition-opacity">
                    @else
                            <span class="font-oswald text-5xl font-bold text-white/5">{{ strtoupper(substr($driver->name, 0, 2)) }}</span>
                        </div>
                    @endif
                    @if($driver->number)
                        <div class="absolute top-3 right-3 font-oswald text-3xl font-bold text-white/20">#{{ $driver->number }}</div>
                    @endif
                    @if(!$driver->is_active)
                        <div class="absolute top-3 left-3 badge-gray">Inactive</div>
                    @endif
                </div>

                {{-- Info --}}
                <div class="p-4 flex flex-col flex-1">
                    <h2 class="font-semibold text-white group-hover:text-f1-red transition-colors">{{ $driver->name }}</h2>
                    <p class="text-xs text-white/40 mt-0.5">{{ $driver->nationality }}</p>
                    <p class="text-xs text-white/30 mt-1">{{ $driver->currentTeam()?->name ?? 'No team' }}</p>
                </div>
            </a>
        @empty
            <div class="col-span-full text-center py-16 text-white/30">
                <p class="text-lg">No drivers found.</p>
            </div>
        @endforelse
    </div>

</div>
@endsection
