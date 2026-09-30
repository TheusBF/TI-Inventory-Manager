```php
<?php
require_once "../auth/proteger.php";
require_once "../config/conexao.php";

$mensagem = "";
$tipoMensagem = "";


// CADASTRAR MANUTENÇÃO
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $equipamento_id = intval($_POST['equipamento_id']);
    $data_entrada = $_POST['data_entrada'];
    $problema = trim($_POST['problema']);
    $tecnico = trim($_POST['tecnico']);
    $observacoes = trim($_POST['observacoes']);

    $data_saida = !empty($_POST['data_saida'])
        ? $_POST['data_saida']
        : null;

    $custo = !empty($_POST['custo'])
        ? str_replace(',', '.', $_POST['custo'])
        : null;

    $status = $_POST['status'];


    if (
        $equipamento_id <= 0 ||
        empty($data_entrada) ||
        empty($problema)
    ) {

        $mensagem = "Preencha os campos obrigatórios.";
        $tipoMensagem = "danger";

    } else {

        $sql = "INSERT INTO manutencoes
                (
                    equipamento_id,
                    data_entrada,
                    problema,
                    tecnico,
                    observacoes,
                    data_saida,
                    custo,
                    status
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "isssssss",
            $equipamento_id,
            $data_entrada,
            $problema,
            $tecnico,
            $observacoes,
            $data_saida,
            $custo,
            $status
        );


        if ($stmt->execute()) {

    // ===============================
    // ATUALIZAR STATUS DO EQUIPAMENTO
    // ===============================

    if ($status === "Concluida") {
        $novo_status_equipamento = "Disponivel";
    } else {
        $novo_status_equipamento = "Manutencao";
    }

    $sql_status = "UPDATE equipamentos
                   SET status = ?
                   WHERE id = ?";

    $stmt_status = $conn->prepare($sql_status);

    if ($stmt_status) {

        $stmt_status->bind_param(
            "si",
            $novo_status_equipamento,
            $equipamento_id
        );

        $stmt_status->execute();
        $stmt_status->close();
    }


    // ===============================
    // REGISTRAR NO HISTÓRICO
    // ===============================

    $usuario = $_SESSION['usuario_nome'] ?? 'Sistema';

    $stmtHistorico = $conn->prepare(
        "INSERT INTO historico
        (equipamento_id, usuario, acao)
        VALUES (?, ?, ?)"
    );

    if ($stmtHistorico) {

        $acao = "Cadastrou manutenção";

        $stmtHistorico->bind_param(
            "iss",
            $equipamento_id,
            $usuario,
            $acao
        );

        $stmtHistorico->execute();

        $stmtHistorico->close();
    }


    $mensagem = "Manutenção cadastrada com sucesso!";
    $tipoMensagem = "success";

        } else {

            $mensagem = "Erro ao cadastrar manutenção: " . $stmt->error;
            $tipoMensagem = "danger";
        }


        $stmt->close();
    }
}


// BUSCAR EQUIPAMENTOS
$sql_equipamentos = "SELECT
                        id,
                        patrimonio,
                        tipo,
                        marca,
                        modelo,
                        status
                     FROM equipamentos
                     ORDER BY patrimonio ASC";

$resultado_equipamentos = $conn->query($sql_equipamentos);


// BUSCAR MANUTENÇÕES
$sql_manutencoes = "SELECT
                        m.*,
                        e.patrimonio,
                        e.tipo,
                        e.marca,
                        e.modelo
                    FROM manutencoes m
                    INNER JOIN equipamentos e
                    ON m.equipamento_id = e.id
                    ORDER BY m.id DESC";

$resultado_manutencoes = $conn->query($sql_manutencoes);


// CONTADORES
$totalManutencoes = 0;
$emAndamento = 0;
$concluidas = 0;

if ($resultado_manutencoes->num_rows > 0) {

    $totalManutencoes = $resultado_manutencoes->num_rows;

    while ($item = $resultado_manutencoes->fetch_assoc()) {

        if ($item['status'] === 'Em andamento') {
            $emAndamento++;
        }

        if ($item['status'] === 'Concluida') {
            $concluidas++;
        }
    }

    // Executa novamente a consulta para preencher a tabela
    $resultado_manutencoes = $conn->query($sql_manutencoes);
}


// FORMATAR DATA
function formatarData($data)
{
    if (empty($data)) {
        return "-";
    }

    $dataFormatada = DateTime::createFromFormat(
        'Y-m-d',
        $data
    );

    if ($dataFormatada) {
        return $dataFormatada->format('d/m/Y');
    }

    return $data;
}

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Manutenção - TI Inventory Manager
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >


    <style>

        /* ==============================
           PÁGINA
        ============================== */

        .maintenance-page {
            max-width: 1400px;
        }


        /* ==============================
           CABEÇALHO
        ============================== */

        .maintenance-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 25px;
        }

        .maintenance-title {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .maintenance-title-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: #eff6ff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .maintenance-title h1 {
            margin: 0 0 5px 0;
        }

        .maintenance-title p {
            margin: 0;
            color: #6b7280;
        }


        /* ==============================
           CARDS DE RESUMO
        ============================== */

        .maintenance-summary {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            margin-bottom: 25px;
        }

        .maintenance-summary-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 15px;
            border: 1px solid #eef0f3;
        }

        .summary-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }

        .summary-total {
            background: #eff6ff;
        }

        .summary-andamento {
            background: #fef3c7;
        }

        .summary-concluida {
            background: #dcfce7;
        }

        .summary-info span {
            display: block;
            color: #6b7280;
            font-size: 13px;
            margin-bottom: 4px;
        }

        .summary-info strong {
            display: block;
            font-size: 24px;
            color: #111827;
        }


        /* ==============================
           FORMULÁRIO
        ============================== */

        .maintenance-form {
            background: white;
            padding: 28px;
            border-radius: 14px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.05);
            border: 1px solid #eef0f3;
            margin-bottom: 30px;
        }

        .form-heading {
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 20px;
            margin-bottom: 25px;
            border-bottom: 1px solid #eef0f3;
        }

        .form-heading-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background: #eff6ff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .form-heading h2 {
            margin: 0 0 4px 0;
            font-size: 19px;
        }

        .form-heading p {
            margin: 0;
            color: #6b7280;
            font-size: 13px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .form-group-full {
            grid-column: 1 / -1;
        }

        .maintenance-form textarea {
            width: 100%;
            min-height: 110px;
            resize: vertical;
            box-sizing: border-box;
        }

        .maintenance-form select,
        .maintenance-form input,
        .maintenance-form textarea {
            font-family: inherit;
        }

        .maintenance-form label {
            display: block;
            margin-bottom: 7px;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
        }

        .maintenance-form input,
        .maintenance-form select,
        .maintenance-form textarea {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 11px 13px;
            background: #fff;
            color: #111827;
            font-size: 14px;
            transition: 0.2s;
        }

        .maintenance-form input:focus,
        .maintenance-form select:focus,
        .maintenance-form textarea:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.10);
        }


        /* ==============================
           BOTÃO
        ============================== */

        .maintenance-submit {
            margin-top: 25px;
        }

        .maintenance-submit .btn {
            min-width: 220px;
            height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
        }


        /* ==============================
           HISTÓRICO
        ============================== */

        .maintenance-history {
            background: white;
            border-radius: 14px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.05);
            border: 1px solid #eef0f3;
            overflow: hidden;
        }

        .history-header {
            padding: 24px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #eef0f3;
        }

        .history-header h2 {
            margin: 0 0 5px 0;
            font-size: 19px;
        }

        .history-header p {
            margin: 0;
            color: #6b7280;
            font-size: 13px;
        }

        .history-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .maintenance-table {
            overflow-x: auto;
        }

        .maintenance-table table {
            width: 100%;
            min-width: 1150px;
            border-collapse: collapse;
        }

        .maintenance-table th {
            background: #f9fafb;
            color: #6b7280;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            font-weight: 600;
        }

        .maintenance-table td,
        .maintenance-table th {
            padding: 15px 16px;
            text-align: left;
        }

        .maintenance-table tbody tr {
            border-top: 1px solid #f0f1f3;
            transition: 0.15s;
        }

        .maintenance-table tbody tr:hover {
            background: #f9fafb;
        }

        .equipment-name {
            font-weight: 600;
            color: #111827;
        }

        .equipment-model {
            display: block;
            margin-top: 3px;
            font-size: 12px;
            color: #6b7280;
        }

        .problem-text {
            max-width: 250px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .maintenance-status {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 7px 11px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }

        .maintenance-andamento {
            background: #fef3c7;
            color: #92400e;
        }

        .maintenance-concluida {
            background: #dcfce7;
            color: #166534;
        }

        .cost {
            white-space: nowrap;
            font-weight: 600;
        }

        .maintenance-actions {
            display: flex;
            gap: 5px;
            white-space: nowrap;
        }

        .maintenance-actions .btn-acao {
            margin: 0;
        }


        /* ==============================
           RESPONSIVO
        ============================== */

        @media (max-width: 900px) {

            .maintenance-summary {
                grid-template-columns: 1fr;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-group-full {
                grid-column: auto;
            }

        }

        @media (max-width: 600px) {

            .maintenance-form {
                padding: 20px;
            }

            .history-header {
                padding: 20px;
            }

            .maintenance-title {
                align-items: flex-start;
            }

            .maintenance-title-icon {
                display: none;
            }

        }

    </style>

</head>


<body>

<div class="layout">


    <!-- SIDEBAR -->

    <?php require_once "../includes/sidebar.php"; ?>



    <!-- CONTEÚDO -->

    <main class="main-content">

        <div class="maintenance-page">


            <!-- CABEÇALHO -->

            <div class="maintenance-header">

                <div class="maintenance-title">

                    <div class="maintenance-title-icon">
                        🔧
                    </div>

                    <div>

                        <h1>
                            Controle de Manutenção
                        </h1>

                        <p>
                            Registre e acompanhe as manutenções dos equipamentos.
                        </p>

                    </div>

                </div>

            </div>



            <!-- RESUMO -->

            <div class="maintenance-summary">


                <div class="maintenance-summary-card">

                    <div class="summary-icon summary-total">
                        📋
                    </div>

                    <div class="summary-info">

                        <span>
                            Total de manutenções
                        </span>

                        <strong>
                            <?= $totalManutencoes; ?>
                        </strong>

                    </div>

                </div>



                <div class="maintenance-summary-card">

                    <div class="summary-icon summary-andamento">
                        🔧
                    </div>

                    <div class="summary-info">

                        <span>
                            Em andamento
                        </span>

                        <strong>
                            <?= $emAndamento; ?>
                        </strong>

                    </div>

                </div>



                <div class="maintenance-summary-card">

                    <div class="summary-icon summary-concluida">
                        ✅
                    </div>

                    <div class="summary-info">

                        <span>
                            Concluídas
                        </span>

                        <strong>
                            <?= $concluidas; ?>
                        </strong>

                    </div>

                </div>


            </div>



            <!-- MENSAGEM -->

            <?php if ($mensagem != ""): ?>

                <div class="alert alert-<?= $tipoMensagem; ?>">

                    <?= htmlspecialchars($mensagem); ?>

                </div>

            <?php endif; ?>



            <!-- NOVA MANUTENÇÃO -->

            <div class="maintenance-form">


                <div class="form-heading">

                    <div class="form-heading-icon">
                        🔧
                    </div>

                    <div>

                        <h2>
                            Nova Manutenção
                        </h2>

                        <p>
                            Registre uma nova manutenção para um equipamento.
                        </p>

                    </div>

                </div>



                <form method="POST">

                    <div class="form-grid">


                        <!-- EQUIPAMENTO -->

                        <div class="form-group">

                            <label for="equipamento_id">
                                Equipamento *
                            </label>

                            <select
                                id="equipamento_id"
                                name="equipamento_id"
                                required
                            >

                                <option value="">
                                    Selecione um equipamento
                                </option>


                                <?php while ($equipamento = $resultado_equipamentos->fetch_assoc()): ?>

                                    <option
                                        value="<?= $equipamento['id']; ?>"
                                    >

                                        Patrimônio
                                        <?= htmlspecialchars($equipamento['patrimonio']); ?>

                                        -

                                        <?= htmlspecialchars($equipamento['tipo']); ?>

                                        -

                                        <?= htmlspecialchars($equipamento['marca']); ?>

                                    </option>

                                <?php endwhile; ?>

                            </select>

                        </div>



                        <!-- DATA DE ENTRADA -->

                        <div class="form-group">

                            <label for="data_entrada">
                                Data de Entrada *
                            </label>

                            <input
                                type="date"
                                id="data_entrada"
                                name="data_entrada"
                                value="<?= date('Y-m-d'); ?>"
                                required
                            >

                        </div>



                        <!-- PROBLEMA -->

                        <div class="form-group form-group-full">

                            <label for="problema">
                                Problema / Motivo *
                            </label>

                            <textarea
                                id="problema"
                                name="problema"
                                placeholder="Descreva o problema apresentado pelo equipamento..."
                                required
                            ></textarea>

                        </div>



                        <!-- TÉCNICO -->

                        <div class="form-group">

                            <label for="tecnico">
                                Técnico Responsável
                            </label>

                            <input
                                type="text"
                                id="tecnico"
                                name="tecnico"
                                placeholder="Ex: João Silva"
                            >

                        </div>



                        <!-- CUSTO -->

                        <div class="form-group">

                            <label for="custo">
                                Custo (R$)
                            </label>

                            <input
                                type="text"
                                id="custo"
                                name="custo"
                                placeholder="Ex: 150,00"
                            >

                        </div>



                        <!-- DATA DE SAÍDA -->

                        <div class="form-group">

                            <label for="data_saida">
                                Data de Saída
                            </label>

                            <input
                                type="date"
                                id="data_saida"
                                name="data_saida"
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
                            >

                                <option value="Em andamento">
                                    🔧 Em andamento
                                </option>

                                <option value="Concluida">
                                    ✅ Concluída
                                </option>

                            </select>

                        </div>



                        <!-- OBSERVAÇÕES -->

                        <div class="form-group form-group-full">

                            <label for="observacoes">
                                Observações
                            </label>

                            <textarea
                                id="observacoes"
                                name="observacoes"
                                placeholder="Adicione informações complementares..."
                            ></textarea>

                        </div>


                    </div>



                    <div class="maintenance-submit">

                        <button
                            type="submit"
                            class="btn"
                        >
                            🔧 Registrar Manutenção
                        </button>

                    </div>


                </form>

            </div>



            <!-- HISTÓRICO -->

            <div class="maintenance-history">


                <div class="history-header">

                    <div>

                        <h2>
                            Histórico de Manutenções
                        </h2>

                        <p>
                            Consulte e edite os registros de manutenção.
                        </p>

                    </div>

                    <div class="history-icon">
                        📋
                    </div>

                </div>



                <div class="table-container maintenance-table">

                    <table>

                        <thead>

                            <tr>

                                <th>
                                    Patrimônio
                                </th>

                                <th>
                                    Equipamento
                                </th>

                                <th>
                                    Problema
                                </th>

                                <th>
                                    Entrada
                                </th>

                                <th>
                                    Saída
                                </th>

                                <th>
                                    Técnico
                                </th>

                                <th>
                                    Custo
                                </th>

                                <th>
                                    Status
                                </th>

                                <th>
                                    Ações
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php if ($resultado_manutencoes->num_rows > 0): ?>


                                <?php while ($manutencao = $resultado_manutencoes->fetch_assoc()): ?>

                                    <tr>


                                        <!-- PATRIMÔNIO -->

                                        <td>

                                            <strong>
                                                <?= htmlspecialchars($manutencao['patrimonio']); ?>
                                            </strong>

                                        </td>


                                        <!-- EQUIPAMENTO -->

                                        <td>

                                            <span class="equipment-name">

                                                <?= htmlspecialchars($manutencao['tipo']); ?>

                                            </span>

                                            <span class="equipment-model">

                                                <?= htmlspecialchars($manutencao['marca']); ?>

                                                <?= htmlspecialchars($manutencao['modelo']); ?>

                                            </span>

                                        </td>


                                        <!-- PROBLEMA -->

                                        <td>

                                            <div
                                                class="problem-text"
                                                title="<?= htmlspecialchars($manutencao['problema']); ?>"
                                            >

                                                <?= htmlspecialchars($manutencao['problema']); ?>

                                            </div>

                                        </td>


                                        <!-- ENTRADA -->

                                        <td>

                                            <?= formatarData($manutencao['data_entrada']); ?>

                                        </td>


                                        <!-- SAÍDA -->

                                        <td>

                                            <?= formatarData($manutencao['data_saida']); ?>

                                        </td>


                                        <!-- TÉCNICO -->

                                        <td>

                                            <?= !empty($manutencao['tecnico'])
                                                ? htmlspecialchars($manutencao['tecnico'])
                                                : '-';
                                            ?>

                                        </td>


                                        <!-- CUSTO -->

                                        <td class="cost">

                                            <?php if ($manutencao['custo'] !== null): ?>

                                                R$

                                                <?= number_format(
                                                    $manutencao['custo'],
                                                    2,
                                                    ',',
                                                    '.'
                                                ); ?>

                                            <?php else: ?>

                                                -

                                            <?php endif; ?>

                                        </td>


                                        <!-- STATUS -->

                                        <td>

                                            <?php if ($manutencao['status'] === 'Em andamento'): ?>

                                                <span class="maintenance-status maintenance-andamento">
                                                    🔧 Em andamento
                                                </span>

                                            <?php else: ?>

                                                <span class="maintenance-status maintenance-concluida">
                                                    ✅ Concluída
                                                </span>

                                            <?php endif; ?>

                                        </td>


                                        <!-- AÇÕES -->

                                        <td>

                                            <div class="maintenance-actions">

    <a
        href="detalhes_manutencao.php?id=<?= $manutencao['id']; ?>"
        class="btn-acao btn-ver"
    >
        👁️ Ver
    </a>

    <a
        href="editar_manutencao.php?id=<?= $manutencao['id']; ?>"
        class="btn-acao btn-editar"
    >
        ✏️ Editar
    </a>

</div>

                                        </td>


                                    </tr>

                                <?php endwhile; ?>


                            <?php else: ?>

                                <tr>

                                    <td
                                        colspan="9"
                                        style="text-align: center; padding: 40px;"
                                    >

                                        <div style="font-size: 32px; margin-bottom: 10px;">
                                            🔧
                                        </div>

                                        <strong>
                                            Nenhuma manutenção registrada
                                        </strong>

                                        <p style="color: #6b7280; margin-top: 5px;">
                                            As manutenções cadastradas aparecerão aqui.
                                        </p>

                                    </td>

                                </tr>

                            <?php endif; ?>


                        </tbody>

                    </table>

                </div>

            </div>


        </div>

    </main>

</div>

</body>

</html>
```
