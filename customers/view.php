<?php
require_once('function.php');

view($_GET['id'] ?? 0);

if (!$customer) {
    flash("Cliente não encontrado.", "danger");
    header("Location: index.php");
    exit;
}

include(HEADER_TEMPLATE);
?>

<h1 style="text-align: center;">Cliente <?php echo e($customer['nome']); ?></h1>

<div class="form-scroll">
    <div class="form-card">
        <!-- somente visualização -->
        <div class="form-group">
            <label for="name">Nome completo</label>
            <input type="text" class="form-control" id="name"
                value="<?php echo e($customer['nome']); ?>" disabled>
        </div>

        <div class="form-group">
            <label for="cep">CEP</label>
            <input type="text" class="form-control" id="cep"
                value="<?php echo e(cep($customer['cep'])); ?>" disabled>
        </div>

        <div class="form-group">
            <label for="phone">Telefone</label>
            <input type="text" class="form-control" id="phone"
                value="<?php echo e(telefone($customer['phone'])); ?>" disabled>
        </div>

        <div class="form-group">
            <label for="birthdate">Data de Nascimento</label>
            <input type="text" class="form-control" id="birthdate"
                value="<?php echo e(formatData($customer['birthdate'], 'd/m/Y')); ?>" disabled>
        </div>

        <div class="form-group">
            <label>Foto Atual</label>
            <div class="photo-preview" id="foto-preview">
                <?php if (!empty($customer['foto'])): ?>
                    <img src="<?php echo e(IMG_URL . $customer['foto']); ?>" alt="Foto atual">
                <?php else: ?>
                    <i class="fa-regular fa-image"></i>
                    <span>Sem Imagem</span>
                <?php endif; ?>
            </div>
        </div>

        <div id="actions">
            <a href="index.php" class="btn btn-crud-secondary">
                <i class="fa-solid fa-rotate-left"></i> Voltar
            </a>
            <a href="edit.php?id=<?php echo (int) $customer['id']; ?>" class="btn btn-crud-primary">
                <i class="fa-solid fa-pencil"></i> Editar
            </a>
        </div>
    </div>
</div>

<?php include FOOTER_TEMPLATE; ?>
