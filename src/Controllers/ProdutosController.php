<?php
// Arquivo: src/Controllers/ProdutoController.php

require_once __DIR__ . '/../Models/Produto.php';
require_once __DIR__ . '/../Models/Categoria.php';

// Arquivo: src/Controllers/ProdutosController.php
class ProdutosController { // Adicione o S aqui
    
    // Mude o nome do método de 'filtrar' para 'categoria'
    public function categoria($id) { 
        $produtoModel = new Produto();
        $categoriaModel = new Categoria();

        $categorias = $categoriaModel->buscarTodas();

        if ($id == 1) {
            $produtos = $produtoModel->buscarTodos();
        } else {
            $produtos = $produtoModel->buscarPorCategoria($id);
        }

        require_once __DIR__ . '/../../views/pages/home.php';
    }

    public function detalhes($id = null) {
        if (!$id) {
            header("Location: /MAGDA-CREW/public/index.php");
            exit;
        }

        $produtoModel = new Produto();
        $produto = $produtoModel->buscarPorId($id);

        if (!$produto) {
            echo "<h1>Produto não encontrado.</h1>";
            return;
        }

        $variantes = $produtoModel->buscarVariantes($id);
        $categoriaModel = new Categoria();
        $categorias = $categoriaModel->buscarTodas();
        
        $tituloDaPagina = "Magda Crew - " . $produto['nome'];
        $cssExtra = '<link rel="stylesheet" href="/MAGDA-CREW/public/assets/css/produto.css">';

        require_once __DIR__ . '/../../views/pages/produto_detalhe.php';
    }
}