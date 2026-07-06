-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 06/07/2026 às 17:12
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `magda_crew`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `carrinho`
--

CREATE TABLE `carrinho` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `variante_id` int(11) NOT NULL,
  `quantidade` int(11) DEFAULT 1,
  `data_adicao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `carrinho`
--

INSERT INTO `carrinho` (`id`, `usuario_id`, `session_id`, `variante_id`, `quantidade`, `data_adicao`) VALUES
(7, NULL, 'avrdqc09m757k17jb768g1gajd', 5, 1, '2026-05-27 19:41:51'),
(8, NULL, 'avrdqc09m757k17jb768g1gajd', 4, 2, '2026-05-28 18:57:19'),
(9, NULL, 'avrdqc09m757k17jb768g1gajd', 8, 2, '2026-05-28 18:57:55'),
(10, NULL, 'nbc63qf75sp7m7so3h0msu36rl', 8, 1, '2026-05-28 19:04:56'),
(19, 2, 'aitbc0p9bike1nd67suobhg6up', 10, 1, '2026-06-02 21:23:41'),
(27, 1, '7r1edg4f74r3lncrci9t4a6h46', 14, 1, '2026-07-04 18:04:08'),
(28, 1, '7r1edg4f74r3lncrci9t4a6h46', 9, 1, '2026-07-04 18:04:56');

-- --------------------------------------------------------

--
-- Estrutura para tabela `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `ativo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `categorias`
--

INSERT INTO `categorias` (`id`, `nome`, `ativo`) VALUES
(1, 'Tudo', 1),
(2, 'Camisetas', 1),
(3, 'Casacos', 1),
(4, 'Calças', 1),
(5, 'Bermudas', 1),
(6, 'Acessórios', 1),
(7, 'Tênis', 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `cores`
--

CREATE TABLE `cores` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `cores`
--

INSERT INTO `cores` (`id`, `nome`) VALUES
(1, 'Preto'),
(2, 'Branco'),
(3, 'Cinza'),
(4, 'Vermelho'),
(5, 'Azul'),
(6, 'Verde'),
(7, 'Amarelo'),
(8, 'Rosa'),
(9, 'Roxo'),
(10, 'Bege'),
(11, 'Marrom'),
(12, 'Laranja');

-- --------------------------------------------------------

--
-- Estrutura para tabela `enderecos`
--

CREATE TABLE `enderecos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `pais` varchar(100) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `sobrenome` varchar(100) NOT NULL,
  `cep` varchar(10) NOT NULL,
  `endereco` varchar(255) NOT NULL,
  `complemento` varchar(100) DEFAULT NULL,
  `cidade` varchar(100) NOT NULL,
  `estado` varchar(2) NOT NULL,
  `telefone` varchar(20) NOT NULL,
  `padrao` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `enderecos`
--

INSERT INTO `enderecos` (`id`, `usuario_id`, `pais`, `nome`, `sobrenome`, `cep`, `endereco`, `complemento`, `cidade`, `estado`, `telefone`, `padrao`) VALUES
(2, 2, 'Brasil', 'Vitor', 'Souza', '89224-381', 'Rua aristides rudnick, 977', 'Casa', 'Joinville', 'SC', '(47) 99637-1550', 1),
(5, 3, 'Brasil', 'Gustavo', 'Fabiam', '89228-160', '284', 'Casa', 'Joinville', 'SC', '(47) 99649-0220', 1),
(6, 5, 'Brasil', 'Gustavo ', 'Fabiam', '89228-160', '284', 'Casa', 'Joinville', 'SC', '(47) 99649-0220', 1),
(7, 6, 'Brasil', 'Erick', 'Schwaab', '89223-660', 'rua brusque 199, jardim sofia', 'fundos', 'Joinville', 'SC', '(47) 99726-3311', 1),
(8, 1, 'Brasil', 'Gabriel ', 'Holz', '89223-600', 'Rua egon beling 42', 'casa', 'Joinville', 'SC', '(47) 99996-7080', 1),
(9, 4, '', 'Joao Emanoel Rocha', '', '89223405', 'Rua Astra Uran', 'casa', 'Joinville', 'SC', '47999605996', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `enderecos_usuario`
--

CREATE TABLE `enderecos_usuario` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `identificacao` varchar(50) DEFAULT 'Casa',
  `cep` varchar(10) NOT NULL,
  `logradouro` varchar(255) NOT NULL,
  `numero` varchar(20) NOT NULL,
  `complemento` varchar(255) DEFAULT NULL,
  `bairro` varchar(100) NOT NULL,
  `cidade` varchar(100) NOT NULL,
  `estado` varchar(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `enderecos_venda`
--

CREATE TABLE `enderecos_venda` (
  `id` int(11) NOT NULL,
  `venda_id` int(11) NOT NULL,
  `cep` varchar(10) DEFAULT NULL,
  `logradouro` varchar(255) DEFAULT NULL,
  `numero` varchar(20) DEFAULT NULL,
  `complemento` varchar(255) DEFAULT NULL,
  `bairro` varchar(100) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `estado` varchar(2) DEFAULT NULL,
  `destinatario` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `enderecos_venda`
--

INSERT INTO `enderecos_venda` (`id`, `venda_id`, `cep`, `logradouro`, `numero`, `complemento`, `bairro`, `cidade`, `estado`, `destinatario`) VALUES
(1, 3, '60000-400', 'R aristides rudnick, 977', '170', 'casa', 'jardin sofia', 'joinville', 'SC', 'Gabriel Holz'),
(2, 4, '60000-400', 'R aristides rudnick, 977', '170', 'casa', 'jardin sofia', 'joinville', 'SC', 'Gabriel Holz'),
(3, 5, '89223-660', 'rua brusque', '199', 'casa', 'jardin sofia', 'Joinville', 'SC', 'Gabriel Holz'),
(4, 6, '89223405', 'Rua Astra Uran', '271', 'casa', 'Jardim Sofia', 'Joinville', 'SC', 'Joao Emanoel Rocha');

-- --------------------------------------------------------

--
-- Estrutura para tabela `home_banners`
--

CREATE TABLE `home_banners` (
  `id` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `texto_botao` varchar(50) NOT NULL DEFAULT 'Compre Agora',
  `link_botao` varchar(255) NOT NULL DEFAULT '#',
  `imagem_fundo` varchar(255) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `ordem` int(11) NOT NULL DEFAULT 0,
  `tipo` varchar(30) NOT NULL DEFAULT 'hero_topo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `home_banners`
--

INSERT INTO `home_banners` (`id`, `titulo`, `texto_botao`, `link_botao`, `imagem_fundo`, `ativo`, `ordem`, `tipo`) VALUES
(1, 'FALL ’26 COLLECTION ©', 'Compre Agora', 'shop.php', '/MagdaCrew/public/assets/images/background3.png', 1, 1, 'hero_topo'),
(2, 'ROMANTIC ’26 ©', 'Compre Agora', 'shop.php', '/MagdaCrew/public/assets/images/background2.png', 1, 2, 'hero_topo'),
(5, 'VAMPETA’26 | T-SHIRTS', 'Explore Agora', '/MagdaCrew/public/produtos', '/MagdaCrew/public/assets/images/banners/banner_5_1780445932_6a1f72ec438fb.png', 0, 1, 'banner_baixo'),
(8, 'FALL COLLETION', 'Compre Agora', '/MagdaCrew/public/produtos', '/MagdaCrew/public/assets/images/banners/banner_novo_1780526313_6a20ace93721d.jpg', 1, 1, 'banner_baixo');

-- --------------------------------------------------------

--
-- Estrutura para tabela `itens_venda`
--

CREATE TABLE `itens_venda` (
  `id` int(11) NOT NULL,
  `venda_id` int(11) NOT NULL,
  `variante_id` int(11) DEFAULT NULL,
  `produto_nome` varchar(100) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `preco_unitario` decimal(10,2) NOT NULL,
  `tamanho_nome` varchar(50) DEFAULT NULL,
  `cor_nome` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `itens_venda`
--

INSERT INTO `itens_venda` (`id`, `venda_id`, `variante_id`, `produto_nome`, `quantidade`, `preco_unitario`, `tamanho_nome`, `cor_nome`) VALUES
(1, 1, NULL, 'teste', 1, 189.00, 'M', '1'),
(5, 2, NULL, 'Magda Art Burgundy Tee', 1, 179.00, 'M', '1'),
(6, 2, NULL, 'Magda Art Burgundy Tee', 2, 179.00, 'M', '1'),
(7, 3, 14, 'Calça Baggy Moletom MgDrop', 1, 165.00, 'P', 'Cinza'),
(8, 4, 14, 'Calça Baggy Moletom MgDrop', 1, 165.00, 'P', 'Cinza'),
(9, 5, 14, 'Calça Baggy Moletom MgDrop', 1, 165.00, 'P', 'Cinza'),
(10, 6, 15, 'Calça Baggy Moletom MgDrop', 1, 165.00, 'G', 'Cinza');

-- --------------------------------------------------------

--
-- Estrutura para tabela `produtos`
--

CREATE TABLE `produtos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `preco` decimal(10,2) NOT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `destaque` tinyint(1) NOT NULL DEFAULT 0,
  `ativo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `produtos`
--

INSERT INTO `produtos` (`id`, `nome`, `descricao`, `preco`, `categoria_id`, `destaque`, `ativo`) VALUES
(1, 'Magda Crew Tee', 'A camiseta Magda Crew Tee é confeccionada em malha de alta gramatura, um tecido encorpado que proporciona um caimento estruturado e toque macio, garantindo uma experiência de uso confortável e durável. O produto apresenta modelagem ampla com foco em uma silhueta moderna e urbana. O destaque da peça reside no bordado lateral vertical \"Magda Crew\", executado em caligrafia clássica com fios de alta densidade, que confere relevo, brilho sutil e um acabamento premium à peça. O design é finalizado com gola em ribana robusta e costuras reforçadas, unindo a resistência do streetwear à sofisticação do detalhe bordado.', 189.90, 2, 0, 1),
(2, 'Magda Art Burgundy Tee', 'A camiseta Magda Art Burgundy Tee é confeccionada em malha premium de alta gramatura, proporcionando um toque macio e um caimento estruturado que valoriza a silhueta. O produto apresenta modelagem oversized, garantindo máximo conforto e uma estética urbana refinada. O destaque central da peça é a estampa frontal, que sobrepõe a tipografia \"MAGDA\" em tom bordô sobre uma obra de arte clássica, criando um contraste visual entre o tradicional e o contemporâneo. Com gola em ribana robusta e acabamento de alta qualidade, a peça une a resistência do streetwear a uma proposta artística exclusiva.', 179.90, 2, 1, 1),
(3, 'Magda Fiedelner Tee', 'A camiseta Magda Fiedelner Tee é confeccionada em malha premium de alta gramatura, proporcionando um toque macio, experiência de uso confortável e um caimento estruturado de alto padrão. O produto apresenta modelagem oversized, unindo estética moderna à durabilidade. Na parte frontal, a peça exibe uma estampa artística em formato oval com a obra \"Autorretrato com a Morte Tocando Violino\" (Fiedelnder Tod), sobrepondo a tipografia \"MAGDA\" ao simbolismo clássico de Arnold Böcklin. O diferencial se estende às costas, que trazem a frase \"Life is like a song, death is just waiting for it to end\" em um bordado de alta precisão com linha cinza claro, conferindo relevo e sofisticação ao conceito memento mori da peça. Finalizada com gola em ribana robusta, é uma peça que transita entre o streetwear e a expressão artística profunda.', 199.90, 2, 0, 1),
(4, 'Magda Signature Black Tee', 'A camiseta Magda Signature Black Tee é confeccionada em malha premium de alta gramatura, proporcionando um toque macio, experiência de uso confortável e um caimento estruturado de alto padrão. O produto apresenta modelagem oversized, equilibrando a estética robusta do streetwear com a sofisticação de detalhes minimalistas. Na parte frontal, a peça exibe uma estampa artística lateralizada em alto contraste, trazendo um retrato expressivo que remete à introspecção e ao estilo clássico. O diferencial se completa nas costas com a assinatura \"Magda\" em um bordado de alta precisão com linha branca, conferindo relevo e um acabamento exclusivo à peça. Finalizada com gola em ribana robusta e costuras reforçadas, é uma peça indispensável para quem busca design autoral e qualidade superior.', 189.90, 2, 1, 1),
(7, 'Magda Essential Red Tee', 'A camiseta Magda Essential Red Tee é confeccionada em malha premium de alta gramatura, proporcionando um toque macio, experiência de uso confortável e um caimento estruturado de alto padrão. O produto apresenta modelagem oversized, garantindo uma estética minimalista e urbana com máxima durabilidade. O grande destaque da peça é o bordado frontal centralizado \"MAGDA\" em tom vermelho vibrante, executado com fios de alta densidade que conferem relevo e um contraste marcante sobre o preto profundo da malha. Com gola em ribana robusta e acabamento reforçado, é uma peça-chave que une a simplicidade do design essencial à sofisticação do detalhe bordado.', 178.87, 2, 1, 1),
(8, 'Biel holz', 'Biel', 1000.00, 7, 0, 0),
(9, 'Calça Baggy Moletom MgDrop', 'TESTE', 165.00, 4, 1, 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `produto_imagens`
--

CREATE TABLE `produto_imagens` (
  `id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `caminho_imagem` varchar(255) NOT NULL,
  `is_principal` tinyint(1) DEFAULT 0,
  `ordem` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `produto_imagens`
--

INSERT INTO `produto_imagens` (`id`, `produto_id`, `caminho_imagem`, `is_principal`, `ordem`) VALUES
(1, 1, 'public/assets/images/produtos/1779750230_9.jpg', 1, 0),
(2, 1, 'public/assets/images/produtos/1779750230_10.jpg', 0, 1),
(3, 2, 'public/assets/images/produtos/1779750411_2.jpg', 0, 0),
(4, 2, 'public/assets/images/produtos/1779750411_1.jpg', 1, 1),
(5, 3, 'public/assets/images/produtos/1779750530_4.jpg', 0, 0),
(6, 3, 'public/assets/images/produtos/1779750530_3.jpg', 1, 1),
(7, 4, 'public/assets/images/produtos/1779750582_6.jpg', 0, 0),
(8, 4, 'public/assets/images/produtos/1779750582_5.jpg', 1, 1),
(18, 7, 'public/assets/images/produtos/1779755327_7.jpg', 1, 1),
(19, 7, 'public/assets/images/produtos/1779755931_8.jpg', 0, 0),
(20, 7, 'public/assets/images/produtos/1780011848_modelo.png', 0, 0),
(22, 2, 'public/assets/images/produtos/1780012601_modelo2.png', 0, 0),
(23, 3, 'public/assets/images/produtos/1780012648_modelo2.png', 0, 0),
(24, 8, 'public/assets/images/produtos/1780446143_daaa45aef94d10741aec483a2d81d04b.jpg', 1, 0),
(25, 8, 'public/assets/images/produtos/1780446143_vampeta.jpeg', 0, 1),
(27, 8, 'public/assets/images/produtos/1780446143_Perdeu aura.jpg', 0, 3),
(28, 8, 'public/assets/images/produtos/1780446671_#figurinhas#memes.jpg', 0, 0),
(29, 8, 'public/assets/images/produtos/1780446671_cleitin.jpg', 0, 1),
(30, 9, 'public/assets/images/produtos/1783191376_baggymoletommagda.jpg', 1, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `produto_variantes`
--

CREATE TABLE `produto_variantes` (
  `id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `tamanho_id` int(11) NOT NULL,
  `cor_id` int(11) NOT NULL,
  `quantidade_estoque` int(11) NOT NULL DEFAULT 0,
  `sku` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `produto_variantes`
--

INSERT INTO `produto_variantes` (`id`, `produto_id`, `tamanho_id`, `cor_id`, `quantidade_estoque`, `sku`) VALUES
(4, 2, 1, 4, 10, NULL),
(5, 2, 2, 4, 15, NULL),
(6, 2, 4, 4, 5, NULL),
(7, 1, 1, 1, 10, NULL),
(8, 1, 3, 1, 18, NULL),
(9, 4, 5, 1, 10, NULL),
(10, 8, 2, 8, 4, NULL),
(11, 8, 3, 8, 12, NULL),
(12, 3, 6, 2, 99, NULL),
(13, 9, 3, 3, 20, NULL),
(14, 9, 2, 3, 17, NULL),
(15, 9, 4, 3, 44, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `tamanhos`
--

CREATE TABLE `tamanhos` (
  `id` int(11) NOT NULL,
  `nome` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tamanhos`
--

INSERT INTO `tamanhos` (`id`, `nome`) VALUES
(1, 'PP'),
(2, 'P'),
(3, 'M'),
(4, 'G'),
(5, 'GG'),
(6, 'XGG');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome_completo` varchar(150) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `cpf` varchar(14) DEFAULT NULL,
  `nascimento` date DEFAULT NULL,
  `codigo_login` varchar(10) DEFAULT NULL,
  `codigo_expira` datetime DEFAULT NULL,
  `email_verificado` tinyint(1) NOT NULL DEFAULT 0,
  `ultimo_login` datetime DEFAULT NULL,
  `data_cadastro` datetime NOT NULL DEFAULT current_timestamp(),
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `ativo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome_completo`, `email`, `telefone`, `cpf`, `nascimento`, `codigo_login`, `codigo_expira`, `email_verificado`, `ultimo_login`, `data_cadastro`, `is_admin`, `ativo`) VALUES
(1, NULL, 'gabrielholz422@gmail.com', NULL, NULL, NULL, NULL, NULL, 1, '2026-07-04 16:41:27', '2026-05-25 19:51:23', 1, 1),
(2, NULL, 'vitordesouza1903@gmail.com', NULL, NULL, NULL, NULL, NULL, 1, '2026-06-01 19:23:47', '2026-05-26 19:14:00', 1, 1),
(3, NULL, 'gustavo_fabiam@estudante.sesisenai.org.br', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-27 19:31:04', '2026-05-27 19:30:44', 0, 1),
(4, NULL, 'holzadrian8@gmail.com', NULL, NULL, NULL, NULL, NULL, 1, '2026-07-04 15:37:29', '2026-05-27 21:37:40', 1, 1),
(5, NULL, 'gustavojoaquimfabiam@gmail.com', NULL, NULL, NULL, NULL, NULL, 1, '2026-07-04 15:53:04', '2026-06-03 21:08:56', 1, 1),
(6, NULL, 'schwaaberick@gmail.com', NULL, NULL, NULL, NULL, NULL, 1, '2026-06-04 01:16:28', '2026-06-04 01:16:11', 0, 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `vendas`
--

CREATE TABLE `vendas` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `valor_total` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `valor_frete` decimal(10,2) NOT NULL,
  `forma_pagamento` varchar(50) DEFAULT 'simulacao',
  `frete_tipo` varchar(50) DEFAULT NULL,
  `cpf_cnpj_nota` varchar(20) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'confirmado',
  `data_venda` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `vendas`
--

INSERT INTO `vendas` (`id`, `usuario_id`, `valor_total`, `subtotal`, `valor_frete`, `forma_pagamento`, `frete_tipo`, `cpf_cnpj_nota`, `status`, `data_venda`) VALUES
(1, 1, 101.00, 20.00, 10.00, 'simulacao', 'sedex', '123.456.789.40', 'cancelado', '2026-05-26 19:26:44'),
(2, 2, 100.00, 100.00, 50.00, 'teste', 'sedex', '123.456.789.40', 'enviado', '2026-05-26 19:47:14'),
(3, 1, 184.90, 165.00, 19.90, 'pix', 'pac', '', 'entregue', '2026-07-04 17:01:30'),
(4, 1, 184.90, 165.00, 19.90, 'dinheiro', 'pac', '', 'entregue', '2026-07-04 17:49:07'),
(5, 1, 184.90, 165.00, 19.90, 'pix', 'pac', '', 'pendente', '2026-07-04 17:57:32'),
(6, 4, 184.90, 165.00, 19.90, 'pix', 'pac', '', 'pendente', '2026-07-04 20:15:34');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `carrinho`
--
ALTER TABLE `carrinho`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `variante_id` (`variante_id`);

--
-- Índices de tabela `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `cores`
--
ALTER TABLE `cores`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `enderecos`
--
ALTER TABLE `enderecos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `enderecos_usuario`
--
ALTER TABLE `enderecos_usuario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `enderecos_venda`
--
ALTER TABLE `enderecos_venda`
  ADD PRIMARY KEY (`id`),
  ADD KEY `venda_id` (`venda_id`);

--
-- Índices de tabela `home_banners`
--
ALTER TABLE `home_banners`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `itens_venda`
--
ALTER TABLE `itens_venda`
  ADD PRIMARY KEY (`id`),
  ADD KEY `venda_id` (`venda_id`),
  ADD KEY `variante_id` (`variante_id`);

--
-- Índices de tabela `produtos`
--
ALTER TABLE `produtos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome` (`nome`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Índices de tabela `produto_imagens`
--
ALTER TABLE `produto_imagens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `produto_id` (`produto_id`);

--
-- Índices de tabela `produto_variantes`
--
ALTER TABLE `produto_variantes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `produto_id` (`produto_id`,`tamanho_id`,`cor_id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `tamanho_id` (`tamanho_id`),
  ADD KEY `cor_id` (`cor_id`);

--
-- Índices de tabela `tamanhos`
--
ALTER TABLE `tamanhos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `cpf` (`cpf`);

--
-- Índices de tabela `vendas`
--
ALTER TABLE `vendas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `carrinho`
--
ALTER TABLE `carrinho`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de tabela `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `cores`
--
ALTER TABLE `cores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `enderecos`
--
ALTER TABLE `enderecos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `enderecos_usuario`
--
ALTER TABLE `enderecos_usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `enderecos_venda`
--
ALTER TABLE `enderecos_venda`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `home_banners`
--
ALTER TABLE `home_banners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `itens_venda`
--
ALTER TABLE `itens_venda`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `produto_imagens`
--
ALTER TABLE `produto_imagens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de tabela `produto_variantes`
--
ALTER TABLE `produto_variantes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de tabela `tamanhos`
--
ALTER TABLE `tamanhos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `vendas`
--
ALTER TABLE `vendas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `carrinho`
--
ALTER TABLE `carrinho`
  ADD CONSTRAINT `carrinho_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `carrinho_ibfk_2` FOREIGN KEY (`variante_id`) REFERENCES `produto_variantes` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `enderecos_usuario`
--
ALTER TABLE `enderecos_usuario`
  ADD CONSTRAINT `enderecos_usuario_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `enderecos_venda`
--
ALTER TABLE `enderecos_venda`
  ADD CONSTRAINT `enderecos_venda_ibfk_1` FOREIGN KEY (`venda_id`) REFERENCES `vendas` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `itens_venda`
--
ALTER TABLE `itens_venda`
  ADD CONSTRAINT `itens_venda_ibfk_1` FOREIGN KEY (`venda_id`) REFERENCES `vendas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `itens_venda_ibfk_2` FOREIGN KEY (`variante_id`) REFERENCES `produto_variantes` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `produtos`
--
ALTER TABLE `produtos`
  ADD CONSTRAINT `produtos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`);

--
-- Restrições para tabelas `produto_imagens`
--
ALTER TABLE `produto_imagens`
  ADD CONSTRAINT `produto_imagens_ibfk_1` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `produto_variantes`
--
ALTER TABLE `produto_variantes`
  ADD CONSTRAINT `produto_variantes_ibfk_1` FOREIGN KEY (`tamanho_id`) REFERENCES `tamanhos` (`id`),
  ADD CONSTRAINT `produto_variantes_ibfk_2` FOREIGN KEY (`cor_id`) REFERENCES `cores` (`id`),
  ADD CONSTRAINT `produto_variantes_ibfk_3` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`);

--
-- Restrições para tabelas `vendas`
--
ALTER TABLE `vendas`
  ADD CONSTRAINT `vendas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
