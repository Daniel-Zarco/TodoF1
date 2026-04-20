<?php

namespace App\Http\Controllers;

use App\Models\Circuit;
use Illuminate\Http\Request;

class CircuitController extends Controller
{
    public function index()
    {
        $circuits = Circuit::withCount('grandPrix')
            ->orderBy('name')
            ->paginate(12);

        return view('circuits.index', compact('circuits'));
    }

    public function show(Circuit $circuit)
    {
        $circuit->load(['grandPrix' => fn ($q) => $q->orderByDesc('date')]);
        return view('circuits.show', compact('circuit'));
    }

    public function create()
    {
        return view('circuits.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:150|unique:circuits,name',
            'country'           => 'required|string|max:100',
            'city'              => 'nullable|string|max:100',
            'length_km'         => 'nullable|numeric|min:1|max:15',
            'lap_count'         => 'nullable|integer|min:1|max:100',
            'lap_record'        => 'nullable|string|max:20',
            'lap_record_driver' => 'nullable|string|max:100',
            'photo_url'         => 'nullable|url|max:500',
        ]);

        Circuit::create($validated);

        return redirect()->route('circuits.index')
            ->with('success', "Circuit {$validated['name']} created successfully.");
    }

    public function edit(Circuit $circuit)
    {
        return view('circuits.edit', compact('circuit'));
    }

    public function update(Request $request, Circuit $circuit)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:150|unique:circuits,name,' . $circuit->id,
            'country'           => 'required|string|max:100',
            'city'              => 'nullable|string|max:100',
            'length_km'         => 'nullable|numeric|min:1|max:15',
            'lap_count'         => 'nullable|integer|min:1|max:100',
            'lap_record'        => 'nullable|string|max:20',
            'lap_record_driver' => 'nullable|string|max:100',
            'photo_url'         => 'nullable|url|max:500',
        ]);

        $circuit->update($validated);

        return redirect()->route('circuits.show', $circuit)
            ->with('success', "Circuit {$circuit->name} updated successfully.");
    }

    public function destroy(Circuit $circuit)
    {
        $name = $circuit->name;
        $circuit->delete();

        return redirect()->route('circuits.index')
            ->with('success', "Circuit {$name} deleted.");
    }
}
