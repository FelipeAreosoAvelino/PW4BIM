<?php
require_once("function.php"); // já exige login
add();
include HEADER_TEMPLATE;
?>

<h1 style="text-align: center;">Novo Enfermeiro</h1>

<?php if (!empty($_SESSION['message'])): ?>
    <div class="alert alert-<?php echo e($_SESSION['type'] ?? 'info'); ?>" role="alert">
        <?php echo e($_SESSION['message']); ?>
    </div>
    <?php clear_messages(); ?>
<?php endif; ?>

<div class="form-scroll">
    <form action="add.php" method="post" enctype="multipart/form-data" class="form-card">

        <div class="form-group">
            <label for="name">Nome completo</label>
            <input type="text" class="form-control" id="name"
                name="enfermeiro[nome]" required
                minlength="3" maxlength="100"
                value="<?php echo e($enfermeiro['nome'] ?? ''); ?>"
                placeholder="Ex: Ana Maria Santos">
        </div>

        <div class="form-group">
            <label for="cep">CEP</label>
            <input type="text" class="form-control" id="cep"
                name="enfermeiro[cep]" required
                inputmode="numeric" minlength="9" maxlength="9"
                pattern="\d{5}-\d{3}"
                title="Informe o CEP completo, com 8 números (ex: 12345-678)"
                value="<?php echo e($enfermeiro['cep'] ?? ''); ?>"
                placeholder="Ex: 12345-678">
        </div>

        <div class="form-group">
            <label for="coren">COREN</label>
            <input type="text" class="form-control" id="coren"
                name="enfermeiro[coren]" required
                inputmode="numeric" minlength="4" maxlength="7"
                pattern="[0-9]{4,7}"
                title="Informe de 4 a 7 números."
                value="<?php echo e($enfermeiro['coren'] ?? ''); ?>"
                placeholder="Ex: 123456">
        </div>

        <div class="form-group">
            <label for="phone">Telefone</label>
            <input type="text" class="form-control" id="phone"
                name="enfermeiro[phone]" required
                inputmode="tel" minlength="14" maxlength="15"
                pattern="\(\d{2}\) \d{4,5}-\d{4}"
                title="Informe o telefone com DDD, com 10 ou 11 números (ex: (11) 91234-5678)"
                value="<?php echo e($enfermeiro['phone'] ?? ''); ?>"
                placeholder="Ex: (11) 91234-5678">
        </div>

        <div class="form-group">
            <label for="birthdate">Data de Nascimento</label>
            <input type="date" class="form-control" id="birthdate"
                name="enfermeiro[birthdate]" required
                min="1900-01-01"
                max="<?php echo date('Y-m-d'); ?>"
                value="<?php echo e($enfermeiro['birthdate'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label for="foto">Foto</label>

            <label for="foto" class="photo-upload">
                <i class="fa-regular fa-image"></i>
                <span class="photo-upload-label">Clique para selecionar</span>
                <span class="photo-upload-hint">JPG, PNG, WEBP ou GIF · máx. recomendado 5MB</span>
            </label>

            <input type="file" id="foto" name="foto"
                accept="image/png, image/jpeg, image/webp, image/gif"
                style="display: none;">
        </div>

        <div class="form-group">
            <label>Pré-visualização</label>
            <div class="photo-preview" id="foto-preview">
                <i class="fa-regular fa-image"></i>
                <span>Sem Imagem</span>
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

    // ---- Máscaras ----
    function maskCep(v) {
        v = v.replace(/\D/g, '').slice(0, 8);
        return v.length > 5
            ? v.slice(0, 5) + '-' + v.slice(5)
            : v;
    }

    function maskPhone(v) {
        var d = v.replace(/\D/g, '').slice(0, 11);

        if (d.length > 10)
            return '(' + d.slice(0, 2) + ') ' +
                d.slice(2, 7) + '-' + d.slice(7);

        if (d.length > 6)
            return '(' + d.slice(0, 2) + ') ' +
                d.slice(2, 6) + '-' + d.slice(6);

        if (d.length > 2)
            return '(' + d.slice(0, 2) + ') ' + d.slice(2);

        return d;
    }

    var cep = document.getElementById('cep');
    var coren = document.getElementById('coren');
    var phone = document.getElementById('phone');
    var birthdate = document.getElementById('birthdate');

    // ---- Validação da data ----
    function validateDate() {
        birthdate.setCustomValidity('');

        if (!birthdate.value) return;

        var selectedDate = new Date(birthdate.value + 'T00:00:00');
        var today = new Date();

        today.setHours(0, 0, 0, 0);

        if (selectedDate.getFullYear() < 1900) {
            birthdate.setCustomValidity(
                'O ano deve ser 1900 ou posterior.'
            );
            return;
        }

        if (selectedDate > today) {
            birthdate.setCustomValidity(
                'A data de nascimento não pode ser futura.'
            );
        }
    }

    // ---- Reaplica máscaras se o formulário voltou com erro ----
    cep.value = maskCep(cep.value);
    phone.value = maskPhone(phone.value);

    // ---- CEP ----
    cep.addEventListener('input', function() {
        cep.value = maskCep(cep.value);
    });

    // ---- COREN ----
    coren.addEventListener('input', function() {
        coren.value = coren.value
            .replace(/[^0-9]/g, '')
            .slice(0, 7);
    });

    // ---- Telefone ----
    phone.addEventListener('input', function() {
        phone.value = maskPhone(phone.value);
    });

    // ---- Data de nascimento ----
    birthdate.addEventListener('change', validateDate);

    // ---- Pré-visualização da foto ----
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
                '" alt="Pré-visualização">';
        };

        reader.readAsDataURL(file);
    });

    // ---- Limpar formulário ----
    var form = input.closest('form');

    form.addEventListener('reset', function() {
        setTimeout(function() {
            preview.innerHTML = placeholder;
            birthdate.setCustomValidity('');
        }, 0);
    });

    // ---- Validar antes do envio ----
    form.addEventListener('submit', function() {
        validateDate();

        cep.value = cep.value.replace(/\D/g, '');
        phone.value = phone.value.replace(/\D/g, '');

        // O input date já envia AAAA-MM-DD.
        // Não é necessário converter a data.
    });

});
</script>

<?php include FOOTER_TEMPLATE; ?>