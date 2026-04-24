<?php

namespace Database\Seeders;

use App\Models\Driver;
use App\Models\GrandPrix;
use App\Models\RaceResult;
use App\Models\Team;
use Illuminate\Database\Seeder;

class RaceResultSeeder extends Seeder
{
    public function run(): void
    {
        // Bahrain Grand Prix 2024
        $grandPrix = GrandPrix::where('name', 'Bahrain Grand Prix')->where('season', 2024)->first();
        if ($grandPrix) {
            $this->createResults($grandPrix->id, [
                ['driver' => 'Max Verstappen', 'team' => 'Red Bull Racing', 'pos' => 1, 'pts' => 26.0, 'fl' => true, 'pole' => true, 'dnf' => false, 'time' => '1:31:44.742', 'laps' => 57],
                ['driver' => 'Sergio Pérez', 'team' => 'Red Bull Racing', 'pos' => 2, 'pts' => 18.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+22.457', 'laps' => 57],
                ['driver' => 'Carlos Sainz', 'team' => 'Ferrari', 'pos' => 3, 'pts' => 15.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+25.110', 'laps' => 57],
                ['driver' => 'Charles Leclerc', 'team' => 'Ferrari', 'pos' => 4, 'pts' => 12.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+39.669', 'laps' => 57],
                ['driver' => 'George Russell', 'team' => 'Mercedes-AMG Petronas', 'pos' => 5, 'pts' => 10.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+46.788', 'laps' => 57],
                ['driver' => 'Lando Norris', 'team' => 'McLaren', 'pos' => 6, 'pts' => 8.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+48.458', 'laps' => 57],
                ['driver' => 'Lewis Hamilton', 'team' => 'Mercedes-AMG Petronas', 'pos' => 7, 'pts' => 6.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+50.324', 'laps' => 57],
                ['driver' => 'Oscar Piastri', 'team' => 'McLaren', 'pos' => 8, 'pts' => 4.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+56.082', 'laps' => 57],
                ['driver' => 'Fernando Alonso', 'team' => 'Aston Martin', 'pos' => 9, 'pts' => 2.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:14.887', 'laps' => 57],
                ['driver' => 'Lance Stroll', 'team' => 'Aston Martin', 'pos' => 10, 'pts' => 1.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:33.216', 'laps' => 57],
                ['driver' => 'Zhou Guanyu', 'team' => 'Kick Sauber', 'pos' => 11, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+6.759', 'laps' => 56],
                ['driver' => 'Kevin Magnussen', 'team' => 'Haas', 'pos' => 12, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+8.316', 'laps' => 56],
                ['driver' => 'Daniel Ricciardo', 'team' => 'Visa Cash App RB', 'pos' => 13, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+8.958', 'laps' => 56],
                ['driver' => 'Yuki Tsunoda', 'team' => 'Visa Cash App RB', 'pos' => 14, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+9.482', 'laps' => 56],
                ['driver' => 'Alexander Albon', 'team' => 'Williams', 'pos' => 15, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+11.886', 'laps' => 56],
                ['driver' => 'Nico Hülkenberg', 'team' => 'Haas', 'pos' => 16, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+17.632', 'laps' => 56],
                ['driver' => 'Esteban Ocon', 'team' => 'Alpine', 'pos' => 17, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+31.450', 'laps' => 56],
                ['driver' => 'Pierre Gasly', 'team' => 'Alpine', 'pos' => 18, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+32.417', 'laps' => 56],
                ['driver' => 'Valtteri Bottas', 'team' => 'Kick Sauber', 'pos' => 19, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+1:23.230', 'laps' => 56],
                ['driver' => 'Logan Sargeant', 'team' => 'Williams', 'pos' => 20, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+20.795', 'laps' => 55],
            ]);
        }

        // Saudi Arabian Grand Prix 2024
        $grandPrix = GrandPrix::where('name', 'Saudi Arabian Grand Prix')->where('season', 2024)->first();
        if ($grandPrix) {
            $this->createResults($grandPrix->id, [
                ['driver' => 'Max Verstappen', 'team' => 'Red Bull Racing', 'pos' => 1, 'pts' => 25.0, 'fl' => false, 'pole' => true, 'dnf' => false, 'time' => '1:20:43.273', 'laps' => 50],
                ['driver' => 'Sergio Pérez', 'team' => 'Red Bull Racing', 'pos' => 2, 'pts' => 18.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+13.643', 'laps' => 50],
                ['driver' => 'Charles Leclerc', 'team' => 'Ferrari', 'pos' => 3, 'pts' => 16.0, 'fl' => true, 'pole' => false, 'dnf' => false, 'time' => '+18.639', 'laps' => 50],
                ['driver' => 'Oscar Piastri', 'team' => 'McLaren', 'pos' => 4, 'pts' => 12.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+32.007', 'laps' => 50],
                ['driver' => 'Fernando Alonso', 'team' => 'Aston Martin', 'pos' => 5, 'pts' => 10.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+35.759', 'laps' => 50],
                ['driver' => 'George Russell', 'team' => 'Mercedes-AMG Petronas', 'pos' => 6, 'pts' => 8.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+39.936', 'laps' => 50],
                ['driver' => 'Oliver Bearman', 'team' => 'Ferrari', 'pos' => 7, 'pts' => 6.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+42.679', 'laps' => 50],
                ['driver' => 'Lando Norris', 'team' => 'McLaren', 'pos' => 8, 'pts' => 4.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+45.708', 'laps' => 50],
                ['driver' => 'Lewis Hamilton', 'team' => 'Mercedes-AMG Petronas', 'pos' => 9, 'pts' => 2.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+47.391', 'laps' => 50],
                ['driver' => 'Nico Hülkenberg', 'team' => 'Haas', 'pos' => 10, 'pts' => 1.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:16.996', 'laps' => 50],
                ['driver' => 'Alexander Albon', 'team' => 'Williams', 'pos' => 11, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:28.354', 'laps' => 50],
                ['driver' => 'Kevin Magnussen', 'team' => 'Haas', 'pos' => 12, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:45.737', 'laps' => 50],
                ['driver' => 'Esteban Ocon', 'team' => 'Alpine', 'pos' => 13, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+4.001', 'laps' => 49],
                ['driver' => 'Logan Sargeant', 'team' => 'Williams', 'pos' => 14, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+6.785', 'laps' => 49],
                ['driver' => 'Yuki Tsunoda', 'team' => 'Visa Cash App RB', 'pos' => 15, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+10.533', 'laps' => 49],
                ['driver' => 'Daniel Ricciardo', 'team' => 'Visa Cash App RB', 'pos' => 16, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+20.715', 'laps' => 49],
                ['driver' => 'Valtteri Bottas', 'team' => 'Kick Sauber', 'pos' => 17, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+23.115', 'laps' => 49],
                ['driver' => 'Zhou Guanyu', 'team' => 'Kick Sauber', 'pos' => 18, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+29.553', 'laps' => 49],
                ['driver' => 'Lance Stroll', 'team' => 'Aston Martin', 'pos' => 19, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => 'Retired', 'laps' => 5],
                ['driver' => 'Pierre Gasly', 'team' => 'Alpine', 'pos' => 20, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => 'Retired', 'laps' => 1],
            ]);
        }

        // Australian Grand Prix 2024
        $grandPrix = GrandPrix::where('name', 'Australian Grand Prix')->where('season', 2024)->first();
        if ($grandPrix) {
            $this->createResults($grandPrix->id, [
                ['driver' => 'Carlos Sainz', 'team' => 'Ferrari', 'pos' => 1, 'pts' => 25.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '1:20:26.843', 'laps' => 58],
                ['driver' => 'Charles Leclerc', 'team' => 'Ferrari', 'pos' => 2, 'pts' => 19.0, 'fl' => true, 'pole' => false, 'dnf' => false, 'time' => '+2.366', 'laps' => 58],
                ['driver' => 'Lando Norris', 'team' => 'McLaren', 'pos' => 3, 'pts' => 15.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+5.904', 'laps' => 58],
                ['driver' => 'Oscar Piastri', 'team' => 'McLaren', 'pos' => 4, 'pts' => 12.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+35.770', 'laps' => 58],
                ['driver' => 'Sergio Pérez', 'team' => 'Red Bull Racing', 'pos' => 5, 'pts' => 10.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+56.309', 'laps' => 58],
                ['driver' => 'Lance Stroll', 'team' => 'Aston Martin', 'pos' => 6, 'pts' => 8.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:33.222', 'laps' => 58],
                ['driver' => 'Yuki Tsunoda', 'team' => 'Visa Cash App RB', 'pos' => 7, 'pts' => 6.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:35.601', 'laps' => 58],
                ['driver' => 'Fernando Alonso', 'team' => 'Aston Martin', 'pos' => 8, 'pts' => 4.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:40.992', 'laps' => 58],
                ['driver' => 'Nico Hülkenberg', 'team' => 'Haas', 'pos' => 9, 'pts' => 2.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:44.553', 'laps' => 58],
                ['driver' => 'Kevin Magnussen', 'team' => 'Haas', 'pos' => 10, 'pts' => 1.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+4.081', 'laps' => 57],
                ['driver' => 'Alexander Albon', 'team' => 'Williams', 'pos' => 11, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+5.009', 'laps' => 57],
                ['driver' => 'Daniel Ricciardo', 'team' => 'Visa Cash App RB', 'pos' => 12, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+11.508', 'laps' => 57],
                ['driver' => 'Pierre Gasly', 'team' => 'Alpine', 'pos' => 13, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+40.953', 'laps' => 57],
                ['driver' => 'Valtteri Bottas', 'team' => 'Kick Sauber', 'pos' => 14, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+42.326', 'laps' => 57],
                ['driver' => 'Zhou Guanyu', 'team' => 'Kick Sauber', 'pos' => 15, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+44.293', 'laps' => 57],
                ['driver' => 'Esteban Ocon', 'team' => 'Alpine', 'pos' => 16, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+53.979', 'laps' => 57],
                ['driver' => 'George Russell', 'team' => 'Mercedes-AMG Petronas', 'pos' => 17, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '', 'laps' => 56],
                ['driver' => 'Lewis Hamilton', 'team' => 'Mercedes-AMG Petronas', 'pos' => 18, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => 'Retired', 'laps' => 15],
                ['driver' => 'Max Verstappen', 'team' => 'Red Bull Racing', 'pos' => 19, 'pts' => 0.0, 'fl' => false, 'pole' => true, 'dnf' => true, 'time' => 'Retired', 'laps' => 3],
            ]);
        }

        // Japanese Grand Prix 2024
        $grandPrix = GrandPrix::where('name', 'Japanese Grand Prix')->where('season', 2024)->first();
        if ($grandPrix) {
            $this->createResults($grandPrix->id, [
                ['driver' => 'Max Verstappen', 'team' => 'Red Bull Racing', 'pos' => 1, 'pts' => 26.0, 'fl' => true, 'pole' => true, 'dnf' => false, 'time' => '1:54:23.566', 'laps' => 53],
                ['driver' => 'Sergio Pérez', 'team' => 'Red Bull Racing', 'pos' => 2, 'pts' => 18.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+12.535', 'laps' => 53],
                ['driver' => 'Carlos Sainz', 'team' => 'Ferrari', 'pos' => 3, 'pts' => 15.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+20.866', 'laps' => 53],
                ['driver' => 'Charles Leclerc', 'team' => 'Ferrari', 'pos' => 4, 'pts' => 12.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+26.522', 'laps' => 53],
                ['driver' => 'Lando Norris', 'team' => 'McLaren', 'pos' => 5, 'pts' => 10.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+29.700', 'laps' => 53],
                ['driver' => 'Fernando Alonso', 'team' => 'Aston Martin', 'pos' => 6, 'pts' => 8.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+44.272', 'laps' => 53],
                ['driver' => 'George Russell', 'team' => 'Mercedes-AMG Petronas', 'pos' => 7, 'pts' => 6.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+45.951', 'laps' => 53],
                ['driver' => 'Oscar Piastri', 'team' => 'McLaren', 'pos' => 8, 'pts' => 4.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+47.525', 'laps' => 53],
                ['driver' => 'Lewis Hamilton', 'team' => 'Mercedes-AMG Petronas', 'pos' => 9, 'pts' => 2.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+48.626', 'laps' => 53],
                ['driver' => 'Yuki Tsunoda', 'team' => 'Visa Cash App RB', 'pos' => 10, 'pts' => 1.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+1.602', 'laps' => 52],
                ['driver' => 'Nico Hülkenberg', 'team' => 'Haas', 'pos' => 11, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+7.168', 'laps' => 52],
                ['driver' => 'Lance Stroll', 'team' => 'Aston Martin', 'pos' => 12, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+11.233', 'laps' => 52],
                ['driver' => 'Kevin Magnussen', 'team' => 'Haas', 'pos' => 13, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+17.919', 'laps' => 52],
                ['driver' => 'Valtteri Bottas', 'team' => 'Kick Sauber', 'pos' => 14, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+18.893', 'laps' => 52],
                ['driver' => 'Esteban Ocon', 'team' => 'Alpine', 'pos' => 15, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+41.152', 'laps' => 52],
                ['driver' => 'Pierre Gasly', 'team' => 'Alpine', 'pos' => 16, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+55.066', 'laps' => 52],
                ['driver' => 'Logan Sargeant', 'team' => 'Williams', 'pos' => 17, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+1:12.765', 'laps' => 52],
                ['driver' => 'Zhou Guanyu', 'team' => 'Kick Sauber', 'pos' => 18, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => 'Retired', 'laps' => 12],
                ['driver' => 'Daniel Ricciardo', 'team' => 'Visa Cash App RB', 'pos' => 19, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => 'Retired', 'laps' => 0],
                ['driver' => 'Alexander Albon', 'team' => 'Williams', 'pos' => 20, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => 'Retired', 'laps' => 0],
            ]);
        }

        // Chinese Grand Prix 2024
        $grandPrix = GrandPrix::where('name', 'Chinese Grand Prix')->where('season', 2024)->first();
        if ($grandPrix) {
            $this->createResults($grandPrix->id, [
                ['driver' => 'Max Verstappen', 'team' => 'Red Bull Racing', 'pos' => 1, 'pts' => 25.0, 'fl' => false, 'pole' => true, 'dnf' => false, 'time' => '1:40:52.554', 'laps' => 56],
                ['driver' => 'Lando Norris', 'team' => 'McLaren', 'pos' => 2, 'pts' => 18.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+13.773', 'laps' => 56],
                ['driver' => 'Sergio Pérez', 'team' => 'Red Bull Racing', 'pos' => 3, 'pts' => 15.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+19.160', 'laps' => 56],
                ['driver' => 'Charles Leclerc', 'team' => 'Ferrari', 'pos' => 4, 'pts' => 12.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+23.623', 'laps' => 56],
                ['driver' => 'Carlos Sainz', 'team' => 'Ferrari', 'pos' => 5, 'pts' => 10.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+33.983', 'laps' => 56],
                ['driver' => 'George Russell', 'team' => 'Mercedes-AMG Petronas', 'pos' => 6, 'pts' => 8.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+38.724', 'laps' => 56],
                ['driver' => 'Fernando Alonso', 'team' => 'Aston Martin', 'pos' => 7, 'pts' => 7.0, 'fl' => true, 'pole' => false, 'dnf' => false, 'time' => '+43.414', 'laps' => 56],
                ['driver' => 'Oscar Piastri', 'team' => 'McLaren', 'pos' => 8, 'pts' => 4.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+56.198', 'laps' => 56],
                ['driver' => 'Lewis Hamilton', 'team' => 'Mercedes-AMG Petronas', 'pos' => 9, 'pts' => 2.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+57.986', 'laps' => 56],
                ['driver' => 'Nico Hülkenberg', 'team' => 'Haas', 'pos' => 10, 'pts' => 1.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:00.476', 'laps' => 56],
                ['driver' => 'Esteban Ocon', 'team' => 'Alpine', 'pos' => 11, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:02.812', 'laps' => 56],
                ['driver' => 'Alexander Albon', 'team' => 'Williams', 'pos' => 12, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:05.506', 'laps' => 56],
                ['driver' => 'Pierre Gasly', 'team' => 'Alpine', 'pos' => 13, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:09.223', 'laps' => 56],
                ['driver' => 'Zhou Guanyu', 'team' => 'Kick Sauber', 'pos' => 14, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:11.689', 'laps' => 56],
                ['driver' => 'Lance Stroll', 'team' => 'Aston Martin', 'pos' => 15, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:22.786', 'laps' => 56],
                ['driver' => 'Kevin Magnussen', 'team' => 'Haas', 'pos' => 16, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:27.533', 'laps' => 56],
                ['driver' => 'Logan Sargeant', 'team' => 'Williams', 'pos' => 17, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:35.110', 'laps' => 56],
                ['driver' => 'Daniel Ricciardo', 'team' => 'Visa Cash App RB', 'pos' => 18, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => 'Retired', 'laps' => 33],
                ['driver' => 'Yuki Tsunoda', 'team' => 'Visa Cash App RB', 'pos' => 19, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => 'Retired', 'laps' => 26],
                ['driver' => 'Valtteri Bottas', 'team' => 'Kick Sauber', 'pos' => 20, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => 'Retired', 'laps' => 19],
            ]);
        }

        // Miami Grand Prix 2024
        $grandPrix = GrandPrix::where('name', 'Miami Grand Prix')->where('season', 2024)->first();
        if ($grandPrix) {
            $this->createResults($grandPrix->id, [
                ['driver' => 'Lando Norris', 'team' => 'McLaren', 'pos' => 1, 'pts' => 25.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '1:30:49.876', 'laps' => 57],
            ]);
        }

        // Miami Grand Prix 2024
        $grandPrix = GrandPrix::where('name', 'Miami Grand Prix')->where('season', 2024)->first();
        if ($grandPrix) {
            $this->createResults($grandPrix->id, [
                ['driver' => 'Max Verstappen', 'team' => 'Red Bull Racing', 'pos' => 2, 'pts' => 18.0, 'fl' => false, 'pole' => true, 'dnf' => false, 'time' => '+7.612', 'laps' => 57],
                ['driver' => 'Charles Leclerc', 'team' => 'Ferrari', 'pos' => 3, 'pts' => 15.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+9.920', 'laps' => 57],
                ['driver' => 'Sergio Pérez', 'team' => 'Red Bull Racing', 'pos' => 4, 'pts' => 12.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+14.650', 'laps' => 57],
                ['driver' => 'Carlos Sainz', 'team' => 'Ferrari', 'pos' => 5, 'pts' => 10.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+16.407', 'laps' => 57],
                ['driver' => 'Lewis Hamilton', 'team' => 'Mercedes-AMG Petronas', 'pos' => 6, 'pts' => 8.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+16.585', 'laps' => 57],
                ['driver' => 'Yuki Tsunoda', 'team' => 'Visa Cash App RB', 'pos' => 7, 'pts' => 6.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+26.185', 'laps' => 57],
                ['driver' => 'George Russell', 'team' => 'Mercedes-AMG Petronas', 'pos' => 8, 'pts' => 4.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+34.789', 'laps' => 57],
                ['driver' => 'Fernando Alonso', 'team' => 'Aston Martin', 'pos' => 9, 'pts' => 2.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+37.107', 'laps' => 57],
                ['driver' => 'Esteban Ocon', 'team' => 'Alpine', 'pos' => 10, 'pts' => 1.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+39.746', 'laps' => 57],
                ['driver' => 'Nico Hülkenberg', 'team' => 'Haas', 'pos' => 11, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+40.789', 'laps' => 57],
                ['driver' => 'Pierre Gasly', 'team' => 'Alpine', 'pos' => 12, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+44.958', 'laps' => 57],
                ['driver' => 'Oscar Piastri', 'team' => 'McLaren', 'pos' => 13, 'pts' => 0.0, 'fl' => true, 'pole' => false, 'dnf' => false, 'time' => '+49.756', 'laps' => 57],
                ['driver' => 'Zhou Guanyu', 'team' => 'Kick Sauber', 'pos' => 14, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+49.979', 'laps' => 57],
                ['driver' => 'Daniel Ricciardo', 'team' => 'Visa Cash App RB', 'pos' => 15, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+50.956', 'laps' => 57],
                ['driver' => 'Valtteri Bottas', 'team' => 'Kick Sauber', 'pos' => 16, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+52.356', 'laps' => 57],
                ['driver' => 'Lance Stroll', 'team' => 'Aston Martin', 'pos' => 17, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+55.173', 'laps' => 57],
                ['driver' => 'Alexander Albon', 'team' => 'Williams', 'pos' => 18, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:16.091', 'laps' => 57],
                ['driver' => 'Kevin Magnussen', 'team' => 'Haas', 'pos' => 19, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:24.683', 'laps' => 57],
                ['driver' => 'Logan Sargeant', 'team' => 'Williams', 'pos' => 20, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => 'Retired', 'laps' => 27],
            ]);
        }

        // Emilia Romagna Grand Prix 2024
        $grandPrix = GrandPrix::where('name', 'Emilia Romagna Grand Prix')->where('season', 2024)->first();
        if ($grandPrix) {
            $this->createResults($grandPrix->id, [
                ['driver' => 'Max Verstappen', 'team' => 'Red Bull Racing', 'pos' => 1, 'pts' => 25.0, 'fl' => false, 'pole' => true, 'dnf' => false, 'time' => '1:25:25.252', 'laps' => 63],
                ['driver' => 'Lando Norris', 'team' => 'McLaren', 'pos' => 2, 'pts' => 18.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+0.725', 'laps' => 63],
                ['driver' => 'Charles Leclerc', 'team' => 'Ferrari', 'pos' => 3, 'pts' => 15.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+7.916', 'laps' => 63],
                ['driver' => 'Oscar Piastri', 'team' => 'McLaren', 'pos' => 4, 'pts' => 12.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+14.132', 'laps' => 63],
                ['driver' => 'Carlos Sainz', 'team' => 'Ferrari', 'pos' => 5, 'pts' => 10.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+22.325', 'laps' => 63],
                ['driver' => 'Lewis Hamilton', 'team' => 'Mercedes-AMG Petronas', 'pos' => 6, 'pts' => 8.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+35.104', 'laps' => 63],
                ['driver' => 'George Russell', 'team' => 'Mercedes-AMG Petronas', 'pos' => 7, 'pts' => 7.0, 'fl' => true, 'pole' => false, 'dnf' => false, 'time' => '+47.154', 'laps' => 63],
                ['driver' => 'Sergio Pérez', 'team' => 'Red Bull Racing', 'pos' => 8, 'pts' => 4.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+54.776', 'laps' => 63],
                ['driver' => 'Lance Stroll', 'team' => 'Aston Martin', 'pos' => 9, 'pts' => 2.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:19.556', 'laps' => 63],
                ['driver' => 'Yuki Tsunoda', 'team' => 'Visa Cash App RB', 'pos' => 10, 'pts' => 1.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+17.856', 'laps' => 62],
                ['driver' => 'Nico Hülkenberg', 'team' => 'Haas', 'pos' => 11, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+25.277', 'laps' => 62],
                ['driver' => 'Kevin Magnussen', 'team' => 'Haas', 'pos' => 12, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+26.434', 'laps' => 62],
                ['driver' => 'Daniel Ricciardo', 'team' => 'Visa Cash App RB', 'pos' => 13, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+27.661', 'laps' => 62],
                ['driver' => 'Esteban Ocon', 'team' => 'Alpine', 'pos' => 14, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+43.907', 'laps' => 62],
                ['driver' => 'Zhou Guanyu', 'team' => 'Kick Sauber', 'pos' => 15, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+44.933', 'laps' => 62],
                ['driver' => 'Pierre Gasly', 'team' => 'Alpine', 'pos' => 16, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+49.715', 'laps' => 62],
                ['driver' => 'Logan Sargeant', 'team' => 'Williams', 'pos' => 17, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+51.051', 'laps' => 62],
                ['driver' => 'Valtteri Bottas', 'team' => 'Kick Sauber', 'pos' => 18, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+56.343', 'laps' => 62],
                ['driver' => 'Fernando Alonso', 'team' => 'Aston Martin', 'pos' => 19, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+1:15.124', 'laps' => 62],
                ['driver' => 'Alexander Albon', 'team' => 'Williams', 'pos' => 20, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => 'Retired', 'laps' => 51],
            ]);
        }

        // Monaco Grand Prix 2024
        $grandPrix = GrandPrix::where('name', 'Monaco Grand Prix')->where('season', 2024)->first();
        if ($grandPrix) {
            $this->createResults($grandPrix->id, [
                ['driver' => 'Charles Leclerc', 'team' => 'Ferrari', 'pos' => 1, 'pts' => 25.0, 'fl' => false, 'pole' => true, 'dnf' => false, 'time' => '2:23:15.554', 'laps' => 78],
                ['driver' => 'Oscar Piastri', 'team' => 'McLaren', 'pos' => 2, 'pts' => 18.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+7.152', 'laps' => 78],
                ['driver' => 'Carlos Sainz', 'team' => 'Ferrari', 'pos' => 3, 'pts' => 15.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+7.585', 'laps' => 78],
                ['driver' => 'Lando Norris', 'team' => 'McLaren', 'pos' => 4, 'pts' => 12.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+8.650', 'laps' => 78],
                ['driver' => 'George Russell', 'team' => 'Mercedes-AMG Petronas', 'pos' => 5, 'pts' => 10.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+13.309', 'laps' => 78],
                ['driver' => 'Max Verstappen', 'team' => 'Red Bull Racing', 'pos' => 6, 'pts' => 8.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+13.853', 'laps' => 78],
                ['driver' => 'Lewis Hamilton', 'team' => 'Mercedes-AMG Petronas', 'pos' => 7, 'pts' => 7.0, 'fl' => true, 'pole' => false, 'dnf' => false, 'time' => '+14.908', 'laps' => 78],
                ['driver' => 'Yuki Tsunoda', 'team' => 'Visa Cash App RB', 'pos' => 8, 'pts' => 4.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+39.487', 'laps' => 77],
                ['driver' => 'Alexander Albon', 'team' => 'Williams', 'pos' => 9, 'pts' => 2.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+54.052', 'laps' => 77],
                ['driver' => 'Pierre Gasly', 'team' => 'Alpine', 'pos' => 10, 'pts' => 1.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+1:00.241', 'laps' => 77],
                ['driver' => 'Fernando Alonso', 'team' => 'Aston Martin', 'pos' => 11, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+3.854', 'laps' => 76],
                ['driver' => 'Daniel Ricciardo', 'team' => 'Visa Cash App RB', 'pos' => 12, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+4.264', 'laps' => 76],
                ['driver' => 'Valtteri Bottas', 'team' => 'Kick Sauber', 'pos' => 13, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+4.488', 'laps' => 76],
                ['driver' => 'Lance Stroll', 'team' => 'Aston Martin', 'pos' => 14, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+5.967', 'laps' => 76],
                ['driver' => 'Logan Sargeant', 'team' => 'Williams', 'pos' => 15, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+9.026', 'laps' => 76],
                ['driver' => 'Zhou Guanyu', 'team' => 'Kick Sauber', 'pos' => 16, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+55.260', 'laps' => 76],
                ['driver' => 'Esteban Ocon', 'team' => 'Alpine', 'pos' => 17, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => 'Retired', 'laps' => 0],
                ['driver' => 'Sergio Pérez', 'team' => 'Red Bull Racing', 'pos' => 18, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => 'Retired', 'laps' => 0],
                ['driver' => 'Nico Hülkenberg', 'team' => 'Haas', 'pos' => 19, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => 'Retired', 'laps' => 0],
                ['driver' => 'Kevin Magnussen', 'team' => 'Haas', 'pos' => 20, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => 'Retired', 'laps' => 0],
            ]);
        }

        // Canadian Grand Prix 2024
        $grandPrix = GrandPrix::where('name', 'Canadian Grand Prix')->where('season', 2024)->first();
        if ($grandPrix) {
            $this->createResults($grandPrix->id, [
                ['driver' => 'Max Verstappen', 'team' => 'Red Bull Racing', 'pos' => 1, 'pts' => 25.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '1:45:47.927', 'laps' => 70],
                ['driver' => 'Lando Norris', 'team' => 'McLaren', 'pos' => 2, 'pts' => 18.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+3.879', 'laps' => 70],
                ['driver' => 'George Russell', 'team' => 'Mercedes-AMG Petronas', 'pos' => 3, 'pts' => 15.0, 'fl' => false, 'pole' => true, 'dnf' => false, 'time' => '+4.317', 'laps' => 70],
                ['driver' => 'Lewis Hamilton', 'team' => 'Mercedes-AMG Petronas', 'pos' => 4, 'pts' => 13.0, 'fl' => true, 'pole' => false, 'dnf' => false, 'time' => '+4.915', 'laps' => 70],
                ['driver' => 'Oscar Piastri', 'team' => 'McLaren', 'pos' => 5, 'pts' => 10.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+10.199', 'laps' => 70],
                ['driver' => 'Fernando Alonso', 'team' => 'Aston Martin', 'pos' => 6, 'pts' => 8.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+17.510', 'laps' => 70],
                ['driver' => 'Lance Stroll', 'team' => 'Aston Martin', 'pos' => 7, 'pts' => 6.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+23.625', 'laps' => 70],
                ['driver' => 'Daniel Ricciardo', 'team' => 'Visa Cash App RB', 'pos' => 8, 'pts' => 4.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+28.672', 'laps' => 70],
                ['driver' => 'Pierre Gasly', 'team' => 'Alpine', 'pos' => 9, 'pts' => 2.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+30.021', 'laps' => 70],
                ['driver' => 'Esteban Ocon', 'team' => 'Alpine', 'pos' => 10, 'pts' => 1.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+30.313', 'laps' => 70],
                ['driver' => 'Nico Hülkenberg', 'team' => 'Haas', 'pos' => 11, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+30.824', 'laps' => 70],
                ['driver' => 'Kevin Magnussen', 'team' => 'Haas', 'pos' => 12, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+31.253', 'laps' => 70],
                ['driver' => 'Valtteri Bottas', 'team' => 'Kick Sauber', 'pos' => 13, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+40.487', 'laps' => 70],
                ['driver' => 'Yuki Tsunoda', 'team' => 'Visa Cash App RB', 'pos' => 14, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+52.694', 'laps' => 70],
                ['driver' => 'Zhou Guanyu', 'team' => 'Kick Sauber', 'pos' => 15, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+53.528', 'laps' => 69],
                ['driver' => 'Carlos Sainz', 'team' => 'Ferrari', 'pos' => 16, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => 'Retired', 'laps' => 52],
                ['driver' => 'Alexander Albon', 'team' => 'Williams', 'pos' => 17, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => 'Retired', 'laps' => 52],
                ['driver' => 'Sergio Pérez', 'team' => 'Red Bull Racing', 'pos' => 18, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => 'Retired', 'laps' => 51],
                ['driver' => 'Charles Leclerc', 'team' => 'Ferrari', 'pos' => 19, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => 'Retired', 'laps' => 40],
                ['driver' => 'Logan Sargeant', 'team' => 'Williams', 'pos' => 20, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => 'Retired', 'laps' => 23],
            ]);
        }

        // Spanish Grand Prix 2024
        $grandPrix = GrandPrix::where('name', 'Spanish Grand Prix')->where('season', 2024)->first();
        if ($grandPrix) {
            $this->createResults($grandPrix->id, [
                ['driver' => 'Max Verstappen', 'team' => 'Red Bull Racing', 'pos' => 1, 'pts' => 25.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '1:28:20.227', 'laps' => 66],
                ['driver' => 'Lando Norris', 'team' => 'McLaren', 'pos' => 2, 'pts' => 19.0, 'fl' => true, 'pole' => true, 'dnf' => false, 'time' => '+2.219', 'laps' => 66],
                ['driver' => 'Lewis Hamilton', 'team' => 'Mercedes-AMG Petronas', 'pos' => 3, 'pts' => 15.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+17.790', 'laps' => 66],
                ['driver' => 'George Russell', 'team' => 'Mercedes-AMG Petronas', 'pos' => 4, 'pts' => 12.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+22.320', 'laps' => 66],
                ['driver' => 'Charles Leclerc', 'team' => 'Ferrari', 'pos' => 5, 'pts' => 10.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+22.709', 'laps' => 66],
                ['driver' => 'Carlos Sainz', 'team' => 'Ferrari', 'pos' => 6, 'pts' => 8.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+31.028', 'laps' => 66],
                ['driver' => 'Oscar Piastri', 'team' => 'McLaren', 'pos' => 7, 'pts' => 6.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+33.760', 'laps' => 66],
                ['driver' => 'Sergio Pérez', 'team' => 'Red Bull Racing', 'pos' => 8, 'pts' => 4.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+59.524', 'laps' => 66],
                ['driver' => 'Pierre Gasly', 'team' => 'Alpine', 'pos' => 9, 'pts' => 2.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:02.025', 'laps' => 66],
                ['driver' => 'Esteban Ocon', 'team' => 'Alpine', 'pos' => 10, 'pts' => 1.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:11.889', 'laps' => 66],
                ['driver' => 'Nico Hülkenberg', 'team' => 'Haas', 'pos' => 11, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:19.215', 'laps' => 66],
                ['driver' => 'Fernando Alonso', 'team' => 'Aston Martin', 'pos' => 12, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+9.497', 'laps' => 65],
                ['driver' => 'Zhou Guanyu', 'team' => 'Kick Sauber', 'pos' => 13, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+15.476', 'laps' => 65],
                ['driver' => 'Lance Stroll', 'team' => 'Aston Martin', 'pos' => 14, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+25.252', 'laps' => 65],
                ['driver' => 'Daniel Ricciardo', 'team' => 'Visa Cash App RB', 'pos' => 15, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+44.050', 'laps' => 65],
                ['driver' => 'Valtteri Bottas', 'team' => 'Kick Sauber', 'pos' => 16, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+53.313', 'laps' => 65],
                ['driver' => 'Kevin Magnussen', 'team' => 'Haas', 'pos' => 17, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+54.788', 'laps' => 65],
                ['driver' => 'Alexander Albon', 'team' => 'Williams', 'pos' => 18, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+58.149', 'laps' => 65],
                ['driver' => 'Yuki Tsunoda', 'team' => 'Visa Cash App RB', 'pos' => 19, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+1:08.904', 'laps' => 65],
                ['driver' => 'Logan Sargeant', 'team' => 'Williams', 'pos' => 20, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+4.866', 'laps' => 64],
            ]);
        }

        // Austrian Grand Prix 2024
        $grandPrix = GrandPrix::where('name', 'Austrian Grand Prix')->where('season', 2024)->first();
        if ($grandPrix) {
            $this->createResults($grandPrix->id, [
                ['driver' => 'George Russell', 'team' => 'Mercedes-AMG Petronas', 'pos' => 1, 'pts' => 25.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '1:24:22.798', 'laps' => 71],
            ]);
        }

        // Austrian Grand Prix 2024
        $grandPrix = GrandPrix::where('name', 'Austrian Grand Prix')->where('season', 2024)->first();
        if ($grandPrix) {
            $this->createResults($grandPrix->id, [
                ['driver' => 'Oscar Piastri', 'team' => 'McLaren', 'pos' => 2, 'pts' => 18.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1.906', 'laps' => 71],
                ['driver' => 'Carlos Sainz', 'team' => 'Ferrari', 'pos' => 3, 'pts' => 15.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+4.533', 'laps' => 71],
                ['driver' => 'Lewis Hamilton', 'team' => 'Mercedes-AMG Petronas', 'pos' => 4, 'pts' => 12.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+23.142', 'laps' => 71],
                ['driver' => 'Max Verstappen', 'team' => 'Red Bull Racing', 'pos' => 5, 'pts' => 10.0, 'fl' => false, 'pole' => true, 'dnf' => false, 'time' => '+37.253', 'laps' => 71],
                ['driver' => 'Nico Hülkenberg', 'team' => 'Haas', 'pos' => 6, 'pts' => 8.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+54.088', 'laps' => 71],
                ['driver' => 'Sergio Pérez', 'team' => 'Red Bull Racing', 'pos' => 7, 'pts' => 6.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+54.672', 'laps' => 71],
                ['driver' => 'Kevin Magnussen', 'team' => 'Haas', 'pos' => 8, 'pts' => 4.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:00.355', 'laps' => 71],
                ['driver' => 'Daniel Ricciardo', 'team' => 'Visa Cash App RB', 'pos' => 9, 'pts' => 2.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:01.169', 'laps' => 71],
                ['driver' => 'Pierre Gasly', 'team' => 'Alpine', 'pos' => 10, 'pts' => 1.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:01.766', 'laps' => 71],
                ['driver' => 'Charles Leclerc', 'team' => 'Ferrari', 'pos' => 11, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:07.056', 'laps' => 71],
                ['driver' => 'Esteban Ocon', 'team' => 'Alpine', 'pos' => 12, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:08.325', 'laps' => 71],
                ['driver' => 'Lance Stroll', 'team' => 'Aston Martin', 'pos' => 13, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+10.234', 'laps' => 70],
                ['driver' => 'Yuki Tsunoda', 'team' => 'Visa Cash App RB', 'pos' => 14, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+13.145', 'laps' => 70],
                ['driver' => 'Alexander Albon', 'team' => 'Williams', 'pos' => 15, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+15.866', 'laps' => 70],
                ['driver' => 'Valtteri Bottas', 'team' => 'Kick Sauber', 'pos' => 16, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+19.375', 'laps' => 70],
                ['driver' => 'Zhou Guanyu', 'team' => 'Kick Sauber', 'pos' => 17, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+44.882', 'laps' => 70],
                ['driver' => 'Fernando Alonso', 'team' => 'Aston Martin', 'pos' => 18, 'pts' => 0.0, 'fl' => true, 'pole' => false, 'dnf' => true, 'time' => '+47.660', 'laps' => 70],
                ['driver' => 'Logan Sargeant', 'team' => 'Williams', 'pos' => 19, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+6.309', 'laps' => 69],
                ['driver' => 'Lando Norris', 'team' => 'McLaren', 'pos' => 20, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '', 'laps' => 64],
            ]);
        }

        // British Grand Prix 2024
        $grandPrix = GrandPrix::where('name', 'British Grand Prix')->where('season', 2024)->first();
        if ($grandPrix) {
            $this->createResults($grandPrix->id, [
                ['driver' => 'Lewis Hamilton', 'team' => 'Mercedes-AMG Petronas', 'pos' => 1, 'pts' => 25.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '1:22:27.059', 'laps' => 52],
                ['driver' => 'Max Verstappen', 'team' => 'Red Bull Racing', 'pos' => 2, 'pts' => 18.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1.465', 'laps' => 52],
                ['driver' => 'Lando Norris', 'team' => 'McLaren', 'pos' => 3, 'pts' => 15.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+7.547', 'laps' => 52],
                ['driver' => 'Oscar Piastri', 'team' => 'McLaren', 'pos' => 4, 'pts' => 12.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+12.429', 'laps' => 52],
                ['driver' => 'Carlos Sainz', 'team' => 'Ferrari', 'pos' => 5, 'pts' => 11.0, 'fl' => true, 'pole' => false, 'dnf' => false, 'time' => '+47.318', 'laps' => 52],
                ['driver' => 'Nico Hülkenberg', 'team' => 'Haas', 'pos' => 6, 'pts' => 8.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+55.722', 'laps' => 52],
                ['driver' => 'Lance Stroll', 'team' => 'Aston Martin', 'pos' => 7, 'pts' => 6.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+56.569', 'laps' => 52],
                ['driver' => 'Fernando Alonso', 'team' => 'Aston Martin', 'pos' => 8, 'pts' => 4.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:03.577', 'laps' => 52],
                ['driver' => 'Alexander Albon', 'team' => 'Williams', 'pos' => 9, 'pts' => 2.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:08.387', 'laps' => 52],
                ['driver' => 'Yuki Tsunoda', 'team' => 'Visa Cash App RB', 'pos' => 10, 'pts' => 1.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:19.303', 'laps' => 52],
                ['driver' => 'Logan Sargeant', 'team' => 'Williams', 'pos' => 11, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:28.960', 'laps' => 52],
                ['driver' => 'Kevin Magnussen', 'team' => 'Haas', 'pos' => 12, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:30.153', 'laps' => 52],
                ['driver' => 'Daniel Ricciardo', 'team' => 'Visa Cash App RB', 'pos' => 13, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+9.937', 'laps' => 51],
                ['driver' => 'Charles Leclerc', 'team' => 'Ferrari', 'pos' => 14, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+40.473', 'laps' => 51],
                ['driver' => 'Valtteri Bottas', 'team' => 'Kick Sauber', 'pos' => 15, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+41.821', 'laps' => 51],
                ['driver' => 'Esteban Ocon', 'team' => 'Alpine', 'pos' => 16, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+10.682', 'laps' => 50],
                ['driver' => 'Sergio Pérez', 'team' => 'Red Bull Racing', 'pos' => 17, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+18.005', 'laps' => 50],
                ['driver' => 'Zhou Guanyu', 'team' => 'Kick Sauber', 'pos' => 18, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+54.476', 'laps' => 50],
                ['driver' => 'George Russell', 'team' => 'Mercedes-AMG Petronas', 'pos' => 19, 'pts' => 0.0, 'fl' => false, 'pole' => true, 'dnf' => true, 'time' => 'Retired', 'laps' => 33],
                ['driver' => 'Pierre Gasly', 'team' => 'Alpine', 'pos' => 20, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => 'Did not start', 'laps' => 0],
            ]);
        }

        // Hungarian Grand Prix 2024
        $grandPrix = GrandPrix::where('name', 'Hungarian Grand Prix')->where('season', 2024)->first();
        if ($grandPrix) {
            $this->createResults($grandPrix->id, [
                ['driver' => 'Oscar Piastri', 'team' => 'McLaren', 'pos' => 1, 'pts' => 25.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '1:38:01.989', 'laps' => 70],
                ['driver' => 'Lando Norris', 'team' => 'McLaren', 'pos' => 2, 'pts' => 18.0, 'fl' => false, 'pole' => true, 'dnf' => false, 'time' => '+2.141', 'laps' => 70],
                ['driver' => 'Lewis Hamilton', 'team' => 'Mercedes-AMG Petronas', 'pos' => 3, 'pts' => 15.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+14.880', 'laps' => 70],
                ['driver' => 'Charles Leclerc', 'team' => 'Ferrari', 'pos' => 4, 'pts' => 12.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+19.686', 'laps' => 70],
                ['driver' => 'Max Verstappen', 'team' => 'Red Bull Racing', 'pos' => 5, 'pts' => 10.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+21.349', 'laps' => 70],
                ['driver' => 'Carlos Sainz', 'team' => 'Ferrari', 'pos' => 6, 'pts' => 8.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+23.073', 'laps' => 70],
                ['driver' => 'Sergio Pérez', 'team' => 'Red Bull Racing', 'pos' => 7, 'pts' => 6.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+39.792', 'laps' => 70],
                ['driver' => 'George Russell', 'team' => 'Mercedes-AMG Petronas', 'pos' => 8, 'pts' => 5.0, 'fl' => true, 'pole' => false, 'dnf' => false, 'time' => '+42.368', 'laps' => 70],
                ['driver' => 'Yuki Tsunoda', 'team' => 'Visa Cash App RB', 'pos' => 9, 'pts' => 2.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:17.259', 'laps' => 70],
                ['driver' => 'Lance Stroll', 'team' => 'Aston Martin', 'pos' => 10, 'pts' => 1.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:17.976', 'laps' => 70],
                ['driver' => 'Fernando Alonso', 'team' => 'Aston Martin', 'pos' => 11, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:22.460', 'laps' => 70],
                ['driver' => 'Daniel Ricciardo', 'team' => 'Visa Cash App RB', 'pos' => 12, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+17.924', 'laps' => 69],
                ['driver' => 'Nico Hülkenberg', 'team' => 'Haas', 'pos' => 13, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+33.184', 'laps' => 69],
                ['driver' => 'Alexander Albon', 'team' => 'Williams', 'pos' => 14, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+36.769', 'laps' => 69],
                ['driver' => 'Kevin Magnussen', 'team' => 'Haas', 'pos' => 15, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+45.302', 'laps' => 69],
                ['driver' => 'Valtteri Bottas', 'team' => 'Kick Sauber', 'pos' => 16, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+45.409', 'laps' => 69],
                ['driver' => 'Logan Sargeant', 'team' => 'Williams', 'pos' => 17, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+52.591', 'laps' => 69],
                ['driver' => 'Esteban Ocon', 'team' => 'Alpine', 'pos' => 18, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+1:00.929', 'laps' => 69],
                ['driver' => 'Zhou Guanyu', 'team' => 'Kick Sauber', 'pos' => 19, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+1:03.598', 'laps' => 69],
                ['driver' => 'Pierre Gasly', 'team' => 'Alpine', 'pos' => 20, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => 'Retired', 'laps' => 33],
            ]);
        }

        // Belgian Grand Prix 2024
        $grandPrix = GrandPrix::where('name', 'Belgian Grand Prix')->where('season', 2024)->first();
        if ($grandPrix) {
            $this->createResults($grandPrix->id, [
                ['driver' => 'Lewis Hamilton', 'team' => 'Mercedes-AMG Petronas', 'pos' => 1, 'pts' => 25.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '1:19:57.566', 'laps' => 44],
                ['driver' => 'Oscar Piastri', 'team' => 'McLaren', 'pos' => 2, 'pts' => 18.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+0.647', 'laps' => 44],
                ['driver' => 'Charles Leclerc', 'team' => 'Ferrari', 'pos' => 3, 'pts' => 15.0, 'fl' => false, 'pole' => true, 'dnf' => false, 'time' => '+8.023', 'laps' => 44],
                ['driver' => 'Max Verstappen', 'team' => 'Red Bull Racing', 'pos' => 4, 'pts' => 12.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+8.700', 'laps' => 44],
                ['driver' => 'Lando Norris', 'team' => 'McLaren', 'pos' => 5, 'pts' => 10.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+9.324', 'laps' => 44],
                ['driver' => 'Carlos Sainz', 'team' => 'Ferrari', 'pos' => 6, 'pts' => 8.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+19.269', 'laps' => 44],
                ['driver' => 'Sergio Pérez', 'team' => 'Red Bull Racing', 'pos' => 7, 'pts' => 7.0, 'fl' => true, 'pole' => false, 'dnf' => false, 'time' => '+42.669', 'laps' => 44],
                ['driver' => 'Fernando Alonso', 'team' => 'Aston Martin', 'pos' => 8, 'pts' => 4.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+49.437', 'laps' => 44],
                ['driver' => 'Esteban Ocon', 'team' => 'Alpine', 'pos' => 9, 'pts' => 2.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+52.026', 'laps' => 44],
                ['driver' => 'Daniel Ricciardo', 'team' => 'Visa Cash App RB', 'pos' => 10, 'pts' => 1.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+54.400', 'laps' => 44],
                ['driver' => 'Lance Stroll', 'team' => 'Aston Martin', 'pos' => 11, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:02.485', 'laps' => 44],
                ['driver' => 'Alexander Albon', 'team' => 'Williams', 'pos' => 12, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:03.125', 'laps' => 44],
                ['driver' => 'Pierre Gasly', 'team' => 'Alpine', 'pos' => 13, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:03.839', 'laps' => 44],
                ['driver' => 'Kevin Magnussen', 'team' => 'Haas', 'pos' => 14, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:06.105', 'laps' => 44],
                ['driver' => 'Valtteri Bottas', 'team' => 'Kick Sauber', 'pos' => 15, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:10.112', 'laps' => 44],
                ['driver' => 'Yuki Tsunoda', 'team' => 'Visa Cash App RB', 'pos' => 16, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:16.211', 'laps' => 44],
                ['driver' => 'Logan Sargeant', 'team' => 'Williams', 'pos' => 17, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:25.531', 'laps' => 44],
                ['driver' => 'Nico Hülkenberg', 'team' => 'Haas', 'pos' => 18, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:28.307', 'laps' => 44],
                ['driver' => 'Zhou Guanyu', 'team' => 'Kick Sauber', 'pos' => 19, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => 'Retired', 'laps' => 5],
                ['driver' => 'George Russell', 'team' => 'Mercedes-AMG Petronas', 'pos' => 20, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '', 'laps' => 44],
            ]);
        }

        // Dutch Grand Prix 2024
        $grandPrix = GrandPrix::where('name', 'Dutch Grand Prix')->where('season', 2024)->first();
        if ($grandPrix) {
            $this->createResults($grandPrix->id, [
                ['driver' => 'Lando Norris', 'team' => 'McLaren', 'pos' => 1, 'pts' => 26.0, 'fl' => true, 'pole' => true, 'dnf' => false, 'time' => '1:30:45.519', 'laps' => 72],
                ['driver' => 'Max Verstappen', 'team' => 'Red Bull Racing', 'pos' => 2, 'pts' => 18.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+22.896', 'laps' => 72],
                ['driver' => 'Charles Leclerc', 'team' => 'Ferrari', 'pos' => 3, 'pts' => 15.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+25.439', 'laps' => 72],
                ['driver' => 'Oscar Piastri', 'team' => 'McLaren', 'pos' => 4, 'pts' => 12.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+27.337', 'laps' => 72],
                ['driver' => 'Carlos Sainz', 'team' => 'Ferrari', 'pos' => 5, 'pts' => 10.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+32.137', 'laps' => 72],
                ['driver' => 'Sergio Pérez', 'team' => 'Red Bull Racing', 'pos' => 6, 'pts' => 8.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+39.542', 'laps' => 72],
                ['driver' => 'George Russell', 'team' => 'Mercedes-AMG Petronas', 'pos' => 7, 'pts' => 6.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+44.617', 'laps' => 72],
                ['driver' => 'Lewis Hamilton', 'team' => 'Mercedes-AMG Petronas', 'pos' => 8, 'pts' => 4.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+49.599', 'laps' => 72],
                ['driver' => 'Pierre Gasly', 'team' => 'Alpine', 'pos' => 9, 'pts' => 2.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+8.604', 'laps' => 71],
                ['driver' => 'Fernando Alonso', 'team' => 'Aston Martin', 'pos' => 10, 'pts' => 1.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+13.533', 'laps' => 71],
                ['driver' => 'Nico Hülkenberg', 'team' => 'Haas', 'pos' => 11, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+19.214', 'laps' => 71],
                ['driver' => 'Daniel Ricciardo', 'team' => 'Visa Cash App RB', 'pos' => 12, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+21.067', 'laps' => 71],
                ['driver' => 'Lance Stroll', 'team' => 'Aston Martin', 'pos' => 13, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+25.712', 'laps' => 71],
                ['driver' => 'Alexander Albon', 'team' => 'Williams', 'pos' => 14, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+40.711', 'laps' => 71],
                ['driver' => 'Esteban Ocon', 'team' => 'Alpine', 'pos' => 15, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+46.878', 'laps' => 71],
                ['driver' => 'Logan Sargeant', 'team' => 'Williams', 'pos' => 16, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+1:04.539', 'laps' => 71],
                ['driver' => 'Yuki Tsunoda', 'team' => 'Visa Cash App RB', 'pos' => 17, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+1:05.146', 'laps' => 71],
                ['driver' => 'Kevin Magnussen', 'team' => 'Haas', 'pos' => 18, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+1:05.707', 'laps' => 71],
                ['driver' => 'Valtteri Bottas', 'team' => 'Kick Sauber', 'pos' => 19, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+3.248', 'laps' => 70],
                ['driver' => 'Zhou Guanyu', 'team' => 'Kick Sauber', 'pos' => 20, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+36.019', 'laps' => 70],
            ]);
        }

        // Italian Grand Prix 2024
        $grandPrix = GrandPrix::where('name', 'Italian Grand Prix')->where('season', 2024)->first();
        if ($grandPrix) {
            $this->createResults($grandPrix->id, [
                ['driver' => 'Charles Leclerc', 'team' => 'Ferrari', 'pos' => 1, 'pts' => 25.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '1:14:40.727', 'laps' => 53],
            ]);
        }

        // Italian Grand Prix 2024
        $grandPrix = GrandPrix::where('name', 'Italian Grand Prix')->where('season', 2024)->first();
        if ($grandPrix) {
            $this->createResults($grandPrix->id, [
                ['driver' => 'Oscar Piastri', 'team' => 'McLaren', 'pos' => 2, 'pts' => 18.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+2.664', 'laps' => 53],
                ['driver' => 'Lando Norris', 'team' => 'McLaren', 'pos' => 3, 'pts' => 16.0, 'fl' => true, 'pole' => true, 'dnf' => false, 'time' => '+6.153', 'laps' => 53],
                ['driver' => 'Carlos Sainz', 'team' => 'Ferrari', 'pos' => 4, 'pts' => 12.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+15.621', 'laps' => 53],
                ['driver' => 'Lewis Hamilton', 'team' => 'Mercedes-AMG Petronas', 'pos' => 5, 'pts' => 10.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+22.820', 'laps' => 53],
                ['driver' => 'Max Verstappen', 'team' => 'Red Bull Racing', 'pos' => 6, 'pts' => 8.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+37.932', 'laps' => 53],
                ['driver' => 'George Russell', 'team' => 'Mercedes-AMG Petronas', 'pos' => 7, 'pts' => 6.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+39.715', 'laps' => 53],
                ['driver' => 'Sergio Pérez', 'team' => 'Red Bull Racing', 'pos' => 8, 'pts' => 4.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+54.148', 'laps' => 53],
                ['driver' => 'Alexander Albon', 'team' => 'Williams', 'pos' => 9, 'pts' => 2.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:07.456', 'laps' => 53],
                ['driver' => 'Kevin Magnussen', 'team' => 'Haas', 'pos' => 10, 'pts' => 1.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:08.302', 'laps' => 53],
                ['driver' => 'Fernando Alonso', 'team' => 'Aston Martin', 'pos' => 11, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:08.495', 'laps' => 53],
                ['driver' => 'Franco Colapinto', 'team' => 'Williams', 'pos' => 12, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:21.308', 'laps' => 53],
                ['driver' => 'Daniel Ricciardo', 'team' => 'Visa Cash App RB', 'pos' => 13, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:33.452', 'laps' => 53],
                ['driver' => 'Esteban Ocon', 'team' => 'Alpine', 'pos' => 14, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+12.659', 'laps' => 52],
                ['driver' => 'Pierre Gasly', 'team' => 'Alpine', 'pos' => 15, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+18.344', 'laps' => 52],
                ['driver' => 'Valtteri Bottas', 'team' => 'Kick Sauber', 'pos' => 16, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+27.211', 'laps' => 52],
                ['driver' => 'Nico Hülkenberg', 'team' => 'Haas', 'pos' => 17, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+30.870', 'laps' => 52],
                ['driver' => 'Zhou Guanyu', 'team' => 'Kick Sauber', 'pos' => 18, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+40.055', 'laps' => 52],
                ['driver' => 'Lance Stroll', 'team' => 'Aston Martin', 'pos' => 19, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+43.508', 'laps' => 52],
                ['driver' => 'Yuki Tsunoda', 'team' => 'Visa Cash App RB', 'pos' => 20, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => 'Retired', 'laps' => 7],
            ]);
        }

        // Azerbaijan Grand Prix 2024
        $grandPrix = GrandPrix::where('name', 'Azerbaijan Grand Prix')->where('season', 2024)->first();
        if ($grandPrix) {
            $this->createResults($grandPrix->id, [
                ['driver' => 'Oscar Piastri', 'team' => 'McLaren', 'pos' => 1, 'pts' => 25.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '1:32:58.007', 'laps' => 51],
                ['driver' => 'Charles Leclerc', 'team' => 'Ferrari', 'pos' => 2, 'pts' => 18.0, 'fl' => false, 'pole' => true, 'dnf' => false, 'time' => '+10.910', 'laps' => 51],
                ['driver' => 'George Russell', 'team' => 'Mercedes-AMG Petronas', 'pos' => 3, 'pts' => 15.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+31.328', 'laps' => 51],
                ['driver' => 'Lando Norris', 'team' => 'McLaren', 'pos' => 4, 'pts' => 13.0, 'fl' => true, 'pole' => false, 'dnf' => false, 'time' => '+36.143', 'laps' => 51],
                ['driver' => 'Max Verstappen', 'team' => 'Red Bull Racing', 'pos' => 5, 'pts' => 10.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:17.098', 'laps' => 51],
                ['driver' => 'Fernando Alonso', 'team' => 'Aston Martin', 'pos' => 6, 'pts' => 8.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:25.468', 'laps' => 51],
                ['driver' => 'Alexander Albon', 'team' => 'Williams', 'pos' => 7, 'pts' => 6.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:27.396', 'laps' => 51],
                ['driver' => 'Franco Colapinto', 'team' => 'Williams', 'pos' => 8, 'pts' => 4.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:29.541', 'laps' => 51],
                ['driver' => 'Lewis Hamilton', 'team' => 'Mercedes-AMG Petronas', 'pos' => 9, 'pts' => 2.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:32.401', 'laps' => 51],
                ['driver' => 'Oliver Bearman', 'team' => 'Haas', 'pos' => 10, 'pts' => 1.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:33.127', 'laps' => 51],
                ['driver' => 'Nico Hülkenberg', 'team' => 'Haas', 'pos' => 11, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:33.465', 'laps' => 51],
                ['driver' => 'Pierre Gasly', 'team' => 'Alpine', 'pos' => 12, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:57.189', 'laps' => 51],
                ['driver' => 'Daniel Ricciardo', 'team' => 'Visa Cash App RB', 'pos' => 13, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+2:26.907', 'laps' => 51],
                ['driver' => 'Zhou Guanyu', 'team' => 'Kick Sauber', 'pos' => 14, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+2:28.841', 'laps' => 51],
                ['driver' => 'Esteban Ocon', 'team' => 'Alpine', 'pos' => 15, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+21.344', 'laps' => 50],
                ['driver' => 'Valtteri Bottas', 'team' => 'Kick Sauber', 'pos' => 16, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+25.395', 'laps' => 50],
                ['driver' => 'Sergio Pérez', 'team' => 'Red Bull Racing', 'pos' => 17, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '', 'laps' => 49],
                ['driver' => 'Carlos Sainz', 'team' => 'Ferrari', 'pos' => 18, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '', 'laps' => 49],
                ['driver' => 'Lance Stroll', 'team' => 'Aston Martin', 'pos' => 19, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '', 'laps' => 45],
                ['driver' => 'Yuki Tsunoda', 'team' => 'Visa Cash App RB', 'pos' => 20, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => 'Retired', 'laps' => 14],
            ]);
        }

        // Singapore Grand Prix 2024
        $grandPrix = GrandPrix::where('name', 'Singapore Grand Prix')->where('season', 2024)->first();
        if ($grandPrix) {
            $this->createResults($grandPrix->id, [
                ['driver' => 'Lando Norris', 'team' => 'McLaren', 'pos' => 1, 'pts' => 25.0, 'fl' => false, 'pole' => true, 'dnf' => false, 'time' => '1:40:52.571', 'laps' => 62],
                ['driver' => 'Max Verstappen', 'team' => 'Red Bull Racing', 'pos' => 2, 'pts' => 18.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+20.945', 'laps' => 62],
                ['driver' => 'Oscar Piastri', 'team' => 'McLaren', 'pos' => 3, 'pts' => 15.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+41.823', 'laps' => 62],
                ['driver' => 'George Russell', 'team' => 'Mercedes-AMG Petronas', 'pos' => 4, 'pts' => 12.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:01.040', 'laps' => 62],
                ['driver' => 'Charles Leclerc', 'team' => 'Ferrari', 'pos' => 5, 'pts' => 10.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:02.430', 'laps' => 62],
                ['driver' => 'Lewis Hamilton', 'team' => 'Mercedes-AMG Petronas', 'pos' => 6, 'pts' => 8.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:25.248', 'laps' => 62],
                ['driver' => 'Carlos Sainz', 'team' => 'Ferrari', 'pos' => 7, 'pts' => 6.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:36.039', 'laps' => 62],
                ['driver' => 'Fernando Alonso', 'team' => 'Aston Martin', 'pos' => 8, 'pts' => 4.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+0.873', 'laps' => 61],
                ['driver' => 'Nico Hülkenberg', 'team' => 'Haas', 'pos' => 9, 'pts' => 2.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+3.140', 'laps' => 61],
                ['driver' => 'Sergio Pérez', 'team' => 'Red Bull Racing', 'pos' => 10, 'pts' => 1.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+4.624', 'laps' => 61],
                ['driver' => 'Franco Colapinto', 'team' => 'Williams', 'pos' => 11, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+6.284', 'laps' => 61],
                ['driver' => 'Yuki Tsunoda', 'team' => 'Visa Cash App RB', 'pos' => 12, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+8.792', 'laps' => 61],
                ['driver' => 'Esteban Ocon', 'team' => 'Alpine', 'pos' => 13, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+44.005', 'laps' => 61],
                ['driver' => 'Lance Stroll', 'team' => 'Aston Martin', 'pos' => 14, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+47.571', 'laps' => 61],
                ['driver' => 'Zhou Guanyu', 'team' => 'Kick Sauber', 'pos' => 15, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+57.220', 'laps' => 61],
                ['driver' => 'Valtteri Bottas', 'team' => 'Kick Sauber', 'pos' => 16, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+57.829', 'laps' => 61],
                ['driver' => 'Pierre Gasly', 'team' => 'Alpine', 'pos' => 17, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+59.059', 'laps' => 61],
                ['driver' => 'Daniel Ricciardo', 'team' => 'Visa Cash App RB', 'pos' => 18, 'pts' => 0.0, 'fl' => true, 'pole' => false, 'dnf' => true, 'time' => '+1:29.796', 'laps' => 61],
                ['driver' => 'Kevin Magnussen', 'team' => 'Haas', 'pos' => 19, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '', 'laps' => 57],
                ['driver' => 'Alexander Albon', 'team' => 'Williams', 'pos' => 20, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => 'Retired', 'laps' => 15],
            ]);
        }

        // United States Grand Prix 2024
        $grandPrix = GrandPrix::where('name', 'United States Grand Prix')->where('season', 2024)->first();
        if ($grandPrix) {
            $this->createResults($grandPrix->id, [
                ['driver' => 'Charles Leclerc', 'team' => 'Ferrari', 'pos' => 1, 'pts' => 25.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '1:35:09.639', 'laps' => 56],
                ['driver' => 'Carlos Sainz', 'team' => 'Ferrari', 'pos' => 2, 'pts' => 18.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+8.562', 'laps' => 56],
                ['driver' => 'Max Verstappen', 'team' => 'Red Bull Racing', 'pos' => 3, 'pts' => 15.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+19.412', 'laps' => 56],
                ['driver' => 'Lando Norris', 'team' => 'McLaren', 'pos' => 4, 'pts' => 12.0, 'fl' => false, 'pole' => true, 'dnf' => false, 'time' => '+20.354', 'laps' => 56],
                ['driver' => 'Oscar Piastri', 'team' => 'McLaren', 'pos' => 5, 'pts' => 10.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+21.921', 'laps' => 56],
                ['driver' => 'George Russell', 'team' => 'Mercedes-AMG Petronas', 'pos' => 6, 'pts' => 8.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+56.295', 'laps' => 56],
                ['driver' => 'Sergio Pérez', 'team' => 'Red Bull Racing', 'pos' => 7, 'pts' => 6.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+59.072', 'laps' => 56],
                ['driver' => 'Nico Hülkenberg', 'team' => 'Haas', 'pos' => 8, 'pts' => 4.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:02.957', 'laps' => 56],
                ['driver' => 'Liam Lawson', 'team' => 'Visa Cash App RB', 'pos' => 9, 'pts' => 2.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:10.563', 'laps' => 56],
                ['driver' => 'Franco Colapinto', 'team' => 'Williams', 'pos' => 10, 'pts' => 1.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:11.979', 'laps' => 56],
                ['driver' => 'Kevin Magnussen', 'team' => 'Haas', 'pos' => 11, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:19.782', 'laps' => 56],
                ['driver' => 'Pierre Gasly', 'team' => 'Alpine', 'pos' => 12, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:30.558', 'laps' => 56],
                ['driver' => 'Fernando Alonso', 'team' => 'Aston Martin', 'pos' => 13, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+1.726', 'laps' => 55],
                ['driver' => 'Yuki Tsunoda', 'team' => 'Visa Cash App RB', 'pos' => 14, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+8.212', 'laps' => 55],
                ['driver' => 'Lance Stroll', 'team' => 'Aston Martin', 'pos' => 15, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+16.983', 'laps' => 55],
                ['driver' => 'Alexander Albon', 'team' => 'Williams', 'pos' => 16, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+18.092', 'laps' => 55],
                ['driver' => 'Valtteri Bottas', 'team' => 'Kick Sauber', 'pos' => 17, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+34.333', 'laps' => 55],
                ['driver' => 'Esteban Ocon', 'team' => 'Alpine', 'pos' => 18, 'pts' => 0.0, 'fl' => true, 'pole' => false, 'dnf' => true, 'time' => '+42.112', 'laps' => 55],
                ['driver' => 'Zhou Guanyu', 'team' => 'Kick Sauber', 'pos' => 19, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+49.240', 'laps' => 55],
                ['driver' => 'Lewis Hamilton', 'team' => 'Mercedes-AMG Petronas', 'pos' => 20, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => 'Retired', 'laps' => 1],
            ]);
        }

        // Mexico City Grand Prix 2024
        $grandPrix = GrandPrix::where('name', 'Mexico City Grand Prix')->where('season', 2024)->first();
        if ($grandPrix) {
            $this->createResults($grandPrix->id, [
                ['driver' => 'Carlos Sainz', 'team' => 'Ferrari', 'pos' => 1, 'pts' => 25.0, 'fl' => false, 'pole' => true, 'dnf' => false, 'time' => '1:40:55.800', 'laps' => 71],
                ['driver' => 'Lando Norris', 'team' => 'McLaren', 'pos' => 2, 'pts' => 18.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+4.705', 'laps' => 71],
                ['driver' => 'Charles Leclerc', 'team' => 'Ferrari', 'pos' => 3, 'pts' => 16.0, 'fl' => true, 'pole' => false, 'dnf' => false, 'time' => '+34.387', 'laps' => 71],
                ['driver' => 'Lewis Hamilton', 'team' => 'Mercedes-AMG Petronas', 'pos' => 4, 'pts' => 12.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+44.780', 'laps' => 71],
                ['driver' => 'George Russell', 'team' => 'Mercedes-AMG Petronas', 'pos' => 5, 'pts' => 10.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+48.536', 'laps' => 71],
                ['driver' => 'Max Verstappen', 'team' => 'Red Bull Racing', 'pos' => 6, 'pts' => 8.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+59.558', 'laps' => 71],
                ['driver' => 'Kevin Magnussen', 'team' => 'Haas', 'pos' => 7, 'pts' => 6.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:03.642', 'laps' => 71],
                ['driver' => 'Oscar Piastri', 'team' => 'McLaren', 'pos' => 8, 'pts' => 4.0, 'fl' => false, 'pole' => false, 'dnf' => false, 'time' => '+1:04.928', 'laps' => 71],
                ['driver' => 'Nico Hülkenberg', 'team' => 'Haas', 'pos' => 9, 'pts' => 2.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+2.720', 'laps' => 70],
                ['driver' => 'Pierre Gasly', 'team' => 'Alpine', 'pos' => 10, 'pts' => 1.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+18.587', 'laps' => 70],
                ['driver' => 'Lance Stroll', 'team' => 'Aston Martin', 'pos' => 11, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+25.072', 'laps' => 70],
                ['driver' => 'Franco Colapinto', 'team' => 'Williams', 'pos' => 12, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+37.497', 'laps' => 70],
                ['driver' => 'Esteban Ocon', 'team' => 'Alpine', 'pos' => 13, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+39.663', 'laps' => 70],
                ['driver' => 'Valtteri Bottas', 'team' => 'Kick Sauber', 'pos' => 14, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+42.227', 'laps' => 70],
                ['driver' => 'Zhou Guanyu', 'team' => 'Kick Sauber', 'pos' => 15, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+1:01.722', 'laps' => 70],
                ['driver' => 'Liam Lawson', 'team' => 'Visa Cash App RB', 'pos' => 16, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+1:04.466', 'laps' => 70],
                ['driver' => 'Sergio Pérez', 'team' => 'Red Bull Racing', 'pos' => 17, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => '+1:13.284', 'laps' => 70],
                ['driver' => 'Fernando Alonso', 'team' => 'Aston Martin', 'pos' => 18, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => 'Retired', 'laps' => 15],
                ['driver' => 'Alexander Albon', 'team' => 'Williams', 'pos' => 19, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => 'Retired', 'laps' => 0],
                ['driver' => 'Yuki Tsunoda', 'team' => 'Visa Cash App RB', 'pos' => 20, 'pts' => 0.0, 'fl' => false, 'pole' => false, 'dnf' => true, 'time' => 'Retired', 'laps' => 0],
            ]);
        }

        // São Paulo Grand Prix 2024
        $grandPrix = GrandPrix::where('name', 'São Paulo Grand Prix')->where('season', 2024)->first();
        if ($grandPrix) {
            $this->createResults($grandPrix->id, [
                ['driver' => 'Max Verstappen', 'team' => 'Red Bull Racing', 'pos' => 1, 'pts' => 26.0, 'fl' => true, 'pole' => false, 'dnf' => false, 'time' => '2:06:54.430', 'laps' => 69],
            ]);
        }

    }

    private function createResults(int $grandPrixId, array $results): void
    {
        foreach ($results as $r) {
            $driver = Driver::where('name', $r['driver'])->first();
            $team = Team::where('name', $r['team'])->first();

            if (!$driver || !$team) {
                continue;
            }

            RaceResult::updateOrCreate(
                [
                    'grand_prix_id' => $grandPrixId,
                    'driver_id' => $driver->id,
                ],
                [
                    'team_id' => $team->id,
                    'position' => $r['pos'],
                    'points' => $r['pts'],
                    'fastest_lap' => $r['fl'],
                    'pole_position' => $r['pole'],
                    'dnf' => $r['dnf'],
                    'total_time' => $r['time'],
                    'laps_completed' => $r['laps'],
                ]
            );
        }
    }
}
