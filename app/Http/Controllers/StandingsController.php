<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\GrandPrix;
use App\Models\Team;
use Illuminate\Http\Request;

class StandingsController extends Controller
{
    public function drivers(Request $request)
    {
        $seasons = GrandPrix::select('season')
            ->distinct()
            ->orderByDesc('season')
            ->pluck('season');

        $season = $request->get('season', $seasons->first());

        $standings = Driver::with(['seasonEntries.team', 'seasonEntries.season'])
            ->withSum([
                'raceResults as season_points' => function ($q) use ($season) {
                    $q->whereHas('grandPrix', function ($gp) use ($season) {
                        $gp->where('season', $season);
                    });
                }
            ], 'points')
            ->get()
            ->sortByDesc(fn($driver) => $driver->season_points ?? 0)
            ->values();

        return view('standings.drivers', compact('standings', 'season', 'seasons'));
    }

    public function teams(Request $request)
    {
        $seasons = GrandPrix::select('season')
            ->distinct()
            ->orderByDesc('season')
            ->pluck('season');

        $season = $request->get('season', $seasons->first());

        $standings = Team::with('seasonEntries.driver')
            ->withSum([
                'raceResults as season_points' => function ($q) use ($season) {
                    $q->whereHas('grandPrix', function ($gp) use ($season) {
                        $gp->where('season', $season);
                    });
                }
            ], 'points')
            ->get()
            ->sortByDesc(fn($team) => $team->season_points ?? 0)
            ->values();

        return view('standings.teams', compact('standings', 'season', 'seasons'));
    }
}