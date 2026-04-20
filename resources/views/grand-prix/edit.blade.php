@extends('layouts.app')
@section('title', 'Edit ' . $grandPrix->name)
@section('content')
<div class="page-header"><div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8"><h1 class="section-title">Edit Grand Prix</h1><p class="section-subtitle">{{ $grandPrix->name }}</p></div></div>
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <form method="POST" action="{{ route('grand-prix.update', $grandPrix) }}" class="card p-8 space-y-6">
        @csrf @method('PUT')
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="sm:col-span-2"><label for="name" class="form-label">Race Name *</label><input id="name" type="text" name="name" value="{{ old('name', $grandPrix->name) }}" required class="form-input"></div>
            <div><label for="circuit_id" class="form-label">Circuit *</label><select id="circuit_id" name="circuit_id" required class="form-select"><option value="">— Select Circuit —</option>@foreach($circuits as $c)<option value="{{ $c->id }}" {{ old('circuit_id', $grandPrix->circuit_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach</select></div>
            <div><label for="season" class="form-label">Season *</label><input id="season" type="number" name="season" value="{{ old('season', $grandPrix->season) }}" required class="form-input"></div>
            <div><label for="date" class="form-label">Race Date *</label><input id="date" type="date" name="date" value="{{ old('date', $grandPrix->date->format('Y-m-d')) }}" required class="form-input"></div>
            <div><label for="round_number" class="form-label">Round Number *</label><input id="round_number" type="number" name="round_number" value="{{ old('round_number', $grandPrix->round_number) }}" required class="form-input"></div>
            <div class="sm:col-span-2"><label for="status" class="form-label">Status *</label><select id="status" name="status" required class="form-select"><option value="scheduled" {{ old('status', $grandPrix->status) === 'scheduled' ? 'selected' : '' }}>Scheduled</option><option value="completed" {{ old('status', $grandPrix->status) === 'completed' ? 'selected' : '' }}>Completed</option><option value="cancelled" {{ old('status', $grandPrix->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option></select></div>
        </div>
        <div class="flex gap-3 pt-2"><button type="submit" class="btn-primary">Save Changes</button><a href="{{ route('grand-prix.show', $grandPrix) }}" class="btn-secondary">Cancel</a></div>
    </form>
</div>
@endsection
