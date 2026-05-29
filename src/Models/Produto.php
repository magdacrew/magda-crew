<?php
// Arquivo: src/Models/Produto.php
require_once __DIR__ . '/../Config/Database.php';

class Produto {
    private $conexao;

    public function __construct() {
        $this->conexao = Database::getConnection();
    }

    public function buscarTodos() {
        $sql = "SELECT p.*, c.nome as categoria_nome, p_img.caminho_imagem,
                       COALESCE(SUM(v.quantidade_estoque), 0) AS total_estoque
                FROM produtos p 
                LEFT JOIN categorias c ON p.categoria_id = c.id 
                LEFT JOIN produto_imagens p_img ON p.id = p_img.produto_id AND p_img.is_principal = 1
                LEFT JOIN produto_variantes v ON p.id = v.produto_id
                WHERE p.ativo = 1 
                GROUP BY p.id
                ORDER BY p.id DESC";
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function buscarPorCategoria($categoria_id) {
        $sql = "SELECT p.*, c.nome as categoria_nome, p_img.caminho_imagem,
                       COALESCE(SUM(v.quantidade_estoque), 0) AS total_estoque
                FROM produtos p 
                LEFT JOIN categorias c ON p.categoria_id = c.id 
                LEFT JOIN produto_imagens p_img ON p.id = p_img.produto_id AND p_img.is_principal = 1
                LEFT JOIN produto_variantes v ON p.id = v.produto_id
                WHERE p.categoria_id = :cat_id AND p.ativo = 1 
                GROUP BY p.id
                ORDER BY p.id DESC";
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute([':cat_id' => $categoria_id]);
        return $stmt->fetchAll();
    }

public function buscarPorNome($termo) {
    // 1. Limpa o termo e divide em palavras
    $termo = trim($termo);
    $palavras = explode(' ', $termo);
    $condicoes = [];
    $parametros = [];

    // 2. Construímos a lógica: Cada palavra digitada DEVE existir em algum campo
    foreach ($palavras as $index => $palavra) {
        $key = ":word" . $index;
        // O uso de parênteses aqui é vital para o OR não quebrar o AND geral
        $condicoes[] = "(p.nome LIKE $key OR c.nome LIKE $key OR p.descricao LIKE $key)";
        $parametros[$key] = '%' . $palavra . '%';
    }

    $whereSql = implode(' AND ', $condicoes);

    // 3. Query robusta
    $sql = "SELECT 
                p.id, p.nome, p.preco, p.descricao, p.categoria_id,
                c.nome as categoria_nome, 
                p_img.caminho_imagem,
                COALESCE(SUM(v.quantidade_estoque), 0) AS total_estoque
            FROM produtos p 
            INNER JOIN categorias c ON p.categoria_id = c.id 
            LEFT JOIN produto_imagens p_img ON p.id = p_img.produto_id AND p_img.is_principal = 1
            LEFT JOIN produto_variantes v ON p.id = v.produto_id
            WHERE ($whereSql) AND p.ativo = 1 
            GROUP BY p.id
            ORDER BY 
                (CASE WHEN p.nome LIKE :priority THEN 1 ELSE 2 END) ASC, 
                p.id DESC";
    
    // Parâmetro de prioridade para o nome do produto aparecer primeiro
    $parametros[':priority'] = '%' . $palavras[0] . '%';

    try {
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro na busca: " . $e->getMessage());
        return [];
    }
}

    public function buscarPorId($id) {
        $sql = "SELECT p.*, c.nome as categoria_nome, p_img.caminho_imagem 
                FROM produtos p 
                LEFT JOIN categorias c ON p.categoria_id = c.id 
                LEFT JOIN produto_imagens p_img ON p.id = p_img.produto_id AND p_img.is_principal = 1
                WHERE p.id = :id AND p.ativo = 1";
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function buscarVariantes($produto_id) {
        $sql = "SELECT pv.*, t.nome as tamanho_nome 
                FROM produto_variantes pv 
                INNER JOIN tamanhos t ON pv.tamanho_id = t.id 
                WHERE pv.produto_id = :produto_id AND pv.quantidade_estoque > 0";
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute([':produto_id' => $produto_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}