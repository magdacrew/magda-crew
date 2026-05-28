<?php
require_once __DIR__ . '/admin_guard.php';

require_once __DIR__ . '/src/Config/Database.php';

// 1. Pega o ID da venda que veio na URL (ex: venda_detalhes.php?id=5)
$venda_id = $_GET['id'] ?? null;

if (!$venda_id) {
    die("ID da venda não informado. Volte e selecione uma venda válida.");
}

try {
    $pdo = Database::getConnection();
} catch (Exception $e) {
    die("Erro ao conectar: " . $e->getMessage());
}

// 2. Se você mudar o status no select e clicar em "Atualizar", ele salva no banco aqui:
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['novo_status'])) {
    $novo_status = $_POST['novo_status'];
    $stmtUpdate = $pdo->prepare("UPDATE vendas SET status = ? WHERE id = ?");
    $stmtUpdate->execute([$novo_status, $venda_id]);
    
    // Atualiza a página para mostrar o novo status
    header("Location: venda_detalhes.php?id=" . $venda_id);
    exit;
}

// 3. Busca os dados gerais da venda
$stmtVenda = $pdo->prepare("SELECT * FROM vendas WHERE id = ?");
$stmtVenda->execute([$venda_id]);
$venda = $stmtVenda->fetch(PDO::FETCH_ASSOC);

if (!$venda) {
    die("Venda não encontrada no banco de dados.");
}

// 4. Busca os produtos que estão dentro desta venda
$stmtItens = $pdo->prepare("SELECT * FROM itens_venda WHERE venda_id = ?");
$stmtItens->execute([$venda_id]);
$itens = $stmtItens->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/magda-crew/public/assets/images/15.png">
    <title>Detalhes da Venda #<?= htmlspecialchars($venda['id']) ?> - Magda Crew</title>
    <link rel="stylesheet" href="/MAGDA-CREW/public/assets/css/gestao.css">

</head>
<body>

<?php include 'sidebar.php'; ?>

    <section class="content">

        <div style="margin-bottom: 20px;">
            <a href="javascript:history.back()" class="btn-voltar">← Voltar para Vendas</a>
        </div>

        <h1>Detalhes do Pedido #<?= htmlspecialchars($venda['id']) ?></h1>

        <div class="painel-detalhes">
            <div class="painel-header">
                <h2>Resumo</h2>
                
                <form method="POST" class="form-status">
                    <label style="color: #888; font-size: 0.9rem;">Status atual:</label>
                    <select name="novo_status">
                        <option value="pendente" <?= ($venda['status'] == 'pendente') ? 'selected' : '' ?>>Pendente</option>
                        <option value="processando" <?= ($venda['status'] == 'processando') ? 'selected' : '' ?>>Processando</option>
                        <option value="confirmado" <?= ($venda['status'] == 'confirmado') ? 'selected' : '' ?>>Confirmado</option>
                        <option value="enviado" <?= ($venda['status'] == 'enviado') ? 'selected' : '' ?>>Enviado</option>
                        <option value="entregue" <?= ($venda['status'] == 'entregue') ? 'selected' : '' ?>>Entregue</option>
                        <option value="cancelado" <?= ($venda['status'] == 'cancelado') ? 'selected' : '' ?>>Cancelado</option>
                    </select>
                    <button type="submit" class="btn-salvar">Atualizar</button>
                </form>
            </div>

            <div class="info-grid">
                <div class="info-box">
                    <span>Subtotal</span>
                    <strong>R$ <?= number_format($venda['subtotal'] ?? 0, 2, ',', '.') ?></strong>
                </div>
                <div class="info-box">
                    <span>Frete (<?= strtoupper($venda['frete_tipo'] ?? 'N/A') ?>)</span>
                    <strong>R$ <?= number_format($venda['valor_frete'] ?? 0, 2, ',', '.') ?></strong>
                </div>
                <div class="info-box">
                    <span>Valor Total</span>
                    <strong style="color: #28a745;">R$ <?= number_format($venda['valor_total'], 2, ',', '.') ?></strong>
                </div>
                <div class="info-box">
                    <span>ID do Cliente</span>
                    <strong><?= htmlspecialchars($venda['usuario_id'] ?? 'Não registrado') ?></strong>
                </div>
            </div>
        </div>

        <h2>Produtos do Pedido</h2>
        <table class="tabela">
            <thead>
                <tr>
                    <th style="text-align: left;">Produto</th>
                    <th style="text-align: center;">Tamanho/Cor</th>
                    <th style="text-align: center;">Quantidade</th>
                    <th style="text-align: right;">Preço Unit.</th>
                    <th style="text-align: right;">Total do Item</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($itens)): ?>
                    <?php foreach($itens as $item): ?>
                        <?php $totalItem = $item['quantidade'] * $item['preco_unitario']; ?>
                        <tr>
                            <td style="text-align: left;">
                                <strong><?= htmlspecialchars(strtoupper($item['produto_nome'])) ?></strong>
                            </td>
                            <td style="text-align: center;">
                                <?= htmlspecialchars($item['tamanho_nome'] ?? '-') ?> / <?= htmlspecialchars($item['cor_nome'] ?? '-') ?>
                            </td>
                            <td style="text-align: center;"><?= $item['quantidade'] ?></td>
                            <td style="text-align: right;">R$ <?= number_format($item['preco_unitario'], 2, ',', '.') ?></td>
                            <td style="text-align: right; font-weight: bold;">R$ <?= number_format($totalItem, 2, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 20px;">Nenhum item encontrado para esta venda.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

    </section>

</body>
</html>