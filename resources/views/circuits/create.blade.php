@extends('layouts.app')
@section('title', 'Add Circuit')
@section('content')
<div class="page-header"><div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8"><h1 class="section-title">Add Circuit</h1></div></div>
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <form method="POST" action="{{ route('circuits.store') }}" class="card p-8 space-y-6">
        @csrf
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="sm:col-span-2">
                <label for="name" class="form-label">Circuit Name *</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required class="form-input">
                @error('name') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="country" class="form-label">Country *</label>
                <input id="country" type="text" name="country" value="{{ old('country') }}" required class="form-input">
            </div>
            <div>
                <label for="city" class="form-label">City</label>
                <input id="city" type="text" name="city" value="{{ old('city') }}" class="form-input">
            </div>
            <div>
                <label for="length_km" class="form-label">Length (km)</label>
                <input id="length_km" type="number" step="0.001" name="length_km" value="{{ old('length_km') }}" class="form-input">
            </div>
            <div>
                <label for="lap_count" class="form-label">Race Laps</label>
                <input id="lap_count" type="number" name="lap_count" value="{{ old('lap_count') }}" class="form-input">
            </div>
            <div>
                <label for="lap_record" class="form-label">Lap Record (e.g. 1:19.119)</label>
                <input id="lap_record" type="text" name="lap_record" value="{{ old('lap_record') }}" placeholder="1:19.119" class="form-input font-mono">
            </div>
            <div>
                <label for="lap_record_driver" class="form-label">Record Holder</label>
                <input id="lap_record_driver" type="text" name="lap_record_driver" value="{{ old('lap_record_driver') }}" class="form-input">
            </div>
            <div class="sm:col-span-2">
                <label for="photo_url" class="form-label">Track Map URL</label>
                <input id="photo_url" type="url" name="photo_url" value="{{ old('photo_url') }}" placeholder="https://…" class="form-input">
            </div>
        </div>
        <div class="flex gap-3 pt-2">
            <button type="submit" class="btn-primary">Create Circuit</button>
            <a href="{{ route('circuits.index') }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
