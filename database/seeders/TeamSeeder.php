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
                'name'               => 'Red Bull Racing',
                'country'            => 'Austria',
                'base'               => 'Milton Keynes, UK',
                'founded_year'       => 2005,
                'logo_url'           => 'https://upload.wikimedia.org/wikipedia/en/thumb/5/5d/Red_Bull_Racing_logo.svg/320px-Red_Bull_Racing_logo.svg.png',
                'constructor_points' => 860,
                'power_unit'         => 'Honda RBPT',
            ],
            [
                'name'               => 'Ferrari',
                'country'            => 'Italy',
                'base'               => 'Maranello, Italy',
                'founded_year'       => 1950,
                'logo_url'           => 'https://upload.wikimedia.org/wikipedia/en/thumb/d/d3/Ferrari_logo.svg/240px-Ferrari_logo.svg.png',
                'constructor_points' => 652,
                'power_unit'         => 'Ferrari',
            ],
            [
                'name'               => 'Mercedes-AMG Petronas',
                'country'            => 'Germany',
                'base'               => 'Brackley, UK',
                'founded_year'       => 2010,
                'logo_url'           => 'https://upload.wikimedia.org/wikipedia/en/thumb/f/f1/Mercedes_AMG_Petronas_F1_Logo.svg/320px-Mercedes_AMG_Petronas_F1_Logo.svg.png',
                'constructor_points' => 409,
                'power_unit'         => 'Mercedes',
            ],
            [
                'name'               => 'McLaren',
                'country'            => 'United Kingdom',
                'base'               => 'Woking, UK',
                'founded_year'       => 1966,
                'logo_url'           => 'https://upload.wikimedia.org/wikipedia/en/thumb/6/66/McLaren_Racing_logo.svg/240px-McLaren_Racing_logo.svg.png',
                'constructor_points' => 666,
                'power_unit'         => 'Mercedes',
            ],
            [
                'name'               => 'Aston Martin',
                'country'            => 'United Kingdom',
                'base'               => 'Silverstone, UK',
                'founded_year'       => 2021,
                'logo_url'           => 'https://upload.wikimedia.org/wikipedia/en/thumb/4/4c/Aston_Martin_F1_team_logo.svg/240px-Aston_Martin_F1_team_logo.svg.png',
                'constructor_points' => 86,
                'power_unit'         => 'Mercedes',
            ],
            [
                'name'               => 'Alpine',
                'country'            => 'France',
                'base'               => 'Enstone, UK',
                'founded_year'       => 2021,
                'logo_url'           => 'https://upload.wikimedia.org/wikipedia/en/thumb/c/cf/Alpine_F1_Team_Logo.svg/240px-Alpine_F1_Team_Logo.svg.png',
                'constructor_points' => 65,
                'power_unit'         => 'Renault',
            ],
            [
                'name'               => 'Williams',
                'country'            => 'United Kingdom',
                'base'               => 'Grove, UK',
                'founded_year'       => 1977,
                'logo_url'           => 'https://upload.wikimedia.org/wikipedia/en/thumb/1/1a/Williams_Racing_logo.svg/240px-Williams_Racing_logo.svg.png',
                'constructor_points' => 28,
                'power_unit'         => 'Mercedes',
            ],
            [
                'name'               => 'Visa Cash App RB',
                'country'            => 'Italy',
                'base'               => 'Faenza, Italy',
                'founded_year'       => 2006,
                'logo_url'           => 'https://upload.wikimedia.org/wikipedia/en/thumb/b/be/RB_F1_team_logo_2024.svg/240px-RB_F1_team_logo_2024.svg.png',
                'constructor_points' => 46,
                'power_unit'         => 'Honda RBPT',
            ],
            [
                'name'               => 'Kick Sauber',
                'country'            => 'Switzerland',
                'base'               => 'Hinwil, Switzerland',
                'founded_year'       => 1993,
                'logo_url'           => 'https://upload.wikimedia.org/wikipedia/en/thumb/7/72/Kick_Sauber_2024_team_logo.svg/240px-Kick_Sauber_2024_team_logo.svg.png',
                'constructor_points' => 4,
                'power_unit'         => 'Ferrari',
            ],
            [
                'name'               => 'Haas',
                'country'            => 'United States',
                'base'               => 'Kannapolis, USA',
                'founded_year'       => 2016,
                'logo_url'           => 'https://upload.wikimedia.org/wikipedia/en/thumb/b/b6/Haas_F1_team_logo.svg/240px-Haas_F1_team_logo.svg.png',
                'constructor_points' => 31,
                'power_unit'         => 'Ferrari',
            ],
        ];

        foreach ($teams as $team) {
            Team::create($team);
        }
    }
}
