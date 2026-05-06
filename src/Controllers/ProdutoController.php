<?php
// Arquivo: src/Controllers/ProdutoController.php

require_once __DIR__ . '/../Models/Produto.php';
require_once __DIR__ . '/../Models/Categoria.php'; // Precisamos para o menu do Header

class ProdutoController {
    
    // O $id é preenchido automaticamente pela nossa rota no index.php
    public function detalhes($id = null) {
        if (!$id) {
            // Se tentarem acessar sem ID, manda de volta pra home
            header("Location: /MAGDA-CREW/public/");
            exit;
        }

        $produtoModel = new Produto();
        $produto = $produtoModel->buscarPorId($id);

        if (!$produto) {
            echo "<h1>Produto não encontrado ou inativo.</h1>";
            return;
        }

        // Busca as opções (M, G, Preto, Branco...)
        $variantes = $produtoModel->buscarVariantes($id);

        // Busca categorias para montar o menu no header
        $categoriaModel = new Categoria();
        $categorias = $categoriaModel->buscarTodas();

        // Variáveis que vão para a View
        $tituloDaPagina = "Magda Crew - " . $produto['nome'];
        
        // Vamos criar um CSS específico para essa página
        $cssExtra = '<link rel="stylesheet" href="/MAGDA-CREW/public/assets/css/produto.css">';

        require_once __DIR__ . '/../../views/pages/produto_detalhe.php';
    }
}