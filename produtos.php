<?php
require_once __DIR__ . '/src/Config/Database.php';

try {
    $pdo = Database::getConnection();
} catch (Exception $e) {
    die("Erro ao conectar: " . $e->getMessage());
}

// Busca a imagem principal do produto junto com os dados dele
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
    <link class="icon" type="image/png" href="/magda-crew/public/assets/images/15.png">
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
            <thead>
                <tr>
                    <th style="width: 80px;">ID</th>
                    <th>Produto</th>
                    <th style="width: 150px;">Preço</th>
                    <th style="width: 150px; text-align: center;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($produtos as $produto): ?>
                <tr>
                    <td><?= $produto['id'] ?></td>

                    <td>
                        <div class="produto-info-cell">
                            <?php if (!empty($produto['caminho_imagem'])): ?>
                                <img src="/magda-crew/<?= htmlspecialchars($produto['caminho_imagem']) ?>" alt="<?= htmlspecialchars($produto['nome']) ?>" class="thumb-produto">
                            <?php else: ?>
                                <div class="thumb-produto placeholder">Sem Foto</div>
                            <?php endif; ?>
                            <span class="produto-nome-texto"><?= htmlspecialchars($produto['nome']) ?></span>
                        </div>
                    </td>

                    <td>
                        R$ <?= number_format($produto['preco'], 2, ',', '.') ?>
                    </td>

                    <td style="text-align: center;">
                        <div class="acoes">
                            <a href="editar-produto.php?id=<?= $produto['id'] ?>" class="btn-editar-img" title="Editar Produto">
                                <img src="/magda-crew/public/assets/images/BlackPencil.png" alt="Editar" class="icon-editar">
                            </a>

                            <label class="switch" title="Ativar/Desativar">
                                <input 
                                    type="checkbox" 
                                    <?= (isset($produto['ativo']) && $produto['ativo'] == 1) ? 'checked' : '' ?>
                                    onchange="window.location.href='toggle-produto.php?id=<?= $produto['id'] ?>'"
                                >
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </section>
</main>

<script src="public/assets/js/main.js"></script>

</body>
</html>