<?php
require_once __DIR__ . '/admin_guard.php';

require_once __DIR__ . '/src/Config/Database.php';

$pdo = Database::getConnection();

$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT ativo FROM produtos WHERE id = ?");
$stmt->execute([$id]);

$produto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produto) {
    die("Produto não encontrado.");
}

$novoStatus = $produto['ativo'] ? 0 : 1;

$update = $pdo->prepare("
    UPDATE produtos
    SET ativo = ?
    WHERE id = ?
");

$update->execute([
    $novoStatus,
    $id
]);

header("Location: produtos.php");
exit;