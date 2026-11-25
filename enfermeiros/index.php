<?php
include("function.php");
index();
include HEADER_TEMPLATE;
?>

<header>
    <div class="row">
        <div class="col-sm-6">
            <h1>Enfermeiros</h1>
        </div>
        <div class="col-sm-6 text-end h2">
            <a class="btn btn-crud-primary" href="add.php"><i class="fa-solid fa-user-plus"></i> Novo Enfermeiro</a>
            <a class="btn btn-crud-secondary" href="index.php"><i class="fa-solid fa-refresh"></i> Atualizar</a>
        </div>
    </div>
</header>

<?php if (!empty($_SESSION['message'])): ?>
    <div class="alert alert-<?php echo $_SESSION['type']; ?> alert-dismissible" role="alert">
        <?php echo $_SESSION['message']; ?>
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
            <!-- Os dois pontos ":" indica se vai mostrar o que vira depois-->
            <?php if ($enfermeiros): ?>
                <?php foreach ($enfermeiros as $enfermeiro): ?>
                    <tr>
                        <td><?php echo $enfermeiro['id']; ?></td>
                        <td><?php echo $enfermeiro['nome']; ?></td>
                        <td><?php echo $enfermeiro['coren']; ?></td>
                        <td><?php echo telefone($enfermeiro['phone']); ?></td>
                        <td>
                            <?php
                            $dt = new DateTime($enfermeiro['modified'], new DateTimeZone("America/Sao_Paulo"));
                            echo $dt->format("d/m/Y - H:i:s");
                            ?>
                        </td>
                        <td class="actions text-end">
                            <a href="view.php?id=<?php echo $enfermeiro['id']; ?>" class="btn btn-sm btn-crud-view"><i
                                    class="fa fa-eye"></i> Visualizar</a>
                            <a href="edit.php?id=<?php echo $enfermeiro['id']; ?>" class="btn btn-sm btn-crud-edit"><i
                                    class="fa fa-pencil"></i> Editar</a>
                            <a href="#" class="btn btn-sm btn-crud-delete" data-bs-toggle="modal"
                                data-bs-target="#delete-modal" data-id="<?= $enfermeiro['id']; ?>">
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

<?php include "modal.php"; ?>
<?php include FOOTER_TEMPLATE; ?>