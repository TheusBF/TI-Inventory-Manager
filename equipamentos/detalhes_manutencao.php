```php
<?php
require_once "../auth/proteger.php";
require_once "../config/conexao.php";


// ==========================================
// VERIFICAR ID
// ==========================================

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Manutenção não encontrada.");
}

$id = intval($_GET['id']);


// ==========================================
// BUSCAR MANUTENÇÃO
// ==========================================

$sql = "SELECT
            m.*,
            e.patrimonio,
            e.tipo,
            e.marca,
            e.modelo,
            e.numero_serie,
            e.responsavel,
            e.setor,
            e.localizacao
        FROM manutencoes m
        INNER JOIN equipamentos e
            ON m.equipamento_id = e.id
        WHERE m.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

$resultado = $stmt->get_result();


if ($resultado->num_rows === 0) {
    die("Manutenção não encontrada.");
}


$manutencao = $resultado->fetch_assoc();

$stmt->close();


// ==========================================
// LIMPAR ESPAÇOS DOS TEXTOS
// ==========================================

$manutencao['problema'] = trim($manutencao['problema']);

$manutencao['observacoes'] = trim(
    $manutencao['observacoes'] ?? ''
);


// ==========================================
// FORMATAR DATA
// ==========================================

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
        Detalhes da Manutenção - TI Inventory Manager
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >


    <style>

        /* ==========================================
           PÁGINA
        ========================================== */

        .maintenance-details-page {
            max-width: 1150px;
        }


        /* ==========================================
           CABEÇALHO
        ========================================== */

        .details-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 25px;
        }

        .details-title {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .details-title-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: #eff6ff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 23px;
        }

        .details-title h1 {
            margin: 0 0 5px 0;
        }

        .details-title p {
            margin: 0;
            color: #6b7280;
        }


        /* ==========================================
           STATUS
        ========================================== */

        .details-status {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
        }

        .status-andamento {
            background: #fef3c7;
            color: #92400e;
        }

        .status-concluida {
            background: #dcfce7;
            color: #166534;
        }


        /* ==========================================
           CARDS
        ========================================== */

        .details-card {
            background: white;
            border-radius: 14px;
            border: 1px solid #eef0f3;
            box-shadow: 0 3px 12px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            overflow: hidden;
        }

        .details-card-header {
            padding: 22px 26px;
            border-bottom: 1px solid #eef0f3;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .details-card-icon {
            width: 40px;
            height: 40px;
            border-radius: 9px;
            background: #eff6ff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 19px;
        }

        .details-card-header h2 {
            margin: 0 0 3px 0;
            font-size: 17px;
        }

        .details-card-header p {
            margin: 0;
            color: #6b7280;
            font-size: 12px;
        }


        /* ==========================================
           EQUIPAMENTO
        ========================================== */

        .equipment-main {
            padding: 26px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 25px;
        }

        .equipment-main-info {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .equipment-main-icon {
            width: 58px;
            height: 58px;
            border-radius: 12px;
            background: #e0edff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 27px;
        }

        .equipment-main h2 {
            margin: 0 0 5px 0;
            font-size: 20px;
        }

        .equipment-main p {
            margin: 0;
            color: #6b7280;
            font-size: 14px;
        }

        .equipment-patrimonio {
            background: #111827;
            color: white;
            padding: 9px 14px;
            border-radius: 7px;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
        }


        /* ==========================================
           GRID DE INFORMAÇÕES
        ========================================== */

        .details-grid {
            padding: 26px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
        }

        .detail-item {
            padding: 16px;
            background: #f9fafb;
            border: 1px solid #eef0f3;
            border-radius: 9px;
        }

        .detail-label {
            display: block;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #9ca3af;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .detail-value {
            display: block;
            font-size: 14px;
            color: #111827;
            font-weight: 500;
            word-break: break-word;
        }


        /* ==========================================
           PROBLEMA E OBSERVAÇÕES
        ========================================== */

        .text-content {
            padding: 26px;
        }

        .text-block {
            margin: 0 0 25px 0;
            padding: 0;
            text-align: left;
        }

        .text-block:last-child {
            margin-bottom: 0;
        }

        .text-block h3 {
            margin: 0 0 10px 0;
            padding: 0;
            font-size: 13px;
            color: #374151;
            font-weight: 600;
            text-align: left;
        }

        .text-box {
            display: block;
            width: 100%;
            min-height: 80px;
            box-sizing: border-box;

            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 10px;

            padding: 18px 20px;

            color: #374151;
            font-size: 14px;
            line-height: 1.7;

            text-align: left;
            vertical-align: top;

            white-space: pre-wrap;
            word-break: break-word;

            margin: 0;
        }


        /* ==========================================
           CUSTO
        ========================================== */

        .cost-highlight {
            color: #166534;
            font-weight: 700;
        }


        /* ==========================================
           BOTÕES
        ========================================== */

        .details-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 25px;
        }

        .details-actions .btn {
            height: 44px;
            min-width: 170px;
            padding: 0 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }

        .btn-edit-details {
            background: #2563eb;
            color: white;
        }

        .btn-edit-details:hover {
            background: #1d4ed8;
        }

        .btn-back-details {
            background: #6b7280;
            color: white;
        }

        .btn-back-details:hover {
            background: #4b5563;
        }


        /* ==========================================
           RESPONSIVO
        ========================================== */

        @media (max-width: 900px) {

            .details-grid {
                grid-template-columns: repeat(2, 1fr);
            }

        }


        @media (max-width: 650px) {

            .details-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .equipment-main {
                align-items: flex-start;
                flex-direction: column;
            }

            .details-grid {
                grid-template-columns: 1fr;
            }

            .details-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .details-actions .btn {
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

        <div class="maintenance-details-page">


            <!-- ==========================================
                 CABEÇALHO
            ========================================== -->

            <div class="details-header">

                <div class="details-title">

                    <div class="details-title-icon">
                        👁️
                    </div>

                    <div>

                        <h1>
                            Detalhes da Manutenção
                        </h1>

                        <p>
                            Visualização completa do atendimento técnico.
                        </p>

                    </div>

                </div>


                <?php if ($manutencao['status'] === 'Em andamento'): ?>

                    <span class="details-status status-andamento">
                        🔧 Em andamento
                    </span>

                <?php else: ?>

                    <span class="details-status status-concluida">
                        ✅ Concluída
                    </span>

                <?php endif; ?>

            </div>



            <!-- ==========================================
                 EQUIPAMENTO
            ========================================== -->

            <div class="details-card">

                <div class="equipment-main">

                    <div class="equipment-main-info">

                        <div class="equipment-main-icon">
                            💻
                        </div>

                        <div>

                            <h2>
                                <?= htmlspecialchars($manutencao['tipo']); ?>
                            </h2>

                            <p>

                                <?= htmlspecialchars($manutencao['marca']); ?>

                                <?php if (!empty($manutencao['modelo'])): ?>

                                    ·

                                    <?= htmlspecialchars($manutencao['modelo']); ?>

                                <?php endif; ?>

                            </p>

                        </div>

                    </div>


                    <div class="equipment-patrimonio">

                        Patrimônio:
                        <?= htmlspecialchars($manutencao['patrimonio']); ?>

                    </div>

                </div>

            </div>



            <!-- ==========================================
                 INFORMAÇÕES DA MANUTENÇÃO
            ========================================== -->

            <div class="details-card">


                <div class="details-card-header">

                    <div class="details-card-icon">
                        🔧
                    </div>

                    <div>

                        <h2>
                            Informações da manutenção
                        </h2>

                        <p>
                            Dados do atendimento realizado.
                        </p>

                    </div>

                </div>



                <div class="details-grid">


                    <!-- ENTRADA -->

                    <div class="detail-item">

                        <span class="detail-label">
                            Data de entrada
                        </span>

                        <span class="detail-value">
                            📅 <?= formatarData($manutencao['data_entrada']); ?>
                        </span>

                    </div>



                    <!-- SAÍDA -->

                    <div class="detail-item">

                        <span class="detail-label">
                            Data de saída
                        </span>

                        <span class="detail-value">
                            📅 <?= formatarData($manutencao['data_saida']); ?>
                        </span>

                    </div>



                    <!-- TÉCNICO -->

                    <div class="detail-item">

                        <span class="detail-label">
                            Técnico responsável
                        </span>

                        <span class="detail-value">

                            👤

                            <?= !empty($manutencao['tecnico'])
                                ? htmlspecialchars($manutencao['tecnico'])
                                : 'Não informado';
                            ?>

                        </span>

                    </div>



                    <!-- CUSTO -->

                    <div class="detail-item">

                        <span class="detail-label">
                            Custo
                        </span>

                        <span class="detail-value cost-highlight">

                            💰

                            <?php if ($manutencao['custo'] !== null): ?>

                                R$

                                <?= number_format(
                                    $manutencao['custo'],
                                    2,
                                    ',',
                                    '.'
                                ); ?>

                            <?php else: ?>

                                Não informado

                            <?php endif; ?>

                        </span>

                    </div>



                    <!-- NÚMERO DE SÉRIE -->

                    <div class="detail-item">

                        <span class="detail-label">
                            Número de série
                        </span>

                        <span class="detail-value">

                            <?= !empty($manutencao['numero_serie'])
                                ? htmlspecialchars($manutencao['numero_serie'])
                                : 'Não informado';
                            ?>

                        </span>

                    </div>



                    <!-- SETOR -->

                    <div class="detail-item">

                        <span class="detail-label">
                            Setor
                        </span>

                        <span class="detail-value">

                            <?= !empty($manutencao['setor'])
                                ? htmlspecialchars($manutencao['setor'])
                                : 'Não informado';
                            ?>

                        </span>

                    </div>



                    <!-- LOCALIZAÇÃO -->

                    <div class="detail-item">

                        <span class="detail-label">
                            Localização
                        </span>

                        <span class="detail-value">

                            <?= !empty($manutencao['localizacao'])
                                ? htmlspecialchars($manutencao['localizacao'])
                                : 'Não informado';
                            ?>

                        </span>

                    </div>



                    <!-- RESPONSÁVEL -->

                    <div class="detail-item">

                        <span class="detail-label">
                            Responsável pelo equipamento
                        </span>

                        <span class="detail-value">

                            <?= !empty($manutencao['responsavel'])
                                ? htmlspecialchars($manutencao['responsavel'])
                                : 'Não informado';
                            ?>

                        </span>

                    </div>



                    <!-- ID -->

                    <div class="detail-item">

                        <span class="detail-label">
                            Registro da manutenção
                        </span>

                        <span class="detail-value">

                            #<?= $manutencao['id']; ?>

                        </span>

                    </div>


                </div>

            </div>



            <!-- ==========================================
                 PROBLEMA E OBSERVAÇÕES
            ========================================== -->

            <div class="details-card">


                <div class="details-card-header">

                    <div class="details-card-icon">
                        📝
                    </div>

                    <div>

                        <h2>
                            Descrição do atendimento
                        </h2>

                        <p>
                            Problema identificado e observações registradas.
                        </p>

                    </div>

                </div>



                <div class="text-content">


                    <!-- PROBLEMA -->

                    <div class="text-block">

                        <h3>
                            ⚠️ Problema / Motivo
                        </h3>

                        <div class="text-box"><?= htmlspecialchars($manutencao['problema']); ?></div>

                    </div>



                    <!-- OBSERVAÇÕES -->

                    <div class="text-block">

                        <h3>
                            📋 Observações
                        </h3>

                        <div class="text-box"><?php if (!empty($manutencao['observacoes'])): ?><?= htmlspecialchars($manutencao['observacoes']); ?><?php else: ?>Nenhuma observação registrada.<?php endif; ?></div>

                    </div>


                </div>

            </div>



            <!-- ==========================================
                 BOTÕES
            ========================================== -->

            <div class="details-actions">

                <a
                    href="editar_manutencao.php?id=<?= $manutencao['id']; ?>"
                    class="btn btn-edit-details"
                >
                    ✏️ Editar manutenção
                </a>


                <a
                    href="manutencao.php"
                    class="btn btn-back-details"
                >
                    ← Voltar para manutenção
                </a>

            </div>


        </div>

    </main>

</div>

</body>

</html>
```
