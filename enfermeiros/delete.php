<?php
// esse é o delete.php (enfermeiros)
require_once("function.php"); // já exige login

// Só exclui com ID e token CSRF válidos
if (isset($_GET['id']) && csrf_check($_GET['token'] ?? null)) {
    delete($_GET['id']); // remove o registro, apaga a foto e redireciona
}

flash("Não foi possível realizar a operação.", "danger");
header("Location: index.php");
exit;