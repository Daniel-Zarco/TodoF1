<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index()
    {
        $teams = Team::withCount('seasonEntries as drivers_count')
            ->withSum('raceResults', 'points')
            ->orderByDesc('race_results_sum_points')
            ->paginate(10);

        return view('teams.index', compact('teams'));
    }

    public function show(Team $team)
    {
        $team->load(['seasonEntries.driver', 'raceResults.grandPrix']);
        $totalPoints  = $team->raceResults->sum('points');
        $wins         = $team->raceResults->where('position', 1)->count();

        return view('teams.show', compact('team', 'totalPoints', 'wins'));
    }

    public function create()
    {
        return view('teams.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:100|unique:teams,name',
            'country'            => 'required|string|max:100',
            'base'               => 'nullable|string|max:150',
            'founded_year'       => 'nullable|integer|min:1950|max:' . date('Y'),
            'logo_url'           => 'nullable|url|max:500',
            'power_unit'         => 'nullable|string|max:100',
        ]);

        Team::create($validated);

        return redirect()->route('teams.index')
            ->with('success', "Team {$validated['name']} created successfully.");
    }

    public function edit(Team $team)
    {
        return view('teams.edit', compact('team'));
    }

    public function update(Request $request, Team $team)
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:100|unique:teams,name,' . $team->id,
            'country'            => 'required|string|max:100',
            'base'               => 'nullable|string|max:150',
            'founded_year'       => 'nullable|integer|min:1950|max:' . date('Y'),
            'logo_url'           => 'nullable|url|max:500',
            'power_unit'         => 'nullable|string|max:100',
        ]);

        $team->update($validated);

        return redirect()->route('teams.show', $team)
            ->with('success', "Team {$team->name} updated successfully.");
    }

    public function destroy(Team $team)
    {
        $name = $team->name;
        $team->delete();

        return redirect()->route('teams.index')
            ->with('success', "Team {$name} deleted.");
    }
}
