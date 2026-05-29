<?php
require_once __DIR__ . '/admin_guard.php';

require_once __DIR__ . '/src/Config/Database.php';

try {
    $pdo = Database::getConnection();
} catch (Exception $e) {
    die("Erro ao conectar: " . $e->getMessage());
}

// Melhoria na Query: Se você tiver uma tabela de usuários, seria ideal fazer um JOIN aqui para pegar o nome do cliente.
$stmt = $pdo->query("
    SELECT *
    FROM vendas
    ORDER BY id DESC
");

$vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/magda-crew/public/assets/images/15.png">
    <title>Vendas - Magda Crew</title>
    
    <link rel="stylesheet" href="/magda-crew/public/assets/css/gestao.css">
    
    <link rel="stylesheet" href="/magda-crew/public/assets/css/produtos.css">
</head>
<body>

<?php include 'sidebar.php'; ?>

<main class="main-content">
    <section class="content">

        <div class="topo-produtos">
            <div>
                <h1>Vendas</h1>
                <p style="color: #666; margin-top: 5px;">Gerencie e acompanhe todos os pedidos da loja.</p>
            </div>
        </div>

        <table class="tabela">
            <thead>
                <tr>
                    <th style="width: 80px;">ID</th>
                    <th>Valor Total</th>
                    <th>Status</th>
                    <th style="width: 150px; text-align: center;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($vendas as $venda): ?>
                    <?php 
                        // Lógica para definir a cor da tag de status
                        $statusClass = 'status-padrao';
                        $statusText = strtolower($venda['status'] ?? '');
                        if ($statusText === 'confirmado' || $statusText === 'pago') $statusClass = 'status-confirmado';
                        elseif ($statusText === 'pendente' || $statusText === 'processando') $statusClass = 'status-pendente';
                        elseif ($statusText === 'cancelado') $statusClass = 'status-cancelado';
                    ?>
                <tr>
                    <td><?= $venda['id'] ?></td>
                    
                    <td>
                        R$ <?= number_format($venda['valor_total'], 2, ',', '.') ?>
                    </td>

                    <td>
                        <span class="status-badge <?= $statusClass ?>">
                            <?= htmlspecialchars($venda['status'] ?? 'N/A') ?>
                        </span>
                    </td>

                    <td style="text-align: center;">
                        <div class="acoes">
                            <a href="venda_detalhes.php?id=<?= $venda['id'] ?>" class="btn-acao">Ver Detalhes</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </section>
</main>

<script src="public/assets/js/main.js"></script>

</body>
</html>