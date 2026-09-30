<?php
require_once "../auth/proteger.php";
require_once "../config/conexao.php";

// Verifica se recebeu o ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Equipamento não informado.");
}

$id = intval($_GET['id']);

// Exclui o equipamento
$sql = "DELETE FROM equipamentos WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {

    header("Location: listar.php");
    exit;

} else {

    die("Erro ao excluir equipamento: " . $conn->error);
}
?>