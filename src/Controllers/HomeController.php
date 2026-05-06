<?php
// Arquivo: src/Controllers/HomeController.php

require_once __DIR__ . '/../Models/Categoria.php';
require_once __DIR__ . '/../Models/Produto.php';

class HomeController {
    
    public function index() {
        $tituloDaPagina = "Magda Crew - Início";
        
        // Instancia os Models
        $categoriaModel = new Categoria();
        $produtoModel = new Produto();
        
        // Pede os dados ao banco
        $categorias = $categoriaModel->buscarTodas();
        $produtosDestaque = $produtoModel->buscarDestaques();
        
        // Chama a View
        require_once __DIR__ . '/../../views/pages/home.php';
    }
}