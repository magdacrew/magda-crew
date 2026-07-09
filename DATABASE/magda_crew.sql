-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 09/07/2026 às 17:38
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
CREATE DATABASE IF NOT EXISTS `magda_crew` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `magda_crew`;

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
(25, NULL, 'm8sfdpdhkru2ae52kt4rbm5s7n', 4, 1, '2026-06-03 21:41:42'),
(29, NULL, 'lbtrre5dagusa4hkjf5addj42m', 7, 1, '2026-07-02 19:36:01'),
(30, NULL, 'lbtrre5dagusa4hkjf5addj42m', 8, 1, '2026-07-02 19:36:08'),
(76, 4, 'kem35r7v4tshqbrejku0a1qjv4', 38, 1, '2026-07-07 22:34:53'),
(89, 4, 'p560lsi7lgbl4haeceqiq1u5vu', 36, 1, '2026-07-08 22:21:31');

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
  `numero` varchar(30) DEFAULT NULL,
  `complemento` varchar(100) DEFAULT NULL,
  `bairro` varchar(120) DEFAULT NULL,
  `cidade` varchar(100) NOT NULL,
  `estado` varchar(2) NOT NULL,
  `telefone` varchar(20) NOT NULL,
  `padrao` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `enderecos`
--

INSERT INTO `enderecos` (`id`, `usuario_id`, `pais`, `nome`, `sobrenome`, `cep`, `endereco`, `numero`, `complemento`, `bairro`, `cidade`, `estado`, `telefone`, `padrao`) VALUES
(2, 2, 'Brasil', 'Vitor', 'Souza', '89224-381', 'Rua aristides rudnick, 977', NULL, 'Casa', NULL, 'Joinville', 'SC', '(47) 99637-1550', 1),
(5, 3, 'Brasil', 'Gustavo', 'Fabiam', '89228-160', '284', NULL, 'Casa', NULL, 'Joinville', 'SC', '(47) 99649-0220', 1),
(6, 5, 'Brasil', 'Gustavo ', 'Fabiam', '89228-160', '284', NULL, 'Casa', NULL, 'Joinville', 'SC', '(47) 99649-0220', 1),
(7, 1, 'Brasil', 'Gabriel', 'Holz', '89224-381', 'Rua Aristídes Rudnick', '977', 'casa', 'Jardim Iririú', 'Joinville', 'SC', '(47) 99999-9999', 1),
(8, 1, 'Brasil', 'Gabriel', 'Holz', '89223-460', 'Rua Iracema Luckow', '170', 'casa', 'Jardim Sofia', 'Joinville', 'SC', '(47) 99999-9999', 0),
(9, 7, 'Brasil', 'maria clara', 'teixeira da silva', '89230-779', 'Rua Carlos Afonso Moreira', '318', 'casa', 'Adhemar Garcia', 'Joinville', 'SC', '(47) 98847-4250', 1);

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
(0, 0, '89224-381', 'Rua Aristídes Rudnick', '977', 'casa', 'Jardim Iririú', 'Joinville', 'SC', 'Gabriel Holz'),
(1, 3, '89224-381', 'Rua aristides rudnick', '977', 'Casa', 'Jardim Iririu', 'Joinville', 'SC', 'Vitor Souza'),
(2, 4, '89224-381', 'Rua aristides rudnick, 977', '977', 'Casa', 'Jardim Iririu', 'Joinville', 'SC', 'Vitor Souza'),
(3, 5, '89224-381', 'Rua aristides rudnick, 977', '977', 'Casa', 'Jardim Iririu', 'Joinville', 'SC', 'Vitor Souza'),
(4, 6, '89224-381', 'Rua aristides rudnick, 977', '977', 'Casa', 'Jardim Iririu', 'Joinville', 'SC', 'Vitor Souza'),
(5, 7, '89224-381', 'Rua aristides rudnick, 977', '977', 'Casa', 'Jardim Iririu', 'Joinville', 'SC', 'Vitor Souza'),
(6, 8, '89224-381', 'Rua Aristídes Rudnick', '977', 'casa', 'Jardim Iririú', 'Joinville', 'SC', 'Gabriel Holz'),
(7, 9, '89224-381', 'Rua aristides rudnick, 977', '977', 'Casa', 'Jardim Iririu', 'Joinville', 'SC', 'Vitor Souza'),
(8, 10, '89230-779', 'Rua Carlos Afonso Moreira', '318', 'casa', 'Adhemar Garcia', 'Joinville', 'SC', 'Maria clara'),
(9, 11, '89224-381', 'Rua aristides rudnick, 977', '977', 'Casa', 'Jardim Iririu', 'Joinville', 'SC', 'Vitor Souza'),
(10, 12, '89230-779', 'Rua Carlos Afonso Moreira', '318', 'casa', 'Adhemar Garcia', 'Joinville', 'SC', 'Maria clara'),
(11, 13, '89224-381', 'Rua Aristídes Rudnick', '977', 'casa', 'Jardim Iririú', 'Joinville', 'SC', 'Gabriel Holz'),
(12, 14, '89224-381', 'Rua Aristídes Rudnick', '977', 'casa', 'Jardim Iririú', 'Joinville', 'SC', 'Gabriel Holz');

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
(1, 'FALL ’26 COLLECTION ©', 'Compre Agora', '/Shop.php', '/MagdaCrew/public/assets/images/background3.png', 1, 1, 'hero_topo'),
(2, 'ROMANTIC ’26 ©', 'Compre Agora', '/views/pages/Shop.php', '/MagdaCrew/public/assets/images/background2.png', 1, 2, 'hero_topo'),
(5, 'VAMPETA’26 | T-SHIRTS', 'Explore Agora', '/MagdaCrew/public/produtos', '/MagdaCrew/public/assets/images/banners/banner_5_1780445932_6a1f72ec438fb.png', 0, 1, 'banner_baixo'),
(8, 'FALL COLLETION', 'Compre Agora', '/views/pages/Shop.php', '/MagdaCrew/public/assets/images/banners/banner_novo_1780526313_6a20ace93721d.jpg', 1, 1, 'banner_baixo');

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
(0, 0, 40, 'Bermuda Balão 3/4 Longa', 1, 200.00, 'M', 'Preto'),
(1, 1, NULL, 'teste', 1, 189.00, 'M', '1'),
(5, 2, NULL, 'Magda Art Burgundy Tee', 1, 179.00, 'M', '1'),
(6, 2, NULL, 'Magda Art Burgundy Tee', 2, 179.00, 'M', '1'),
(7, 3, 9, 'Magda Signature Black Tee', 2, 189.90, 'GG', 'Preto'),
(8, 4, 10, 'Signature Black Baggy', 1, 369.90, 'P', 'Rosa'),
(9, 5, 7, 'Magda Crew Tee', 1, 189.90, 'PP', 'Preto'),
(10, 6, 9, 'Magda Signature Black Tee', 2, 189.90, 'GG', 'Preto'),
(11, 7, 9, 'Magda Signature Black Tee', 1, 189.90, 'GG', 'Preto'),
(12, 8, 10, 'Signature Black Baggy', 1, 369.90, 'P', 'Rosa'),
(13, 9, 9, 'Magda Signature Black Tee', 1, 189.90, 'GG', 'Preto'),
(14, 10, 10, 'Signature Black Baggy', 1, 369.90, 'P', 'Rosa'),
(15, 10, 5, 'Magda Art Burgundy Tee', 1, 179.90, 'P', 'Vermelho'),
(16, 11, 10, 'Signature Black Baggy', 1, 369.90, 'P', 'Rosa'),
(17, 12, 8, 'Magda Crew Tee', 1, 189.90, 'M', 'Preto'),
(18, 12, 6, 'Magda Art Burgundy Tee', 1, 179.90, 'G', 'Vermelho'),
(19, 12, 9, 'Magda Signature Black Tee', 1, 189.90, 'GG', 'Preto'),
(20, 12, 11, 'Signature Black Baggy', 1, 369.90, 'M', 'Rosa'),
(21, 13, 40, 'Bermuda Balão 3/4 Longa', 1, 200.00, 'M', 'Preto'),
(22, 14, 41, 'Bermuda Balão 3/4 Longa', 1, 200.00, 'P', 'Preto');

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
(8, 'Signature Black Baggy', 'A Calça Jeans Baggy Preta Estonada combina conforto, resistência e um visual urbano atemporal. Produzida em 100% algodão com gramatura 11 oz, oferece estrutura, durabilidade e um caimento encorpado, ideal para o dia a dia.\r\n\r\nSua modelagem baggy garante um ajuste amplo e confortável, enquanto a lavagem preta estonada proporciona um acabamento moderno e versátil. Os quatro bordados tom sobre tom — dois na parte frontal e dois na traseira — adicionam identidade à peça de forma discreta e sofisticada.\r\n\r\nPensada para durar, a calça conta com costuras reforçadas, zíper YKK de alta resistência, botão e rebites personalizados, além de ser pré-encolhida, reduzindo alterações após as lavagens.\r\n\r\nOs detalhes de acabamento incluem etiqueta traseira em couro confeccionada artesanalmente, etiqueta interna no cós, cinco bolsos funcionais e passadores para cinto, unindo praticidade e estilo em uma única peça.\r\n\r\nEspecificações:\r\n\r\n100% algodão\r\nTecido 11 oz\r\nModelagem Baggy\r\nLavagem preta estonada\r\n4 bordados tom sobre tom (2 frontais e 2 traseiros)\r\nPré-encolhida\r\nBotão personalizado\r\nRebites personalizados\r\nZíper YKK de alta resistência\r\nCosturas reforçadas\r\nEtiqueta traseira em couro artesanal\r\nEtiqueta interna no cós\r\n5 bolsos\r\nCós com passadores para cinto\r\n\r\nObservação: Conforme as normas da ABNT, podem ocorrer variações de 3% a 5% na lavagem e nas medidas entre as peças, características naturais do processo de fabricação do jeans.', 369.90, 4, 0, 1),
(9, 'Script Beanie', 'O Script Beanie combina conforto, proteção térmica e um visual streetwear de forte identidade. Desenvolvido em malha retilínea de alta qualidade, oferece um toque macio, excelente elasticidade e um ajuste anatômico ideal para o uso diário em dias frios.\r\n\r\nSua modelagem clássica garante um caimento firme e confortável, enquanto o padrão gráfico em jacquard confere um acabamento moderno e urbano à peça. O design destaca-se pela tipografia estilizada em formato script de estética agressiva e marcante, envolvendo a parte frontal.\r\n\r\nPensado para oferecer versatilidade e estilo, o modelo está disponível em duas variações de cores contrastantes sobre a base preta: Laranja e Branco. A peça conta com acabamentos e costuras reforçadas em seu topo, garantindo que o gorro mantenha sua estrutura e formato original mesmo após o uso contínuo.\r\n\r\nOs detalhes de confecção incluem uma trama fechada que retém o calor eficientemente, unindo praticidade, aquecimento e atitude em um único acessório essencial para o guarda-roupa streetwear.\r\n\r\nEspecificações:\r\nComposição: 100% Acrílico (Malha Retilínea)\r\n\r\nModelagem: Beanie Tradicional / Gorro\r\n\r\nEstampa: Gráfico em Jacquard (direto na trama do tecido)\r\n\r\nVariações de Cor: Preto com Laranja / Preto com Branco\r\n\r\nTamanho: Único (com elasticidade adaptável)\r\n\r\nToque: Macio e confortável\r\n\r\nCosturas: Reforçadas no topo para ajuste anatômico', 79.99, 6, 0, 1),
(10, 'Nasty Black Baggy', 'A Calça Jeans Baggy Nasty Black combina conforto, resistência e um visual urbano marcante. Produzida em 100% algodão com tecido encorpado de alta gramatura, oferece estrutura, durabilidade e um caimento pesado ideal para o dia a dia no streetwear.\r\n\r\nSua modelagem baggy super ampla garante um ajuste muito confortável e despojado, enquanto a lavagem preta com efeito estonado e lixado agressivo proporciona contrastes intensos de luz nas pernas, joelhos e nos vincos frontais. Um bordado estilizado posicionado discretamente no bolso frontal pequeno adiciona identidade exclusiva à peça.\r\n\r\nPensada para durar, a calça conta com costuras reforçadas em toda a sua estrutura, zíper de alta resistência, botão e rebites personalizados, além de passar por processo de pré-encolhimento que reduz alterações após as lavagens domésticas.\r\n\r\nOs detalhes de acabamento incluem etiqueta interna personalizada no cós, etiqueta miniatura aplicada na barra, cinco bolsos funcionais e passadores amplos para cinto, unindo a máxima praticidade a uma estética underground autêntica.\r\n\r\nEspecificações:\r\n\r\n100% algodão\r\n\r\nTecido jeans de alta gramatura\r\n\r\nModelagem Baggy ampla\r\n\r\nLavagem preta estonada com efeitos de lixados contrastantes\r\n\r\nBordado exclusivo no bolso frontal\r\n\r\nPré-encolhida\r\n\r\nBotão personalizado\r\n\r\nRebites personalizados\r\n\r\nZíper de alta resistência\r\n\r\nCosturas reforçadas\r\n\r\nEtiqueta interna no cós e detalhe na barra\r\n\r\n4 bolsos\r\n\r\nCós com passadores para cinto', 350.00, 4, 0, 1),
(11, 'Mosquetão TnB', 'O Mosquetão Chaveiro Tá Na Base combina praticidade, resistência e um visual urbano atemporal. Produzido em metal zamak com banho niquelado, oferece estrutura robusta, durabilidade e um acabamento brilhante encorpado, ideal para o uso no dia a dia.\r\n\r\nSua modelagem com argola articulada garante um encaixe amplo e confortável em passadores de calça, mochilas ou bolsas, enquanto a aplicação resinada com a inscrição \"Tá Na Base\" e estrelas nas extremidades proporciona um acabamento moderno e versátil. O detalhe com base giratória adiciona identidade e funcionalidade à peça de forma discreta e sofisticada.\r\n\r\nPensado para durar, o chaveiro conta com mola de alta pressão e trava de segurança integrada, reduzindo o risco de desencaixes acidentais após longos períodos de uso.\r\n\r\nOs detalhes de acabamento incluem argola para chaves reforçada e resina de alta proteção contra riscos, unindo utilidade e estilo streetwear em uma única peça.\r\n\r\nEspecificações:\r\n\r\nLiga metálica zamak de alta resistência\r\n\r\nAcabamento banhado a níquel com brilho metálico\r\n\r\nPlaca com aplicação resinada personalizada\r\n\r\nInscrição exclusiva Tá Na Base com detalhes em laranja e estrelas\r\n\r\nBase giratória com rotação 360 graus\r\n\r\nTrava com mola de retorno rápido\r\n\r\nArgola de chaves inclusa\r\n\r\nModelo utilitário com encaixe firme\r\n\r\nDesign focado em alta durabilidade\r\n\r\nDimensões compactas e peso ideal para o cotidiano', 49.99, 6, 0, 1),
(12, 'Mosquetão Romã', 'O Mosquetão Chaveiro Romã combina praticidade, resistência e um visual urbano minimalista. Produzido em liga metálica de alta resistência com acabamento fosco texturizado, oferece estrutura robusta, durabilidade e um caimento técnico encorpado, ideal para o dia a dia.\r\n\r\nSua modelagem com trava de rosca texturizada garante um fechamento seguro e firme em passadores de calça, mochilas ou bolsas, enquanto a gravação a laser com a inscrição \"romã\" na lateral proporciona um acabamento moderno e versátil. O detalhe com argola metálica integrada adiciona identidade e funcionalidade à peça de forma discreta e sofisticada.\r\n\r\nPensado para durar, o chaveiro conta com sistema de fechamento mecânico preciso, reduzindo folgas e desgastes mesmo após longos períodos de uso contínuo.\r\n\r\nOs detalhes de acabamento incluem uma superfície cinza chumbo com toque industrial e argola reforçada para chaves, unindo utilidade e estilo streetwear tático em uma única peça.\r\n\r\nEspecificações:\r\n\r\nLiga metálica de alta resistência\r\n\r\nAcabamento fosco texturizado em tom cinza chumbo\r\n\r\nGravação exclusiva romã realizada a laser\r\n\r\nTrava com sistema de rosca recartilhada de alta precisão\r\n\r\nAbertura angular otimizada para encaixe rápido\r\n\r\nArgola de chaves em aço inclusa\r\n\r\nModelo utilitário com fixação firme e segura\r\n\r\nDesign focado em alta durabilidade e estética tática\r\n\r\nDimensões compactas ideais para o cotidiano', 49.99, 6, 0, 1),
(13, 'Moletom Zíper', 'O Moletom de Zíper Preto combina conforto, resistência e um visual urbano atemporal. Produzido em algodão de alta gramatura com toque encorpado, oferece excelente proteção térmica, estrutura e durabilidade, sendo ideal para os dias mais frios.\r\n\r\nSua modelagem oversize garante um caimento amplo, despojado e muito confortável, com ombros caídos que reforçam a estética streetwear. O fechamento frontal por zíper de metal adiciona praticidade e versatilidade ao design minimalista, permitindo diferentes formas de uso.\r\n\r\nPensada para durar, a peça conta com acabamentos em ribana reforçada nos punhos e na barra, mantendo o formato original mesmo após o uso contínuo. O capuz anatômico conta com cordão de ajuste no mesmo tom do tecido, e o bolso estilo canguru dividido traz funcionalidade para o cotidiano.\r\n\r\nOs detalhes incluem uma etiqueta interna personalizada no cós da gola, costuras reforçadas e tecido pré-encolhido para mitigar alterações significativas após as lavagens domésticas.\r\n\r\nEspecificações:\r\n\r\nTecido de alta gramatura encorpado\r\n\r\nModelagem ampla com ombros caídos\r\n\r\nFechamento por zíper frontal de metal\r\n\r\nCapuz com cordão de ajuste regulável\r\n\r\nPunhos e barra em ribana elástica reforçada\r\n\r\nBolso frontal estilo canguru dividido\r\n\r\nPré-encolhido\r\n\r\nEtiqueta interna personalizada na gola\r\n\r\nCosturas reforçadas para maior longevidade', 178.99, 3, 0, 1),
(14, 'Midnight Jorts', 'A Bermuda Jeans Jorts Midnight combina conforto, resistência e um visual urbano marcante. Produzida em 100% algodão com tecido encorpado de alta gramatura, oferece estrutura, durabilidade e um caimento pesado ideal para o dia a dia no streetwear.\r\n\r\nSua modelagem jorts super ampla garante um ajuste muito confortável e despojado abaixo do joelho, enquanto a lavagem escura com efeito estonado e lixados frontais em degradê proporciona contrastes intensos de luz nas pernas e nos vincos. Uma etiqueta miniatura aplicada na barra adiciona identidade discreta e exclusiva à peça.\r\n\r\nPensada para durar, a bermuda conta com costuras reforçadas em toda a sua estrutura, zíper de alta resistência, botão e rebites personalizados, além de passar por processo de pré-encolhimento que reduz alterações após as lavagens domésticas.\r\n\r\nOs detalhes de acabamento incluem etiqueta interna personalizada no cós, cinco bolsos funcionais e passadores amplos para cinto, unindo a máxima praticidade a uma estética urbana autêntica.\r\n\r\nEspecificações:\r\n\r\n100% algodão\r\n\r\nTecido jeans de alta gramatura\r\n\r\nModelagem Jorts baggy ampla\r\n\r\nLavagem escura estonada com efeitos de lixados contrastantes\r\n\r\nDetalhe de etiqueta miniatura na barra\r\n\r\nPré-encolhida\r\n\r\nBotão personalizado\r\n\r\nRebites personalizados\r\n\r\nZíper de alta resistência\r\n\r\nCosturas reforçadas\r\n\r\nEtiqueta interna personalizada no cós\r\n\r\n5 bolsos\r\n\r\nCós com passadores para cinto', 256.90, 5, 0, 1),
(15, 'Jorts Lotus', 'A Bermuda Jeans Jorts Lotus combina conforto, resistência e um visual urbano artístico. Produzida em 100% algodão com tecido encorpado de alta gramatura, oferece estrutura, durabilidade e um caimento pesado ideal para o dia a dia no streetwear.\r\n\r\nSua modelagem jorts super ampla garante um ajuste muito confortável e despojado abaixo do joelho, enquanto a lavagem azul escura destaca uma estampa corrida texturizada que remete a elementos da natureza e flores de lótus na metade inferior das pernas. Um bordado sutil no bolso frontal pequeno e a etiqueta miniatura aplicada na barra adicionam identidade exclusiva à peça.\r\n\r\nPensada para durar, a bermuda conta com costuras reforçadas em tom contrastante, zíper de alta resistência, botão e rebites personalizados, além de passar por processo de pré-encolhimento que reduz alterações após as lavagens domésticas.\r\n\r\nOs detalhes de acabamento incluem etiqueta interna personalizada no cós, cinco bolsos funcionais e passadores amplos para cinto, unindo a máxima praticidade a uma estética underground autêntica.\r\n\r\nEspecificações:\r\n\r\n100% algodão\r\n\r\nTecido jeans de alta gramatura\r\n\r\nModelagem Jorts baggy ampla\r\n\r\nLavagem azul escura com estampa Lotus integrada ao tecido\r\n\r\nBordado discreto no bolso frontal e detalhe de etiqueta na barra\r\n\r\nPré-encolhida\r\n\r\nBotão personalizado\r\n\r\nRebites personalizados\r\n\r\nZíper de alta resistência\r\n\r\nCosturas reforçadas com linha contrastante\r\n\r\nEtiqueta interna personalizada no cós\r\n\r\n4 bolsos\r\n\r\nCós com passadores para cinto', 250.99, 5, 0, 1),
(16, 'Jorts Acid Ops', 'A Bermuda Jeans Jorts Acid Ops combina conforto, resistência e um visual urbano marcante. Produzida em 100% algodão com tecido encorpado de alta gramatura, oferece estrutura, durabilidade e um caimento pesado ideal para o dia a dia no streetwear.\r\n\r\nSua modelagem jorts super ampla garante um ajuste muito confortável e despojado abaixo do joelho, enquanto a lavagem clara com efeito acid wash e aspectos amarelados envelhecidos proporciona contrastes intensos de luz e desgaste vintage nas pernas e nos vincos. O design limpo foca na textura e na coloração diferenciada do tecido para trazer autenticidade à peça.\r\n\r\nPensada para durar, a bermuda conta com costuras reforçadas em toda a sua estrutura, zíper de alta resistência, botão e rebites personalizados, além de passar por processo de pré-encolhimento que reduz alterações após as lavagens domésticas.\r\n\r\nOs detalhes de acabamento incluem etiqueta interna personalizada no cós, cinco bolsos funcionais e passadores amplos para cinto, unindo a máxima praticidade a uma estética underground e nostálgica de forte identidade.\r\n\r\nEspecificações:\r\n\r\n100% algodão\r\n\r\nTecido jeans de alta gramatura\r\n\r\nModelagem Jorts baggy ampla\r\n\r\nLavagem clara estilo acid wash com efeito envelhecido/amarelado\r\n\r\nPré-encolhida\r\n\r\nBotão personalizado\r\n\r\nRebites personalizados\r\n\r\nZíper de alta resistência\r\n\r\nCosturas reforçadas\r\n\r\nEtiqueta interna personalizada no cós\r\n\r\n4 bolsos\r\n\r\nCós com passadores para cinto', 178.00, 5, 0, 1),
(17, 'Jaqueta Carbon Fade', 'A Jaqueta Jeans Carbon Fade combina conforto, resistência e um visual urbano atemporal. Produzida em jeans 100% algodão de alta gramatura com forro encorpado, oferece estrutura, excelente isolamento térmico e durabilidade, sendo ideal para os dias frios.\r\n\r\nSua modelagem box garante um ajuste amplo e confortável com caimento streetwear despojado, enquanto a lavagem escura com efeito carbon fade proporciona nuances de desgaste e um acabamento moderno e versátil. O design clean foca na textura do tecido trabalhado em lavanderia industrial para adicionar identidade à peça de forma discreta e sofisticada.\r\n\r\nPensada para durar, a jaqueta conta com costuras reforçadas em toda a sua estrutura, fechamento por zíper frontal duplo de alta resistência e punhos e barra em ribana elástica pesada que impede a entrada de vento frio.\r\n\r\nOs detalhes de acabamento incluem capuz integrado e anatômico, bolsos frontais estilo canguru embutidos e etiqueta interna personalizada no cós da gola, unindo praticidade, aquecimento e estilo em uma única peça essencial.\r\n\r\nEspecificações:\r\n\r\n100% algodão\r\n\r\nTecido jeans de alta gramatura\r\n\r\nModelagem Box / Oversize com capuz\r\n\r\nLavagem escura com efeito carbon fade (estonado degradê)\r\n\r\nForro interno para maior proteção térmica\r\n\r\nPré-encolhida\r\n\r\nZíper frontal duplo de metal de alta resistência\r\n\r\nPunhos e barra em ribana elástica reforçada\r\n\r\nCosturas reforçadas\r\n\r\nEtiqueta interna personalizada na gola\r\n\r\nBolsos frontais funcionais estilo canguru', 359.99, 3, 0, 1),
(18, 'CLS Medium Bag', 'A Bolsa Class Medium Bag Black combina conforto, resistência e um visual urbano atemporal. Produzida em tecido sintético de alta qualidade com acabamento acetinado macio, oferece leveza, durabilidade e uma estrutura flexível encorpada, ideal para carregar seus pertences essenciais no dia a dia.\r\n\r\nSua modelagem tiracolo garante um ajuste prático e confortável junto ao corpo, enquanto a tonalidade preta lisa proporciona um visual minimalista, moderno e altamente versátil. A gravação discreta da assinatura Class em tom dourado na parte frontal adiciona identidade à peça de forma sofisticada.\r\n\r\nPensada para durar, a bolsa conta com costuras reforçadas em seus pontos de maior tensão, alça transversal larga e ajustável por meio de fivela metálica de alta resistência, além de fechamento seguro por zíper superior embutido.\r\n\r\nOs detalhes de acabamento incluem compartimento interno funcional e aviamentos selecionados, unindo praticidade e estilo streetwear utilitário em uma única peça.\r\n\r\nEspecificações:\r\n\r\nTecido sintético premium com toque acetinado\r\n\r\nModelagem Medium Bag (bolsa tiracolo média)\r\n\r\nCor preta com acabamento liso e sutil brilho fosco\r\n\r\nAssinatura Class gravada em tom dourado na parte frontal\r\n\r\nAlça transversal larga com regulagem de altura\r\n\r\nFivela de ajuste metálica prateada\r\n\r\nFechamento superior por zíper de alta resistência\r\n\r\nCosturas embutidas e reforçadas\r\n\r\nEspaço interno otimizado', 120.00, 6, 0, 1),
(19, 'Calça Reta Inverso', 'A Calça Jeans Reta Inverso Preta combina conforto, resistência e um visual urbano atemporal. Produzida em 100% algodão com tecido encorpado de alta gramatura, oferece estrutura, durabilidade e um caimento reto alinhado, ideal para o dia a dia.\r\n\r\nSua modelagem reta garante um ajuste confortável e equilibrado, enquanto a lavagem preta com sutis efeitos estonados proporciona um acabamento moderno e versátil. O grande diferencial fica por conta do bolso traseiro com design invertido e aplicação de etiqueta personalizada na cor laranja, adicionando identidade à peça de forma autêntica e sofisticada.\r\n\r\nPensada para durar, a calça conta com costuras reforçadas, zíper de alta resistência, botão e rebites personalizados, além de ser pré-encolhida, reduzindo alterações após as lavagens domésticas.\r\n\r\nOs detalhes de acabamento incluem cinco bolsos funcionais e passadores para cinto, unindo praticidade e estilo streetwear em uma única peça.\r\n\r\nEspecificações:\r\n\r\n100% algodão\r\n\r\nTecido jeans de alta gramatura\r\n\r\nModelagem Reta\r\n\r\nLavagem preta com leve estonagem\r\n\r\nBolso traseiro exclusivo invertido com etiqueta detalhada\r\n\r\nPré-encolhida\r\n\r\nBotão personalizado\r\n\r\nRebites personalizados\r\n\r\nZíper de alta resistência\r\n\r\nCosturas reforçadas\r\n\r\n4 bolsos\r\n\r\nCós com passadores para cinto', 245.99, 4, 0, 1),
(20, 'Calça Jeans Cruvassa Dim', 'A Calça Jeans Cru Vassadim combina conforto, resistência e um visual urbano atemporal. Produzida em 100% algodão com tecido encorpado de alta gramatura, oferece estrutura, durabilidade e um caimento imponente, ideal para o dia a dia.\r\n\r\nSua modelagem baggy garante um ajuste amplo e muito confortável, enquanto a tonalidade cru natural do jeans sem lavagem pesada proporciona um acabamento moderno, clean e versátil. Os recortes geométricos e costuras contrastantes em tom marrom cruzam a peça na parte frontal e traseira, adicionando identidade e uma estética utilitária de forma discreta e sofisticada.\r\n\r\nPensada para durar, a calça conta com costuras reforçadas, zíper de alta resistência, botão e rebites personalizados, além de ser pré-encolhida, reduzindo alterações significativas após as lavagens domésticas.\r\n\r\nOs detalhes de acabamento incluem bolsos frontais e traseiros funcionais com formatos angulares exclusivos e passadores estruturados para cinto, unindo praticidade e estilo streetwear em uma única peça.\r\n\r\nEspecificações:\r\n\r\n100% algodão\r\n\r\nTecido jeans de alta gramatura\r\n\r\nModelagem Baggy ampla\r\n\r\nCor jeans cru natural\r\n\r\nPainéis com recortes e costuras marrons contrastantes\r\n\r\nDesign de bolsos angulares exclusivos\r\n\r\nPré-encolhida\r\n\r\nBotão personalizado\r\n\r\nRebites personalizados\r\n\r\nZíper de alta resistência\r\n\r\nCosturas reforçadas\r\n\r\nCós com passadores para cinto', 289.99, 4, 0, 1),
(21, 'Calça Moletom Baggy', 'A Calça de Moletom Baggy Cinza Mescla combina conforto, resistência e um visual urbano atemporal. Produzida em algodão de alta gramatura com excelente toque encorpado, oferece estrutura, maciez e aquecimento ideal para o dia a dia.\r\n\r\nSua modelagem baggy garante um ajuste amplo e confortável com caimento despojado nas pernas, enquanto a coloração cinza mescla clássica proporciona um acabamento moderno e versátil. O design conta com ajuste em elástico e cordão regulável na cintura, oferecendo máxima praticidade e vestibilidade.\r\n\r\nPensada para durar, a calça conta com costuras reforçadas em toda a sua estrutura, barras com elástico embutido que mantêm a peça firme no tornozelo e tecido trabalhado para reduzir alterações e encolhimento após as lavagens domésticas.\r\n\r\nOs detalhes de acabamento incluem bolsos laterais funcionais e um bolso traseiro minimalista com etiqueta personalizada aplicada de forma discreta e sofisticada, unindo utilidade e estilo streetwear em uma única peça.\r\n\r\nEspecificações:\r\n\r\nTecido de moletom de alta gramatura encorpado\r\n\r\nModelagem Baggy ampla\r\n\r\nCor cinza mescla\r\n\r\nCós elástico com cordão de ajuste regulável\r\n\r\nBarras com acabamento em elástico embutido\r\n\r\nCosturas reforçadas\r\n\r\nBolsos laterais embutidos e bolso traseiro funcional\r\n\r\nDetalhe de etiqueta personalizada no bolso traseiro', 250.00, 4, 0, 1),
(22, 'Calça Balão OG', 'A Calça Jeans Balão OG Rosa combina conforto, resistência e um visual urbano atemporal. Produzida em 100% algodão com tecido encorpado de alta gramatura, oferece estrutura, durabilidade e um caimento volumoso marcante, ideal para o dia a dia.\r\n\r\nSua modelagem balão super ampla garante um ajuste muito confortável e despojado, com pernas bem largas que afunilam levemente na barra, enquanto a coloração rosa estonada proporciona um acabamento moderno, autêntico e versátil. Um discreto bordado personalizado posicionado na parte traseira adiciona identidade exclusiva à peça de forma sofisticada.\r\n\r\nPensada para durar, a calça conta com costuras reforçadas, zíper de alta resistência, botão e rebites personalizados, além de ser pré-encolhida, reduzindo alterações significativas após as lavagens domésticas.\r\n\r\nOs detalhes de acabamento incluem etiqueta traseira, cinco bolsos funcionais e passadores para cinto, unindo o máximo de praticidade e estilo streetwear em uma única peça de forte presença.\r\n\r\nEspecificações:\r\n\r\n100% algodão\r\n\r\nTecido jeans de alta gramatura\r\n\r\nModelagem Balão ampla\r\n\r\nLavagem rosa estonada\r\n\r\nBordado exclusivo na parte traseira\r\n\r\nPré-encolhida\r\n\r\nBotão personalizado\r\n\r\nRebites personalizados\r\n\r\nZíper de alta resistência\r\n\r\nCosturas reforçadas\r\n\r\n4 bolsos\r\n\r\nCós com passadores para cinto', 300.00, 4, 0, 1),
(23, 'Blusa Moletom Zíper Jesus Cross', 'O Moletom de Zíper Jesus Cross Preto combina conforto, resistência e um visual urbano de forte identidade. Produzido em algodão de alta gramatura com toque encorpado, oferece excelente proteção térmica, estrutura e durabilidade, sendo ideal para os dias mais frios.\r\n\r\nSua modelagem oversize garante um caimento amplo, despojado e muito confortável, com ombros caídos que reforçam a estética streetwear. O design destaca-se pelas estampas aplicadas nas mangas e pela cruz estilizada com a inscrição \"Jesus\" centralizada nas costas, adicionando originalidade à peça de forma marcante.\r\n\r\nPensada para durar, a peça conta com acabamentos em ribana reforçada nos punhos e na barra, mantendo o formato original mesmo após o uso contínuo. O capuz anatômico conta com cordão de ajuste no mesmo tom do tecido, e o fechamento frontal por zíper de metal traz funcionalidade para o cotidiano.\r\n\r\nOs detalhes incluem bolso estilo canguru dividido, costuras reforçadas e tecido pré-encolhido para mitigar alterações significativas após as lavagens domésticas.\r\n\r\nEspecificações:\r\n\r\nTecido de alta gramatura encorpado\r\n\r\nModelagem ampla com ombros caídos\r\n\r\nFechamento por zíper frontal de metal\r\n\r\nEstampa artística nas mangas e arte em formato de cruz nas costas\r\n\r\nCapuz com cordão de ajuste regulável\r\n\r\nPunhos e barra em ribana elástica reforçada\r\n\r\nBolso frontal estilo canguru dividido\r\n\r\nPré-encolhido\r\n\r\nCosturas reforçadas para maior longevidade', 178.99, 3, 0, 1),
(24, 'Blusa Moletom Face Zip Seleção', 'O Blusão Moletom Face Zip Seleção Off-White combina conforto, resistência e um visual urbano de forte identidade. Produzido em algodão de alta gramatura com toque encorpado, oferece excelente proteção térmica, estrutura e durabilidade, sendo ideal para os dias mais frios.\r\n\r\nSua modelagem oversize garante um caimento amplo, despojado e muito confortável, com ombros caídos que reforçam a estética streetwear. O design minimalista destaca-se pela tonalidade off-white e pelo fechamento frontal por zíper de metal que se estende por todo o capuz, permitindo um fechamento total e adicionando um visual técnico e exclusivo à peça.\r\n\r\nPensada para durar, a peça conta com acabamentos em ribana reforçada nos punhos e na barra, mantendo o formato original mesmo após o uso contínuo. O bolso estilo canguru dividido traz praticidade e funcionalidade para o cotidiano.\r\n\r\nOs detalhes incluem costuras reforçadas e tecido pré-encolhido para mitigar alterações significativas após as lavagens domésticas.\r\n\r\nEspecificações:\r\n\r\nTecido de alta gramatura encorpado\r\n\r\nModelagem ampla com ombros caídos\r\n\r\nFechamento por zíper frontal de metal que cobre o capuz (Face Zip)\r\n\r\nPunhos e barra em ribana elástica reforçada\r\n\r\nBolso frontal estilo canguru dividido\r\n\r\nPré-encolhido\r\n\r\nCosturas reforçadas para maior longevidade', 200.00, 3, 0, 1),
(25, 'Bermuda Balão 3/4 Plissada', 'A Bermuda Jeans Balão 3/4 Plissada Preta combina conforto, resistência e um visual urbano de forte identidade. Produzida em 100% algodão com tecido encorpado de alta gramatura, oferece estrutura, durabilidade e um caimento volumoso marcante, ideal para o dia a dia no streetwear.\r\n\r\nSua modelagem balão de comprimento 3/4 garante um ajuste muito amplo e despojado abaixo do joelho, destacando-se pelos detalhes plissados com vincos verticais bem marcados tanto na parte frontal quanto na traseira. A lavagem preta uniforme proporciona um visual minimalista e altamente versátil para diversas combinações.\r\n\r\nPensada para durar, a bermuda conta com costuras reforçadas em toda a sua estrutura, zíper de alta resistência, botão e rebites personalizados, além de passar por processo de pré-encolhimento que reduz alterações após as lavagens domésticas.\r\n\r\nOs detalhes de acabamento incluem etiqueta interna personalizada no cós, bolsos funcionais e passadores amplos para cinto, unindo a máxima praticidade a uma estética vanguardista de forte presença.\r\n\r\nEspecificações:\r\n\r\n100% algodão\r\n\r\nTecido jeans de alta gramatura\r\n\r\nModelagem Balão 3/4 ampla\r\n\r\nAcabamento plissado com vincos verticais estruturados\r\n\r\nCor preta com lavagem uniforme\r\n\r\nPré-encolhida\r\n\r\nBotão personalizado\r\n\r\nRebites personalizados\r\n\r\nZíper de alta resistência\r\n\r\nCosturas reforçadas\r\n\r\nBolsos funcionais\r\n\r\nCós com passadores para cinto', 150.00, 5, 0, 1),
(26, 'Bermuda Balão 3/4 Longa', 'A Bermuda Jeans Balão 3/4 Longa Preta combina conforto, resistência e um visual urbano de forte identidade. Produzida em 100% algodão com tecido encorpado de alta gramatura, oferece estrutura, durabilidade e um caimento volumoso marcante, ideal para o dia a dia no streetwear.\r\n\r\nSua modelagem balão de comprimento 3/4 alongado garante um ajuste muito amplo e despojado abaixo do joelho, caindo de forma fluida nas pernas. A lavagem preta uniforme proporciona um visual minimalista, clean e altamente versátil para diversas combinações.\r\n\r\nPensada para durar, a bermuda conta com costuras reforçadas em toda a sua estrutura, zíper de alta resistência, botão e rebites personalizados, além de passar por processo de pré-encolhimento que reduz alterações após as lavagens domésticas.\r\n\r\nOs detalhes de acabamento incluem etiqueta interna personalizada no cós, bolsos funcionais e passadores amplos para cinto, unindo a máxima praticidade a uma estética underground clássica e de forte presença.\r\n\r\nEspecificações:\r\n\r\n100% algodão\r\n\r\nTecido jeans de alta gramatura\r\n\r\nModelagem Balão 3/4 longa e ampla\r\n\r\nCor preta com lavagem uniforme\r\n\r\nPré-encolhida\r\n\r\nBotão personalizado\r\n\r\nRebites personalizados\r\n\r\nZíper de alta resistência\r\n\r\nCosturas reforçadas\r\n\r\nBolsos funcionais\r\n\r\nCós com passadores para cinto', 200.00, 5, 0, 1);

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
(30, 8, 'public/assets/images/produtos/1783041591_tnb-lookbook-keepsearching-8-11e7914412299df59517764557788109-1024-1024.webp', 0, 0),
(31, 8, 'public/assets/images/produtos/1783041591_tnb-still-calca02costas-9c01a2555c011dc4ce17764557552104-1024-1024.webp', 0, 1),
(32, 8, 'public/assets/images/produtos/1783041591_tnb-still-calca02frente-cdb25687c2dedc868d17764557550189-1024-1024.webp', 1, 2),
(33, 9, 'public/assets/images/produtos/1783383450_ScriptBeanieLARANJAMODELO.webp', 0, 0),
(34, 9, 'public/assets/images/produtos/1783383450_ScriptBeanieBRANCOMODELO.webp', 0, 1),
(35, 9, 'public/assets/images/produtos/1783383450_ScriptBeanieBRANCOLARANJA.webp', 1, 2),
(36, 10, 'public/assets/images/produtos/1783383596_NastyBlackBaggyCOSTAS.webp', 1, 0),
(37, 10, 'public/assets/images/produtos/1783383596_NastyBlackBaggyFRENTE.webp', 0, 1),
(38, 10, 'public/assets/images/produtos/1783383596_NastyBlackBaggyMODELO.webp', 0, 2),
(39, 11, 'public/assets/images/produtos/1783383722_MosquetãoTnB.webp', 1, 0),
(40, 12, 'public/assets/images/produtos/1783383817_Mosquetão.webp', 1, 0),
(41, 12, 'public/assets/images/produtos/1783383817_MosquetãoROMA.webp', 0, 1),
(42, 13, 'public/assets/images/produtos/1783383915_MoletomZíperPretoCOSTAS.webp', 0, 0),
(43, 13, 'public/assets/images/produtos/1783383915_MoletomZíperPretoFRENTE.webp', 1, 1),
(44, 13, 'public/assets/images/produtos/1783383915_MoletomZíperPretoMODELO.webp', 0, 2),
(45, 14, 'public/assets/images/produtos/1783384067_MidnightJortsCOSTAS.webp', 0, 0),
(46, 14, 'public/assets/images/produtos/1783384067_MidnightJortsFRENTE.webp', 1, 1),
(47, 14, 'public/assets/images/produtos/1783384067_MidnightJortsMODELO.webp', 0, 2),
(48, 15, 'public/assets/images/produtos/1783384229_JortsLotusCOSTAS.webp', 0, 0),
(49, 15, 'public/assets/images/produtos/1783384229_JortsLotusFRENTE.webp', 1, 1),
(50, 15, 'public/assets/images/produtos/1783384229_JortsLotusMODELO.webp', 0, 2),
(51, 16, 'public/assets/images/produtos/1783384377_JortsAcidOpsCOSTAS.webp', 0, 0),
(52, 16, 'public/assets/images/produtos/1783384377_JortsAcidOpsFRENTE.webp', 1, 1),
(53, 16, 'public/assets/images/produtos/1783384377_JortsAcidOpsMODELO.webp', 0, 2),
(54, 17, 'public/assets/images/produtos/1783384553_JaquetaCarbonFadeCOSTAS.webp', 0, 0),
(55, 17, 'public/assets/images/produtos/1783384553_JaquetaCarbonFadeFRENTE.webp', 1, 1),
(56, 17, 'public/assets/images/produtos/1783384553_JaquetaCarbonFadeMODELO.webp', 0, 2),
(57, 18, 'public/assets/images/produtos/1783384638_CLSMEDIUMBAGCLASSBLACKcostas.webp', 0, 0),
(58, 18, 'public/assets/images/produtos/1783384638_CLSMEDIUMBAGCLASSBLACKfrente.webp', 1, 1),
(59, 19, 'public/assets/images/produtos/1783384808_CALÇARETAiNVERSO(PRETO)costas.webp', 0, 0),
(60, 19, 'public/assets/images/produtos/1783384808_CALÇARETAINVERSO(PRETO)mad.webp', 1, 1),
(61, 20, 'public/assets/images/produtos/1783385083_CALÇAJEANSCRUVASSADIMcostasmad.webp', 0, 0),
(62, 20, 'public/assets/images/produtos/1783385083_CALÇAJEANSCRUVASSADIMfrentemad.webp', 1, 1),
(63, 21, 'public/assets/images/produtos/1783385261_CalçadeMoletomBaggyCinza MesclaCOSTASMODELO.webp', 0, 0),
(64, 21, 'public/assets/images/produtos/1783385261_CalçadeMoletomBaggyCinza MesclaFRENTE.webp', 1, 1),
(65, 21, 'public/assets/images/produtos/1783385261_CalçadeMoletomBaggyCinzaMesclaMODELO.webp', 0, 2),
(66, 22, 'public/assets/images/produtos/1783385382_CALÇABALÃOOG(ROSA)madFRENTE.webp', 1, 0),
(67, 22, 'public/assets/images/produtos/1783385382_CALÇABALÃOOG(ROSA)MODELOmad.webp', 0, 1),
(68, 23, 'public/assets/images/produtos/1783385474_BLUSAMOLETOMZÍPERJESUSCROSSPRETOmad.webp', 1, 0),
(69, 24, 'public/assets/images/produtos/1783385559_BLUSAMOLETOMFACEZIPSELEÇÃO(OFFWHITE)mad.webp', 1, 0),
(70, 25, 'public/assets/images/produtos/1783385655_BERMUDABALÃO34PLISSADA(PRETO)costasmad.webp', 0, 0),
(71, 25, 'public/assets/images/produtos/1783385655_BERMUDABALÃO34PLISSADA(PRETO)frentemad.webp', 1, 1),
(72, 25, 'public/assets/images/produtos/1783385655_BERMUDABALÃO34PLISSADA(PRETO)frentemodelo.webp', 0, 2),
(73, 26, 'public/assets/images/produtos/1783385801_BERMUDABALÃO34LONGA(PRETO)costasmad.webp', 0, 0),
(74, 26, 'public/assets/images/produtos/1783385801_BERMUDABALÃO34LONGA(PRETO)frentemad.webp', 1, 1),
(75, 26, 'public/assets/images/produtos/1783385801_BERMUDABALÃO34LONGA(PRETO)modelomad.webp', 0, 2);

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
(5, 2, 2, 4, 14, NULL),
(6, 2, 4, 4, 4, NULL),
(7, 1, 1, 1, 9, NULL),
(8, 1, 3, 1, 17, NULL),
(9, 4, 5, 1, 3, NULL),
(10, 8, 2, 8, 0, NULL),
(11, 8, 3, 8, 11, NULL),
(12, 8, 3, 1, 3, NULL),
(13, 8, 4, 1, 2, NULL),
(14, 9, 3, 12, 3, NULL),
(15, 9, 2, 12, 3, NULL),
(16, 9, 3, 2, 2, NULL),
(17, 9, 2, 2, 3, NULL),
(18, 10, 3, 1, 5, NULL),
(19, 10, 5, 1, 2, NULL),
(20, 13, 2, 1, 6, NULL),
(21, 13, 6, 1, 2, NULL),
(22, 14, 1, 1, 1, NULL),
(23, 14, 5, 1, 3, NULL),
(24, 15, 5, 5, 2, NULL),
(25, 15, 3, 5, 1, NULL),
(26, 17, 6, 11, 2, NULL),
(27, 17, 1, 11, 1, NULL),
(28, 19, 3, 1, 1, NULL),
(29, 19, 5, 1, 4, NULL),
(30, 21, 3, 3, 2, NULL),
(31, 21, 5, 3, 3, NULL),
(32, 22, 3, 8, 3, NULL),
(33, 22, 6, 8, 2, NULL),
(34, 23, 1, 1, 1, NULL),
(35, 23, 6, 1, 2, NULL),
(36, 24, 1, 2, 2, NULL),
(37, 24, 5, 2, 1, NULL),
(38, 25, 1, 1, 2, NULL),
(39, 25, 6, 1, 6, NULL),
(40, 26, 3, 1, 0, NULL),
(41, 26, 2, 1, 0, NULL);

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
(1, NULL, 'gabrielholz422@gmail.com', NULL, NULL, NULL, NULL, NULL, 1, '2026-07-07 20:30:53', '2026-05-25 19:51:23', 1, 1),
(2, NULL, 'vitordesouza1903@gmail.com', NULL, NULL, NULL, NULL, NULL, 1, '2026-07-06 22:00:44', '2026-05-26 19:14:00', 1, 1),
(3, NULL, 'gustavo_fabiam@estudante.sesisenai.org.br', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-27 19:31:04', '2026-05-27 19:30:44', 0, 1),
(4, NULL, 'holzadrian8@gmail.com', NULL, NULL, NULL, NULL, NULL, 1, '2026-07-08 22:19:34', '2026-05-27 21:37:40', 1, 1),
(5, NULL, 'gustavojoaquimfabiam@gmail.com', NULL, NULL, NULL, NULL, NULL, 1, '2026-07-08 22:17:10', '2026-06-03 21:08:56', 1, 1),
(7, NULL, 'maria_ct_silva@estudante.sesisenai.org.br', NULL, NULL, NULL, NULL, NULL, 1, '2026-07-06 21:44:12', '2026-07-06 21:32:51', 0, 1),
(8, NULL, 'lucasedbatista@gmail.com', NULL, NULL, NULL, '660664', '2026-07-07 02:52:42', 0, NULL, '2026-07-06 21:41:06', 0, 1);

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
(0, 1, 234.90, 200.00, 34.90, 'pix', 'sedex', '', 'cancelado', '2026-07-07 20:33:26'),
(1, 1, 101.00, 20.00, 10.00, 'simulacao', 'sedex', '123.456.789.40', 'cancelado', '2026-05-26 19:26:44'),
(2, 2, 100.00, 100.00, 50.00, 'teste', 'sedex', '123.456.789.40', 'enviado', '2026-05-26 19:47:14'),
(3, 2, 414.70, 379.80, 34.90, 'pix', 'sedex', '', 'cancelado', '2026-07-06 20:48:12'),
(4, 2, 389.80, 369.90, 19.90, 'cartao', 'pac', '', 'cancelado', '2026-07-06 20:50:42'),
(5, 2, 209.80, 189.90, 19.90, 'cartao', 'pac', '', 'enviado', '2026-07-06 20:56:14'),
(6, 2, 414.70, 379.80, 34.90, 'cartao', 'sedex', '', 'cancelado', '2026-07-06 21:18:31'),
(7, 2, 209.80, 189.90, 19.90, 'cartao', 'pac', '', 'confirmado', '2026-07-06 21:29:24'),
(8, 1, 404.80, 369.90, 34.90, 'pix', 'sedex', '', 'confirmado', '2026-07-06 21:40:54'),
(9, 2, 209.80, 189.90, 19.90, 'cartao', 'pac', '', 'cancelado', '2026-07-06 21:47:02'),
(10, 7, 584.70, 549.80, 34.90, 'cartao', 'sedex', '', 'processando', '2026-07-06 21:51:56'),
(11, 2, 389.80, 369.90, 19.90, 'cartao', 'pac', '', 'processando', '2026-07-06 21:54:29'),
(12, 7, 964.50, 929.60, 34.90, 'pix', 'sedex', '', 'cancelado', '2026-07-06 21:54:30'),
(13, 1, 219.90, 200.00, 19.90, 'pix', 'pac', '', 'pendente', '2026-07-07 21:35:45'),
(14, 1, 219.90, 200.00, 19.90, 'pix', 'pac', '', 'pendente', '2026-07-07 21:36:12');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

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
-- Banco de dados: `phpmyadmin`
--
CREATE DATABASE IF NOT EXISTS `phpmyadmin` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;
USE `phpmyadmin`;

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__bookmark`
--

CREATE TABLE `pma__bookmark` (
  `id` int(10) UNSIGNED NOT NULL,
  `dbase` varchar(255) NOT NULL DEFAULT '',
  `user` varchar(255) NOT NULL DEFAULT '',
  `label` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `query` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Bookmarks';

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__central_columns`
--

CREATE TABLE `pma__central_columns` (
  `db_name` varchar(64) NOT NULL,
  `col_name` varchar(64) NOT NULL,
  `col_type` varchar(64) NOT NULL,
  `col_length` text DEFAULT NULL,
  `col_collation` varchar(64) NOT NULL,
  `col_isNull` tinyint(1) NOT NULL,
  `col_extra` varchar(255) DEFAULT '',
  `col_default` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Central list of columns';

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__column_info`
--

CREATE TABLE `pma__column_info` (
  `id` int(5) UNSIGNED NOT NULL,
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `column_name` varchar(64) NOT NULL DEFAULT '',
  `comment` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `mimetype` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `transformation` varchar(255) NOT NULL DEFAULT '',
  `transformation_options` varchar(255) NOT NULL DEFAULT '',
  `input_transformation` varchar(255) NOT NULL DEFAULT '',
  `input_transformation_options` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Column information for phpMyAdmin';

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__designer_settings`
--

CREATE TABLE `pma__designer_settings` (
  `username` varchar(64) NOT NULL,
  `settings_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Settings related to Designer';

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__export_templates`
--

CREATE TABLE `pma__export_templates` (
  `id` int(5) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL,
  `export_type` varchar(10) NOT NULL,
  `template_name` varchar(64) NOT NULL,
  `template_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Saved export templates';

--
-- Despejando dados para a tabela `pma__export_templates`
--

INSERT INTO `pma__export_templates` (`id`, `username`, `export_type`, `template_name`, `template_data`) VALUES
(1, 'root', 'database', 'magda_crew', '{\"quick_or_custom\":\"quick\",\"what\":\"sql\",\"structure_or_data_forced\":\"0\",\"table_select[]\":[\"carrinho\",\"categorias\",\"cores\",\"enderecos\",\"enderecos_usuario\",\"enderecos_venda\",\"home_banners\",\"itens_venda\",\"produtos\",\"produto_imagens\",\"produto_variantes\",\"tamanhos\",\"usuarios\",\"vendas\"],\"table_structure[]\":[\"carrinho\",\"categorias\",\"cores\",\"enderecos\",\"enderecos_usuario\",\"enderecos_venda\",\"home_banners\",\"itens_venda\",\"produtos\",\"produto_imagens\",\"produto_variantes\",\"tamanhos\",\"usuarios\",\"vendas\"],\"table_data[]\":[\"carrinho\",\"categorias\",\"cores\",\"enderecos\",\"enderecos_usuario\",\"enderecos_venda\",\"home_banners\",\"itens_venda\",\"produtos\",\"produto_imagens\",\"produto_variantes\",\"tamanhos\",\"usuarios\",\"vendas\"],\"aliases_new\":\"\",\"output_format\":\"sendit\",\"filename_template\":\"@DATABASE@\",\"remember_template\":\"on\",\"charset\":\"utf-8\",\"compression\":\"none\",\"maxsize\":\"\",\"codegen_structure_or_data\":\"data\",\"codegen_format\":\"0\",\"csv_separator\":\",\",\"csv_enclosed\":\"\\\"\",\"csv_escaped\":\"\\\"\",\"csv_terminated\":\"AUTO\",\"csv_null\":\"NULL\",\"csv_columns\":\"something\",\"csv_structure_or_data\":\"data\",\"excel_null\":\"NULL\",\"excel_columns\":\"something\",\"excel_edition\":\"win\",\"excel_structure_or_data\":\"data\",\"json_structure_or_data\":\"data\",\"json_unicode\":\"something\",\"latex_caption\":\"something\",\"latex_structure_or_data\":\"structure_and_data\",\"latex_structure_caption\":\"Estrutura da tabela @TABLE@\",\"latex_structure_continued_caption\":\"Estrutura da tabela @TABLE@ (continuação)\",\"latex_structure_label\":\"tab:@TABLE@-structure\",\"latex_relation\":\"something\",\"latex_comments\":\"something\",\"latex_mime\":\"something\",\"latex_columns\":\"something\",\"latex_data_caption\":\"Conteúdo da tabela @TABLE@\",\"latex_data_continued_caption\":\"Conteúdo da tabela @TABLE@ (continuação)\",\"latex_data_label\":\"tab:@TABLE@-data\",\"latex_null\":\"\\\\textit{NULL}\",\"mediawiki_structure_or_data\":\"structure_and_data\",\"mediawiki_caption\":\"something\",\"mediawiki_headers\":\"something\",\"htmlword_structure_or_data\":\"structure_and_data\",\"htmlword_null\":\"NULL\",\"ods_null\":\"NULL\",\"ods_structure_or_data\":\"data\",\"odt_structure_or_data\":\"structure_and_data\",\"odt_relation\":\"something\",\"odt_comments\":\"something\",\"odt_mime\":\"something\",\"odt_columns\":\"something\",\"odt_null\":\"NULL\",\"pdf_report_title\":\"\",\"pdf_structure_or_data\":\"structure_and_data\",\"phparray_structure_or_data\":\"data\",\"sql_include_comments\":\"something\",\"sql_header_comment\":\"\",\"sql_use_transaction\":\"something\",\"sql_compatibility\":\"NONE\",\"sql_structure_or_data\":\"structure_and_data\",\"sql_create_table\":\"something\",\"sql_auto_increment\":\"something\",\"sql_create_view\":\"something\",\"sql_procedure_function\":\"something\",\"sql_create_trigger\":\"something\",\"sql_backquotes\":\"something\",\"sql_type\":\"INSERT\",\"sql_insert_syntax\":\"both\",\"sql_max_query_size\":\"50000\",\"sql_hex_for_binary\":\"something\",\"sql_utc_time\":\"something\",\"texytext_structure_or_data\":\"structure_and_data\",\"texytext_null\":\"NULL\",\"xml_structure_or_data\":\"data\",\"xml_export_events\":\"something\",\"xml_export_functions\":\"something\",\"xml_export_procedures\":\"something\",\"xml_export_tables\":\"something\",\"xml_export_triggers\":\"something\",\"xml_export_views\":\"something\",\"xml_export_contents\":\"something\",\"yaml_structure_or_data\":\"data\",\"\":null,\"lock_tables\":null,\"as_separate_files\":null,\"csv_removeCRLF\":null,\"excel_removeCRLF\":null,\"json_pretty_print\":null,\"htmlword_columns\":null,\"ods_columns\":null,\"sql_dates\":null,\"sql_relation\":null,\"sql_mime\":null,\"sql_disable_fk\":null,\"sql_views_as_tables\":null,\"sql_metadata\":null,\"sql_create_database\":null,\"sql_drop_table\":null,\"sql_if_not_exists\":null,\"sql_simple_view_export\":null,\"sql_view_current_user\":null,\"sql_or_replace_view\":null,\"sql_truncate\":null,\"sql_delayed\":null,\"sql_ignore\":null,\"texytext_columns\":null}');

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__favorite`
--

CREATE TABLE `pma__favorite` (
  `username` varchar(64) NOT NULL,
  `tables` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Favorite tables';

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__history`
--

CREATE TABLE `pma__history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL DEFAULT '',
  `db` varchar(64) NOT NULL DEFAULT '',
  `table` varchar(64) NOT NULL DEFAULT '',
  `timevalue` timestamp NOT NULL DEFAULT current_timestamp(),
  `sqlquery` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='SQL history for phpMyAdmin';

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__navigationhiding`
--

CREATE TABLE `pma__navigationhiding` (
  `username` varchar(64) NOT NULL,
  `item_name` varchar(64) NOT NULL,
  `item_type` varchar(64) NOT NULL,
  `db_name` varchar(64) NOT NULL,
  `table_name` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Hidden items of navigation tree';

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__pdf_pages`
--

CREATE TABLE `pma__pdf_pages` (
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `page_nr` int(10) UNSIGNED NOT NULL,
  `page_descr` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='PDF relation pages for phpMyAdmin';

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__recent`
--

CREATE TABLE `pma__recent` (
  `username` varchar(64) NOT NULL,
  `tables` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Recently accessed tables';

--
-- Despejando dados para a tabela `pma__recent`
--

INSERT INTO `pma__recent` (`username`, `tables`) VALUES
('root', '[{\"db\":\"magda_crew\",\"table\":\"usuarios\"},{\"db\":\"magda_crew\",\"table\":\"produtos\"},{\"db\":\"prisao_junina\",\"table\":\"ordens_prisao\"}]');

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__relation`
--

CREATE TABLE `pma__relation` (
  `master_db` varchar(64) NOT NULL DEFAULT '',
  `master_table` varchar(64) NOT NULL DEFAULT '',
  `master_field` varchar(64) NOT NULL DEFAULT '',
  `foreign_db` varchar(64) NOT NULL DEFAULT '',
  `foreign_table` varchar(64) NOT NULL DEFAULT '',
  `foreign_field` varchar(64) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Relation table';

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__savedsearches`
--

CREATE TABLE `pma__savedsearches` (
  `id` int(5) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL DEFAULT '',
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `search_name` varchar(64) NOT NULL DEFAULT '',
  `search_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Saved searches';

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__table_coords`
--

CREATE TABLE `pma__table_coords` (
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `pdf_page_number` int(11) NOT NULL DEFAULT 0,
  `x` float UNSIGNED NOT NULL DEFAULT 0,
  `y` float UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Table coordinates for phpMyAdmin PDF output';

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__table_info`
--

CREATE TABLE `pma__table_info` (
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `display_field` varchar(64) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Table information for phpMyAdmin';

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__table_uiprefs`
--

CREATE TABLE `pma__table_uiprefs` (
  `username` varchar(64) NOT NULL,
  `db_name` varchar(64) NOT NULL,
  `table_name` varchar(64) NOT NULL,
  `prefs` text NOT NULL,
  `last_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Tables'' UI preferences';

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__tracking`
--

CREATE TABLE `pma__tracking` (
  `db_name` varchar(64) NOT NULL,
  `table_name` varchar(64) NOT NULL,
  `version` int(10) UNSIGNED NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  `schema_snapshot` text NOT NULL,
  `schema_sql` text DEFAULT NULL,
  `data_sql` longtext DEFAULT NULL,
  `tracking` set('UPDATE','REPLACE','INSERT','DELETE','TRUNCATE','CREATE DATABASE','ALTER DATABASE','DROP DATABASE','CREATE TABLE','ALTER TABLE','RENAME TABLE','DROP TABLE','CREATE INDEX','DROP INDEX','CREATE VIEW','ALTER VIEW','DROP VIEW') DEFAULT NULL,
  `tracking_active` int(1) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Database changes tracking for phpMyAdmin';

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__userconfig`
--

CREATE TABLE `pma__userconfig` (
  `username` varchar(64) NOT NULL,
  `timevalue` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `config_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='User preferences storage for phpMyAdmin';

--
-- Despejando dados para a tabela `pma__userconfig`
--

INSERT INTO `pma__userconfig` (`username`, `timevalue`, `config_data`) VALUES
('root', '2026-07-09 15:38:23', '{\"Console\\/Mode\":\"collapse\",\"lang\":\"pt_BR\",\"NavigationWidth\":189}');

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__usergroups`
--

CREATE TABLE `pma__usergroups` (
  `usergroup` varchar(64) NOT NULL,
  `tab` varchar(64) NOT NULL,
  `allowed` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='User groups with configured menu items';

-- --------------------------------------------------------

--
-- Estrutura para tabela `pma__users`
--

CREATE TABLE `pma__users` (
  `username` varchar(64) NOT NULL,
  `usergroup` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Users and their assignments to user groups';

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `pma__bookmark`
--
ALTER TABLE `pma__bookmark`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `pma__central_columns`
--
ALTER TABLE `pma__central_columns`
  ADD PRIMARY KEY (`db_name`,`col_name`);

--
-- Índices de tabela `pma__column_info`
--
ALTER TABLE `pma__column_info`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `db_name` (`db_name`,`table_name`,`column_name`);

--
-- Índices de tabela `pma__designer_settings`
--
ALTER TABLE `pma__designer_settings`
  ADD PRIMARY KEY (`username`);

--
-- Índices de tabela `pma__export_templates`
--
ALTER TABLE `pma__export_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `u_user_type_template` (`username`,`export_type`,`template_name`);

--
-- Índices de tabela `pma__favorite`
--
ALTER TABLE `pma__favorite`
  ADD PRIMARY KEY (`username`);

--
-- Índices de tabela `pma__history`
--
ALTER TABLE `pma__history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `username` (`username`,`db`,`table`,`timevalue`);

--
-- Índices de tabela `pma__navigationhiding`
--
ALTER TABLE `pma__navigationhiding`
  ADD PRIMARY KEY (`username`,`item_name`,`item_type`,`db_name`,`table_name`);

--
-- Índices de tabela `pma__pdf_pages`
--
ALTER TABLE `pma__pdf_pages`
  ADD PRIMARY KEY (`page_nr`),
  ADD KEY `db_name` (`db_name`);

--
-- Índices de tabela `pma__recent`
--
ALTER TABLE `pma__recent`
  ADD PRIMARY KEY (`username`);

--
-- Índices de tabela `pma__relation`
--
ALTER TABLE `pma__relation`
  ADD PRIMARY KEY (`master_db`,`master_table`,`master_field`),
  ADD KEY `foreign_field` (`foreign_db`,`foreign_table`);

--
-- Índices de tabela `pma__savedsearches`
--
ALTER TABLE `pma__savedsearches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `u_savedsearches_username_dbname` (`username`,`db_name`,`search_name`);

--
-- Índices de tabela `pma__table_coords`
--
ALTER TABLE `pma__table_coords`
  ADD PRIMARY KEY (`db_name`,`table_name`,`pdf_page_number`);

--
-- Índices de tabela `pma__table_info`
--
ALTER TABLE `pma__table_info`
  ADD PRIMARY KEY (`db_name`,`table_name`);

--
-- Índices de tabela `pma__table_uiprefs`
--
ALTER TABLE `pma__table_uiprefs`
  ADD PRIMARY KEY (`username`,`db_name`,`table_name`);

--
-- Índices de tabela `pma__tracking`
--
ALTER TABLE `pma__tracking`
  ADD PRIMARY KEY (`db_name`,`table_name`,`version`);

--
-- Índices de tabela `pma__userconfig`
--
ALTER TABLE `pma__userconfig`
  ADD PRIMARY KEY (`username`);

--
-- Índices de tabela `pma__usergroups`
--
ALTER TABLE `pma__usergroups`
  ADD PRIMARY KEY (`usergroup`,`tab`,`allowed`);

--
-- Índices de tabela `pma__users`
--
ALTER TABLE `pma__users`
  ADD PRIMARY KEY (`username`,`usergroup`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `pma__bookmark`
--
ALTER TABLE `pma__bookmark`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pma__column_info`
--
ALTER TABLE `pma__column_info`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pma__export_templates`
--
ALTER TABLE `pma__export_templates`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `pma__history`
--
ALTER TABLE `pma__history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pma__pdf_pages`
--
ALTER TABLE `pma__pdf_pages`
  MODIFY `page_nr` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pma__savedsearches`
--
ALTER TABLE `pma__savedsearches`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- Banco de dados: `prisao_junina`
--
CREATE DATABASE IF NOT EXISTS `prisao_junina` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `prisao_junina`;

-- --------------------------------------------------------

--
-- Estrutura para tabela `ordens_prisao`
--

CREATE TABLE `ordens_prisao` (
  `id` int(10) UNSIGNED NOT NULL,
  `categoria_alvo` enum('aluno','professor','externo') NOT NULL,
  `nome_alvo` varchar(100) DEFAULT NULL,
  `caracteristicas_externo` text DEFAULT NULL,
  `tipo_pedido` enum('prisao','fianca') NOT NULL DEFAULT 'prisao',
  `status` enum('aguardando_pagamento','procurado','preso','fianca_paga','finalizado') NOT NULL DEFAULT 'aguardando_pagamento',
  `valor_cobrado` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tempo_pena_minutos` int(10) UNSIGNED DEFAULT NULL,
  `hora_captura` datetime DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `ordens_prisao`
--

INSERT INTO `ordens_prisao` (`id`, `categoria_alvo`, `nome_alvo`, `caracteristicas_externo`, `tipo_pedido`, `status`, `valor_cobrado`, `tempo_pena_minutos`, `hora_captura`, `criado_em`) VALUES
(6, 'aluno', 'Adrian Holz (1°1)', NULL, 'prisao', 'finalizado', 2.00, 5, '2026-07-01 19:19:24', '2026-07-01 22:15:48'),
(7, 'aluno', 'Gabriel Holz (1°2)', NULL, 'prisao', 'finalizado', 5.00, 10, '2026-07-01 19:23:13', '2026-07-01 22:21:33'),
(8, 'aluno', 'Adrian Holz (1°1)', NULL, 'prisao', 'finalizado', 10.00, 15, '2026-07-01 19:26:45', '2026-07-01 22:25:25'),
(9, 'externo', NULL, 'camisa verde', 'fianca', 'finalizado', 2.00, 10, '2026-07-01 19:33:27', '2026-07-01 22:32:41'),
(10, 'aluno', 'Adrian Holz (1°1)', NULL, 'prisao', 'preso', 10.00, 15, '2026-07-03 19:49:36', '2026-07-03 22:47:20'),
(11, 'externo', NULL, 'Um cara de casaco preto', 'fianca', 'fianca_paga', 2.00, 5, '2026-07-03 19:53:19', '2026-07-03 22:52:58');

-- --------------------------------------------------------

--
-- Estrutura para tabela `professores`
--

CREATE TABLE `professores` (
  `id` int(10) UNSIGNED NOT NULL,
  `nome` varchar(100) NOT NULL,
  `is_imune` tinyint(1) NOT NULL DEFAULT 0,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `professores`
--

INSERT INTO `professores` (`id`, `nome`, `is_imune`, `criado_em`) VALUES
(1, 'Prof. Carlos Silva', 0, '2026-07-01 22:05:43'),
(2, 'Profa. Maria Souza', 0, '2026-07-01 22:05:43'),
(3, 'Prof. João Pereira', 1, '2026-07-01 22:05:43'),
(4, 'Profa. Ana Lima', 0, '2026-07-01 22:05:43'),
(5, 'Prof. Roberto Alves', 0, '2026-07-01 22:05:43');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `ordens_prisao`
--
ALTER TABLE `ordens_prisao`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_nome_alvo` (`nome_alvo`);

--
-- Índices de tabela `professores`
--
ALTER TABLE `professores`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `ordens_prisao`
--
ALTER TABLE `ordens_prisao`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `professores`
--
ALTER TABLE `professores`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- Banco de dados: `test`
--
CREATE DATABASE IF NOT EXISTS `test` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `test`;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
