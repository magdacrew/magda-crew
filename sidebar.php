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
        <a href="/magda-crew/public/index.php">
            <img src="/magda-crew/public/assets/images/MagdaWhiteLogo.png" alt="Logo Magda Crew" class="logo-img">
        </a>
    </div>

    <nav>
        <ul>
            <li><a href="painel.php" class="<?= $paginaAtual == 'painel.php' ? 'active' : '' ?>">Dashboard</a></li>
            <li><a href="produtos.php" class="<?= $paginaAtual == 'produtos.php' ? 'active' : '' ?>">Produtos</a></li>
            <li><a href="categorias.php" class="<?= $paginaAtual == 'categorias.php' ? 'active' : '' ?>">Categorias</a></li>
            <li><a href="vendas.php" class="<?= $paginaAtual == 'vendas.php' ? 'active' : '' ?>">Vendas</a></li>
            <li><a href="clientes.php" class="<?= $paginaAtual == 'clientes.php' ? 'active' : '' ?>">Clientes</a></li>
            <li><a href="estoque.php" class="<?= $paginaAtual == 'estoque.php' ? 'active' : '' ?>">Estoque</a></li>
        </ul>
    </nav>
</aside>

<main class="main-content">
    <header class="header">
        <button id="menuToggle">☰</button>

        <div class="user-info">
            <span>Bem-vindo(a), <?= htmlspecialchars($nomeUsuario) ?></span>
            <a href="/magda-crew/public/index.php" class="btn-sair">Voltar à Loja</a>
        </div>
    </header>

    <script src="/magda-crew/public/assets/js/main.js"></script>