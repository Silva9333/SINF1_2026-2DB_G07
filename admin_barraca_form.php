<?php
// ============================================================
// admin_barraca_form.php - FORMULÁRIO CRIAR/EDITAR BARRACA
// ============================================================
require_once 'BusinessLogicLayer.php';
requireAdmin();

$id = (int)($_GET['id'] ?? 0);
$modo_edicao = $id > 0;
$resultado = null;
$barraca = $modo_edicao ? getBarraca($id) : null;
$faculdades = getFaculdades();

if ($modo_edicao && !$barraca) { header('Location: barracas.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($modo_edicao) {
        $resultado = processUpdateBarraca($id, $_POST);
        if ($resultado['sucesso']) $barraca = getBarraca($id);
    } else {
        $resultado = processCreateBarraca($_POST);
        if ($resultado['sucesso']) { header('Location: barracas.php'); exit; }
    }
}

$page_title = $modo_edicao ? 'Editar Barraca' : 'Nova Barraca';
$current_page = 'admin';
include 'includes/header.php';
?>

<div class="container">
    <a href="barracas.php" class="btn btn-outline btn-sm" style="margin-bottom:1rem;">← Voltar às Barracas</a>

    <div class="form-card">
        <h2 class="form-title"><?= $modo_edicao ? '✏️ Editar Barraca' : '⛺ Nova Barraca' ?></h2>

        <?php if ($resultado): ?>
            <?php showMessage(($resultado['sucesso']?'✅ ':'❌ ').$resultado['mensagem'], $resultado['sucesso']?'success':'error'); ?>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Nome da Barraca <span class="obrigatorio">*</span></label>
                <input type="text" name="nome" class="form-control" required
                       placeholder="Ex: Barraca da FEUP"
                       value="<?= htmlspecialchars($barraca['nome'] ?? $_POST['nome'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Faculdade <span class="obrigatorio">*</span></label>
                <select name="faculdade_id" class="form-control" required>
                    <option value="">-- Selecionar Faculdade --</option>
                    <?php foreach ($faculdades as $f): ?>
                        <option value="<?= $f['id'] ?>" <?= (($barraca['faculdade_id'] ?? $_POST['faculdade_id'] ?? '') == $f['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($f['sigla']) ?> – <?= htmlspecialchars($f['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Localização no Recinto</label>
                <input type="text" name="localizacao" class="form-control"
                       placeholder="Ex: Zona A - Entrada Principal"
                       value="<?= htmlspecialchars($barraca['localizacao'] ?? $_POST['localizacao'] ?? '') ?>">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Hora de Abertura</label>
                    <input type="time" name="hora_abertura" class="form-control"
                           value="<?= $barraca ? substr($barraca['hora_abertura'],0,5) : ($_POST['hora_abertura'] ?? '18:00') ?>">
                </div>
                <div class="form-group">
                    <label>Hora de Fecho</label>
                    <input type="time" name="hora_fecho" class="form-control"
                           value="<?= $barraca ? substr($barraca['hora_fecho'],0,5) : ($_POST['hora_fecho'] ?? '04:00') ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Descrição</label>
                <textarea name="descricao" class="form-control" rows="4"
                          placeholder="Descreva a barraca, as suas especialidades e o ambiente..."><?= htmlspecialchars($barraca['descricao'] ?? $_POST['descricao'] ?? '') ?></textarea>
            </div>

            <div class="btn-group">
                <button type="submit" class="btn btn-primary btn-lg">
                    <?= $modo_edicao ? '💾 Guardar Alterações' : '⛺ Criar Barraca' ?>
                </button>
                <a href="barracas.php" class="btn btn-outline btn-lg">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
