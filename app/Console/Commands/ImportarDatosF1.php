<?php

namespace App\Console\Commands;

use App\Services\F1ApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class ImportarDatosF1 extends Command
{
    protected $signature = 'f1:importar
        {--desde=1970 : Primera temporada a importar}
        {--hasta=2026 : Ultima temporada a importar}';

    protected $description = 'Importar resultados históricos de Fórmula 1';

    public function __construct(
        protected F1ApiService $f1Api
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $desde = (int) $this->option('desde');
        $hasta = (int) $this->option('hasta');

        if ($desde < 1950 || $hasta > now()->year || $desde > $hasta) {
            $this->error('El intervalo de temporadas no es válido.');
            return self::FAILURE;
        }

        $this->info("Importando temporadas desde {$desde} hasta {$hasta}");

        $temporadasCorrectas = 0;
        $temporadasFallidas = [];

        for ($anio = $desde; $anio <= $hasta; $anio++) {
            $this->newLine();
            $this->info("Temporada {$anio}");

            try {
                $datos = $this->f1Api->getCarrerasPorTemporada($anio);

                $carreras = $datos['MRData']['RaceTable']['Races'] ?? [];

                if (empty($carreras)) {
                    $this->warn("Sin resultados para {$anio}");
                    continue;
                }

                $totalResultados = 0;

                DB::transaction(function () use (
                    $carreras,
                    $anio,
                    &$totalResultados
                ) {
                    foreach ($carreras as $carrera) {
                        $granPremio = $this->f1Api->guardarGranPremio(
                            $carrera['raceName'],
                            $carrera['Circuit']['circuitName'],
                            $carrera['Circuit']['Location']['country'],
                            $carrera['date'],
                            $anio,
                            (int) $carrera['round']
                        );
                    
                        foreach ($carrera['Results'] ?? [] as $resultado) {
                            // Guardar escudería con identificador de Jolpica
                            $escuderia = $this->f1Api->guardarEscuderia(
                                $resultado['Constructor']['name'],
                                $resultado['Constructor']['nationality'],
                                null,
                                $resultado['Constructor']['constructorId']
                            );
                    
                            // Guardar piloto con identificador de Jolpica
                            $piloto = $this->f1Api->guardarPiloto(
                                $resultado['Driver']['givenName'] . ' ' .
                                $resultado['Driver']['familyName'],
                                $resultado['Driver']['nationality'],
                                $resultado['Driver']['dateOfBirth'] ?? null,
                                $resultado['Driver']['driverId']
                            );
                    
                            // Guardar estadísticas de carrera
                            $this->f1Api->guardarStatsCarrera(
                                $piloto->id,
                                $escuderia->id,
                                $granPremio->id,
                                isset($resultado['position'])
                                    ? (int) $resultado['position']
                                    : null,
                                $resultado['Time']['time'] ?? null,
                                $resultado['points'] ?? 0,
                                ($resultado['FastestLap']['rank'] ?? null) === '1',
                                ($resultado['grid'] ?? null) === '1'
                            );
                    
                            $totalResultados++;
                        }
                    }
                    });

                $this->line(
                    count($carreras) . " Grandes Premios procesados"
                );

                $this->line(
                    "{$totalResultados} resultados procesados"
                );

                $temporadasCorrectas++;

            } catch (Throwable $e) {
                $this->error(
                    "Error en {$anio}: {$e->getMessage()}"
                );

                $temporadasFallidas[] = $anio;
            }
        }

        $this->newLine();

        $this->info(
            "Temporadas importadas: {$temporadasCorrectas}"
        );

        if (!empty($temporadasFallidas)) {
            $this->warn(
                'Temporadas fallidas: ' .
                implode(', ', $temporadasFallidas)
            );

            return self::FAILURE;
        }

        $this->info('Importación finalizada.');

        return self::SUCCESS;
    }
}