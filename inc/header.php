<?php
// Evita erro caso a sessão já tenha sido iniciada em outro lugar antes do include
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>Gestão de enfermeiros</title>
    <meta name="description" content="Gestão de enfermeiros, cadastro de enfermeiros, cadastro de clientes, cadastro de usuários">
    <meta name="Keywords" content="HTML, CSS, PHP, bootstrap, enfermeiros">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="<?php echo BASEURL; ?>lf_icon.ico" type="image/x-icon">
    <link rel="stylesheet" href="<?php echo BASEURL; ?>css/bootstrap/bootstrap.min.css">

    <style>
        body {
            padding-top: 50px;
            padding-bottom: 20px;
        }

        main {
            margin-top: 1.5rem;
        }

        .navbar.fixed-top {
            background: var(--white);
        }

        /* NAVBAR */
        .topbar {
            background: var(--white);
            border-bottom: 1px solid var(--border);
            padding: 0 2rem;
            display: flex;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .06);
        }

        /* LOGO */
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: .6rem;
            font-weight: 700;
            font-size: 1.20rem;
            color: var(--teal);
        }

        .navbar-brand:hover,
        .navbar-brand:focus {
            color: var(--teal);
        }

        .navbar-brand-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: var(--teal);
            color: var(--white);
            font-size: 1rem;
            flex-shrink: 0;
        }

        /* ÁREA DOS MENUS */
        .topbar-end {
            display: flex;
            align-items: center;
            gap: 1.25rem;
        }

        .navbar-nav {
            width: 100%;
            gap: .5rem;
        }

        /* DROPDOWNS */
        .clientes-toggle {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: .5rem 0;
            color: var(--muted);
            font-weight: 600;
            transition: color .1s ease;
        }

        .clientes-toggle:hover,
        .clientes-toggle:focus,
        .clientes-toggle.show {
            color: var(--teal);
        }

        .clientes-toggle::after {
            margin-left: .2rem;
        }

        .dropdown-menu {
            border: 1px solid var(--border);
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, .08);
            padding: .4rem;
            margin-top: .5rem;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: .6rem;
            border-radius: 6px;
            padding: .55rem .75rem;
            color: var(--muted);
            font-size: .95rem;
        }

        .dropdown-item i {
            color: var(--teal);
            width: 1.1rem;
            text-align: center;
        }

        .dropdown-item:hover,
        .dropdown-item:focus {
            background-color: var(--teal-dim);
            color: var(--teal);
        }

        .dropdown-item:active {
            background-color: var(--teal);
            color: var(--white);
        }

        .dropdown-item:active i {
            color: var(--white);
        }

        /* LOGIN E LOGOUT */
        .nav-auth-item {
            margin-left: auto;
        }

        .nav-auth {
            color: var(--teal) !important;
            font-size: 1.3rem;
            font-weight: 650;
            transition: color .1s ease;
            margin-left: auto;
        }

        .nav-auth:hover,
        .nav-auth:focus {
            color: var(--teal-light) !important;
        }

        /* BOTÃO RESPONSIVO */
        .navbar-toggler {
            border-color: var(--border);
        }

        /* RESPONSIVIDADE */
        @media (max-width: 991.98px) {
            .topbar {
                padding: 0 1rem;
            }

            .navbar-brand {
                font-size: 1.05rem;
                gap: .4rem;
            }

            .navbar-brand-icon {
                width: 28px;
                height: 28px;
                font-size: .85rem;
            }

            .topbar-end {
                gap: .75rem;
            }

            .navbar-nav {
                padding-top: .5rem;
            }

            /* Login e logout ficam abaixo dos outros menus */
            .nav-auth-item {
                margin-left: 0;
            }
        }

        @media (max-width: 575.98px) {
            .topbar {
                padding: 0 .75rem;
            }

            .navbar-brand {
                font-size: .95rem;
            }

            .navbar-brand-icon {
                width: 26px;
                height: 26px;
                font-size: .75rem;
            }
        }
    </style>

    <link rel="stylesheet" href="<?php echo BASEURL; ?>css/style.css">
    <link rel="stylesheet" href="<?php echo BASEURL; ?>css/awesome/all.min.css">

</head>

<body>

    <nav class="navbar navbar-expand-lg fixed-top mb-3" data-bs-theme="light">

        <div class="container-fluid topbar">

            <!-- LOGO -->
            <a class="navbar-brand" href="<?php echo BASEURL; ?>">

                <span class="navbar-brand-icon">
                    <i class="fa-solid fa-user"></i>
                </span>

                Gestão de enfermeiros

            </a>

            <!-- BOTÃO RESPONSIVO -->
            <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent"
                aria-expanded="false"
                aria-label="Toggle navigation">

                <span class="navbar-toggler-icon"></span>

            </button>

            <!-- CONTEÚDO DA NAVBAR -->
            <div class="collapse navbar-collapse topbar-end"
                id="navbarSupportedContent">

                <ul class="navbar-nav mb-2 mb-lg-0">

                    <!-- CLIENTES -->
                    <li class="nav-item dropdown">

                        <a class="nav-link dropdown-toggle clientes-toggle"
                            href="#" role="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false">

                            <i class="fa-solid fa-users"></i>
                            Clientes

                        </a>

                        <ul class="dropdown-menu">

                            <li>
                                <a class="dropdown-item"
                                    href="<?php echo BASEURL; ?>customers/add.php">

                                    <i class="fa-solid fa-user-plus"></i>
                                    Adicionar Cliente

                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item"
                                    href="<?php echo BASEURL; ?>customers/">

                                    <i class="fa-solid fa-users"></i>
                                    Gerenciar Clientes

                                </a>
                            </li>

                        </ul>

                    </li>

                    <!-- PRODUTOS -->
                    <li class="nav-item dropdown">

                        <a class="nav-link dropdown-toggle clientes-toggle"
                            href="#" role="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false">

                            <i class="fa-solid fa-user"></i>
                            Enfermeiros

                        </a>

                        <ul class="dropdown-menu">

                            <li>
                                <a class="dropdown-item"
                                    href="<?php echo BASEURL; ?>enfermeiros/add.php">

                                    <i class="fa-solid fa-user-plus"></i>
                                    Adicionar Enfermeiro

                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item"
                                    href="<?php echo BASEURL; ?>enfermeiros/index.php">

                                    <i class="fa-solid fa-users"></i>
                                    Gerenciar Enfermeiros

                                </a>
                            </li>

                        </ul>

                    </li>

                    <!-- USUÁRIOS E AUTENTICAÇÃO -->
                    <?php if (isset($_SESSION['user'])) : ?>

                        <?php if ($_SESSION['user'] == "admin") : ?>

                            <!-- USUÁRIOS -->
                            <li class="nav-item dropdown">

                                <a class="nav-link dropdown-toggle clientes-toggle"
                                    href="#" role="button"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false">

                                    <i class="fa-solid fa-user-lock"></i>
                                    Usuários

                                </a>

                                <ul class="dropdown-menu">

                                    <li>
                                        <a class="dropdown-item"
                                            href="<?php echo BASEURL; ?>usuarios/add.php">

                                            <i class="fa-solid fa-user-tie"></i>
                                            Adicionar Usuário

                                        </a>
                                    </li>

                                    <li>
                                        <a class="dropdown-item"
                                            href="<?php echo BASEURL; ?>usuarios/">

                                            <i class="fa-solid fa-user-lock"></i>
                                            Gerenciar Usuários

                                        </a>
                                    </li>

                                </ul>

                            </li>

                        <?php endif; ?>

                        <!-- LOGOUT -->
                        <li class="nav-item nav-auth-item">

                            <a class="nav-link nav-auth"
                                href="<?php echo BASEURL; ?>inc/logout.php">

                                Bem vindo <?php echo $_SESSION['user']; ?>!

                                <i class="fa-solid fa-person-walking-arrow-right"></i>
                                Desconectar

                            </a>

                        </li>

                    <?php else : ?>

                        <!-- LOGIN -->
                        <li class="nav-item nav-auth-item">

                            <a class="nav-link nav-auth"
                                href="<?php echo BASEURL; ?>inc/login.php">

                                <i class="fa-solid fa-users"></i>
                                Login

                            </a>

                        </li>

                    <?php endif; ?>

                </ul>

            </div>

        </div>

    </nav>

    <main class="container">