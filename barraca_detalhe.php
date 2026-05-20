<?php
// ============================================================
// barraca_detalhe.php - DETALHE DA BARRACA
// ============================================================
require_once 'BusinessLogicLayer.php';
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: barracas.php'); exit; }

$resultado = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'avaliar') {
    $resultado = processRatingBarraca($id, $_POST['nota'] ?? 0, $_POST['comentario'] ?? '');
}

$barraca = getBarraca($id);
if (!$barraca) { header('Location: barracas.php'); exit; }

$page_title = $barraca['nome'];
$current_page = 'barracas';
include 'includes/header.php';
?>

<div class="container">
    <a href="barracas.php" class="btn btn-outline btn-sm" style="margin-bottom:1rem;">← Voltar às Barracas</a>

    <div style="background:white; border-radius:var(--raio); box-shadow:var(--sombra); overflow:hidden;">
        <!-- Cabeçalho com cor da faculdade -->
        <div style="background:linear-gradient(135deg, <?= $barraca['faculdade_cor'] ?>, <?= $barraca['faculdade_cor'] ?>aa); color:white; padding:3rem 2rem;">
            <div style="display:flex; align-items:center; gap:1rem; margin-bottom:1rem; flex-wrap:wrap;">
                <span style="background:rgba(255,255,255,0.25); border-radius:20px; padding:4px 16px; font-size:0.9rem; font-weight:700;">
                    <?= htmlspecialchars($barraca['faculdade_sigla']) ?>
                </span>
                <?php if ($barraca['total_ratings'] > 0): ?>
                    <span>⭐ <?= number_format($barraca['media_rating'],1) ?>/5 (<?= $barraca['total_ratings'] ?> avaliações)</span>
                <?php endif; ?>
            </div>
            <h1 style="font-size:2rem; margin-bottom:0.5rem;"><?= htmlspecialchars($barraca['nome']) ?></h1>
            <div style="opacity:0.9; display:flex; flex-wrap:wrap; gap:1.5rem; font-size:0.95rem;">
                <span>🏛️ <?= htmlspecialchars($barraca['faculdade_nome']) ?></span>
                <span>📍 <?= htmlspecialchars($barraca['localizacao']) ?></span>
                <span>🕐 <?= substr($barraca['hora_abertura'],0,5) ?> – <?= substr($barraca['hora_fecho'],0,5) ?></span>
            </div>
            <?php if (isAdmin()): ?>
            <div style="margin-top:1.5rem; display:flex; gap:0.5rem;">
                <a href="admin_barraca_form.php?id=<?= $barraca['id'] ?>" class="btn btn-warning btn-sm">✏️ Editar</a>
                <a href="admin_delete.php?tipo=barraca&id=<?= $barraca['id'] ?>" class="btn btn-danger btn-sm"
                   onclick="return confirm('Eliminar esta barraca?');">🗑️ Eliminar</a>
            </div>
            <?php endif; ?>
        </div>

        <div style="padding:2rem; display:grid; grid-template-columns:1fr 300px; gap:2rem;">
            <!-- COLUNA ESQUERDA -->
            <div>
                <h3 style="color:var(--cor-escura); margin-bottom:1rem;">📋 Sobre a Barraca</h3>
                <p style="line-height:1.8; color:#444;"><?= nl2br(htmlspecialchars($barraca['descricao'])) ?></p>

                <!-- Comentários -->
                <?php if (!empty($barraca['comentarios'])): ?>
                <div style="margin-top:2rem;">
                    <h3 style="color:var(--cor-escura); margin-bottom:1rem;">💬 Avaliações dos Estudantes</h3>
                    <?php foreach ($barraca['comentarios'] as $c): ?>
                    <div style="background:#f8f4f0; border-left:3px solid <?= $barraca['faculdade_cor'] ?>; border-radius:0 8px 8px 0; padding:1rem; margin-bottom:0.8rem;">
                        <div style="display:flex; justify-content:space-between; margin-bottom:0.4rem;">
                            <strong style="color:var(--cor-escura);"><?= htmlspecialchars($c['user_nome']) ?></strong>
                            <span class="stars" style="font-size:0.9rem;"><?= str_repeat('★',$c['nota']) ?><?= str_repeat('☆',5-$c['nota']) ?></span>
                        </div>
                        <p style="color:#444; font-size:0.9rem; margin:0;"><?= htmlspecialchars($c['comentario']) ?></p>
                        <small style="color:var(--cor-cinza);"><?= date('d/m/Y H:i', strtotime($c['created_at'])) ?></small>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- COLUNA DIREITA: avaliação -->
            <div>
                <?php if ($resultado): ?>
                    <?php showMessage(($resultado['sucesso']?'✅ ':'❌ ').$resultado['mensagem'], $resultado['sucesso']?'success':'error'); ?>
                <?php endif; ?>

                <?php if (isStudent()): ?>
                <div style="background:#f8f4f0; border-radius:10px; padding:1.5rem; border:1px solid var(--cor-borda);">
                    <h4 style="color:var(--cor-escura); margin-bottom:1rem;">⭐ Avaliar esta Barraca</h4>
                    <?php if ($barraca['meu_rating']): ?>
                        <p style="color:var(--cor-cinza); font-size:0.85rem; margin-bottom:0.8rem;">A sua avaliação atual: <?= str_repeat('★',$barraca['meu_rating']['nota']) ?></p>
                    <?php endif; ?>
                    <form method="POST">
                        <input type="hidden" name="acao" value="avaliar">
                        <div class="form-group">
                            <label>Classificação:</label>
                            <div class="star-picker">
                                <?php for ($i=1;$i<=5;$i++): ?>
                                    <button type="button" class="star-btn <?= ($barraca['meu_rating'] && $i<=$barraca['meu_rating']['nota'])?'ativo':'' ?>"
                                            data-valor="<?= $i ?>" onclick="selecionarEstrela(<?= $i ?>)">★</button>
                                <?php endfor; ?>
                            </div>
                            <input type="hidden" name="nota" id="notaInput" value="<?= $barraca['meu_rating']['nota'] ?? 0 ?>">
                        </div>
                        <div class="form-group">
                            <label>Comentário:</label>
                            <textarea name="comentario" class="form-control" rows="3"
                                      placeholder="A sua experiência..."><?= htmlspecialchars($barraca['meu_rating']['comentario'] ?? '') ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">💾 Guardar</button>
                    </form>
                </div>
                <?php elseif (!isLoggedIn()): ?>
                <div class="alert alert-info"><a href="login.php">Faça login</a> para avaliar esta barraca.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Ficheiro JS externo: lógica de estrelas partilhada com evento_detalhe -->
<!-- A função selecionarEstrela() está definida em js/estrelas.js -->
<script src="js/estrelas.js"></script>

<?php include 'includes/footer.php'; ?>
