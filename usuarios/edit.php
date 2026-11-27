<?php
require_once("function.php");
require_admin();
edit();
include HEADER_TEMPLATE;

$foto = !empty($usuario['foto']) ? $usuario['foto'] : "semimagem.jpg";
?>

<h1 style="text-align: center;" class="titulo-modal">Atualizar Usuário</h1>

<?php if (!empty($_SESSION['message'])): ?>
    <div class="alert alert-<?php echo e($_SESSION['type'] ?? 'info'); ?>" role="alert">
        <?php echo e($_SESSION['message']); ?>
    </div>
    <?php clear_messages(); ?>
<?php endif; ?>

<div class="form-scroll">
    <form
        action="edit.php?id=<?php echo (int) $usuario['id']; ?>"
        method="post"
        enctype="multipart/form-data"
        class="form-card">

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
                value="<?php echo e($usuario['nome']); ?>">
        </div>

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
                autocomplete="off"
                value="<?php echo e($usuario['user']); ?>">
        </div>

        <div class="form-group">
            <label for="password">Nova senha</label>
            <input
                type="password"
                class="form-control"
                id="password"
                name="usuario[password]"
                minlength="6"
                autocomplete="new-password"
                placeholder="Deixe em branco para manter a senha atual">
        </div>

        <div class="form-group">
            <label for="birthdate">Data de Nascimento</label>
            <input type="date" class="form-control" id="birthdate"
                name="usuario[birthdate]" required
                min="1900-01-01"
                max="<?php echo date('Y-m-d'); ?>"
                value="<?php
                        echo !empty($usuario['birthdate'])
                            ? date('Y-m-d', strtotime($usuario['birthdate']))
                            : '';
                        ?>">
        </div>

        <div class="form-group">
            <label for="nivel">Nível de acesso</label>
            <select
                class="form-control"
                id="nivel"
                name="usuario[nivel]"
                <?php echo (int) $usuario['id'] === (int) ($_SESSION['id'] ?? 0) ? 'disabled' : ''; ?>>

                <option value="user"
                    <?php echo $usuario['nivel'] === 'user' ? 'selected' : ''; ?>>
                    Usuário
                </option>

                <option value="admin"
                    <?php echo $usuario['nivel'] === 'admin' ? 'selected' : ''; ?>>
                    Administrador
                </option>

            </select>

            <?php if ((int) $usuario['id'] === (int) ($_SESSION['id'] ?? 0)): ?>
                <small class="text-muted">
                    Você não pode alterar seu próprio nível de acesso.
                </small>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="foto">Trocar Foto</label>

            <label for="foto" class="photo-upload">
                <i class="fa-regular fa-image"></i>
                <span class="photo-upload-label">Clique para selecionar</span>
                <span class="photo-upload-hint">JPG, PNG, WEBP ou GIF · máx. recomendado 5MB</span>
            </label>

            <input
                type="file"
                id="foto"
                name="foto"
                accept="image/png, image/jpeg, image/webp, image/gif"
                style="display: none;">
        </div>

        <div class="form-group">
            <label>Foto Atual</label>

            <div class="photo-preview" id="foto-preview">
                <img
                    src="<?php echo e(IMG_URL . $foto); ?>"
                    alt="Foto atual do usuário">
            </div>
        </div>

        <div id="actions">
            <button type="submit" class="btn btn-crud-primary">
                <i class="fa-solid fa-floppy-disk"></i> Salvar
            </button>

            <button type="reset" class="btn btn-crud-secondary">
                <i class="fa-solid fa-eraser"></i> Limpar
            </button>

            <a href="index.php" class="btn btn-crud-secondary">
                <i class="fa-solid fa-rotate-left"></i> Cancelar
            </a>
        </div>

    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        // ---- Pré-visualização da nova foto ----
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
                    '" alt="Pré-visualização da nova foto">';
            };

            reader.readAsDataURL(file);
        });

        // ---- Limpar formulário ----
        var form = input.closest('form');

        form.addEventListener('reset', function() {
            setTimeout(function() {
                preview.innerHTML = placeholder;
            }, 0);
        });

    });
</script>

<?php include FOOTER_TEMPLATE; ?>