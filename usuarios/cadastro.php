
<style>

/* ==========================================
   CADASTRO PUBLICO DE USUARIOS
========================================== */

.cadastro-card {
    max-width: 720px;
    width: 100%;
    margin: 1.5rem auto;
    padding: 2rem;
    border-radius: 16px;
}

/* Organização dos campos */
.cadastro-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    column-gap: 1.25rem;
    row-gap: .5rem;
}

/* Nome e usuário na mesma linha */
.cadastro-grid > .form-group {
    min-width: 0;
}

/* Senha ocupando toda a largura do formulário */
.cadastro-senha {
    grid-column: 1 / -1;
    width: 100%;
    justify-self: stretch;
}

/* Foto e pré-visualização ocupam toda a largura */
.cadastro-foto,
.cadastro-preview {
    grid-column: 1 / -1;
}

/* Área de envio da foto */
.cadastro-foto .photo-upload {
    padding: 1.2rem;
}

/* Pré-visualização */
.cadastro-preview .photo-preview {
    width: 120px;
    height: 120px;
}

/* Espaçamento dos botões */
.cadastro-card #actions {
    display: flex;
    justify-content: center;
    gap: .75rem;
    flex-wrap: wrap;
}

/* Responsividade */
@media (max-width: 767.98px) {

    .cadastro-scroll > .cadastro-card {
        min-width: 0;
    }

    .cadastro-card {
        padding: 1.5rem 1.25rem;
    }

    .cadastro-grid {
        grid-template-columns: 1fr;
        gap: .5rem;
    }

    .cadastro-senha,
    .cadastro-foto,
    .cadastro-preview {
        grid-column: 1;
        width: 100%;
    }

    .cadastro-card #actions {
        flex-direction: column;
    }

    .cadastro-card #actions .btn {
        width: 100%;
        justify-content: center;
    }
}

</style>
<?php
require_once("function.php");

// Cadastro público: não exige autenticação
registrar_usuario();

include HEADER_TEMPLATE;

$u = $usuario ?? [];
?>

<h1 style="text-align: center;">Criar minha conta</h1>

<p style="text-align: center;">
    Preencha os campos abaixo para realizar seu cadastro.
</p>

<?php if (!empty($_SESSION['message'])): ?>
    <div class="alert alert-<?php echo e($_SESSION['type'] ?? 'info'); ?>" role="alert">
        <?php echo e($_SESSION['message']); ?>
    </div>
    <?php clear_messages(); ?>
<?php endif; ?>

<div class="form-scroll cadastro-scroll">

    <form action="cadastro.php" method="post"
          enctype="multipart/form-data" class="form-card cadastro-card">

        <input type="hidden" name="csrf"
               value="<?php echo e(csrf_token()); ?>">

        <div class="cadastro-grid">

            <!-- Nome -->
            <div class="form-group">
                <label for="nome">Nome completo</label>
                <input
                    type="text"
                    class="form-control"
                    id="nome"
                    name="usuario[nome]"
                    required
                    minlength="3"
                    maxlength="255"
                    value="<?php echo e($u['nome'] ?? ''); ?>"
                    placeholder="Ex: João da Silva">
            </div>

            <!-- Usuário -->
            <div class="form-group">
                <label for="user">Usuário (Login)</label>
                <input
                    type="text"
                    class="form-control"
                    id="user"
                    name="usuario[user]"
                    required
                    minlength="3"
                    maxlength="50"
                    pattern="[A-Za-z0-9_.@\-]{3,50}"
                    autocomplete="username"
                    value="<?php echo e($u['user'] ?? ''); ?>"
                    placeholder="Escolha um usuário">
            </div>

            <!-- Senha -->
            <div class="form-group cadastro-senha">
                <label for="password">Senha</label>
                <input
                    type="password"
                    class="form-control"
                    id="password"
                    name="usuario[password]"
                    required
                    minlength="6"
                    autocomplete="new-password"
                    placeholder="Mínimo de 6 caracteres">
            </div>

            <!-- Foto -->
            <div class="form-group cadastro-foto">
                <label for="foto">Foto de perfil (opcional)</label>

                <label for="foto" class="photo-upload">
                    <i class="fa-regular fa-image"></i>
                    <span class="photo-upload-label">
                        Clique para selecionar
                    </span>
                    <span class="photo-upload-hint">
                        JPG, PNG, WEBP ou GIF · máximo 5MB
                    </span>
                </label>

                <input
                    type="file"
                    id="foto"
                    name="foto"
                    accept="image/png, image/jpeg, image/webp, image/gif"
                    style="display: none;">
            </div>

            <!-- Pré-visualização -->
            <div class="form-group cadastro-preview">
                <label>Pré-visualização</label>

                <div class="photo-preview" id="foto-preview">
                    <i class="fa-regular fa-image"></i>
                    <span>Sem imagem</span>
                </div>
            </div>

        </div>

        <!-- Ações -->
        <div id="actions">

            <button type="submit" class="btn btn-crud-primary">
                <i class="fa-solid fa-user-plus"></i> Criar conta
            </button>

            <button type="reset" class="btn btn-crud-secondary">
                <i class="fa-solid fa-eraser"></i> Limpar
            </button>

            <a href="<?php echo BASEURL; ?>inc/login.php"
               class="btn btn-crud-secondary">
                <i class="fa-solid fa-right-to-bracket"></i> Voltar ao login
            </a>

        </div>

    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // Pré-visualização da foto
    var input = document.getElementById('foto');
    var preview = document.getElementById('foto-preview');
    var placeholder = preview.innerHTML;

    input.addEventListener('change', function() {

        var file = input.files && input.files[0];

        if (!file) {
            preview.innerHTML = placeholder;
            return;
        }

        var reader = new FileReader();

        reader.onload = function(e) {
            preview.innerHTML =
                '<img src="' + e.target.result +
                '" alt="Pré-visualização da foto">';
        };

        reader.readAsDataURL(file);
    });

    // Limpar formulário
    var form = input.closest('form');

    form.addEventListener('reset', function() {
        setTimeout(function() {
            preview.innerHTML = placeholder;
        }, 0);
    });

});
</script>

<?php include FOOTER_TEMPLATE; ?>
