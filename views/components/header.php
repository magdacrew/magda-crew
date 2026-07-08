<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../src/Config/Database.php';

$session_id = session_id();
$usuario_id = $_SESSION['usuario_id'] ?? null;
$itensCarrinho = [];
$totalCarrinho = 0;
$quantidadeTotal = 0;

try {
    $pdo_cart = Database::getConnection();
    
    // Adicionado "p.id AS produto_id" para podermos criar o link para a página do produto
    $sql = "SELECT c.quantidade, c.variante_id, t.nome AS tamanho_nome, p.nome, p.preco, p_img.caminho_imagem, p.id AS produto_id
            FROM carrinho c
            JOIN produto_variantes v ON c.variante_id = v.id
            JOIN tamanhos t ON v.tamanho_id = t.id
            JOIN produtos p ON v.produto_id = p.id
            LEFT JOIN produto_imagens p_img ON p.id = p_img.produto_id AND p_img.is_principal = 1
            WHERE c.usuario_id = :usuario_id OR c.session_id = :session_id";
            
    $stmtCarrinho = $pdo_cart->prepare($sql);
    $stmtCarrinho->execute([':usuario_id' => $usuario_id ?? 0, ':session_id' => $session_id]);
    $itensCarrinho = $stmtCarrinho->fetchAll(PDO::FETCH_ASSOC);

    foreach($itensCarrinho as $item) {
        $totalCarrinho += ($item['preco'] * $item['quantidade']);
        $quantidadeTotal += $item['quantidade'];
    }
    
} catch (Exception $e) { 
    error_log($e->getMessage()); 
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($tituloDaPagina) ? htmlspecialchars($tituloDaPagina) : 'Magda Crew' ?></title>
    <link rel="icon" type="image/png" href="/MagdaCrew/public/assets/images/MgdWhite.png">
    <link rel="stylesheet" href="/MagdaCrew/public/assets/css/header.css">
</head>
<body>

<header>
  <nav>
    <a href="/MagdaCrew/views/pages/Shop.php">Shop</a>
    <a href="#">Archive</a>
    <a href="/MagdaCrew/views/pages/flagship.php">Flagship</a>
    <?php if (!empty($_SESSION["usuario_id"]) && !empty($_SESSION["is_admin"])): ?>
        <a href="/MagdaCrew/Painel.php">Painel</a>
    <?php endif; ?>
  </nav>

  <div class="logo">
    <a href="/MagdaCrew/public/Index.php" class="logo-link">
      <img src="/MagdaCrew/public/assets/images/MagdaWhiteLogo.png" alt="Magda Crew" class="logo-img">
    </a>
  </div>

  <div class="actions">
    <div class="search" style="display: flex; align-items: center; position: relative;">
      <input type="text" id="inputBusca" placeholder="Buscar" 
             onkeyup="if(event.key === 'Enter') executarBusca()"
             value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
      <img src="/MagdaCrew/public/assets/images/WhiteMagnifyingGlass.png" 
           alt="Buscar" class="icon" onclick="executarBusca()" style="cursor: pointer;">
    </div>

    <?php if (isset($_SESSION["usuario_id"])): ?>
      <a href="/MagdaCrew/views/pages/Profile.php">
        <img src="/MagdaCrew/public/assets/images/WhiteUser.png" alt="Perfil" class="icon">
      </a>
    <?php else: ?>
      <a href="/MagdaCrew/views/pages/login.php">
        <img src="/MagdaCrew/public/assets/images/WhiteUser.png" alt="Login" class="icon">
      </a>
    <?php endif; ?>

    <a href="#"><img src="/MagdaCrew/public/assets/images/Sun.png" alt="Alternar tema" class="icon"></a>
    
    <!-- Ícone da Sacola com o Badge -->
    <a href="#" onclick="abrirCarrinho(event)" style="position: relative; display: inline-flex;">
      <img src="/MagdaCrew/public/assets/images/WhiteBag.png" alt="Sacola" class="icon">
      <?php if ($quantidadeTotal > 0): ?>
          <span class="sacola-badge"><?= $quantidadeTotal ?></span>
      <?php endif; ?>
    </a>
  </div>
</header>

<div id="cartOverlay" class="cart-overlay" onclick="fecharCarrinho()"></div>
<div id="cartDrawer" class="cart-drawer">
    <div class="cart-header">
        <h2>CARRINHO <span class="cart-count"><?= $quantidadeTotal ?></span></h2>
        <button class="fechar-btn" onclick="fecharCarrinho()">&times;</button>
    </div>

    <div class="cart-content">
        <?php if (empty($itensCarrinho)): ?>
            <p style="text-align: center; color: #888; margin-top: 20px;">Seu carrinho está vazio.</p>
        <?php else: ?>
            <?php foreach($itensCarrinho as $item): ?>
                <div class="cart-item">
                    <?php 
                        $imgSrc = $item['caminho_imagem'];
                        if (strpos($imgSrc, 'http') === false) {
                            $imgSrc = '/MagdaCrew/' . ltrim($imgSrc, '/');
                        }
                    ?>
                    
                    <!-- Link na Imagem -->
                    <a href="/MagdaCrew/public/produtos/detalhes/<?= $item['produto_id'] ?>">
                        <img src="<?= htmlspecialchars($imgSrc) ?>" class="cart-item-img" alt="<?= htmlspecialchars($item['nome']) ?>">
                    </a>
                    
                    <div class="cart-item-details">
                        <div class="cart-item-info-top">
                            <a href="/MagdaCrew/public/produtos/detalhes/<?= $item['produto_id'] ?>" style="text-decoration: none; color: inherit;">
                                <h4 class="cart-item-title"><?= htmlspecialchars($item['nome']) ?></h4>
                            </a>
                            <button class="btn-remover" onclick="removerItem(<?= $item['variante_id'] ?>)">
                                <img src="/MagdaCrew/public/assets/images/WhiteTrash.png" alt="Remover">
                            </button>
                        </div>

                        <!-- Esse bloco será empurrado para baixo pelo margin-top: auto -->
                        <div class="cart-item-info-bottom">
                            <p class="cart-item-variant">Tamanho: <?= htmlspecialchars($item['tamanho_nome']) ?></p>
                            <p class="cart-item-variant">Quantidade: <?= $item['quantidade'] ?></p>
                            <p class="cart-item-price">R$ <?= number_format($item['preco'] * $item['quantidade'], 2, ',', '.') ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <div class="cart-footer">
        <div class="cart-total">
            <span>SUBTOTAL</span>
            <span>R$ <?= number_format($totalCarrinho, 2, ',', '.') ?></span>
        </div>
        <p class="cart-footer-note">Taxas de frete calculadas no checkout.</p>
        
        <?php if (empty($itensCarrinho)): ?>
            <a class="btn-finalizar" href="/MagdaCrew/views/pages/Shop.php">CONTINUAR COMPRANDO</a>
        <?php else: ?>
            <a class="btn-finalizar" href="/MagdaCrew/views/pages/checkout.php">FINALIZAR COMPRA</a>
        <?php endif; ?>
    </div>
</div>

<script>
    function executarBusca() {
        const input = document.getElementById('inputBusca');
        const termo = input.value.trim();
        
        if (termo.length >= 2) { 
            window.location.href = '/MagdaCrew/views/pages/search.php?q=' + encodeURIComponent(termo);
        } else {
            input.focus();
            input.style.borderBottom = "1px solid red"; 
            setTimeout(() => input.style.borderBottom = "", 1000);
        }
    }

    function abrirCarrinho(e) { if(e) e.preventDefault(); document.getElementById('cartDrawer').classList.add('aberto'); document.getElementById('cartOverlay').classList.add('ativo'); document.body.style.overflow = 'hidden'; }
    function fecharCarrinho() { document.getElementById('cartDrawer').classList.remove('aberto'); document.getElementById('cartOverlay').classList.remove('ativo'); document.body.style.overflow = 'auto'; }
    
    function removerItem(varianteId) {
        const formData = new FormData();
        formData.append('variante_id', varianteId);
        fetch('/MagdaCrew/src/Controllers/RemoverCarrinho.php', { method: 'POST', body: formData })
        .then(res => res.json()).then(data => { if(data.success) location.reload(); });
    }
</script>