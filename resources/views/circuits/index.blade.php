@extends('layouts.app')
@section('title', 'Circuits')
@section('content')
<div class="page-header">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="section-title">Circuits</h1>
                <p class="section-subtitle">{{ $circuits->total() }} F1 circuits worldwide</p>
            </div>
            @auth @if(auth()->user()->isAdmin())
                <a href="{{ route('circuits.create') }}" class="btn-primary">+ Add Circuit</a>
            @endif @endauth
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($circuits as $circuit)
            <a href="{{ route('circuits.show', $circuit) }}" class="card-hover group flex flex-col">
                <div class="relative h-44 bg-gradient-to-br from-f1-card to-[#0a0a10] overflow-hidden">
                    @if($circuit->photo_url)
                        <img src="{{ $circuit->photo_url }}" alt="{{ $circuit->name }}" class="w-full h-full object-contain p-4 opacity-60 group-hover:opacity-90 transition-opacity">
                    @else
                        <div class="w-full h-full flex items-center justify-center">
                            <svg class="w-16 h-16 text-white/5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                        </div>
                    @endif
                </div>
                <div class="p-4">
                    <h2 class="font-semibold text-white group-hover:text-f1-red transition-colors">{{ $circuit->name }}</h2>
                    <p class="text-xs text-white/40 mt-0.5">{{ $circuit->city ? $circuit->city . ', ' : '' }}{{ $circuit->country }}</p>
                    <div class="flex gap-4 mt-3 text-xs text-white/30">
                        @if($circuit->length_km) <span>{{ $circuit->length_km }} km</span> @endif
                        @if($circuit->lap_count) <span>{{ $circuit->lap_count }} laps</span> @endif
                        @if($circuit->lap_record) <span>⏱ {{ $circuit->lap_record }}</span> @endif
                    </div>
                </div>
            </a>
        @empty
            <div class="col-span-full text-center py-16 text-white/30">No circuits found.</div>
        @endforelse
    </div>
    <div class="mt-8">{{ $circuits->links() }}</div>
</div>
@endsection
