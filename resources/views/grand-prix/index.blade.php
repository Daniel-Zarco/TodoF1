@extends('layouts.app')
@section('title', 'Grand Prix')
@section('content')
<div class="page-header">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="section-title">Grand Prix</h1>
                <p class="section-subtitle">Race calendar and results</p>
            </div>
            @auth @if(auth()->user()->isAdmin())
                <a href="{{ route('grand-prix.create') }}" class="btn-primary">+ Add Grand Prix</a>
            @endif @endauth
        </div>

        <form method="GET" action="{{ route('grand-prix.index') }}" class="mt-5 flex flex-wrap gap-3">
            <select name="season" class="form-select w-36">
                <option value="">All Seasons</option>
                @foreach($seasons as $s)
                    <option value="{{ $s }}" {{ request('season') == $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-primary">Filter</button>
            @if(request('season'))<a href="{{ route('grand-prix.index') }}" class="btn-secondary">Clear</a>@endif
        </form>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="space-y-3">
        @forelse($grandPrix as $gp)
            <a href="{{ route('grand-prix.show', $gp) }}" class="card-hover flex items-center gap-5 p-5 group">
                <div class="w-8 text-center font-oswald text-xl font-bold text-white/30 flex-shrink-0">{{ str_pad($gp->round_number, 2, '0', STR_PAD_LEFT) }}</div>
                <div class="flex-1 min-w-0">
                    <h2 class="font-semibold text-white group-hover:text-f1-red transition-colors">{{ $gp->name }}</h2>
                    <p class="text-xs text-white/40 mt-0.5">{{ $gp->circuit->name }} · {{ $gp->circuit->country }}</p>
                </div>
                <div class="hidden sm:block text-sm text-white/40">{{ $gp->date->format('d M Y') }}</div>
                <div><span class="badge-{{ $gp->status === 'completed' ? 'green' : ($gp->status === 'cancelled' ? 'red' : 'gray') }}">{{ ucfirst($gp->status) }}</span></div>
                <div class="text-white/20 text-xs">{{ $gp->season }}</div>
            </a>
        @empty
            <div class="text-center py-16 text-white/30">No Grand Prix found.</div>
        @endforelse
    </div>
    <div class="mt-8">{{ $grandPrix->links() }}</div>
</div>
@endsection
