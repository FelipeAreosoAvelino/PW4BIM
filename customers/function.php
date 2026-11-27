<?php
ob_start(); // output buffer aberto, para não dar erro de header location

require_once('../config.php');
require_once(DBAPI);

$customers = null;
$customer = null;

/**
 * Tamanho de texto em caracteres (funciona mesmo sem a extensão mbstring).
 */
function str_len($texto)
{
    return function_exists('mb_strlen') ? mb_strlen($texto, 'UTF-8') : (int) preg_match_all('/./us', $texto);
}

/**
 * Formatar as datas
 */
function formatData($data, $formato)
{
    $dt = new DateTime($data, new DateTimeZone("America/Sao_Paulo"));
    return $dt->format($formato);
}

/**
 * Formatar os telefones (10 ou 11 dígitos)
 */
function telefone($tel)
{
    $tel = preg_replace('/\D/', '', (string) $tel);

    if (strlen($tel) == 11) {
        return "(" . substr($tel, 0, 2) . ") " . substr($tel, 2, 5) . "-" . substr($tel, 7, 4);
    }

    if (strlen($tel) == 10) {
        return "(" . substr($tel, 0, 2) . ") " . substr($tel, 2, 4) . "-" . substr($tel, 6, 4);
    }

    return $tel;
}

/**
 * Formatar os CEPs
 */
function cep($cep)
{
    $cep = preg_replace('/\D/', '', (string) $cep);

    if (strlen($cep) == 8) {
        return substr($cep, 0, 5) . "-" . substr($cep, 5, 3);
    }

    return $cep;
}

/**
 * Pega do formulário só os campos permitidos e valida.
 * Lança Exception se algo estiver inválido.
 */
function dados_cliente($post)
{
    $c = [];
    $c['nome'] = trim($post['nome'] ?? '');
    $c['birthdate'] = trim($post['birthdate'] ?? '');
    $c['cep'] = preg_replace('/\D/', '', (string) ($post['cep'] ?? ''));
    $c['phone'] = preg_replace('/\D/', '', (string) ($post['phone'] ?? ''));

    if (str_len($c['nome']) < 3 || str_len($c['nome']) > 255) {
        throw new Exception("Informe o nome (mínimo 3 caracteres).");
    }

    $d = DateTime::createFromFormat('Y-m-d', $c['birthdate']);
    if (!$d || $d->format('Y-m-d') !== $c['birthdate'] || $d > new DateTime('today')) {
        throw new Exception("Data de nascimento inválida.");
    }

    if (strlen($c['cep']) !== 8) {
        throw new Exception("CEP inválido.");
    }

    if (!in_array(strlen($c['phone']), [10, 11], true)) {
        throw new Exception("Telefone inválido.");
    }

    return $c;
}

/**
 * Listagem de Clientes
 */
function index()
{
    global $customers;

    $pesquisa = trim($_POST['customers'] ?? '');

    if ($pesquisa !== '') {
        $customers = search("customers", "nome", $pesquisa);
    } else {
        $customers = find_all("customers");
    }
}

/**
 * Visualização de um Cliente
 */
function view($id = null)
{
    global $customer;
    $customer = find('customers', (int) $id);
}

/**
 * Cadastro de Clientes
 */
function add()
{
    global $customer;

    if (empty($_POST['customer'])) {
        return;
    }

    $nova_foto = null;

    try {
        $customer = dados_cliente($_POST['customer']);
        $nova_foto = uploadFoto($_FILES['foto'] ?? null);
        $customer['foto'] = $nova_foto ?? ''; // coluna foto é NOT NULL

        if (!save('customers', $customer)) {
            throw new Exception("Não foi possível salvar o cliente.");
        }

        header('Location: index.php');
        exit;

    } catch (Exception $e) {
        deletarFoto($nova_foto); // não deixa foto órfã
        flash($e->getMessage(), 'danger');
        $customer = $_POST['customer']; // mantém o que foi digitado
    }
}

/**
 * Atualização/Edição de Cliente
 */
function edit()
{
    global $customer;

    if (!isset($_GET['id']) || (int) $_GET['id'] <= 0) {
        header("Location: index.php");
        exit;
    }

    $id = (int) $_GET['id'];
    $atual = find("customers", $id);

    if (!$atual) {
        flash("Cliente não encontrado.", "danger");
        header("Location: index.php");
        exit;
    }

    $customer = $atual;

    if (empty($_POST['customer'])) {
        return;
    }

    $nova_foto = null;

    try {
        $dados = dados_cliente($_POST['customer']);
        $nova_foto = uploadFoto($_FILES['foto'] ?? null);

        if ($nova_foto) {
            $dados['foto'] = $nova_foto; // sem foto nova, a coluna não é alterada
        }

        if (!update("customers", $id, $dados)) {
            throw new Exception("Não foi possível atualizar o cliente.");
        }

        // Só apaga a foto antiga depois que o banco foi atualizado
        if ($nova_foto) {
            deletarFoto($atual['foto'] ?? null);
        }

        header("Location: index.php");
        exit;

    } catch (Exception $e) {
        deletarFoto($nova_foto);
        flash($e->getMessage(), 'danger');
        $customer = array_merge($atual, $_POST['customer']); // mantém o digitado
    }
}

/**
 * Exclusão de um Cliente
 */
function delete($id = null)
{
    $id = (int) $id;
    $cliente = find('customers', $id);

    // Remove primeiro o registro; a foto só é apagada se isso deu certo
    if ($cliente && remove('customers', $id)) {
        deletarFoto($cliente['foto'] ?? null);
    }

    header("Location: index.php");
    exit;
}

/**
 * Redimensiona a imagem para no máximo $larguraMax de largura.
 */
function redimensionarImagem($origem, $destino, $larguraMax = 300)
{
    $info = getimagesize($origem);
    if (!$info) {
        throw new Exception("Não foi possível ler a imagem.");
    }

    $largura = $info[0];
    $altura  = $info[1];
    $tipo    = $info[2];

    // Evita imagens gigantes que estourariam a memória ao redimensionar
    if ($largura * $altura > 25000000) {
        throw new Exception("A imagem tem dimensões muito grandes.");
    }

    // Se a imagem já for menor que o limite, só copia
    if ($largura <= $larguraMax) {
        if (!copy($origem, $destino)) {
            throw new Exception("Não foi possível salvar a imagem.");
        }
        return;
    }

    switch ($tipo) {
        case IMAGETYPE_JPEG: $src = imagecreatefromjpeg($origem); break;
        case IMAGETYPE_PNG:  $src = imagecreatefrompng($origem);  break;
        case IMAGETYPE_WEBP: $src = imagecreatefromwebp($origem); break;
        case IMAGETYPE_GIF:  $src = imagecreatefromgif($origem);  break;
        default:
            throw new Exception("Formato de imagem não suportado. Use JPG, PNG, WEBP ou GIF.");
    }

    if (!$src) {
        throw new Exception("Não foi possível processar a imagem.");
    }

    $novaLarg = $larguraMax;
    $novaAlt  = max(1, (int) ($altura * ($larguraMax / $largura)));
    $nova = imagecreatetruecolor($novaLarg, $novaAlt);

    // Preserva transparência (PNG, WEBP e GIF)
    if ($tipo !== IMAGETYPE_JPEG) {
        imagealphablending($nova, false);
        imagesavealpha($nova, true);
        imagefill($nova, 0, 0, imagecolorallocatealpha($nova, 0, 0, 0, 127));
    }

    imagecopyresampled($nova, $src, 0, 0, 0, 0, $novaLarg, $novaAlt, $largura, $altura);

    switch ($tipo) {
        case IMAGETYPE_JPEG: $ok = imagejpeg($nova, $destino, 85); break;
        case IMAGETYPE_PNG:  $ok = imagepng($nova, $destino, 6);   break;
        case IMAGETYPE_WEBP: $ok = imagewebp($nova, $destino, 85); break;
        case IMAGETYPE_GIF:  $ok = imagegif($nova, $destino);      break;
    }

    if (empty($ok)) {
        throw new Exception("Não foi possível salvar a imagem.");
    }
}

/**
 * Faz o upload de uma foto: valida, redimensiona e salva em IMG_DIR.
 * Retorna o nome do arquivo salvo, ou null se nenhum arquivo foi enviado.
 */
function uploadFoto($arquivo)
{
    // Nenhum arquivo enviado (ex: usuário não trocou a foto na edição)
    if (empty($arquivo) || ($arquivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($arquivo['error'] === UPLOAD_ERR_INI_SIZE || $arquivo['error'] === UPLOAD_ERR_FORM_SIZE) {
        throw new Exception("A imagem deve ter no máximo 5MB.");
    }

    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Erro ao enviar a imagem.");
    }

    if (!is_uploaded_file($arquivo['tmp_name'])) {
        throw new Exception("Upload inválido.");
    }

    $extensoesPermitidas = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];

    $tipo = mime_content_type($arquivo['tmp_name']);
    if (!isset($extensoesPermitidas[$tipo])) {
        throw new Exception("Formato de imagem não permitido. Use JPG, PNG, WEBP ou GIF.");
    }

    if ($arquivo['size'] > 5 * 1024 * 1024) {
        throw new Exception("A imagem deve ter no máximo 5MB.");
    }

    if (!is_dir(IMG_DIR) && !mkdir(IMG_DIR, 0755, true)) {
        throw new Exception("Não foi possível criar a pasta de imagens.");
    }

    $nomeUnico = 'foto_' . bin2hex(random_bytes(12)) . '.' . $extensoesPermitidas[$tipo];
    redimensionarImagem($arquivo['tmp_name'], IMG_DIR . $nomeUnico, 300);

    return $nomeUnico;
}

/**
 * Remove do disco a foto de um cliente, se existir.
 */
function deletarFoto($nomeArquivo)
{
    if (empty($nomeArquivo)) {
        return;
    }

    $caminho = IMG_DIR . basename($nomeArquivo);

    if (is_file($caminho)) {
        @unlink($caminho);
    }
}
