<?php

namespace App\Services;

use App\Models\Escuderia;
use App\Models\Piloto;
use App\Models\GP;
use App\Models\StatsCarrera;
use Illuminate\Support\Facades\Http;

class F1ApiService
{
    // Función para obtener las carreras de un año
    public function getCarrerasPorTemporada(int $anio): array
    {
        $resultados = [];
        $offset = 0;
        $limit = 100;

        do {
            $response = Http::timeout(30)
                ->retry(3, 1000)
                ->get("https://api.jolpi.ca/ergast/f1/{$anio}/results.json", [
                    'limit' => $limit,
                    'offset' => $offset,
                ]);

            $response->throw();

            $data = $response->json();

            if (!isset($data['MRData']['RaceTable']['Races'])) {
                throw new \RuntimeException(
                    "Respuesta inesperada de Jolpica para {$anio}"
                );
            }

            $races = $data['MRData']['RaceTable']['Races'];
            $total = (int) ($data['MRData']['total'] ?? 0);

            foreach ($races as $race) {
                $round = (string) $race['round'];

                if (!isset($resultados[$round])) {
                    $resultados[$round] = $race;
                } else {
                    $resultados[$round]['Results'] = array_merge(
                        $resultados[$round]['Results'] ?? [],
                        $race['Results'] ?? []
                    );
                }
            }
            $offset += $limit;

        } while ($offset < $total);

        return [
            'MRData' => [
                'RaceTable' => [
                    'Races' => array_values($resultados),
                ],
            ],
        ];
    }

    // Función para guardar las escuderías en la base de datos
    public function guardarEscuderia(
        $nombre,
        $pais,
        $año_fundacion = null,
        $constructorId = null
    ) {
        if ($constructorId === null) {
            return Escuderia::firstOrCreate(
                ['nombre' => $nombre],
                [
                    'pais' => $pais,
                    'año_fundacion' => $año_fundacion,
                ]
            );
        }

        return Escuderia::updateOrCreate(
            ['constructor_id' => $constructorId],
            [
                'nombre' => $nombre,
                'pais' => $pais,
                'año_fundacion' => $año_fundacion,
            ]
        );
    }

    // Función para guardar los pilotos
    public function guardarPiloto(
        $nombre,
        $nacionalidad,
        $fecha_nacimiento = null,
        $driverId = null
    ) {
        if ($driverId === null) {
            return Piloto::firstOrCreate(
                ['nombre' => $nombre],
                [
                    'nacionalidad' => $nacionalidad,
                    'fecha_nacimiento' => $fecha_nacimiento,
                ]
            );
        }

        return Piloto::updateOrCreate(
            ['driver_id' => $driverId],
            [
                'nombre' => $nombre,
                'nacionalidad' => $nacionalidad,
                'fecha_nacimiento' => $fecha_nacimiento,
            ]
        );
    }

    // Función para guardar los grandes premios
    public function guardarGranPremio(
        $nombre,
        $circuito,
        $pais,
        $fecha,
        $temporada,
        $ronda = null
    ) {
        if ($ronda === null) {
            return GP::firstOrCreate(
                [
                    'nombre' => $nombre,
                    'temporada' => $temporada,
                ],
                [
                    'circuito' => $circuito,
                    'pais' => $pais,
                    'fecha' => $fecha,
                ]
            );
        }
    
        return GP::updateOrCreate(
            [
                'temporada' => $temporada,
                'ronda' => $ronda,
            ],
            [
                'nombre' => $nombre,
                'circuito' => $circuito,
                'pais' => $pais,
                'fecha' => $fecha,
            ]
        );
    }

    // Función para guardar los resultados de la carrera
    public function guardarStatsCarrera($piloto_id, $escuderia_id, $gran_premio_id, $posicion, $tiempo_total, $puntos, $vuelta_rapida, $pole_position){
        StatsCarrera::firstOrCreate(
            [
                'piloto_id' => $piloto_id,
                'escuderia_id' => $escuderia_id,
                'gran_premio_id' => $gran_premio_id
            ],
            [
                'posicion' => $posicion,
                'tiempo_total' => $tiempo_total,
                'puntos' => $puntos,
                'vuelta_rapida' => $vuelta_rapida,
                'pole_position' => $pole_position
            ]
        );
    }
}

