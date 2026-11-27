<?php
require_once("function.php"); // já exige login
index();
include HEADER_TEMPLATE;
?>

<header>
    <div class="row">
        <div class="col-sm-6">
            <h1>Enfermeiros</h1>

            <form action="index.php" method="post" class="search-bar d-flex align-items-center gap-2">
                <input
                    type="text"
                    name="enfermeiros"
                    maxlength="80"
                    value="<?php echo e($_POST['enfermeiros'] ?? ''); ?>"
                    placeholder="Pesquisar enfermeiro pelo nome..."
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
                <i class="fa-solid fa-user-plus"></i> Novo Enfermeiro
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
                <th>COREN</th>
                <th>Telefone</th>
                <th>Atualizado em</th>
                <th>Opções</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($enfermeiros): ?>
                <?php foreach ($enfermeiros as $enf): ?>
                    <tr>
                        <td><?php echo (int) $enf['id']; ?></td>
                        <td><?php echo e($enf['nome']); ?></td>
                        <td><?php echo e($enf['coren']); ?></td>
                        <td><?php echo e(telefone($enf['phone'])); ?></td>
                        <td><?php echo e(formatData($enf['modified'], "d/m/Y - H:i:s")); ?></td>
                        <td class="actions text-end">
                            <a href="view.php?id=<?php echo (int) $enf['id']; ?>" class="btn btn-sm btn-crud-view"><i
                                    class="fa fa-eye"></i> Visualizar</a>
                            <a href="edit.php?id=<?php echo (int) $enf['id']; ?>" class="btn btn-sm btn-crud-edit"><i
                                    class="fa fa-pencil"></i> Editar</a>
                            <a href="#" class="btn btn-sm btn-crud-delete" data-bs-toggle="modal"
                                data-bs-target="#delete-modal" data-id="<?php echo (int) $enf['id']; ?>">
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
$modal_titulo = 'Excluir Enfermeiro';
$modal_texto  = 'Deseja realmente excluir este enfermeiro?';
include "modal.php";
?>
<?php include FOOTER_TEMPLATE; ?>