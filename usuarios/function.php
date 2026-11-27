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
    return function_exists('mb_strlen') ? mb_strlen($texto, 'UTF-8') : (int) preg_match_all('/./us', $texto);
}

/**
 * Formatação de datas
 */
function formatdata($data, $formato)
{
    $dt = new DateTime($data, new DateTimeZone("America/Sao_Paulo"));
    return $dt->format($formato);
}

/**
 * Listagem de Usuários (com pesquisa opcional por nome)
 */
function index()
{
    global $usuarios;

    $pesquisa = trim($_POST['users'] ?? '');

    if ($pesquisa !== '') {
        $usuarios = search("usuarios", "nome", $pesquisa);
    } else {
        $usuarios = find_all("usuarios");
    }
}

/**
 * Valida e monta os dados do usuário a partir do formulário.
 * Só os campos permitidos são aceitos (impede mandar "nivel" arbitrário).
 * Lança Exception se algo estiver inválido.
 */
function dados_usuario($post, $senha_obrigatoria)
{
    $usuario = [];

    $usuario['nome'] = trim($post['nome'] ?? '');
    $usuario['user'] = trim($post['user'] ?? '');
    $senha = (string) ($post['password'] ?? '');

    if (str_len($usuario['nome']) < 3 || str_len($usuario['nome']) > 255) {
        throw new Exception("Informe o nome (mínimo 3 caracteres).");
    }

    if (!preg_match('/^[A-Za-z0-9_.@-]{3,50}$/', $usuario['user'])) {
        throw new Exception("O usuário deve ter de 3 a 50 caracteres (letras, números, _ . @ -).");
    }

    $nivel = $post['nivel'] ?? 'user';
    $usuario['nivel'] = in_array($nivel, ['user', 'admin'], true) ? $nivel : 'user';

    if ($senha !== '') {
        if (strlen($senha) < 6) {
            throw new Exception("A senha deve ter no mínimo 6 caracteres.");
        }
        $usuario['password'] = criptografia($senha);
    } elseif ($senha_obrigatoria) {
        throw new Exception("Informe a senha.");
    }

    return $usuario;
}

/**
 * Verifica se já existe outro usuário com o mesmo login.
 */
function login_em_uso($login, $ignorar_id = 0)
{
    $lista = find_all("usuarios") ?: [];

    foreach ($lista as $u) {
        if (strcasecmp($u['user'], $login) === 0 && (int) $u['id'] !== (int) $ignorar_id) {
            return true;
        }
    }
    return false;
}

/**
 * Upload de imagens
 *
 * Retorna o nome do arquivo armazenado.
 * Lança uma Exception caso alguma validação falhe.
 */

function upload($pasta_destino, $nome_temp, $tamanho_arquivo)
{
    if (!is_uploaded_file($nome_temp)) {
        throw new Exception("O arquivo não foi enviado corretamente.");
    }

    if ($tamanho_arquivo <= 0 || $tamanho_arquivo > 5 * 1024 * 1024) {
        throw new Exception("O arquivo deve ter no máximo 5 MB.");
    }

    // Extensões permitidas de acordo com o tipo real da imagem
    $formatos = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
    ];

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($nome_temp);

    if (!isset($formatos[$mime])) {
        throw new Exception("Formato não permitido. Use JPG, PNG, WEBP ou GIF.");
    }

    if (@getimagesize($nome_temp) === false) {
        throw new Exception("O arquivo não é uma imagem válida.");
    }

    // Cria a pasta caso ainda não exista
    if (!is_dir($pasta_destino)) {
        if (!mkdir($pasta_destino, 0755, true) && !is_dir($pasta_destino)) {
            throw new Exception("Não foi possível criar a pasta de imagens.");
        }
    }

    if (!is_writable($pasta_destino)) {
        throw new Exception("A pasta de destino não possui permissão de escrita.");
    }

    $nomearquivo = bin2hex(random_bytes(16)) . "." . $formatos[$mime];

    $arquivo_destino = rtrim($pasta_destino, "/\\")
        . DIRECTORY_SEPARATOR . $nomearquivo;

    if (!move_uploaded_file($nome_temp, $arquivo_destino)) {
        throw new Exception("Não foi possível armazenar o arquivo.");
    }

    return $nomearquivo;
}


/**
 * Trata o campo <input name="foto">.
 * Retorna o nome do arquivo salvo ou null se nada foi enviado.
 */

function receber_foto()
{
    if (!isset($_FILES['foto']) || $_FILES['foto']['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    switch ($_FILES['foto']['error']) {
        case UPLOAD_ERR_OK:
            break;

        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            throw new Exception("A imagem é maior que o tamanho permitido.");

        default:
            throw new Exception("Erro no envio da imagem.");
    }

    // Utiliza o mesmo diretório configurado para Enfermeiros
    return upload(
        IMG_DIR,
        $_FILES['foto']['tmp_name'],
        $_FILES['foto']['size']
    );
}


/**
 * Apaga uma foto da pasta fotos/ (ignora vazio e a imagem padrão).
 */

function apagar_foto($nome)
{
    if (empty($nome) || $nome === 'semimagem.jpg') {
        return;
    }

    // Utiliza o mesmo diretório de armazenamento das imagens
    $caminho = IMG_DIR . basename($nome);

    if (is_file($caminho)) {
        @unlink($caminho);
    }
}

/**
 * Cadastro de Usuários
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
            throw new Exception("Esse nome de usuário já está em uso.");
        }

        $nova_foto = receber_foto();
        $usuario['foto'] = $nova_foto ?? '';

        if (!save("usuarios", $usuario)) {
            throw new Exception("Não foi possível salvar o usuário.");
        }

        header("Location: index.php");
        exit;

    } catch (Exception $e) {
        apagar_foto($nova_foto);

        // Mantém o que foi digitado (nunca devolve a senha)
        $usuario = [
            'nome'  => $_POST['usuario']['nome'] ?? '',
            'user'  => $_POST['usuario']['user'] ?? '',
            'nivel' => $_POST['usuario']['nivel'] ?? 'user',
        ];

        flash("Aconteceu um erro: " . $e->getMessage(), "danger");
    }
}

/**
 * Cadastro público de usuários.
 * Não exige autenticação e sempre cria contas com nível "user".
 */
function registrar_usuario()
{
    global $usuario;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    $entrada = $_POST['usuario'] ?? null;
    $dadosEnviados = is_array($entrada) ? $entrada : [];
    $nova_foto = null;

    try {

        if (!is_array($entrada)) {
            throw new Exception("Dados do cadastro inválidos.");
        }

        // Proteção CSRF
        if (!csrf_check($_POST['csrf'] ?? null)) {
            throw new Exception("Sua sessão expirou. Atualize a página e tente novamente.");
        }

        // Impede o envio de valores em formatos inesperados
        foreach (['nome', 'user', 'password'] as $campo) {
            if (isset($entrada[$campo]) && !is_string($entrada[$campo])) {
                throw new Exception("Os dados informados são inválidos.");
            }
        }

        // Valida nome, login e senha
        $dados = dados_usuario($entrada, true);

        // Cadastro público nunca pode criar administradores
        $dados['nivel'] = 'user';

        // Verifica se o login já existe
        if (login_em_uso($dados['user'])) {
            throw new Exception("Esse nome de usuário já está em uso. Escolha outro.");
        }

        // Recebe a foto, caso tenha sido enviada
        $nova_foto = receber_foto();
        $dados['foto'] = $nova_foto ?? '';

        // Salva no banco
        if (!save("usuarios", $dados)) {
            throw new Exception("Não foi possível salvar seu cadastro.");
        }

        // Mensagem de sucesso para a tela de login
        flash(
            "Cadastro realizado com sucesso! Agora você já pode entrar na sua conta.",
            "success"
        );

        header("Location: " . BASEURL . "inc/login.php");
        exit;

    } catch (Exception $e) {

        // Remove a foto caso o cadastro não tenha sido concluído
        apagar_foto($nova_foto);

        // Mantém os campos preenchidos, exceto a senha
        $usuario = [
            'nome' => is_string($dadosEnviados['nome'] ?? null)
                ? trim($dadosEnviados['nome']) : '',

            'user' => is_string($dadosEnviados['user'] ?? null)
                ? trim($dadosEnviados['user']) : '',

            'nivel' => 'user'
        ];

        flash(
            "Não foi possível realizar o cadastro: " . $e->getMessage(),
            "danger"
        );
    }
}

/**
 * Atualização / Edição de Usuários
 */
function edit()
{
    global $usuario;

    if (!isset($_GET['id']) || (int) $_GET['id'] <= 0) {
        header("Location: index.php");
        exit;
    }

    $id = (int) $_GET['id'];
    $atual = find("usuarios", $id);

    if (!$atual) {
        flash("Usuário não encontrado.", "danger");
        header("Location: index.php");
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
            throw new Exception("Esse nome de usuário já está em uso.");
        }

        // Admin não pode tirar o próprio acesso de administrador
        if ($id === (int) ($_SESSION['id'] ?? 0)) {
            $dados['nivel'] = $atual['nivel'];
        }

        $nova_foto = receber_foto();
        if ($nova_foto !== null) {
            $dados['foto'] = $nova_foto;
        }

        if (!update("usuarios", $id, $dados)) {
            throw new Exception("Não foi possível atualizar o usuário.");
        }

        // Só apaga a foto antiga depois que o banco foi atualizado
        if ($nova_foto !== null) {
            apagar_foto($atual['foto']);
        }

        // Mantém o nome exibido no menu atualizado
        if ($id === (int) ($_SESSION['id'] ?? 0)) {
            $_SESSION['nome'] = $dados['nome'];
            $_SESSION['user'] = $dados['user'];
            $_SESSION['nivel'] = $dados['nivel'];
        }

        header("Location: index.php");
        exit;

    } catch (Exception $e) {
        apagar_foto($nova_foto);

        // Mostra o que foi digitado, sem perder o id e a foto atuais
        $usuario = array_merge($atual, [
            'nome' => $_POST['usuario']['nome'] ?? $atual['nome'],
            'user' => $_POST['usuario']['user'] ?? $atual['user'],
        ]);

        flash("Aconteceu um erro: " . $e->getMessage(), "danger");
    }
}

/**
 * Visualização de um Usuário
 */
function view($id = null)
{
    global $usuario;

    $usuario = find("usuarios", (int) $id);
}

/**
 * Exclusão de um Usuário  e a foto dele)
 */
function delete($id = null)
{
    $id = (int) $id;
    $usuario = find("usuarios", $id);

    if (!$usuario) {
        flash("Usuário não encontrado.", "danger");
    } elseif ($id === (int) ($_SESSION['id'] ?? 0)) {
        flash("Você não pode excluir o seu próprio usuário.", "danger");
    } elseif (remove("usuarios", $id)) {
        apagar_foto($usuario['foto']);
    }

    header("Location: index.php");
    exit;
}