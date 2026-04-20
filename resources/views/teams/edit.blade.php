@extends('layouts.app')
@section('title', 'Edit ' . $team->name)
@section('content')
<div class="page-header">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="section-title">Edit Team</h1>
        <p class="section-subtitle">{{ $team->name }}</p>
    </div>
</div>
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <form method="POST" action="{{ route('teams.update', $team) }}" class="card p-8 space-y-6">
        @csrf @method('PUT')
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="sm:col-span-2">
                <label for="name" class="form-label">Team Name *</label>
                <input id="name" type="text" name="name" value="{{ old('name', $team->name) }}" required class="form-input">
                @error('name') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="country" class="form-label">Country *</label>
                <input id="country" type="text" name="country" value="{{ old('country', $team->country) }}" required class="form-input">
            </div>
            <div>
                <label for="founded_year" class="form-label">Founded Year</label>
                <input id="founded_year" type="number" name="founded_year" value="{{ old('founded_year', $team->founded_year) }}" min="1950" max="{{ date('Y') }}" class="form-input">
            </div>
            <div>
                <label for="base" class="form-label">Base / Headquarters</label>
                <input id="base" type="text" name="base" value="{{ old('base', $team->base) }}" class="form-input">
            </div>
            <div>
                <label for="power_unit" class="form-label">Power Unit / Engine</label>
                <input id="power_unit" type="text" name="power_unit" value="{{ old('power_unit', $team->power_unit) }}" class="form-input">
            </div>
            <div>
                <label for="constructor_points" class="form-label">Constructor Points</label>
                <input id="constructor_points" type="number" name="constructor_points" value="{{ old('constructor_points', $team->constructor_points) }}" min="0" class="form-input">
            </div>
            <div class="sm:col-span-2">
                <label for="logo_url" class="form-label">Logo URL</label>
                <input id="logo_url" type="url" name="logo_url" value="{{ old('logo_url', $team->logo_url) }}" placeholder="https://…" class="form-input">
            </div>
        </div>
        <div class="flex gap-3 pt-2">
            <button type="submit" class="btn-primary">Save Changes</button>
            <a href="{{ route('teams.show', $team) }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
