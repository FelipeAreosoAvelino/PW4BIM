<style>

/* ==========================================
   LOGIN
========================================== */

.login-page {
    width: 100%;
    max-width: 430px;
    margin: 2rem auto;
    padding: 0 .5rem;
}

/* Cartão principal */
.login-card {
    background: var(--white);
    border: 2px solid var(--teal-light);
    border-radius: 16px;
    padding: 1.75rem 2rem;
    box-shadow: 0 5px 18px rgba(15, 23, 42, .05);
}

/* Cabeçalho */
.login-heading {
    text-align: center;
    margin-bottom: 1.5rem;
}

.login-heading-icon {
    width: 52px;
    height: 52px;
    margin: 0 auto .75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--teal-dim);
    color: var(--teal);
    border-radius: 14px;
    font-size: 1.35rem;
}

.login-heading h1 {
    font-size: 1.5rem;
    margin-bottom: .35rem;
}

.login-heading p {
    color: var(--muted);
    font-size: .9rem;
    margin: 0;
}

/* Campos */
.login-field {
    margin-bottom: 1.1rem;
}

.login-field > label {
    display: block;
    font-size: .82rem;
    font-weight: 700;
    color: var(--slate);
    margin-bottom: .4rem;
}

/* Grupo de input e ícone */
.login-input-group {
    flex-wrap: nowrap;
}

.login-input-group .input-group-text {
    background: var(--teal-dim);
    border: 1px solid var(--border);
    border-right: none;
    color: var(--teal);
    border-radius: 9px 0 0 9px;
    padding: 0 .9rem;
}

.login-input-group .form-control {
    min-width: 0;
    height: 43px;
    border-radius: 0 9px 9px 0;
    font-size: .92rem;
    padding: .6rem .8rem;
}

.login-input-group .form-control:focus {
    border-color: var(--teal-light);
    box-shadow: none;
}

.login-input-group:focus-within .input-group-text {
    border-color: var(--teal-light);
    background: var(--teal);
    color: var(--white);
}

/* Botões */
.login-actions {
    display: flex;
    flex-direction: column;
    gap: .65rem;
    margin-top: 1.5rem;
}

.login-actions .btn {
    width: 100%;
    justify-content: center;
    padding: .65rem;
    border-radius: 9px;
    font-size: .9rem;
}

/* Rodapé */
.login-footer {
    text-align: center;
    margin-top: 1.25rem;
    padding-top: .9rem;
    border-top: 1px solid var(--border);
}

.login-footer a {
    color: var(--muted);
    font-size: .85rem;
    text-decoration: none;
    transition: color .15s ease;
}

.login-footer a:hover {
    color: var(--teal);
}

/* Alerta */
.login-page > .alert {
    margin-bottom: 1rem;
    font-size: .9rem;
}

/* Responsividade */
@media (max-width: 575.98px) {

    .login-page {
        margin: 1rem auto;
        padding: 0;
    }

    .login-card {
        padding: 1.5rem 1.25rem;
    }

    .login-heading h1 {
        font-size: 1.35rem;
    }
}

</style>
<?php
// Esse é o login.php
require_once("../config.php");
require_once(DBAPI);

// Quem já está logado não precisa ver o formulário
if (is_logged()) {
    header("Location: " . BASEURL . "index.php");
    exit;
}

include(HEADER_TEMPLATE);
?>

<div class="login-page">

    <?php if (!empty($_SESSION['message'])) : ?>
        <div class="alert alert-<?php echo e($_SESSION['type'] ?? 'info'); ?> alert-dismissible"
             role="alert">

            <?php echo e($_SESSION['message']); ?>

            <button type="button" class="btn-close"
                data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        <?php clear_messages(); ?>
    <?php endif; ?>

    <div class="login-card">

        <!-- Cabeçalho -->
        <div class="login-heading">
            <div class="login-heading-icon">
                <i class="fa-solid fa-user"></i>
            </div>

            <h1>Acessar conta</h1>

            <p>Entre com seus dados para continuar.</p>
        </div>

        <!-- Formulário -->
        <form action="valida.php" method="post">

            <!-- Usuário -->
            <div class="login-field">
                <label for="log">Usuário</label>

                <div class="input-group login-input-group">
                    <span class="input-group-text">
                        <i class="fa-solid fa-user"></i>
                    </span>

                    <input
                        type="text"
                        class="form-control"
                        id="log"
                        placeholder="Digite seu usuário"
                        name="login"
                        required
                        maxlength="255"
                        autocomplete="username"
                        autofocus>
                </div>
            </div>

            <!-- Senha -->
            <div class="login-field">
                <label for="pass">Senha</label>

                <div class="input-group login-input-group">
                    <span class="input-group-text">
                        <i class="fa-solid fa-lock"></i>
                    </span>

                    <input
                        type="password"
                        class="form-control"
                        id="pass"
                        placeholder="Digite sua senha"
                        name="senha"
                        required
                        autocomplete="current-password">
                </div>
            </div>

            <!-- Botões -->
            <div class="login-actions">

                <button type="submit" class="btn btn-crud-primary">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    Conectar
                </button>

                <a href="<?php echo BASEURL; ?>usuarios/cadastro.php"
                   class="btn btn-crud-secondary">
                    <i class="fa-solid fa-user-plus"></i>
                    Criar uma conta
                </a>

            </div>

            <div class="login-footer">
                <a href="<?php echo BASEURL; ?>">
                    <i class="fa-solid fa-arrow-left"></i>
                    Voltar ao início
                </a>
            </div>

        </form>

    </div>

</div>

<?php include(FOOTER_TEMPLATE); ?>
