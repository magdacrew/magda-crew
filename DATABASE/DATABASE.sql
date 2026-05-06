-- ==========================================
-- .TABELAS AUXILIARES E DE CADASTRO BASE
-- ==========================================
CREATE TABLE categorias (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL
);

INSERT INTO categorias(nome) VALUES 
('Camisetas'), ('Casacos'), ('Calças'), ('Bermudas'), ('Acessórios');

CREATE TABLE cores(
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(50) NOT NULL
);

INSERT INTO cores(nome) VALUES 
('Preto'), ('Branco'), ('Cinza'), ('Vermelho'), ('Azul'), ('Verde'), 
('Amarelo'), ('Rosa'), ('Roxo'), ('Bege'), ('Marrom'), ('Laranja');

CREATE TABLE tamanhos(
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(10) NOT NULL
);

INSERT INTO tamanhos(nome) VALUES
('PP'), ('P'), ('M'), ('G'), ('GG'), ('XGG'); 

-- ==========================================
-- .USUÁRIOS E ENDEREÇOS BASE
-- ==========================================
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome_completo VARCHAR(150) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    telefone VARCHAR(20),
    cpf VARCHAR(14) UNIQUE,
    nascimento DATE NOT NULL,
    senha_hash VARCHAR(255) NOT NULL,
    data_cadastro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    is_admin BOOLEAN NOT NULL DEFAULT FALSE,
    ativo BOOLEAN NOT NULL DEFAULT TRUE
);

CREATE TABLE enderecos_usuario (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    identificacao VARCHAR(50) DEFAULT 'Casa', -- Ex: Casa, Trabalho
    cep VARCHAR(10) NOT NULL,
    logradouro VARCHAR(255) NOT NULL,
    numero VARCHAR(20) NOT NULL,
    complemento VARCHAR(255),
    bairro VARCHAR(100) NOT NULL,
    cidade VARCHAR(100) NOT NULL,
    estado VARCHAR(2) NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- ==========================================
-- .CATÁLOGO DE PRODUTOS E VARIANTES
-- ==========================================
CREATE TABLE produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE,
    descricao TEXT,
    preco DECIMAL(10, 2) NOT NULL,
    categoria_id INT,
    destaque BOOLEAN NOT NULL DEFAULT FALSE,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id)
);

CREATE TABLE produto_imagens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    produto_id INT NOT NULL,
    caminho_imagem VARCHAR(255) NOT NULL,
    is_principal BOOLEAN DEFAULT FALSE,
    ordem INT DEFAULT 0,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
);



CREATE TABLE produto_variantes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    produto_id INT NOT NULL,
    tamanho_id INT NOT NULL,
    cor_id INT NOT NULL,
    quantidade_estoque INT NOT NULL DEFAULT 0,
    sku VARCHAR(50) UNIQUE,
    FOREIGN KEY (tamanho_id) REFERENCES tamanhos(id),
    FOREIGN KEY (cor_id) REFERENCES cores(id),
    FOREIGN KEY (produto_id) REFERENCES produtos(id),
    UNIQUE(produto_id, tamanho_id, cor_id)
);

-- ==========================================
-- .OPERACIONAL (CARRINHO E VENDAS)
-- ==========================================
CREATE TABLE carrinho (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NULL,
    session_id VARCHAR(255) NULL,
    variante_id INT NOT NULL,
    quantidade INT DEFAULT 1,
    data_adicao DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (variante_id) REFERENCES produto_variantes(id) ON DELETE CASCADE
);

CREATE TABLE vendas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    valor_total DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    valor_frete DECIMAL(10,2) NOT NULL,
    forma_pagamento VARCHAR(50) DEFAULT 'simulacao',
    frete_tipo VARCHAR(50),
    cpf_cnpj_nota VARCHAR(20),
    status VARCHAR(50) DEFAULT 'confirmado',
    data_venda DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE TABLE enderecos_venda (
    id INT PRIMARY KEY AUTO_INCREMENT,
    venda_id INT NOT NULL,
    cep VARCHAR(10),
    logradouro VARCHAR(255),
    numero VARCHAR(20),
    complemento VARCHAR(255),
    bairro VARCHAR(100),
    cidade VARCHAR(100),
    estado VARCHAR(2),
    destinatario VARCHAR(255),
    FOREIGN KEY (venda_id) REFERENCES vendas(id) ON DELETE CASCADE
);

CREATE TABLE itens_venda (
    id INT PRIMARY KEY AUTO_INCREMENT,
    venda_id INT NOT NULL,
    variante_id INT NULL,
    produto_nome VARCHAR(100) NOT NULL,
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    tamanho_nome VARCHAR(50),
    cor_nome VARCHAR(50),
    FOREIGN KEY (venda_id) REFERENCES vendas(id) ON DELETE CASCADE,
    FOREIGN KEY (variante_id) REFERENCES produto_variantes(id) ON DELETE SET NULL
);