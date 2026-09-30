<?php
require_once "../auth/proteger.php";
require_once "../config/conexao.php";

$statusFiltro = $_GET['status'] ?? '';
$tipoFiltro = $_GET['tipo'] ?? '';
$responsavelFiltro = $_GET['responsavel'] ?? '';
$busca = $_GET['busca'] ?? '';

$busca = trim($busca);

$sql = "SELECT * FROM equipamentos WHERE 1=1";

$params = [];
$types = "";

if (!empty($busca)) {

    $sql .= " AND (
        patrimonio LIKE ?
        OR tipo LIKE ?
        OR marca LIKE ?
        OR modelo LIKE ?
        OR numero_serie LIKE ?
    )";

    $termo = "%$busca%";

    for ($i = 0; $i < 5; $i++) {
        $params[] = $termo;
        $types .= "s";
    }
}

if (!empty($statusFiltro)) {

    $sql .= " AND status = ?";

    $params[] = $statusFiltro;
    $types .= "s";
}

if (!empty($tipoFiltro)) {

    $sql .= " AND tipo LIKE ?";

    $params[] = "%$tipoFiltro%";
    $types .= "s";
}

if (!empty($responsavelFiltro)) {

    $sql .= " AND responsavel LIKE ?";

    $params[] = "%$responsavelFiltro%";
    $types .= "s";
}

$sql .= " ORDER BY id DESC";

$stmt = $conn->prepare($sql);

if (!empty($params)) {

    $stmt->bind_param(
        $types,
        ...$params
    );

    $stmt->execute();

    $resultado = $stmt->get_result();

} else {

    $resultado = $conn->query(
        "SELECT * FROM equipamentos ORDER BY id DESC"
    );
}

if (!$resultado) {
    die("Erro ao consultar equipamentos: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Equipamentos - TI Inventory Manager</title>

    <link rel="stylesheet" href="../assets/css/style.css">

</head>

<body>

<div class="layout">

    <!-- MENU LATERAL -->

    <?php require_once "../includes/sidebar.php"; ?>


    <!-- CONTEÚDO -->

    <main class="main-content">

        <header class="topbar">

            <div>

                <h1>Equipamentos</h1>

                <p>Gerencie os equipamentos cadastrados</p>

            </div>

            <a href="cadastrar.php" class="btn">
                + Novo equipamento
            </a>

        </header>


        <!-- PESQUISA -->

        <section class="dashboard-section">

            <div class="section-header">

                <div>

                    <h2>Lista de equipamentos</h2>

                    <p>
                        Pesquise por patrimônio, tipo, marca, modelo ou número de série.
                    </p>

                </div>

            </div>


            <form method="GET" class="search-form filtros-avancados">

    <input
        type="text"
        name="busca"
        placeholder="🔍 Pesquisar..."
        value="<?= htmlspecialchars($busca); ?>"
    >

    <select name="status">

        <option value="">
            Todos os status
        </option>

        <option value="Disponivel"
            <?= $statusFiltro == 'Disponivel' ? 'selected' : ''; ?>>
            Disponível
        </option>

        <option value="Emprestado"
            <?= $statusFiltro == 'Emprestado' ? 'selected' : ''; ?>>
            Emprestado
        </option>

        <option value="Manutencao"
            <?= $statusFiltro == 'Manutencao' ? 'selected' : ''; ?>>
            Manutenção
        </option>

        <option value="Baixado"
            <?= $statusFiltro == 'Baixado' ? 'selected' : ''; ?>>
            Baixado
        </option>

    </select>

    <input
        type="text"
        name="tipo"
        placeholder="📦 Tipo"
        value="<?= htmlspecialchars($tipoFiltro); ?>"
    >

    <input
        type="text"
        name="responsavel"
        placeholder="👤 Responsável"
        value="<?= htmlspecialchars($responsavelFiltro); ?>"
    >

    <button type="submit" class="btn">
        🔎 Filtrar
    </button>

    <a href="listar.php" class="btn btn-dashboard">
        ♻️ Limpar
    </a>

    <a
    href="exportar_excel.php?busca=<?= urlencode($busca); ?>&status=<?= urlencode($statusFiltro); ?>&tipo=<?= urlencode($tipoFiltro); ?>&responsavel=<?= urlencode($responsavelFiltro); ?>"
    class="btn btn-excel"
>
        📊 Excel
    </a>

    <a
    href="exportar_pdf.php?busca=<?= urlencode($busca); ?>&status=<?= urlencode($statusFiltro); ?>&tipo=<?= urlencode($tipoFiltro); ?>&responsavel=<?= urlencode($responsavelFiltro); ?>"
    class="btn btn-pdf"
>
        📄 PDF
    </a>
</form>


            <!-- TABELA -->

            <div class="table-container">

                <table>

                    <thead>

                        <tr>

                            <th>ID</th>

                            <th>Patrimônio</th>

                            <th>Tipo</th>

                            <th>Marca</th>

                            <th>Modelo</th>

                            <th>Número de Série</th>

                            <th>Status</th>

                            <th>Ações</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php if ($resultado->num_rows > 0): ?>

                        <?php while ($equipamento = $resultado->fetch_assoc()): ?>

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

                                    <span class="<?= $classeStatus ?>">
                                        <?= htmlspecialchars($textoStatus); ?>
                                    </span>

                                </td>

                                <td class="acoes">
    <a href="detalhes.php?id=<?= $equipamento['id']; ?>" class="btn-acao btn-ver">
        👁️ Ver
    </a>

    <a href="editar.php?id=<?= $equipamento['id']; ?>" class="btn-acao btn-editar">
        ✏️ Editar
    </a>

    <a
        href="excluir.php?id=<?= $equipamento['id']; ?>"
        class="btn-acao btn-excluir"
        onclick="return confirm('Tem certeza que deseja excluir este equipamento?');"
    >
        🗑️ Excluir
    </a>
</td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="8" style="text-align: center; padding: 30px;">

                                Nenhum equipamento encontrado.

                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>

    </main>

</div>

</body>

</html>