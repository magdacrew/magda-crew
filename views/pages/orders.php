<?php
session_start();
require_once __DIR__ . '/../../src/Config/Database.php';

$pdo = Database::getConnection();
$usuario_id = $_SESSION['usuario_id'] ?? 1;

// 1. Busca os pedidos
$stmtVendas = $pdo->prepare("
    SELECT id, valor_total, valor_frete, subtotal, frete_tipo, status, data_venda 
    FROM vendas 
    WHERE usuario_id = ? 
    ORDER BY data_venda DESC
");
$stmtVendas->execute([$usuario_id]);
$pedidos = $stmtVendas->fetchAll(PDO::FETCH_ASSOC);

// 2. Query para itens (Corrigida e Reforçada)
// Dica: Se a sua tabela 'itens_venda' tiver a coluna 'produto_id', 
// troque a linha do JOIN p/ "LEFT JOIN produtos p ON p.id = i.produto_id"
$stmtItens = $pdo->prepare("
    SELECT 
        i.produto_nome, i.quantidade, i.preco_unitario, i.tamanho_nome, i.cor_nome,
        pi.caminho_imagem
    FROM itens_venda i
    LEFT JOIN produtos p ON TRIM(LOWER(p.nome)) = TRIM(LOWER(i.produto_nome))
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
    <style>
        /* Estilos inline para garantir a aparência profissional */
        :root { --dark-card: #111; --border-color: #222; --text-muted: #888; }
        
        .container { max-width: 1000px; margin: 40px auto; padding: 0 20px; }
        h1 { font-size: 2rem; margin-bottom: 30px; letter-spacing: -1px; }

        .order-card { 
            background: var(--dark-card); 
            border: 1px solid var(--border-color);
            border-radius: 12px; 
            padding: 25px; 
            margin-bottom: 30px;
        }

        .order-header-info {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 15px;
            margin-bottom: 20px;
            font-size: 0.85rem;
            text-transform: uppercase;
            color: var(--text-muted);
        }

        .grid-labels {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            padding: 10px 0;
            border-bottom: 1px solid var(--border-color);
            font-size: 0.7rem;
            letter-spacing: 1px;
            color: var(--text-muted);
        }

        .product-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            align-items: center;
            padding: 20px 0;
            border-bottom: 1px solid #1a1a1a;
        }

        .product-info { display: flex; align-items: center; gap: 20px; }
        .product-img { 
            width: 80px; height: 100px; 
            object-fit: cover; 
            border-radius: 4px; 
            background: #1a1a1a;
        }

        .product-name { font-weight: 600; font-size: 0.95rem; }
        .product-meta { display: block; font-size: 0.75rem; color: var(--text-muted); margin-top: 4px; }

        .col-center { text-align: center; font-size: 0.9rem; }

        .order-footer {
            display: flex;
            justify-content: flex-end;
            padding-top: 20px;
        }

        .summary-box { width: 300px; }
        .summary-line { 
            display: flex; 
            justify-content: space-between; 
            margin-bottom: 8px; 
            font-size: 0.85rem; 
            color: var(--text-muted);
        }

        .total-line { 
            margin-top: 15px; 
            padding-top: 15px; 
            border-top: 1px solid var(--border-color);
            font-size: 1.2rem; 
            font-weight: 700; 
            color: #fff;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.7rem;
            background: #222;
            color: #fff;
        }
    </style>
</head>
<body>

    <div class="topbar">
        <div class="top-content">
            <div class="menu">
                <a href="/magda-crew/public/index.php">
                    <img src="/magda-crew/public/assets/images/MagdaWhiteLogo.png" class="logo" alt="Logo">
                </a>
                <a href="/magda-crew/views/pages/orders.php" style="text-decoration: underline;">Orders</a>
                <a href="/magda-crew/views/pages/Profile.php">Profile</a>
            </div>
            <a href="javascript:history.back()">
                <img src="/magda-crew/public/assets/images/X.png" alt="Voltar" class="profile-icon">
            </a>
        </div>
    </div>

    <div class="container">
        <h1>Meus Pedidos</h1>

        <?php if (empty($pedidos)): ?>
            <div class="order-card" style="text-align: center; padding: 60px;">
                <p style="color: var(--text-muted);">Você ainda não realizou nenhum pedido.</p>
                <a href="/magda-crew/public/index.php" style="color: #fff; text-decoration: underline; font-size: 0.9rem;">Ir para a loja</a>
            </div>
        <?php else: ?>
            
            <?php foreach($pedidos as $pedido): ?>
                <div class="order-card">
                    <div class="order-header-info">
                        <span>Pedido #<?= $pedido['id'] ?> • <?= date('d/m/Y', strtotime($pedido['data_venda'])) ?></span>
                        <span class="status-badge"><?= strtoupper($pedido['status'] ?? 'Processando') ?></span>
                    </div>

                    <div class="grid-labels">
                        <div>PRODUTO</div>
                        <div class="col-center">PREÇO</div>
                        <div class="col-center">QNT.</div>
                        <div class="col-center">TOTAL</div>
                    </div>

                    <?php 
                        $stmtItens->execute([$pedido['id']]);
                        $itens = $stmtItens->fetchAll(PDO::FETCH_ASSOC);
                        
                        foreach($itens as $item): 
                            $valor_total_item = $item['preco_unitario'] * $item['quantidade'];
                            
                            // Lógica inteligente para montar o caminho da imagem sem duplicar o /magda-crew/
                            $caminho_imagem = '';
                            if (!empty($item['caminho_imagem'])) {
                                $caminho = $item['caminho_imagem'];
                                // Se o caminho já vier do banco com "magda-crew", não adiciona de novo
                                if (strpos($caminho, 'magda-crew') !== false) {
                                    $caminho_imagem = (strpos($caminho, '/') === 0 ? '' : '/') . $caminho;
                                } else {
                                    $caminho_imagem = '/magda-crew/' . ltrim($caminho, '/');
                                }
                            }
                    ?>
                        <div class="product-row">
                            <div class="product-info">
                                <?php if (!empty($caminho_imagem)): ?>
                                    <img src="<?= htmlspecialchars($caminho_imagem) ?>" class="product-img" alt="Produto">
                                <?php else: ?>
                                    <div class="product-img" style="display:flex; align-items:center; justify-content:center; font-size:10px;">NO IMG</div>
                                <?php endif; ?>
                                
                                <div>
                                    <span class="product-name"><?= htmlspecialchars(strtoupper($item['produto_nome'])) ?></span>
                                    <span class="product-meta">TAMANHO: <?= htmlspecialchars($item['tamanho_nome']) ?></span>
                                </div>
                            </div>
                            
                            <div class="col-center">R$ <?= number_format($item['preco_unitario'], 2, ',', '.') ?></div>
                            <div class="col-center"><?= $item['quantidade'] ?></div>
                            <div class="col-center" style="font-weight: 600;">R$ <?= number_format($valor_total_item, 2, ',', '.') ?></div>
                        </div>
                    <?php endforeach; ?>

                    <div class="order-footer">
                        <div class="summary-box">
                            <div class="summary-line">
                                <span>Subtotal</span>
                                <span>R$ <?= number_format($pedido['subtotal'], 2, ',', '.') ?></span>
                            </div>
                            <div class="summary-line">
                                <span>Frete (<?= strtoupper($pedido['frete_tipo']) ?>)</span>
                                <span>R$ <?= number_format($pedido['valor_frete'], 2, ',', '.') ?></span>
                            </div>
                            <div class="summary-line total-line">
                                <span>Total</span>
                                <span>R$ <?= number_format($pedido['valor_total'], 2, ',', '.') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>