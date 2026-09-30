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

/* Busca geral */

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

/*
|--------------------------------------------------------------------------
| CABEÇALHO DO ARQUIVO
|--------------------------------------------------------------------------
*/

$nomeArquivo = "equipamentos_" . date("Y-m-d_H-i-s") . ".xls";

header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"$nomeArquivo\"");
header("Pragma: no-cache");
header("Expires: 0");

/*
|--------------------------------------------------------------------------
| EXCEL
|--------------------------------------------------------------------------
*/

echo "\xEF\xBB\xBF";

?>

<table border="1">

    <tr>
        <th>ID</th>
        <th>Patrimônio</th>
        <th>Tipo</th>
        <th>Marca</th>
        <th>Modelo</th>
        <th>Número de Série</th>
        <th>Status</th>
        <th>Responsável</th>
        <th>Setor</th>
        <th>Localização</th>
        <th>Data do Empréstimo</th>
    </tr>

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

        <td>
            <?php

            switch ($equipamento['status']) {

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

            }

            ?>

        </td>

    </tr>

<?php endwhile; ?>

</table>

<?php

$stmt->close();
$conn->close();

?>