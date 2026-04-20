@extends('layouts.app')
@section('title', $circuit->name)
@section('content')
<div class="page-header">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row gap-6 items-start">
            <div class="flex-1">
                <h1 class="section-title">{{ $circuit->name }}</h1>
                <p class="section-subtitle">{{ $circuit->city ? $circuit->city . ', ' : '' }}{{ $circuit->country }}</p>
            </div>
            @auth @if(auth()->user()->isAdmin())
                <div class="flex gap-2">
                    <a href="{{ route('circuits.edit', $circuit) }}" class="btn-secondary">Edit</a>
                    <form method="POST" action="{{ route('circuits.destroy', $circuit) }}" onsubmit="return confirm('Delete circuit?')">
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
            @if($circuit->photo_url)
                <div class="card p-4 flex items-center justify-center h-48">
                    <img src="{{ $circuit->photo_url }}" alt="{{ $circuit->name }}" class="max-w-full max-h-full object-contain opacity-80">
                </div>
            @endif
            <div class="card p-6">
                <h2 class="font-oswald text-lg font-bold text-white uppercase mb-4">Track Info</h2>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-white/40">Country</dt><dd class="text-white">{{ $circuit->country }}</dd></div>
                    <div class="flex justify-between"><dt class="text-white/40">City</dt><dd class="text-white">{{ $circuit->city ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-white/40">Length</dt><dd class="text-white">{{ $circuit->length_km ? $circuit->length_km . ' km' : '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-white/40">Race Laps</dt><dd class="text-white">{{ $circuit->lap_count ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-white/40">Lap Record</dt><dd class="text-white font-mono">{{ $circuit->lap_record ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-white/40">Record Holder</dt><dd class="text-white text-right">{{ $circuit->lap_record_driver ?? '—' }}</dd></div>
                </dl>
            </div>
        </div>

        <div class="lg:col-span-2 card">
            <div class="p-6 border-b border-white/5">
                <h2 class="font-oswald text-lg font-bold text-white uppercase">Grand Prix History</h2>
            </div>
            @if($circuit->grandPrix->isEmpty())
                <div class="p-6 text-white/30 text-sm">No Grands Prix on record.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead><tr><th>Race</th><th>Season</th><th>Date</th><th>Status</th></tr></thead>
                        <tbody>
                            @foreach($circuit->grandPrix as $gp)
                                <tr>
                                    <td><a href="{{ route('grand-prix.show', $gp) }}" class="text-white hover:text-f1-red transition-colors">{{ $gp->name }}</a></td>
                                    <td>{{ $gp->season }}</td>
                                    <td>{{ $gp->date->format('d M Y') }}</td>
                                    <td><span class="badge-{{ $gp->status === 'completed' ? 'green' : ($gp->status === 'cancelled' ? 'red' : 'gray') }}">{{ ucfirst($gp->status) }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
    <div class="mt-6"><a href="{{ route('circuits.index') }}" class="text-white/40 hover:text-white text-sm transition-colors">← Back to Circuits</a></div>
</div>
@endsection
