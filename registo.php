<?php
// ============================================================
// registo.php - PÁGINA DE REGISTO DE NOVO UTILIZADOR
// ============================================================
require_once 'BusinessLogicLayer.php';

if (isLoggedIn()) { header('Location: index.php'); exit; }

$page_title = 'Criar Conta';
$current_page = 'registo';
$resultado = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resultado = processRegisto(
        $_POST['nome'] ?? '',
        $_POST['email'] ?? '',
        $_POST['password'] ?? '',
        $_POST['confirmar'] ?? ''
    );
    // Se sucesso, redirecionar para login com mensagem
    if ($resultado['sucesso']) {
        header('Location: login.php?msg=registo_ok');
        // Adicionar mensagem temporária via sessão
        session_start();
        $_SESSION['flash'] = $resultado['mensagem'];
        exit;
    }
}

include 'includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card" style="max-width:480px;">
        <div class="auth-logo">
            <div class="icon">📝</div>
            <h2>Criar Conta</h2>
            <p style="color:var(--cor-cinza); font-size:0.9rem;">Junte-se à comunidade da Queima das Fitas 2026</p>
        </div>

        <?php if ($resultado && !$resultado['sucesso']): ?>
            <?php showMessage('❌ ' . $resultado['mensagem'], 'error'); ?>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="nome">👤 Nome Completo <span class="obrigatorio">*</span></label>
                <input type="text" id="nome" name="nome" class="form-control"
                       placeholder="O seu nome"
                       value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>"
                       required>
            </div>

            <div class="form-group">
                <label for="email">📧 Email Académico <span class="obrigatorio">*</span></label>
                <input type="email" id="email" name="email" class="form-control"
                       placeholder="nome@fe.up.pt"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       required>
            </div>

            <div class="form-group">
                <label for="password">🔒 Password <span class="obrigatorio">*</span></label>
                <input type="password" id="password" name="password" class="form-control"
                       placeholder="Mínimo 6 caracteres"
                       required minlength="6">
            </div>

            <div class="form-group">
                <label for="confirmar">🔒 Confirmar Password <span class="obrigatorio">*</span></label>
                <input type="password" id="confirmar" name="confirmar" class="form-control"
                       placeholder="Repita a password"
                       required>
            </div>

        <!-- Ficheiro JS externo: validação de passwords em js/registo_form.js -->
        <script src="js/registo_form.js"></script>

            <button type="submit" class="btn btn-primary btn-lg" style="width:100%; justify-content:center; margin-top:0.5rem;">
                📝 Criar Conta
            </button>
        </form>

        <div style="text-align:center; margin-top:1.5rem; padding-top:1.5rem; border-top:1px solid var(--cor-borda);">
            <p style="color:var(--cor-cinza); font-size:0.9rem;">
                Já tem conta?
                <a href="login.php" style="font-weight:600;">Fazer Login</a>
            </p>
        </div>

        <div class="alert alert-info" style="margin-top:1rem; font-size:0.85rem;">
            ℹ️ A conta será criada com perfil de <strong>Estudante</strong>. Para perfil de Administrador, contacte a organização.
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
