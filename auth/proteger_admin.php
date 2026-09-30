<?php

require_once "proteger.php";

if ($_SESSION['usuario_nivel'] !== 'admin') {

    header("Location: ../index.php");
    exit;

}

?>