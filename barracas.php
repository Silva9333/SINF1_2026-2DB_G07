<?php
// ============================================================
// barracas.php - LISTAGEM DE BARRACAS
// ============================================================
// SEM lógica: apenas obtém dados da BLL e imprime.
// A lógica "está aberta?" foi movida para barracaEstaAberta()
// na BusinessLogicLayer.php.
// ============================================================
require_once 'BusinessLogicLayer.php';

$page_title   = 'Barracas';
$current_page = 'barracas';

// BLL fornece os dados já prontos
$barracas = getBarracas();

include 'includes/header.php';
?>

<div class="container">
    <div class="section-header">
        <h2>⛺ Barracas das Faculdades</h2>
        <?php if (isAdmin()): ?>
            <a href="admin_barraca_form.php" class="btn btn-success btn-sm">➕ Nova Barraca</a>
        <?php endif; ?>
    </div>

    <p style="color:var(--cor-cinza);margin-bottom:1.5rem;font-size:0.95rem;">
        🕐 Hora atual: <strong><?= date('H:i') ?></strong>
        &nbsp;·&nbsp; Semana da Queima: 11–17 Maio 2026
    </p>

    <?php if (empty($barracas)): ?>
        <div class="alert alert-info">Nenhuma barraca registada.</div>
    <?php else: ?>
    <div class="grid">
        <?php foreach ($barracas as $barraca):
            // barracaEstaAberta() está na BLL — a página só chama e imprime
            $aberta = barracaEstaAberta($barraca['hora_abertura'], $barraca['hora_fecho']);
        ?>
        <div class="card" onclick="location.href='barraca_detalhe.php?id=<?= $barraca['id'] ?>'" style="cursor:pointer;">
            <div class="barraca-cor-barra" style="background:<?= htmlspecialchars($barraca['faculdade_cor']) ?>;"></div>
            <div class="card-body">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:0.5rem;">
                    <span style="background:<?= $barraca['faculdade_cor'] ?>22;color:<?= $barraca['faculdade_cor'] ?>;border:1px solid <?= $barraca['faculdade_cor'] ?>44;border-radius:20px;padding:2px 10px;font-size:0.8rem;font-weight:700;">
                        <?= htmlspecialchars($barraca['faculdade_sigla']) ?>
                    </span>
                    <span class="barraca-status <?= $aberta ? 'aberta' : 'fechada' ?>">
                        <?= $aberta ? '🟢 Aberta' : '🔴 Fechada' ?>
                    </span>
                </div>
                <h3 class="card-title"><?= htmlspecialchars($barraca['nome']) ?></h3>
                <div class="card-meta">
                    <span>🏛️ <?= htmlspecialchars($barraca['faculdade_nome']) ?></span>
                    <span>📍 <?= htmlspecialchars($barraca['localizacao']) ?></span>
                    <span>🕐 <?= substr($barraca['hora_abertura'],0,5) ?> – <?= substr($barraca['hora_fecho'],0,5) ?></span>
                </div>
                <p class="card-desc"><?= htmlspecialchars($barraca['descricao']) ?></p>
                <div style="margin-top:0.8rem;">
                    <?php if ($barraca['total_ratings'] > 0): ?>
                        <?= renderStars($barraca['media_rating'], $barraca['total_ratings']) ?>
                    <?php else: ?>
                        <span style="color:var(--cor-cinza);font-size:0.85rem;">Sem avaliações ainda</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-footer">
                <a href="barraca_detalhe.php?id=<?= $barraca['id'] ?>" class="btn btn-primary btn-sm" onclick="event.stopPropagation();">Ver Barraca</a>
                <?php if (isAdmin()): ?>
                <div class="btn-group">
                    <a href="admin_barraca_form.php?id=<?= $barraca['id'] ?>" class="btn btn-warning btn-sm" onclick="event.stopPropagation();">✏️</a>
                    <a href="admin_delete.php?tipo=barraca&id=<?= $barraca['id'] ?>" class="btn btn-danger btn-sm"
                       onclick="event.stopPropagation();return confirm('Eliminar barraca?');">🗑️</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
