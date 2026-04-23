@extends('layouts.app')
@section('title', 'Teams')

@section('content')
<div class="page-header">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="section-title">Constructors</h1>
                <p class="section-subtitle">{{ $teams->total() }} teams competing in Formula 1</p>
            </div>
            @auth @if(auth()->user()->isAdmin())
                <a href="{{ route('teams.create') }}" class="btn-primary">+ Add Team</a>
            @endif @endauth
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="space-y-4">
        @forelse($teams as $i => $team)
            <a href="{{ route('teams.show', $team) }}" class="card-hover flex items-center gap-5 p-5 group">
                {{-- Position indicator --}}
                @php $pos = ($teams->currentPage() - 1) * $teams->perPage() + $i + 1; @endphp
                <div class="w-8 text-center font-oswald text-2xl font-bold {{ $pos === 1 ? 'text-f1-red' : ($pos <= 3 ? 'text-white/60' : 'text-white/20') }}">
                    {{ $pos }}
                </div>

                {{-- Logo --}}
                <div class="w-20 h-14 rounded-lg bg-gradient-to-br from-white/5 to-transparent border border-white/5 flex-shrink-0 flex items-center justify-center relative overflow-hidden group-hover:border-f1-red/20 transition-all">
                    <div class="absolute inset-0 bg-f1-red/5 opacity-0 group-hover:opacity-100 transition-opacity blur-xl"></div>
                    @if($team->logo_url)
                        <img src="{{ $team->logo_url }}" alt="{{ $team->name }}" class="max-w-[80%] max-h-[80%] object-contain relative z-10 filter brightness-90 group-hover:brightness-110 transition-all">
                    @else
                        <div class="relative z-10 w-10 h-10 rounded bg-f1-red/20 flex items-center justify-center text-f1-red font-oswald font-bold text-xs">
                            {{ strtoupper(substr($team->name, 0, 3)) }}
                        </div>
                    @endif
                </div>

                {{-- Info --}}
                <div class="flex-1 min-w-0">
                    <h2 class="font-semibold text-white group-hover:text-f1-red transition-colors">{{ $team->name }}</h2>
                    <p class="text-xs text-white/40 mt-0.5">{{ $team->country }} · Est. {{ $team->founded_year ?? '—' }} · {{ $team->power_unit ?? '—' }}</p>
                </div>

                {{-- Drivers count --}}
                <div class="hidden sm:block text-center px-4">
                    <div class="font-oswald text-xl font-bold text-white">{{ $team->drivers_count }}</div>
                    <div class="text-xs text-white/30">Drivers</div>
                </div>

                {{-- Points --}}
                <div class="text-right">
                    <div class="font-oswald text-2xl font-bold text-white">{{ number_format($team->race_results_sum_points ?? 0, 1) }}</div>
                    <div class="text-xs text-white/30">pts</div>
                </div>
            </a>
        @empty
            <div class="text-center py-16 text-white/30">No teams found.</div>
        @endforelse
    </div>

    <div class="mt-8">{{ $teams->links() }}</div>
</div>
@endsection
