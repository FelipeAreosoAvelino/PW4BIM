<?php
ob_start(); //output buffer aberto, para não dar erro de header location

if (session_status() === PHP_SESSION_NONE) {
	session_start(); // necessario para os alertas em $_SESSION['message'] funcionarem
}

include('../config.php');
include(DBAPI);



$enfermeiros = null;
$enfermeiro = null;

function clear_messages() 
{
    unset($_SESSION['message']);
    unset($_SESSION['type']);
}

/**
 *  Formatar as datas
 */
function formatData($data, $formato)
{
	$dt = new Datetime($data, new DateTimeZone("America/Sao_Paulo"));// "-0300"
	return $dt->format($formato);
}

/**
 *  Formatar os telefones
 */
function telefone($tel)
{ //15999990000
	return "(" . substr($tel, 0, 2) . ")" . substr($tel, 2, 5)
		. "-" . substr($tel, 7, 4);
}

/**
 *  Formatar os cep´s
 */
function cep($cep)
{
	return substr($cep, 0, 5) . "-" . substr($cep, 5, 3);
}

/**
 * Filtragem de Enfermeiros
 */
function filter($table, $where)
{
    $database = open_database();

    $found = null;

    try {
        $sql = "SELECT * FROM " . $table . " WHERE " . $where;

        $result = $database->query($sql);

        if ($result) {
            $found = $result->fetch_all(MYSQLI_ASSOC);
        }

    } catch (Exception $e) {

        $_SESSION['message'] = "Erro ao realizar a pesquisa: "
            . $e->getMessage();

        $_SESSION['type'] = "danger";
    }

    close_database($database);

    return $found;
}

/**
 *  Listagem de Clientes
 */
/**
 * Listagem e Pesquisa de Enfermeiros
 */
function index()
{
    global $enfermeiros;

    if (!empty($_POST['enfermeiros'])) {

        $pesquisa = addslashes($_POST['enfermeiros']);

        $enfermeiros = filter(
            "enfermeiros",
            "nome LIKE '%" . $pesquisa . "%'"
        );

    } else {

        $enfermeiros = find_all("enfermeiros");

    }
}
/**
 *  Visualização de um Cliente
 */
function view($id = null)
{
	global $enfermeiro;
	$enfermeiro = find('enfermeiros', $id);
}

/**
 *  Cadastro de Clientes
 */
function add()
{
	if (!empty($_POST['enfermeiro'])) {

		// $today = date_create('now', new DateTimeZone('-0300'));
		$today = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));

		$enfermeiro = $_POST['enfermeiro'];
		$enfermeiro['modified'] = $enfermeiro['created'] = $today->format("Y-m-d H:i:s");
		//modified e created são posições que serão adiconadas dentro do array $enfermeiro

		try {
			$enfermeiro['foto'] = uploadFoto($_FILES['foto'] ?? null);
		} catch (Exception $e) {
			$_SESSION['message'] = $e->getMessage();
			$_SESSION['type'] = 'danger';
			return; // nao salva o cliente se a foto enviada for invalida
		}

		save('enfermeiros', $enfermeiro); // 'enfermeiros' -> nome da tabela; $enfermeiro -> associative array
		header('location: index.php'); // output buffer
	}
}

/**
 *	Atualizacao/Edicao de Cliente
 */
function edit() {

  $now = date_create('now', new DateTimeZone('America/Sao_Paulo'));

  if (isset($_GET['id'])) {

    $id = $_GET['id'];

    if (isset($_POST['enfermeiro'])) {

      global $enfermeiro;
      $atual = find("enfermeiros", $id); // dados que ja estavam salvos, incluindo a foto antiga

      $enfermeiro = $_POST['enfermeiro'];
      $enfermeiro['modified'] = $now->format("Y-m-d H:i:s");

      try {
        $novaFoto = uploadFoto($_FILES['foto'] ?? null);
      } catch (Exception $e) {
        $_SESSION['message'] = $e->getMessage();
        $_SESSION['type'] = 'danger';
        $enfermeiro = $atual; // devolve os dados atuais pra tela nao ficar em branco
        return;
      }

      if ($novaFoto) {
        deletarFoto($atual['foto'] ?? null); // so apaga a antiga se uma nova foi enviada
        $enfermeiro['foto'] = $novaFoto;
      } else {
        $enfermeiro['foto'] = $atual['foto'] ?? null; // mantem a foto que ja existia
      }

      update("enfermeiros", $id, $enfermeiro);
      header("location: index.php");
    } else {

      global $enfermeiro;
      $enfermeiro = find("enfermeiros", $id);
    } 
  } else {
    header("location: index.php");
  }
}

/**
 *  Exclusão de um Cliente
 */
function delete($id = null) {

  global $enfermeiro;
  $enfermeiro = find('enfermeiros', $id); // busca os dados (inclusive a foto) antes de excluir

  if ($enfermeiro) {
    deletarFoto($enfermeiro['foto'] ?? null);
  }

  remove('enfermeiros', $id);

  header("location: index.php");
}
function redimensionarImagem($origem, $destino, $larguraMax = 300) {
    if (!extension_loaded('gd')) {
        throw new Exception("A extensão GD do PHP não está ativada no servidor. Ative 'extension=gd' no php.ini e reinicie o Apache.");
    }

    $info = getimagesize($origem);
    if (!$info) {
        throw new Exception("Não foi possível ler a imagem.");
    }

    $largura = $info[0];
    $altura  = $info[1];
    $tipo    = $info[2];

    // Se a imagem já for menor que o limite, só copia
    if ($largura <= $larguraMax) {
        copy($origem, $destino);
        return;
    }

    $ratio    = $larguraMax / $largura;
    $novaLarg = $larguraMax;
    $novaAlt  = (int)($altura * $ratio);

    $nova = imagecreatetruecolor($novaLarg, $novaAlt);

    if ($tipo === IMAGETYPE_JPEG) {
        $src = imagecreatefromjpeg($origem);
    } elseif ($tipo === IMAGETYPE_PNG) {
        // Preserva transparência no PNG
        imagealphablending($nova, false);
        imagesavealpha($nova, true);
        $src = imagecreatefrompng($origem);
    } elseif ($tipo === IMAGETYPE_WEBP) {
        $src = imagecreatefromwebp($origem);
    } elseif ($tipo === IMAGETYPE_GIF) {
        $src = imagecreatefromgif($origem);
    } else {
        throw new Exception("Formato de imagem não suportado. Use JPG, PNG, WEBP ou GIF.");
    }

    imagecopyresampled($nova, $src, 0, 0, 0, 0, $novaLarg, $novaAlt, $largura, $altura);

    if ($tipo === IMAGETYPE_JPEG) {
        imagejpeg($nova, $destino, 85);
    } elseif ($tipo === IMAGETYPE_PNG) {
        imagepng($nova, $destino, 6);
    } elseif ($tipo === IMAGETYPE_WEBP) {
        imagewebp($nova, $destino, 85);
    } elseif ($tipo === IMAGETYPE_GIF) {
        imagegif($nova, $destino);
    }

imagedestroy($src);
    imagedestroy($nova);
}

/**
 *  Faz o upload de uma foto: valida, redimensiona e salva na pasta img/.
 *  Retorna o nome do arquivo salvo, ou null se nenhum arquivo foi enviado.
 */
function uploadFoto($arquivo)
{
	// Nenhum arquivo enviado (ex: usuario nao trocou a foto na edicao)
	if (empty($arquivo) || empty($arquivo['name']) || $arquivo['error'] === UPLOAD_ERR_NO_FILE) {
		return null;
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

	$tamanhoMaximo = 5 * 1024 * 1024; // 5MB
	if ($arquivo['size'] > $tamanhoMaximo) {
		throw new Exception("A imagem deve ter no máximo 5MB.");
	}

	if (!is_dir(IMG_DIR)) {
		mkdir(IMG_DIR, 0755, true);
	}

	$nomeUnico = uniqid('foto_', true) . '.' . $extensoesPermitidas[$tipo];
	redimensionarImagem($arquivo['tmp_name'], IMG_DIR . $nomeUnico, 300);

	return $nomeUnico;
}

/**
 *  Remove do disco a foto de um cliente, se existir.
 */
function deletarFoto($nomeArquivo)
{
	if (empty($nomeArquivo)) {
		return;
	}

	$caminho = IMG_DIR . $nomeArquivo;

	if (is_file($caminho)) {
		unlink($caminho);
	}
}