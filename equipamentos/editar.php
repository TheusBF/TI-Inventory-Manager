<?php
require_once "../auth/proteger.php";
require_once "../config/conexao.php";

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Equipamento não encontrado.");
}

$id = intval($_GET['id']);
$erro = "";

// BUSCAR EQUIPAMENTO
$sql = "SELECT * FROM equipamentos WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    die("Equipamento não encontrado.");
}

$equipamento = $resultado->fetch_assoc();

$stmt->close();


// ATUALIZAR EQUIPAMENTO
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $tipo = trim($_POST['tipo']);
    $marca = trim($_POST['marca']);
    $modelo = trim($_POST['modelo']);
    $numero_serie = trim($_POST['numero_serie']);
    $status = trim($_POST['status']);

    $responsavel = trim($_POST['responsavel']);
    $setor = trim($_POST['setor']);
    $localizacao = trim($_POST['localizacao']);

    $data_emprestimo = !empty($_POST['data_emprestimo'])
        ? $_POST['data_emprestimo']
        : null;


    $sql_update = "UPDATE equipamentos
                   SET tipo = ?,
                       marca = ?,
                       modelo = ?,
                       numero_serie = ?,
                       status = ?,
                       responsavel = ?,
                       setor = ?,
                       localizacao = ?,
                       data_emprestimo = ?
                   WHERE id = ?";

    $stmt_update = $conn->prepare($sql_update);

    $stmt_update->bind_param(
        "sssssssssi",
        $tipo,
        $marca,
        $modelo,
        $numero_serie,
        $status,
        $responsavel,
        $setor,
        $localizacao,
        $data_emprestimo,
        $id
    );

    if ($stmt_update->execute()) {

        header("Location: listar.php");
        exit;

    } else {

        $erro = "Erro ao atualizar equipamento: " . $stmt_update->error;
    }

    $stmt_update->close();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Editar Equipamento - TI Inventory Manager</title>

    <link rel="stylesheet" href="../assets/css/style.css">

</head>

<body>

<div class="layout">

    <!-- SIDEBAR -->

    <?php require_once "../includes/sidebar.php"; ?>


    <!-- CONTEÚDO -->

    <main class="main-content">

        <div class="topbar">

            <div>

                <h1>Editar Equipamento</h1>

                <p>Altere as informações do equipamento.</p>

            </div>

        </div>


        <?php if ($erro != ""): ?>

            <div class="alert alert-danger">
                <?= htmlspecialchars($erro); ?>
            </div>

        <?php endif; ?>


        <div class="dashboard-section">

            <form method="POST">


                <!-- PATRIMÔNIO -->

                <div class="form-group">

                    <label for="patrimonio">
                        Patrimônio
                    </label>

                    <input
                        type="text"
                        id="patrimonio"
                        value="<?= htmlspecialchars($equipamento['patrimonio']); ?>"
                        disabled
                    >

                </div>


                <!-- TIPO -->

                <div class="form-group">

                    <label for="tipo">
                        Tipo
                    </label>

                    <input
                        type="text"
                        id="tipo"
                        name="tipo"
                        value="<?= htmlspecialchars($equipamento['tipo']); ?>"
                        placeholder="Ex: Notebook"
                        required
                    >

                </div>


                <!-- MARCA -->

                <div class="form-group">

                    <label for="marca">
                        Marca
                    </label>

                    <input
                        type="text"
                        id="marca"
                        name="marca"
                        value="<?= htmlspecialchars($equipamento['marca']); ?>"
                        placeholder="Ex: Dell"
                        required
                    >

                </div>


                <!-- MODELO -->

                <div class="form-group">

                    <label for="modelo">
                        Modelo
                    </label>

                    <input
                        type="text"
                        id="modelo"
                        name="modelo"
                        value="<?= htmlspecialchars($equipamento['modelo']); ?>"
                        placeholder="Ex: Latitude 5420"
                        required
                    >

                </div>


                <!-- NÚMERO DE SÉRIE -->

                <div class="form-group">

                    <label for="numero_serie">
                        Número de Série
                    </label>

                    <input
                        type="text"
                        id="numero_serie"
                        name="numero_serie"
                        value="<?= htmlspecialchars($equipamento['numero_serie']); ?>"
                        placeholder="Ex: SN123456"
                        required
                    >

                </div>


                <!-- RESPONSÁVEL -->

                <div class="form-group">

                    <label for="responsavel">
                        Responsável
                    </label>

                    <input
                        type="text"
                        id="responsavel"
                        name="responsavel"
                        value="<?= htmlspecialchars($equipamento['responsavel'] ?? ''); ?>"
                        placeholder="Ex: João Silva"
                    >

                </div>


                <!-- SETOR -->

                <div class="form-group">

                    <label for="setor">
                        Setor
                    </label>

                    <input
                        type="text"
                        id="setor"
                        name="setor"
                        value="<?= htmlspecialchars($equipamento['setor'] ?? ''); ?>"
                        placeholder="Ex: Financeiro"
                    >

                </div>


                <!-- LOCALIZAÇÃO -->

                <div class="form-group">

                    <label for="localizacao">
                        Localização
                    </label>

                    <input
                        type="text"
                        id="localizacao"
                        name="localizacao"
                        value="<?= htmlspecialchars($equipamento['localizacao'] ?? ''); ?>"
                        placeholder="Ex: Sala 204"
                    >

                </div>


                <!-- DATA DO EMPRÉSTIMO -->

                <div class="form-group">

                    <label for="data_emprestimo">
                        Data do Empréstimo
                    </label>

                    <input
                        type="date"
                        id="data_emprestimo"
                        name="data_emprestimo"
                        value="<?= htmlspecialchars($equipamento['data_emprestimo'] ?? ''); ?>"
                    >

                </div>


                <!-- STATUS -->

                <div class="form-group">

                    <label for="status">
                        Status
                    </label>

                    <select
                        id="status"
                        name="status"
                        required
                    >

                        <option
                            value="Disponivel"
                            <?= $equipamento['status'] === 'Disponivel' ? 'selected' : ''; ?>
                        >
                            Disponível
                        </option>

                        <option
                            value="Emprestado"
                            <?= $equipamento['status'] === 'Emprestado' ? 'selected' : ''; ?>
                        >
                            Emprestado
                        </option>

                        <option
                            value="Manutencao"
                            <?= $equipamento['status'] === 'Manutencao' ? 'selected' : ''; ?>
                        >
                            Manutenção
                        </option>

                        <option
                            value="Baixado"
                            <?= $equipamento['status'] === 'Baixado' ? 'selected' : ''; ?>
                        >
                            Baixado
                        </option>

                    </select>

                </div>


                <!-- BOTÕES -->

                <div class="form-actions">

                    <button
                        type="submit"
                        class="btn"
                    >
                        Salvar Alterações
                    </button>

                    <a
                        href="listar.php"
                        class="btn btn-dashboard"
                    >
                        Cancelar
                    </a>

                </div>


            </form>

        </div>

    </main>

</div>

</body>

</html>