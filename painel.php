<?php
// 1. Puxando a sua classe Database
require_once __DIR__ . '/src/Config/Database.php';

// 2. Iniciando a conexão
try {
    $pdo = Database::getConnection();
} catch (Exception $e) {
    die("Erro ao conectar no painel: " . $e->getMessage());
}

// 3. Consultas no banco de dados para os cards
// Total de usuários
$stmtUsers = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
$totalUsers = $stmtUsers->fetch()['total'];

// Total de produtos
$stmtProdutos = $pdo->query("SELECT COUNT(*) as total FROM produtos");
$totalProdutos = $stmtProdutos->fetch()['total'];

// Total de vendas
$stmtVendas = $pdo->query("SELECT COUNT(*) as total FROM vendas WHERE status = 'confirmado'");
$totalVendas = $stmtVendas->fetch()['total'];

// Faturamento total
$stmtFaturamento = $pdo->query("SELECT SUM(valor_total) as total FROM vendas WHERE status = 'confirmado'");
$faturamentoTotal = $stmtFaturamento->fetch()['total'];
// Formata o valor para Real (R$), se for nulo, mostra 0,00
$faturamentoFormatado = $faturamentoTotal ? number_format($faturamentoTotal, 2, ',', '.') : '0,00';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel - Magda Crew</title>
    <link rel="stylesheet" href="/MAGDA-CREW/public/assets/css/gestao.css">
</head>
<body>

    <aside class="sidebar" id="sidebar">
    <div class="logo-container">
        <img src="public/assets/images/MagdaWhiteLogo.png" alt="Magda Crew Logo" class="logo-img">
    </div>
        <nav>
            <ul>
                <li><a href="painel.php">Dashboard</a></li>
                <li><a href="#">Produtos</a></li>
                <li><a href="#">Categorias & Variantes</a></li>
                <li><a href="#">Vendas</a></li>
                <li><a href="#">Clientes</a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <header class="header">
            <button id="menuToggle" style="background:none;border:none;cursor:pointer;font-size:20px;">☰</button>
            <div class="user-info">
                <span>Bem-vindo(a), Admin</span>
                <a href="logout.php" style="margin-left: 15px; color: #d9534f; text-decoration: none; font-weight: bold;">Sair</a>
            </div>
        </header>

        <section class="content">
            <h1>Visão Geral</h1>
            <p style="margin-bottom: 20px; color: #666;">Acompanhe os números oficiais da sua loja.</p>

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
                    <h3>Clientes (Usuários)</h3>
                    <div class="number"><?= $totalUsers ?></div>
                </div>
            </div>
        </section>
    </main>

    <script src="public/assets/js/main.js"></script>
</body>
</html>