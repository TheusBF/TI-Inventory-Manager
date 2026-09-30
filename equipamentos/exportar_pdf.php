<?php
require_once "../auth/proteger.php";
require_once "../config/conexao.php";

/*
|--------------------------------------------------------------------------
| FILTROS
|--------------------------------------------------------------------------
*/

$busca = trim($_GET['busca'] ?? '');
$statusFiltro = trim($_GET['status'] ?? '');
$tipoFiltro = trim($_GET['tipo'] ?? '');
$responsavelFiltro = trim($_GET['responsavel'] ?? '');

/*
|--------------------------------------------------------------------------
| CONSULTA
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            id,
            patrimonio,
            tipo,
            marca,
            modelo,
            numero_serie,
            status,
            responsavel,
            setor,
            localizacao,
            data_emprestimo
        FROM equipamentos
        WHERE 1=1";

$params = [];
$types = "";

/* Busca */

if ($busca !== '') {

    $sql .= " AND (
        patrimonio LIKE ?
        OR tipo LIKE ?
        OR marca LIKE ?
        OR modelo LIKE ?
        OR numero_serie LIKE ?
    )";

    $termo = "%" . $busca . "%";

    for ($i = 0; $i < 5; $i++) {
        $params[] = $termo;
        $types .= "s";
    }
}

/* Status */

if ($statusFiltro !== '') {

    $sql .= " AND status = ?";

    $params[] = $statusFiltro;
    $types .= "s";
}

/* Tipo */

if ($tipoFiltro !== '') {

    $sql .= " AND tipo LIKE ?";

    $params[] = "%" . $tipoFiltro . "%";
    $types .= "s";
}

/* Responsável */

if ($responsavelFiltro !== '') {

    $sql .= " AND responsavel LIKE ?";

    $params[] = "%" . $responsavelFiltro . "%";
    $types .= "s";
}

$sql .= " ORDER BY id DESC";

/*
|--------------------------------------------------------------------------
| EXECUTA CONSULTA
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Erro ao preparar consulta: " . $conn->error);
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();

$resultado = $stmt->get_result();

$total = $resultado->num_rows;

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <title>Relatório de Equipamentos</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f3f4f6;
            color: #111827;
            margin: 0;
            padding: 30px;
        }

        .relatorio {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 35px;
            border-radius: 10px;
        }

        .cabecalho {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #111827;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }

        .titulo h1 {
            margin: 0 0 5px 0;
            font-size: 24px;
        }

        .titulo p {
            margin: 0;
            color: #6b7280;
            font-size: 13px;
        }

        .data-relatorio {
            text-align: right;
            font-size: 13px;
            color: #4b5563;
        }

        .resumo {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
        }

        .resumo-box {
            background: #f3f4f6;
            border-radius: 8px;
            padding: 15px 20px;
            min-width: 180px;
        }

        .resumo-box strong {
            display: block;
            font-size: 22px;
            margin-bottom: 4px;
        }

        .resumo-box span {
            color: #6b7280;
            font-size: 13px;
        }

        .filtros {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 25px;
            font-size: 13px;
        }

        .filtros strong {
            margin-right: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        th {
            background: #111827;
            color: white;
            padding: 10px 7px;
            text-align: left;
        }

        td {
            padding: 9px 7px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
        }

        tr:nth-child(even) {
            background: #f9fafb;
        }

        .status {
            font-weight: bold;
        }

        .disponivel {
            color: #15803d;
        }

        .emprestado {
            color: #b45309;
        }

        .manutencao {
            color: #b91c1c;
        }

        .baixado {
            color: #4b5563;
        }

        .rodape {
            margin-top: 25px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            font-size: 11px;
            color: #6b7280;
            text-align: center;
        }

        .botoes {
            max-width: 1200px;
            margin: 0 auto 15px auto;
            display: flex;
            gap: 10px;
        }

        .btn {
            border: none;
            border-radius: 7px;
            padding: 10px 18px;
            color: white;
            font-weight: bold;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-imprimir {
            background: #2563eb;
        }

        .btn-voltar {
            background: #6b7280;
        }

        @media print {

            body {
                background: white;
                padding: 0;
            }

            .botoes {
                display: none;
            }

            .relatorio {
                max-width: none;
                padding: 0;
                border-radius: 0;
            }

            table {
                font-size: 9px;
            }

            th,
            td {
                padding: 6px 4px;
            }

            @page {
                size: landscape;
                margin: 10mm;
            }

        }

    </style>

</head>

<body>

<div class="botoes">

    <button
        class="btn btn-imprimir"
        onclick="window.print()"
    >
        🖨️ Salvar como PDF
    </button>

    <button
        class="btn btn-voltar"
        onclick="history.back()"
    >
        ← Voltar
    </button>

</div>


<div class="relatorio">

    <div class="cabecalho">

        <div class="titulo">

            <h1>Relatório de Equipamentos de TI</h1>

            <p>
                Inventário de equipamentos
            </p>

        </div>

        <div class="data-relatorio">

            Gerado em:<br>

            <?= date('d/m/Y H:i'); ?>

        </div>

    </div>


    <div class="resumo">

        <div class="resumo-box">

            <strong><?= $total; ?></strong>

            <span>Equipamentos encontrados</span>

        </div>

    </div>


    <?php if (
        $busca !== '' ||
        $statusFiltro !== '' ||
        $tipoFiltro !== '' ||
        $responsavelFiltro !== ''
    ): ?>

        <div class="filtros">

            <strong>Filtros utilizados:</strong>

            <?php if ($busca !== ''): ?>

                Busca:
                <?= htmlspecialchars($busca); ?>

            <?php endif; ?>


            <?php if ($statusFiltro !== ''): ?>

                | Status:
                <?php

                switch ($statusFiltro) {

                    case 'Disponivel':
                        echo 'Disponível';
                        break;

                    case 'Emprestado':
                        echo 'Emprestado';
                        break;

                    case 'Manutencao':
                        echo 'Manutenção';
                        break;

                    case 'Baixado':
                        echo 'Baixado';
                        break;

                    default:
                        echo htmlspecialchars($statusFiltro);
                }

                ?>

            <?php endif; ?>


            <?php if ($tipoFiltro !== ''): ?>

                | Tipo:
                <?= htmlspecialchars($tipoFiltro); ?>

            <?php endif; ?>


            <?php if ($responsavelFiltro !== ''): ?>

                | Responsável:
                <?= htmlspecialchars($responsavelFiltro); ?>

            <?php endif; ?>

        </div>

    <?php endif; ?>


    <table>

        <thead>

            <tr>

                <th>ID</th>
                <th>Patrimônio</th>
                <th>Tipo</th>
                <th>Marca</th>
                <th>Modelo</th>
                <th>Nº Série</th>
                <th>Status</th>
                <th>Responsável</th>
                <th>Setor</th>
                <th>Localização</th>
                <th>Empréstimo</th>

            </tr>

        </thead>

        <tbody>

        <?php if ($resultado->num_rows > 0): ?>

            <?php while ($equipamento = $resultado->fetch_assoc()): ?>

                <tr>

                    <td>
                        <?= htmlspecialchars($equipamento['id']); ?>
                    </td>

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
                        <?= htmlspecialchars($equipamento['numero_serie']); ?>
                    </td>

                    <td class="status">

                        <?php

                        switch ($equipamento['status']) {

                            case 'Disponivel':
                                echo '<span class="disponivel">Disponível</span>';
                                break;

                            case 'Emprestado':
                                echo '<span class="emprestado">Emprestado</span>';
                                break;

                            case 'Manutencao':
                                echo '<span class="manutencao">Manutenção</span>';
                                break;

                            case 'Baixado':
                                echo '<span class="baixado">Baixado</span>';
                                break;

                            default:
                                echo htmlspecialchars($equipamento['status']);
                        }

                        ?>

                    </td>

                    <td>
                        <?= htmlspecialchars($equipamento['responsavel'] ?? ''); ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($equipamento['setor'] ?? ''); ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($equipamento['localizacao'] ?? ''); ?>
                    </td>

                    <td>

                        <?php

                        if (!empty($equipamento['data_emprestimo'])) {

                            echo date(
                                'd/m/Y',
                                strtotime($equipamento['data_emprestimo'])
                            );

                        } else {

                            echo '-';

                        }

                        ?>

                    </td>

                </tr>

            <?php endwhile; ?>

        <?php else: ?>

            <tr>

                <td colspan="11" style="text-align:center; padding:30px;">
                    Nenhum equipamento encontrado.
                </td>

            </tr>

        <?php endif; ?>

        </tbody>

    </table>


    <div class="rodape">

        Sistema de Inventário de TI —
        Relatório gerado automaticamente

    </div>

</div>


<script>

    window.onload = function() {

        // Abre automaticamente a tela de impressão
        window.print();

    };

</script>

</body>

</html>

<?php

$stmt->close();
$conn->close();

?>