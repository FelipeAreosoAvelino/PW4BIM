
<?php

require_once('../config.php');
require_once(DBAPI);

$usuario = null;
$usuarios = null;

/**
 * Formatação de datas
 */
function formatadata($data, $formato)
{
    $dt = new DateTime(
        $data,
        new DateTimeZone("America/Sao_Paulo")
    );

    return $dt->format($formato);
}

function clear_messages() {
    $_SESSION['message'] = null;
    $_SESSION['type'] = null;
}

/**
 * Formatação de telefones
 */
function telefone($tel)
{
    $tel = preg_replace('/\D/', '', $tel);

    if (strlen($tel) == 11) {
        return "(" . substr($tel, 0, 2) . ") "
            . substr($tel, 2, 5) . "-"
            . substr($tel, 7, 4);
    }

    if (strlen($tel) == 10) {
        return "(" . substr($tel, 0, 2) . ") "
            . substr($tel, 2, 4) . "-"
            . substr($tel, 6, 4);
    }

    return $tel;
}

/**
 * Formatação de CEP
 */
function cep($cep)
{
    $cep = preg_replace('/\D/', '', $cep);

    if (strlen($cep) == 8) {
        return substr($cep, 0, 5) . "-"
            . substr($cep, 5, 3);
    }

    return $cep;
}

/**
 * Listagem de Usuários
 */
function index()
{
    global $usuarios;

    if (!empty($_POST['users'])) {
        $pesquisa = addslashes($_POST['users']);

        $usuarios = filter(
            "usuarios",
            "nome like '%" . $pesquisa . "%'"
        );
    } else {
        $usuarios = find_all("usuarios");
    }
}

/**
 * Upload de imagens
 *
 * Retorna o nome do arquivo armazenado.
 * Lança uma Exception caso alguma validação falhe.
 */
function upload(
    $pasta_destino,
    $arquivo_destino,
    $tipo_arquivo,
    $nome_temp,
    $tamanho_arquivo
) {
    // Verifica se o arquivo veio de um upload do PHP
    if (!is_uploaded_file($nome_temp)) {
        throw new Exception("O arquivo não foi enviado corretamente.");
    }

    // Verifica o tamanho do arquivo
    if ($tamanho_arquivo <= 0 || $tamanho_arquivo > 5000000) {
        throw new Exception("O arquivo deve ter no máximo 5 MB.");
    }

    // Extensões e MIME permitidos
    $formatos = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif'
    ];

    $tipo_arquivo = strtolower($tipo_arquivo);

    // Verifica se a extensão é permitida
    if (!isset($formatos[$tipo_arquivo])) {
        throw new Exception("Formato de arquivo não permitido.");
    }

    // Identifica o MIME real do arquivo
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($nome_temp);

    // Verifica se o MIME corresponde à extensão
    if ($mime !== $formatos[$tipo_arquivo]) {
        throw new Exception(
            "O arquivo não corresponde ao formato informado."
        );
    }

    // Verifica se o arquivo é uma imagem válida
    if (getimagesize($nome_temp) === false) {
        throw new Exception("O arquivo não é uma imagem válida.");
    }

    // Verifica se a pasta de destino existe
    if (!is_dir($pasta_destino)) {
        throw new Exception("A pasta de destino não existe.");
    }

    // Verifica se a pasta possui permissão de escrita
    if (!is_writable($pasta_destino)) {
        throw new Exception("A pasta de destino não possui permissão de escrita.");
    }

    // Gera um nome único para o arquivo
    $nomearquivo = bin2hex(random_bytes(16))
        . "." . $tipo_arquivo;

    // Monta o caminho definitivo
    $arquivo_destino = rtrim($pasta_destino, "/\\")
        . DIRECTORY_SEPARATOR . $nomearquivo;

    // Move o arquivo para a pasta definitiva
    if (!move_uploaded_file($nome_temp, $arquivo_destino)) {
        throw new Exception("Não foi possível armazenar o arquivo.");
    }

    // Retorna o nome gerado
    return $nomearquivo;
}

/**
 * Cadastro de Usuários
 */
function add()
{
    if (!empty($_POST['usuario'])) {

        $arquivo_enviado = null;

        try {
            $usuario = $_POST['usuario'];

            // Upload da foto
            if (
                isset($_FILES['foto']) &&
                $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE
            ) {

                if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception("Erro no envio da imagem.");
                }

                $pasta_destino = "fotos/";

                $nome_temp = $_FILES['foto']['tmp_name'];
                $tamanho_arquivo = $_FILES['foto']['size'];

                $tipo_arquivo = strtolower(
                    pathinfo(
                        $_FILES['foto']['name'],
                        PATHINFO_EXTENSION
                    )
                );

                // Chama a função upload
                $nomearquivo = upload(
                    $pasta_destino,
                    null,
                    $tipo_arquivo,
                    $nome_temp,
                    $tamanho_arquivo
                );

                $usuario['foto'] = $nomearquivo;

                // Guarda o caminho para possível limpeza em caso de erro
                $arquivo_enviado = $pasta_destino . $nomearquivo;
            }

            // Criptografando a senha
            if (!empty($usuario['password'])) {
                $usuario['password'] = criptografia(
                    $usuario['password']
                );
            }

            // Salva o usuário
            save("usuarios", $usuario);

            header("Location: index.php");
            exit;

        } catch (Exception $e) {

            // Remove o arquivo caso o cadastro tenha falhado
            if (
                $arquivo_enviado !== null &&
                file_exists($arquivo_enviado)
            ) {
                unlink($arquivo_enviado);
            }

            $_SESSION['message'] = "Aconteceu um erro: "
                . $e->getMessage();

            $_SESSION['type'] = "danger";
        }
    }
}

/**
 * Atualização / Edição de Usuários
 */
function edit()
{
    if (isset($_GET['id'])) {

        $id = $_GET['id'];

        if (isset($_POST['usuario'])) {

            $arquivo_enviado = null;

            try {
                $usuario = $_POST['usuario'];

                // Senha: criptografa somente se foi preenchida
                if (!empty($usuario['password'])) {
                    $usuario['password'] = criptografia(
                        $usuario['password']
                    );
                } else {
                    // Não altera a senha atual se o campo estiver vazio
                    unset($usuario['password']);
                }

                // Upload de uma nova foto
                if (
                    isset($_FILES['foto']) &&
                    $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE
                ) {

                    if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
                        throw new Exception("Erro no envio da imagem.");
                    }

                    $pasta_destino = "fotos/";

                    $nome_temp = $_FILES['foto']['tmp_name'];
                    $tamanho_arquivo = $_FILES['foto']['size'];

                    $tipo_arquivo = strtolower(
                        pathinfo(
                            $_FILES['foto']['name'],
                            PATHINFO_EXTENSION
                        )
                    );

                    // Chama a função upload
                    $nomearquivo = upload(
                        $pasta_destino,
                        null,
                        $tipo_arquivo,
                        $nome_temp,
                        $tamanho_arquivo
                    );

                    $usuario['foto'] = $nomearquivo;

                    // Guarda o caminho para possível limpeza em caso de erro
                    $arquivo_enviado = $pasta_destino . $nomearquivo;
                }

                // Atualiza o usuário
                update("usuarios", $id, $usuario);

                header("Location: index.php");
                exit;

            } catch (Exception $e) {

                // Remove a nova foto caso a atualização falhe
                if (
                    $arquivo_enviado !== null &&
                    file_exists($arquivo_enviado)
                ) {
                    unlink($arquivo_enviado);
                }

                $_SESSION['message'] = "Aconteceu um erro: "
                    . $e->getMessage();

                $_SESSION['type'] = "danger";
            }

        } else {

            // Carrega o usuário para edição
            global $usuario;
            $usuario = find("usuarios", $id);
        }

    } else {
        header("Location: index.php");
        exit;
    }
}

/**
 * Visualização de um Usuário
 */
function view($id = null)
{
    global $usuario;

    $usuario = find("usuarios", $id);
}

/**
 * Exclusão de um Usuário
 */
function delete($id = null)
{
    remove("usuarios", $id);

    header("Location: index.php");
    exit;
}

?>
