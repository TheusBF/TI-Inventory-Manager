<link rel="stylesheet" href="/TI-Inventory-Manager/assets/css/sidebar.css">

<?php
$paginaAtual = $_SERVER['REQUEST_URI'];
?>

<aside class="sidebar">

    <div class="logo">

        <div class="logo-icon">
            TI
        </div>

        <div>
            <strong>Inventory</strong>
            <span>Manager</span>
        </div>

    </div>


    <nav>

        <a href="/TI-Inventory-Manager/index.php"
   class="menu-item <?= strpos($paginaAtual, '/index.php') !== false ? 'active' : '' ?>">
            <span>🏠</span>
            Dashboard
        </a>

        <a href="/TI-Inventory-Manager/equipamentos/listar.php"
   class="menu-item <?= strpos($paginaAtual, '/equipamentos/listar.php') !== false ? 'active' : '' ?>">
            <span>💻</span>
            Equipamentos
        </a>

        <a href="/TI-Inventory-Manager/equipamentos/cadastrar.php"
   class="menu-item <?= strpos($paginaAtual, '/equipamentos/cadastrar.php') !== false ? 'active' : '' ?>">
            <span>➕</span>
            Cadastrar
        </a>

        <a href="/TI-Inventory-Manager/equipamentos/manutencao.php"
   class="menu-item <?= strpos($paginaAtual, '/equipamentos/manutencao.php') !== false ? 'active' : '' ?>">
            <span>🔧</span>
            Manutenção
        </a>

        <a href="/TI-Inventory-Manager/usuarios/listar.php"
   class="menu-item <?= strpos($paginaAtual, '/usuarios/listar.php') !== false ? 'active' : '' ?>">
            <span>👥</span>
            Usuários
        </a>

        <a href="/TI-Inventory-Manager/historico.php"
   class="menu-item <?= strpos($paginaAtual, '/historico.php') !== false ? 'active' : '' ?>">
            <span>📋</span>
            Histórico
        </a>

    </nav>


    <div class="sidebar-user">

        <div class="sidebar-user-icon">
            👤
        </div>

        <div class="sidebar-user-info">

            <strong>
                <?= htmlspecialchars($_SESSION['usuario_nome']); ?>
            </strong>

            <span>
                <?= htmlspecialchars($_SESSION['usuario_nivel']); ?>
            </span>

        </div>

    </div>


    <a
        href="/TI-Inventory-Manager/auth/logout.php"
        class="sidebar-logout"
    >
        🚪 Sair
    </a>

</aside>