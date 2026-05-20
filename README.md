# 🎓 Queima das Fitas do Porto 2026 — Sistema de Informação
### Projeto SINF1 2025/2026

---

## 📋 COMO INSTALAR NO XAMPP

### Passo 1 — Copiar o projeto
Copiar a pasta `queima_fitas` para:
```
C:\xampp\htdocs\queima_fitas\
```

### Passo 2 — Importar a base de dados
1. Iniciar o XAMPP (Apache + MySQL)
2. Abrir o browser em: `http://localhost/phpmyadmin`
3. Clicar em **"Importar"** (menu superior)
4. Selecionar o ficheiro `database.sql` desta pasta
5. Clicar **"Executar"**

### Passo 3 — Aceder ao site
```
http://localhost/queima_fitas/
```

---

## 🔑 CONTAS DE TESTE

| Papel         | Email                | Password   |
|---------------|----------------------|------------|
| Administrador | admin@queima.pt      | password   |
| Estudante     | ana@gmail.com        | password   |
| Estudante     | joao@gmail.com       | password   |
| Estudante     | maria@gmail.com      | password   |

---

## 🏗️ ARQUITETURA — 3 CAMADAS

```
┌─────────────────────────────────┐
│  APRESENTAÇÃO (Camada 1)        │
│  index.php, eventos.php, ...    │
│  HTML + CSS + JavaScript        │
└────────────┬────────────────────┘
             │ chama funções
┌────────────▼────────────────────┐
│  LÓGICA DE NEGÓCIO (Camada 2)   │
│  BusinessLogicLayer.php         │
│  Validação, sessões, regras     │
└────────────┬────────────────────┘
             │ usa DAL
┌────────────▼────────────────────┐
│  ACESSO A DADOS (Camada 3)      │
│  DataAccessLayer.php            │
│  PDO + MySQL (Prepared stmts)   │
└─────────────────────────────────┘
```

---

## 📁 ESTRUTURA DE FICHEIROS

```
queima_fitas/
├── database.sql              ← Script completo da BD (criar + dados)
├── DataAccessLayer.php       ← Camada de Acesso a Dados (DAL)
├── BusinessLogicLayer.php    ← Camada de Lógica de Negócio (BLL)
├── style.css                 ← CSS completo do site
│
├── includes/
│   ├── header.php            ← Cabeçalho e navbar reutilizável
│   └── footer.php            ← Rodapé reutilizável
│
├── index.php                 ← Página inicial (countdown, alertas)
├── login.php                 ← Login de utilizadores
├── registo.php               ← Registo de novos utilizadores
├── logout.php                ← Terminar sessão
├── perfil.php                ← Perfil do utilizador (editar)
│
├── eventos.php               ← Listagem de eventos (com filtros)
├── evento_detalhe.php        ← Detalhe de evento + avaliação + agenda
├── artistas.php              ← Listagem de artistas
├── artista_detalhe.php       ← Perfil do artista
├── barracas.php              ← Listagem de barracas
├── barraca_detalhe.php       ← Detalhe de barraca + avaliação
├── faculdades.php            ← Listagem de faculdades
├── agenda.php                ← Agenda pessoal do estudante
│
├── admin.php                 ← Dashboard de administração
├── admin_evento_form.php     ← Criar/editar eventos
├── admin_artista_form.php    ← Criar/editar artistas
├── admin_barraca_form.php    ← Criar/editar barracas
├── admin_faculdade_form.php  ← Criar/editar faculdades
└── admin_delete.php          ← Eliminar qualquer entidade
```

---

## 🗄️ BASE DE DADOS — TABELAS

| Tabela             | Descrição                              |
|--------------------|----------------------------------------|
| `roles`            | Perfis (administrador, estudante)      |
| `users`            | Utilizadores registados                |
| `faculdades`       | Faculdades da UP                       |
| `barracas`         | Barracas das faculdades                |
| `artistas`         | Artistas confirmados                   |
| `eventos`          | Todos os eventos do festival           |
| `evento_artista`   | Relação N:N artistas ↔ eventos         |
| `ratings_eventos`  | Avaliações de eventos (estudantes)     |
| `ratings_barracas` | Avaliações de barracas (estudantes)    |
| `agenda_pessoal`   | Agenda pessoal de cada estudante       |

---

## ✨ FUNCIONALIDADES

### Para todos os visitantes
- Ver programa completo de eventos
- Ver artistas confirmados
- Ver barracas e horários
- Ver faculdades participantes

### Para estudantes (após login)
- Criar agenda pessoal com eventos favoritos
- Avaliar eventos (1-5 estrelas + comentário)
- Avaliar barracas (1-5 estrelas + comentário)
- Editar perfil

### Para administradores
- CRUD completo: eventos, artistas, barracas, faculdades
- Dashboard com estatísticas
- Associar artistas a concertos

---

## 🛡️ SEGURANÇA

- **Passwords**: guardadas com `password_hash()` (bcrypt)
- **SQL Injection**: prevenido com PDO Prepared Statements
- **XSS**: prevenido com `htmlspecialchars()` em todos os outputs
- **Controlo de acesso**: funções `requireLogin()` e `requireAdmin()`
- **Sessões PHP**: gestão segura de autenticação

---

## 🚀 TECNOLOGIAS

- **PHP 8+** — Backend e lógica
- **MySQL** — Base de dados relacional
- **HTML5** — Estrutura das páginas
- **CSS3** — Estilos com variáveis CSS e design responsivo
- **JavaScript** — Countdown timer, seletor de estrelas interativo
- **PDO** — Acesso seguro à base de dados


