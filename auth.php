<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'conexao.php';

$acao          = $_POST['acao'] ?? '';
$identificador = trim($_POST['identificador'] ?? '');
$senha         = $_POST['senha'] ?? '';

if ($acao === 'logout') {
    session_destroy();
    echo json_encode(['sucesso' => true]);
    exit;
}

if (empty($identificador) || empty($senha)) {
    echo json_encode([
        'sucesso'  => false,
        'mensagem' => 'Preencha todos os campos.'
    ]);
    exit;
}

// --- CADASTRO ---
if ($acao === 'cadastro') {
    if (strlen($senha) < 4) {
        echo json_encode([
            'sucesso'  => false,
            'mensagem' => 'A senha deve ter pelo menos 4 caracteres.'
        ]);
        exit;
    }

    
    $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE identificador = :id LIMIT 1');
    $stmt->execute([':id' => $identificador]);

    if ($stmt->fetch()) {
        echo json_encode([
            'sucesso'  => false,
            'mensagem' => 'Este E-mail ou CPF já possui cadastro.'
        ]);
        exit;
    }

    
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO usuarios (identificador, senha_hash) VALUES (:id, :hash)');
    $stmt->execute([
        ':id'   => $identificador,
        ':hash' => $senhaHash
    ]);

    $_SESSION['usuario_id'] = (int) $pdo->lastInsertId();
    $_SESSION['usuario']    = $identificador;

    echo json_encode([
        'sucesso'  => true,
        'mensagem' => 'Cadastro realizado com sucesso!'
    ]);
    exit;
}


if ($acao === 'login') {
    $stmt = $pdo->prepare('SELECT id, senha_hash FROM usuarios WHERE identificador = :id LIMIT 1');
    $stmt->execute([':id' => $identificador]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Bloqueia o acesso se o usuário não existir ou se a senha estiver incorreta
    if (!$usuario || !password_verify($senha, $usuario['senha_hash'])) {
        echo json_encode([
            'sucesso'  => false,
            'mensagem' => 'E-mail/CPF ou senha inválidos. Tente novamente.'
        ]);
        exit;
    }

    $_SESSION['usuario_id'] = (int) $usuario['id'];
    $_SESSION['usuario']    = $identificador;

    echo json_encode([
        'sucesso'  => true,
        'mensagem' => 'Login realizado com sucesso!'
    ]);
    exit;
}

echo json_encode(['sucesso' => false, 'mensagem' => 'Ação inválida.']);
