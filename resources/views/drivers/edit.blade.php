@extends('layouts.app')
@section('title', 'Edit ' . $driver->name)

@section('content')
<div class="page-header">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="section-title">Edit Driver</h1>
        <p class="section-subtitle">{{ $driver->name }}</p>
    </div>
</div>

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <form method="POST" action="{{ route('drivers.update', $driver) }}" class="card p-8 space-y-6">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="sm:col-span-2">
                <label for="name" class="form-label">Full Name *</label>
                <input id="name" type="text" name="name" value="{{ old('name', $driver->name) }}" required class="form-input">
                @error('name') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="nationality" class="form-label">Nationality *</label>
                <input id="nationality" type="text" name="nationality" value="{{ old('nationality', $driver->nationality) }}" required class="form-input">
                @error('nationality') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="number" class="form-label">Car Number</label>
                <input id="number" type="number" name="number" value="{{ old('number', $driver->number) }}" min="1" max="99" class="form-input">
                @error('number') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="date_of_birth" class="form-label">Date of Birth</label>
                <input id="date_of_birth" type="date" name="date_of_birth" value="{{ old('date_of_birth', $driver->date_of_birth?->format('Y-m-d')) }}" class="form-input">
                @error('date_of_birth') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="team_id" class="form-label">Team</label>
                <select id="team_id" name="team_id" class="form-select">
                    <option value="">— No team —</option>
                    @foreach($teams as $team)
                        <option value="{{ $team->id }}" {{ old('team_id', $driver->team_id) == $team->id ? 'selected' : '' }}>{{ $team->name }}</option>
                    @endforeach
                </select>
                @error('team_id') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="photo_url" class="form-label">Photo URL</label>
                <input id="photo_url" type="url" name="photo_url" value="{{ old('photo_url', $driver->photo_url) }}" placeholder="https://…" class="form-input">
                @error('photo_url') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="bio" class="form-label">Biography</label>
                <textarea id="bio" name="bio" rows="4" class="form-input resize-none">{{ old('bio', $driver->bio) }}</textarea>
                @error('bio') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center gap-3">
                <input id="is_active" type="checkbox" name="is_active" value="1" {{ old('is_active', $driver->is_active) ? 'checked' : '' }}
                       class="w-4 h-4 rounded bg-white/5 border border-white/20 text-f1-red focus:ring-f1-red/50">
                <label for="is_active" class="text-sm text-white/70">Active driver</label>
            </div>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="btn-primary">Save Changes</button>
            <a href="{{ route('drivers.show', $driver) }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
