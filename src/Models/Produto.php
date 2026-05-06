<?php
// Arquivo: src/Models/Produto.php

require_once __DIR__ . '/../Config/Database.php';

class Produto {
    private $conexao;

    public function __construct() {
        $this->conexao = Database::getConnection();
    }

    // Busca apenas os produtos marcados como destaque e que estão ativos
    public function buscarDestaques() {
        // Usamos um JOIN para trazer também o nome da categoria do produto
        $sql = "SELECT p.*, c.nome as categoria_nome 
                FROM produtos p 
                LEFT JOIN categorias c ON p.categoria_id = c.id 
                WHERE p.destaque = 1 AND p.ativo = 1 
                ORDER BY p.id DESC LIMIT 6";
                
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    // Busca os dados de um produto específico pelo ID
    public function buscarPorId($id) {
        $sql = "SELECT p.*, c.nome as categoria_nome 
                FROM produtos p 
                LEFT JOIN categorias c ON p.categoria_id = c.id 
                WHERE p.id = :id AND p.ativo = 1";
                
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute([':id' => $id]);
        
        return $stmt->fetch(); // Retorna apenas 1 registro (fetch em vez de fetchAll)
    }

    // Busca as opções de tamanho e cor que tem em estoque para este produto
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

?>