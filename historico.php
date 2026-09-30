<?php

require_once "auth/proteger.php";
require_once "config/conexao.php";

$sql = "SELECT
            h.id,
            h.usuario,
            h.acao,
            h.data_hora,
            e.patrimonio,
            e.tipo,
            e.marca,
            e.modelo
        FROM historico h
        INNER JOIN equipamentos e
            ON h.equipamento_id = e.id
        ORDER BY h.id DESC";

$resultado = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Histórico - TI Inventory Manager</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f5f7fb;
            color: #1f2937;
        }

        .container {
    margin-left: 250px;
    padding: 35px;
}

        .topo {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .titulo h1 {
            margin: 0;
            font-size: 28px;
        }

        .titulo p {
            margin-top: 6px;
            color: #6b7280;
        }

        .card {
            background: white;
            border-radius: 14px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }

        th {
            text-align: left;
            padding: 14px;
            background: #f8fafc;
            color: #64748b;
            font-size: 13px;
            text-transform: uppercase;
        }

        td {
            padding: 15px 14px;
            border-top: 1px solid #e5e7eb;
            font-size: 14px;
        }

        tr:hover {
            background: #f8fafc;
        }

        .patrimonio {
            font-weight: bold;
        }

        .acao {
            font-weight: 600;
        }

        .usuario {
            color: #475569;
        }

        .data {
            color: #64748b;
            white-space: nowrap;
        }

        .vazio {
            text-align: center;
            padding: 40px;
            color: #64748b;
        }
        /* ==========================================
   BADGES DE AÇÃO
========================================== */

.acao-badge {
    display: inline-flex;
    align-items: center;
    gap: 7px;

    padding: 7px 10px;

    border-radius: 7px;

    font-size: 12px;
    font-weight: 600;

    line-height: 1.4;
}

.acao-icon {
    font-size: 13px;
}


/* CADASTRO */

.acao-cadastro {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
}


/* EDIÇÃO */

.acao-edicao {
    background: #dbeafe;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
}


/* MANUTENÇÃO */

.acao-manutencao {
    background: #ffedd5;
    color: #c2410c;
    border: 1px solid #fed7aa;
}


/* EXCLUSÃO */

.acao-exclusao {
    background: #fee2e2;
    color: #b91c1c;
    border: 1px solid #fecaca;
}


/* OUTROS */

.acao-outros {
    background: #f3e8ff;
    color: #7e22ce;
    border: 1px solid #e9d5ff;
}

    </style>

</head>

<body>

<?php require_once "includes/sidebar.php"; ?>

<div class="container">

    <div class="topo">

        <div class="titulo">

            <h1>Histórico do Sistema</h1>

            <p>
                Registro das ações realizadas nos equipamentos
            </p>

        </div>

    </div>


    <div class="card">

        <table>

            <thead>

                <tr>

                    <th>Data / Hora</th>

                    <th>Usuário</th>

                    <th>Patrimônio</th>

                    <th>Equipamento</th>

                    <th>Ação</th>

                </tr>

            </thead>

            <tbody>

            <?php if ($resultado && $resultado->num_rows > 0): ?>

                <?php while ($item = $resultado->fetch_assoc()): ?>

                    <tr>

                        <td class="data">

                            <?php

                            if (!empty($item['data_hora'])) {

                                $data = new DateTime($item['data_hora']);

                                echo $data->format('d/m/Y H:i');

                            } else {

                                echo "-";

                            }

                            ?>

                        </td>


                        <td class="usuario">

                            <?= htmlspecialchars($item['usuario']) ?>

                        </td>


                        <td class="patrimonio">

                            <?= htmlspecialchars($item['patrimonio']) ?>

                        </td>


                        <td>

                            <?= htmlspecialchars($item['tipo']) ?>

                            <?php if (!empty($item['marca'])): ?>

                                - <?= htmlspecialchars($item['marca']) ?>

                            <?php endif; ?>

                            <?php if (!empty($item['modelo'])): ?>

                                <?= htmlspecialchars($item['modelo']) ?>

                            <?php endif; ?>

                        </td>


                        <td>

    <?php
    $acao = $item['acao'];

    if (stripos($acao, 'Cadastrou') !== false) {

        $classeAcao = 'acao-cadastro';
        $iconeAcao = '➕';

    } elseif (
        stripos($acao, 'Alterou') !== false ||
        stripos($acao, 'Editou') !== false
    ) {

        $classeAcao = 'acao-edicao';
        $iconeAcao = '✏️';

    } elseif (stripos($acao, 'manutenção') !== false) {

        $classeAcao = 'acao-manutencao';
        $iconeAcao = '🔧';

    } elseif (stripos($acao, 'Excluiu') !== false) {

        $classeAcao = 'acao-exclusao';
        $iconeAcao = '🗑️';

    } else {

        $classeAcao = 'acao-outros';
        $iconeAcao = '📋';
    }
    ?>

    <span class="acao-badge <?= $classeAcao ?>">

        <span class="acao-icon">
            <?= $iconeAcao ?>
        </span>

        <?= htmlspecialchars($acao); ?>

    </span>

</td>

                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>

                    <td colspan="5" class="vazio">

                        Nenhum registro encontrado no histórico.

                    </td>

                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

</body>

</html>