<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: /index.html/login.php");
    exit();
}

// Valores por defecto si la página se abre directamente (el flujo normal los inyecta desde ../api/PageInfo.php)
$generos_json = $generos_json ?? '{}';
$paises_json = $paises_json ?? '{}';
$edades_json = $edades_json ?? '[]';
$totalUsuarios_json = $totalUsuarios_json ?? '0';

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Panel de datos &mdash; TodoF1</title>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="css/todof1.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/PageInfo.css" />
</head>

<body>
    <?php include __DIR__ . '/../api/navbar.php'; ?>

    <main class="tf-main">
        <div class="tf-container">

            <div class="tf-section__head">
                <div>
                    <span class="tf-eyebrow">Administraci&oacute;n</span>
                    <h1 class="tf-section__title tf-page-title">Datos registrados</h1>
                    <p class="tf-section__sub">Estad&iacute;sticas agregadas de los usuarios de la plataforma.</p>
                </div>
                <a class="tf-back" href="/index.html/PagePrincipal.php">Volver</a>
            </div>

            <div class="tf-kpi tf-info-total">
                <span class="tf-kpi__label">Total de usuarios</span>
                <div class="tf-kpi__value tf-num" id="totalUsuarios">—</div>
                <div class="tf-kpi__meta">Registros en la base de datos</div>
            </div>

            <div class="tf-grid tf-grid--3 tf-mt-5">
                <div class="tf-panel tf-chart-box">
                    <h2 class="tf-chart-box__title">Distribuci&oacute;n de g&eacute;nero</h2>
                    <div class="tf-chart-box__canvas"><canvas id="genderChart"></canvas></div>
                </div>
                <div class="tf-panel tf-chart-box">
                    <h2 class="tf-chart-box__title">Distribuci&oacute;n de pa&iacute;ses</h2>
                    <div class="tf-chart-box__canvas"><canvas id="countryChart"></canvas></div>
                </div>
                <div class="tf-panel tf-chart-box">
                    <h2 class="tf-chart-box__title">Distribuci&oacute;n de edades</h2>
                    <div class="tf-chart-box__canvas"><canvas id="ageChart"></canvas></div>
                </div>
            </div>

        </div>
    </main>

    <script>
        // Datos PHP inyectados en JS
        const generos = <?= $generos_json ?>;
        const paises = <?= $paises_json ?>;
        const edades = <?= $edades_json ?>;
        const totalUsuarios = <?= $totalUsuarios_json ?>;

        const PALETTE = {
            red: '#E10600',
            redSoft: 'rgba(225, 6, 0, 0.75)',
            grid: 'rgba(255, 255, 255, 0.08)',
            text: '#A1A1AA',
            white: '#F5F5F5'
        };

        const pieColors = ['#E10600', '#FF6B57', '#A1A1AA', '#34D399', '#5B8DEF'];

        Chart.defaults.color = PALETTE.text;
        Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
        Chart.defaults.borderColor = PALETTE.grid;

        document.getElementById('totalUsuarios').textContent = totalUsuarios;

        // Gráfico de Género (Pie chart)
        const ctxGen = document.getElementById('genderChart').getContext('2d');
        new Chart(ctxGen, {
            type: 'doughnut',
            data: {
                labels: Object.keys(generos),
                datasets: [{
                    data: Object.values(generos),
                    backgroundColor: pieColors,
                    borderColor: '#161619',
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: PALETTE.text,
                            usePointStyle: true,
                            padding: 16,
                            font: { size: 12, weight: '600' }
                        }
                    }
                }
            }
        });

        // Gráfico de País (Bar chart)
        const ctxPais = document.getElementById('countryChart').getContext('2d');
        new Chart(ctxPais, {
            type: 'bar',
            data: {
                labels: Object.keys(paises),
                datasets: [{
                    label: 'Usuarios',
                    data: Object.values(paises),
                    backgroundColor: PALETTE.redSoft,
                    borderRadius: 6,
                    maxBarThickness: 34
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        precision: 0,
                        ticks: { color: PALETTE.text },
                        grid: { color: PALETTE.grid }
                    },
                    x: {
                        ticks: { color: PALETTE.text, autoSkip: false, maxRotation: 60, minRotation: 45 },
                        grid: { display: false }
                    }
                },
                plugins: { legend: { display: false } }
            }
        });

        // Agrupar edades en rangos de 10 años
        function agruparEdades(edades) {
            const bins = {};
            edades.forEach(age => {
                const rango = Math.floor(age / 10) * 10;
                const label = `${rango} - ${rango + 9}`;
                bins[label] = (bins[label] || 0) + 1;
            });
            return bins;
        }

        const edadesAgrupadas = agruparEdades(edades);

        const ctxEdad = document.getElementById('ageChart').getContext('2d');
        new Chart(ctxEdad, {
            type: 'bar',
            data: {
                labels: Object.keys(edadesAgrupadas),
                datasets: [{
                    label: 'Usuarios',
                    data: Object.values(edadesAgrupadas),
                    backgroundColor: PALETTE.redSoft,
                    borderRadius: 6,
                    maxBarThickness: 34
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        precision: 0,
                        ticks: { color: PALETTE.text },
                        grid: { color: PALETTE.grid }
                    },
                    x: {
                        ticks: { color: PALETTE.text },
                        grid: { display: false }
                    }
                },
                plugins: { legend: { display: false } }
            }
        });
    </script>

</body>

</html>
