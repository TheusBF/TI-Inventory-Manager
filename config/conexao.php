<?php

$host = "localhost";
$usuario = "root";
$senha = "";
$banco = "inventario_ti";

$conn = new mysqli(
    $host,
    $usuario,
    $senha,
    $banco
);

if ($conn->connect_error) {
    die("Erro ao conectar ao banco de dados.");
}

$conn->set_charset("utf8mb4");