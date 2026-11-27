<?php
// Esse é o valida.php (não imprime HTML: só valida e redireciona)
require_once("../config.php");
require_once(DBAPI);

$loginUrl = BASEURL . "inc/login.php";

$login = $_POST['login'] ?? null;
$senha = $_POST['senha'] ?? null;

if ($_SERVER['REQUEST_METHOD'] !== 'POST'
    || !is_string($login) || !is_string($senha)
    || trim($login) === '' || $senha === '') {
    flash("Informe usuário e senha.", "danger");
    header("Location: " . $loginUrl);
    exit;
}

$dados = null;
$bd = null;

try {
    $bd = open_database();
    $stmt = $bd->prepare(
        "SELECT id, nome, user, password, nivel FROM usuarios WHERE user = ? LIMIT 1"
    );
    $usuario = trim($login);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $dados = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Atualiza o hash se o algoritmo padrão do PHP mudou
    if ($dados && password_verify($senha, $dados['password'])
        && password_needs_rehash($dados['password'], PASSWORD_DEFAULT)) {
        $novo = criptografia($senha);
        $up = $bd->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
        $up->bind_param("si", $novo, $dados['id']);
        $up->execute();
        $up->close();
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    close_database($bd);
    flash("Não foi possível conectar. Tente novamente.", "danger");
    header("Location: " . $loginUrl);
    exit;
}

close_database($bd);

if ($dados && password_verify($senha, $dados['password'])) {
    session_regenerate_id(true); // evita session fixation
    $_SESSION['id']    = $dados['id'];
    $_SESSION['nome']  = $dados['nome'];
    $_SESSION['user']  = $dados['user'];
    $_SESSION['nivel'] = $dados['nivel'];
    flash("Bem vindo " . $dados['nome'] . "!", "info");
    header("Location: " . BASEURL . "index.php");
    exit;
}

flash("Não foi possível se conectar! Verifique seu usuário e senha.", "danger");
header("Location: " . $loginUrl);
exit;