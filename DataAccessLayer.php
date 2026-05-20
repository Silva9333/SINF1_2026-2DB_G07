<?php
// ============================================================
// DataAccessLayer.php - CAMADA DE ACESSO A DADOS (DAL)
// ============================================================
// Esta camada é responsável APENAS por comunicar com a base
// de dados. Não contém lógica de negócio nem HTML.
//
// Princípio: Separação de responsabilidades.
// A DAL só sabe "como" guardar/obter dados, não "porquê".
//
// Usa PDO (PHP Data Objects) para maior segurança:
// - Prepared statements previnem SQL Injection
// - Funciona com vários tipos de bases de dados
// ============================================================

class DAL {
    // Propriedade privada: só esta classe acede à ligação
    private $conn;

    // Construtor: cria a ligação à base de dados quando a classe é instanciada
    function __construct() {
        // PDO com charset utf8mb4 para suportar caracteres especiais (acentos, emojis)
        $this->conn = new PDO(
            'mysql:host=localhost;dbname=queima_fitas;charset=utf8mb4',
            'root',   // utilizador MySQL (padrão do XAMPP)
            ''        // password MySQL (vazia no XAMPP por defeito)
        );
        // Configura o PDO para lançar exceções em caso de erro
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // Retorna resultados como arrays associativos por defeito
        $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    // ==================== UTILIZADORES ====================

    // Obtém um utilizador pelo email (usado no login)
    function getUserByEmail($email) {
        $stmt = $this->conn->prepare("SELECT u.*, r.nome as role_nome FROM users u JOIN roles r ON u.role_id = r.id WHERE u.email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(); // Retorna uma linha ou false
    }

    // Obtém um utilizador pelo ID (usado no perfil)
    function getUserById($id) {
        $stmt = $this->conn->prepare("SELECT u.*, r.nome as role_nome FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Verifica se já existe um email registado (para o registo)
    function emailExists($email) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $row = $stmt->fetch();
        return $row['total'] > 0;
    }

    // Cria um novo utilizador (registo)
    function createUser($nome, $email, $passwordHash) {
        $stmt = $this->conn->prepare("INSERT INTO users (nome, email, password, role_id) VALUES (:nome, :email, :password, 2)");
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $passwordHash);
        return $stmt->execute();
    }

    // Atualiza o perfil do utilizador
    function updateUser($id, $nome, $email) {
        $stmt = $this->conn->prepare("UPDATE users SET nome = :nome, email = :email WHERE id = :id");
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // ==================== EVENTOS ====================

    // Obtém todos os eventos, ordenados por data (mais próximos primeiro)
    function getAllEventos() {
        $stmt = $this->conn->query("
            SELECT e.*, b.nome as barraca_nome,
                   COALESCE(AVG(r.nota), 0) as media_rating,
                   COUNT(r.id) as total_ratings
            FROM eventos e
            LEFT JOIN barracas b ON e.barraca_id = b.id
            LEFT JOIN ratings_eventos r ON e.id = r.evento_id
            GROUP BY e.id
            ORDER BY e.data_hora ASC
        ");
        return $stmt->fetchAll();
    }

    // Obtém eventos filtrados por tipo
    function getEventosByTipo($tipo) {
        $stmt = $this->conn->prepare("
            SELECT e.*, b.nome as barraca_nome,
                   COALESCE(AVG(r.nota), 0) as media_rating,
                   COUNT(r.id) as total_ratings
            FROM eventos e
            LEFT JOIN barracas b ON e.barraca_id = b.id
            LEFT JOIN ratings_eventos r ON e.id = r.evento_id
            WHERE e.tipo = :tipo
            GROUP BY e.id
            ORDER BY e.data_hora ASC
        ");
        $stmt->bindParam(':tipo', $tipo);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Obtém um evento específico pelo ID, com artistas associados
    function getEventoById($id) {
        $stmt = $this->conn->prepare("
            SELECT e.*, b.nome as barraca_nome,
                   COALESCE(AVG(r.nota), 0) as media_rating,
                   COUNT(r.id) as total_ratings
            FROM eventos e
            LEFT JOIN barracas b ON e.barraca_id = b.id
            LEFT JOIN ratings_eventos r ON e.id = r.evento_id
            WHERE e.id = :id
            GROUP BY e.id
        ");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Obtém os artistas de um evento específico
    function getArtistasByEvento($evento_id) {
        $stmt = $this->conn->prepare("
            SELECT a.* FROM artistas a
            JOIN evento_artista ea ON a.id = ea.artista_id
            WHERE ea.evento_id = :evento_id
        ");
        $stmt->bindParam(':evento_id', $evento_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Eventos que acontecem nas próximas 48 horas (alertas)
    function getEventosProximos48h() {
        $stmt = $this->conn->query("
            SELECT * FROM eventos
            WHERE data_hora BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 48 HOUR)
            ORDER BY data_hora ASC
        ");
        return $stmt->fetchAll();
    }

    // Cria um novo evento
    function createEvento($nome, $descricao, $data_hora, $local, $tipo, $barraca_id) {
        $stmt = $this->conn->prepare("INSERT INTO eventos (nome, descricao, data_hora, local, tipo, barraca_id) VALUES (:nome, :descricao, :data_hora, :local, :tipo, :barraca_id)");
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':descricao', $descricao);
        $stmt->bindParam(':data_hora', $data_hora);
        $stmt->bindParam(':local', $local);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->bindParam(':barraca_id', $barraca_id, PDO::PARAM_INT);
        $stmt->execute();
        return $this->conn->lastInsertId(); // Retorna o ID do novo evento
    }

    // Atualiza um evento existente
    function updateEvento($id, $nome, $descricao, $data_hora, $local, $tipo, $barraca_id) {
        $stmt = $this->conn->prepare("UPDATE eventos SET nome=:nome, descricao=:descricao, data_hora=:data_hora, local=:local, tipo=:tipo, barraca_id=:barraca_id WHERE id=:id");
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':descricao', $descricao);
        $stmt->bindParam(':data_hora', $data_hora);
        $stmt->bindParam(':local', $local);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->bindParam(':barraca_id', $barraca_id, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Elimina um evento
    function deleteEvento($id) {
        $stmt = $this->conn->prepare("DELETE FROM eventos WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Associa um artista a um evento
    function addArtistaToEvento($evento_id, $artista_id) {
        $stmt = $this->conn->prepare("INSERT IGNORE INTO evento_artista (evento_id, artista_id) VALUES (:evento_id, :artista_id)");
        $stmt->bindParam(':evento_id', $evento_id, PDO::PARAM_INT);
        $stmt->bindParam(':artista_id', $artista_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Remove todos os artistas de um evento (antes de atualizar)
    function removeAllArtistasFromEvento($evento_id) {
        $stmt = $this->conn->prepare("DELETE FROM evento_artista WHERE evento_id = :evento_id");
        $stmt->bindParam(':evento_id', $evento_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // ==================== ARTISTAS ====================

    function getAllArtistas() {
        return $this->conn->query("SELECT * FROM artistas ORDER BY nome")->fetchAll();
    }

    function getArtistaById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM artistas WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Obtém os eventos de um artista (para a página de detalhe do artista)
    function getEventosByArtista($artista_id) {
        $stmt = $this->conn->prepare("
            SELECT e.* FROM eventos e
            JOIN evento_artista ea ON e.id = ea.evento_id
            WHERE ea.artista_id = :artista_id
            ORDER BY e.data_hora ASC
        ");
        $stmt->bindParam(':artista_id', $artista_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    function createArtista($nome, $genero, $pais, $biografia) {
        $stmt = $this->conn->prepare("INSERT INTO artistas (nome, genero, pais, biografia) VALUES (:nome, :genero, :pais, :biografia)");
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':genero', $genero);
        $stmt->bindParam(':pais', $pais);
        $stmt->bindParam(':biografia', $biografia);
        return $stmt->execute();
    }

    function updateArtista($id, $nome, $genero, $pais, $biografia) {
        $stmt = $this->conn->prepare("UPDATE artistas SET nome=:nome, genero=:genero, pais=:pais, biografia=:biografia WHERE id=:id");
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':genero', $genero);
        $stmt->bindParam(':pais', $pais);
        $stmt->bindParam(':biografia', $biografia);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    function deleteArtista($id) {
        $stmt = $this->conn->prepare("DELETE FROM artistas WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // ==================== FACULDADES ====================

    function getAllFaculdades() {
        return $this->conn->query("SELECT * FROM faculdades ORDER BY sigla")->fetchAll();
    }

    function getFaculdadeById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM faculdades WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    function createFaculdade($nome, $sigla, $descricao, $cor) {
        $stmt = $this->conn->prepare("INSERT INTO faculdades (nome, sigla, descricao, cor) VALUES (:nome, :sigla, :descricao, :cor)");
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':sigla', $sigla);
        $stmt->bindParam(':descricao', $descricao);
        $stmt->bindParam(':cor', $cor);
        return $stmt->execute();
    }

    function updateFaculdade($id, $nome, $sigla, $descricao, $cor) {
        $stmt = $this->conn->prepare("UPDATE faculdades SET nome=:nome, sigla=:sigla, descricao=:descricao, cor=:cor WHERE id=:id");
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':sigla', $sigla);
        $stmt->bindParam(':descricao', $descricao);
        $stmt->bindParam(':cor', $cor);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    function deleteFaculdade($id) {
        $stmt = $this->conn->prepare("DELETE FROM faculdades WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // ==================== BARRACAS ====================

    // Obtém todas as barracas com o nome da faculdade e a média de avaliações
    function getAllBarracas() {
        return $this->conn->query("
            SELECT b.*, f.nome as faculdade_nome, f.sigla as faculdade_sigla, f.cor as faculdade_cor,
                   COALESCE(AVG(r.nota), 0) as media_rating,
                   COUNT(r.id) as total_ratings
            FROM barracas b
            JOIN faculdades f ON b.faculdade_id = f.id
            LEFT JOIN ratings_barracas r ON b.id = r.barraca_id
            GROUP BY b.id
            ORDER BY b.nome
        ")->fetchAll();
    }

    function getBarracaById($id) {
        $stmt = $this->conn->prepare("
            SELECT b.*, f.nome as faculdade_nome, f.sigla as faculdade_sigla, f.cor as faculdade_cor,
                   COALESCE(AVG(r.nota), 0) as media_rating,
                   COUNT(r.id) as total_ratings
            FROM barracas b
            JOIN faculdades f ON b.faculdade_id = f.id
            LEFT JOIN ratings_barracas r ON b.id = r.barraca_id
            WHERE b.id = :id
            GROUP BY b.id
        ");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    function createBarraca($nome, $faculdade_id, $localizacao, $hora_abertura, $hora_fecho, $descricao) {
        $stmt = $this->conn->prepare("INSERT INTO barracas (nome, faculdade_id, localizacao, hora_abertura, hora_fecho, descricao) VALUES (:nome, :faculdade_id, :localizacao, :hora_abertura, :hora_fecho, :descricao)");
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':faculdade_id', $faculdade_id, PDO::PARAM_INT);
        $stmt->bindParam(':localizacao', $localizacao);
        $stmt->bindParam(':hora_abertura', $hora_abertura);
        $stmt->bindParam(':hora_fecho', $hora_fecho);
        $stmt->bindParam(':descricao', $descricao);
        return $stmt->execute();
    }

    function updateBarraca($id, $nome, $faculdade_id, $localizacao, $hora_abertura, $hora_fecho, $descricao) {
        $stmt = $this->conn->prepare("UPDATE barracas SET nome=:nome, faculdade_id=:faculdade_id, localizacao=:localizacao, hora_abertura=:hora_abertura, hora_fecho=:hora_fecho, descricao=:descricao WHERE id=:id");
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':faculdade_id', $faculdade_id, PDO::PARAM_INT);
        $stmt->bindParam(':localizacao', $localizacao);
        $stmt->bindParam(':hora_abertura', $hora_abertura);
        $stmt->bindParam(':hora_fecho', $hora_fecho);
        $stmt->bindParam(':descricao', $descricao);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    function deleteBarraca($id) {
        $stmt = $this->conn->prepare("DELETE FROM barracas WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // ==================== RATINGS EVENTOS ====================

    // Obtém a avaliação de um utilizador a um evento específico
    function getRatingEvento($user_id, $evento_id) {
        $stmt = $this->conn->prepare("SELECT * FROM ratings_eventos WHERE user_id = :user_id AND evento_id = :evento_id");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':evento_id', $evento_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Guarda (ou atualiza) a avaliação de um utilizador a um evento
    function saveRatingEvento($user_id, $evento_id, $nota, $comentario) {
        // INSERT OR UPDATE: se já existe, atualiza (ON DUPLICATE KEY UPDATE)
        $stmt = $this->conn->prepare("
            INSERT INTO ratings_eventos (user_id, evento_id, nota, comentario)
            VALUES (:user_id, :evento_id, :nota, :comentario)
            ON DUPLICATE KEY UPDATE nota = :nota2, comentario = :comentario2
        ");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':evento_id', $evento_id, PDO::PARAM_INT);
        $stmt->bindParam(':nota', $nota, PDO::PARAM_INT);
        $stmt->bindParam(':comentario', $comentario);
        $stmt->bindParam(':nota2', $nota, PDO::PARAM_INT);
        $stmt->bindParam(':comentario2', $comentario);
        return $stmt->execute();
    }

    // Obtém todos os comentários de um evento
    function getComentariosEvento($evento_id) {
        $stmt = $this->conn->prepare("
            SELECT r.*, u.nome as user_nome FROM ratings_eventos r
            JOIN users u ON r.user_id = u.id
            WHERE r.evento_id = :evento_id AND r.comentario IS NOT NULL AND r.comentario != ''
            ORDER BY r.created_at DESC
        ");
        $stmt->bindParam(':evento_id', $evento_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ==================== RATINGS BARRACAS ====================

    function getRatingBarraca($user_id, $barraca_id) {
        $stmt = $this->conn->prepare("SELECT * FROM ratings_barracas WHERE user_id = :user_id AND barraca_id = :barraca_id");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':barraca_id', $barraca_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    function saveRatingBarraca($user_id, $barraca_id, $nota, $comentario) {
        $stmt = $this->conn->prepare("
            INSERT INTO ratings_barracas (user_id, barraca_id, nota, comentario)
            VALUES (:user_id, :barraca_id, :nota, :comentario)
            ON DUPLICATE KEY UPDATE nota = :nota2, comentario = :comentario2
        ");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':barraca_id', $barraca_id, PDO::PARAM_INT);
        $stmt->bindParam(':nota', $nota, PDO::PARAM_INT);
        $stmt->bindParam(':comentario', $comentario);
        $stmt->bindParam(':nota2', $nota, PDO::PARAM_INT);
        $stmt->bindParam(':comentario2', $comentario);
        return $stmt->execute();
    }

    function getComentariosBarraca($barraca_id) {
        $stmt = $this->conn->prepare("
            SELECT r.*, u.nome as user_nome FROM ratings_barracas r
            JOIN users u ON r.user_id = u.id
            WHERE r.barraca_id = :barraca_id AND r.comentario IS NOT NULL AND r.comentario != ''
            ORDER BY r.created_at DESC
        ");
        $stmt->bindParam(':barraca_id', $barraca_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ==================== AGENDA PESSOAL ====================

    // Obtém todos os eventos na agenda de um utilizador
    function getAgendaByUser($user_id) {
        $stmt = $this->conn->prepare("
            SELECT e.*, ap.added_at,
                   COALESCE(AVG(r.nota), 0) as media_rating
            FROM agenda_pessoal ap
            JOIN eventos e ON ap.evento_id = e.id
            LEFT JOIN ratings_eventos r ON e.id = r.evento_id
            WHERE ap.user_id = :user_id
            GROUP BY e.id, ap.added_at
            ORDER BY e.data_hora ASC
        ");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Verifica se um evento já está na agenda do utilizador
    function isEventoInAgenda($user_id, $evento_id) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM agenda_pessoal WHERE user_id = :user_id AND evento_id = :evento_id");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':evento_id', $evento_id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();
        return $row['total'] > 0;
    }

    // Adiciona um evento à agenda
    function addToAgenda($user_id, $evento_id) {
        $stmt = $this->conn->prepare("INSERT IGNORE INTO agenda_pessoal (user_id, evento_id) VALUES (:user_id, :evento_id)");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':evento_id', $evento_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Remove um evento da agenda
    function removeFromAgenda($user_id, $evento_id) {
        $stmt = $this->conn->prepare("DELETE FROM agenda_pessoal WHERE user_id = :user_id AND evento_id = :evento_id");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':evento_id', $evento_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // ==================== DASHBOARD / ESTATÍSTICAS ====================

    // Estatísticas gerais para o painel de administração
    function getEstatisticas() {
        $stats = [];
        $stats['total_eventos']   = $this->conn->query("SELECT COUNT(*) as n FROM eventos")->fetch()['n'];
        $stats['total_artistas']  = $this->conn->query("SELECT COUNT(*) as n FROM artistas")->fetch()['n'];
        $stats['total_barracas']  = $this->conn->query("SELECT COUNT(*) as n FROM barracas")->fetch()['n'];
        $stats['total_users']     = $this->conn->query("SELECT COUNT(*) as n FROM users")->fetch()['n'];
        $stats['total_ratings']   = $this->conn->query("SELECT COUNT(*) as n FROM ratings_eventos")->fetch()['n'];
        // Evento mais popular (mais avaliações)
        $stats['evento_popular']  = $this->conn->query("
            SELECT e.nome, COUNT(r.id) as total FROM eventos e
            LEFT JOIN ratings_eventos r ON e.id = r.evento_id
            GROUP BY e.id ORDER BY total DESC LIMIT 1
        ")->fetch();
        // Barraca melhor avaliada
        $stats['barraca_top']     = $this->conn->query("
            SELECT b.nome, AVG(r.nota) as media FROM barracas b
            LEFT JOIN ratings_barracas r ON b.id = r.barraca_id
            GROUP BY b.id HAVING media IS NOT NULL ORDER BY media DESC LIMIT 1
        ")->fetch();
        return $stats;
    }
}
?>
