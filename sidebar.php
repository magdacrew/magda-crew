<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$paginaAtual = basename($_SERVER['PHP_SELF']);

// Usando o e-mail da sessão (conforme o seu print_r anterior)
$emailUsuario = $_SESSION['usuario_email'] ?? $_SESSION['email'] ?? 'admin@magda.com';
?>

<aside class="sidebar" id="sidebar">
    <div class="logo-container">
        <a href="/MagdaCrew/public/Index.php">
            <img src="/MagdaCrew/public/assets/images/MagdaWhiteLogo.png" alt="Logo Magda Crew" class="logo-img">
        </a>
    </div>

    <nav>
        <ul>
            <li><a href="Painel.php" class="<?= $paginaAtual == 'Painel.php' ? 'active' : '' ?>">Dashboard</a></li>
            <li><a href="Produtos.php" class="<?= $paginaAtual == 'Produtos.php' ? 'active' : '' ?>">Produtos</a></li>
            <li><a href="Categorias.php" class="<?= $paginaAtual == 'Categorias.php' ? 'active' : '' ?>">Categorias</a></li>
            <li><a href="Vendas.php" class="<?= $paginaAtual == 'Vendas.php' ? 'active' : '' ?>">Vendas</a></li>
            <li><a href="Clientes.php" class="<?= $paginaAtual == 'Clientes.php' ? 'active' : '' ?>">Clientes</a></li>
            <li><a href="Estoque.php" class="<?= $paginaAtual == 'Estoque.php' ? 'active' : '' ?>">Estoque</a></li>
            <li><a href="Banners.php" class="<?= $paginaAtual == 'Banners.php' ? 'active' : '' ?>">Banners</a></li>
        </ul>
    </nav>
</aside>

<main class="main-content">
    <header class="header">
        <button id="menuToggle">☰</button>

        <div class="user-info">
            <span style="font-size: 14px; color: #333; font-weight: 500;">
                <?= htmlspecialchars($emailUsuario) ?>
            </span>
            
            <a href="/MagdaCrew/public/index.php" class="btn-sair" style="display: flex; align-items: center;">
                <img src="/MagdaCrew/public/assets/images/X.png" alt="Sair" style="width: 20px; height: 20px;">
            </a>
        </div>
    </header>

    <script src="/MagdaCrew/public/assets/js/main.js"></script>