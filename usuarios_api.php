<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'conexao.php';


if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$acao = $_REQUEST['acao'] ?? '';


if ($acao === 'listar') {
    $busca = trim($_GET['busca'] ?? '');

    $sql = "SELECT 
                u.id,
                u.nome,
                u.email,
                u.status,
                u.criado_em,
                s.nome AS setor_nome,
                p.nome AS perfil_nome
            FROM usuarios u
            INNER JOIN setores s ON u.setor_id = s.id
            INNER JOIN perfis p  ON u.perfil_id = p.id
            WHERE u.status = 'Ativo'";

    $params = [];

    if ($busca !== '') {
        $sql .= " AND (u.nome LIKE :busca_nome OR u.email LIKE :busca_email)";
        $params[':busca_nome']  = '%' . $busca . '%';
        $params[':busca_email'] = '%' . $busca . '%';
    }

    $sql .= " ORDER BY u.id DESC";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $usuarios = $stmt->fetchAll();

       
        $stmtAud = $pdo->query("SELECT id, usuario_alvo_id, acao, autor, data_hora, resultado FROM auditoria_usuarios ORDER BY id DESC LIMIT 10");
        $auditoria = $stmtAud->fetchAll();

        echo json_encode([
            'sucesso'   => true,
            'usuarios'  => $usuarios,
            'auditoria' => $auditoria
        ]);
    } catch (PDOException $e) {
        error_log('Erro listar usuarios: ' . $e->getMessage());
        echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao consultar usuarios no banco de dados.']);
    }
    exit;
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Metodo nao permitido. Utilize POST.']);
    exit;
}

$csrfRecebido = $_POST['csrf_token'] ?? '';
if (!hash_equals($_SESSION['csrf_token'], $csrfRecebido)) {
    http_response_code(403);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Falha de segurança (Token CSRF inválido). Atualize a página.']);
    exit;
}

$autorOperacao = $_SESSION['usuario'] ?? 'Operador nao identificado';


if ($acao === 'cadastrar') {
    $nome     = trim($_POST['nome'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $senha    = $_POST['senha'] ?? '';
    $setorId  = filter_input(INPUT_POST, 'setor_id', FILTER_VALIDATE_INT);
    $perfilId = filter_input(INPUT_POST, 'perfil_id', FILTER_VALIDATE_INT);

    $erros = [];

    if ($nome === '' || mb_strlen($nome) < 3) {
        $erros['nome'] = 'Informe o nome completo (minimo 3 caracteres).';
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros['email'] = 'Informe um endereco de e-mail valido.';
    }

    if ($senha === '' || strlen($senha) < 4) {
        $erros['senha'] = 'A senha deve conter no minimo 4 caracteres.';
    }

    if (!$setorId || $setorId <= 0) {
        $erros['setor_id'] = 'Selecione um setor valido.';
    }

    if (!$perfilId || $perfilId <= 0) {
        $erros['perfil_id'] = 'Selecione um perfil valido.';
    }

    
    if (empty($erros['setor_id'])) {
        $stmtSetor = $pdo->prepare("SELECT id FROM setores WHERE id = :id AND ativo = 1 LIMIT 1");
        $stmtSetor->execute([':id' => $setorId]);
        if (!$stmtSetor->fetch()) {
            $erros['setor_id'] = 'O setor selecionado nao existe ou esta inativo.';
        }
    }

    if (empty($erros['perfil_id'])) {
        $stmtPerfil = $pdo->prepare("SELECT id FROM perfis WHERE id = :id AND ativo = 1 LIMIT 1");
        $stmtPerfil->execute([':id' => $perfilId]);
        if (!$stmtPerfil->fetch()) {
            $erros['perfil_id'] = 'O perfil selecionado nao existe ou esta inativo.';
        }
    }

   
    if (empty($erros['email'])) {
        $stmtDup = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email LIMIT 1");
        $stmtDup->execute([':email' => $email]);
        if ($stmtDup->fetch()) {
            $erros['email'] = 'Este e-mail ja esta cadastrado no sistema.';
        }
    }

    if (!empty($erros)) {
        echo json_encode([
            'sucesso'  => false,
            'mensagem' => 'Corrija os campos destacados antes de salvar.',
            'erros'    => $erros
        ]);
        exit;
    }

    try {
        $pdo->beginTransaction();

        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        $stmtIns = $pdo->prepare("
            INSERT INTO usuarios (nome, email, senha_hash, setor_id, perfil_id, status)
            VALUES (:nome, :email, :senha_hash, :setor_id, :perfil_id, 'Ativo')
        ");
        $stmtIns->execute([
            ':nome'       => $nome,
            ':email'      => $email,
            ':senha_hash' => $senhaHash,
            ':setor_id'   => $setorId,
            ':perfil_id'  => $perfilId
        ]);

        $novoId = (int) $pdo->lastInsertId();

        
        $stmtAud = $pdo->prepare("
            INSERT INTO auditoria_usuarios (usuario_alvo_id, acao, autor, resultado)
            VALUES (:alvo, 'CADASTRO_USUARIO', :autor, 'Sucesso')
        ");
        $stmtAud->execute([
            ':alvo'  => $novoId,
            ':autor' => $autorOperacao
        ]);

        $pdo->commit();

        echo json_encode([
            'sucesso'  => true,
            'mensagem' => 'Usuario cadastrado e persistido no MySQL com sucesso!'
        ]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log('Erro ao inserir usuraio: ' . $e->getMessage());
        echo json_encode([
            'sucesso'  => false,
            'mensagem' => 'Não foi possivel concluir o cadastro no momento.'
        ]);
    }
    exit;
}


if ($acao === 'excluir') {
    $idAlvo = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if (!$idAlvo || $idAlvo <= 0) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Identificador de usuário inválido.']);
        exit;
    }

    try {
        $pdo->beginTransaction();

       
        $stmtCheck = $pdo->prepare("SELECT id, nome, status FROM usuarios WHERE id = :id LIMIT 1 FOR UPDATE");
        $stmtCheck->execute([':id' => $idAlvo]);
        $usuario = $stmtCheck->fetch();

        if (!$usuario) {
            $pdo->rollBack();
            echo json_encode(['sucesso' => false, 'mensagem' => 'Usuário não encontrado no banco de dados.']);
            exit;
        }

        if ($usuario['status'] === 'Baixa') {
            $pdo->rollBack();
            echo json_encode(['sucesso' => false, 'mensagem' => 'Este usuário já recebeu Baixa anteriormente.']);
            exit;
        }

        
        $stmtBaixa = $pdo->prepare("
            UPDATE usuarios 
            SET status = 'Baixa', baixado_em = NOW() 
            WHERE id = :id
        ");
        $stmtBaixa->execute([':id' => $idAlvo]);

        // Registra na Auditoria
        $stmtAud = $pdo->prepare("
            INSERT INTO auditoria_usuarios (usuario_alvo_id, acao, autor, resultado)
            VALUES (:alvo, 'EXCLUSAO_LOGICA_BAIXA', :autor, 'Sucesso - Status alterado para Baixa')
        ");
        $stmtAud->execute([
            ':alvo'  => $idAlvo,
            ':autor' => $autorOperacao
        ]);

        $pdo->commit();

        echo json_encode([
            'sucesso'  => true,
            'mensagem' => "Usuario \"{$usuario['nome']}\" recebeu Baixa com sucesso."
        ]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log('Erro na baixa de usuario: ' . $e->getMessage());
        echo json_encode(['sucesso' => false, 'mensagem' => 'Falha ao processar a exclusao no servidor.']);
    }
    exit;
}

echo json_encode(['sucesso' => false, 'mensagem' => 'Operacao desconhecida.']);
