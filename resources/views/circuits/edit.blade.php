@extends('layouts.app')
@section('title', 'Edit ' . $circuit->name)
@section('content')
<div class="page-header"><div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8"><h1 class="section-title">Edit Circuit</h1><p class="section-subtitle">{{ $circuit->name }}</p></div></div>
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <form method="POST" action="{{ route('circuits.update', $circuit) }}" class="card p-8 space-y-6">
        @csrf @method('PUT')
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="sm:col-span-2"><label for="name" class="form-label">Circuit Name *</label><input id="name" type="text" name="name" value="{{ old('name', $circuit->name) }}" required class="form-input"></div>
            <div><label for="country" class="form-label">Country *</label><input id="country" type="text" name="country" value="{{ old('country', $circuit->country) }}" required class="form-input"></div>
            <div><label for="city" class="form-label">City</label><input id="city" type="text" name="city" value="{{ old('city', $circuit->city) }}" class="form-input"></div>
            <div><label for="length_km" class="form-label">Length (km)</label><input id="length_km" type="number" step="0.001" name="length_km" value="{{ old('length_km', $circuit->length_km) }}" class="form-input"></div>
            <div><label for="lap_count" class="form-label">Race Laps</label><input id="lap_count" type="number" name="lap_count" value="{{ old('lap_count', $circuit->lap_count) }}" class="form-input"></div>
            <div><label for="lap_record" class="form-label">Lap Record</label><input id="lap_record" type="text" name="lap_record" value="{{ old('lap_record', $circuit->lap_record) }}" class="form-input font-mono"></div>
            <div><label for="lap_record_driver" class="form-label">Record Holder</label><input id="lap_record_driver" type="text" name="lap_record_driver" value="{{ old('lap_record_driver', $circuit->lap_record_driver) }}" class="form-input"></div>
            <div class="sm:col-span-2"><label for="photo_url" class="form-label">Track Map URL</label><input id="photo_url" type="url" name="photo_url" value="{{ old('photo_url', $circuit->photo_url) }}" class="form-input"></div>
        </div>
        <div class="flex gap-3 pt-2">
            <button type="submit" class="btn-primary">Save Changes</button>
            <a href="{{ route('circuits.show', $circuit) }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
