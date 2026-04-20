<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    public function home()
    {
        return view('welcome');
    }

    public function dashboard()
    {
        $driverCount  = \App\Models\Driver::count();
        $teamCount    = \App\Models\Team::count();
        $circuitCount = \App\Models\Circuit::count();
        $gpCount      = \App\Models\GrandPrix::count();

        $latestResults = \App\Models\GrandPrix::with(['circuit', 'raceResults.driver', 'raceResults.team'])
            ->where('status', 'completed')
            ->orderByDesc('date')
            ->limit(3)
            ->get();

        return view('dashboard', compact('driverCount', 'teamCount', 'circuitCount', 'gpCount', 'latestResults'));
    }
}
