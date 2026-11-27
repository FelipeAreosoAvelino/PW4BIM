<?php

require_once('../config.php');
require_once(DBAPI);

$usuario = null;
$usuarios = null;

/**
 * Tamanho de texto em caracteres (funciona mesmo sem a extensão mbstring).
 */
function str_len($texto)
{
    return function_exists('mb_strlen')
        ? mb_strlen($texto, 'UTF-8')
        : (int) preg_match_all('/./us', $texto);
}

/**
 * Formatação de datas.
 */
function formatdata($data, $formato)
{
    $dt = new DateTime($data, new DateTimeZone('America/Sao_Paulo'));
    return $dt->format($formato);
}

/**
 * Listagem de usuários (com pesquisa opcional por nome).
 */
function index()
{
    global $usuarios;

    $pesquisa = trim($_POST['users'] ?? '');

    if ($pesquisa !== '') {
        $usuarios = search('usuarios', 'nome', $pesquisa);
    } else {
        $usuarios = find_all('usuarios');
    }
}

/**
 * Valida e monta os dados do usuário a partir do formulário.
 * Lança Exception se algo estiver inválido.
 */
function dados_usuario($post, $senha_obrigatoria)
{
    $usuario = [];

    $usuario['nome'] = trim((string) ($post['nome'] ?? ''));
    $usuario['user'] = trim((string) ($post['user'] ?? ''));
    $usuario['birthdate'] = trim((string) ($post['birthdate'] ?? ''));
    $senha = (string) ($post['password'] ?? '');

    if (str_len($usuario['nome']) < 3 || str_len($usuario['nome']) > 255) {
        throw new Exception('Informe o nome (mínimo 3 caracteres).');
    }

    if (!preg_match('/^[A-Za-z0-9_.@-]{3,50}$/', $usuario['user'])) {
        throw new Exception(
            'O usuário deve ter de 3 a 50 caracteres (letras, números, _ . @ -).'
        );
    }

    // Valida a data no formato YYYY-MM-DD e impede datas futuras.
    $data = DateTime::createFromFormat('!Y-m-d', $usuario['birthdate']);
    $errosData = DateTime::getLastErrors();

    if (
        !$data ||
        ($errosData !== false &&
            ($errosData['warning_count'] > 0 || $errosData['error_count'] > 0)) ||
        $data->format('Y-m-d') !== $usuario['birthdate'] ||
        $data > new DateTime('today')
    ) {
        throw new Exception('Data de nascimento inválida.');
    }

    $nivel = $post['nivel'] ?? 'user';
    $usuario['nivel'] = in_array($nivel, ['user', 'admin'], true)
        ? $nivel
        : 'user';

    if ($senha !== '') {
        if (strlen($senha) < 6) {
            throw new Exception('A senha deve ter no mínimo 6 caracteres.');
        }

        $usuario['password'] = criptografia($senha);
    } elseif ($senha_obrigatoria) {
        throw new Exception('Informe a senha.');
    }

    return $usuario;
}

/**
 * Verifica se já existe outro usuário com o mesmo login.
 */
function login_em_uso($login, $ignorar_id = 0)
{
    $lista = find_all('usuarios') ?: [];

    foreach ($lista as $u) {
        if (
            strcasecmp($u['user'], $login) === 0 &&
            (int) $u['id'] !== (int) $ignorar_id
        ) {
            return true;
        }
    }

    return false;
}

/**
 * Upload de imagem.
 * Retorna o nome do arquivo armazenado ou lança Exception.
 */
function upload($pasta_destino, $nome_temp, $tamanho_arquivo)
{
    if (!is_uploaded_file($nome_temp)) {
        throw new Exception('O arquivo não foi enviado corretamente.');
    }

    if ($tamanho_arquivo <= 0 || $tamanho_arquivo > 5 * 1024 * 1024) {
        throw new Exception('O arquivo deve ter no máximo 5 MB.');
    }

    $formatos = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
    ];

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($nome_temp);

    if (!isset($formatos[$mime])) {
        throw new Exception('Formato não permitido. Use JPG, PNG, WEBP ou GIF.');
    }

    if (@getimagesize($nome_temp) === false) {
        throw new Exception('O arquivo não é uma imagem válida.');
    }

    if (!is_dir($pasta_destino)) {
        if (!mkdir($pasta_destino, 0755, true) && !is_dir($pasta_destino)) {
            throw new Exception('Não foi possível criar a pasta de imagens.');
        }
    }

    if (!is_writable($pasta_destino)) {
        throw new Exception('A pasta de destino não possui permissão de escrita.');
    }

    $nomearquivo = bin2hex(random_bytes(16)) . '.' . $formatos[$mime];
    $arquivo_destino = rtrim($pasta_destino, "/\\")
        . DIRECTORY_SEPARATOR
        . $nomearquivo;

    if (!move_uploaded_file($nome_temp, $arquivo_destino)) {
        throw new Exception('Não foi possível armazenar o arquivo.');
    }

    return $nomearquivo;
}

/**
 * Trata o campo <input name="foto">.
 */
function receber_foto()
{
    if (
        !isset($_FILES['foto']) ||
        $_FILES['foto']['error'] === UPLOAD_ERR_NO_FILE
    ) {
        return null;
    }

    switch ($_FILES['foto']['error']) {
        case UPLOAD_ERR_OK:
            break;

        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            throw new Exception('A imagem é maior que o tamanho permitido.');

        default:
            throw new Exception('Erro no envio da imagem.');
    }

    return upload(
        IMG_DIR,
        $_FILES['foto']['tmp_name'],
        $_FILES['foto']['size']
    );
}

/**
 * Apaga uma foto da pasta de imagens, exceto a imagem padrão.
 */
function apagar_foto($nome)
{
    if (empty($nome) || $nome === 'semimagem.jpg') {
        return;
    }

    $caminho = rtrim(IMG_DIR, "/\\")
        . DIRECTORY_SEPARATOR
        . basename($nome);

    if (is_file($caminho)) {
        @unlink($caminho);
    }
}

/**
 * Cadastro de usuários.
 */
function add()
{
    global $usuario;

    if (empty($_POST['usuario'])) {
        return;
    }

    $nova_foto = null;

    try {
        $usuario = dados_usuario($_POST['usuario'], true);

        if (login_em_uso($usuario['user'])) {
            throw new Exception('Esse nome de usuário já está em uso.');
        }

        $nova_foto = receber_foto();
        $usuario['foto'] = $nova_foto ?? '';

        if (!save('usuarios', $usuario)) {
            throw new Exception('Não foi possível salvar o usuário.');
        }

        header('Location: index.php');
        exit;
    } catch (Exception $e) {
        apagar_foto($nova_foto);

        // Mantém os campos digitados, mas nunca devolve a senha.
        $entrada = is_array($_POST['usuario'] ?? null)
            ? $_POST['usuario']
            : [];

        $usuario = [
            'nome'      => is_string($entrada['nome'] ?? null)
                ? $entrada['nome']
                : '',
            'user'      => is_string($entrada['user'] ?? null)
                ? $entrada['user']
                : '',
            'birthdate' => is_string($entrada['birthdate'] ?? null)
                ? $entrada['birthdate']
                : '',
            'nivel'     => is_string($entrada['nivel'] ?? null)
                ? $entrada['nivel']
                : 'user',
        ];

        flash('Aconteceu um erro: ' . $e->getMessage(), 'danger');
    }
}

/**
 * Cadastro público de usuários.
 * Sempre cria contas com nível "user".
 */
function registrar_usuario()
{
    global $usuario;

    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        return;
    }

    $entrada = $_POST['usuario'] ?? null;
    $dadosEnviados = is_array($entrada) ? $entrada : [];
    $nova_foto = null;

    try {
        if (!is_array($entrada)) {
            throw new Exception('Dados do cadastro inválidos.');
        }

        if (!csrf_check($_POST['csrf'] ?? null)) {
            throw new Exception(
                'Sua sessão expirou. Atualize a página e tente novamente.'
            );
        }

        foreach (['nome', 'user', 'password', 'birthdate'] as $campo) {
            if (isset($entrada[$campo]) && !is_string($entrada[$campo])) {
                throw new Exception('Os dados informados são inválidos.');
            }
        }

        $dados = dados_usuario($entrada, true);

        // Cadastro público nunca pode criar administradores.
        $dados['nivel'] = 'user';

        if (login_em_uso($dados['user'])) {
            throw new Exception(
                'Esse nome de usuário já está em uso. Escolha outro.'
            );
        }

        $nova_foto = receber_foto();
        $dados['foto'] = $nova_foto ?? '';

        if (!save('usuarios', $dados)) {
            throw new Exception('Não foi possível salvar seu cadastro.');
        }

        flash(
            'Cadastro realizado com sucesso! Agora você já pode entrar na sua conta.',
            'success'
        );

        header('Location: ' . BASEURL . 'inc/login.php');
        exit;
    } catch (Exception $e) {
        apagar_foto($nova_foto);

        $usuario = [
            'nome' => is_string($dadosEnviados['nome'] ?? null)
                ? trim($dadosEnviados['nome'])
                : '',
            'user' => is_string($dadosEnviados['user'] ?? null)
                ? trim($dadosEnviados['user'])
                : '',
            'birthdate' => is_string($dadosEnviados['birthdate'] ?? null)
                ? trim($dadosEnviados['birthdate'])
                : '',
            'nivel' => 'user',
        ];

        flash(
            'Não foi possível realizar o cadastro: ' . $e->getMessage(),
            'danger'
        );
    }
}

/**
 * Atualização / edição de usuários.
 */
function edit()
{
    global $usuario;

    if (!isset($_GET['id']) || (int) $_GET['id'] <= 0) {
        header('Location: index.php');
        exit;
    }

    $id = (int) $_GET['id'];
    $atual = find('usuarios', $id);

    if (!$atual) {
        flash('Usuário não encontrado.', 'danger');
        header('Location: index.php');
        exit;
    }

    $usuario = $atual;

    if (empty($_POST['usuario'])) {
        return;
    }

    $nova_foto = null;

    try {
        $dados = dados_usuario($_POST['usuario'], false);

        if (login_em_uso($dados['user'], $id)) {
            throw new Exception('Esse nome de usuário já está em uso.');
        }

        // Impede que o usuário remova o próprio acesso de administrador.
        if ($id === (int) ($_SESSION['id'] ?? 0)) {
            $dados['nivel'] = $atual['nivel'];
        }

        $nova_foto = receber_foto();

        if ($nova_foto !== null) {
            $dados['foto'] = $nova_foto;
        }

        if (!update('usuarios', $id, $dados)) {
            throw new Exception('Não foi possível atualizar o usuário.');
        }

        if ($nova_foto !== null) {
            apagar_foto($atual['foto'] ?? null);
        }

        if ($id === (int) ($_SESSION['id'] ?? 0)) {
            $_SESSION['nome'] = $dados['nome'];
            $_SESSION['user'] = $dados['user'];
            $_SESSION['nivel'] = $dados['nivel'];
        }

        header('Location: index.php');
        exit;
    } catch (Exception $e) {
        apagar_foto($nova_foto);

        $entrada = is_array($_POST['usuario'] ?? null)
            ? $_POST['usuario']
            : [];

        $usuario = array_merge($atual, [
            'nome' => is_string($entrada['nome'] ?? null)
                ? $entrada['nome']
                : $atual['nome'],
            'user' => is_string($entrada['user'] ?? null)
                ? $entrada['user']
                : $atual['user'],
            'birthdate' => is_string($entrada['birthdate'] ?? null)
                ? $entrada['birthdate']
                : ($atual['birthdate'] ?? ''),
        ]);

        flash('Aconteceu um erro: ' . $e->getMessage(), 'danger');
    }
}

/**
 * Visualização de um usuário.
 */
function view($id = null)
{
    global $usuario;

    $usuario = find('usuarios', (int) $id);
}

/**
 * Exclusão de um usuário.
 */
function delete($id = null)
{
    $id = (int) $id;
    $usuario = find('usuarios', $id);

    if (!$usuario) {
        flash('Usuário não encontrado.', 'danger');
    } elseif ($id === (int) ($_SESSION['id'] ?? 0)) {
        flash('Você não pode excluir o seu próprio usuário.', 'danger');
    } elseif (remove('usuarios', $id)) {
        apagar_foto($usuario['foto'] ?? null);
    }

    header('Location: index.php');
    exit;
}
