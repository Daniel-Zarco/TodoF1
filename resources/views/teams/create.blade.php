@extends('layouts.app')
@section('title', 'Add Team')
@section('content')
<div class="page-header">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="section-title">Add Team</h1>
    </div>
</div>
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <form method="POST" action="{{ route('teams.store') }}" class="card p-8 space-y-6">
        @csrf
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="sm:col-span-2">
                <label for="name" class="form-label">Team Name *</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required class="form-input">
                @error('name') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="country" class="form-label">Country *</label>
                <input id="country" type="text" name="country" value="{{ old('country') }}" required class="form-input">
                @error('country') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="founded_year" class="form-label">Founded Year</label>
                <input id="founded_year" type="number" name="founded_year" value="{{ old('founded_year') }}" min="1950" max="{{ date('Y') }}" class="form-input">
                @error('founded_year') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="base" class="form-label">Base / Headquarters</label>
                <input id="base" type="text" name="base" value="{{ old('base') }}" class="form-input">
            </div>
            <div>
                <label for="power_unit" class="form-label">Power Unit / Engine</label>
                <input id="power_unit" type="text" name="power_unit" value="{{ old('power_unit') }}" class="form-input">
            </div>
            <div>
                <label for="constructor_points" class="form-label">Constructor Points</label>
                <input id="constructor_points" type="number" name="constructor_points" value="{{ old('constructor_points', 0) }}" min="0" class="form-input">
            </div>
            <div class="sm:col-span-2">
                <label for="logo_url" class="form-label">Logo URL</label>
                <input id="logo_url" type="url" name="logo_url" value="{{ old('logo_url') }}" placeholder="https://…" class="form-input">
                @error('logo_url') <p class="form-error">{{ $message }}</p> @enderror
            </div>
        </div>
        <div class="flex gap-3 pt-2">
            <button type="submit" class="btn-primary">Create Team</button>
            <a href="{{ route('teams.index') }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
