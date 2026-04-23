@extends('layouts.app')
@section('title', $team->name)

@section('content')
<div class="page-header">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row gap-6 items-start">
            @if($team->logo_url)
                <div class="w-32 h-24 rounded-xl bg-gradient-to-br from-white/5 to-transparent border border-white/5 flex items-center justify-center flex-shrink-0 relative overflow-hidden shadow-[0_0_30px_-10px_rgba(225,6,0,0.1)]">
                    <div class="absolute inset-0 bg-f1-red/10 blur-2xl"></div>
                    <img src="{{ $team->logo_url }}" alt="{{ $team->name }}" class="max-w-[80%] max-h-[80%] object-contain relative z-10">
                </div>
            @else
                <div class="w-32 h-24 rounded-xl bg-gradient-to-br from-white/5 to-transparent border border-white/5 flex items-center justify-center flex-shrink-0 relative overflow-hidden shadow-[0_0_30px_-10px_rgba(225,6,0,0.05)]">
                    <div class="absolute inset-0 bg-f1-red/5 blur-xl"></div>
                    <div class="relative z-10 font-oswald text-3xl font-bold text-f1-red/50 tracking-widest">{{ strtoupper(substr($team->name, 0, 3)) }}</div>
                </div>
            @endif
            <div class="flex-1">
                <h1 class="section-title">{{ $team->name }}</h1>
                <p class="section-subtitle">{{ $team->country }} · Founded {{ $team->founded_year ?? '—' }} · {{ $team->base ?? '—' }}</p>
            </div>
            @auth @if(auth()->user()->isAdmin())
                <div class="flex gap-2">
                    <a href="{{ route('teams.edit', $team) }}" class="btn-secondary">Edit</a>
                    <form method="POST" action="{{ route('teams.destroy', $team) }}" onsubmit="return confirm('Delete team?')">
                        @csrf @method('DELETE')
                        <button class="btn-danger">Delete</button>
                    </form>
                </div>
            @endif @endauth
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="space-y-5">
            <div class="card p-6">
                <h2 class="font-oswald text-lg font-bold text-white uppercase mb-4">Team Info</h2>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-white/40">Country</dt><dd class="text-white">{{ $team->country }}</dd></div>
                    <div class="flex justify-between"><dt class="text-white/40">Base</dt><dd class="text-white">{{ $team->base ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-white/40">Founded</dt><dd class="text-white">{{ $team->founded_year ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-white/40">Power Unit</dt><dd class="text-white">{{ $team->power_unit ?? '—' }}</dd></div>
                </dl>
            </div>

            <div class="card p-6">
                <h2 class="font-oswald text-lg font-bold text-white uppercase mb-4">Season Stats</h2>
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-white/3 rounded-lg p-3 text-center">
                        <div class="font-oswald text-3xl font-bold text-f1-red">{{ $team->totalPoints() }}</div>
                        <div class="text-xs text-white/40 uppercase tracking-widest mt-1">Points</div>
                    </div>
                    <div class="bg-white/3 rounded-lg p-3 text-center">
                        <div class="font-oswald text-3xl font-bold text-white">{{ $wins }}</div>
                        <div class="text-xs text-white/40 uppercase tracking-widest mt-1">Wins</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 space-y-5">
            <div class="card p-6">
                <h2 class="font-oswald text-lg font-bold text-white uppercase mb-4">Drivers</h2>
                @if($team->seasonEntries->isEmpty())
                    <p class="text-white/30 text-sm">No drivers assigned.</p>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($team->seasonEntries->pluck('driver')->unique('id') as $driver)
                            <a href="{{ route('drivers.show', $driver) }}" class="flex items-center gap-3 p-3 rounded-xl bg-white/3 hover:bg-white/5 transition-colors group">
                                @if($driver->photo_url)
                                    <img src="{{ $driver->photo_url }}" alt="{{ $driver->name }}" class="w-10 h-10 rounded-full object-cover object-top">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-f1-red/20 flex items-center justify-center text-f1-red font-bold text-xs">{{ strtoupper(substr($driver->name,0,2)) }}</div>
                                @endif
                                <div>
                                    <div class="text-sm font-semibold text-white group-hover:text-f1-red transition-colors">{{ $driver->name }}</div>
                                    <div class="text-xs text-white/40">{{ $driver->nationality }} @if($driver->number)· #{{ $driver->number }}@endif</div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="mt-6"><a href="{{ route('teams.index') }}" class="text-white/40 hover:text-white text-sm transition-colors">← Back to Teams</a></div>
</div>
@endsection
