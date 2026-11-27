<?php
// esse é o delete.php (clientes)
require_once("function.php");

// Só exclui com ID e token CSRF válidos (o token é gerado na página da lista)
if (isset($_GET['id']) && csrf_check($_GET['token'] ?? null)) {
    delete($_GET['id']); // remove o registro, apaga a foto e redireciona
}

flash("Não foi possível realizar a operação.", "danger");
header("Location: index.php");
exit;
