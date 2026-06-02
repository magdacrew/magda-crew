<?php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../Config/Database.php';

// Limpa qualquer saída anterior para não quebrar o JSON
if (ob_get_length()) ob_clean();

try {
    $pdo = Database::getConnection();
    $variante_id = $_POST['variante_id'] ?? null;
    $session_id = session_id();
    $usuario_id = $_SESSION['usuario_id'] ?? null;

    if (!$variante_id) {
        echo json_encode(['success' => false, 'message' => 'ID da variante não enviado.']);
        exit;
    }

    // 1. Primeiro, buscamos o item para saber a quantidade atual
    $stmt = $pdo->prepare("SELECT id, quantidade FROM carrinho WHERE variante_id = ? AND (usuario_id = ? OR session_id = ?)");
    $stmt->execute([$variante_id, $usuario_id ?? 0, $session_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($item) {
        if ($item['quantidade'] > 1) {
            // 2. Se tiver mais de um, apenas subtrai 1 da quantidade
            $stmtUpdate = $pdo->prepare("UPDATE carrinho SET quantidade = quantidade - 1 WHERE id = ?");
            $stmtUpdate->execute([$item['id']]);
            $acao = 'subtraido';
        } else {
            // 3. Se tiver apenas 1, remove o registro completamente
            $stmtDelete = $pdo->prepare("DELETE FROM carrinho WHERE id = ?");
            $stmtDelete->execute([$item['id']]);
            $acao = 'removido';
        }
        echo json_encode(['success' => true, 'acao' => $acao]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Item não encontrado no carrinho.']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro no servidor: ' . $e->getMessage()]);
}