<?php
// ============================================================
// faculdades.php - LISTAGEM DE FACULDADES
// ============================================================
require_once 'BusinessLogicLayer.php';
$page_title = 'Faculdades';
$current_page = 'faculdades';
$faculdades = getFaculdades();
include 'includes/header.php';
?>

<div class="container">
    <div class="section-header">
        <h2>🏛️ Faculdades da Universidade do Porto</h2>
        <?php if (isAdmin()): ?>
            <a href="admin_faculdade_form.php" class="btn btn-success btn-sm">➕ Nova Faculdade</a>
        <?php endif; ?>
    </div>

    <?php if (empty($faculdades)): ?>
        <div class="alert alert-info">Nenhuma faculdade registada.</div>
    <?php else: ?>
    <div class="grid">
        <?php foreach ($faculdades as $f): ?>
        <div class="card" style="border-top:5px solid <?= $f['cor'] ?>;">
            <div class="card-body">
                <div style="display:flex; align-items:center; gap:1rem; margin-bottom:1rem;">
                    <!-- Círculo com a cor e sigla da faculdade -->
                    <div style="width:60px; height:60px; background:<?= $f['cor'] ?>; border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; font-weight:900; font-size:0.75rem; flex-shrink:0; text-align:center; line-height:1.2;">
                        <?= htmlspecialchars($f['sigla']) ?>
                    </div>
                    <div>
                        <h3 class="card-title" style="margin-bottom:0.2rem;"><?= htmlspecialchars($f['nome']) ?></h3>
                        <span style="color:<?= $f['cor'] ?>; font-weight:700; font-size:0.9rem;"><?= htmlspecialchars($f['sigla']) ?></span>
                    </div>
                </div>
                <p class="card-desc"><?= htmlspecialchars($f['descricao']) ?></p>
            </div>
            <div class="card-footer">
                <a href="barracas.php" class="btn btn-outline btn-sm">🏕️ Ver Barracas</a>
                <?php if (isAdmin()): ?>
                    <div class="btn-group">
                        <a href="admin_faculdade_form.php?id=<?= $f['id'] ?>" class="btn btn-warning btn-sm">✏️</a>
                        <a href="admin_delete.php?tipo=faculdade&id=<?= $f['id'] ?>" class="btn btn-danger btn-sm"
                           onclick="return confirm('Eliminar faculdade?');">🗑️</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
