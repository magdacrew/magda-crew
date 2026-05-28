<?php
require_once __DIR__ . '/admin_guard.php';

require_once __DIR__ . '/src/Config/Database.php';

try {
    $pdo = Database::getConnection();
} catch (Exception $e) {
    die("Erro ao conectar no painel: " . $e->getMessage());
}

// ── KPIs Principais ──────────────────────────────────────────────────────────
$totalUsers        = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
$totalProdutos     = $pdo->query("SELECT COUNT(*) FROM produtos")->fetchColumn();
$totalAtivo        = $pdo->query("SELECT COUNT(*) FROM produtos WHERE ativo = 1")->fetchColumn();
$totalCategorias   = $pdo->query("SELECT COUNT(*) FROM categorias WHERE ativo = 1")->fetchColumn();

$rowVendas         = $pdo->query("SELECT COUNT(*) as qtd, COALESCE(SUM(valor_total),0) as fat FROM vendas WHERE status = 'confirmado'")->fetch();
$totalVendas       = $rowVendas['qtd'];
$faturamentoTotal  = $rowVendas['fat'];

$rowCanceladas     = $pdo->query("SELECT COUNT(*) as qtd FROM vendas WHERE status = 'cancelado'")->fetch();
$totalCanceladas   = $rowCanceladas['qtd'];

$ticketMedio       = $totalVendas > 0 ? $faturamentoTotal / $totalVendas : 0;

// ── Estoque total e alertas ──────────────────────────────────────────────────
$totalEstoque      = $pdo->query("SELECT COALESCE(SUM(quantidade_estoque),0) FROM produto_variantes")->fetchColumn();
$estoqueBaixo      = $pdo->query("
    SELECT p.nome, pv.quantidade_estoque, t.nome AS tamanho, c.nome AS cor
    FROM produto_variantes pv
    JOIN produtos p ON p.id = pv.produto_id
    JOIN tamanhos t ON t.id = pv.tamanho_id
    JOIN cores c    ON c.id = pv.cor_id
    WHERE pv.quantidade_estoque <= 5
    ORDER BY pv.quantidade_estoque ASC
    LIMIT 6
")->fetchAll();

// ── Produtos mais vendidos ───────────────────────────────────────────────────
$maisProdutos = $pdo->query("
    SELECT iv.produto_nome,
           SUM(iv.quantidade) AS total_vendido,
           SUM(iv.quantidade * iv.preco_unitario) AS receita
    FROM itens_venda iv
    JOIN vendas v ON v.id = iv.venda_id
    WHERE v.status = 'confirmado'
    GROUP BY iv.produto_nome
    ORDER BY total_vendido DESC
    LIMIT 5
")->fetchAll();

// ── Últimas vendas ───────────────────────────────────────────────────────────
$ultimasVendas = $pdo->query("
    SELECT v.id, v.valor_total, v.status, v.data_venda,
           v.forma_pagamento, v.frete_tipo,
           u.email AS cliente_email
    FROM vendas v
    LEFT JOIN usuarios u ON u.id = v.usuario_id
    ORDER BY v.data_venda DESC
    LIMIT 5
")->fetchAll();

// ── Clientes recentes ────────────────────────────────────────────────────────
$clientesRecentes = $pdo->query("
    SELECT id, email, data_cadastro, ultimo_login
    FROM usuarios
    ORDER BY data_cadastro DESC
    LIMIT 5
")->fetchAll();

// ── Distribuição de status das vendas ────────────────────────────────────────
$statusVendas = $pdo->query("
    SELECT status, COUNT(*) as total
    FROM vendas
    GROUP BY status
")->fetchAll();

// ── Produto com mais estoque ─────────────────────────────────────────────────
$topEstoque = $pdo->query("
    SELECT p.nome, SUM(pv.quantidade_estoque) AS total
    FROM produto_variantes pv
    JOIN produtos p ON p.id = pv.produto_id
    GROUP BY p.nome
    ORDER BY total DESC
    LIMIT 5
")->fetchAll();

// ── Formatação ───────────────────────────────────────────────────────────────
$faturamentoFmt = 'R$ ' . number_format($faturamentoTotal, 2, ',', '.');
$ticketMedioFmt = 'R$ ' . number_format($ticketMedio, 2, ',', '.');
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="/magda-crew/public/assets/images/15.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel – Magda Crew</title>
    <link rel="stylesheet" href="/MAGDA-CREW/public/assets/css/gestao.css">
    <link rel="stylesheet" href="/MAGDA-CREW/public/assets/css/painel.css">
    <!-- Chart.js para os gráficos -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>

<?php include 'sidebar.php'; ?>

<section class="content">

    <!-- ── Cabeçalho ── -->
    <div class="dash-header">
        <div>
            <h1>Visão Geral</h1>
            <p class="dash-subtitle">Resumo executivo da MAGDA CREW</p>
        </div>
        <span class="dash-date"><?= date('d/m/Y') ?></span>
    </div>

    <!-- ── KPI Cards ── -->
    <div class="kpi-grid">
        <div class="kpi-card kpi-green">
            <div class="kpi-icon">💰</div>
            <div class="kpi-info">
                <span class="kpi-label">Faturamento Confirmado</span>
                <strong class="kpi-value"><?= $faturamentoFmt ?></strong>
            </div>
        </div>
        <div class="kpi-card kpi-blue">
            <div class="kpi-icon">🛒</div>
            <div class="kpi-info">
                <span class="kpi-label">Vendas Confirmadas</span>
                <strong class="kpi-value"><?= $totalVendas ?></strong>
            </div>
        </div>
        <div class="kpi-card kpi-purple">
            <div class="kpi-icon">🎯</div>
            <div class="kpi-info">
                <span class="kpi-label">Ticket Médio</span>
                <strong class="kpi-value"><?= $ticketMedioFmt ?></strong>
            </div>
        </div>
        <div class="kpi-card kpi-gray">
            <div class="kpi-icon">👥</div>
            <div class="kpi-info">
                <span class="kpi-label">Clientes Cadastrados</span>
                <strong class="kpi-value"><?= $totalUsers ?></strong>
            </div>
        </div>
        <div class="kpi-card kpi-dark">
            <div class="kpi-icon">👕</div>
            <div class="kpi-info">
                <span class="kpi-label">Produtos Ativos</span>
                <strong class="kpi-value"><?= $totalAtivo ?> / <?= $totalProdutos ?></strong>
            </div>
        </div>
        <div class="kpi-card kpi-red">
            <div class="kpi-icon">❌</div>
            <div class="kpi-info">
                <span class="kpi-label">Vendas Canceladas</span>
                <strong class="kpi-value"><?= $totalCanceladas ?></strong>
            </div>
        </div>
        <div class="kpi-card kpi-orange">
            <div class="kpi-icon">📦</div>
            <div class="kpi-info">
                <span class="kpi-label">Unidades em Estoque</span>
                <strong class="kpi-value"><?= $totalEstoque ?></strong>
            </div>
        </div>
        <div class="kpi-card kpi-teal">
            <div class="kpi-icon">🏷️</div>
            <div class="kpi-info">
                <span class="kpi-label">Categorias Ativas</span>
                <strong class="kpi-value"><?= $totalCategorias ?></strong>
            </div>
        </div>
    </div>

    <!-- ── Linha de Gráficos ── -->
    <div class="charts-row">

        <!-- Gráfico: Status das Vendas (Donut) -->
        <div class="chart-box">
            <h2 class="chart-title">Status das Vendas</h2>
            <div class="chart-wrap chart-wrap-sm">
                <canvas id="chartStatus"></canvas>
            </div>
        </div>

        <!-- Gráfico: Estoque por Produto (Barras) -->
        <div class="chart-box chart-box-wide">
            <h2 class="chart-title">Estoque por Produto</h2>
            <div class="chart-wrap">
                <canvas id="chartEstoque"></canvas>
            </div>
        </div>

    </div>

    <!-- ── Linha: Mais Vendidos + Alertas de Estoque ── -->
    <div class="tables-row">

        <!-- Produtos mais vendidos -->
        <div class="table-box">
            <h2 class="section-title">🏆 Produtos Mais Vendidos</h2>
            <?php if (empty($maisProdutos)): ?>
                <p class="empty-msg">Nenhuma venda confirmada registrada ainda.</p>
            <?php else: ?>
            <table class="tabela">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Produto</th>
                        <th class="text-center">Qtd</th>
                        <th class="text-right">Receita</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($maisProdutos as $i => $p): ?>
                    <tr>
                        <td><span class="rank rank-<?= $i + 1 ?>"><?= $i + 1 ?></span></td>
                        <td><?= htmlspecialchars($p['produto_nome']) ?></td>
                        <td class="text-center"><strong><?= $p['total_vendido'] ?></strong></td>
                        <td class="text-right">R$ <?= number_format($p['receita'], 2, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <!-- Alertas de estoque baixo -->
        <div class="table-box">
            <h2 class="section-title">⚠️ Alertas de Estoque Baixo</h2>
            <?php if (empty($estoqueBaixo)): ?>
                <p class="empty-msg" style="color:#10b981;">✅ Estoque saudável em todos os produtos.</p>
            <?php else: ?>
            <table class="tabela">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th class="text-center">Tam.</th>
                        <th class="text-center">Cor</th>
                        <th class="text-center">Qtd</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($estoqueBaixo as $e): ?>
                    <tr>
                        <td><?= htmlspecialchars($e['nome']) ?></td>
                        <td class="text-center"><?= htmlspecialchars($e['tamanho']) ?></td>
                        <td class="text-center"><?= htmlspecialchars($e['cor']) ?></td>
                        <td class="text-center">
                            <span class="badge-estoque <?= $e['quantidade_estoque'] == 0 ? 'badge-zero' : 'badge-baixo' ?>">
                                <?= $e['quantidade_estoque'] ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

    </div>

    <!-- ── Linha: Últimas Vendas + Clientes Recentes ── -->
    <div class="tables-row">

        <!-- Últimas Vendas -->
        <div class="table-box">
            <h2 class="section-title">🕐 Últimas Vendas</h2>
            <?php if (empty($ultimasVendas)): ?>
                <p class="empty-msg">Nenhuma venda registrada.</p>
            <?php else: ?>
            <table class="tabela">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th class="text-right">Valor</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Data</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ultimasVendas as $v): 
                        $sc = 'status-padrao';
                        $st = strtolower($v['status'] ?? '');
                        if ($st === 'confirmado' || $st === 'pago') $sc = 'status-confirmado';
                        elseif ($st === 'pendente' || $st === 'processando') $sc = 'status-pendente';
                        elseif ($st === 'cancelado') $sc = 'status-cancelado';
                    ?>
                    <tr>
                        <td><a href="venda_detalhes.php?id=<?= $v['id'] ?>" class="link-id">#<?= $v['id'] ?></a></td>
                        <td class="td-email"><?= htmlspecialchars($v['cliente_email'] ?? 'Visitante') ?></td>
                        <td class="text-right">R$ <?= number_format($v['valor_total'], 2, ',', '.') ?></td>
                        <td class="text-center"><span class="status-badge <?= $sc ?>"><?= htmlspecialchars($v['status']) ?></span></td>
                        <td class="text-center"><?= date('d/m/Y', strtotime($v['data_venda'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="ver-mais-wrap">
                <a href="vendas.php" class="btn-ver-mais">Ver todas as vendas →</a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Clientes Recentes -->
        <div class="table-box">
            <h2 class="section-title">👤 Clientes Recentes</h2>
            <?php if (empty($clientesRecentes)): ?>
                <p class="empty-msg">Nenhum cliente cadastrado.</p>
            <?php else: ?>
            <table class="tabela">
                <thead>
                    <tr>
                        <th>E-mail</th>
                        <th class="text-center">Cadastro</th>
                        <th class="text-center">Último Login</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientesRecentes as $c): ?>
                    <tr>
                        <td class="td-email">
                            <a href="cliente_detalhes.php?id=<?= $c['id'] ?>" class="link-id">
                                <?= htmlspecialchars($c['email']) ?>
                            </a>
                        </td>
                        <td class="text-center"><?= date('d/m/Y', strtotime($c['data_cadastro'])) ?></td>
                        <td class="text-center">
                            <?= $c['ultimo_login'] ? date('d/m/Y', strtotime($c['ultimo_login'])) : '<span style="color:#aaa">—</span>' ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="ver-mais-wrap">
                <a href="clientes.php" class="btn-ver-mais">Ver todos os clientes →</a>
            </div>
            <?php endif; ?>
        </div>

    </div>

</section><!-- .content -->

<!-- ── Chart.js Scripts ── -->
<script>
// Dados injetados via PHP
const statusLabels = <?= json_encode(array_column($statusVendas, 'status')) ?>;
const statusData   = <?= json_encode(array_map('intval', array_column($statusVendas, 'total'))) ?>;

const estoqueLabels = <?= json_encode(array_column($topEstoque, 'nome')) ?>;
const estoqueData   = <?= json_encode(array_map('intval', array_column($topEstoque, 'total'))) ?>;

// Paleta da marca (preto/tons neutros + acentos)
const palette = ['#10b981','#f59e0b','#ef4444','#6b7280','#3b82f6'];

// ── Donut: Status das Vendas ──
new Chart(document.getElementById('chartStatus'), {
    type: 'doughnut',
    data: {
        labels: statusLabels,
        datasets: [{
            data: statusData,
            backgroundColor: palette,
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        cutout: '60%',
        plugins: {
            legend: { position: 'bottom', labels: { font: { family: 'Inter', size: 13 } } }
        }
    }
});

// ── Barras: Estoque por Produto ──
new Chart(document.getElementById('chartEstoque'), {
    type: 'bar',
    data: {
        labels: estoqueLabels,
        datasets: [{
            label: 'Unidades em Estoque',
            data: estoqueData,
            backgroundColor: '#111',
            borderRadius: 6,
            borderSkipped: false
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { stepSize: 5, font: { family: 'Inter' } },
                grid: { color: '#f0f0f0' }
            },
            x: {
                ticks: {
                    font: { family: 'Inter', size: 11 },
                    maxRotation: 20
                },
                grid: { display: false }
            }
        }
    }
});
</script>

<script src="public/assets/js/main.js"></script>
</body>
</html>
