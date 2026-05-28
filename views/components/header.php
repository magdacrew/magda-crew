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
    // Query corrigida com JOIN na tabela tamanhos
    $sql = "SELECT c.quantidade, t.nome AS tamanho_nome, p.nome, p.preco, p_img.caminho_imagem
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
} catch (Exception $e) { error_log($e->getMessage()); }
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($tituloDaPagina) ? htmlspecialchars($tituloDaPagina) : 'Magda Crew' ?></title>

    <link rel="stylesheet" href="/MAGDA-CREW/public/assets/css/header.css">
    
    <style>
        .cart-overlay {
            position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
            background: rgba(0, 0, 0, 0.6); z-index: 999;
            opacity: 0; visibility: hidden; transition: all 0.3s ease-in-out;
            backdrop-filter: blur(2px);
        }
        .cart-overlay.ativo {
            opacity: 1; visibility: visible;
        }

        .cart-drawer {
            position: fixed; top: 0; right: -450px; width: 100%; max-width: 450px;
            height: 100vh; background-color: #1a1a1a; color: #fff; z-index: 1000;
            box-shadow: -5px 0 15px rgba(0, 0, 0, 0.5);
            transition: right 0.3s ease-in-out; display: flex; flex-direction: column;
            font-family: Arial, sans-serif;
        }
        .cart-drawer.aberto {
            right: 0;
        }

        .cart-header { display: flex; justify-content: space-between; align-items: center; padding: 20px; border-bottom: 1px solid #333; flex-wrap: wrap;}
        .cart-header h2 { font-size: 1.2rem; margin: 0; display: flex; align-items: center; gap: 10px; font-weight: normal; width: 100%;}
        .cart-count { background: #fff; color: #000; border-radius: 50%; padding: 2px 8px; font-size: 0.9rem; font-weight: bold;}
        .fechar-btn { background: none; border: none; color: #fff; font-size: 1.8rem; cursor: pointer; position: absolute; right: 20px; top: 15px;}
        .cart-content { flex: 1; padding: 20px; overflow-y: auto; }
        .cart-footer { padding: 20px; border-top: 1px solid #333; background: #1a1a1a; }
        .cart-total { display: flex; justify-content: space-between; margin-bottom: 20px; font-size: 1.1rem; font-weight: bold; }
        .btn-finalizar { width: 100%; padding: 15px; background: #fff; color: #000; border: none; border-radius: 5px; font-size: 1rem; font-weight: bold; cursor: pointer; }
    </style>

    <?= isset($cssExtra) ? $cssExtra : '' ?>
</head>
<body>

<header>
  <nav>
    <a href="/MAGDA-CREW/views/pages/shop.php">Shop</a>
    <a href="#">Archive</a>
    <a href="#">Flagship</a>
    
    <?php if (!empty($_SESSION["usuario_id"]) && !empty($_SESSION["is_admin"])): ?>
        <a href="/magda-crew/painel.php">Painel</a>
    <?php endif; ?>
  </nav>

  <div class="logo">
    <a href="/MAGDA-CREW/public/index.php" class="logo-link">
      <img src="/MAGDA-CREW/public/assets/images/MagdaWhiteLogo.png" alt="Magda Crew" class="logo-img">
    </a>
  </div>

  <div class="actions">
    <div class="search">
      <input type="text" placeholder="Buscar">
      <img src="/MAGDA-CREW/public/assets/images/WhiteMagnifyingGlass.png" alt="Buscar" class="icon">
    </div>

    <?php if (isset($_SESSION["usuario_id"])): ?>
      <a href="/magda-crew/views/pages/Profile.php">
        <img src="/MAGDA-CREW/public/assets/images/WhiteUser.png" alt="Perfil" class="icon">
      </a>
    <?php else: ?>
      <a href="/magda-crew/views/pages/login.php">
        <img src="/MAGDA-CREW/public/assets/images/WhiteUser.png" alt="Login" class="icon">
      </a>
    <?php endif; ?>

    <a href="#">
      <img src="/MAGDA-CREW/public/assets/images/Sun.png" alt="Alternar tema" class="icon">
    </a>

    <a href="#" onclick="abrirCarrinho(event)">
      <img src="/MAGDA-CREW/public/assets/images/WhiteBag.png" alt="Sacola" class="icon">
    </a>
  </div>
</header>

<div id="cartOverlay" class="cart-overlay" onclick="fecharCarrinho()"></div>

<div id="cartDrawer" class="cart-drawer">
    <div class="cart-header">
        <h2>Carrinho <span class="cart-count"><?= $quantidadeTotal ?></span></h2>
        
        <p style="font-size: 12px; color: #ff6b6b; margin-top: 10px; width: 100%;">Minha Sessão: <?= htmlspecialchars($session_id) ?></p> 
        
        <button class="fechar-btn" onclick="fecharCarrinho()">&times;</button>
    </div>
    
    <div class="cart-content">
        <?php if (empty($itensCarrinho)): ?>
            <p style="text-align: center; color: #888; margin-top: 20px;">Seu carrinho está vazio.</p>
        <?php else: ?>
            <?php foreach($itensCarrinho as $item): ?>
                <div style="display: flex; gap: 15px; margin-bottom: 20px; border-bottom: 1px solid #333; padding-bottom: 15px;">
                    
                    <?php 
                        $imgSrc = $item['caminho_imagem'];
                        if (strpos($imgSrc, 'public/') === false && strpos($imgSrc, 'http') === false) {
                            $imgSrc = 'public/assets/images/produtos/' . $imgSrc;
                        }
                    ?>
                    <img src="/magda-crew/<?= htmlspecialchars($imgSrc) ?>" style="width: 70px; height: 70px; object-fit: cover; border-radius: 5px; background: #fff;">
                    
                    <div style="flex: 1;">
                        <h4 style="margin: 0; font-size: 0.95rem; text-transform: uppercase;"><?= htmlspecialchars($item['nome']) ?></h4>
                        <p style="margin: 5px 0; color: #aaa; font-size: 0.85rem;">
                            Tamanho: <?= htmlspecialchars($item['tamanho_nome']) ?> <br>
                            Quantidade: <?= $item['quantidade'] ?>
                        </p>
                        <p style="margin: 0; font-weight: bold;">R$ <?= number_format($item['preco'] * $item['quantidade'], 2, ',', '.') ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="cart-footer">
        <div class="cart-total">
            <span>Total estimado</span>
            <span>R$ <?= number_format($totalCarrinho, 2, ',', '.') ?></span>
        </div>
        <button class="btn-finalizar">Finalizar a compra</button>
    </div>
</div>

<main>

<script>
    function abrirCarrinho(e) {
        if(e) e.preventDefault();
        document.getElementById('cartDrawer').classList.add('aberto');
        document.getElementById('cartOverlay').classList.add('ativo');
        document.body.style.overflow = 'hidden';
    }

    function fecharCarrinho() {
        document.getElementById('cartDrawer').classList.remove('aberto');
        document.getElementById('cartOverlay').classList.remove('ativo');
        document.body.style.overflow = 'auto';
    }
</script>