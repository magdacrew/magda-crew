<?php
require_once __DIR__ . '/src/Config/Database.php';

try {
    $pdo = Database::getConnection();
} catch (Exception $e) {
    die("Erro ao conectar no painel: " . $e->getMessage());
}

$stmtUsers = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
$totalUsers = $stmtUsers->fetch()['total'];

$stmtProdutos = $pdo->query("SELECT COUNT(*) as total FROM produtos");
$totalProdutos = $stmtProdutos->fetch()['total'];

$stmtVendas = $pdo->query("SELECT COUNT(*) as total FROM vendas WHERE status = 'confirmado'");
$totalVendas = $stmtVendas->fetch()['total'];

$stmtFaturamento = $pdo->query("SELECT SUM(valor_total) as total FROM vendas WHERE status = 'confirmado'");
$faturamentoTotal = $stmtFaturamento->fetch()['total'];

$faturamentoFormatado = $faturamentoTotal ? number_format($faturamentoTotal, 2, ',', '.') : '0,00';
$stmproduto = $pdo->query("SELECT COUNT(*) as total FROM produtos WHERE ativo = 1");
$totalAtivo = $stmproduto->fetch()['total'];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="/magda-crew/public/assets/images/15.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel - Magda Crew</title>
    <link rel="stylesheet" href="/MAGDA-CREW/public/assets/css/gestao.css">
</head>
<body>



    <section class="content">
        <h1>Visão Geral</h1>

        <div class="dashboard-cards">
            <div class="card">
                <h3>Faturamento Total</h3>
                <div class="number">R$ <?= $faturamentoFormatado ?></div>
            </div>

            <div class="card">
                <h3>Vendas Realizadas</h3>
                <div class="number"><?= $totalVendas ?></div>
            </div>

            <div class="card">
                <h3>Produtos Cadastrados</h3>
                <div class="number"><?= $totalProdutos ?></div>
            </div>

            <div class="card">
                <h3>Clientes</h3>
                <div class="number"><?= $totalUsers ?></div>
            </div>

            <div class="card">
                <h3>Produtos ativados</h3>
                <div class="number"><?= $totalAtivo ?></div>
            </div>

        </div>
    </section>
</main>

</body>
</html>