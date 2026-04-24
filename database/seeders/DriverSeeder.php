<?php

namespace Database\Seeders;

use App\Models\Driver;
use App\Models\Season;
use App\Models\SeasonEntry;
use App\Models\Team;
use Illuminate\Database\Seeder;

class DriverSeeder extends Seeder
{
    public function run(): void
    {
        $base = 'https://srpowxjflwomhwujzwzb.supabase.co/storage/v1/object/public/todo-f1-assets/';

        $drivers = [
            // Red Bull Racing
            [
                'name' => 'Max Verstappen',
                'nationality' => 'Dutch',
                'date_of_birth' => '1997-09-30',
                'number' => 1,
                'team' => 'Red Bull Racing',
                'photo_url' => $base . 'drivers/portraits/2024/max-verstappen.png',
                'bio' => '4-time Formula 1 World Champion. Youngest champion in F1 history. Races for Red Bull Racing since 2016.',
                'is_active' => true,
            ],
            [
                'name' => 'Sergio Pérez',
                'nationality' => 'Mexican',
                'date_of_birth' => '1990-01-26',
                'number' => 11,
                'team' => 'Red Bull Racing',
                'photo_url' => $base . 'drivers/portraits/2024/sergio-perez.png',
                'bio' => 'Mexican racing driver and Red Bull Racing teammate. Known for his exceptional tyre management.',
                'is_active' => true,
            ],

            // Ferrari
            [
                'name' => 'Charles Leclerc',
                'nationality' => 'Monégasque',
                'date_of_birth' => '1997-10-16',
                'number' => 16,
                'team' => 'Ferrari',
                'photo_url' => $base . 'drivers/portraits/2024/charles-leclerc.png',
                'bio' => 'Ferrari driver and Monégasque racing star. Known for his exceptional qualifying pace.',
                'is_active' => true,
            ],
            [
                'name' => 'Carlos Sainz',
                'nationality' => 'Spanish',
                'date_of_birth' => '1994-09-01',
                'number' => 55,
                'team' => 'Ferrari',
                'photo_url' => $base . 'drivers/portraits/2024/carlos-sainz.png',
                'bio' => 'Spanish driver and son of two-time WRC champion Carlos Sainz Sr. Consistent top performer in F1.',
                'is_active' => true,
            ],

            // Mercedes
            [
                'name' => 'Lewis Hamilton',
                'nationality' => 'British',
                'date_of_birth' => '1985-01-07',
                'number' => 44,
                'team' => 'Mercedes-AMG Petronas',
                'photo_url' => $base . 'drivers/portraits/2024/lewis-hamilton.png',
                'bio' => '7-time Formula 1 World Champion. Holds records for most wins, poles and podiums in F1 history.',
                'is_active' => true,
            ],
            [
                'name' => 'George Russell',
                'nationality' => 'British',
                'date_of_birth' => '1998-02-15',
                'number' => 63,
                'team' => 'Mercedes-AMG Petronas',
                'photo_url' => $base . 'drivers/portraits/2024/george-russell.png',
                'bio' => 'British driver known for his precision driving style. First F1 win at the 2022 Brazilian GP.',
                'is_active' => true,
            ],

            // McLaren
            [
                'name' => 'Lando Norris',
                'nationality' => 'British',
                'date_of_birth' => '1999-11-13',
                'number' => 4,
                'team' => 'McLaren',
                'photo_url' => $base . 'drivers/portraits/2024/lando-norris.png',
                'bio' => 'Rising McLaren star. Won his first F1 race at the 2024 Miami Grand Prix.',
                'is_active' => true,
            ],
            [
                'name' => 'Oscar Piastri',
                'nationality' => 'Australian',
                'date_of_birth' => '2001-04-06',
                'number' => 81,
                'team' => 'McLaren',
                'photo_url' => $base . 'drivers/portraits/2024/oscar-piastri.png',
                'bio' => 'Australian driver and reigning F2 champion. McLaren\'s second driver since 2023.',
                'is_active' => true,
            ],

            // Aston Martin
            [
                'name' => 'Fernando Alonso',
                'nationality' => 'Spanish',
                'date_of_birth' => '1981-07-29',
                'number' => 14,
                'team' => 'Aston Martin',
                'photo_url' => $base . 'drivers/portraits/2024/fernando-alonso.png',
                'bio' => '2-time Formula 1 World Champion (2005, 2006). Legendary veteran with incredible racecraft.',
                'is_active' => true,
            ],
            [
                'name' => 'Lance Stroll',
                'nationality' => 'Canadian',
                'date_of_birth' => '1998-10-29',
                'number' => 18,
                'team' => 'Aston Martin',
                'photo_url' => $base . 'drivers/portraits/2024/lance-stroll.png',
                'bio' => 'Canadian driver racing for Aston Martin alongside Fernando Alonso.',
                'is_active' => true,
            ],

            // Haas
            [
                'name' => 'Kevin Magnussen',
                'nationality' => 'Danish',
                'date_of_birth' => '1992-10-05',
                'number' => 20,
                'team' => 'Haas',
                'photo_url' => $base . 'drivers/portraits/2024/kevin-magnussen.png',
                'bio' => 'Experienced Danish driver in his second stint with the Haas F1 Team.',
                'is_active' => true,
            ],
            [
                'name' => 'Nico Hülkenberg',
                'nationality' => 'German',
                'date_of_birth' => '1987-08-19',
                'number' => 27,
                'team' => 'Haas',
                'photo_url' => $base . 'drivers/portraits/2024/nico-hulkenberg.png',
                'bio' => 'German veteran known as the best driver never to podium in F1.',
                'is_active' => true,
            ],

            // Williams
            [
                'name' => 'Alexander Albon',
                'nationality' => 'Thai',
                'date_of_birth' => '1996-03-23',
                'number' => 23,
                'team' => 'Williams',
                'photo_url' => $base . 'drivers/portraits/2024/alexander-albon.png',
                'bio' => 'Thai-British driver and Williams team leader.',
                'is_active' => true,
            ],
            [
                'name' => 'Logan Sargeant',
                'nationality' => 'American',
                'date_of_birth' => '2000-12-31',
                'number' => 2,
                'team' => 'Williams',
                'photo_url' => $base . 'drivers/portraits/2024/logan-sargeant.png',
                'bio' => 'American driver and first US F1 driver in many years.',
                'is_active' => false,
            ],

            // Alpine
            [
                'name' => 'Esteban Ocon',
                'nationality' => 'French',
                'date_of_birth' => '1996-09-17',
                'number' => 31,
                'team' => 'Alpine',
                'photo_url' => $base . 'drivers/portraits/2024/esteban-ocon.png',
                'bio' => 'French driver with one F1 race win: 2021 Hungarian Grand Prix.',
                'is_active' => true,
            ],
            [
                'name' => 'Pierre Gasly',
                'nationality' => 'French',
                'date_of_birth' => '1996-02-07',
                'number' => 10,
                'team' => 'Alpine',
                'photo_url' => $base . 'drivers/portraits/2024/pierre-gasly.png',
                'bio' => 'French driver with one incredible F1 win at the 2020 Italian Grand Prix.',
                'is_active' => true,
            ],
        ];

        $season = Season::where('year', 2024)->first();

        foreach ($drivers as $data) {
            $team = Team::where('name', $data['team'])->first();

            $driver = Driver::create([
                'name' => $data['name'],
                'nationality' => $data['nationality'],
                'date_of_birth' => $data['date_of_birth'],
                'number' => $data['number'],
                'photo_url' => $data['photo_url'],
                'bio' => $data['bio'],
                'is_active' => $data['is_active'],
            ]);

            if ($team && $season) {
                SeasonEntry::create([
                    'season_id' => $season->id,
                    'team_id' => $team->id,
                    'driver_id' => $driver->id,
                ]);
            }
        }
    }
}