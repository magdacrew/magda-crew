<?php
require_once __DIR__ . '/../Models/Categoria.php';
require_once __DIR__ . '/../Models/Produto.php';
require_once __DIR__ . '/../Config/Database.php';

class HomeController {
    
    public function index() {
        $tituloDaPagina = "Magda Crew";
        
        $categoriaModel = new Categoria();
        $produtoModel = new Produto();
        
        $categorias = $categoriaModel->buscarTodas();
        $produtos = $produtoModel->buscarTodos();

        $pdo = Database::getConnection();

        $bannersTopo = $pdo->query("
            SELECT * FROM home_banners
            WHERE ativo = 1 AND tipo = 'hero_topo'
            ORDER BY ordem ASC, id ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $bannerBaixo = $pdo->query("
            SELECT * FROM home_banners
            WHERE ativo = 1 AND tipo = 'banner_baixo'
            ORDER BY ordem ASC, id ASC
            LIMIT 1
        ")->fetch(PDO::FETCH_ASSOC);
        
        require_once __DIR__ . '/../../views/pages/home.php';
    }
}