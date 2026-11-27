<?php
// esse é o delete.php
require_once("function.php");
require_admin();

// A exclusão só acontece com ID válido e token CSRF correto
if (isset($_GET['id']) && csrf_check($_GET['token'] ?? null)) {
    delete($_GET['id']); // remove o registro, apaga a foto e redireciona
}

flash("Não foi possível realizar a operação.", "danger");
header("Location: index.php");
exit;