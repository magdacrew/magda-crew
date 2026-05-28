<?php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../Config/Database.php';

ob_clean(); // Limpa saídas indesejadas que quebram o JSON

try {
    $pdo = Database::getConnection();
    $variante_id = $_POST['variante_id'] ?? null;
    $session_id = session_id(); 
    $usuario_id = $_SESSION['usuario_id'] ?? null;

    if (!$variante_id) {
        echo json_encode(['success' => false, 'message' => 'Selecione um tamanho.']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT id FROM carrinho WHERE (usuario_id = ? OR session_id = ?) AND variante_id = ?");
    $stmt->execute([$usuario_id, $session_id, $variante_id]);
    $item = $stmt->fetch();

    if ($item) {
        $stmt = $pdo->prepare("UPDATE carrinho SET quantidade = quantidade + 1 WHERE id = ?");
        $stmt->execute([$item['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO carrinho (session_id, usuario_id, variante_id, quantidade) VALUES (?, ?, ?, 1)");
        $stmt->execute([$session_id, $usuario_id, $variante_id]);
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}