<?php
require_once __DIR__ . '/src/Config/Database.php';

try {
    $pdo = Database::getConnection();
} catch (Exception $e) {
    die("Erro ao conectar: " . $e->getMessage());
}

$stmt = $pdo->query("SELECT * FROM produtos ORDER BY id DESC");
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/magda-crew/public/assets/images/15.png">
    <title>Produtos - Magda Crew</title>
    <link rel="stylesheet" href="/MAGDA-CREW/public/assets/css/gestao.css">
</head>
<body>

<aside class="sidebar" id="sidebar">
    <div class="logo-container">
        <img src="public/assets/images/MagdaWhiteLogo.png" alt="Logo" class="logo-img">
    </div>

    <nav>
        <ul>
            <li><a href="painel.php">Dashboard</a></li>
            <li><a href="produtos.php">Produtos</a></li>
            <li><a href="categorias.php">Categorias</a></li>
            <li><a href="vendas.php">Vendas</a></li>
            <li><a href="clientes.php">Clientes</a></li>
        </ul>
    </nav>
</aside>

<main class="main-content">

    <header class="header">
        <button id="menuToggle">☰</button>

        <div class="user-info">
            <span>Bem-vindo(a), Admin</span>
            <a href="logout.php">Sair</a>
        </div>
    </header>

    <section class="content">

        <div class="topo-produtos">
            <div>
                <h1>Produtos</h1>

                <p style="margin-bottom: 20px; color: #666;">
                    Gerencie os produtos da loja.
                </p>
            </div>

            <a href="adicionar-produto.php" class="btn-add">
                + Novo Produto
            </a>
        </div>

        <table class="tabela">

            <tr>
                <th>ID</th>
                <th>Produto</th>
                <th>Preço</th>
                <th>Ações</th>
            </tr>

            <?php foreach($produtos as $produto): ?>

            <tr>

                <td><?= $produto['id'] ?></td>

                <td><?= $produto['nome'] ?></td>

                <td>
                    R$ <?= number_format($produto['preco'], 2, ',', '.') ?>
                </td>

                <td class="acoes">

    <a href="editar-produto.php?id=<?= $produto['id'] ?>" class="btn-editar">
        Editar
    </a>

    <?php if(isset($produto['ativo']) && $produto['ativo'] == 1): ?>

        <a href="toggle-produto.php?id=<?= $produto['id'] ?>" class="btn-status">
            Desativar
        </a>

    <?php else: ?>

        <a href="toggle-produto.php?id=<?= $produto['id'] ?>" class="btn-add">
            Ativar
        </a>

    <?php endif; ?>

</td>

            </tr>

            <?php endforeach; ?>

        </table>

    </section>

</main>

<script src="public/assets/js/main.js"></script>

</body>
</html>