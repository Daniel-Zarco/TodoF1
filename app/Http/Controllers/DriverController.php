<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Season;
use App\Models\SeasonEntry;
use App\Models\Team;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function index(Request $request)
    {
        $query = Driver::with(['seasonEntries.team', 'seasonEntries.season'])->orderBy('name');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('team')) {
            $query->whereHas('seasonEntries', function ($q) use ($request) {
                $q->where('team_id', $request->team);
            });
        }

        if ($request->filled('nationality')) {
            $query->where('nationality', 'like', '%' . $request->nationality . '%');
        }

        $drivers = $query->get();
        $teams = Team::orderBy('name')->get();

        return view('drivers.index', compact('drivers', 'teams'));
    }

    public function show(Driver $driver)
    {
        $driver->load(['seasonEntries.team', 'seasonEntries.season', 'raceResults.grandPrix.circuit']);

        $totalPoints = $driver->raceResults->sum('points');
        $wins = $driver->raceResults->where('position', 1)->count();
        $podiums = $driver->raceResults->whereIn('position', [1, 2, 3])->count();
        $poles = $driver->raceResults->where('pole_position', true)->count();
        $fastestLaps = $driver->raceResults->where('fastest_lap', true)->count();

        return view('drivers.show', compact('driver', 'totalPoints', 'wins', 'podiums', 'poles', 'fastestLaps'));
    }

    public function create()
    {
        $teams = Team::orderBy('name')->get();
        return view('drivers.create', compact('teams'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'team_id' => 'nullable|exists:teams,id',
            'name' => 'required|string|max:100',
            'nationality' => 'required|string|max:100',
            'date_of_birth' => 'nullable|date|before:today',
            'number' => 'nullable|integer|min:1|max:99|unique:drivers,number',
            'photo_url' => 'nullable|url|max:500',
            'bio' => 'nullable|string|max:2000',
            'is_active' => 'boolean',
        ]);

        $teamId = $request->input('team_id');
        unset($validated['team_id']);

        $validated['is_active'] = $request->boolean('is_active', true);

        $driver = Driver::create($validated);

        if ($teamId) {
            $season = Season::firstOrCreate(['year' => 2024]);
            SeasonEntry::create([
                'season_id' => $season->id,
                'team_id' => $teamId,
                'driver_id' => $driver->id,
            ]);
        }

        return redirect()->route('drivers.index')
            ->with('success', "Driver {$validated['name']} created successfully.");
    }

    public function edit(Driver $driver)
    {
        $teams = Team::orderBy('name')->get();
        return view('drivers.edit', compact('driver', 'teams'));
    }

    public function update(Request $request, Driver $driver)
    {
        $validated = $request->validate([
            'team_id' => 'nullable|exists:teams,id',
            'name' => 'required|string|max:100',
            'nationality' => 'required|string|max:100',
            'date_of_birth' => 'nullable|date|before:today',
            'number' => 'nullable|integer|min:1|max:99|unique:drivers,number,' . $driver->id,
            'photo_url' => 'nullable|url|max:500',
            'bio' => 'nullable|string|max:2000',
            'is_active' => 'boolean',
        ]);

        $teamId = $request->input('team_id');
        unset($validated['team_id']);

        $validated['is_active'] = $request->boolean('is_active');

        $driver->update($validated);

        if ($teamId) {
            $season = Season::firstOrCreate(['year' => 2024]);
            SeasonEntry::updateOrCreate(
                ['season_id' => $season->id, 'driver_id' => $driver->id],
                ['team_id' => $teamId]
            );
        } else {
            $season = Season::where('year', 2024)->first();
            if ($season) {
                SeasonEntry::where('season_id', $season->id)->where('driver_id', $driver->id)->delete();
            }
        }

        return redirect()->route('drivers.show', $driver)
            ->with('success', "Driver {$driver->name} updated successfully.");
    }

    public function destroy(Driver $driver)
    {
        $name = $driver->name;
        $driver->delete();

        return redirect()->route('drivers.index')
            ->with('success', "Driver {$name} deleted.");
    }
}
