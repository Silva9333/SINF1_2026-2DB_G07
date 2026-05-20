<?php
// ============================================================
// eventos.php - LISTAGEM DE TODOS OS EVENTOS
// ============================================================
// Mostra todos os eventos com filtro por tipo (tabs).
// Qualquer utilizador pode ver os eventos.
// ============================================================
require_once 'BusinessLogicLayer.php';

$page_title = 'Programa de Eventos';
$current_page = 'eventos';

// Filtro de tipo via URL: ?tipo=concerto, ?tipo=cerimonia, ?tipo=atividade
// Se não há filtro, mostra todos
$tipo_filtro = $_GET['tipo'] ?? 'todos';
$eventos = ($tipo_filtro === 'todos') ? getEventos() : getEventos($tipo_filtro);

include 'includes/header.php';
?>

<div class="container">
    <div class="section-header">
        <h2>🎪 Programa de Eventos 2026</h2>
        <?php if (isAdmin()): ?>
            <a href="admin_evento_form.php" class="btn btn-success btn-sm">➕ Novo Evento</a>
        <?php endif; ?>
    </div>

    <!-- ===== TABS DE FILTRO ===== -->
    <!-- Cada tab filtra os eventos por tipo, passando o parâmetro na URL -->
    <div class="tabs">
        <a href="eventos.php" class="tab-btn <?= $tipo_filtro === 'todos' ? 'active' : '' ?>">🎪 Todos</a>
        <a href="eventos.php?tipo=cerimonia" class="tab-btn <?= $tipo_filtro === 'cerimonia' ? 'active' : '' ?>">🎓 Cerimónias</a>
        <a href="eventos.php?tipo=concerto" class="tab-btn <?= $tipo_filtro === 'concerto' ? 'active' : '' ?>">🎵 Concertos</a>
        <a href="eventos.php?tipo=atividade" class="tab-btn <?= $tipo_filtro === 'atividade' ? 'active' : '' ?>">🎨 Atividades</a>
    </div>

    <!-- Contador de resultados -->
    <p style="color:var(--cor-cinza); font-size:0.9rem; margin-bottom:1rem;">
        <?= count($eventos) ?> evento(s) encontrado(s)
        <?= $tipo_filtro !== 'todos' ? 'em <strong>' . tipoEventoLabel($tipo_filtro) . '</strong>' : '' ?>
    </p>

    <?php if (empty($eventos)): ?>
        <div class="alert alert-info">Não foram encontrados eventos para este filtro.</div>
    <?php else: ?>

    <!-- ===== GRELHA DE EVENTOS ===== -->
    <div class="grid">
        <?php foreach ($eventos as $evento): ?>
        <div class="card" onclick="location.href='evento_detalhe.php?id=<?= $evento['id'] ?>'" style="cursor:pointer;">
            <!-- Barra colorida no topo consoante o tipo -->
            <div class="card-header-evento <?= $evento['tipo'] ?>"></div>
            <div class="card-body">
                <span class="badge badge-<?= $evento['tipo'] ?>"><?= tipoEventoLabel($evento['tipo']) ?></span>
                <h3 class="card-title" style="margin-top:0.6rem;"><?= htmlspecialchars($evento['nome']) ?></h3>
                <div class="card-meta">
                    <span><span class="icon">📅</span><?= formatarDataHora($evento['data_hora']) ?></span>
                    <span><span class="icon">📍</span><?= htmlspecialchars($evento['local']) ?></span>
                    <?php if ($evento['barraca_nome']): ?>
                        <span><span class="icon">⛺</span><?= htmlspecialchars($evento['barraca_nome']) ?></span>
                    <?php endif; ?>
                </div>
                <!-- Avaliação média -->
                <?php if ($evento['total_ratings'] > 0): ?>
                    <div style="margin-top:0.5rem;">
                        <?= renderStars($evento['media_rating'], $evento['total_ratings']) ?>
                    </div>
                <?php else: ?>
                    <div style="margin-top:0.5rem; color:var(--cor-cinza); font-size:0.85rem;">Sem avaliações ainda</div>
                <?php endif; ?>
            </div>
            <div class="card-footer">
                <a href="evento_detalhe.php?id=<?= $evento['id'] ?>" class="btn btn-primary btn-sm" onclick="event.stopPropagation();">Ver detalhes</a>
                <!-- Botões de edição/eliminação apenas para admins -->
                <?php if (isAdmin()): ?>
                    <div class="btn-group">
                        <a href="admin_evento_form.php?id=<?= $evento['id'] ?>" class="btn btn-warning btn-sm" onclick="event.stopPropagation();">✏️</a>
                        <a href="admin_delete.php?tipo=evento&id=<?= $evento['id'] ?>" class="btn btn-danger btn-sm"
                           onclick="event.stopPropagation(); return confirm('Eliminar este evento?');">🗑️</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
