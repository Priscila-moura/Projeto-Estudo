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
        errorBox.textContent = 'Erro de comunicacao com o servidor.';
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
        errorBox.textContent = 'Erro de comunicacao com o servidor.';
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
        exibirAlertaUsuario('erro', 'Erro de comunicacao ao salvar usuario.');
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
        exibirAlertaUsuario('erro', 'Erro ao processar exclusao no servidor.');
      } finally {
        btn.disabled = false;
        btn.textContent = 'Confirmar Exclusao';
      }
    }

   
