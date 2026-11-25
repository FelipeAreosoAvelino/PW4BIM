<?php
include("function.php");
index();
include HEADER_TEMPLATE;
?>

<header>
    <div class="row">
        <div class="col-sm-6">
            <h1>Clientes</h1>
        </div>
        <div class="col-sm-6 text-end h2">
            <a class="btn btn-crud-primary" href="add.php"><i class="fa-solid fa-user-plus"></i> Novo Cliente</a>
            <a class="btn btn-crud-secondary" href="index.php"><i class="fa-solid fa-refresh"></i> Atualizar</a>
        </div>
    </div>
</header>
<form action="index.php" method="post">

    <input
        type="text"
        name="customers"
        class="form-control"
        placeholder="Pesquisar cliente pelo nome...">

    <button type="submit" class="btn btn-primary">
        <i class="fa-solid fa-magnifying-glass"></i>
        Pesquisar
    </button>

    <a href="index.php" class="btn btn-secondary">
        <i class="fa-solid fa-eraser"></i>
        Limpar
    </a>

</form>
<?php if (!empty($_SESSION['message'])): ?>
    <div class="alert alert-<?php echo $_SESSION['type']; ?> alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                aria-hidden="true">&times;</span></button>
        <?php echo $_SESSION['message']; ?>
    </div>
    <?php //clear_messages(); 
    ?>
    <!-- clear_messages -> limpa as msg´s ($_SESSION['message'] e $_SESSION['type'])-->
<?php endif; ?>

<hr>

<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th width="30%">Nome</th>
                <th>CEP</th>
                <th>Telefone</th>
                <th>Atualizado em</th>
                <th>Opções</th>
            </tr>
        </thead>
        <tbody>
            <!-- Os dois pontos ":" indica se vai mostrar o que vira depois-->
            <?php if ($customers): ?>
                <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td><?php echo $customer['id']; ?></td>
                        <td><?php echo $customer['nome']; ?></td>
                        <td>
                            <?php echo substr($customer['cep'], 0, 5) . '-' . substr($customer['cep'], 5, 3); ?>
                        </td>
                        <td><?php echo telefone($customer['phone']); ?></td>
                        <td>
                            <?php
                            $dt = new DateTime($customer['modified'], new DateTimeZone("America/Sao_Paulo"));
                            echo $dt->format("d/m/Y - H:i:s");
                            ?>
                        </td>
                        <td class="actions text-end">
                            <a href="view.php?id=<?php echo $customer['id']; ?>" class="btn btn-sm btn-crud-view"><i
                                    class="fa fa-eye"></i> Visualizar</a>
                            <a href="edit.php?id=<?php echo $customer['id']; ?>" class="btn btn-sm btn-crud-edit"><i
                                    class="fa fa-pencil"></i> Editar</a>
                            <a href="#" class="btn btn-sm btn-crud-delete" data-bs-toggle="modal" data-bs-target="#delete-modal"
                                data-customer="<?= $customer['id']; ?>">
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