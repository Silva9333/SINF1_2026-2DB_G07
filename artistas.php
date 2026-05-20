<?php
// ============================================================
// artistas.php - LISTAGEM DE ARTISTAS
// ============================================================
require_once 'BusinessLogicLayer.php';
$page_title = 'Artistas';
$current_page = 'artistas';
$artistas = getArtistas();
include 'includes/header.php';
?>

<div class="container">
    <div class="section-header">
        <h2>🎤 Artistas da Queima 2026</h2>
        <?php if (isAdmin()): ?>
            <a href="admin_artista_form.php" class="btn btn-success btn-sm">➕ Novo Artista</a>
        <?php endif; ?>
    </div>

    <?php if (empty($artistas)): ?>
        <div class="alert alert-info">Nenhum artista registado ainda.</div>
    <?php else: ?>
    <div class="grid grid-3">
        <?php foreach ($artistas as $artista): ?>
        <div class="card card-artista" onclick="location.href='artista_detalhe.php?id=<?= $artista['id'] ?>'" style="cursor:pointer;">
            <!-- Avatar com inicial do nome -->
            <div class="artista-avatar"><?= mb_strtoupper(mb_substr($artista['nome'], 0, 1)) ?></div>
            <div class="card-body" style="text-align:center;">
                <h3 class="card-title"><?= htmlspecialchars($artista['nome']) ?></h3>
                <?php if ($artista['genero']): ?>
                    <span class="badge badge-concerto"><?= htmlspecialchars($artista['genero']) ?></span>
                <?php endif; ?>
                <div class="card-meta" style="justify-content:center; margin-top:0.8rem;">
                    <?php if ($artista['pais']): ?>
                        <span style="justify-content:center;">🌍 <?= htmlspecialchars($artista['pais']) ?></span>
                    <?php endif; ?>
                </div>
                <?php if ($artista['biografia']): ?>
                    <p class="card-desc" style="margin-top:0.8rem; text-align:left;"><?= htmlspecialchars($artista['biografia']) ?></p>
                <?php endif; ?>
            </div>
            <div class="card-footer" style="justify-content:center;">
                <a href="artista_detalhe.php?id=<?= $artista['id'] ?>" class="btn btn-primary btn-sm" onclick="event.stopPropagation();">Ver Perfil</a>
                <?php if (isAdmin()): ?>
                    <a href="admin_artista_form.php?id=<?= $artista['id'] ?>" class="btn btn-warning btn-sm" onclick="event.stopPropagation();">✏️</a>
                    <a href="admin_delete.php?tipo=artista&id=<?= $artista['id'] ?>" class="btn btn-danger btn-sm"
                       onclick="event.stopPropagation(); return confirm('Eliminar artista?');">🗑️</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
