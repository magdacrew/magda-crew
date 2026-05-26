<?php
session_start();

// Volta duas pastas e entra em src/Config
require_once __DIR__ . '/../../src/Config/Database.php';

$pdo = Database::getConnection();

// Pega o ID do utilizador logado
$usuario_id = $_SESSION['usuario_id'] ?? 1;

// 1. Busca os dados principais das vendas desse utilizador
$stmtVendas = $pdo->prepare("
    SELECT id, valor_total, valor_frete, subtotal, frete_tipo, status, data_venda 
    FROM vendas 
    WHERE usuario_id = ? 
    ORDER BY data_venda DESC
");
$stmtVendas->execute([$usuario_id]);
$pedidos = $stmtVendas->fetchAll(PDO::FETCH_ASSOC);

// 2. Busca os ITENS de cada venda e vai buscar a imagem correta à tabela 'produto_imagens'
// Faz a ligação pelo nome do produto e garante que apanha apenas a imagem principal
$stmtItens = $pdo->prepare("
    SELECT 
        i.produto_nome, 
        i.quantidade, 
        i.preco_unitario, 
        i.tamanho_nome, 
        i.cor_nome,
        pi.caminho_imagem
    FROM itens_venda i
    LEFT JOIN produtos p ON p.nome = i.produto_nome
    LEFT JOIN produto_imagens pi ON pi.produto_id = p.id AND pi.is_principal = 1
    WHERE i.venda_id = ?
");

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Pedidos - Magda Crew</title>
    <link rel="stylesheet" href="/magda-crew/public/assets/css/orders.css"> 
</head>
<body>

    <div class="topbar">
        <div class="top-content">
            <div class="menu">
                <a href="/magda-crew/public/index.php">
                    <img src="/magda-crew/public/assets/images/MagdaWhiteLogo.png" class="logo" alt="Magda Crew Logo">
                </a>
                
                <a href="/magda-crew/views/pages/orders.php" style="text-decoration: underline;">Orders</a>
                <a href="/magda-crew/views/pages/Profile.php">Profile</a>
            </div>

            <a href="javascript:history.back()">
        <img src="/magda-crew/public/assets/images/X.png" alt="Voltar" class="profile-icon">
</a>
            </div>
        </div>
    </div>

    <div class="container">
        <h1>Pedidos</h1>

        <?php if (empty($pedidos)): ?>
            <div class="card empty-state">
                <h3 style="margin-bottom: 10px;">Ainda não há pedidos</h3>
                <p style="color:#777;">Vá até a loja para fazer um pedido.</p>
            </div>
        <?php else: ?>
            
            <?php foreach($pedidos as $pedido): ?>
                
                <div class="order-card">
                    
                    <div class="order-grid-header">
                        <div class="col-produto">PRODUTO</div>
                        <div class="col-center">PREÇO</div>
                        <div class="col-center">QUANTIDADE</div>
                        <div class="col-center">TOTAL</div>
                    </div>

                    <?php 
                        // Procura os itens deste pedido
                        $stmtItens->execute([$pedido['id']]);
                        $itens_deste_pedido = $stmtItens->fetchAll(PDO::FETCH_ASSOC);
                        
                        foreach($itens_deste_pedido as $item): 
                            
                            $total_item = $item['preco_unitario'];

                            // Formata o nome com cor e tamanho
                            $nome_exibicao = $item['produto_nome'];
                            $detalhes = array_filter([$item['tamanho_nome']]);
                            if (!empty($detalhes)) {
                                $nome_exibicao .= ' (' . implode(' - ', $detalhes) . ')';
                            }
                    ?>
                        
                        <div class="order-item-row">
                            <div class="item-info col-produto">
                                
                                <?php if (!empty($item['caminho_imagem'])): ?>
                                    <img src="/magda-crew/<?= $item['caminho_imagem'] ?>" 
                                         alt="<?= htmlspecialchars($item['produto_nome']) ?>" 
                                         style="width: 60px; height: auto; border-radius: 8px; object-fit: contain;">
                                <?php else: ?>
                                    <div class="imagem-placeholder" style="width: 60px; height: 75px; background: #333; display: flex; align-items: center; justify-content: center; border-radius: 8px;">
                                        <span style="font-size: 9px; color: #ccc;">SEM FOTO</span>
                                    </div>
                                <?php endif; ?>
                                
                                <span class="item-name"><?= htmlspecialchars(strtoupper($nome_exibicao)) ?></span>
                            </div>
                            
                            <div class="col-center">R$ <?= number_format($item['preco_unitario'], 2, ',', '.') ?></div>
                            <div class="col-center"><?= htmlspecialchars($item['quantidade']) ?></div>
                            <div class="col-center">R$ <?= number_format($total_item, 2, ',', '.') ?></div>
                        </div>

                    <?php endforeach; ?>

                    <div class="order-summary">
                        <p>FRETE (<?= htmlspecialchars(strtoupper($pedido['frete_tipo'] ?? 'NÃO ESPECIFICADO')) ?>): R$ <?= number_format($pedido['valor_frete'] ?? 0, 2, ',', '.') ?></p>
                        <p>SUBTOTAL: R$ <?= number_format($pedido['subtotal'] ?? 0, 2, ',', '.') ?></p>
                        <p class="total-line">TOTAL: R$ <?= number_format($pedido['valor_total'], 2, ',', '.') ?></p>
                    </div>

                </div>
            <?php endforeach; ?>

        <?php endif; ?>
    </div>

</body>
</html>
