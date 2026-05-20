-- ============================================================
-- QUEIMA DAS FITAS DO PORTO 2026
-- Script SQL - Criação da Base de Dados e Dados de Teste
-- ============================================================
-- Este ficheiro cria todas as tabelas e insere dados reais
-- da Queima das Fitas do Porto 2026.
-- Para usar: importar no phpMyAdmin ou correr no MySQL.
-- ============================================================

-- Criar a base de dados (se não existir) e selecioná-la
CREATE DATABASE IF NOT EXISTS queima_fitas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE queima_fitas;

-- ============================================================
-- TABELA: roles
-- Guarda os perfis de utilizador: Administrador e Estudante
-- ============================================================
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL UNIQUE  -- ex: 'administrador', 'estudante'
);

-- ============================================================
-- TABELA: users
-- Utilizadores do sistema (estudantes e administradores)
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,       -- guardada com password_hash()
    role_id INT NOT NULL DEFAULT 2,       -- por defeito é estudante (role 2)
    foto VARCHAR(255) DEFAULT NULL,       -- caminho opcional para foto de perfil
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

-- ============================================================
-- TABELA: faculdades
-- Faculdades da Universidade do Porto participantes
-- ============================================================
CREATE TABLE IF NOT EXISTS faculdades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    sigla VARCHAR(20) NOT NULL UNIQUE,    -- ex: FEUP, FDUP, ICBAS
    descricao TEXT,
    cor VARCHAR(7) DEFAULT '#8B0000'      -- cor representativa em HEX (#RRGGBB)
);

-- ============================================================
-- TABELA: barracas
-- Tendas/barracas de cada faculdade no recinto
-- ============================================================
CREATE TABLE IF NOT EXISTS barracas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    faculdade_id INT NOT NULL,
    localizacao VARCHAR(200),             -- localização dentro do recinto
    hora_abertura TIME,
    hora_fecho TIME,
    descricao TEXT,
    FOREIGN KEY (faculdade_id) REFERENCES faculdades(id) ON DELETE CASCADE
);

-- ============================================================
-- TABELA: artistas
-- Artistas que atuam nos concertos da Queima
-- ============================================================
CREATE TABLE IF NOT EXISTS artistas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    genero VARCHAR(100),                  -- género musical (Pop, Rock, etc.)
    pais VARCHAR(100),
    biografia TEXT,
    foto VARCHAR(255) DEFAULT NULL        -- nome do ficheiro de imagem (opcional)
);

-- ============================================================
-- TABELA: eventos
-- Todos os eventos do festival (cerimónias, concertos, atividades)
-- ============================================================
CREATE TABLE IF NOT EXISTS eventos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(200) NOT NULL,
    descricao TEXT,
    data_hora DATETIME NOT NULL,
    local VARCHAR(200),
    tipo ENUM('cerimonia','concerto','atividade') NOT NULL,
    barraca_id INT DEFAULT NULL,          -- associado a uma barraca (opcional)
    imagem VARCHAR(255) DEFAULT NULL,     -- imagem do evento (opcional)
    FOREIGN KEY (barraca_id) REFERENCES barracas(id) ON DELETE SET NULL
);

-- ============================================================
-- TABELA: evento_artista (N:N)
-- Um evento pode ter vários artistas; um artista pode atuar em vários eventos
-- ============================================================
CREATE TABLE IF NOT EXISTS evento_artista (
    evento_id INT NOT NULL,
    artista_id INT NOT NULL,
    PRIMARY KEY (evento_id, artista_id),
    FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE CASCADE,
    FOREIGN KEY (artista_id) REFERENCES artistas(id) ON DELETE CASCADE
);

-- ============================================================
-- TABELA: ratings_eventos
-- Avaliações de estudantes a eventos (1-5 estrelas)
-- Cada estudante só pode avaliar um evento uma vez (UNIQUE)
-- ============================================================
CREATE TABLE IF NOT EXISTS ratings_eventos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    evento_id INT NOT NULL,
    nota INT NOT NULL CHECK (nota BETWEEN 1 AND 5),
    comentario TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_avaliacao_evento (user_id, evento_id),  -- 1 avaliação por utilizador por evento
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE CASCADE
);

-- ============================================================
-- TABELA: ratings_barracas
-- Avaliações de estudantes a barracas (1-5 estrelas)
-- ============================================================
CREATE TABLE IF NOT EXISTS ratings_barracas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    barraca_id INT NOT NULL,
    nota INT NOT NULL CHECK (nota BETWEEN 1 AND 5),
    comentario TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_avaliacao_barraca (user_id, barraca_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (barraca_id) REFERENCES barracas(id) ON DELETE CASCADE
);

-- ============================================================
-- TABELA: agenda_pessoal
-- Cada estudante pode adicionar eventos à sua agenda pessoal
-- ============================================================
CREATE TABLE IF NOT EXISTS agenda_pessoal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    evento_id INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_agenda (user_id, evento_id),            -- sem duplicados
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE CASCADE
);

-- ============================================================
-- INSERÇÃO DE DADOS: Perfis
-- ============================================================
INSERT INTO roles (nome) VALUES ('administrador'), ('estudante');

-- ============================================================
-- INSERÇÃO DE DADOS: Utilizadores
-- Passwords: 'admin123' e 'estudante123' (geradas com password_hash)
-- Para criar novas: php -r "echo password_hash('suapassword', PASSWORD_DEFAULT);"
-- ============================================================
INSERT INTO users (nome, email, password, role_id) VALUES
('Administrador Queima', 'admin@queima.pt', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
('Ana Martins',          'ana@fe.up.pt',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2),
('João Silva',           'joao@fe.up.pt',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2),
('Maria Costa',          'maria@fd.up.pt',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2);
-- NOTA: A password de todos os utilizadores de teste é 'password'
-- O hash acima é o hash padrão do Laravel/PHP para 'password'

-- ============================================================
-- INSERÇÃO DE DADOS: Faculdades da Universidade do Porto
-- ============================================================
INSERT INTO faculdades (nome, sigla, descricao, cor) VALUES
('Faculdade de Engenharia',                          'FEUP',  'A maior e mais antiga faculdade de engenharia do Porto, conhecida pela sua inovação e excelência académica.', '#005A9C'),
('Faculdade de Direito',                             'FDUP',  'Formação jurídica de excelência no coração do Porto, com tradição secular no ensino do Direito.', '#8B0000'),
('Faculdade de Medicina',                            'FMUP',  'Referência no ensino médico em Portugal, formando os melhores profissionais de saúde.', '#006400'),
('Faculdade de Economia',                            'FEP',   'Centro de excelência em Economia, Gestão e Finanças da Universidade do Porto.', '#FF8C00'),
('Faculdade de Letras',                              'FLUP',  'Tradição humanista e cultural, berço das artes, letras e ciências humanas no Porto.', '#6A0DAD'),
('Instituto de Ciências Biomédicas Abel Salazar',    'ICBAS', 'Formação em ciências da vida e do mar, com forte componente de investigação científica.', '#008080'),
('Faculdade de Psicologia e de Ciências da Educação','FPCEUP','Psicologia e Ciências da Educação aliadas à investigação aplicada ao bem-estar humano.', '#C71585'),
('Faculdade de Arquitetura',                         'FAUP',  'Design, arquitetura e urbanismo com visão criativa e inovadora.', '#D2691E');

-- ============================================================
-- INSERÇÃO DE DADOS: Barracas 2026
-- ============================================================
INSERT INTO barracas (nome, faculdade_id, localizacao, hora_abertura, hora_fecho, descricao) VALUES
('Barraca da FEUP',   1, 'Zona A - Entrada Principal',  '18:00:00', '04:00:00', 'A barraca mais animada do recinto! A FEUP garante música, convívio e muita energia. Especialidade: shots de engenharia e sangria da casa.'),
('Barraca da FDUP',   2, 'Zona B - Largo Central',      '19:00:00', '03:00:00', 'Elegância e tradição jurídica. A barraca da Direito é conhecida pelo ambiente sofisticado e pelos debates acalorados à meia-noite.'),
('Barraca da FMUP',   3, 'Zona C - Ala Norte',          '18:30:00', '03:30:00', 'Os futuros médicos sabem como curar qualquer tristeza! A barraca da Medicina é sinónimo de boa disposição e solidariedade.'),
('Barraca da FEP',    4, 'Zona D - Ala Sul',            '19:00:00', '04:00:00', 'A barraca da Economia sabe como gerir o orçamento para a diversão máxima. Cocktails temáticos e DJ todas as noites.'),
('Barraca da FLUP',   5, 'Zona E - Jardim das Letras',  '18:00:00', '03:00:00', 'Arte, poesia e muita cultura. A FLUP traz o melhor da música portuguesa e noites de fado contemporâneo.'),
('Barraca do ICBAS',  6, 'Zona F - Ribeira do Recinto', '19:30:00', '03:00:00', 'Ciência e diversão andam juntas! O ICBAS apresenta a barraca mais científica do recinto, com cocktails temáticos inspirados na biologia marinha.'),
('Barraca da FPCEUP', 7, 'Zona G - Centro do Recinto',  '18:00:00', '02:30:00', 'O lugar mais acolhedor da Queima! A FPCEUP garante um ambiente descontraído, com atividades lúdicas e muita psicologia positiva.'),
('Barraca da FAUP',   8, 'Zona H - Esplanada Design',   '19:00:00', '04:00:00', 'Uma barraca com design premiado! A Arquitetura trouxe ao recinto uma estrutura visualmente impressionante com instalações artísticas.');

-- ============================================================
-- INSERÇÃO DE DADOS: Artistas confirmados para a Queima 2026
-- ============================================================
INSERT INTO artistas (nome, genero, pais, biografia) VALUES
('Dino d''Santiago',   'R&B / Soul / Funaná', 'Portugal', 'Dino d''Santiago é um dos artistas mais versáteis e aclamados da música portuguesa contemporânea. Nascido em Lisboa, de origem cabo-verdiana, mistura funaná, R&B, soul e batucada numa fusão única que conquistou toda a Europa.'),
('Wet Bed Gang',       'Trap / Rap',           'Portugal', 'Coletivo de rap português formado em Lisboa, o Wet Bed Gang revolucionou o trap nacional com letras cruas e batidas pesadas. Um dos atos mais aguardados da juventude portuguesa.'),
('Ana Moura',          'Fado',                 'Portugal', 'Considerada uma das maiores vozes do fado da sua geração, Ana Moura já partilhou o palco com os Rolling Stones e Prince. A sua voz inconfundível é sinónimo de emoção pura.'),
('Black Mamba',        'Pop / Soul',           'Portugal', 'Representante de Portugal na Eurovisão 2021, Diogo Piçarra, mais conhecido como Black Mamba, conquistou a Europa com a sua voz poderosa e presença cénica.'),
('Agir',               'Pop / R&B',            'Portugal', 'Um dos cantores portugueses mais populares da última década, Agir é conhecido pelos seus êxitos de verão que animam praias e festivais de norte a sul do país.'),
('Ivandro',            'Pop / Afropop',        'Portugal', 'Cantor e compositor português de origem guineense, Ivandro explodiu nas tabelas portuguesas com os seus ritmos afropop e letras românticas que conquistaram todas as gerações.'),
('Rui Veloso',         'Rock / Blues',         'Portugal', 'O padrinho do rock português. Rui Veloso é uma lenda viva da música nacional, responsável por clássicos imortais que atravessam gerações inteiras de portugueses.'),
('Surma',              'Indie / Experimental', 'Portugal', 'Vocalista dos OISEAUX-TEMPÊTE e artista a solo, Surma é uma das vozes mais originais e intemporais da música portuguesa contemporânea. Os seus concertos são experiências únicas e hipnóticas.'),
('Jorge Palma',        'Rock / Pop',           'Portugal', 'Lenda da música portuguesa com mais de 50 anos de carreira. Jorge Palma é um dos compositores mais respeitados do país, com um catálogo imenso de canções inesquecíveis.'),
('David Carreira',     'Pop / Dance',          'Portugal', 'Filho do cantor Tony Carreira, David Carreira conquistou o público português com a sua energia contagiante, coreografias impressionantes e pop radiofónica.');

-- ============================================================
-- INSERÇÃO DE DADOS: Eventos da Queima das Fitas 2026
-- ============================================================
INSERT INTO eventos (nome, descricao, data_hora, local, tipo, barraca_id) VALUES
-- CERIMÓNIAS ACADÉMICAS
('Serenata Monumental',
 'A cerimónia mais emotiva da Queima das Fitas! A Serenata Monumental acontece na Reitoria da Universidade do Porto e reúne estudantes de todas as faculdades numa noite de fado e solidariedade académica. Os estudantes finalistas despedem-se da academia com as suas pastas e capas, num ritual único e emocionante.',
 '2026-05-11 21:30:00', 'Reitoria da Universidade do Porto', 'cerimonia', NULL),

('Cortejo Académico',
 'O Cortejo Académico é o coração da Queima das Fitas! Milhares de estudantes percorrem as ruas do Porto em carros alegóricos decorados por cada faculdade, com músicas, danças e muita criatividade. É o momento mais aguardado do ano académico, com o Porto inteiro na rua.',
 '2026-05-13 15:00:00', 'Avenida dos Aliados - Porto', 'cerimonia', NULL),

('Missa da Bênção das Pastas',
 'Cerimónia religiosa tradicional onde os estudantes finalistas recebem a bênção das suas pastas académicas. Um momento de reflexão e gratidão que marca o início oficial da semana da Queima das Fitas do Porto.',
 '2026-05-10 11:00:00', 'Sé Catedral do Porto', 'cerimonia', NULL),

('Queima da Fita',
 'O momento mais simbólico do festival! Os estudantes finalistas queimam as suas fitas coloridas como símbolo da conclusão do curso e da despedida da vida académica. Uma tradição secular carregada de emoção.',
 '2026-05-17 22:00:00', 'Praça da Liberdade - Porto', 'cerimonia', NULL),

-- CONCERTOS
('Concerto de Abertura - Dino d''Santiago',
 'A abertura oficial da Queima das Fitas 2026 começa com o explosivo Dino d''Santiago! Uma noite de funaná, R&B e soul que vai fazer o Queimódromo vibrar do início ao fim. O artista promete um espetáculo visual e sonoro sem precedentes.',
 '2026-05-11 23:00:00', 'Queimódromo - Porto', 'concerto', NULL),

('Noite do Rap - Wet Bed Gang',
 'A noite mais quente do trap nacional! O Wet Bed Gang toma conta do Queimódromo numa noite dedicada ao rap e trap português. Convidados surpresa garantidos. Esta é a noite que nenhum estudante pode perder.',
 '2026-05-12 23:30:00', 'Queimódromo - Porto', 'concerto', NULL),

('Noite do Fado - Ana Moura',
 'Uma noite única dedicada ao fado mais puro e emotivo. Ana Moura, uma das maiores vozes do fado português, traz ao Queimódromo uma atuação que promete ser histórica. Um espetáculo para ficar na memória de todos.',
 '2026-05-13 22:00:00', 'Queimódromo - Porto', 'concerto', NULL),

('Noite Pop - Black Mamba & Agir',
 'Pop de qualidade numa noite dupla incrível! Black Mamba e Agir partilham o mesmo palco numa noite de hits radiofónicos e emoções à flor da pele. Dois dos artistas mais carismáticos de Portugal juntos pela primeira vez na Queima.',
 '2026-05-14 23:00:00', 'Queimódromo - Porto', 'concerto', NULL),

('Noite Afro - Ivandro em Grande',
 'Os ritmos africanos chegam ao Queimódromo com Ivandro! Uma noite de afropop, dança e muita festa que vai transformar o recinto numa grande festa atlântica. Ivandro promete um espetáculo com banda completa e efeitos especiais.',
 '2026-05-15 23:30:00', 'Queimódromo - Porto', 'concerto', NULL),

('Grande Noite de Encerramento - Rui Veloso & Jorge Palma',
 'O encerramento mais épico da Queima das Fitas 2026! Dois gigantes da música portuguesa, Rui Veloso e Jorge Palma, juntos num mesmo palco pela última vez. Uma noite histórica e inesquecível para terminar a semana mais especial do ano académico portuense.',
 '2026-05-17 23:00:00', 'Queimódromo - Porto', 'concerto', NULL),

('Concerto Indie - Surma',
 'Para os amantes da música alternativa e indie, Surma traz ao Queimódromo uma das atuações mais originais e intimistas do festival. Uma experiência sonora que toca a alma.',
 '2026-05-16 22:30:00', 'Palco Alternativo - Queimódromo', 'concerto', NULL),

('Festa de Encerramento das Barracas - David Carreira',
 'A festa final nas barracas com David Carreira a animar a última noite do recinto! Dance, pop e muito boa disposição para encerrar com chave de ouro a Queima das Fitas 2026.',
 '2026-05-17 20:00:00', 'Recinto das Barracas', 'concerto', NULL),

-- ATIVIDADES CULTURAIS
('Noite do Fado nas Barracas - FLUP',
 'A Faculdade de Letras apresenta uma noite especial de fado contemporâneo nas suas barracas, com estudantes fadistas da universidade a mostrar o melhor da tradição musical portuguesa num ambiente descontraído.',
 '2026-05-12 21:00:00', 'Barraca da FLUP', 'atividade', 5),

('Torneio Interacadémico de Futebol',
 'O grande torneio de futebol entre as faculdades da Universidade do Porto regressa! Oito equipas, oito faculdades, uma taça. Venha apoiar a sua faculdade e viver a rivalidade saudável que só a Queima proporciona.',
 '2026-05-12 10:00:00', 'Campos Desportivos da UP', 'atividade', NULL),

('Exposição de Arte "Porto Académico"',
 'Uma exposição coletiva de arte produzida por estudantes da FAUP e FLUP, celebrando a relação entre Porto e a sua academia. Pinturas, esculturas e instalações que retratam a vida académica portuense.',
 '2026-05-10 10:00:00', 'Reitoria da UP - Galeria de Exposições', 'atividade', NULL),

('Workshop de Fado para Estudantes',
 'Aprenda os segredos do fado com músicos profissionais! Este workshop gratuito para estudantes da UP inclui aulas de guitarra portuguesa, viola baixo e, claro, técnica vocal de fado. Inscrições limitadas.',
 '2026-05-11 14:00:00', 'Faculdade de Letras - Auditório', 'atividade', NULL),

('Debate "O Futuro do Ensino Superior"',
 'Um debate de alto nível com reitores, professores e estudantes sobre os desafios do ensino superior português na era digital. Moderado por jornalistas da SIC e RTP.',
 '2026-05-13 17:00:00', 'Reitoria da UP - Salão Nobre', 'atividade', NULL),

('Concurso de Tunas Académicas',
 'As melhores tunas académicas do norte de Portugal competem pelo título de melhor tuna da Queima 2026! Uma tarde de música tradicional académica, humor e muita camaradagem.',
 '2026-05-14 16:00:00', 'Praça dos Leões - Porto', 'atividade', NULL),

('Noite de Jogos Tradicionais nas Barracas',
 'Esqueça o telemóvel! Uma noite dedicada aos jogos tradicionais portugueses: malha, jogo do pau, pião e muito mais, organizada pela FPCEUP para promover o convívio e as tradições culturais.',
 '2026-05-15 20:00:00', 'Barraca da FPCEUP', 'atividade', 7),

('Visita Guiada ao Porto Histórico',
 'Descubra os segredos do Porto histórico com guias especialistas! Uma visita guiada gratuita para estudantes que percorre os pontos mais emblemáticos da cidade: Ribeira, Clérigos, Livraria Lello e muito mais.',
 '2026-05-16 10:00:00', 'Praça da Liberdade - Ponto de Encontro', 'atividade', NULL);

-- ============================================================
-- ASSOCIAÇÃO: Artistas ↔ Eventos (concertos)
-- ============================================================
INSERT INTO evento_artista (evento_id, artista_id) VALUES
(5,  1),   -- Dino d'Santiago no Concerto de Abertura
(6,  2),   -- Wet Bed Gang na Noite do Rap
(7,  3),   -- Ana Moura na Noite do Fado
(8,  4),   -- Black Mamba na Noite Pop
(8,  5),   -- Agir na Noite Pop (dois artistas no mesmo evento)
(9,  6),   -- Ivandro na Noite Afro
(10, 7),   -- Rui Veloso no Encerramento
(10, 9),   -- Jorge Palma no Encerramento (dois artistas)
(11, 8),   -- Surma no Concerto Indie
(12, 10);  -- David Carreira na Festa de Encerramento

-- ============================================================
-- AVALIAÇÕES DE EXEMPLO (para demonstração)
-- ============================================================
INSERT INTO ratings_eventos (user_id, evento_id, nota, comentario) VALUES
(2, 1, 5, 'A Serenata foi absolutamente emocionante! Chorei do início ao fim.'),
(3, 1, 5, 'Melhor noite da minha vida académica. Inesquecível!'),
(2, 5, 4, 'O Dino d''Santiago é um fenómeno ao vivo. Show incrível!'),
(3, 6, 5, 'Wet Bed Gang ao vivo é outra dimensão. Melhor concerto do ano!'),
(4, 7, 5, 'A Ana Moura fez-me perceber o que é o fado de verdade. Arrepios.'),
(2, 2, 4, 'O Cortejo foi espetacular! A FEUP teve o melhor carro alegórico.');

INSERT INTO ratings_barracas (user_id, barraca_id, nota, comentario) VALUES
(2, 1, 5, 'A barraca da FEUP tem a melhor sangria do recinto! 10/10'),
(3, 1, 4, 'Muito animada, fila um pouco grande mas vale a pena.'),
(4, 2, 4, 'A barraca da Direito tem um ambiente muito elegante e sofisticado.'),
(2, 5, 5, 'A FLUP surpreendeu com a noite de fado. Absolutamente mágico!'),
(3, 4, 4, 'Os cocktails da FEP são os melhores! Muito criativos.');

-- ============================================================
-- AGENDA PESSOAL DE EXEMPLO
-- ============================================================
INSERT INTO agenda_pessoal (user_id, evento_id) VALUES
(2, 1),  -- Ana tem a Serenata na agenda
(2, 5),  -- Ana tem o concerto de Abertura
(2, 2),  -- Ana tem o Cortejo Académico
(3, 6),  -- João tem a Noite do Rap
(3, 8),  -- João tem a Noite Pop
(3, 2),  -- João tem o Cortejo
(4, 7),  -- Maria tem a Noite do Fado
(4, 1),  -- Maria tem a Serenata
(4, 10); -- Maria tem o Encerramento

-- ============================================================
-- FIM DO SCRIPT
-- Base de dados pronta! Aceder em: http://localhost/queima_fitas/
-- Login Admin: admin@queima.pt / password
-- Login Estudante: ana@fe.up.pt / password
-- ============================================================
