<?php
// ============================================================
// index.php - PÁGINA INICIAL
// ============================================================
// PRINCÍPIO: esta página NÃO tem lógica.
// Apenas:
//   1. Chama funções da BLL para obter dados
//   2. Imprime o HTML com os dados recebidos
// Toda a lógica (quantos mostrar, o que são alertas, etc.)
// vive na BusinessLogicLayer.php.
// ============================================================

require_once 'BusinessLogicLayer.php';

$page_title   = 'Início';
$current_page = 'inicio';

// Obter dados preparados pela BLL — sem lógica aqui
$proximos_eventos = getEventosDestaque(6);    // BLL decide o limite
$alertas          = getAlertasProximos();     // BLL filtra as 48h
$msg_url          = getMensagemURL();         // BLL lê a query string

include 'includes/header.php';
?>

<div class="hero">
    <div class="hero-content">
        <div class="hero-year">🎓 Porto · Maio 2026</div>
        <h1>🎉 Queima das <span>Fitas</span></h1>
        <p>A maior festa académica do Porto. Uma semana de concertos, cerimónias, barracas e convívio entre estudantes de todas as faculdades da Universidade do Porto.</p>
        <div class="btn-group" style="justify-content:center; margin-top:1.5rem;">
            <a href="eventos.php" class="btn btn-warning btn-lg">🎪 Ver Programa</a>
            <a href="artistas.php" class="btn btn-lg" style="background:rgba(255,255,255,0.15);color:white;border:2px solid rgba(255,255,255,0.4);">🎤 Artistas</a>
            <?php if (!isLoggedIn()): ?>
                <a href="registo.php" class="btn btn-lg" style="background:rgba(255,255,255,0.15);color:white;border:2px solid rgba(255,255,255,0.4);">📝 Registar</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="container">

    <?php if ($msg_url): ?>
        <?php showMessage($msg_url[0], $msg_url[1]); ?>
    <?php endif; ?>

    <!-- Countdown — lógica da data está no JavaScript, não em PHP -->
    <div class="countdown-box">
        <div class="countdown-title">⏳ Faltam para a Queima das Fitas 2026</div>
        <div class="countdown-timer" id="countdown">
            <div class="countdown-unit"><span class="num" id="cd-dias">--</span><span class="label">Dias</span></div>
            <div class="countdown-unit"><span class="num" id="cd-horas">--</span><span class="label">Horas</span></div>
            <div class="countdown-unit"><span class="num" id="cd-min">--</span><span class="label">Minutos</span></div>
            <div class="countdown-unit"><span class="num" id="cd-seg">--</span><span class="label">Segundos</span></div>
        </div>
    </div>

    <!-- Alertas 48h -->
    <?php if (!empty($alertas)): ?>
    <div class="alertas-container">
        <h3>🔔 Eventos nas próximas 48 horas:</h3>
        <?php foreach ($alertas as $alerta): ?>
            <div class="alerta-item">
                <span>⚡</span>
                <strong><?= htmlspecialchars($alerta['nome']) ?></strong>
                &nbsp;—&nbsp;
                <?= formatarDataHora($alerta['data_hora']) ?>
                &nbsp;
                <a href="evento_detalhe.php?id=<?= $alerta['id'] ?>" style="color:#856404;font-weight:600;">Ver →</a>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Próximos eventos em destaque -->
    <div class="section-header">
        <h2>🎪 Próximos Eventos</h2>
        <a href="eventos.php" class="btn btn-outline btn-sm">Ver todos →</a>
    </div>

    <?php if (empty($proximos_eventos)): ?>
        <div class="alert alert-info">Não há eventos disponíveis de momento.</div>
    <?php else: ?>
    <div class="grid">
        <?php foreach ($proximos_eventos as $evento): ?>
        <div class="card" onclick="location.href='evento_detalhe.php?id=<?= $evento['id'] ?>'" style="cursor:pointer;">
            <div class="card-header-evento <?= $evento['tipo'] ?>"></div>
            <div class="card-body">
                <span class="badge badge-<?= $evento['tipo'] ?>"><?= tipoEventoLabel($evento['tipo']) ?></span>
                <h3 class="card-title" style="margin-top:0.6rem;"><?= htmlspecialchars($evento['nome']) ?></h3>
                <div class="card-meta">
                    <span>📅 <?= formatarDataHora($evento['data_hora']) ?></span>
                    <span>📍 <?= htmlspecialchars($evento['local']) ?></span>
                </div>
                <?php if ($evento['total_ratings'] > 0): ?>
                    <div style="margin-top:0.5rem;"><?= renderStars($evento['media_rating'], $evento['total_ratings']) ?></div>
                <?php endif; ?>
            </div>
            <div class="card-footer">
                <a href="evento_detalhe.php?id=<?= $evento['id'] ?>" class="btn btn-primary btn-sm" onclick="event.stopPropagation();">Ver detalhes</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Links rápidos -->
    <div style="margin-top:3rem;">
        <div class="section-header"><h2>🗺️ Explorar</h2></div>
        <div class="grid" style="grid-template-columns:repeat(auto-fill,minmax(220px,1fr));">
            <a href="artistas.php" class="card" style="text-decoration:none;text-align:center;">
                <div class="card-body" style="align-items:center;padding:2rem 1rem;">
                    <div style="font-size:3rem;margin-bottom:1rem;">🎤</div>
                    <h3 style="color:var(--cor-escura);">Artistas</h3>
                    <p style="color:var(--cor-cinza);font-size:0.9rem;margin-top:0.5rem;">Conheça os artistas confirmados para 2026</p>
                </div>
            </a>
            <a href="barracas.php" class="card" style="text-decoration:none;text-align:center;">
                <div class="card-body" style="align-items:center;padding:2rem 1rem;">
                    <div style="font-size:3rem;margin-bottom:1rem;">⛺</div>
                    <h3 style="color:var(--cor-escura);">Barracas</h3>
                    <p style="color:var(--cor-cinza);font-size:0.9rem;margin-top:0.5rem;">Barracas das faculdades com horários</p>
                </div>
            </a>
            <a href="faculdades.php" class="card" style="text-decoration:none;text-align:center;">
                <div class="card-body" style="align-items:center;padding:2rem 1rem;">
                    <div style="font-size:3rem;margin-bottom:1rem;">🏛️</div>
                    <h3 style="color:var(--cor-escura);">Faculdades</h3>
                    <p style="color:var(--cor-cinza);font-size:0.9rem;margin-top:0.5rem;">Faculdades da Universidade do Porto</p>
                </div>
            </a>
            <?php if (isLoggedIn()): ?>
            <a href="agenda.php" class="card" style="text-decoration:none;text-align:center;">
                <div class="card-body" style="align-items:center;padding:2rem 1rem;">
                    <div style="font-size:3rem;margin-bottom:1rem;">📅</div>
                    <h3 style="color:var(--cor-escura);">Minha Agenda</h3>
                    <p style="color:var(--cor-cinza);font-size:0.9rem;margin-top:0.5rem;">Os seus eventos favoritos</p>
                </div>
            </a>
            <?php else: ?>
            <a href="registo.php" class="card" style="text-decoration:none;text-align:center;border:2px dashed var(--cor-primaria);">
                <div class="card-body" style="align-items:center;padding:2rem 1rem;">
                    <div style="font-size:3rem;margin-bottom:1rem;">📝</div>
                    <h3 style="color:var(--cor-primaria);">Criar Conta</h3>
                    <p style="color:var(--cor-cinza);font-size:0.9rem;margin-top:0.5rem;">Registe-se para criar agenda e avaliar</p>
                </div>
            </a>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- Ficheiro JS externo: toda a lógica do countdown está em js/countdown.js -->
<script src="js/countdown.js"></script>

<?php include 'includes/footer.php'; ?>
