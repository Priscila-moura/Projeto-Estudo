<?php
session_start();
require_once 'conexao.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken  = $_SESSION['csrf_token'];
$estaLogado = isset($_SESSION['usuario_id']);

$stmtSetores = $pdo->query("SELECT id, nome FROM setores WHERE ativo = 1 ORDER BY nome ASC");
$setoresAtivos = $stmtSetores->fetchAll();

$stmtPerfis = $pdo->query("SELECT id, nome FROM perfis WHERE ativo = 1 ORDER BY nome ASC");
$perfisAtivos = $stmtPerfis->fetchAll();

$stmtCat = $pdo->query("SELECT * FROM categorias ORDER BY id ASC");
$categorias = $stmtCat->fetchAll();

$stmtProd = $pdo->query("SELECT * FROM produtos ORDER BY categoria_id ASC, nome ASC");
$todosProdutos = $stmtProd->fetchAll();

$produtosPorCategoria = [];
foreach ($todosProdutos as $prod) {
    $produtosPorCategoria[$prod['categoria_id']][] = $prod;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="shortcut icon" href="img/Projeto.ico" type="image/x-icon">
  <title>MERCEARIA - Sistema & Gestão de Usuários</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .auth-error, .alert-box {
      padding: 10px 14px;
      border-radius: 6px;
      font-size: 0.88rem;
      margin-bottom: 14px;
      display: none;
    }
    .auth-error, .alert-error {
      color: #ff6b6b;
      background: rgba(255, 107, 107, 0.12);
      border: 1px solid #ff6b6b;
    }
    .alert-success {
      color: #6ede4e;
      background: rgba(110, 222, 78, 0.12);
      border: 1px solid #368f1b;
    }

   
    .tabela-produtos, .tabela-usuarios {
      width: 100%;
      margin: 20px auto;
      border-collapse: collapse;
      background-color: var(--bg-card);
      border-radius: 8px;
      overflow: hidden;
      border: 1px solid rgba(54, 143, 27, 0.4);
    }
    .tabela-produtos th, .tabela-produtos td,
    .tabela-usuarios th, .tabela-usuarios td {
      padding: 12px 15px;
      text-align: left;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      font-size: 0.92rem;
    }
    .tabela-produtos th, .tabela-usuarios th {
      background-color: #12100e;
      color: var(--accent-gold);
      text-transform: uppercase;
      font-size: 0.85rem;
    }
    .tabela-produtos tr:hover, .tabela-usuarios tr:hover {
      background-color: rgba(54, 143, 27, 0.08);
    }
    .preco-destaque {
      color: #6ede4e;
      font-weight: bold;
    }

   
    .usuarios-layout {
      display: grid;
      grid-template-columns: 360px 1fr;
      gap: 25px;
      align-items: start;
    }
    @media (max-width: 900px) {
      .usuarios-layout { grid-template-columns: 1fr; }
    }
    .panel-box {
      background: var(--bg-card);
      border: 1px solid rgba(54, 143, 27, 0.45);
      border-radius: 10px;
      padding: 22px;
    }
    .panel-box h2, .panel-box h3 {
      color: var(--accent-gold);
      margin-bottom: 15px;
    }
    .input-group select {
      width: 100%;
      padding: 11px;
      border-radius: 6px;
      border: 1px solid rgba(212, 175, 55, 0.3);
      background-color: #12100e;
      color: var(--text-light);
      font-size: 0.95rem;
      outline: none;
    }
    .input-group select:focus {
      border-color: var(--accent-gold);
    }
    .field-error {
      color: #ff6b6b;
      font-size: 0.78rem;
      margin-top: 4px;
      display: block;
      min-height: 14px;
    }
    .search-bar {
      display: flex;
      gap: 10px;
      margin-bottom: 15px;
    }
    .search-bar input {
      flex: 1;
      padding: 10px 14px;
      border-radius: 6px;
      border: 1px solid rgba(54, 143, 27, 0.4);
      background: #12100e;
      color: var(--text-light);
    }
    .btn-excluir {
      background: rgba(217, 83, 79, 0.15);
      border: 1px solid #d9534f;
      color: #ff7b77;
      padding: 6px 12px;
      border-radius: 5px;
      cursor: pointer;
      font-size: 0.82rem;
      font-weight: bold;
      transition: 0.2s;
    }
    .btn-excluir:hover {
      background: #d9534f;
      color: #fff;
    }
    .badge-ativo {
      background: rgba(54, 143, 27, 0.2);
      color: #6ede4e;
      padding: 3px 8px;
      border-radius: 4px;
      font-size: 0.78rem;
      font-weight: bold;
    }

  
    .modal-backdrop {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.78);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 2000;
      padding: 20px;
    }
    .modal-card {
      background: var(--bg-card);
      border: 2px solid #d9534f;
      border-radius: 10px;
      max-width: 440px;
      width: 100%;
      padding: 25px;
      box-shadow: 0 12px 35px rgba(0,0,0,0.6);
    }
    .modal-card h3 {
      color: #ff7b77;
      margin-bottom: 12px;
    }
    .modal-user-info {
      background: #12100e;
      padding: 12px;
      border-radius: 6px;
      margin: 14px 0;
      border-left: 3px solid #d9534f;
      font-size: 0.9rem;
    }
    .modal-actions {
      display: flex;
      justify-content: flex-end;
      gap: 12px;
      margin-top: 18px;
    }
    .btn-cancelar {
      background: #3a3430;
      color: #f5f5f5;
      border: none;
      padding: 10px 16px;
      border-radius: 6px;
      cursor: pointer;
    }
    .btn-confirmar-baixa {
      background: #d9534f;
      color: #fff;
      border: none;
      padding: 10px 16px;
      border-radius: 6px;
      font-weight: bold;
      cursor: pointer;
    }
  </style>
</head>
<body>

  
  <div id="authOverlay" class="auth-overlay" style="display: <?= $estaLogado ? 'none' : 'flex' ?>;">
    <div id="loginCard" class="auth-card">
      <h2>Acessar Mercearia</h2>
      <div id="loginError" class="auth-error"></div>
      <form class="auth-form" onsubmit="handleLogin(event)">
        <div class="input-group">
          <label for="loginUser">E-mail</label>
          <input type="email" id="loginUser" placeholder="exemplo@email.com" required>
        </div>
        <div class="input-group">
          <label for="loginPass">Senha</label>
          <input type="password" id="loginPass" placeholder="Sua senha" required>
        </div>
        <button type="submit" class="auth-btn" id="btnLogin">Entrar</button>
      </form>
      <div class="toggle-auth">
        Nao tem uma conta? <span onclick="toggleAuthMode('signup')">Cadastre-se</span>
      </div>
    </div>

    <div id="signupCard" class="auth-card" style="display: none;">
      <h2>Criar Conta</h2>
      <div id="signupError" class="auth-error"></div>
      <form class="auth-form" onsubmit="handleSignup(event)">
        <div class="input-group">
          <label for="signupUser">E-mail</label>
          <input type="email" id="signupUser" placeholder="exemplo@email.com" required>
        </div>
        <div class="input-group">
          <label for="signupPass">Senha</label>
          <input type="password" id="signupPass" placeholder="Crie uma senha" required>
        </div>
        <button type="submit" class="auth-btn" id="btnSignup">Cadastrar e Entrar</button>
      </form>
      <div class="toggle-auth">
        Ja possui conta? <span onclick="toggleAuthMode('login')">Fazer Login</span>
      </div>
    </div>
  </div>

  
  <div id="appContainer" class="app-container" style="display: <?= $estaLogado ? 'flex' : 'none' ?>;">
    <header>
      <div class="brand">Economia certa para o seu bolso!</div>
      <nav>
        <button class="nav-btn active" onclick="switchTab('home', event)">Home</button>
        <button class="nav-btn" onclick="switchTab('dashboard', event)">Dashboard</button>
        <button class="nav-btn" onclick="switchTab('produtos', event)">Produtos</button>
        <button class="nav-btn" onclick="switchTab('usuarios', event)">Usuários</button>
        <button class="nav-btn" onclick="switchTab('sobre', event)">Sobre</button>
        <button class="nav-btn" onclick="switchTab('contato', event)">Contato</button>
        <button class="nav-btn logout-btn" onclick="handleLogout()">Sair</button>
      </nav>
    </header>

    <main>
     
      <section id="home" class="tab-content active">
        <div class="hero-banner">
          <h1>COMPRE RAPIDO</h1>
          <p>Produtos de alta qualidade.</p>
          <img src="https://cdn.shopify.com/s/files/1/0587/8487/4575/files/STM_Banners_Categorias_MOB_mercearia_6a4ce5c6-d543-4ff5-9cad-8651b070fcb8.png?v=1731615126" alt="Banner Mercearia">
        </div>
      </section>

      
      <section id="dashboard" class="tab-content">
        <div class="hero-banner">
          <h1>Dashboard de Categorias</h1>
          <p>Passe o mouse sobre uma categoria para visualizar os itens e preços disponiveis.</p>
        </div>

        <div class="dashboard-grid">
          <?php foreach ($categorias as $cat): 
            $itens = $produtosPorCategoria[$cat['id']] ?? [];
          ?>
            <div class="card">
              <span class="icon"><?= htmlspecialchars($cat['icone']) ?></span>
              <h3><?= htmlspecialchars($cat['nome']) ?></h3>
              <p><?= count($itens) ?> itens cadastrados</p>
              <div class="product-overlay">
                <h4>Produtos e Preços:</h4>
                <ul>
                  <?php foreach ($itens as $item): ?>
                    <li>
                      • <?= htmlspecialchars($item['nome']) ?> — 
                      <strong style="color: #6ede4e;">R$ <?= number_format($item['preco'], 2, ',', '.') ?></strong>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>

     
      <section id="produtos" class="tab-content">
        <div class="hero-banner">
          <h1>Catalogo de Produtos</h1>
          <p>Confira nossa tabela de preços atualizada.</p>

          <table class="tabela-produtos" style="max-width: 750px;">
            <thead>
              <tr>
                <th>Produto</th>
                <th>Estoque</th>
                <th>Preço</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($todosProdutos as $produto): ?>
                <tr>
                  <td><?= htmlspecialchars($produto['nome']) ?></td>
                  <td><?= (int) $produto['estoque'] ?> un.</td>
                  <td class="preco-destaque">R$ <?= number_format($produto['preco'], 2, ',', '.') ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>

          <br>
          <p>OBRIGADO PELA SUA PREFERENCIA!</p>
          <span class="icon" style="font-size: 2rem; margin-top: 10px;">❤️</span>
        </div>
      </section>

        <section id="usuarios" class="tab-content">
        <div class="hero-banner" style="padding: 25px 20px; margin-bottom: 25px;">
          <h1>Gestão de Usuários</h1>
          <p>Cadastro persistente em MySQL, relacionamento por JOIN e exclusão lógica auditada.</p>
        </div>

        <div id="userGlobalAlert" class="alert-box"></div>

        <div class="usuarios-layout">
          <!-- FORMULÁRIO DE CADASTRO (PONTOS 04, 05 E 06) -->
          <div class="panel-box">
            <h2>Novo Usuário</h2>
            <form id="formCadastroUsuario" class="auth-form" onsubmit="cadastrarUsuario(event)" novalidate>
              <input type="hidden" id="csrfToken" value="<?= htmlspecialchars($csrfToken) ?>">

              <div class="input-group">
                <label for="cadNome">Nome Completo *</label>
                <input type="text" id="cadNome" name="nome" placeholder="Ex.: João da Silva" required minlength="3">
                <span class="field-error" id="err-nome"></span>
              </div>

              <div class="input-group">
                <label for="cadEmail">E-mail *</label>
                <input type="email" id="cadEmail" name="email" placeholder="joao@mercearia.com" required>
                <span class="field-error" id="err-email"></span>
              </div>

              <div class="input-group">
                <label for="cadSenha">Senha *</label>
                <input type="password" id="cadSenha" name="senha" placeholder="Mínimo 4 caracteres" required minlength="4">
                <span class="field-error" id="err-senha"></span>
              </div>

             
              <div class="input-group">
                <label for="cadSetor">Setor *</label>
                <?php if (empty($setoresAtivos)): ?>
                  <p class="field-error">Nenhum setor ativo encontrado no banco de dados.</p>
                <?php else: ?>
                  <select id="cadSetor" name="setor_id" required>
                    <option value="">Selecione um setor...</option>
                    <?php foreach ($setoresAtivos as $setor): ?>
                      <option value="<?= (int) $setor['id'] ?>"><?= htmlspecialchars($setor['nome']) ?></option>
                    <?php endforeach; ?>
                  </select>
                <?php endif; ?>
                <span class="field-error" id="err-setor_id"></span>
              </div>

              <div class="input-group">
                <label for="cadPerfil">Perfil de Acesso *</label>
                <?php if (empty($perfisAtivos)): ?>
                  <p class="field-error">Nenhum perfil ativo encontrado no banco de dados.</p>
                <?php else: ?>
                  <select id="cadPerfil" name="perfil_id" required>
                    <option value="">Selecione um perfil...</option>
                    <?php foreach ($perfisAtivos as $perfil): ?>
                      <option value="<?= (int) $perfil['id'] ?>"><?= htmlspecialchars($perfil['nome']) ?></option>
                    <?php endforeach; ?>
                  </select>
                <?php endif; ?>
                <span class="field-error" id="err-perfil_id"></span>
              </div>

              <button type="submit" class="auth-btn" id="btnSalvarUsuario" <?= (empty($setoresAtivos) || empty($perfisAtivos)) ? 'disabled' : '' ?>>
                Salvar Usuário
              </button>
            </form>
          </div>

         
          <div class="panel-box">
            <h2>Usuários Ativos (Consulta JOIN)</h2>

            <div class="search-bar">
              <input type="text" id="campoBuscaUsuario" placeholder="Pesquisar por nome ou e-mail..." onkeyup="carregarUsuarios()">
              <button type="button" class="auth-btn" style="margin-top: 0; padding: 10px 18px;" onclick="carregarUsuarios()">Buscar</button>
            </div>

            <div style="overflow-x: auto;">
              <table class="tabela-usuarios">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Setor (JOIN)</th>
                    <th>Perfil (JOIN)</th>
                    <th>Status</th>
                    <th>Ação</th>
                  </tr>
                </thead>
                <tbody id="tbodyUsuarios">
                  <tr><td colspan="7">Carregando registros do MySQL...</td></tr>
                </tbody>
              </table>
            </div>

            
            <h3 style="margin-top: 30px; font-size: 1.05rem;">Histórico de Auditoria (Últimas Operações)</h3>
            <div style="overflow-x: auto;">
              <table class="tabela-usuarios">
                <thead>
                  <tr>
                    <th>ID Alvo</th>
                    <th>Operação</th>
                    <th>Autor</th>
                    <th>Data / Hora</th>
                    <th>Resultado</th>
                  </tr>
                </thead>
                <tbody id="tbodyAuditoria">
                  <tr><td colspan="5">Carregando auditoria...</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </section>

    
      <section id="sobre" class="tab-content">
        <div class="hero-banner">
          <h1>Sobre Nos</h1>
          <p style="margin-bottom: 15px;">Tudo comecou na decada de 2000, na esquina de uma rua movimentada de bairro!</p>
          <p>Decidi abrir as portas de uma pequena mercearia. No inicio, o espaco era modesto: prateleiras de madeira macica cheias de sacos de arroz, feijao, sabao em barra e grandes potes de vidro com doces coloridos que fascinavam as criancas. Hoje, modernizou-se com novos produtos e aceita cartoes, mas a sua essencia permanece intacta. Continua a ser o local onde o cliente e chamado pelo nome e onde a simplicidade e o afeto fazem com que qualquer pessoa se sinta em casa.</p>
        </div>
      </section>

     
      <section id="contato" class="tab-content">
        <div class="hero-banner">
          <h1>Fale Conosco</h1>
          <p>Entre em contato conosco por qualquer um dos canais abaixo:</p>
          <br>
          <p>📞 <strong>Telefone / WhatsApp:</strong> (81) 3044-0655</p>
          <p>✉️ <strong>E-mail:</strong> mercearia@gmail.com</p>
          <p>📍 <strong>Endereco:</strong> Rua Principal, N 123 - Bairro Do Recife</p>
          <p>🕒 <strong>Horario de Atendimento:</strong> Segunda a Sabado: 07h as 19h</p>
        </div>
      </section>
    </main>
  </div>

  <div id="modalConfirmarExclusao" class="modal-backdrop" role="dialog" aria-modal="true">
    <div class="modal-card">
      <h3>Deseja realmente excluir este usuário?</h3>
      <p style="color: var(--text-muted); font-size: 0.9rem;">
        Esta ação aplicará o status <strong>Baixa</strong> ao cadastro selecionado e registrará a operação na auditoria.
      </p>
      <div class="modal-user-info" id="modalUserInfo"></div>
      <div class="modal-actions">
        <button type="button" class="btn-cancelar" onclick="fecharModalExclusao()">Cancelar</button>
        <button type="button" class="btn-confirmar-baixa" id="btnConfirmarExclusao" onclick="confirmarExclusaoUsuario()">Confirmar Exclusão</button>
      </div>
    </div>
  </div>

  <script>
    let usuarioParaExcluirId = null;

    function escaparHTML(str) {
      const div = document.createElement('div');
      div.textContent = str ?? '';
      return div.innerHTML;
    }

    function toggleAuthMode(mode) {
      document.getElementById('loginError').style.display = 'none';
      document.getElementById('signupError').style.display = 'none';
      document.getElementById('loginCard').style.display  = mode === 'signup' ? 'none' : 'block';
      document.getElementById('signupCard').style.display = mode === 'signup' ? 'block' : 'none';
    }

    function enterApp() {
      document.getElementById('authOverlay').style.display = 'none';
      document.getElementById('appContainer').style.display = 'flex';
      carregarUsuarios();
    }

    async function handleLogin(event) {
      event.preventDefault();
      const errorBox = document.getElementById('loginError');
      const btn = document.getElementById('btnLogin');
      errorBox.style.display = 'none';
      btn.disabled = true;

      const formData = new FormData();
      formData.append('acao', 'login');
      formData.append('identificador', document.getElementById('loginUser').value);
      formData.append('senha', document.getElementById('loginPass').value);

      try {
        const res = await fetch('auth.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.sucesso) {
          enterApp();
        } else {
          errorBox.textContent = data.mensagem;
          errorBox.style.display = 'block';
          document.getElementById('loginPass').value = '';
        }
      } catch (e) {
        errorBox.textContent = 'Erro de comunicação com o servidor.';
        errorBox.style.display = 'block';
      } finally {
        btn.disabled = false;
      }
    }

    async function handleSignup(event) {
      event.preventDefault();
      const errorBox = document.getElementById('signupError');
      const btn = document.getElementById('btnSignup');
      errorBox.style.display = 'none';
      btn.disabled = true;

      const formData = new FormData();
      formData.append('acao', 'cadastro');
      formData.append('identificador', document.getElementById('signupUser').value);
      formData.append('senha', document.getElementById('signupPass').value);

      try {
        const res = await fetch('auth.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.sucesso) {
          alert(data.mensagem);
          enterApp();
        } else {
          errorBox.textContent = data.mensagem;
          errorBox.style.display = 'block';
        }
      } catch (e) {
        errorBox.textContent = 'Erro de comunicação com o servidor.';
        errorBox.style.display = 'block';
      } finally {
        btn.disabled = false;
      }
    }

    async function handleLogout() {
      const formData = new FormData();
      formData.append('acao', 'logout');
      await fetch('auth.php', { method: 'POST', body: formData });
      document.getElementById('appContainer').style.display = 'none';
      document.getElementById('authOverlay').style.display = 'flex';
    }

    function switchTab(tabId, event) {
      document.querySelectorAll('.nav-btn').forEach(btn => btn.classList.remove('active'));
      document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
      if (event) event.currentTarget.classList.add('active');
      document.getElementById(tabId).classList.add('active');

      if (tabId === 'usuarios') {
        carregarUsuarios();
      }
    }

       function exibirAlertaUsuario(tipo, texto) {
      const box = document.getElementById('userGlobalAlert');
      box.className = 'alert-box ' + (tipo === 'sucesso' ? 'alert-success' : 'alert-error');
      box.textContent = texto;
      box.style.display = 'block';
    }

    function limparErrosCampos() {
      document.querySelectorAll('.field-error').forEach(el => el.textContent = '');
    }

    async function carregarUsuarios() {
      const busca = document.getElementById('campoBuscaUsuario').value;
      const tbody = document.getElementById('tbodyUsuarios');
      const tbodyAud = document.getElementById('tbodyAuditoria');

      try {
        const res = await fetch(`usuarios_api.php?acao=listar&busca=${encodeURIComponent(busca)}`);
        const data = await res.json();

        if (!data.sucesso) {
          tbody.innerHTML = `<tr><td colspan="7">${escaparHTML(data.mensagem)}</td></tr>`;
          return;
        }

        if (data.usuarios.length === 0) {
          tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; color: var(--text-muted);">Nenhum usuário ativo encontrado para a consulta.</td></tr>`;
        } else {
          tbody.innerHTML = data.usuarios.map(u => `
            <tr>
              <td>#${u.id}</td>
              <td>${escaparHTML(u.nome)}</td>
              <td>${escaparHTML(u.email)}</td>
              <td>${escaparHTML(u.setor_nome)}</td>
              <td>${escaparHTML(u.perfil_nome)}</td>
              <td><span class="badge-ativo">${escaparHTML(u.status)}</span></td>
              <td>
                <button type="button" class="btn-excluir"
                  onclick="abrirModalExclusao(${u.id}, '${escaparHTML(u.nome).replace(/'/g, "\\'")}', '${escaparHTML(u.email).replace(/'/g, "\\'")}')">
                  Excluir
                </button>
              </td>
            </tr>
          `).join('');
        }

        if (data.auditoria && data.auditoria.length > 0) {
          tbodyAud.innerHTML = data.auditoria.map(a => `
            <tr>
              <td>#${a.usuario_alvo_id}</td>
              <td>${escaparHTML(a.acao)}</td>
              <td>${escaparHTML(a.autor)}</td>
              <td>${escaparHTML(a.data_hora)}</td>
              <td>${escaparHTML(a.resultado)}</td>
            </tr>
          `).join('');
        }
      } catch (err) {
        tbody.innerHTML = `<tr><td colspan="7">Erro ao carregar dados do servidor.</td></tr>`;
      }
    }

    async function cadastrarUsuario(event) {
      event.preventDefault();
      limparErrosCampos();
      document.getElementById('userGlobalAlert').style.display = 'none';

      const btn = document.getElementById('btnSalvarUsuario');
      btn.disabled = true;
      btn.textContent = 'Salvando...';

      const formData = new FormData();
      formData.append('acao', 'cadastrar');
      formData.append('csrf_token', document.getElementById('csrfToken').value);
      formData.append('nome', document.getElementById('cadNome').value);
      formData.append('email', document.getElementById('cadEmail').value);
      formData.append('senha', document.getElementById('cadSenha').value);
      formData.append('setor_id', document.getElementById('cadSetor').value);
      formData.append('perfil_id', document.getElementById('cadPerfil').value);

      try {
        const res = await fetch('usuarios_api.php', { method: 'POST', body: formData });
        const data = await res.json();

        if (data.sucesso) {
          exibirAlertaUsuario('sucesso', data.mensagem);
          document.getElementById('formCadastroUsuario').reset();
          carregarUsuarios();
        } else {
          exibirAlertaUsuario('erro', data.mensagem);
          if (data.erros) {
            for (const [campo, msg] of Object.entries(data.erros)) {
              const span = document.getElementById(`err-${campo}`);
              if (span) span.textContent = msg;
            }
          }
          // Limpa apenas a senha, preservando nome, e-mail, setor e perfil (Ponto 05)
          document.getElementById('cadSenha').value = '';
        }
      } catch (err) {
        exibirAlertaUsuario('erro', 'Erro de comunicação ao salvar usuário.');
      } finally {
        btn.disabled = false;
        btn.textContent = 'Salvar Usuário';
      }
    }

    function abrirModalExclusao(id, nome, email) {
      usuarioParaExcluirId = id;
      document.getElementById('modalUserInfo').innerHTML = `
        <strong>ID:</strong> #${id}<br>
        <strong>Nome:</strong> ${escaparHTML(nome)}<br>
        <strong>E-mail:</strong> ${escaparHTML(email)}
      `;
      document.getElementById('modalConfirmarExclusao').style.display = 'flex';
    }

    function fecharModalExclusao() {
      usuarioParaExcluirId = null;
      document.getElementById('modalConfirmarExclusao').style.display = 'none';
    }

    async function confirmarExclusaoUsuario() {
      if (!usuarioParaExcluirId) return;

      const btn = document.getElementById('btnConfirmarExclusao');
      btn.disabled = true;
      btn.textContent = 'Processando...';

      const formData = new FormData();
      formData.append('acao', 'excluir');
      formData.append('csrf_token', document.getElementById('csrfToken').value);
      formData.append('id', usuarioParaExcluirId);

      try {
        const res = await fetch('usuarios_api.php', { method: 'POST', body: formData });
        const data = await res.json();

        fecharModalExclusao();
        exibirAlertaUsuario(data.sucesso ? 'sucesso' : 'erro', data.mensagem);
        carregarUsuarios();
      } catch (err) {
        fecharModalExclusao();
        exibirAlertaUsuario('erro', 'Erro ao processar exclusão no servidor.');
      } finally {
        btn.disabled = false;
        btn.textContent = 'Confirmar Exclusão';
      }
    }

    <?php if ($estaLogado): ?>
      window.addEventListener('DOMContentLoaded', carregarUsuarios);
    <?php endif; ?>
  </script>
</body>
</html>
