<?php
// ============================================================
// admin_faculdade_form.php - FORMULÁRIO CRIAR/EDITAR FACULDADE
// ============================================================
require_once 'BusinessLogicLayer.php';
requireAdmin();

$id = (int)($_GET['id'] ?? 0);
$modo_edicao = $id > 0;
$resultado = null;
$faculdade = $modo_edicao ? getFaculdade($id) : null;

if ($modo_edicao && !$faculdade) { header('Location: faculdades.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($modo_edicao) {
        $resultado = processUpdateFaculdade($id, $_POST);
        if ($resultado['sucesso']) $faculdade = getFaculdade($id);
    } else {
        $resultado = processCreateFaculdade($_POST);
        if ($resultado['sucesso']) { header('Location: faculdades.php'); exit; }
    }
}

$page_title = $modo_edicao ? 'Editar Faculdade' : 'Nova Faculdade';
$current_page = 'admin';
include 'includes/header.php';
?>

<div class="container">
    <a href="faculdades.php" class="btn btn-outline btn-sm" style="margin-bottom:1rem;">← Voltar às Faculdades</a>

    <div class="form-card">
        <h2 class="form-title"><?= $modo_edicao ? '✏️ Editar Faculdade' : '🏛️ Nova Faculdade' ?></h2>

        <?php if ($resultado): ?>
            <?php showMessage(($resultado['sucesso']?'✅ ':'❌ ').$resultado['mensagem'], $resultado['sucesso']?'success':'error'); ?>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Nome Completo da Faculdade <span class="obrigatorio">*</span></label>
                <input type="text" name="nome" class="form-control" required
                       placeholder="Ex: Faculdade de Engenharia"
                       value="<?= htmlspecialchars($faculdade['nome'] ?? $_POST['nome'] ?? '') ?>">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Sigla <span class="obrigatorio">*</span></label>
                    <input type="text" name="sigla" class="form-control" required
                           placeholder="Ex: FEUP" maxlength="20"
                           style="text-transform:uppercase;"
                           value="<?= htmlspecialchars($faculdade['sigla'] ?? $_POST['sigla'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Cor Representativa</label>
                    <div style="display:flex; align-items:center; gap:0.8rem;">
                        <!-- Input de cor - abre o seletor de cores nativo do browser -->
                        <input type="color" name="cor" class="form-control" style="height:45px; padding:4px; width:80px; cursor:pointer;"
                               value="<?= htmlspecialchars($faculdade['cor'] ?? $_POST['cor'] ?? '#8B0000') ?>">
                        <span style="color:var(--cor-cinza); font-size:0.85rem;">Escolha a cor representativa da faculdade</span>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Descrição</label>
                <textarea name="descricao" class="form-control" rows="4"
                          placeholder="Breve descrição da faculdade..."><?= htmlspecialchars($faculdade['descricao'] ?? $_POST['descricao'] ?? '') ?></textarea>
            </div>

            <div class="btn-group">
                <button type="submit" class="btn btn-primary btn-lg">
                    <?= $modo_edicao ? '💾 Guardar Alterações' : '🏛️ Criar Faculdade' ?>
                </button>
                <a href="faculdades.php" class="btn btn-outline btn-lg">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
