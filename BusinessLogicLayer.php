<?php
// ============================================================
// BusinessLogicLayer.php - CAMADA DE LÓGICA DE NEGÓCIO (BLL)
// ============================================================
// PRINCÍPIO FUNDAMENTAL:
//   Toda a lógica do sistema vive AQUI.
//   As páginas PHP (.php) devem ser "burras":
//     - Chamam funções da BLL
//     - Fazem echo/print dos resultados
//     - NÃO tomam decisões nem processam dados
//
// Esta camada:
//   1. Gere sessões e autenticação
//   2. Valida dados dos formulários
//   3. Aplica regras de negócio (quem pode fazer o quê)
//   4. Prepara dados formatados para a apresentação
//   5. Chama a DAL para persistência
// ============================================================

require_once 'DataAccessLayer.php';

// Iniciar sessão uma única vez
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==================== AUTENTICAÇÃO ====================

// Verifica se há utilizador autenticado na sessão
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Verifica se o utilizador é administrador
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'administrador';
}

// Verifica se o utilizador é estudante
function isStudent() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'estudante';
}

// Retorna o ID do utilizador da sessão
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Retorna o nome do utilizador da sessão
function getCurrentUserName() {
    return $_SESSION['user_nome'] ?? 'Utilizador';
}

// Redireciona para login se não autenticado
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php?msg=login_required');
        exit;
    }
}

// Redireciona se não for administrador
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: index.php?msg=sem_permissao');
        exit;
    }
}

// ==================== LOGIN / REGISTO / LOGOUT ====================

// Processa tentativa de login — retorna array com sucesso e mensagem
function processLogin($email, $password) {
    if (empty($email) || empty($password)) {
        return ['sucesso' => false, 'mensagem' => 'Preencha todos os campos.'];
    }
    try {
        $dal  = new DAL();
        $user = $dal->getUserByEmail($email);
        // password_verify compara a password com o hash guardado na BD
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['user_nome']  = $user['nome'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['role']       = $user['role_nome'];
            return ['sucesso' => true, 'mensagem' => 'Bem-vindo!'];
        }
        return ['sucesso' => false, 'mensagem' => 'Email ou password incorretos.'];
    } catch (Exception $e) {
        return ['sucesso' => false, 'mensagem' => 'Erro de ligação à base de dados.'];
    }
}

// Termina a sessão e redireciona para a página inicial
function processLogout() {
    session_unset();
    session_destroy();
    header('Location: index.php?msg=logout_ok');
    exit;
}

// Processa registo de novo utilizador
function processRegisto($nome, $email, $password, $confirmar) {
    if (empty($nome) || empty($email) || empty($password)) {
        return ['sucesso' => false, 'mensagem' => 'Preencha todos os campos obrigatórios.'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['sucesso' => false, 'mensagem' => 'Email inválido.'];
    }
    if (strlen($password) < 6) {
        return ['sucesso' => false, 'mensagem' => 'Password com mínimo 6 caracteres.'];
    }
    if ($password !== $confirmar) {
        return ['sucesso' => false, 'mensagem' => 'As passwords não coincidem.'];
    }
    try {
        $dal = new DAL();
        if ($dal->emailExists($email)) {
            return ['sucesso' => false, 'mensagem' => 'Este email já está registado.'];
        }
        $dal->createUser($nome, $email, password_hash($password, PASSWORD_DEFAULT));
        return ['sucesso' => true, 'mensagem' => 'Conta criada! Pode fazer login.'];
    } catch (Exception $e) {
        return ['sucesso' => false, 'mensagem' => 'Erro ao criar conta.'];
    }
}

// ==================== DADOS DA HOMEPAGE ====================
// Funções específicas para a homepage — lógica de "quantos mostrar"
// fica aqui, não na página index.php

// Retorna os N primeiros eventos (para a homepage)
function getEventosDestaque($limite = 6) {
    try {
        $todos = (new DAL())->getAllEventos();
        return array_slice($todos, 0, $limite);
    } catch (Exception $e) { return []; }
}

// Retorna eventos nas próximas 48h (alertas)
function getAlertasProximos() {
    try { return (new DAL())->getEventosProximos48h(); }
    catch (Exception $e) { return []; }
}

// Retorna true se existem alertas (para a view não precisar de empty())
function temAlertas() {
    return count(getAlertasProximos()) > 0;
}

// ==================== EVENTOS ====================

// Retorna todos os eventos, com filtro opcional por tipo
function getEventos($tipo = null) {
    try {
        $dal = new DAL();
        return $tipo ? $dal->getEventosByTipo($tipo) : $dal->getAllEventos();
    } catch (Exception $e) { return []; }
}

// Retorna os dados completos de um evento (com artistas, comentários, etc.)
function getEvento($id) {
    try {
        $dal    = new DAL();
        $evento = $dal->getEventoById($id);
        if (!$evento) return null;
        $evento['artistas']    = $dal->getArtistasByEvento($id);
        $evento['comentarios'] = $dal->getComentariosEvento($id);
        if (isLoggedIn()) {
            $evento['na_agenda']  = $dal->isEventoInAgenda(getCurrentUserId(), $id);
            $evento['meu_rating'] = $dal->getRatingEvento(getCurrentUserId(), $id);
        } else {
            $evento['na_agenda']  = false;
            $evento['meu_rating'] = null;
        }
        return $evento;
    } catch (Exception $e) { return null; }
}

// Retorna true se existe um evento com este ID
function eventoExiste($id) {
    return getEvento($id) !== null;
}

// Cria um novo evento (apenas admin)
function processCreateEvento($dados, $artistas_ids = []) {
    requireAdmin();
    if (empty($dados['nome']) || empty($dados['data_hora']) || empty($dados['local']) || empty($dados['tipo'])) {
        return ['sucesso' => false, 'mensagem' => 'Preencha todos os campos obrigatórios.'];
    }
    try {
        $dal        = new DAL();
        $barraca_id = !empty($dados['barraca_id']) ? (int)$dados['barraca_id'] : null;
        $evento_id  = $dal->createEvento(
            trim($dados['nome']),
            trim($dados['descricao'] ?? ''),
            $dados['data_hora'],
            trim($dados['local']),
            $dados['tipo'],
            $barraca_id
        );
        foreach ($artistas_ids as $aid) {
            if ((int)$aid > 0) $dal->addArtistaToEvento($evento_id, (int)$aid);
        }
        return ['sucesso' => true, 'mensagem' => 'Evento criado com sucesso!'];
    } catch (Exception $e) {
        return ['sucesso' => false, 'mensagem' => 'Erro ao criar evento.'];
    }
}

// Atualiza um evento existente (apenas admin)
function processUpdateEvento($id, $dados, $artistas_ids = []) {
    requireAdmin();
    if (empty($dados['nome']) || empty($dados['data_hora']) || empty($dados['local']) || empty($dados['tipo'])) {
        return ['sucesso' => false, 'mensagem' => 'Preencha todos os campos obrigatórios.'];
    }
    try {
        $dal        = new DAL();
        $barraca_id = !empty($dados['barraca_id']) ? (int)$dados['barraca_id'] : null;
        $dal->updateEvento($id, trim($dados['nome']), trim($dados['descricao'] ?? ''),
                           $dados['data_hora'], trim($dados['local']), $dados['tipo'], $barraca_id);
        $dal->removeAllArtistasFromEvento($id);
        foreach ($artistas_ids as $aid) {
            if ((int)$aid > 0) $dal->addArtistaToEvento($id, (int)$aid);
        }
        return ['sucesso' => true, 'mensagem' => 'Evento atualizado!'];
    } catch (Exception $e) {
        return ['sucesso' => false, 'mensagem' => 'Erro ao atualizar evento.'];
    }
}

// Elimina um evento (apenas admin)
function processDeleteEvento($id) {
    requireAdmin();
    try { (new DAL())->deleteEvento($id); return ['sucesso' => true, 'mensagem' => 'Evento eliminado.']; }
    catch (Exception $e) { return ['sucesso' => false, 'mensagem' => 'Erro ao eliminar.']; }
}

// ==================== ARTISTAS ====================

function getArtistas() {
    try { return (new DAL())->getAllArtistas(); } catch (Exception $e) { return []; }
}

function getArtista($id) {
    try {
        $dal     = new DAL();
        $artista = $dal->getArtistaById($id);
        if ($artista) $artista['eventos'] = $dal->getEventosByArtista($id);
        return $artista;
    } catch (Exception $e) { return null; }
}

function processCreateArtista($dados) {
    requireAdmin();
    if (empty($dados['nome'])) return ['sucesso' => false, 'mensagem' => 'O nome é obrigatório.'];
    try {
        (new DAL())->createArtista(trim($dados['nome']), trim($dados['genero'] ?? ''),
                                    trim($dados['pais'] ?? ''), trim($dados['biografia'] ?? ''));
        return ['sucesso' => true, 'mensagem' => 'Artista adicionado!'];
    } catch (Exception $e) { return ['sucesso' => false, 'mensagem' => 'Erro ao criar artista.']; }
}

function processUpdateArtista($id, $dados) {
    requireAdmin();
    if (empty($dados['nome'])) return ['sucesso' => false, 'mensagem' => 'O nome é obrigatório.'];
    try {
        (new DAL())->updateArtista($id, trim($dados['nome']), trim($dados['genero'] ?? ''),
                                    trim($dados['pais'] ?? ''), trim($dados['biografia'] ?? ''));
        return ['sucesso' => true, 'mensagem' => 'Artista atualizado!'];
    } catch (Exception $e) { return ['sucesso' => false, 'mensagem' => 'Erro ao atualizar.']; }
}

function processDeleteArtista($id) {
    requireAdmin();
    try { (new DAL())->deleteArtista($id); return ['sucesso' => true, 'mensagem' => 'Artista eliminado.']; }
    catch (Exception $e) { return ['sucesso' => false, 'mensagem' => 'Erro ao eliminar.']; }
}

// ==================== FACULDADES ====================

function getFaculdades() {
    try { return (new DAL())->getAllFaculdades(); } catch (Exception $e) { return []; }
}

function getFaculdade($id) {
    try { return (new DAL())->getFaculdadeById($id); } catch (Exception $e) { return null; }
}

function processCreateFaculdade($dados) {
    requireAdmin();
    if (empty($dados['nome']) || empty($dados['sigla']))
        return ['sucesso' => false, 'mensagem' => 'Nome e sigla são obrigatórios.'];
    try {
        (new DAL())->createFaculdade(trim($dados['nome']), strtoupper(trim($dados['sigla'])),
                                      trim($dados['descricao'] ?? ''), $dados['cor'] ?? '#8B0000');
        return ['sucesso' => true, 'mensagem' => 'Faculdade criada!'];
    } catch (Exception $e) { return ['sucesso' => false, 'mensagem' => 'Erro. Sigla pode já existir.']; }
}

function processUpdateFaculdade($id, $dados) {
    requireAdmin();
    if (empty($dados['nome']) || empty($dados['sigla']))
        return ['sucesso' => false, 'mensagem' => 'Nome e sigla são obrigatórios.'];
    try {
        (new DAL())->updateFaculdade($id, trim($dados['nome']), strtoupper(trim($dados['sigla'])),
                                      trim($dados['descricao'] ?? ''), $dados['cor'] ?? '#8B0000');
        return ['sucesso' => true, 'mensagem' => 'Faculdade atualizada!'];
    } catch (Exception $e) { return ['sucesso' => false, 'mensagem' => 'Erro ao atualizar.']; }
}

function processDeleteFaculdade($id) {
    requireAdmin();
    try { (new DAL())->deleteFaculdade($id); return ['sucesso' => true, 'mensagem' => 'Faculdade eliminada.']; }
    catch (Exception $e) { return ['sucesso' => false, 'mensagem' => 'Não é possível eliminar (tem barracas).']; }
}

// ==================== BARRACAS ====================

function getBarracas() {
    try { return (new DAL())->getAllBarracas(); } catch (Exception $e) { return []; }
}

function getBarraca($id) {
    try {
        $dal     = new DAL();
        $barraca = $dal->getBarracaById($id);
        if (!$barraca) return null;
        $barraca['comentarios'] = $dal->getComentariosBarraca($id);
        $barraca['meu_rating']  = isLoggedIn() ? $dal->getRatingBarraca(getCurrentUserId(), $id) : null;
        return $barraca;
    } catch (Exception $e) { return null; }
}

// Determina se uma barraca está aberta neste momento
// Esta lógica estava na página barracas.php — passou para aqui
function barracaEstaAberta($hora_abertura, $hora_fecho) {
    $agora      = date('H:i:s');
    $abertura   = substr($hora_abertura, 0, 8);
    $fecho      = substr($hora_fecho, 0, 8);
    // Barracas de madrugada: fecho < abertura (ex: 18:00–04:00)
    if ($fecho < $abertura) {
        return ($agora >= $abertura || $agora <= $fecho);
    }
    return ($agora >= $abertura && $agora <= $fecho);
}

function processCreateBarraca($dados) {
    requireAdmin();
    if (empty($dados['nome']) || empty($dados['faculdade_id']))
        return ['sucesso' => false, 'mensagem' => 'Nome e faculdade são obrigatórios.'];
    try {
        (new DAL())->createBarraca(trim($dados['nome']), (int)$dados['faculdade_id'],
                                    trim($dados['localizacao'] ?? ''),
                                    $dados['hora_abertura'] ?? '18:00',
                                    $dados['hora_fecho']    ?? '04:00',
                                    trim($dados['descricao'] ?? ''));
        return ['sucesso' => true, 'mensagem' => 'Barraca criada!'];
    } catch (Exception $e) { return ['sucesso' => false, 'mensagem' => 'Erro ao criar barraca.']; }
}

function processUpdateBarraca($id, $dados) {
    requireAdmin();
    if (empty($dados['nome']) || empty($dados['faculdade_id']))
        return ['sucesso' => false, 'mensagem' => 'Nome e faculdade são obrigatórios.'];
    try {
        (new DAL())->updateBarraca($id, trim($dados['nome']), (int)$dados['faculdade_id'],
                                    trim($dados['localizacao'] ?? ''),
                                    $dados['hora_abertura'] ?? '18:00',
                                    $dados['hora_fecho']    ?? '04:00',
                                    trim($dados['descricao'] ?? ''));
        return ['sucesso' => true, 'mensagem' => 'Barraca atualizada!'];
    } catch (Exception $e) { return ['sucesso' => false, 'mensagem' => 'Erro ao atualizar.']; }
}

function processDeleteBarraca($id) {
    requireAdmin();
    try { (new DAL())->deleteBarraca($id); return ['sucesso' => true, 'mensagem' => 'Barraca eliminada.']; }
    catch (Exception $e) { return ['sucesso' => false, 'mensagem' => 'Erro ao eliminar.']; }
}

// ==================== RATINGS ====================

// Processa avaliação de evento — regra: só estudantes
function processRatingEvento($evento_id, $nota, $comentario) {
    if (!isLoggedIn())  return ['sucesso' => false, 'mensagem' => 'Login necessário para avaliar.'];
    if (!isStudent())   return ['sucesso' => false, 'mensagem' => 'Só estudantes podem avaliar.'];
    if ($nota < 1 || $nota > 5) return ['sucesso' => false, 'mensagem' => 'Nota inválida (1 a 5).'];
    try {
        (new DAL())->saveRatingEvento(getCurrentUserId(), $evento_id, (int)$nota, trim($comentario ?? ''));
        return ['sucesso' => true, 'mensagem' => 'Avaliação guardada!'];
    } catch (Exception $e) { return ['sucesso' => false, 'mensagem' => 'Erro ao guardar avaliação.']; }
}

// Processa avaliação de barraca — regra: só estudantes
function processRatingBarraca($barraca_id, $nota, $comentario) {
    if (!isLoggedIn())  return ['sucesso' => false, 'mensagem' => 'Login necessário para avaliar.'];
    if (!isStudent())   return ['sucesso' => false, 'mensagem' => 'Só estudantes podem avaliar.'];
    if ($nota < 1 || $nota > 5) return ['sucesso' => false, 'mensagem' => 'Nota inválida (1 a 5).'];
    try {
        (new DAL())->saveRatingBarraca(getCurrentUserId(), $barraca_id, (int)$nota, trim($comentario ?? ''));
        return ['sucesso' => true, 'mensagem' => 'Avaliação guardada!'];
    } catch (Exception $e) { return ['sucesso' => false, 'mensagem' => 'Erro ao guardar avaliação.']; }
}

// ==================== AGENDA PESSOAL ====================

function getMinhaAgenda() {
    if (!isLoggedIn()) return [];
    try { return (new DAL())->getAgendaByUser(getCurrentUserId()); }
    catch (Exception $e) { return []; }
}

// Separa a agenda em eventos futuros e passados (lógica que estava na agenda.php)
function separarAgendaPorData($agenda) {
    $agora    = new DateTime();
    $futuros  = [];
    $passados = [];
    foreach ($agenda as $ev) {
        $data = new DateTime($ev['data_hora']);
        if ($data >= $agora) $futuros[]  = $ev;
        else                 $passados[] = $ev;
    }
    return ['futuros' => $futuros, 'passados' => $passados];
}

function processAddToAgenda($evento_id) {
    if (!isLoggedIn()) return ['sucesso' => false, 'mensagem' => 'Login necessário.'];
    try {
        (new DAL())->addToAgenda(getCurrentUserId(), $evento_id);
        return ['sucesso' => true, 'mensagem' => 'Evento adicionado à agenda!'];
    } catch (Exception $e) { return ['sucesso' => false, 'mensagem' => 'Erro.']; }
}

function processRemoveFromAgenda($evento_id) {
    if (!isLoggedIn()) return ['sucesso' => false, 'mensagem' => 'Login necessário.'];
    try {
        (new DAL())->removeFromAgenda(getCurrentUserId(), $evento_id);
        return ['sucesso' => true, 'mensagem' => 'Evento removido da agenda.'];
    } catch (Exception $e) { return ['sucesso' => false, 'mensagem' => 'Erro.']; }
}

// ==================== UTILIZADORES / PERFIL ====================

function getUserAtual() {
    if (!isLoggedIn()) return null;
    try { return (new DAL())->getUserById(getCurrentUserId()); }
    catch (Exception $e) { return null; }
}

function processUpdatePerfil($id, $nome, $email) {
    if (empty($nome) || empty($email))
        return ['sucesso' => false, 'mensagem' => 'Nome e email são obrigatórios.'];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        return ['sucesso' => false, 'mensagem' => 'Email inválido.'];
    try {
        (new DAL())->updateUser($id, $nome, $email);
        $_SESSION['user_nome']  = $nome;
        $_SESSION['user_email'] = $email;
        return ['sucesso' => true, 'mensagem' => 'Perfil atualizado!'];
    } catch (Exception $e) { return ['sucesso' => false, 'mensagem' => 'Erro ao atualizar perfil.']; }
}

// ==================== DASHBOARD (ADMIN) ====================

function getEstatisticasDashboard() {
    try { return (new DAL())->getEstatisticas(); }
    catch (Exception $e) { return []; }
}

// ==================== UTILITÁRIOS DE APRESENTAÇÃO ====================
// Estas funções formatam dados para a camada de apresentação.
// São "helpers" da BLL que as páginas podem usar.

// Converte tipo de evento para etiqueta legível
function tipoEventoLabel($tipo) {
    $labels = [
        'cerimonia' => 'Cerimónia Académica',
        'concerto'  => 'Concerto',
        'atividade' => 'Atividade Cultural',
    ];
    return $labels[$tipo] ?? $tipo;
}

// Formata data/hora para português
function formatarDataHora($datetime) {
    if (!$datetime) return 'Data não definida';
    $dt   = new DateTime($datetime);
    $dias = ['Sunday'=>'Domingo','Monday'=>'Segunda-feira','Tuesday'=>'Terça-feira',
             'Wednesday'=>'Quarta-feira','Thursday'=>'Quinta-feira','Friday'=>'Sexta-feira','Saturday'=>'Sábado'];
    $meses= ['January'=>'Janeiro','February'=>'Fevereiro','March'=>'Março','April'=>'Abril',
             'May'=>'Maio','June'=>'Junho','July'=>'Julho','August'=>'Agosto',
             'September'=>'Setembro','October'=>'Outubro','November'=>'Novembro','December'=>'Dezembro'];
    return $dias[$dt->format('l')] . ', ' . $dt->format('d') . ' de '
         . $meses[$dt->format('F')] . ' de ' . $dt->format('Y') . ' às ' . $dt->format('H:i');
}

// Gera HTML das estrelas de avaliação
function renderStars($media, $total = null) {
    $cheias = round($media);
    $html   = '<span class="stars">' . str_repeat('★', $cheias) . str_repeat('☆', 5 - $cheias) . '</span>';
    if ($total !== null) {
        $html .= ' <small class="rating-count">(' . $total . ' avaliações)</small>';
    }
    return $html;
}

// Mostra uma caixa de alerta/sucesso formatada
function showMessage($msg, $tipo = 'info') {
    echo "<div class='alert alert-$tipo'>" . htmlspecialchars($msg) . "</div>";
}

// Lê mensagens de feedback da query string (após redirect)
function getMensagemURL() {
    $msgs = [
        'login_required' => ['Tem de fazer login para aceder a esta página.', 'warning'],
        'sem_permissao'  => ['Não tem permissão para aceder a esta página.', 'error'],
        'logout_ok'      => ['Sessão terminada com sucesso. Até breve!', 'success'],
    ];
    if (isset($_GET['msg']) && isset($msgs[$_GET['msg']])) {
        return $msgs[$_GET['msg']];
    }
    return null;
}
