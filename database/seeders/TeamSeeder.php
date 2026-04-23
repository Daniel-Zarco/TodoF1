<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        $teams = [
            [
                'name' => 'Red Bull Racing',
                'country' => 'Austria',
                'base' => 'Milton Keynes, UK',
                'founded_year' => 2005,
                'logo_url' => '/images/teams/red-bull-racing.png',
                'power_unit' => 'Honda RBPT',
            ],
            [
                'name' => 'Ferrari',
                'country' => 'Italy',
                'base' => 'Maranello, Italy',
                'founded_year' => 1950,
                'logo_url' => '/images/teams/ferrari.png',
                'power_unit' => 'Ferrari',
            ],
            [
                'name' => 'Mercedes-AMG Petronas',
                'country' => 'Germany',
                'base' => 'Brackley, UK',
                'founded_year' => 2010,
                'logo_url' => '/images/teams/mercedes.png',
                'power_unit' => 'Mercedes',
            ],
            [
                'name' => 'McLaren',
                'country' => 'United Kingdom',
                'base' => 'Woking, UK',
                'founded_year' => 1966,
                'logo_url' => '/images/teams/mclaren.png',
                'power_unit' => 'Mercedes',
            ],
            [
                'name' => 'Aston Martin',
                'country' => 'United Kingdom',
                'base' => 'Silverstone, UK',
                'founded_year' => 2021,
                'logo_url' => '/images/teams/aston-martin.png',
                'power_unit' => 'Mercedes',
            ],
            [
                'name' => 'Alpine',
                'country' => 'France',
                'base' => 'Enstone, UK',
                'founded_year' => 2021,
                'logo_url' => '/images/teams/alpine.png',
                'power_unit' => 'Renault',
            ],
            [
                'name' => 'Williams',
                'country' => 'United Kingdom',
                'base' => 'Grove, UK',
                'founded_year' => 1977,
                'logo_url' => '/images/teams/williams.png',
                'power_unit' => 'Mercedes',
            ],
            [
                'name' => 'Visa Cash App RB',
                'country' => 'Italy',
                'base' => 'Faenza, Italy',
                'founded_year' => 2006,
                'logo_url' => '/images/teams/rb.png',
                'power_unit' => 'Honda RBPT',
            ],
            [
                'name' => 'Kick Sauber',
                'country' => 'Switzerland',
                'base' => 'Hinwil, Switzerland',
                'founded_year' => 1993,
                'logo_url' => '/images/teams/kick-sauber.png',
                'power_unit' => 'Ferrari',
            ],
            [
                'name' => 'Haas',
                'country' => 'United States',
                'base' => 'Kannapolis, USA',
                'founded_year' => 2016,
                'logo_url' => '/images/teams/haas.png',
                'power_unit' => 'Ferrari',
            ],
        ];

        foreach ($teams as $team) {
            Team::create($team);
        }
    }
}
