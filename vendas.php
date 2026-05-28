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
    <link rel="stylesheet" href="/MAGDA-CREW/public/assets/css/gestao.css">
    
</head>
<body>

<?php include 'sidebar.php'; ?>

    <section class="content">

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <h1>Vendas</h1>
                <p style="color: #666; margin-top: 5px;">Gerencie e acompanhe todos os pedidos da loja.</p>
            </div>
        </div>

        <table class="tabela">
            <thead>
                <tr>
                    <th class="text-center">ID</th>
                    <th class="text-right">Valor Total</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Ações</th>
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
                    <td class="text-center"><strong>#<?= $venda['id'] ?></strong></td>
                    
                    <td class="text-right">
                        R$ <?= number_format($venda['valor_total'], 2, ',', '.') ?>
                    </td>

                    <td class="text-center">
                        <span class="status-badge <?= $statusClass ?>">
                            <?= htmlspecialchars($venda['status'] ?? 'N/A') ?>
                        </span>
                    </td>

                    <td class="text-center">
                        <a href="venda_detalhes.php?id=<?= $venda['id'] ?>" class="btn-acao">Ver Detalhes</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </section>

<script src="public/assets/js/main.js"></script>

</body>
</html>
