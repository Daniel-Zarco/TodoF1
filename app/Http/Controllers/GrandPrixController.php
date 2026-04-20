<?php

namespace App\Http\Controllers;

use App\Models\Circuit;
use App\Models\GrandPrix;
use Illuminate\Http\Request;

class GrandPrixController extends Controller
{
    public function index(Request $request)
    {
        $query = GrandPrix::with('circuit')->orderByDesc('date');

        if ($request->filled('season')) {
            $query->where('season', $request->season);
        }

        $grandPrix = $query->paginate(15)->withQueryString();
        $seasons   = GrandPrix::select('season')->distinct()->orderByDesc('season')->pluck('season');

        return view('grand-prix.index', compact('grandPrix', 'seasons'));
    }

    public function show(GrandPrix $grandPrix)
    {
        $grandPrix->load([
            'circuit',
            'raceResults' => fn ($q) => $q->orderBy('position')->with('driver', 'team'),
        ]);

        return view('grand-prix.show', compact('grandPrix'));
    }

    public function create()
    {
        $circuits = Circuit::orderBy('name')->get();
        return view('grand-prix.create', compact('circuits'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'circuit_id'   => 'required|exists:circuits,id',
            'name'         => 'required|string|max:150',
            'season'       => 'required|integer|min:1950|max:' . (date('Y') + 1),
            'date'         => 'required|date',
            'round_number' => 'required|integer|min:1|max:30',
            'status'       => 'required|in:scheduled,completed,cancelled',
        ]);

        GrandPrix::create($validated);

        return redirect()->route('grand-prix.index')
            ->with('success', "Grand Prix {$validated['name']} created successfully.");
    }

    public function edit(GrandPrix $grandPrix)
    {
        $circuits = Circuit::orderBy('name')->get();
        return view('grand-prix.edit', compact('grandPrix', 'circuits'));
    }

    public function update(Request $request, GrandPrix $grandPrix)
    {
        $validated = $request->validate([
            'circuit_id'   => 'required|exists:circuits,id',
            'name'         => 'required|string|max:150',
            'season'       => 'required|integer|min:1950|max:' . (date('Y') + 1),
            'date'         => 'required|date',
            'round_number' => 'required|integer|min:1|max:30',
            'status'       => 'required|in:scheduled,completed,cancelled',
        ]);

        $grandPrix->update($validated);

        return redirect()->route('grand-prix.show', $grandPrix)
            ->with('success', "Grand Prix {$grandPrix->name} updated.");
    }

    public function destroy(GrandPrix $grandPrix)
    {
        $name = $grandPrix->name;
        $grandPrix->delete();

        return redirect()->route('grand-prix.index')
            ->with('success', "Grand Prix {$name} deleted.");
    }
}
