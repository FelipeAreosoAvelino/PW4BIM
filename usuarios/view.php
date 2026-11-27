<?php
//esse é o view.php
require_once("function.php");
require_admin();

view($_GET['id'] ?? 0);

if (!$usuario) {
    flash("Usuário não encontrado.", "danger");
    header("Location: index.php");
    exit;
}

include(HEADER_TEMPLATE);
$foto = !empty($usuario['foto']) ? $usuario['foto'] : "semimagem.jpg";
?>

<header>
    <h2>Usuário <?php echo e($usuario['id']); ?></h2>
</header>
<hr>

<dl class="dl-horizontal">
    <dt>Nome:</dt>
    <dd><?php echo e($usuario['nome']); ?></dd>

    <dt>Login:</dt>
    <dd><?php echo e($usuario['user']); ?></dd>

    <dt>Nível:</dt>
    <dd><?php echo e($usuario['nivel']); ?></dd>

    <dt>Foto:</dt>
    <dd>
        <img src="fotos/<?php echo e($foto); ?>" class="shadow p-1 mb-1 bg-body rounded" width="300" alt="Foto de <?php echo e($usuario['nome']); ?>">
    </dd>
</dl>

<div id="actions" class="row">
    <div class="col-md-12">
        <a href="edit.php?id=<?php echo (int) $usuario['id']; ?>" class="btn btn-secondary"><i class="fa-solid fa-pen-to-square"></i> Editar</a>
        <a href="index.php" class="btn btn-light"><i class="fa-solid fa-rotate-left"></i> Voltar</a>
    </div>
</div>

<?php include(FOOTER_TEMPLATE); ?>