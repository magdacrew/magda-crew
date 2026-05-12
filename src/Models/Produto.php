<?php
// Arquivo: src/Models/Produto.php

require_once __DIR__ . '/../Config/Database.php';

class Produto {
    private $conexao;

    public function __construct() {
        $this->conexao = Database::getConnection();
    }

    // Busca TODOS os produtos ativos (Usado para o botão "Tudo")
    public function buscarTodos() {
        $sql = "SELECT p.*, c.nome as categoria_nome 
                FROM produtos p 
                LEFT JOIN categorias c ON p.categoria_id = c.id 
                WHERE p.ativo = 1 
                ORDER BY p.id DESC";
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Busca produtos de UMA categoria específica (Dinâmico para Camisas, Calças, etc.)
    public function buscarPorCategoria($categoria_id) {
    $sql = "SELECT p.*, c.nome as categoria_nome 
            FROM produtos p 
            LEFT JOIN categorias c ON p.categoria_id = c.id 
            WHERE p.categoria_id = :cat_id AND p.ativo = 1 
            ORDER BY p.id DESC";
    
    $stmt = $this->conexao->prepare($sql);
    $stmt->execute([':cat_id' => $categoria_id]);
    return $stmt->fetchAll();
}

    public function buscarPorId($id) {
        $sql = "SELECT p.*, c.nome as categoria_nome 
                FROM produtos p 
                LEFT JOIN categorias c ON p.categoria_id = c.id 
                WHERE p.id = :id AND p.ativo = 1";
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function buscarVariantes($produto_id) {
        $sql = "SELECT pv.*, t.nome as tamanho_nome, c.nome as cor_nome 
                FROM produto_variantes pv 
                INNER JOIN tamanhos t ON pv.tamanho_id = t.id 
                INNER JOIN cores c ON pv.cor_id = c.id 
                WHERE pv.produto_id = :produto_id AND pv.quantidade_estoque > 0";
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute([':produto_id' => $produto_id]);
        return $stmt->fetchAll();
    }
}