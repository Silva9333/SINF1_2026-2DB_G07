<?php
// ============================================================
// login.php - PÁGINA DE LOGIN
// ============================================================
// Permite que utilizadores façam autenticação no sistema.
// Fluxo:
// 1. Utilizador preenche email e password
// 2. Formulário é submetido (POST)
// 3. BLL verifica credenciais
// 4. Se correto: cria sessão e redireciona
// 5. Se errado: mostra mensagem de erro
// ============================================================

require_once 'BusinessLogicLayer.php';

// Se já está autenticado, redirecionar para a página inicial
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$page_title = 'Login';
$current_page = 'login';
$resultado = null;

// Processar o formulário de login quando é submetido (método POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Chamar a função da BLL que trata o login
    $resultado = processLogin($_POST['email'] ?? '', $_POST['password'] ?? '');

    if ($resultado['sucesso']) {
        // Login com sucesso: redirecionar para a página inicial
        header('Location: index.php');
        exit;
    }
    // Se não teve sucesso, o $resultado['mensagem'] será mostrado abaixo
}

// Obter mensagem da URL (ex: "login_required" quando tentou aceder a página protegida)
$msg_url = getMensagemURL();

include 'includes/header.php';
?>

<!-- ===== PÁGINA DE LOGIN ===== -->
<div class="auth-wrapper">
    <div class="auth-card">
        <!-- Logo e título -->
        <div class="auth-logo">
            <div class="icon">🎓</div>
            <h2>Bem-vindo de volta!</h2>
            <p style="color:var(--cor-cinza); font-size:0.9rem;">Queima das Fitas do Porto 2026</p>
        </div>

        <!-- Mensagem de URL (ex: "É preciso fazer login") -->
        <?php if ($msg_url): ?>
            <?php showMessage($msg_url[0], $msg_url[1]); ?>
        <?php endif; ?>

        <!-- Mensagem de erro do login -->
        <?php if ($resultado && !$resultado['sucesso']): ?>
            <?php showMessage('❌ ' . $resultado['mensagem'], 'error'); ?>
        <?php endif; ?>

        <!-- FORMULÁRIO DE LOGIN -->
        <!-- action="" significa que submete para a mesma página -->
        <!-- method="POST" envia os dados de forma segura -->
        <form action="" method="POST">
            <div class="form-group">
                <label for="email">📧 Email <span class="obrigatorio">*</span></label>
                <!-- htmlspecialchars previne XSS ao mostrar o valor submetido -->
                <input type="email" id="email" name="email" class="form-control"
                       placeholder="seu@email.pt"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       required>
            </div>

            <div class="form-group">
                <label for="password">🔒 Password <span class="obrigatorio">*</span></label>
                <input type="password" id="password" name="password" class="form-control"
                       placeholder="A sua password"
                       required>
            </div>

            <button type="submit" class="btn btn-primary btn-lg" style="width:100%; justify-content:center; margin-top:0.5rem;">
                🔑 Entrar
            </button>
        </form>

        <!-- Links para outras páginas -->
        <div style="text-align:center; margin-top:1.5rem; padding-top:1.5rem; border-top:1px solid var(--cor-borda);">
            <p style="color:var(--cor-cinza); font-size:0.9rem;">
                Não tem conta?
                <a href="registo.php" style="font-weight:600;">Criar conta gratuita</a>
            </p>
        </div>

        <!-- Credenciais de teste (para demonstração) -->
        <div style="background:#f8f4f0; border-radius:8px; padding:1rem; margin-top:1rem; font-size:0.85rem;">
            <strong>🧪 Contas de teste:</strong><br>
            <strong>Admin:</strong> admin@queima.pt / password<br>
            <strong>Estudante:</strong> ana@fe.up.pt / password
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
