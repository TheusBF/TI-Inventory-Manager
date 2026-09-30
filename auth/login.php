<?php

session_start();

require_once "../config/conexao.php";

if (isset($_SESSION['usuario_id'])) {
    header("Location: ../index.php");
    exit;
}

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $usuario = trim($_POST['usuario'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($usuario === '' || $senha === '') {

        $erro = "Preencha usuário e senha.";

    } else {

        $sql = "SELECT id, nome, usuario, senha, nivel
                FROM usuarios
                WHERE usuario = ?
                LIMIT 1";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param("s", $usuario);

        $stmt->execute();

        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 1) {

            $dados = $resultado->fetch_assoc();

            if (password_verify($senha, $dados['senha'])) {

                $_SESSION['usuario_id'] = $dados['id'];
                $_SESSION['usuario_nome'] = $dados['nome'];
                $_SESSION['usuario_usuario'] = $dados['usuario'];
                $_SESSION['usuario_nivel'] = $dados['nivel'];

                header("Location: ../index.php");
                exit;

            } else {

                $erro = "Usuário ou senha incorretos.";

            }

        } else {

            $erro = "Usuário ou senha incorretos.";

        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - Inventário de TI</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #111827;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
        }

        .login-box {
            background: white;
            padding: 35px;
            border-radius: 14px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-icon {
            font-size: 45px;
            margin-bottom: 10px;
        }

        .login-header h1 {
            color: #111827;
            font-size: 24px;
            margin-bottom: 8px;
        }

        .login-header p {
            color: #6b7280;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
        }

        .form-group input {
            width: 100%;
            height: 45px;
            padding: 0 14px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
        }

        .form-group input:focus {
            border-color: #2563eb;
        }

        .btn-login {
            width: 100%;
            height: 45px;
            border: none;
            border-radius: 8px;
            background: #2563eb;
            color: white;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-login:hover {
            background: #1d4ed8;
        }

        .erro {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }

        .rodape {
            text-align: center;
            margin-top: 20px;
            color: #9ca3af;
            font-size: 12px;
        }

    </style>

</head>

<body>

<div class="login-container">

    <div class="login-box">

        <div class="login-header">

            <div class="login-icon">
                🔐
            </div>

            <h1>Inventário de TI</h1>

            <p>Entre com suas credenciais</p>

        </div>


        <?php if ($erro !== ''): ?>

            <div class="erro">
                <?= htmlspecialchars($erro); ?>
            </div>

        <?php endif; ?>


        <form method="POST">

            <div class="form-group">

                <label for="usuario">
                    Usuário
                </label>

                <input
                    type="text"
                    id="usuario"
                    name="usuario"
                    placeholder="Digite seu usuário"
                    required
                    autofocus
                >

            </div>


            <div class="form-group">

                <label for="senha">
                    Senha
                </label>

                <input
                    type="password"
                    id="senha"
                    name="senha"
                    placeholder="Digite sua senha"
                    required
                >

            </div>


            <button
                type="submit"
                class="btn-login"
            >
                Entrar
            </button>

        </form>

    </div>

    <div class="rodape">
        Sistema de Inventário de TI
    </div>

</div>

</body>

</html>