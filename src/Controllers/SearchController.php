<?php
require_once __DIR__ . '/../Models/Produto.php';

class SearchController {
    private $produtoModel;

    public function __construct($db) {
        $this->produtoModel = new Produto($db);
    }

    public function render() {
        $termo = isset($_GET['q']) ? trim($_GET['q']) : '';
        $produtos = [];

        if (!empty($termo)) {
            $produtos = $this->produtoModel->buscarPorNome($termo);
        }

        return [
            'termo' => $termo,
            'produtos' => $produtos
        ];
    }
}