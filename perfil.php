<?php
// ============================================================
// perfil.php - PERFIL DO UTILIZADOR
// ============================================================
// SEM lógica: processamento e validação passaram para
// processUpdatePerfil() e getUserAtual() na BLL.
// ============================================================
require_once 'BusinessLogicLayer.php';
requireLogin();

$page_title   = 'O Meu Perfil';
$current_page = 'perfil';
$resultado    = null;

// Processar formulário — BLL trata tudo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resultado = processUpdatePerfil(
        getCurrentUserId(),
        trim($_POST['nome']  ?? ''),
        trim($_POST['email'] ?? '')
    );
}

// Obter dados atualizados do utilizador via BLL
$user          = getUserAtual();
$agenda_count  = count(getMinhaAgenda());

include 'includes/header.php';
?>

<div class="container" style="max-width:800px;">

    <div class="profile-header">
        <div class="profile-avatar">
            <?= mb_strtoupper(mb_substr($user['nome'], 0, 1)) ?>
        </div>
        <div>
            <h2 style="font-size:1.8rem;margin-bottom:0.3rem;"><?= htmlspecialchars($user['nome']) ?></h2>
            <p style="opacity:0.8;"><?= htmlspecialchars($user['email']) ?></p>
            <div style="margin-top:0.5rem;display:flex;gap:1rem;flex-wrap:wrap;">
                <span style="background:rgba(255,255,255,0.2);border-radius:20px;padding:3px 12px;font-size:0.85rem;">
                    <?= isAdmin() ? '⚙️ Administrador' : '🎓 Estudante' ?>
                </span>
                <span style="background:rgba(255,255,255,0.2);border-radius:20px;padding:3px 12px;font-size:0.85rem;">
                    📅 <?= $agenda_count ?> evento(s) na agenda
                </span>
                <span style="background:rgba(255,255,255,0.2);border-radius:20px;padding:3px 12px;font-size:0.85rem;">
                    📆 Membro desde <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                </span>
            </div>
        </div>
    </div>

    <div class="form-card" style="margin:1.5rem 0 0;">
        <h3 class="form-title">✏️ Editar Perfil</h3>

        <?php if ($resultado): ?>
            <?php showMessage(($resultado['sucesso'] ? '✅ ' : '❌ ') . $resultado['mensagem'],
                               $resultado['sucesso'] ? 'success' : 'error'); ?>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>👤 Nome Completo <span class="obrigatorio">*</span></label>
                <input type="text" name="nome" class="form-control"
                       value="<?= htmlspecialchars($user['nome']) ?>" required>
            </div>
            <div class="form-group">
                <label>📧 Email <span class="obrigatorio">*</span></label>
                <input type="email" name="email" class="form-control"
                       value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <div class="btn-group">
                <button type="submit" class="btn btn-primary">💾 Guardar Alterações</button>
                <a href="agenda.php" class="btn btn-outline">📅 Ver Agenda</a>
            </div>
        </form>
    </div>

    <div class="alert alert-info" style="margin-top:1.5rem;">
        <?php if (isAdmin()): ?>
            ⚙️ Tem perfil de <strong>Administrador</strong>.
            Aceda ao <a href="admin.php">Painel de Administração</a> para gerir conteúdos.
        <?php else: ?>
            🎓 Tem perfil de <strong>Estudante</strong>. Pode avaliar eventos, avaliar barracas e criar a sua agenda pessoal.
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
