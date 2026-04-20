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
                'photo_url'         => 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/10/Bahrain_International_Circuit--Flat.svg/600px-Bahrain_International_Circuit--Flat.svg.png',
            ],
            [
                'name'              => 'Jeddah Corniche Circuit',
                'country'           => 'Saudi Arabia',
                'city'              => 'Jeddah',
                'length_km'         => 6.174,
                'lap_count'         => 50,
                'lap_record'        => '1:30.734',
                'lap_record_driver' => 'Lewis Hamilton (2021)',
                'photo_url'         => 'https://upload.wikimedia.org/wikipedia/commons/thumb/9/96/Jeddah_Corniche_Circuit_%28dark%29.svg/600px-Jeddah_Corniche_Circuit_%28dark%29.svg.png',
            ],
            [
                'name'              => 'Albert Park Circuit',
                'country'           => 'Australia',
                'city'              => 'Melbourne',
                'length_km'         => 5.278,
                'lap_count'         => 58,
                'lap_record'        => '1:20.235',
                'lap_record_driver' => 'Charles Leclerc (2022)',
                'photo_url'         => 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/ce/Albert_Park_Circuit%2C_Melbourne_Wikipedia.svg/600px-Albert_Park_Circuit%2C_Melbourne_Wikipedia.svg.png',
            ],
            [
                'name'              => 'Circuit de Monaco',
                'country'           => 'Monaco',
                'city'              => 'Monte Carlo',
                'length_km'         => 3.337,
                'lap_count'         => 78,
                'lap_record'        => '1:12.909',
                'lap_record_driver' => 'Rubens Barrichello (2004)',
                'photo_url'         => 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e0/Circuit_de_Monaco.svg/600px-Circuit_de_Monaco.svg.png',
            ],
            [
                'name'              => 'Circuit de Spa-Francorchamps',
                'country'           => 'Belgium',
                'city'              => 'Stavelot',
                'length_km'         => 7.004,
                'lap_count'         => 44,
                'lap_record'        => '1:46.286',
                'lap_record_driver' => 'Valtteri Bottas (2018)',
                'photo_url'         => 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/40/Spa-Francorchamps_of_Belgium.svg/600px-Spa-Francorchamps_of_Belgium.svg.png',
            ],
            [
                'name'              => 'Silverstone Circuit',
                'country'           => 'United Kingdom',
                'city'              => 'Silverstone',
                'length_km'         => 5.891,
                'lap_count'         => 52,
                'lap_record'        => '1:27.097',
                'lap_record_driver' => 'Max Verstappen (2020)',
                'photo_url'         => 'https://upload.wikimedia.org/wikipedia/commons/thumb/6/61/Silverstone_Circuit_2020.svg/600px-Silverstone_Circuit_2020.svg.png',
            ],
            [
                'name'              => 'Autodromo Nazionale Monza',
                'country'           => 'Italy',
                'city'              => 'Monza',
                'length_km'         => 5.793,
                'lap_count'         => 53,
                'lap_record'        => '1:21.046',
                'lap_record_driver' => 'Rubens Barrichello (2004)',
                'photo_url'         => 'https://upload.wikimedia.org/wikipedia/commons/thumb/d/d1/Monza_track_map.svg/600px-Monza_track_map.svg.png',
            ],
            [
                'name'              => 'Circuit de Catalunya',
                'country'           => 'Spain',
                'city'              => 'Montmeló',
                'length_km'         => 4.657,
                'lap_count'         => 66,
                'lap_record'        => '1:18.149',
                'lap_record_driver' => 'Max Verstappen (2021)',
                'photo_url'         => 'https://upload.wikimedia.org/wikipedia/commons/thumb/b/bc/Circuit_de_Catalunya_track_map.svg/600px-Circuit_de_Catalunya_track_map.svg.png',
            ],
            [
                'name'              => 'Suzuka International Racing Course',
                'country'           => 'Japan',
                'city'              => 'Suzuka',
                'length_km'         => 5.807,
                'lap_count'         => 53,
                'lap_record'        => '1:30.983',
                'lap_record_driver' => 'Lewis Hamilton (2019)',
                'photo_url'         => 'https://upload.wikimedia.org/wikipedia/commons/thumb/5/5a/Suzuka_circuit_map.svg/600px-Suzuka_circuit_map.svg.png',
            ],
            [
                'name'              => 'Autódromo José Carlos Pace',
                'country'           => 'Brazil',
                'city'              => 'São Paulo',
                'length_km'         => 4.309,
                'lap_count'         => 71,
                'lap_record'        => '1:10.540',
                'lap_record_driver' => 'Valtteri Bottas (2018)',
                'photo_url'         => 'https://upload.wikimedia.org/wikipedia/commons/thumb/2/2e/Interlagos_track_map.svg/600px-Interlagos_track_map.svg.png',
            ],
            [
                'name'              => 'Las Vegas Strip Circuit',
                'country'           => 'United States',
                'city'              => 'Las Vegas',
                'length_km'         => 6.201,
                'lap_count'         => 50,
                'lap_record'        => '1:35.490',
                'lap_record_driver' => 'Oscar Piastri (2023)',
                'photo_url'         => 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/ab/Las_Vegas_Strip_Circuit.svg/600px-Las_Vegas_Strip_Circuit.svg.png',
            ],
            [
                'name'              => 'Yas Marina Circuit',
                'country'           => 'UAE',
                'city'              => 'Abu Dhabi',
                'length_km'         => 5.281,
                'lap_count'         => 58,
                'lap_record'        => '1:26.103',
                'lap_record_driver' => 'Max Verstappen (2021)',
                'photo_url'         => 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/a3/Yas_Marina_Circuit_2021.svg/600px-Yas_Marina_Circuit_2021.svg.png',
            ],
        ];

        foreach ($circuits as $circuit) {
            Circuit::create($circuit);
        }
    }
}
