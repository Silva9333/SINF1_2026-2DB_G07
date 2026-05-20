<?php
// ============================================================
// includes/header.php - Cabeçalho reutilizável do site
// ============================================================
// Este ficheiro é incluído no topo de TODAS as páginas.
// Contém: DOCTYPE, CSS, navbar e abertura do container.
//
// Como usar: include 'includes/header.php';
// É preciso definir $page_title antes de incluir.
// ============================================================

// Definir título padrão se não foi definido na página
if (!isset($page_title)) $page_title = 'Queima das Fitas do Porto 2026';

// Definir página atual para destacar na navbar (active)
if (!isset($current_page)) $current_page = '';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> | Queima das Fitas 2026</title>
    <!-- Ficheiro CSS principal do projeto -->
    <link rel="stylesheet" href="<?= isset($css_path) ? $css_path : '' ?>style.css">
</head>
<body>

<!-- ===== NAVBAR DE NAVEGAÇÃO ===== -->
<!-- A navbar é fixa no topo (sticky) e mostra links diferentes consoante o papel do utilizador -->
<nav class="navbar">
    <div class="navbar-inner">
        <!-- Logo e nome do festival -->
        <a href="<?= isset($css_path) ? $css_path : '' ?>index.php" class="navbar-brand">
            <span class="logo-icon">🎓</span>
            <span class="brand-text">
                <span class="brand-title">Queima das Fitas</span>
                <span class="brand-sub">Porto · 2026</span>
            </span>
        </a>

        <!-- Links de navegação principal -->
        <ul class="navbar-nav">
            <!-- Link para eventos, destacado se estiver na página de eventos -->
            <li><a href="<?= isset($css_path) ? $css_path : '' ?>eventos.php" class="<?= $current_page === 'eventos' ? 'active' : '' ?>">🎪 Eventos</a></li>
            <li><a href="<?= isset($css_path) ? $css_path : '' ?>artistas.php" class="<?= $current_page === 'artistas' ? 'active' : '' ?>">🎤 Artistas</a></li>
            <li><a href="<?= isset($css_path) ? $css_path : '' ?>barracas.php" class="<?= $current_page === 'barracas' ? 'active' : '' ?>">⛺ Barracas</a></li>
            <li><a href="<?= isset($css_path) ? $css_path : '' ?>faculdades.php" class="<?= $current_page === 'faculdades' ? 'active' : '' ?>">🏛️ Faculdades</a></li>

            <?php if (isLoggedIn()): ?>
                <!-- Links exclusivos para utilizadores autenticados -->
                <li><a href="<?= isset($css_path) ? $css_path : '' ?>agenda.php" class="<?= $current_page === 'agenda' ? 'active' : '' ?>">📅 Agenda</a></li>

                <?php if (isAdmin()): ?>
                    <!-- Link de administração (apenas para admins) -->
                    <li><a href="<?= isset($css_path) ? $css_path : '' ?>admin.php" class="<?= $current_page === 'admin' ? 'active' : '' ?>">⚙️ Admin <span class="badge-admin">ADM</span></a></li>
                <?php endif; ?>

                <!-- Perfil e logout -->
                <li><a href="<?= isset($css_path) ? $css_path : '' ?>perfil.php" class="<?= $current_page === 'perfil' ? 'active' : '' ?>">👤 <?= htmlspecialchars(getCurrentUserName()) ?></a></li>
                <li><a href="<?= isset($css_path) ? $css_path : '' ?>logout.php">🚪 Sair</a></li>
            <?php else: ?>
                <!-- Links para utilizadores não autenticados -->
                <li><a href="<?= isset($css_path) ? $css_path : '' ?>login.php" class="btn-nav-login">🔑 Entrar</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
<!-- ===== FIM NAVBAR ===== -->
