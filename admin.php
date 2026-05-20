<?php
// ============================================================
// admin.php - PAINEL DE ADMINISTRAÇÃO
// ============================================================
// SEM lógica: estatísticas obtidas via getEstatisticasDashboard()
// da BLL. A página só imprime o que recebe.
// ============================================================
require_once 'BusinessLogicLayer.php';
requireAdmin();

$page_title   = 'Administração';
$current_page = 'admin';

// BLL prepara tudo — a página não processa nada
$stats = getEstatisticasDashboard();

include 'includes/header.php';
?>

<div class="container">

    <div class="admin-panel">
        <div>
            <h2>⚙️ Painel de Administração</h2>
            <p style="opacity:0.8;font-size:0.9rem;margin-top:0.3rem;">Gerir conteúdos da Queima das Fitas 2026</p>
        </div>
        <div style="color:rgba(255,255,255,0.7);font-size:0.85rem;">
            Sessão: <strong style="color:var(--cor-secundaria);"><?= htmlspecialchars(getCurrentUserName()) ?></strong>
        </div>
    </div>

    <!-- Estatísticas -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">🎪</div>
            <div class="stat-number"><?= $stats['total_eventos'] ?? 0 ?></div>
            <div class="stat-label">Eventos</div>
        </div>
        <div class="stat-card" style="border-top-color:#0f3460;">
            <div class="stat-icon">🎤</div>
            <div class="stat-number"><?= $stats['total_artistas'] ?? 0 ?></div>
            <div class="stat-label">Artistas</div>
        </div>
        <div class="stat-card" style="border-top-color:#006400;">
            <div class="stat-icon">⛺</div>
            <div class="stat-number"><?= $stats['total_barracas'] ?? 0 ?></div>
            <div class="stat-label">Barracas</div>
        </div>
        <div class="stat-card" style="border-top-color:#FF8C00;">
            <div class="stat-icon">👤</div>
            <div class="stat-number"><?= $stats['total_users'] ?? 0 ?></div>
            <div class="stat-label">Utilizadores</div>
        </div>
        <div class="stat-card" style="border-top-color:#6A0DAD;">
            <div class="stat-icon">⭐</div>
            <div class="stat-number"><?= $stats['total_ratings'] ?? 0 ?></div>
            <div class="stat-label">Avaliações</div>
        </div>
        <?php if (!empty($stats['evento_popular'])): ?>
        <div class="stat-card" style="border-top-color:#C71585;">
            <div class="stat-icon">🔥</div>
            <div class="stat-number" style="font-size:1rem;margin:0.3rem 0;"><?= htmlspecialchars(mb_substr($stats['evento_popular']['nome'],0,28)) ?></div>
            <div class="stat-label">Evento mais popular</div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Secções de gestão -->
    <div class="grid" style="grid-template-columns:repeat(auto-fill,minmax(260px,1fr));">
        <div class="card" style="border-top:4px solid var(--cor-primaria);">
            <div class="card-body" style="text-align:center;padding:2rem 1rem;">
                <div style="font-size:3rem;margin-bottom:1rem;">🎪</div>
                <h3 style="color:var(--cor-escura);margin-bottom:0.5rem;">Eventos</h3>
                <p style="color:var(--cor-cinza);font-size:0.85rem;margin-bottom:1.5rem;">Cerimónias, concertos e atividades</p>
            </div>
            <div class="card-footer" style="justify-content:center;gap:0.5rem;">
                <a href="eventos.php" class="btn btn-outline btn-sm">📋 Ver Todos</a>
                <a href="admin_evento_form.php" class="btn btn-success btn-sm">➕ Novo</a>
            </div>
        </div>
        <div class="card" style="border-top:4px solid #0f3460;">
            <div class="card-body" style="text-align:center;padding:2rem 1rem;">
                <div style="font-size:3rem;margin-bottom:1rem;">🎤</div>
                <h3 style="color:var(--cor-escura);margin-bottom:0.5rem;">Artistas</h3>
                <p style="color:var(--cor-cinza);font-size:0.85rem;margin-bottom:1.5rem;">Artistas confirmados e biografias</p>
            </div>
            <div class="card-footer" style="justify-content:center;gap:0.5rem;">
                <a href="artistas.php" class="btn btn-outline btn-sm">📋 Ver Todos</a>
                <a href="admin_artista_form.php" class="btn btn-success btn-sm">➕ Novo</a>
            </div>
        </div>
        <div class="card" style="border-top:4px solid #006400;">
            <div class="card-body" style="text-align:center;padding:2rem 1rem;">
                <div style="font-size:3rem;margin-bottom:1rem;">⛺</div>
                <h3 style="color:var(--cor-escura);margin-bottom:0.5rem;">Barracas</h3>
                <p style="color:var(--cor-cinza);font-size:0.85rem;margin-bottom:1.5rem;">Barracas das faculdades e horários</p>
            </div>
            <div class="card-footer" style="justify-content:center;gap:0.5rem;">
                <a href="barracas.php" class="btn btn-outline btn-sm">📋 Ver Todas</a>
                <a href="admin_barraca_form.php" class="btn btn-success btn-sm">➕ Nova</a>
            </div>
        </div>
        <div class="card" style="border-top:4px solid #FF8C00;">
            <div class="card-body" style="text-align:center;padding:2rem 1rem;">
                <div style="font-size:3rem;margin-bottom:1rem;">🏛️</div>
                <h3 style="color:var(--cor-escura);margin-bottom:0.5rem;">Faculdades</h3>
                <p style="color:var(--cor-cinza);font-size:0.85rem;margin-bottom:1.5rem;">Faculdades participantes e cores</p>
            </div>
            <div class="card-footer" style="justify-content:center;gap:0.5rem;">
                <a href="faculdades.php" class="btn btn-outline btn-sm">📋 Ver Todas</a>
                <a href="admin_faculdade_form.php" class="btn btn-success btn-sm">➕ Nova</a>
            </div>
        </div>
    </div>

    <?php if (!empty($stats['barraca_top'])): ?>
    <div style="background:linear-gradient(135deg,#fff3cd,#ffeaa7);border:2px solid #ffc107;border-radius:var(--raio);padding:1.5rem 2rem;margin-top:2rem;display:flex;align-items:center;gap:1rem;">
        <span style="font-size:2.5rem;">🏆</span>
        <div>
            <strong style="color:#856404;">Barraca melhor avaliada:</strong>
            <div style="color:#533f03;font-size:1.3rem;font-weight:700;"><?= htmlspecialchars($stats['barraca_top']['nome']) ?></div>
            <div style="color:#856404;">Média de <?= number_format($stats['barraca_top']['media'],1) ?>/5 ⭐</div>
        </div>
    </div>
    <?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>
