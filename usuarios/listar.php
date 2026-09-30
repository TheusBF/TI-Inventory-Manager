<?php

require_once "../auth/proteger_admin.php";
require_once "../config/conexao.php";

$sql = "SELECT id, nome, usuario, nivel, criado_em
        FROM usuarios
        ORDER BY id DESC";

$resultado = $conn->query($sql);

if (!$resultado) {
    die("Erro ao consultar usuários: " . $conn->error);
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Usuários - Inventário de TI</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <style>

        .usuarios-container {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.06);
        }

        .usuarios-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .usuarios-header h1 {
            margin: 0;
        }

        .nivel-admin {
            background: #dbeafe;
            color: #1d4ed8;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .nivel-usuario {
            background: #e5e7eb;
            color: #374151;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .btn-novo {
            background: #16a34a;
            color: white;
        }

        .btn-novo:hover {
            background: #15803d;
        }

    </style>

</head>

<body>

<div class="app">

    <?php require_once "../includes/sidebar.php"; ?>


    <main class="main-content">

        <div class="topbar">

            <div>
                <h1>Usuários</h1>
                <p>Gerenciamento de usuários do sistema</p>
            </div>

        </div>


        <div class="usuarios-container">

            <div class="usuarios-header">

                <h2>
                    Usuários cadastrados
                </h2>

                <a
                    href="cadastrar.php"
                    class="btn btn-novo"
                >
                    ➕ Novo usuário
                </a>

            </div>


            <table>

                <thead>

                    <tr>

                        <th>ID</th>
                        <th>Nome</th>
                        <th>Usuário</th>
                        <th>Nível</th>
                        <th>Criado em</th>
                        <th>Ações</th>

                    </tr>

                </thead>

                <tbody>

                <?php while ($usuario = $resultado->fetch_assoc()): ?>

                    <tr>

                        <td>
                            <?= $usuario['id']; ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($usuario['nome']); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($usuario['usuario']); ?>
                        </td>

                        <td>

                            <?php if ($usuario['nivel'] === 'admin'): ?>

                                <span class="nivel-admin">
                                    Administrador
                                </span>

                            <?php else: ?>

                                <span class="nivel-usuario">
                                    Usuário
                                </span>

                            <?php endif; ?>

                        </td>

                        <td>

                            <?= date(
                                'd/m/Y H:i',
                                strtotime($usuario['criado_em'])
                            ); ?>

                        </td>

                        <td class="acoes">

                            <a
                                href="editar.php?id=<?= $usuario['id']; ?>"
                                class="btn-acao btn-editar"
                            >
                                ✏️ Editar
                            </a>

                            <?php if ($usuario['id'] != $_SESSION['usuario_id']): ?>

                                <a
                                    href="excluir.php?id=<?= $usuario['id']; ?>"
                                    class="btn-acao btn-excluir"
                                    onclick="return confirm('Tem certeza que deseja excluir este usuário?');"
                                >
                                    🗑️ Excluir
                                </a>

                            <?php endif; ?>

                        </td>

                    </tr>

                <?php endwhile; ?>

                </tbody>

            </table>

        </div>

    </main>

</div>

</body>

</html>

<?php

$conn->close();

?>