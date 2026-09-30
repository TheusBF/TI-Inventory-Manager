<?php

require_once "auth/proteger.php";
require_once "config/conexao.php";

/*
|--------------------------------------------------------------------------
| CONSULTAS DO DASHBOARD
|--------------------------------------------------------------------------
*/

// Total de equipamentos
$sqlTotal = "SELECT COUNT(*) AS total FROM equipamentos";
$resultTotal = $conn->query($sqlTotal);
$total = $resultTotal->fetch_assoc()['total'];

// Disponíveis
$sqlDisponiveis = "SELECT COUNT(*) AS total FROM equipamentos WHERE status = 'Disponivel'";
$resultDisponiveis = $conn->query($sqlDisponiveis);
$disponiveis = $resultDisponiveis->fetch_assoc()['total'];

// Emprestados
$sqlEmprestados = "SELECT COUNT(*) AS total FROM equipamentos WHERE status = 'Emprestado'";
$resultEmprestados = $conn->query($sqlEmprestados);
$emprestados = $resultEmprestados->fetch_assoc()['total'];

// Em manutenção
$sqlManutencao = "SELECT COUNT(*) AS total FROM equipamentos WHERE status = 'Manutencao'";
$resultManutencao = $conn->query($sqlManutencao);
$manutencao = $resultManutencao->fetch_assoc()['total'];

// Baixados
$sqlBaixados = "SELECT COUNT(*) AS total FROM equipamentos WHERE status = 'Baixado'";
$resultBaixados = $conn->query($sqlBaixados);
$baixados = $resultBaixados->fetch_assoc()['total'];

// Últimos equipamentos cadastrados
$sqlRecentes = "SELECT * FROM equipamentos ORDER BY id DESC LIMIT 5";
$resultRecentes = $conn->query($sqlRecentes);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard - TI Inventory Manager</title>

    <link rel="stylesheet" href="assets/css/style.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>

        /* =========================================================
           GRÁFICO DO DASHBOARD
        ========================================================= */

        .dashboard-chart {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .chart-header {
            margin-bottom: 20px;
        }

        .chart-header h2 {
            margin: 0 0 6px 0;
            font-size: 20px;
            color: #111827;
        }

        .chart-header p {
            margin: 0;
            color: #6b7280;
            font-size: 14px;
        }

        .chart-container {
            position: relative;
            width: 100%;
            max-width: 500px;
            height: 350px;
            margin: 0 auto;
        }

        @media (max-width: 600px) {

            .chart-container {
                height: 280px;
            }

        }

    </style>

</head>

<body>

<div class="layout">

    <!-- MENU LATERAL -->

    <?php require_once "includes/sidebar.php"; ?>


    <!-- CONTEÚDO -->

    <main class="main-content">

        <header class="topbar">

            <div>

                <h1>Dashboard</h1>

                <p>
                    Visão geral dos equipamentos de TI
                </p>

            </div>

        </header>


        <!-- CARDS -->

        <section class="cards">

            <div class="dashboard-card">

                <div class="card-icon blue">
                    💻
                </div>

                <div>

                    <span>Total de equipamentos</span>

                    <strong>
                        <?= $total; ?>
                    </strong>

                </div>

            </div>


            <div class="dashboard-card">

                <div class="card-icon green">
                    ✓
                </div>

                <div>

                    <span>Disponíveis</span>

                    <strong>
                        <?= $disponiveis; ?>
                    </strong>

                </div>

            </div>


            <div class="dashboard-card">

                <div class="card-icon yellow">
                    ↗
                </div>

                <div>

                    <span>Emprestados</span>

                    <strong>
                        <?= $emprestados; ?>
                    </strong>

                </div>

            </div>


            <div class="dashboard-card">

                <div class="card-icon red">
                    🔧
                </div>

                <div>

                    <span>Manutenção</span>

                    <strong>
                        <?= $manutencao; ?>
                    </strong>

                </div>

            </div>


            <div class="dashboard-card">

                <div class="card-icon gray">
                    ▪
                </div>

                <div>

                    <span>Baixados</span>

                    <strong>
                        <?= $baixados; ?>
                    </strong>

                </div>

            </div>

        </section>


        <!-- GRÁFICO -->

        <section class="dashboard-chart">

            <div class="chart-header">

                <h2>📊 Equipamentos por status</h2>

                <p>
                    Distribuição atual dos equipamentos cadastrados
                </p>

            </div>


            <div class="chart-container">

                <canvas id="graficoStatus"></canvas>

            </div>

        </section>


        <!-- EQUIPAMENTOS RECENTES -->

        <section class="dashboard-section">

            <div class="section-header">

                <div>

                    <h2>Equipamentos recentes</h2>

                    <p>
                        Últimos equipamentos cadastrados
                    </p>

                </div>

                <a href="equipamentos/listar.php" class="btn">
                    Ver todos
                </a>

            </div>


            <div class="table-container">

                <table>

                    <thead>

                        <tr>

                            <th>Patrimônio</th>

                            <th>Tipo</th>

                            <th>Marca</th>

                            <th>Modelo</th>

                            <th>Status</th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php if ($resultRecentes->num_rows > 0): ?>

                        <?php while ($equipamento = $resultRecentes->fetch_assoc()): ?>

                            <?php

                            $status = $equipamento['status'];

                            switch ($status) {

                                case 'Disponivel':
                                    $classeStatus = 'status-disponivel';
                                    $textoStatus = 'Disponível';
                                    break;

                                case 'Emprestado':
                                    $classeStatus = 'status-emprestado';
                                    $textoStatus = 'Emprestado';
                                    break;

                                case 'Manutencao':
                                    $classeStatus = 'status-manutencao';
                                    $textoStatus = 'Manutenção';
                                    break;

                                case 'Baixado':
                                    $classeStatus = 'status-baixado';
                                    $textoStatus = 'Baixado';
                                    break;

                                default:
                                    $classeStatus = '';
                                    $textoStatus = $status;

                            }

                            ?>

                            <tr>

                                <td>
                                    <?= htmlspecialchars($equipamento['patrimonio']); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($equipamento['tipo']); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($equipamento['marca']); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($equipamento['modelo']); ?>
                                </td>

                                <td>

                                    <span class="<?= $classeStatus ?>">
                                        <?= htmlspecialchars($textoStatus); ?>
                                    </span>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="5">
                                Nenhum equipamento cadastrado.
                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>

    </main>

</div>


<!-- =========================================================
     JAVASCRIPT DO GRÁFICO
========================================================= -->

<script>

const contexto = document
    .getElementById('graficoStatus')
    .getContext('2d');

new Chart(contexto, {

    type: 'doughnut',

    data: {

        labels: [
            'Disponíveis',
            'Emprestados',
            'Manutenção',
            'Baixados'
        ],

        datasets: [

            {

                data: [
                    <?= $disponiveis; ?>,
                    <?= $emprestados; ?>,
                    <?= $manutencao; ?>,
                    <?= $baixados; ?>
                ],

                borderWidth: 2

            }

        ]

    },

    options: {

        responsive: true,

        maintainAspectRatio: false,

        cutout: '65%',

        plugins: {

            legend: {

                position: 'bottom',

                labels: {

                    padding: 20,

                    font: {
                        size: 13
                    }

                }

            },

            tooltip: {

                callbacks: {

                    label: function(context) {

                        return ' ' +
                            context.label +
                            ': ' +
                            context.raw +
                            ' equipamento(s)';

                    }

                }

            }

        }

    }

});

</script>

</body>

</html>