<?php
// ============================================================
// evento_detalhe.php - PÁGINA DE DETALHE DE UM EVENTO
// ============================================================
// Mostra toda a informação de um evento específico.
// Permite: adicionar à agenda, avaliar (só estudantes).
// ============================================================
require_once 'BusinessLogicLayer.php';

// Obter o ID do evento da URL (?id=X)
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: eventos.php'); exit; }

$page_title = 'Evento';
$current_page = 'eventos';
$resultado = null;

// Processar ações POST (avaliação, agenda)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['acao'])) {
        switch ($_POST['acao']) {
            case 'avaliar':
                // Só estudantes podem avaliar (verificado na BLL)
                $resultado = processRatingEvento($id, $_POST['nota'] ?? 0, $_POST['comentario'] ?? '');
                break;
            case 'add_agenda':
                $resultado = processAddToAgenda($id);
                break;
            case 'remove_agenda':
                $resultado = processRemoveFromAgenda($id);
                break;
        }
    }
}

// Obter dados do evento (inclui artistas, comentários, agenda)
$evento = getEvento($id);
if (!$evento) { header('Location: eventos.php'); exit; }

$page_title = $evento['nome'];
include 'includes/header.php';
?>

<div class="container">
    <!-- BOTÃO VOLTAR -->
    <a href="eventos.php" class="btn btn-outline btn-sm" style="margin-bottom:1rem;">← Voltar aos Eventos</a>

    <!-- ===== CABEÇALHO DO EVENTO ===== -->
    <div class="detail-header">
        <span class="badge badge-<?= $evento['tipo'] ?>" style="margin-bottom:0.8rem;"><?= tipoEventoLabel($evento['tipo']) ?></span>
        <h1><?= htmlspecialchars($evento['nome']) ?></h1>
        <div style="display:flex; flex-wrap:wrap; gap:1.5rem; margin-top:1rem; opacity:0.9; font-size:0.95rem;">
            <span>📅 <?= formatarDataHora($evento['data_hora']) ?></span>
            <span>📍 <?= htmlspecialchars($evento['local']) ?></span>
            <?php if ($evento['barraca_nome']): ?>
                <span>⛺ <?= htmlspecialchars($evento['barraca_nome']) ?></span>
            <?php endif; ?>
        </div>
        <!-- Avaliação média no cabeçalho -->
        <?php if ($evento['total_ratings'] > 0): ?>
        <div style="margin-top:1rem;">
            <span class="stars stars-lg"><?= str_repeat('★', round($evento['media_rating'])) ?><?= str_repeat('☆', 5 - round($evento['media_rating'])) ?></span>
            <span style="opacity:0.8; font-size:0.9rem;"><?= number_format($evento['media_rating'], 1) ?>/5 (<?= $evento['total_ratings'] ?> avaliações)</span>
        </div>
        <?php endif; ?>
    </div>

    <div class="detail-body">
        <!-- MENSAGEM DE RESULTADO (avaliação, agenda) -->
        <?php if ($resultado): ?>
            <?php showMessage(($resultado['sucesso'] ? '✅ ' : '❌ ') . $resultado['mensagem'], $resultado['sucesso'] ? 'success' : 'error'); ?>
        <?php endif; ?>

        <!-- LAYOUT EM 2 COLUNAS: descrição + ações -->
        <div style="display:grid; grid-template-columns:1fr 300px; gap:2rem;">
            <!-- COLUNA ESQUERDA: descrição e artistas -->
            <div>
                <h3 style="color:var(--cor-escura); margin-bottom:1rem; font-size:1.2rem;">📋 Descrição</h3>
                <p style="line-height:1.8; color:#444;"><?= nl2br(htmlspecialchars($evento['descricao'])) ?></p>

                <!-- ARTISTAS ASSOCIADOS (só se tiver) -->
                <?php if (!empty($evento['artistas'])): ?>
                <div style="margin-top:2rem;">
                    <h3 style="color:var(--cor-escura); margin-bottom:1rem; font-size:1.2rem;">🎤 Artistas</h3>
                    <div style="display:flex; flex-wrap:wrap; gap:1rem;">
                        <?php foreach ($evento['artistas'] as $artista): ?>
                        <a href="artista_detalhe.php?id=<?= $artista['id'] ?>" style="text-decoration:none;">
                            <div style="background:#f8f4f0; border:1px solid var(--cor-borda); border-radius:10px; padding:1rem; text-align:center; min-width:140px; transition:var(--transicao);"
                                 onmouseover="this.style.background='#f0e8e0'" onmouseout="this.style.background='#f8f4f0'">
                                <div style="font-size:2rem; margin-bottom:0.5rem;">🎤</div>
                                <strong style="color:var(--cor-escura); font-size:0.95rem;"><?= htmlspecialchars($artista['nome']) ?></strong>
                                <div style="color:var(--cor-cinza); font-size:0.8rem; margin-top:0.2rem;"><?= htmlspecialchars($artista['genero']) ?></div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- COMENTÁRIOS -->
                <?php if (!empty($evento['comentarios'])): ?>
                <div style="margin-top:2rem;">
                    <h3 style="color:var(--cor-escura); margin-bottom:1rem; font-size:1.2rem;">💬 Comentários</h3>
                    <?php foreach ($evento['comentarios'] as $comentario): ?>
                    <div style="background:#f8f4f0; border-left:3px solid var(--cor-primaria); border-radius:0 8px 8px 0; padding:1rem; margin-bottom:0.8rem;">
                        <div style="display:flex; justify-content:space-between; margin-bottom:0.4rem;">
                            <strong style="color:var(--cor-escura);"><?= htmlspecialchars($comentario['user_nome']) ?></strong>
                            <span class="stars" style="font-size:0.9rem;"><?= str_repeat('★', $comentario['nota']) ?><?= str_repeat('☆', 5 - $comentario['nota']) ?></span>
                        </div>
                        <p style="color:#444; font-size:0.9rem; margin:0;"><?= htmlspecialchars($comentario['comentario']) ?></p>
                        <small style="color:var(--cor-cinza);"><?= date('d/m/Y H:i', strtotime($comentario['created_at'])) ?></small>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- COLUNA DIREITA: ações -->
            <div>
                <!-- BOTÃO AGENDA -->
                <?php if (isLoggedIn()): ?>
                <div style="background:#f8f4f0; border-radius:10px; padding:1.5rem; margin-bottom:1.5rem; border:1px solid var(--cor-borda);">
                    <h4 style="color:var(--cor-escura); margin-bottom:1rem;">📅 Minha Agenda</h4>
                    <?php if ($evento['na_agenda']): ?>
                        <p style="color:#155724; font-size:0.9rem; margin-bottom:0.8rem;">✅ Este evento está na sua agenda.</p>
                        <form method="POST">
                            <input type="hidden" name="acao" value="remove_agenda">
                            <button type="submit" class="btn btn-danger" style="width:100%; justify-content:center;">🗑️ Remover da Agenda</button>
                        </form>
                    <?php else: ?>
                        <p style="color:var(--cor-cinza); font-size:0.9rem; margin-bottom:0.8rem;">Adicione este evento à sua agenda pessoal.</p>
                        <form method="POST">
                            <input type="hidden" name="acao" value="add_agenda">
                            <button type="submit" class="btn btn-success" style="width:100%; justify-content:center;">➕ Adicionar à Agenda</button>
                        </form>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- FORMULÁRIO DE AVALIAÇÃO (só para estudantes) -->
                <?php if (isStudent()): ?>
                <div style="background:#f8f4f0; border-radius:10px; padding:1.5rem; border:1px solid var(--cor-borda);">
                    <h4 style="color:var(--cor-escura); margin-bottom:1rem;">⭐ Avaliar Evento</h4>
                    <?php if ($evento['meu_rating']): ?>
                        <p style="color:var(--cor-cinza); font-size:0.85rem;">A sua avaliação atual: <?= str_repeat('★', $evento['meu_rating']['nota']) ?></p>
                        <p style="color:var(--cor-cinza); font-size:0.85rem; margin-bottom:1rem;">Pode atualizar abaixo:</p>
                    <?php endif; ?>

                    <form method="POST">
                        <input type="hidden" name="acao" value="avaliar">
                        <!-- Seletor de estrelas interativo (JavaScript) -->
                        <div class="form-group">
                            <label>Classificação:</label>
                            <div class="star-picker" id="starPicker">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <button type="button" class="star-btn <?= ($evento['meu_rating'] && $i <= $evento['meu_rating']['nota']) ? 'ativo' : '' ?>"
                                            data-valor="<?= $i ?>" onclick="selecionarEstrela(<?= $i ?>)">★</button>
                                <?php endfor; ?>
                            </div>
                            <!-- Campo hidden que guarda a nota selecionada -->
                            <input type="hidden" name="nota" id="notaInput"
                                   value="<?= $evento['meu_rating']['nota'] ?? 0 ?>">
                        </div>
                        <div class="form-group">
                            <label>Comentário (opcional):</label>
                            <textarea name="comentario" class="form-control" rows="3"
                                      placeholder="Partilhe a sua experiência..."><?= htmlspecialchars($evento['meu_rating']['comentario'] ?? '') ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">💾 Guardar Avaliação</button>
                    </form>
                </div>
                <?php elseif (!isLoggedIn()): ?>
                <div class="alert alert-info">
                    <a href="login.php">Faça login</a> para avaliar este evento e adicionar à sua agenda.
                </div>
                <?php endif; ?>

                <!-- BOTÕES DE ADMINISTRAÇÃO -->
                <?php if (isAdmin()): ?>
                <div style="margin-top:1rem; display:flex; flex-direction:column; gap:0.5rem;">
                    <a href="admin_evento_form.php?id=<?= $evento['id'] ?>" class="btn btn-warning" style="justify-content:center;">✏️ Editar Evento</a>
                    <a href="admin_delete.php?tipo=evento&id=<?= $evento['id'] ?>" class="btn btn-danger" style="justify-content:center;"
                       onclick="return confirm('Eliminar este evento permanentemente?');">🗑️ Eliminar Evento</a>
                </div>
                <?php endif; ?>
            </div>
        </div><!-- fim grid 2 colunas -->
    </div><!-- fim detail-body -->
</div>

<!-- Ficheiro JS externo: lógica de seleção de estrelas em js/estrelas.js -->
<!-- Os botões .star-btn chamam selecionarEstrela(valor) definida neste ficheiro -->
<script src="js/estrelas.js"></script>

<?php include 'includes/footer.php'; ?>
