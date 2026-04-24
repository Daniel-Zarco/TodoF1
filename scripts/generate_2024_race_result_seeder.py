import requests
from pathlib import Path

SEASON = 2024
API_URL = f"https://api.jolpi.ca/ergast/f1/{SEASON}/results.json"

OUTPUT_PATH = Path("../database/seeders/RaceResultSeeder.php")

TEAM_MAP = {
    "Red Bull": "Red Bull Racing",
    "Ferrari": "Ferrari",
    "Mercedes": "Mercedes-AMG Petronas",
    "McLaren": "McLaren",
    "Aston Martin": "Aston Martin",
    "Alpine F1 Team": "Alpine",
    "Alpine": "Alpine",
    "Williams": "Williams",
    "RB F1 Team": "Visa Cash App RB",
    "RB": "Visa Cash App RB",
    "Sauber": "Kick Sauber",
    "Kick Sauber": "Kick Sauber",
    "Haas F1 Team": "Haas",
    "Haas": "Haas",
}

DRIVER_MAP = {
    "Max Verstappen": "Max Verstappen",
    "Sergio Pérez": "Sergio Pérez",
    "Sergio Perez": "Sergio Pérez",
    "Charles Leclerc": "Charles Leclerc",
    "Carlos Sainz": "Carlos Sainz",
    "Lewis Hamilton": "Lewis Hamilton",
    "George Russell": "George Russell",
    "Lando Norris": "Lando Norris",
    "Oscar Piastri": "Oscar Piastri",
    "Fernando Alonso": "Fernando Alonso",
    "Lance Stroll": "Lance Stroll",
    "Kevin Magnussen": "Kevin Magnussen",
    "Nico Hülkenberg": "Nico Hülkenberg",
    "Nico Hulkenberg": "Nico Hülkenberg",
    "Alexander Albon": "Alexander Albon",
    "Logan Sargeant": "Logan Sargeant",
    "Esteban Ocon": "Esteban Ocon",
    "Pierre Gasly": "Pierre Gasly",
    "Yuki Tsunoda": "Yuki Tsunoda",
    "Daniel Ricciardo": "Daniel Ricciardo",
    "Valtteri Bottas": "Valtteri Bottas",
    "Zhou Guanyu": "Zhou Guanyu",
    "Guanyu Zhou": "Zhou Guanyu",
    "Liam Lawson": "Liam Lawson",
    "Oliver Bearman": "Oliver Bearman",
    "Franco Colapinto": "Franco Colapinto",
    "Jack Doohan": "Jack Doohan",
}

def php_string(value):
    if value is None:
        return "null"
    escaped = str(value).replace("\\", "\\\\").replace("'", "\\'")
    return f"'{escaped}'"

def bool_php(value):
    return "true" if value else "false"

def get_driver_name(result):
    driver = result["Driver"]
    return f"{driver['givenName']} {driver['familyName']}"

def get_team_name(result):
    return result["Constructor"]["name"]

def safe_points(value):
    return float(value)

def generate():
    print(f"Descargando resultados reales F1 {SEASON} desde Jolpica...")

all_races = []
limit = 100
offset = 0

while True:
    url = f"{API_URL}?limit={limit}&offset={offset}"

    response = requests.get(url, timeout=30)
    response.raise_for_status()

    data = response.json()
    races_page = data["MRData"]["RaceTable"]["Races"]

    if not races_page:
        break

    all_races.extend(races_page)

    total = int(data["MRData"]["total"])
    offset += limit

    if offset >= total:
        break

    races = all_races


    if not races:
        raise RuntimeError("No se han encontrado carreras en la API.")

    php = """<?php

namespace Database\\Seeders;

use App\\Models\\Driver;
use App\\Models\\GrandPrix;
use App\\Models\\RaceResult;
use App\\Models\\Team;
use Illuminate\\Database\\Seeder;

class RaceResultSeeder extends Seeder
{
    public function run(): void
    {
"""

    warnings = []

    for race in races:
        race_name = race["raceName"]
        results = race.get("Results", [])

        php += f"        // {race_name} {SEASON}\n"
        php += f"        $grandPrix = GrandPrix::where('name', {php_string(race_name)})->where('season', {SEASON})->first();\n"
        php += "        if ($grandPrix) {\n"
        php += "            $this->createResults($grandPrix->id, [\n"

        for result in results:
            api_driver = get_driver_name(result)
            api_team = get_team_name(result)

            driver_name = DRIVER_MAP.get(api_driver)
            team_name = TEAM_MAP.get(api_team)

            if not driver_name:
                warnings.append(f"Piloto sin mapear: {api_driver}")
                driver_name = api_driver

            if not team_name:
                warnings.append(f"Equipo sin mapear: {api_team}")
                team_name = api_team

            position = result.get("position")
            points = safe_points(result.get("points", 0))
            fastest_lap = result.get("FastestLap", {}).get("rank") == "1"
            grid = result.get("grid")
            pole_position = grid == "1"
            status = result.get("status", "")
            dnf = not status.startswith("+") and status != "Finished"

            total_time = None
            if "Time" in result:
                total_time = result["Time"].get("time")
            elif status:
                total_time = status

            laps_completed = result.get("laps")

            php += (
                "                ["
                f"'driver' => {php_string(driver_name)}, "
                f"'team' => {php_string(team_name)}, "
                f"'pos' => {position}, "
                f"'pts' => {points}, "
                f"'fl' => {bool_php(fastest_lap)}, "
                f"'pole' => {bool_php(pole_position)}, "
                f"'dnf' => {bool_php(dnf)}, "
                f"'time' => {php_string(total_time)}, "
                f"'laps' => {laps_completed}"
                "],\n"
            )

        php += "            ]);\n"
        php += "        }\n\n"

    php += """    }

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
"""

    OUTPUT_PATH.write_text(php, encoding="utf-8")

    print(f"Seeder generado correctamente en: {OUTPUT_PATH}")
    print(f"Carreras procesadas: {len(races)}")

    unique_warnings = sorted(set(warnings))
    if unique_warnings:
        print("\nAvisos:")
        for warning in unique_warnings:
            print(f"- {warning}")

if __name__ == "__main__":
    generate()