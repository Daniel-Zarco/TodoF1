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
                        <img src="{{ $circuit->photo_url }}" alt="{{ $circuit->name }}" class="w-full h-full object-contain p-4 opacity-60 group-hover:opacity-90 transition-opacity relative z-10">
                    @else
                        <div class="absolute inset-0 bg-gradient-to-br from-[#111111] to-[#050505] flex flex-col items-center justify-center overflow-hidden">
                            <div class="absolute inset-0 opacity-[0.03]" style="background-image: repeating-linear-gradient(45deg, transparent, transparent 10px, #fff 10px, #fff 11px);"></div>
                            <div class="absolute -bottom-6 -right-6 w-32 h-32 border-[20px] border-f1-red/5 rounded-full"></div>
                            
                            <span class="relative font-oswald text-5xl font-black tracking-widest text-white/10 group-hover:text-white/20 transition-colors z-10">{{ strtoupper(substr($circuit->name, 0, 3)) }}</span>
                            <div class="relative w-8 h-1 bg-f1-red/50 mt-3 rounded-full z-10"></div>
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
