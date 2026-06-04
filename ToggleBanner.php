<?php
require_once __DIR__ . '/AdminGuard.php';
require_once __DIR__ . '/src/Config/Database.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $pdo = Database::getConnection();

    // Busca o estado atual do banner
    $stmt = $pdo->prepare("SELECT ativo FROM home_banners WHERE id = ?");
    $stmt->execute([$id]);
    $banner = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($banner) {
        // Inverte o status (se for 1 vira 0, se for 0 vira 1)
        $novoStatus = $banner['ativo'] == 1 ? 0 : 1;

        $update = $pdo->prepare("UPDATE home_banners SET ativo = ? WHERE id = ?");
        $update->execute([$novoStatus, $id]);
    }
}

// Retorna imediatamente à listagem de banners
header("Location: Banners.php");
exit;