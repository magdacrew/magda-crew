<?php
require_once __DIR__ . '/src/Config/Database.php';

try {
    $pdo = Database::getConnection();
} catch (Exception $e) {
    die("Erro ao conectar: " . $e->getMessage());
}

// Atualizado para buscar a imagem principal do produto junto com os dados dele
$stmt = $pdo->query("
    SELECT p.*, 
           (SELECT caminho_imagem FROM produto_imagens WHERE produto_id = p.id AND is_principal = 1 LIMIT 1) as caminho_imagem
    FROM produtos p 
    ORDER BY p.id DESC
");
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/magda-crew/public/assets/images/15.png">
    <title>Produtos - Magda Crew</title>
    <link rel="stylesheet" href="/magda-crew/public/assets/css/gestao.css">
</head>
<body>

<?php include 'sidebar.php'; ?>

<main class="main-content">
    <section class="content">

        <div class="topo-produtos">
            <div>
                <h1>Produtos</h1>
                <p style="margin-bottom: 20px; color: #666;">
                    Gerencie os produtos da loja.
                </p>
            </div>
            <a href="adicionar-produto.php" class="btn-adicionar">
                + Novo Produto
            </a>
        </div>

        <table class="tabela">
            <tr>
                <th>ID</th>
                <th></th> <th>Produto</th>
                <th>Preço</th>
                <th>Ações</th>
            </tr>

            <?php foreach($produtos as $produto): ?>
            <tr>
                <td><?= $produto['id'] ?></td>

                <td>
                    <?php if (!empty($produto['caminho_imagem'])): ?>
                        <img src="/magda-crew/<?= htmlspecialchars($produto['caminho_imagem']) ?>" alt="<?= htmlspecialchars($produto['nome']) ?>" class="thumb-produto">
                    <?php else: ?>
                        <div class="thumb-produto placeholder">Sem Foto</div>
                    <?php endif; ?>
                </td>

                <td><?= htmlspecialchars($produto['nome']) ?></td>

                <td>
                    R$ <?= number_format($produto['preco'], 2, ',', '.') ?>
                </td>

                <td class="acoes">
                    <a href="editar-produto.php?id=<?= $produto['id'] ?>" class="btn-editar">Editar</a>

                    <?php if(isset($produto['ativo']) && $produto['ativo'] == 1): ?>
                        <a href="toggle-produto.php?id=<?= $produto['id'] ?>" class="btn-status">Desativar</a>
                    <?php else: ?>
                        <a href="toggle-produto.php?id=<?= $produto['id'] ?>" class="btn-add">Ativar</a>
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