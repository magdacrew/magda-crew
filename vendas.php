<?php
require_once __DIR__ . '/src/Config/Database.php';

try {
    $pdo = Database::getConnection();
} catch (Exception $e) {
    die("Erro ao conectar: " . $e->getMessage());
}

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

        <h1>Vendas</h1>

        <p style="margin-bottom: 20px; color: #666;">
            Lista de vendas realizadas na loja.
        </p>

        <table class="tabela">

            <tr>
                <th>ID</th>
                <th>Valor Total</th>
                <th>Status</th>
            </tr>

            <?php foreach($vendas as $venda): ?>

            <tr>
                <td><?= $venda['id'] ?></td>

                <td>
                    R$ <?= number_format($venda['valor_total'], 2, ',', '.') ?>
                </td>

                <td><?= $venda['status'] ?></td>
            </tr>

            <?php endforeach; ?>

        </table>

    </section>

</main>

<script src="public/assets/js/main.js"></script>

</body>
</html>