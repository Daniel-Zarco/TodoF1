<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        $base = 'https://srpowxjflwomhwujzwzb.supabase.co/storage/v1/object/public/todo-f1-assets/';

        $teams = [
            [
                'name' => 'Red Bull Racing',
                'country' => 'Austria',
                'base' => 'Milton Keynes, UK',
                'founded_year' => 2005,
                'logo_url' => $base . 'teams/logos/2024/red-bull-racing.png',
                'power_unit' => 'Honda RBPT',
            ],
            [
                'name' => 'Ferrari',
                'country' => 'Italy',
                'base' => 'Maranello, Italy',
                'founded_year' => 1950,
                'logo_url' => $base . 'teams/logos/2024/ferrari.png',
                'power_unit' => 'Ferrari',
            ],
            [
                'name' => 'Mercedes-AMG Petronas',
                'country' => 'Germany',
                'base' => 'Brackley, UK',
                'founded_year' => 2010,
                'logo_url' => $base . 'teams/logos/2024/mercedes.png',
                'power_unit' => 'Mercedes',
            ],
            [
                'name' => 'McLaren',
                'country' => 'United Kingdom',
                'base' => 'Woking, UK',
                'founded_year' => 1966,
                'logo_url' => $base . 'teams/logos/2024/mclaren.png',
                'power_unit' => 'Mercedes',
            ],
            [
                'name' => 'Aston Martin',
                'country' => 'United Kingdom',
                'base' => 'Silverstone, UK',
                'founded_year' => 2021,
                'logo_url' => $base . 'teams/logos/2024/aston-martin.png',
                'power_unit' => 'Mercedes',
            ],
            [
                'name' => 'Alpine',
                'country' => 'France',
                'base' => 'Enstone, UK',
                'founded_year' => 2021,
                'logo_url' => $base . 'teams/logos/2024/alpine.png',
                'power_unit' => 'Renault',
            ],
            [
                'name' => 'Williams',
                'country' => 'United Kingdom',
                'base' => 'Grove, UK',
                'founded_year' => 1977,
                'logo_url' => $base . 'teams/logos/2024/williams.png',
                'power_unit' => 'Mercedes',
            ],
            [
                'name' => 'Visa Cash App RB',
                'country' => 'Italy',
                'base' => 'Faenza, Italy',
                'founded_year' => 2006,
                'logo_url' => $base . 'teams/logos/2024/rb.png',
                'power_unit' => 'Honda RBPT',
            ],
            [
                'name' => 'Kick Sauber',
                'country' => 'Switzerland',
                'base' => 'Hinwil, Switzerland',
                'founded_year' => 1993,
                'logo_url' => $base . 'teams/logos/2024/kick-sauber.png',
                'power_unit' => 'Ferrari',
            ],
            [
                'name' => 'Haas',
                'country' => 'United States',
                'base' => 'Kannapolis, USA',
                'founded_year' => 2016,
                'logo_url' => $base . 'teams/logos/2024/haas.png',
                'power_unit' => 'Ferrari',
            ],
        ];

        foreach ($teams as $team) {
            Team::create($team);
        }
    }
}