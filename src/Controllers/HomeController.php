<?php
// Arquivo: src/Controllers/HomeController.php

require_once __DIR__ . '/../Models/Categoria.php';
require_once __DIR__ . '/../Models/Produto.php';

class HomeController {
    
    public function index() {
        $tituloDaPagina = "Magda Crew";
        
        $categoriaModel = new Categoria();
        $produtoModel = new Produto();
        
        $categorias = $categoriaModel->buscarTodas();
        // Carrega todos os produtos por padrão
        $produtos = $produtoModel->buscarTodos(); 
        
        require_once __DIR__ . '/../../views/pages/home.php';
    }
}