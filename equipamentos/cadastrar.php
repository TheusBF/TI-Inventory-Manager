<?php

require_once "../auth/proteger.php";
require_once "../config/conexao.php";

$mensagem = "";
$tipoMensagem = "";

$patrimonio = "";
$tipo = "";
$marca = "";
$modelo = "";
$numero_serie = "";
$responsavel = "";
$setor = "";
$localizacao = "";
$data_emprestimo = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Dados do equipamento
    $patrimonio = trim($_POST['patrimonio'] ?? '');
    $tipo = trim($_POST['tipo'] ?? '');
    $marca = trim($_POST['marca'] ?? '');
    $modelo = trim($_POST['modelo'] ?? '');
    $numero_serie = trim($_POST['numero_serie'] ?? '');

    // Dados do responsável
    $responsavel = trim($_POST['responsavel'] ?? '');
    $setor = trim($_POST['setor'] ?? '');
    $localizacao = trim($_POST['localizacao'] ?? '');

    $data_emprestimo = !empty($_POST['data_emprestimo'])
        ? $_POST['data_emprestimo']
        : null;


    // ==========================================
    // VERIFICA SE O PATRIMÔNIO JÁ EXISTE
    // ==========================================

    $verificar = $conn->prepare(
        "SELECT id FROM equipamentos WHERE patrimonio = ?"
    );

    if (!$verificar) {
        die("Erro ao preparar verificação: " . $conn->error);
    }

    $verificar->bind_param("s", $patrimonio);
    $verificar->execute();

    $resultado = $verificar->get_result();


    if ($resultado->num_rows > 0) {

        $mensagem = "Já existe um equipamento cadastrado com o patrimônio $patrimonio.";
        $tipoMensagem = "danger";

    } else {

        // ==========================================
        // CADASTRA O EQUIPAMENTO
        // ==========================================

        $sql = "INSERT INTO equipamentos
                (
                    patrimonio,
                    tipo,
                    marca,
                    modelo,
                    numero_serie,
                    responsavel,
                    setor,
                    localizacao,
                    data_emprestimo
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            die("Erro ao preparar cadastro: " . $conn->error);
        }

        $stmt->bind_param(
            "sssssssss",
            $patrimonio,
            $tipo,
            $marca,
            $modelo,
            $numero_serie,
            $responsavel,
            $setor,
            $localizacao,
            $data_emprestimo
        );


        // ==========================================
        // EXECUTA O CADASTRO
        // ==========================================

        if ($stmt->execute()) {

            $equipamento_id = $conn->insert_id;


            // ==========================================
            // REGISTRA NO HISTÓRICO
            // ==========================================

            $usuario = $_SESSION['usuario_nome'] ?? 'Sistema';

            $stmtHistorico = $conn->prepare(
                "INSERT INTO historico
                (equipamento_id, usuario, acao)
                VALUES (?, ?, ?)"
            );

            if ($stmtHistorico) {

                $acao = "Cadastrou equipamento";

                $stmtHistorico->bind_param(
                    "iss",
                    $equipamento_id,
                    $usuario,
                    $acao
                );

                $stmtHistorico->execute();

                $stmtHistorico->close();
            }


            // ==========================================
            // MENSAGEM DE SUCESSO
            // ==========================================

            $mensagem = "Equipamento cadastrado com sucesso!";
            $tipoMensagem = "success";


            // ==========================================
            // LIMPA OS CAMPOS
            // ==========================================

            $patrimonio = "";
            $tipo = "";
            $marca = "";
            $modelo = "";
            $numero_serie = "";
            $responsavel = "";
            $setor = "";
            $localizacao = "";
            $data_emprestimo = "";


        } else {

            $mensagem = "Erro ao cadastrar equipamento: " . $stmt->error;
            $tipoMensagem = "danger";
        }


        // Fecha o statement UMA ÚNICA VEZ
        $stmt->close();
    }


    // Fecha a verificação
    $verificar->close();
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Cadastrar Equipamento - TI Inventory Manager</title>

    <link rel="stylesheet" href="../assets/css/style.css">

</head>

<body>

<div class="layout">

    <!-- SIDEBAR -->

    <?php require_once "../includes/sidebar.php"; ?>


    <!-- CONTEÚDO PRINCIPAL -->

    <main class="main-content">


        <!-- TOPO -->

        <header class="topbar">

            <div>

                <h1>Cadastrar Equipamento</h1>

                <p>Adicione um novo equipamento ao inventário</p>

            </div>

        </header>


        <!-- FORMULÁRIO -->

        <section class="dashboard-section">


            <?php if ($mensagem != ""): ?>

                <div class="alerta <?= $tipoMensagem ?>">

                    <?= htmlspecialchars($mensagem); ?>

                </div>

            <?php endif; ?>


            <form method="POST">


                <!-- PATRIMÔNIO -->

                <div class="form-group">

                    <label for="patrimonio">
                        Patrimônio
                    </label>

                    <input
                        type="text"
                        id="patrimonio"
                        name="patrimonio"
                        value="<?= htmlspecialchars($patrimonio); ?>"
                        placeholder="Ex: 10025"
                        required
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
                        value="<?= htmlspecialchars($tipo); ?>"
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
                        value="<?= htmlspecialchars($marca); ?>"
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
                        value="<?= htmlspecialchars($modelo); ?>"
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
                        value="<?= htmlspecialchars($numero_serie); ?>"
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
                        value="<?= htmlspecialchars($responsavel); ?>"
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
                        value="<?= htmlspecialchars($setor); ?>"
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
                        value="<?= htmlspecialchars($localizacao); ?>"
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
                        value="<?= htmlspecialchars($data_emprestimo); ?>"
                    >

                </div>


                <!-- BOTÕES -->

                <div class="form-actions">

                    <button
                        type="submit"
                        class="btn"
                    >
                        Salvar Equipamento
                    </button>


                    <a
                        href="listar.php"
                        class="btn btn-dashboard"
                    >
                        Ver Equipamentos
                    </a>

                </div>


            </form>

        </section>

    </main>

</div>

</body>

</html>