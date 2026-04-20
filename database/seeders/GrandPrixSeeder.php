<?php

namespace Database\Seeders;

use App\Models\Circuit;
use App\Models\GrandPrix;
use Illuminate\Database\Seeder;

class GrandPrixSeeder extends Seeder
{
    public function run(): void
    {
        $races = [
            ['name' => 'Bahrain Grand Prix',       'circuit' => 'Circuito de Bahrain',                  'season' => 2024, 'date' => '2024-03-02', 'round' => 1,  'status' => 'completed'],
            ['name' => 'Saudi Arabian Grand Prix',  'circuit' => 'Jeddah Corniche Circuit',               'season' => 2024, 'date' => '2024-03-09', 'round' => 2,  'status' => 'completed'],
            ['name' => 'Australian Grand Prix',     'circuit' => 'Albert Park Circuit',                   'season' => 2024, 'date' => '2024-03-24', 'round' => 3,  'status' => 'completed'],
            ['name' => 'Monaco Grand Prix',         'circuit' => 'Circuit de Monaco',                     'season' => 2024, 'date' => '2024-05-26', 'round' => 8,  'status' => 'completed'],
            ['name' => 'Spanish Grand Prix',        'circuit' => 'Circuit de Catalunya',                  'season' => 2024, 'date' => '2024-06-23', 'round' => 10, 'status' => 'completed'],
            ['name' => 'Belgian Grand Prix',        'circuit' => 'Circuit de Spa-Francorchamps',          'season' => 2024, 'date' => '2024-07-28', 'round' => 14, 'status' => 'completed'],
            ['name' => 'British Grand Prix',        'circuit' => 'Silverstone Circuit',                   'season' => 2024, 'date' => '2024-07-07', 'round' => 12, 'status' => 'completed'],
            ['name' => 'Italian Grand Prix',        'circuit' => 'Autodromo Nazionale Monza',             'season' => 2024, 'date' => '2024-09-01', 'round' => 16, 'status' => 'completed'],
            ['name' => 'Japanese Grand Prix',       'circuit' => 'Suzuka International Racing Course',    'season' => 2024, 'date' => '2024-04-07', 'round' => 4,  'status' => 'completed'],
            ['name' => 'Brazilian Grand Prix',      'circuit' => 'Autódromo José Carlos Pace',            'season' => 2024, 'date' => '2024-11-03', 'round' => 21, 'status' => 'completed'],
            ['name' => 'Las Vegas Grand Prix',      'circuit' => 'Las Vegas Strip Circuit',               'season' => 2024, 'date' => '2024-11-23', 'round' => 22, 'status' => 'completed'],
            ['name' => 'Abu Dhabi Grand Prix',      'circuit' => 'Yas Marina Circuit',                    'season' => 2024, 'date' => '2024-12-08', 'round' => 24, 'status' => 'completed'],
        ];

        foreach ($races as $race) {
            $circuit = Circuit::where('name', $race['circuit'])->first();
            if (!$circuit) continue;

            GrandPrix::create([
                'circuit_id'   => $circuit->id,
                'name'         => $race['name'],
                'season'       => $race['season'],
                'date'         => $race['date'],
                'round_number' => $race['round'],
                'status'       => $race['status'],
            ]);
        }
    }
}
