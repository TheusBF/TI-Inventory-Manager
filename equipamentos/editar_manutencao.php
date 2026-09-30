```php
<?php
require_once "../auth/proteger.php";
require_once "../config/conexao.php";

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Manutenção não encontrada.");
}

$id = intval($_GET['id']);
$erro = "";


// ==========================================
// BUSCAR MANUTENÇÃO
// ==========================================

$sql = "SELECT
            m.*,
            e.patrimonio,
            e.tipo,
            e.marca,
            e.modelo
        FROM manutencoes m
        INNER JOIN equipamentos e
            ON m.equipamento_id = e.id
        WHERE m.id = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Erro ao preparar consulta: " . $conn->error);
}

$stmt->bind_param("i", $id);
$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    die("Manutenção não encontrada.");
}

$manutencao = $resultado->fetch_assoc();

$stmt->close();


// ==========================================
// ATUALIZAR MANUTENÇÃO
// ==========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    // ==========================================
    // GUARDAR DADOS ANTIGOS
    // ==========================================

    $data_entrada_antiga = $manutencao['data_entrada'];
    $problema_antigo = $manutencao['problema'];
    $tecnico_antigo = $manutencao['tecnico'];
    $observacoes_antigas = $manutencao['observacoes'];
    $data_saida_antiga = $manutencao['data_saida'];
    $custo_antigo = $manutencao['custo'];
    $status_antigo = $manutencao['status'];


    // ==========================================
    // RECEBER NOVOS DADOS
    // ==========================================

    $data_entrada = $_POST['data_entrada'] ?? '';
    $problema = trim($_POST['problema'] ?? '');
    $tecnico = trim($_POST['tecnico'] ?? '');
    $observacoes = trim($_POST['observacoes'] ?? '');

    $data_saida = !empty($_POST['data_saida'])
        ? $_POST['data_saida']
        : null;

    $custo = !empty($_POST['custo'])
        ? str_replace(',', '.', $_POST['custo'])
        : null;

    $status = $_POST['status'] ?? 'Em andamento';


    // ==========================================
    // VALIDAR CAMPOS OBRIGATÓRIOS
    // ==========================================

    if (empty($data_entrada) || empty($problema)) {

        $erro = "Preencha os campos obrigatórios.";

    } else {


        // ==========================================
        // DATA DE SAÍDA AUTOMÁTICA
        // ==========================================

        if ($status === "Concluida" && empty($data_saida)) {
            $data_saida = date('Y-m-d');
        }


        // ==========================================
        // ATUALIZAR MANUTENÇÃO
        // ==========================================

        $sql_update = "UPDATE manutencoes
                       SET data_entrada = ?,
                           problema = ?,
                           tecnico = ?,
                           observacoes = ?,
                           data_saida = ?,
                           custo = ?,
                           status = ?
                       WHERE id = ?";

        $stmt_update = $conn->prepare($sql_update);

        if (!$stmt_update) {

            $erro = "Erro ao preparar atualização: " . $conn->error;

        } else {

            $stmt_update->bind_param(
                "sssssssi",
                $data_entrada,
                $problema,
                $tecnico,
                $observacoes,
                $data_saida,
                $custo,
                $status,
                $id
            );


            // ==========================================
            // EXECUTAR ATUALIZAÇÃO
            // ==========================================

            if ($stmt_update->execute()) {


                // ==========================================
                // ATUALIZAR STATUS DO EQUIPAMENTO
                // ==========================================

                if ($status === "Concluida") {

                    $novoStatus = "Disponivel";

                } else {

                    $novoStatus = "Manutencao";
                }


                $sql_equipamento = "UPDATE equipamentos
                                    SET status = ?
                                    WHERE id = ?";

                $stmt_equipamento = $conn->prepare($sql_equipamento);

                if ($stmt_equipamento) {

                    $stmt_equipamento->bind_param(
                        "si",
                        $novoStatus,
                        $manutencao['equipamento_id']
                    );

                    $stmt_equipamento->execute();

                    $stmt_equipamento->close();
                }


                // ==========================================
// IDENTIFICAR ALTERAÇÕES
// ==========================================

$alteracoes = [];


// DATA DE ENTRADA
if ($data_entrada_antiga != $data_entrada) {

    $alteracoes[] =
        "Data de entrada: "
        . date('d/m/Y', strtotime($data_entrada_antiga))
        . " → "
        . date('d/m/Y', strtotime($data_entrada));
}


// PROBLEMA
if ($problema_antigo != $problema) {

    $alteracoes[] =
        "Problema alterado";
}


// TÉCNICO
if ($tecnico_antigo != $tecnico) {

    $tecnicoAnterior = !empty($tecnico_antigo)
        ? $tecnico_antigo
        : "Não informado";

    $tecnicoNovo = !empty($tecnico)
        ? $tecnico
        : "Não informado";

    $alteracoes[] =
        "Técnico: "
        . $tecnicoAnterior
        . " → "
        . $tecnicoNovo;
}


// OBSERVAÇÕES
if ($observacoes_antigas != $observacoes) {

    $alteracoes[] =
        "Observações alteradas";
}


// DATA DE SAÍDA
if ($data_saida_antiga != $data_saida) {

    $dataAnterior = !empty($data_saida_antiga)
        ? date('d/m/Y', strtotime($data_saida_antiga))
        : "Não definida";

    $dataNova = !empty($data_saida)
        ? date('d/m/Y', strtotime($data_saida))
        : "Não definida";

    $alteracoes[] =
        "Data de saída: "
        . $dataAnterior
        . " → "
        . $dataNova;
}


// CUSTO
if ((string)$custo_antigo != (string)$custo) {

    $custoAnterior = !empty($custo_antigo)
        ? "R$ " . number_format(
            (float)$custo_antigo,
            2,
            ',',
            '.'
        )
        : "Não informado";

    $custoNovo = !empty($custo)
        ? "R$ " . number_format(
            (float)$custo,
            2,
            ',',
            '.'
        )
        : "Não informado";

    $alteracoes[] =
        "Custo: "
        . $custoAnterior
        . " → "
        . $custoNovo;
}


// STATUS
if ($status_antigo != $status) {

    $statusAnterior = $status_antigo;

    $statusNovo = $status;

    $alteracoes[] =
        "Status: "
        . $statusAnterior
        . " → "
        . $statusNovo;
}


// ==========================================
// MONTAR AÇÃO DO HISTÓRICO
// ==========================================

if (!empty($alteracoes)) {

    $acao = "Alterou manutenção: "
          . implode(" | ", $alteracoes);

} else {

    $acao = "Editou manutenção sem alterações";
}


                // ==========================================
                // MONTAR AÇÃO DO HISTÓRICO
                // ==========================================

                if (!empty($alteracoes)) {

                    $acao = "Alterou manutenção: "
                          . implode(", ", $alteracoes);

                } else {

                    $acao = "Editou manutenção sem alterações";
                }


                // ==========================================
                // REGISTRAR NO HISTÓRICO
                // ==========================================

                $usuario = $_SESSION['usuario_nome'] ?? 'Sistema';

                $stmtHistorico = $conn->prepare(
                    "INSERT INTO historico
                    (equipamento_id, usuario, acao)
                    VALUES (?, ?, ?)"
                );

                if ($stmtHistorico) {

                    $stmtHistorico->bind_param(
                        "iss",
                        $manutencao['equipamento_id'],
                        $usuario,
                        $acao
                    );

                    $stmtHistorico->execute();

                    $stmtHistorico->close();
                }


                // ==========================================
                // VOLTAR PARA MANUTENÇÃO
                // ==========================================

                header("Location: manutencao.php");
                exit;


            } else {

                $erro = "Erro ao atualizar manutenção: "
                      . $stmt_update->error;
            }


            $stmt_update->close();
        }
    }
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
        Editar Manutenção - TI Inventory Manager
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >


    <style>

        /* ==========================================
           PÁGINA
        ========================================== */

        .edit-maintenance-page {
            max-width: 1100px;
        }


        /* ==========================================
           CABEÇALHO
        ========================================== */

        .edit-maintenance-header {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 25px;
        }

        .edit-maintenance-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: #eff6ff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 23px;
        }

        .edit-maintenance-header h1 {
            margin: 0 0 5px 0;
        }

        .edit-maintenance-header p {
            margin: 0;
            color: #6b7280;
        }


        /* ==========================================
           CARD PRINCIPAL
        ========================================== */

        .edit-maintenance-card {
            background: white;
            border-radius: 14px;
            border: 1px solid #eef0f3;
            box-shadow: 0 3px 12px rgba(0,0,0,0.05);
            overflow: hidden;
        }


        /* ==========================================
           INFORMAÇÕES DO EQUIPAMENTO
        ========================================== */

        .equipment-banner {
            padding: 25px 28px;
            background: #f8fafc;
            border-bottom: 1px solid #eef0f3;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .equipment-banner-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .equipment-icon {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            background: #e0edff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 25px;
            flex-shrink: 0;
        }

        .equipment-banner h2 {
            margin: 0 0 5px 0;
            font-size: 18px;
            color: #111827;
        }

        .equipment-subtitle {
            margin: 0;
            color: #6b7280;
            font-size: 13px;
        }

        .patrimonio-badge {
            background: #111827;
            color: white;
            padding: 8px 13px;
            border-radius: 7px;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
        }


        /* ==========================================
           STATUS DO EQUIPAMENTO
        ========================================== */

        .equipment-details {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }

        .equipment-detail {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 5px 9px;
            font-size: 12px;
            color: #4b5563;
        }


        /* ==========================================
           FORMULÁRIO
        ========================================== */

        .edit-form-content {
            padding: 28px;
        }

        .form-section-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .form-section-title-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: #eff6ff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
        }

        .form-section-title h3 {
            margin: 0;
            font-size: 17px;
            color: #111827;
        }

        .form-section-title p {
            margin: 3px 0 0 0;
            font-size: 12px;
            color: #6b7280;
        }

        .edit-form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .form-group-full {
            grid-column: 1 / -1;
        }

        .edit-form-content label {
            display: block;
            margin-bottom: 7px;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
        }

        .edit-form-content input,
        .edit-form-content select,
        .edit-form-content textarea {
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 11px 13px;
            background: white;
            color: #111827;
            font-family: inherit;
            font-size: 14px;
            transition: 0.2s;
        }

        .edit-form-content input:focus,
        .edit-form-content select:focus,
        .edit-form-content textarea:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.10);
        }

        .edit-form-content textarea {
            min-height: 110px;
            resize: vertical;
        }

        .field-hint {
            display: block;
            margin-top: 5px;
            font-size: 11px;
            color: #9ca3af;
        }


        /* ==========================================
           STATUS
        ========================================== */

        .status-field {
            position: relative;
        }

        .status-current {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 7px;
            font-size: 11px;
            color: #6b7280;
        }


        /* ==========================================
           ALERTA
        ========================================== */

        .edit-alert {
            margin-bottom: 22px;
            padding: 13px 16px;
            border-radius: 8px;
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
            font-size: 14px;
        }


        /* ==========================================
           BOTÕES
        ========================================== */

        .edit-form-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 28px;
            padding-top: 24px;
            border-top: 1px solid #eef0f3;
        }

        .edit-form-actions .btn {
            height: 44px;
            min-width: 180px;
            padding: 0 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
            border-radius: 8px;
            text-decoration: none;
            font-family: inherit;
            font-size: 14px;
            font-weight: 500;
            line-height: 1;
            border: none;
        }

        .btn-save-maintenance {
            background: #2563eb;
            color: white;
        }

        .btn-save-maintenance:hover {
            background: #1d4ed8;
        }

        .btn-back-maintenance {
            background: #6b7280;
            color: white;
        }

        .btn-back-maintenance:hover {
            background: #4b5563;
        }


        /* ==========================================
           RESPONSIVO
        ========================================== */

        @media (max-width: 800px) {

            .edit-form-grid {
                grid-template-columns: 1fr;
            }

            .form-group-full {
                grid-column: auto;
            }

            .equipment-banner {
                align-items: flex-start;
                flex-direction: column;
            }

            .patrimonio-badge {
                align-self: flex-start;
            }

        }


        @media (max-width: 600px) {

            .edit-form-content {
                padding: 20px;
            }

            .equipment-banner {
                padding: 20px;
            }

            .edit-maintenance-icon {
                display: none;
            }

            .edit-form-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .edit-form-actions .btn {
                width: 100%;
            }

        }

    </style>

</head>


<body>

<div class="layout">


    <!-- ==========================================
         SIDEBAR
    ========================================== -->

    <aside class="sidebar">

        <div class="logo">

            <div class="logo-icon">
                TI
            </div>

            <div>

                <strong>
                    Inventory
                </strong>

                <span>
                    Manager
                </span>

            </div>

        </div>


        <nav>

            <a
                href="../index.php"
                class="menu-item"
            >
                <span>🏠</span>
                Dashboard
            </a>


            <a
                href="listar.php"
                class="menu-item"
            >
                <span>💻</span>
                Equipamentos
            </a>


            <a
                href="cadastrar.php"
                class="menu-item"
            >
                <span>➕</span>
                Cadastrar
            </a>


            <a
                href="manutencao.php"
                class="menu-item active"
            >
                <span>🔧</span>
                Manutenção
            </a>

        </nav>


        <div class="sidebar-footer">

            <span>
                TI Inventory Manager
            </span>

            <small>
                v1.0
            </small>

        </div>

    </aside>



    <!-- ==========================================
         CONTEÚDO
    ========================================== -->

    <main class="main-content">

        <div class="edit-maintenance-page">


            <!-- CABEÇALHO -->

            <div class="edit-maintenance-header">

                <div class="edit-maintenance-icon">
                    ✏️
                </div>

                <div>

                    <h1>
                        Editar Manutenção
                    </h1>

                    <p>
                        Atualize as informações do atendimento técnico.
                    </p>

                </div>

            </div>



            <!-- CARD -->

            <div class="edit-maintenance-card">


                <!-- EQUIPAMENTO -->

                <div class="equipment-banner">

                    <div class="equipment-banner-left">

                        <div class="equipment-icon">
                            💻
                        </div>

                        <div>

                            <h2>
                                <?= htmlspecialchars($manutencao['tipo']); ?>
                            </h2>

                            <p class="equipment-subtitle">

                                <?= htmlspecialchars($manutencao['marca']); ?>

                                <?php if (!empty($manutencao['modelo'])): ?>

                                    ·

                                    <?= htmlspecialchars($manutencao['modelo']); ?>

                                <?php endif; ?>

                            </p>


                            <div class="equipment-details">

                                <span class="equipment-detail">
                                    Patrimônio:
                                    <strong>
                                        <?= htmlspecialchars($manutencao['patrimonio']); ?>
                                    </strong>
                                </span>

                            </div>

                        </div>

                    </div>


                    <div class="patrimonio-badge">

                        ID #<?= $manutencao['id']; ?>

                    </div>

                </div>



                <!-- FORMULÁRIO -->

                <div class="edit-form-content">


                    <!-- ERRO -->

                    <?php if ($erro != ""): ?>

                        <div class="edit-alert">

                            ⚠️

                            <?= htmlspecialchars($erro); ?>

                        </div>

                    <?php endif; ?>



                    <div class="form-section-title">

                        <div class="form-section-title-icon">
                            🔧
                        </div>

                        <div>

                            <h3>
                                Informações da manutenção
                            </h3>

                            <p>
                                Altere os dados registrados para este atendimento.
                            </p>

                        </div>

                    </div>



                    <form method="POST">


                        <div class="edit-form-grid">


                            <!-- DATA ENTRADA -->

                            <div class="form-group">

                                <label for="data_entrada">
                                    Data de Entrada *
                                </label>

                                <input
                                    type="date"
                                    id="data_entrada"
                                    name="data_entrada"
                                    value="<?= htmlspecialchars($manutencao['data_entrada']); ?>"
                                    required
                                >

                            </div>



                            <!-- DATA SAÍDA -->

                            <div class="form-group">

                                <label for="data_saida">
                                    Data de Saída
                                </label>

                                <input
                                    type="date"
                                    id="data_saida"
                                    name="data_saida"
                                    value="<?= htmlspecialchars($manutencao['data_saida'] ?? ''); ?>"
                                >

                                <span class="field-hint">
                                    Ao concluir sem data, o sistema preenche automaticamente.
                                </span>

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
                                ><?= htmlspecialchars($manutencao['problema']); ?></textarea>

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
                                    value="<?= htmlspecialchars($manutencao['tecnico'] ?? ''); ?>"
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
                                    value="<?= htmlspecialchars($manutencao['custo'] ?? ''); ?>"
                                    placeholder="Ex: 150,00"
                                >

                                <span class="field-hint">
                                    Informe somente o valor da manutenção.
                                </span>

                            </div>



                            <!-- STATUS -->

                            <div class="form-group status-field">

                                <label for="status">
                                    Status *
                                </label>

                                <select
                                    id="status"
                                    name="status"
                                    required
                                >

                                    <option
                                        value="Em andamento"
                                        <?= $manutencao['status'] === 'Em andamento'
                                            ? 'selected'
                                            : ''; ?>
                                    >
                                        🔧 Em andamento
                                    </option>

                                    <option
                                        value="Concluida"
                                        <?= $manutencao['status'] === 'Concluida'
                                            ? 'selected'
                                            : ''; ?>
                                    >
                                        ✅ Concluída
                                    </option>

                                </select>

                                <span class="status-current">

                                    Status atual:

                                    <strong>

                                        <?php if ($manutencao['status'] === 'Concluida'): ?>

                                            Concluída

                                        <?php else: ?>

                                            Em andamento

                                        <?php endif; ?>

                                    </strong>

                                </span>

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
                                ><?= htmlspecialchars($manutencao['observacoes'] ?? ''); ?></textarea>

                            </div>


                        </div>



                        <!-- BOTÕES -->

                        <div class="edit-form-actions">


                            <button
                                type="submit"
                                class="btn btn-save-maintenance"
                            >
                                💾 Salvar alterações
                            </button>


                            <a
                                href="manutencao.php"
                                class="btn btn-back-maintenance"
                            >
                                ← Voltar para manutenção
                            </a>


                        </div>


                    </form>

                </div>

            </div>

        </div>

    </main>

</div>

</body>

</html>
```
