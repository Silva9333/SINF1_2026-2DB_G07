<?php
// ============================================================
// artista_detalhe.php - DETALHE DO ARTISTA
// ============================================================
require_once 'BusinessLogicLayer.php';
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: artistas.php'); exit; }

$artista = getArtista($id);
if (!$artista) { header('Location: artistas.php'); exit; }

$page_title = $artista['nome'];
$current_page = 'artistas';
include 'includes/header.php';
?>

<div class="container">
    <a href="artistas.php" class="btn btn-outline btn-sm" style="margin-bottom:1rem;">← Voltar aos Artistas</a>

    <div style="background:white; border-radius:var(--raio); box-shadow:var(--sombra); overflow:hidden;">
        <!-- Cabeçalho com gradiente -->
        <div style="background:linear-gradient(135deg, var(--cor-escura), #8B0000); color:white; padding:3rem 2rem; display:flex; align-items:center; gap:2rem; flex-wrap:wrap;">
            <div style="width:100px; height:100px; background:rgba(255,255,255,0.2); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:3rem; font-weight:700; flex-shrink:0;">
                <?= mb_strtoupper(mb_substr($artista['nome'], 0, 1)) ?>
            </div>
            <div>
                <h1 style="font-size:2rem; margin-bottom:0.5rem;"><?= htmlspecialchars($artista['nome']) ?></h1>
                <div style="display:flex; flex-wrap:wrap; gap:1rem; opacity:0.9;">
                    <?php if ($artista['genero']): ?>
                        <span>🎵 <?= htmlspecialchars($artista['genero']) ?></span>
                    <?php endif; ?>
                    <?php if ($artista['pais']): ?>
                        <span>🌍 <?= htmlspecialchars($artista['pais']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <?php if (isAdmin()): ?>
            <div style="margin-left:auto; display:flex; gap:0.5rem;">
                <a href="admin_artista_form.php?id=<?= $artista['id'] ?>" class="btn btn-warning btn-sm">✏️ Editar</a>
                <a href="admin_delete.php?tipo=artista&id=<?= $artista['id'] ?>" class="btn btn-danger btn-sm"
                   onclick="return confirm('Eliminar artista?');">🗑️ Eliminar</a>
            </div>
            <?php endif; ?>
        </div>

        <div style="padding:2rem;">
            <!-- Biografia -->
            <?php if ($artista['biografia']): ?>
            <div style="margin-bottom:2rem;">
                <h3 style="color:var(--cor-escura); margin-bottom:1rem; font-size:1.2rem;">📖 Biografia</h3>
                <p style="line-height:1.8; color:#444;"><?= nl2br(htmlspecialchars($artista['biografia'])) ?></p>
            </div>
            <?php endif; ?>

            <!-- Eventos do artista -->
            <?php if (!empty($artista['eventos'])): ?>
            <div>
                <h3 style="color:var(--cor-escura); margin-bottom:1rem; font-size:1.2rem;">🎪 Atuações na Queima 2026</h3>
                <div style="display:flex; flex-direction:column; gap:0.8rem;">
                    <?php foreach ($artista['eventos'] as $evento): ?>
                    <a href="evento_detalhe.php?id=<?= $evento['id'] ?>" style="text-decoration:none;">
                        <div style="background:#f8f4f0; border-radius:10px; padding:1rem 1.2rem; border:1px solid var(--cor-borda); display:flex; align-items:center; gap:1rem; transition:var(--transicao);"
                             onmouseover="this.style.background='#f0e8e0'" onmouseout="this.style.background='#f8f4f0'">
                            <span class="badge badge-<?= $evento['tipo'] ?>"><?= tipoEventoLabel($evento['tipo']) ?></span>
                            <div>
                                <strong style="color:var(--cor-escura);"><?= htmlspecialchars($evento['nome']) ?></strong>
                                <div style="color:var(--cor-cinza); font-size:0.85rem;">
                                    📅 <?= formatarDataHora($evento['data_hora']) ?> &nbsp;·&nbsp; 📍 <?= htmlspecialchars($evento['local']) ?>
                                </div>
                            </div>
                            <span style="margin-left:auto; color:var(--cor-primaria);">→</span>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php else: ?>
            <div class="alert alert-info">Este artista ainda não tem atuações associadas.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
