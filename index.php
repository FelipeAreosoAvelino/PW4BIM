<?php
include "config.php";
include DBAPI;
include HEADER_TEMPLATE;
$erro = null;
try {
    $db = open_database();
} catch (Exception $e) {
    $erro = $e->getMessage();
}
?>

<h1>Dashboard</h1>
<hr>

<?php if (!$erro): ?>

    <div class="action-grid">
        <a href="enfermeiros/add.php" class="action-card action-card--primary">
            <i class="fa-solid fa-user-plus"></i>
            <p>Novo Enfermeiro</p>
        </a>

        <a href="enfermeiros" class="action-card">
            <i class="fa-solid fa-user-group"></i>
            <p>Enfermeiros</p>
        </a>


        <a href="customers/add.php" class="action-card">
            <div class="row">
                <div class="col-xs-12 text-center">
                    <i class="fa fa-user-plus fa-5x"></i>
                </div>
                <div class="col-xs-12 text-center">
                    <p>Novo Cliente</p>
                </div>
            </div>
        </a>

        <a href="customers" class="action-card">
            <div class="row">
                <div class="col-xs-12 text-center">
                    <i class="fa fa-user-group fa-5x"></i>
                </div>
                <div class="col-xs-12 text-center">
                    <p>Clientes</p>
                </div>
            </div>
        </a>
        <?php if (isset($_SESSION['nivel']) && $_SESSION['nivel'] === 'adm'): ?>
            <a href="usuarios/add.php" class="action-card">
                <div class="row">
                    <div class="col-xs-12 text-center">
                        <i class="fa fa-user-gear fa-5x"></i>
                    </div>
                    <div class="col-xs-12 text-center">
                        <p>Novo Usuário</p>
                    </div>
                </div>
            </a>

            <a href="usuarios" class="action-card">
                <div class="row">
                    <div class="col-xs-12 text-center">
                        <i class="fa fa-users-gear fa-5x"></i>
                    </div>
                    <div class="col-xs-12 text-center">
                        <p>Usuários</p>
                    </div>
                </div>
            </a>
        <?php endif; ?>
    </div> 


<?php else: ?>
    <div class="alert alert-danger" role="alert">
        <p>
            <b>ERRO:</b> Não foi possível Conectar ao Banco de Dados!<br>
            <?= $erro; ?>
            <?php //echo $erro; 
            ?>
        </p>
    </div>
<?php endif; ?>

<?php include FOOTER_TEMPLATE; ?>