<?php
//esse é o view.php (usuários)
require_once("function.php");
require_admin();

view($_GET['id'] ?? 0);

if (!$usuario) {
    flash("Usuário não encontrado.", "danger");
    header("Location: index.php");
    exit;
}

include(HEADER_TEMPLATE);
?>

<h1 style="text-align: center;">Usuário <?php echo e($usuario['nome']); ?></h1>

<div class="form-scroll">
    <div class="form-card">
        <!-- somente visualização -->
        <div class="form-group">
            <label for="name">Nome completo</label>
            <input type="text" class="form-control" id="name"
                value="<?php echo e($usuario['nome']); ?>" disabled>
        </div>

        <div class="form-group">
            <label for="user">Usuário (Login)</label>
            <input type="text" class="form-control" id="user"
                value="<?php echo e($usuario['user']); ?>" disabled>
        </div>


        <div class="form-group">
            <label for="nivel">Nível de acesso</label>
            <input type="text" class="form-control" id="nivel"
                value="<?php echo $usuario['nivel'] === 'admin' ? 'Administrador' : 'Usuário'; ?>" disabled>
        </div>

        <div class="form-group">
            <label for="created">Cadastrado em</label>
            <input type="text" class="form-control" id="created"
                value="<?php echo e(formatdata($usuario['created'], 'd/m/Y - H:i')); ?>" disabled>
        </div>
        
        <div class="form-group">
            <label for="birthdate">Data de Nascimento</label>
            <input type="text" class="form-control" id="birthdate"
                value="<?php echo e(formatData($usuario['birthdate'], 'd/m/Y')); ?>" disabled>
        </div>

        <div class="form-group">
            <label>Foto Atual</label>
            <div class="photo-preview" id="foto-preview">
                <?php if (!empty($usuario['foto'])): ?>
                    <img src="<?php echo e(IMG_URL . $usuario['foto']); ?>" alt="Foto atual">
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
            <a href="edit.php?id=<?php echo (int) $usuario['id']; ?>" class="btn btn-crud-primary">
                <i class="fa-solid fa-pencil"></i> Editar
            </a>
        </div>
    </div>
</div>

<?php include(FOOTER_TEMPLATE); ?>