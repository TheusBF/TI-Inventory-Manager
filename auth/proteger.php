<?php

// Iniciar sessão somente se ainda não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// ==========================================
// VERIFICAR SE O USUÁRIO ESTÁ LOGADO
// ==========================================

if (
    !isset($_SESSION['usuario_id']) ||
    empty($_SESSION['usuario_id'])
) {

    header("Location: /TI-Inventory-Manager/auth/login.php");
    exit;
}


// ==========================================
// VERIFICAR DADOS BÁSICOS DA SESSÃO
// ==========================================

if (
    !isset($_SESSION['usuario_nome']) ||
    !isset($_SESSION['usuario_nivel'])
) {

    // Sessão incompleta ou inválida
    session_unset();
    session_destroy();

    header("Location: /TI-Inventory-Manager/auth/login.php");
    exit;
}

?>