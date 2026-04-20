<?php

namespace Database\Seeders;

use App\Models\Driver;
use App\Models\GrandPrix;
use App\Models\RaceResult;
use Illuminate\Database\Seeder;

class RaceResultSeeder extends Seeder
{
    public function run(): void
    {
        // Bahrain GP 2024 results
        $bahrain = GrandPrix::where('name', 'Bahrain Grand Prix')->first();
        if ($bahrain) {
            $this->createResults($bahrain->id, [
                ['driver' => 'Max Verstappen',  'team' => 'Red Bull Racing',       'pos' => 1, 'pts' => 25,   'fl' => true,  'pole' => true,  'dnf' => false, 'time' => '1:31:44.742'],
                ['driver' => 'Sergio Pérez',    'team' => 'Red Bull Racing',       'pos' => 2, 'pts' => 18,   'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+22.457s'],
                ['driver' => 'Carlos Sainz',    'team' => 'Ferrari',               'pos' => 3, 'pts' => 15,   'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+25.110s'],
                ['driver' => 'Charles Leclerc', 'team' => 'Ferrari',               'pos' => 4, 'pts' => 12,   'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+39.235s'],
                ['driver' => 'George Russell',  'team' => 'Mercedes-AMG Petronas', 'pos' => 5, 'pts' => 10,   'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+46.457s'],
                ['driver' => 'Lando Norris',    'team' => 'McLaren',               'pos' => 6, 'pts' => 8,    'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:07.0s'],
                ['driver' => 'Lewis Hamilton',  'team' => 'Mercedes-AMG Petronas', 'pos' => 7, 'pts' => 6,    'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:20.0s'],
                ['driver' => 'Oscar Piastri',   'team' => 'McLaren',               'pos' => 8, 'pts' => 4,    'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:25.0s'],
                ['driver' => 'Fernando Alonso', 'team' => 'Aston Martin',          'pos' => 9, 'pts' => 2,    'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:35.0s'],
                ['driver' => 'Lance Stroll',    'team' => 'Aston Martin',          'pos' => 10,'pts' => 1,    'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:45.0s'],
            ]);
        }

        // Monaco GP 2024 results
        $monaco = GrandPrix::where('name', 'Monaco Grand Prix')->first();
        if ($monaco) {
            $this->createResults($monaco->id, [
                ['driver' => 'Charles Leclerc', 'team' => 'Ferrari',               'pos' => 1, 'pts' => 25,  'fl' => false, 'pole' => true,  'dnf' => false, 'time' => '2:23:15.554'],
                ['driver' => 'Oscar Piastri',   'team' => 'McLaren',               'pos' => 2, 'pts' => 18,  'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+7.152s'],
                ['driver' => 'Carlos Sainz',    'team' => 'Ferrari',               'pos' => 3, 'pts' => 15,  'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+7.723s'],
                ['driver' => 'Lando Norris',    'team' => 'McLaren',               'pos' => 4, 'pts' => 12,  'fl' => true,  'pole' => false, 'dnf' => false, 'time' => '+14.428s'],
                ['driver' => 'Max Verstappen',  'team' => 'Red Bull Racing',       'pos' => 6, 'pts' => 8,   'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+25.612s'],
                ['driver' => 'George Russell',  'team' => 'Mercedes-AMG Petronas', 'pos' => 8, 'pts' => 4,   'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+52.785s'],
                ['driver' => 'Lewis Hamilton',  'team' => 'Mercedes-AMG Petronas', 'pos' => 9, 'pts' => 2,   'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+55.120s'],
                ['driver' => 'Fernando Alonso', 'team' => 'Aston Martin',          'pos' => 5, 'pts' => 10,  'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+21.015s'],
            ]);
        }

        // British GP 2024
        $british = GrandPrix::where('name', 'British Grand Prix')->first();
        if ($british) {
            $this->createResults($british->id, [
                ['driver' => 'Lewis Hamilton',  'team' => 'Mercedes-AMG Petronas', 'pos' => 1, 'pts' => 25,  'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '1:22:27.066'],
                ['driver' => 'Max Verstappen',  'team' => 'Red Bull Racing',       'pos' => 2, 'pts' => 18,  'fl' => true,  'pole' => false, 'dnf' => false, 'time' => '+1.465s'],
                ['driver' => 'Lando Norris',    'team' => 'McLaren',               'pos' => 3, 'pts' => 15,  'fl' => false, 'pole' => true,  'dnf' => false, 'time' => '+5.483s'],
                ['driver' => 'Oscar Piastri',   'team' => 'McLaren',               'pos' => 4, 'pts' => 12,  'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+7.945s'],
                ['driver' => 'Carlos Sainz',    'team' => 'Ferrari',               'pos' => 5, 'pts' => 10,  'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+27.491s'],
                ['driver' => 'Charles Leclerc', 'team' => 'Ferrari',               'pos' => 6, 'pts' => 8,   'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+31.458s'],
                ['driver' => 'Sergio Pérez',    'team' => 'Red Bull Racing',       'pos' => 8, 'pts' => 4,   'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+57.174s'],
                ['driver' => 'Fernando Alonso', 'team' => 'Aston Martin',          'pos' => 7, 'pts' => 6,   'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+52.615s'],
            ]);
        }
    }

    private function createResults(int $gpId, array $results): void
    {
        foreach ($results as $r) {
            $driver = Driver::where('name', $r['driver'])->first();
            $team   = \App\Models\Team::where('name', $r['team'])->first();
            if (!$driver || !$team) continue;

            RaceResult::create([
                'grand_prix_id' => $gpId,
                'driver_id'     => $driver->id,
                'team_id'       => $team->id,
                'position'      => $r['pos'],
                'points'        => $r['pts'],
                'fastest_lap'   => $r['fl'],
                'pole_position' => $r['pole'],
                'dnf'           => $r['dnf'],
                'total_time'    => $r['time'],
                'laps_completed'=> null,
            ]);
        }
    }
}
