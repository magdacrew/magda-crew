<?php
// Inicia a sessão caso ainda não tenha sido iniciada (necessário para pegar o nome do usuário)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Pega o nome do arquivo atual para iluminar o menu
$paginaAtual = basename($_SERVER['PHP_SELF']);

// Tenta pegar o nome do usuário logado na sessão
$nomeUsuario = $_SESSION['usuario_nome'] ?? $_SESSION['admin_nome'] ?? 'Admin';
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
        </ul>
    </nav>
</aside>

<main class="main-content">
    <header class="header">
        <button id="menuToggle">☰</button>

        <div class="user-info">
            <span>Bem-vindo(a), <?= htmlspecialchars($nomeUsuario) ?></span>
            <a href="/MagdaCrew/public/index.php" class="btn-sair">Voltar à Loja</a>
        </div>
    </header>

    <script src="/MagdaCrew/public/assets/js/main.js"></script>