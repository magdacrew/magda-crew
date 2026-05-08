<?php
// Arquivo: src/Models/Categoria.php

require_once __DIR__ . '/../Config/Database.php';

class Categoria {
    private $conexao;

    public function __construct() {
        // Pega a conexão com o banco sempre que o Model for instanciado
        $this->conexao = Database::getConnection();
    }

    // Método para buscar todas as categorias
    public function buscarTodas() {
        $sql = "SELECT * FROM categorias ORDER BY id ASC";
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute();
        
        // Retorna um array com todas as categorias
        return $stmt->fetchAll();
    }
}