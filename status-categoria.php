<?php
require_once __DIR__ . '/src/Config/Database.php';

$pdo = Database::getConnection();

// Pega o ID da URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // 1. Descobre qual é o status atual da categoria
    $stmt = $pdo->prepare("SELECT ativo FROM categorias WHERE id = ?");
    $stmt->execute([$id]);
    $categoria = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($categoria) {
        // 2. Se for 1 (Ativo) vira 0. Se for 0 (Inativo) vira 1.
        $novo_status = ($categoria['ativo'] == 1) ? 0 : 1;
        
        // 3. Salva no banco de dados
        $update = $pdo->prepare("UPDATE categorias SET ativo = ? WHERE id = ?");
        $update->execute([$novo_status, $id]);
    }
}

// 4. Volta automaticamente para a página de categorias
header("Location: categorias.php");
exit;