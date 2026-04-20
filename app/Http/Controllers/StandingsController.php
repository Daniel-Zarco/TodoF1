<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Team;
use Illuminate\Http\Request;

class StandingsController extends Controller
{
    public function drivers(Request $request)
    {
        $season = $request->get('season', date('Y'));

        $standings = Driver::with('team')
            ->withSum(['raceResults as season_points' => function ($q) use ($season) {
                $q->whereHas('grandPrix', fn ($gp) => $gp->where('season', $season));
            }], 'points')
            ->orderByDesc('season_points')
            ->get();

        $seasons = \App\Models\GrandPrix::select('season')->distinct()->orderByDesc('season')->pluck('season');

        return view('standings.drivers', compact('standings', 'season', 'seasons'));
    }

    public function teams(Request $request)
    {
        $season = $request->get('season', date('Y'));

        $standings = Team::with('drivers')
            ->withSum(['raceResults as season_points' => function ($q) use ($season) {
                $q->whereHas('grandPrix', fn ($gp) => $gp->where('season', $season));
            }], 'points')
            ->orderByDesc('season_points')
            ->get();

        $seasons = \App\Models\GrandPrix::select('season')->distinct()->orderByDesc('season')->pluck('season');

        return view('standings.teams', compact('standings', 'season', 'seasons'));
    }
}
