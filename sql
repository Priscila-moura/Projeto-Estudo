CREATE DATABASE mercearia ;
USE mercearia;

CREATE TABLE setores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE perfis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;


CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    setor_id INT NOT NULL,
    perfil_id INT NOT NULL,
    status ENUM('Ativo', 'Baixa') NOT NULL DEFAULT 'Ativo',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    baixado_em DATETIME NULL DEFAULT NULL,
    CONSTRAINT fk_usuarios_setor FOREIGN KEY (setor_id) REFERENCES setores(id),
    CONSTRAINT fk_usuarios_perfil FOREIGN KEY (perfil_id) REFERENCES perfis(id)
) ENGINE=InnoDB;

CREATE TABLE auditoria_usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_alvo_id INT NOT NULL,
    acao VARCHAR(50) NOT NULL,
    autor VARCHAR(150) NOT NULL,
    data_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resultado VARCHAR(100) NOT NULL,
    CONSTRAINT fk_auditoria_usuario FOREIGN KEY (usuario_alvo_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    icone VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria_id INT NOT NULL,
    nome VARCHAR(150) NOT NULL,
    preco DECIMAL(10, 2) DEFAULT 0.00,
    estoque INT DEFAULT 10,
    CONSTRAINT fk_produtos_categoria FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO setores (id, nome, ativo) VALUES
(1, 'Atendimento & Caixa', 1),
(2, 'Estoque & Hortifruti', 1),
(3, 'Administrativo', 1),
(4, 'Setor Inativo Teste', 0);

INSERT INTO perfis (id, nome, ativo) VALUES
(1, 'Cliente', 1),
(2, 'Operador de Caixa', 1),
(3, 'Gerente', 1),
(4, 'Perfil Inativo Teste', 0);

-- Senha padrão de todos os usuários fictícios abaixo: 1234
INSERT INTO usuarios (nome, email, senha_hash, setor_id, perfil_id, status) VALUES
('Ana Clara Silva', 'ana.silva@mercearia.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 3, 'Ativo'),
('Carlos Eduardo Santos', 'carlos.santos@mercearia.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 2, 'Ativo'),
('Mariana Oliveira', 'mariana.cli@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 1, 'Ativo'),
('João Pedro Baixado', 'joao.antigo@mercearia.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 2, 'Baixa');

INSERT INTO auditoria_usuarios (usuario_alvo_id, acao, autor, resultado) VALUES
(1, 'CADASTRO_INICIAL', 'Sistema (Seed)', 'Sucesso'),
(4, 'EXCLUSAO_LOGICA_BAIXA', 'Sistema (Seed)', 'Sucesso - Status alterado para Baixa');

INSERT INTO categorias (id, nome, icone) VALUES 
(1, 'FRUTAS', '🍎🍌🍇'),
(2, 'CEREAIS', '🫘'),
(3, 'Laticinios & Queijos', '🧀');

INSERT INTO produtos (categoria_id, nome, preco, estoque) VALUES 
(1, 'MAÇÃ', 8.99, 25),
(1, 'PERA', 11.50, 18),
(1, 'UVA', 9.90, 20),
(1, 'BANANA', 5.49, 30),
(2, 'Arroz', 6.29, 40),
(2, 'Feijão', 7.89, 35),
(2, 'Leite', 4.99, 50),
(2, 'Macarrão', 3.79, 45),
(3, 'Queijo Coalho', 24.90, 15),
(3, 'Queijo Mussarela', 32.50, 12),
(3, 'Manteiga Artesanal', 14.90, 20),
(3, 'Requeijão de Corte', 16.50, 18);
