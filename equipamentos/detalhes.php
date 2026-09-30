<?php
require_once "../auth/proteger.php";
require_once "../config/conexao.php";


// Verifica se recebeu o ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Equipamento não encontrado.");
}

$id = intval($_GET['id']);


// Busca o equipamento
$sql = "SELECT * FROM equipamentos WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

$resultado = $stmt->get_result();


// Verifica se encontrou
if ($resultado->num_rows === 0) {
    die("Equipamento não encontrado.");
}

$equipamento = $resultado->fetch_assoc();

$stmt->close();


// Define o nome do status
$status = $equipamento['status'];

$nomeStatus = match ($status) {
    'Disponivel' => 'Disponível',
    'Emprestado' => 'Emprestado',
    'Manutencao' => 'Manutenção',
    'Baixado' => 'Baixado',
    default => $status
};


// Define a classe visual do status
$classeStatus = match ($status) {
    'Disponivel' => 'status-disponivel',
    'Emprestado' => 'status-emprestado',
    'Manutencao' => 'status-manutencao',
    'Baixado' => 'status-baixado',
    default => ''
};


// Formata a data do empréstimo
$dataEmprestimo = "-";

if (!empty($equipamento['data_emprestimo'])) {

    $data = DateTime::createFromFormat(
        'Y-m-d',
        $equipamento['data_emprestimo']
    );

    if ($data) {
        $dataEmprestimo = $data->format('d/m/Y');
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
        Detalhes do Equipamento - TI Inventory Manager
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

    <style>

        .details-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .details-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e5e7eb;
        }

        .details-header h2 {
            margin: 0 0 6px 0;
        }

        .details-header p {
            margin: 0;
            color: #6b7280;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .detail-item {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 18px;
        }

        .detail-label {
            display: block;
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 7px;
        }

        .detail-value {
            display: block;
            font-size: 16px;
            font-weight: 600;
            color: #111827;
            word-break: break-word;
        }

        .detail-status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }

        .details-actions {
            display: flex;
            gap: 12px;
            margin-top: 30px;
        }

        .details-actions .btn {
            min-width: 150px;
            height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
            text-decoration: none;
            margin: 0;
        }

        @media (max-width: 700px) {

            .details-grid {
                grid-template-columns: 1fr;
            }

            .details-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .details-actions {
                flex-direction: column;
            }

            .details-actions .btn {
                width: 100%;
            }

        }

    </style>

</head>


<body>

<div class="layout">


    <!-- SIDEBAR -->

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
                class="menu-item active"
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
                class="menu-item"
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



    <!-- CONTEÚDO -->

    <main class="main-content">


        <div class="topbar">

            <div>

                <h1>
                    Detalhes do Equipamento
                </h1>

                <p>
                    Visualização completa das informações do equipamento.
                </p>

            </div>

        </div>



        <div class="details-card">


            <!-- CABEÇALHO -->

            <div class="details-header">

                <div>

                    <h2>
                        <?= htmlspecialchars($equipamento['tipo']); ?>
                    </h2>

                    <p>
                        Patrimônio:
                        <strong>
                            <?= htmlspecialchars($equipamento['patrimonio']); ?>
                        </strong>
                    </p>

                </div>


                <span class="detail-status <?= $classeStatus; ?>">

                    <?= htmlspecialchars($nomeStatus); ?>

                </span>

            </div>



            <!-- INFORMAÇÕES -->

            <div class="details-grid">


                <!-- PATRIMÔNIO -->

                <div class="detail-item">

                    <span class="detail-label">
                        Patrimônio
                    </span>

                    <span class="detail-value">
                        <?= htmlspecialchars($equipamento['patrimonio']); ?>
                    </span>

                </div>


                <!-- TIPO -->

                <div class="detail-item">

                    <span class="detail-label">
                        Tipo
                    </span>

                    <span class="detail-value">
                        <?= htmlspecialchars($equipamento['tipo']); ?>
                    </span>

                </div>


                <!-- MARCA -->

                <div class="detail-item">

                    <span class="detail-label">
                        Marca
                    </span>

                    <span class="detail-value">
                        <?= htmlspecialchars($equipamento['marca']); ?>
                    </span>

                </div>


                <!-- MODELO -->

                <div class="detail-item">

                    <span class="detail-label">
                        Modelo
                    </span>

                    <span class="detail-value">
                        <?= htmlspecialchars($equipamento['modelo']); ?>
                    </span>

                </div>


                <!-- NÚMERO DE SÉRIE -->

                <div class="detail-item">

                    <span class="detail-label">
                        Número de Série
                    </span>

                    <span class="detail-value">
                        <?= htmlspecialchars($equipamento['numero_serie']); ?>
                    </span>

                </div>


                <!-- STATUS -->

                <div class="detail-item">

                    <span class="detail-label">
                        Status
                    </span>

                    <span class="detail-value">

                        <span class="detail-status <?= $classeStatus; ?>">

                            <?= htmlspecialchars($nomeStatus); ?>

                        </span>

                    </span>

                </div>


                <!-- RESPONSÁVEL -->

                <div class="detail-item">

                    <span class="detail-label">
                        Responsável
                    </span>

                    <span class="detail-value">

                        <?= !empty($equipamento['responsavel'])
                            ? htmlspecialchars($equipamento['responsavel'])
                            : '-';
                        ?>

                    </span>

                </div>


                <!-- SETOR -->

                <div class="detail-item">

                    <span class="detail-label">
                        Setor
                    </span>

                    <span class="detail-value">

                        <?= !empty($equipamento['setor'])
                            ? htmlspecialchars($equipamento['setor'])
                            : '-';
                        ?>

                    </span>

                </div>


                <!-- LOCALIZAÇÃO -->

                <div class="detail-item">

                    <span class="detail-label">
                        Localização
                    </span>

                    <span class="detail-value">

                        <?= !empty($equipamento['localizacao'])
                            ? htmlspecialchars($equipamento['localizacao'])
                            : '-';
                        ?>

                    </span>

                </div>


                <!-- DATA DO EMPRÉSTIMO -->

                <div class="detail-item">

                    <span class="detail-label">
                        Data do Empréstimo
                    </span>

                    <span class="detail-value">

                        <?= htmlspecialchars($dataEmprestimo); ?>

                    </span>

                </div>


            </div>



            <!-- BOTÕES -->

            <div class="details-actions">

                <a
                    href="editar.php?id=<?= $equipamento['id']; ?>"
                    class="btn"
                >
                    ✏️ Editar
                </a>


                <a
                    href="listar.php"
                    class="btn btn-dashboard"
                >
                    ← Voltar
                </a>

            </div>


        </div>


    </main>

</div>

</body>

</html>