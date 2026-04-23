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
                        {{-- Premium circuit graphic fallback --}}
                        <div class="absolute inset-0 bg-[#090909] flex flex-col justify-between overflow-hidden">

                            {{-- Background watermark: full circuit name, very faint --}}
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="font-oswald font-black uppercase whitespace-nowrap"
                                      style="font-size: 5rem; letter-spacing: 0.15em; color: rgba(255,255,255,0.03); line-height: 1;">
                                    {{ strtoupper($circuit->name) }}
                                </span>
                            </div>

                            {{-- Red accent: top-left corner bar --}}
                            <div class="absolute top-0 left-0 w-1 h-full bg-gradient-to-b from-f1-red/60 via-f1-red/10 to-transparent"></div>

                            {{-- Bottom-left: country tag --}}
                            <div class="absolute bottom-4 left-5">
                                <p class="text-white/20 text-[10px] uppercase tracking-[0.25em] font-medium group-hover:text-white/30 transition-colors">
                                    {{ strtoupper($circuit->country) }}
                                </p>
                            </div>

                            {{-- Center: bold abbreviation --}}
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="font-oswald text-6xl font-black tracking-tight text-white/[0.07] group-hover:text-white/[0.12] transition-colors select-none" style="letter-spacing: -0.02em;">
                                    {{ strtoupper(substr($circuit->name, 0, 3)) }}
                                </span>
                            </div>

                            {{-- Top-right: subtle corner detail --}}
                            <div class="absolute -top-8 -right-8 w-24 h-24 rounded-full border border-white/[0.03]"></div>
                            <div class="absolute -top-4 -right-4 w-12 h-12 rounded-full border border-white/[0.03]"></div>

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
