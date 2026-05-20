<?php
// ============================================================
// admin_artista_form.php - FORMULÁRIO CRIAR/EDITAR ARTISTA
// ============================================================
require_once 'BusinessLogicLayer.php';
requireAdmin();

$id = (int)($_GET['id'] ?? 0);
$modo_edicao = $id > 0;
$resultado = null;
$artista = $modo_edicao ? getArtista($id) : null;

if ($modo_edicao && !$artista) { header('Location: artistas.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($modo_edicao) {
        $resultado = processUpdateArtista($id, $_POST);
        if ($resultado['sucesso']) $artista = getArtista($id);
    } else {
        $resultado = processCreateArtista($_POST);
        if ($resultado['sucesso']) { header('Location: artistas.php'); exit; }
    }
}

$page_title = $modo_edicao ? 'Editar Artista' : 'Novo Artista';
$current_page = 'admin';
include 'includes/header.php';
?>

<div class="container">
    <a href="artistas.php" class="btn btn-outline btn-sm" style="margin-bottom:1rem;">← Voltar aos Artistas</a>

    <div class="form-card">
        <h2 class="form-title"><?= $modo_edicao ? '✏️ Editar Artista' : '➕ Novo Artista' ?></h2>

        <?php if ($resultado): ?>
            <?php showMessage(($resultado['sucesso']?'✅ ':'❌ ').$resultado['mensagem'], $resultado['sucesso']?'success':'error'); ?>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Nome do Artista <span class="obrigatorio">*</span></label>
                <input type="text" name="nome" class="form-control" required
                       placeholder="Ex: Dino d'Santiago"
                       value="<?= htmlspecialchars($artista['nome'] ?? $_POST['nome'] ?? '') ?>">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Género Musical</label>
                    <input type="text" name="genero" class="form-control"
                           placeholder="Ex: R&B / Soul / Funaná"
                           value="<?= htmlspecialchars($artista['genero'] ?? $_POST['genero'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>País</label>
                    <input type="text" name="pais" class="form-control"
                           placeholder="Ex: Portugal"
                           value="<?= htmlspecialchars($artista['pais'] ?? $_POST['pais'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Biografia</label>
                <textarea name="biografia" class="form-control" rows="5"
                          placeholder="Breve biografia do artista..."><?= htmlspecialchars($artista['biografia'] ?? $_POST['biografia'] ?? '') ?></textarea>
            </div>

            <div class="btn-group">
                <button type="submit" class="btn btn-primary btn-lg">
                    <?= $modo_edicao ? '💾 Guardar Alterações' : '➕ Adicionar Artista' ?>
                </button>
                <a href="artistas.php" class="btn btn-outline btn-lg">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
