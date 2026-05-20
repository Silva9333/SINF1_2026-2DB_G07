<?php
// ============================================================
// agenda.php - AGENDA PESSOAL
// ============================================================
// SEM lógica: separação futuros/passados foi movida para
// separarAgendaPorData() na BusinessLogicLayer.php.
// ============================================================
require_once 'BusinessLogicLayer.php';
requireLogin();

$page_title   = 'Minha Agenda';
$current_page = 'agenda';

// BLL obtém e organiza a agenda — a página só imprime
$agenda  = getMinhaAgenda();
$partes  = separarAgendaPorData($agenda);  // BLL separa futuros/passados
$futuros = $partes['futuros'];
$passados= $partes['passados'];

include 'includes/header.php';
?>

<div class="container">
    <div class="section-header">
        <h2>📅 Minha Agenda Pessoal</h2>
        <span style="color:var(--cor-cinza);font-size:0.9rem;"><?= count($agenda) ?> evento(s) guardado(s)</span>
    </div>

    <?php if (empty($agenda)): ?>
        <div style="text-align:center;padding:4rem 2rem;">
            <div style="font-size:4rem;margin-bottom:1rem;">📅</div>
            <h3 style="color:var(--cor-cinza);">A sua agenda está vazia</h3>
            <p style="color:var(--cor-cinza);margin-bottom:2rem;">Adicione eventos que quer ver na Queima das Fitas 2026!</p>
            <a href="eventos.php" class="btn btn-primary btn-lg">🎪 Explorar Eventos</a>
        </div>
    <?php else: ?>

        <?php if (!empty($futuros)): ?>
        <h3 style="color:var(--cor-escura);margin-bottom:1rem;font-size:1.2rem;">🔜 Próximos Eventos</h3>
        <div class="grid" style="margin-bottom:2.5rem;">
            <?php foreach ($futuros as $ev): ?>
            <div class="card">
                <div class="card-header-evento <?= $ev['tipo'] ?>"></div>
                <div class="card-body">
                    <span class="badge badge-<?= $ev['tipo'] ?>"><?= tipoEventoLabel($ev['tipo']) ?></span>
                    <h3 class="card-title" style="margin-top:0.6rem;"><?= htmlspecialchars($ev['nome']) ?></h3>
                    <div class="card-meta">
                        <span>📅 <?= formatarDataHora($ev['data_hora']) ?></span>
                        <span>📍 <?= htmlspecialchars($ev['local']) ?></span>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="evento_detalhe.php?id=<?= $ev['id'] ?>" class="btn btn-primary btn-sm">Ver detalhes</a>
                    <a href="evento_detalhe.php?id=<?= $ev['id'] ?>" class="btn btn-danger btn-sm"
                       onclick="return confirm('Remover da agenda?');">🗑️ Remover</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($passados)): ?>
        <h3 style="color:var(--cor-cinza);margin-bottom:1rem;font-size:1.2rem;">✅ Eventos Passados</h3>
        <div style="display:flex;flex-direction:column;gap:0.8rem;">
            <?php foreach ($passados as $ev): ?>
            <div style="background:white;border-radius:10px;padding:1rem 1.5rem;box-shadow:var(--sombra);display:flex;align-items:center;gap:1rem;flex-wrap:wrap;opacity:0.7;">
                <span class="badge badge-<?= $ev['tipo'] ?>"><?= tipoEventoLabel($ev['tipo']) ?></span>
                <strong><?= htmlspecialchars($ev['nome']) ?></strong>
                <span style="color:var(--cor-cinza);font-size:0.85rem;">📅 <?= formatarDataHora($ev['data_hora']) ?></span>
                <a href="evento_detalhe.php?id=<?= $ev['id'] ?>" class="btn btn-outline btn-sm" style="margin-left:auto;">Avaliar ⭐</a>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
