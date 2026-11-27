<?php
// Exige que o usuário tenha permissão de administrador
require_once("function.php");
require_admin();
index();

include HEADER_TEMPLATE;
?>

<header>
    <div class="row">
        <div class="col-sm-6">
            <h1>Usuários</h1>

            <form action="index.php" method="post" class="search-bar d-flex align-items-center gap-2">
                <input
                    type="text"
                    name="usuarios"
                    maxlength="80"
                    value="<?php echo e($_POST['usuarios'] ?? ''); ?>"
                    placeholder="Pesquisar usuário pelo nome..."
                    class="form-control">

                <button type="submit" class="btn btn-crud-primary">
                    <i class="fas fa-search"></i><span>Pesquisar</span>
                </button>

                <a href="index.php" class="btn btn-crud-secondary">
                    Limpar
                </a>
            </form>
        </div>

        <div class="col-sm-6 d-flex justify-content-end align-items-end gap-2">
            <a class="btn btn-crud-primary" href="add.php">
                <i class="fa-solid fa-user-plus"></i> Novo Usuário
            </a>

            <a class="btn btn-crud-secondary" href="index.php">
                <i class="fa-solid fa-refresh"></i> Atualizar
            </a>
        </div>
    </div>
</header>

<?php if (!empty($_SESSION['message'])): ?>
    <div class="alert alert-<?php echo e($_SESSION['type'] ?? 'info'); ?> alert-dismissible" role="alert">
        <?php echo e($_SESSION['message']); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <?php clear_messages(); ?>
<?php endif; ?>

<hr>

<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th width="30%">Nome</th>
                <th>Usuário</th>
                <th>Nível</th>
                <th>Foto</th>
                <th>Opções</th>
            </tr>
        </thead>

        <tbody>
            <?php if ($usuarios): ?>
                <?php foreach ($usuarios as $u): ?>
                    <?php
                    $foto = !empty($u['foto']) ? $u['foto'] : 'semimagem.jpg';
                    ?>

                    <tr>
                        <td><?php echo (int) $u['id']; ?></td>

                        <td><?php echo e($u['nome']); ?></td>

                        <td><?php echo e($u['user']); ?></td>

                        <td><?php echo e($u['nivel']); ?></td>

                        <td>
                            <img
                                src="<?php echo e(IMG_URL . $foto); ?>"
                                class="user-photo shadow"
                                alt="Foto de <?php echo e($u['nome']); ?>">
                        </td>

                        <td class="actions text-end">
                            <a
                                href="view.php?id=<?php echo (int) $u['id']; ?>"
                                class="btn btn-sm btn-crud-view">
                                <i class="fa fa-eye"></i> Visualizar
                            </a>

                            <a
                                href="edit.php?id=<?php echo (int) $u['id']; ?>"
                                class="btn btn-sm btn-crud-edit">
                                <i class="fa fa-pencil"></i> Editar
                            </a>

                            <a
                                href="#"
                                class="btn btn-sm btn-crud-delete"
                                data-bs-toggle="modal"
                                data-bs-target="#delete-modal"
                                data-usuario="<?php echo (int) $u['id']; ?>">
                                <i class="fa fa-trash"></i> Excluir
                            </a>
                        </td>
                    </tr>

                <?php endforeach; ?>

            <?php else: ?>
                <tr>
                    <td colspan="6">Nenhum registro encontrado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
$modal_titulo = 'Excluir Usuário';
$modal_texto  = 'Deseja realmente excluir este usuário?';

include "modal.php";
?>

<?php include FOOTER_TEMPLATE; ?>