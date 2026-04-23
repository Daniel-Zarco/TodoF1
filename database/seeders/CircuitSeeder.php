<?php

namespace Database\Seeders;

use App\Models\Circuit;
use Illuminate\Database\Seeder;

class CircuitSeeder extends Seeder
{
    public function run(): void
    {
        $circuits = [
            [
                'name'              => 'Circuito de Bahrain',
                'country'           => 'Bahrain',
                'city'              => 'Sakhir',
                'length_km'         => 5.412,
                'lap_count'         => 57,
                'lap_record'        => '1:31.447',
                'lap_record_driver' => 'Pedro de la Rosa (2005)',
                'photo_url'         => null,
            ],
            [
                'name'              => 'Jeddah Corniche Circuit',
                'country'           => 'Saudi Arabia',
                'city'              => 'Jeddah',
                'length_km'         => 6.174,
                'lap_count'         => 50,
                'lap_record'        => '1:30.734',
                'lap_record_driver' => 'Lewis Hamilton (2021)',
                'photo_url'         => null,
            ],
            [
                'name'              => 'Albert Park Circuit',
                'country'           => 'Australia',
                'city'              => 'Melbourne',
                'length_km'         => 5.278,
                'lap_count'         => 58,
                'lap_record'        => '1:20.235',
                'lap_record_driver' => 'Charles Leclerc (2022)',
                'photo_url'         => null,
            ],
            [
                'name'              => 'Circuit de Monaco',
                'country'           => 'Monaco',
                'city'              => 'Monte Carlo',
                'length_km'         => 3.337,
                'lap_count'         => 78,
                'lap_record'        => '1:12.909',
                'lap_record_driver' => 'Rubens Barrichello (2004)',
                'photo_url'         => null,
            ],
            [
                'name'              => 'Circuit de Spa-Francorchamps',
                'country'           => 'Belgium',
                'city'              => 'Stavelot',
                'length_km'         => 7.004,
                'lap_count'         => 44,
                'lap_record'        => '1:46.286',
                'lap_record_driver' => 'Valtteri Bottas (2018)',
                'photo_url'         => null,
            ],
            [
                'name'              => 'Silverstone Circuit',
                'country'           => 'United Kingdom',
                'city'              => 'Silverstone',
                'length_km'         => 5.891,
                'lap_count'         => 52,
                'lap_record'        => '1:27.097',
                'lap_record_driver' => 'Max Verstappen (2020)',
                'photo_url'         => null,
            ],
            [
                'name'              => 'Autodromo Nazionale Monza',
                'country'           => 'Italy',
                'city'              => 'Monza',
                'length_km'         => 5.793,
                'lap_count'         => 53,
                'lap_record'        => '1:21.046',
                'lap_record_driver' => 'Rubens Barrichello (2004)',
                'photo_url'         => null,
            ],
            [
                'name'              => 'Circuit de Catalunya',
                'country'           => 'Spain',
                'city'              => 'Montmeló',
                'length_km'         => 4.657,
                'lap_count'         => 66,
                'lap_record'        => '1:18.149',
                'lap_record_driver' => 'Max Verstappen (2021)',
                'photo_url'         => null,
            ],
            [
                'name'              => 'Suzuka International Racing Course',
                'country'           => 'Japan',
                'city'              => 'Suzuka',
                'length_km'         => 5.807,
                'lap_count'         => 53,
                'lap_record'        => '1:30.983',
                'lap_record_driver' => 'Lewis Hamilton (2019)',
                'photo_url'         => null,
            ],
            [
                'name'              => 'Autódromo José Carlos Pace',
                'country'           => 'Brazil',
                'city'              => 'São Paulo',
                'length_km'         => 4.309,
                'lap_count'         => 71,
                'lap_record'        => '1:10.540',
                'lap_record_driver' => 'Valtteri Bottas (2018)',
                'photo_url'         => null,
            ],
            [
                'name'              => 'Las Vegas Strip Circuit',
                'country'           => 'United States',
                'city'              => 'Las Vegas',
                'length_km'         => 6.201,
                'lap_count'         => 50,
                'lap_record'        => '1:35.490',
                'lap_record_driver' => 'Oscar Piastri (2023)',
                'photo_url'         => null,
            ],
            [
                'name'              => 'Yas Marina Circuit',
                'country'           => 'UAE',
                'city'              => 'Abu Dhabi',
                'length_km'         => 5.281,
                'lap_count'         => 58,
                'lap_record'        => '1:26.103',
                'lap_record_driver' => 'Max Verstappen (2021)',
                'photo_url'         => null,
            ],
        ];

        foreach ($circuits as $circuit) {
            Circuit::create($circuit);
        }
    }
}
