<?php
// Esse é o logout.php
require_once("../config.php");
require_once(DBAPI); // carrega a sessão (auth.php)

// Limpa os dados e o cookie da sessão
$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}

session_destroy();

header("Location: " . BASEURL . "index.php");
exit;