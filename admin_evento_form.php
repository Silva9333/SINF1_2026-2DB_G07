<?php
// ============================================================
// admin_evento_form.php - FORMULÁRIO CRIAR/EDITAR EVENTO
// ============================================================
// Se ?id=X está na URL → modo de edição
// Se não → modo de criação
// Apenas administradores podem aceder.
// ============================================================
require_once 'BusinessLogicLayer.php';
requireAdmin();

$id = (int)($_GET['id'] ?? 0);
$modo_edicao = $id > 0;
$resultado = null;
$evento = null;
$artistas_do_evento = [];

// Se estamos em modo de edição, carregar os dados atuais do evento
if ($modo_edicao) {
    $evento = getEvento($id);
    if (!$evento) { header('Location: eventos.php'); exit; }
    $artistas_do_evento = array_column($evento['artistas'], 'id');
}

// Carregar listas para os dropdowns
$barracas   = getBarracas();
$artistas   = getArtistas();

$page_title = $modo_edicao ? 'Editar Evento' : 'Novo Evento';
$current_page = 'admin';

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Os checkboxes dos artistas vêm como array
    $artistas_ids = $_POST['artistas_ids'] ?? [];
    if ($modo_edicao) {
        $resultado = processUpdateEvento($id, $_POST, $artistas_ids);
    } else {
        $resultado = processCreateEvento($_POST, $artistas_ids);
        if ($resultado['sucesso']) {
            header('Location: eventos.php?msg=criado');
            exit;
        }
    }
}

include 'includes/header.php';
?>

<div class="container">
    <a href="eventos.php" class="btn btn-outline btn-sm" style="margin-bottom:1rem;">← Voltar aos Eventos</a>

    <div class="form-card" style="max-width:800px;">
        <h2 class="form-title">
            <?= $modo_edicao ? '✏️ Editar Evento' : '➕ Criar Novo Evento' ?>
        </h2>

        <?php if ($resultado): ?>
            <?php showMessage(($resultado['sucesso']?'✅ ':'❌ ').$resultado['mensagem'], $resultado['sucesso']?'success':'error'); ?>
        <?php endif; ?>

        <form action="" method="POST">
            <!-- Nome do evento -->
            <div class="form-group">
                <label>Nome do Evento <span class="obrigatorio">*</span></label>
                <input type="text" name="nome" class="form-control" required
                       placeholder="Ex: Concerto de Abertura - Dino d'Santiago"
                       value="<?= htmlspecialchars($evento['nome'] ?? $_POST['nome'] ?? '') ?>">
            </div>

            <!-- Tipo de evento e data em linha -->
            <div class="form-row">
                <div class="form-group">
                    <label>Tipo de Evento <span class="obrigatorio">*</span></label>
                    <select name="tipo" class="form-control" required id="tipoSelect" onchange="mostrarArtistas()">
                        <option value="">-- Selecionar --</option>
                        <?php foreach (['cerimonia'=>'Cerimónia Académica','concerto'=>'Concerto','atividade'=>'Atividade Cultural'] as $val => $label): ?>
                            <option value="<?= $val ?>" <?= (($evento['tipo'] ?? $_POST['tipo'] ?? '') === $val) ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Data e Hora <span class="obrigatorio">*</span></label>
                    <!-- datetime-local combina data e hora num só campo -->
                    <input type="datetime-local" name="data_hora" class="form-control" required
                           value="<?= $evento ? date('Y-m-d\TH:i', strtotime($evento['data_hora'])) : ($_POST['data_hora'] ?? '') ?>">
                </div>
            </div>

            <!-- Local -->
            <div class="form-group">
                <label>Local <span class="obrigatorio">*</span></label>
                <input type="text" name="local" class="form-control" required
                       placeholder="Ex: Queimódromo - Porto"
                       value="<?= htmlspecialchars($evento['local'] ?? $_POST['local'] ?? '') ?>">
            </div>

            <!-- Descrição -->
            <div class="form-group">
                <label>Descrição</label>
                <textarea name="descricao" class="form-control" rows="4"
                          placeholder="Descreva o evento..."><?= htmlspecialchars($evento['descricao'] ?? $_POST['descricao'] ?? '') ?></textarea>
            </div>

            <!-- Barraca associada (opcional) -->
            <div class="form-group">
                <label>Barraca Associada <span style="color:var(--cor-cinza); font-weight:400;">(opcional)</span></label>
                <select name="barraca_id" class="form-control">
                    <option value="">-- Nenhuma --</option>
                    <?php foreach ($barracas as $b): ?>
                        <option value="<?= $b['id'] ?>" <?= (($evento['barraca_id'] ?? $_POST['barraca_id'] ?? '') == $b['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($b['nome']) ?> (<?= htmlspecialchars($b['faculdade_sigla']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Artistas (só mostrar se for concerto) -->
            <div class="form-group" id="artistasSection" style="display:none;">
                <label>🎤 Artistas (para concertos)</label>
                <div style="background:#f8f4f0; border-radius:8px; padding:1rem; border:1px solid var(--cor-borda); max-height:200px; overflow-y:auto;">
                    <?php foreach ($artistas as $a): ?>
                    <label style="display:flex; align-items:center; gap:0.5rem; padding:0.4rem 0; cursor:pointer; font-weight:400;">
                        <input type="checkbox" name="artistas_ids[]" value="<?= $a['id'] ?>"
                               <?= in_array($a['id'], $artistas_do_evento) ? 'checked' : '' ?>>
                        <span><?= htmlspecialchars($a['nome']) ?></span>
                        <?php if ($a['genero']): ?>
                            <span style="color:var(--cor-cinza); font-size:0.8rem;">(<?= htmlspecialchars($a['genero']) ?>)</span>
                        <?php endif; ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="btn-group" style="margin-top:1.5rem;">
                <button type="submit" class="btn btn-primary btn-lg">
                    <?= $modo_edicao ? '💾 Guardar Alterações' : '➕ Criar Evento' ?>
                </button>
                <a href="eventos.php" class="btn btn-outline btn-lg">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<!-- Ficheiro JS externo: mostrar/esconder artistas consoante o tipo -->
<script src="js/evento_form.js"></script>

<?php include 'includes/footer.php'; ?>
