<?php
/**
 * Sessão, mensagens (flash), escape de HTML e controle de acesso.
 * É carregado automaticamente pelo database.php (DBAPI).
 */

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/**
 * Escapa texto para exibir em HTML (evita XSS).
 */
if (!function_exists('e')) {
    function e($valor)
    {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Define uma mensagem para a próxima tela.
 */
if (!function_exists('flash')) {
    function flash($mensagem, $tipo = 'info')
    {
        $_SESSION['message'] = $mensagem;
        $_SESSION['type'] = $tipo;
    }
}

if (!function_exists('clear_messages')) {
    function clear_messages()
    {
        unset($_SESSION['message'], $_SESSION['type']);
    }
}

function is_logged()
{
    return !empty($_SESSION['user']);
}

function is_admin()
{
    return is_logged() && ($_SESSION['nivel'] ?? '') === 'admin';
}

/**
 * Exige usuário logado; senão manda para o login.
 */
function require_login()
{
    if (!is_logged()) {
        flash("Você precisa estar logado para acessar esse recurso!", "danger");
        header("Location: " . BASEURL . "inc/login.php");
        exit;
    }
}

/**
 * Exige usuário administrador.
 */
function require_admin()
{
    if (!is_logged()) {
        flash("Você precisa estar logado e ser administrador para acessar esse recurso!", "danger");
        header("Location: " . BASEURL . "inc/login.php");
        exit;
    }

    if (!is_admin()) {
        flash("Você precisa ser administrador para acessar esse recurso!", "danger");
        header("Location: " . BASEURL . "index.php");
        exit;
    }
}

/**
 * Token CSRF (usado nos links/ações que alteram dados).
 */
function csrf_token()
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_check($token)
{
    return is_string($token)
        && !empty($_SESSION['csrf'])
        && hash_equals($_SESSION['csrf'], $token);
}