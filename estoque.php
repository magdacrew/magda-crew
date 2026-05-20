<?php
require_once __DIR__ . '/src/Config/Database.php';

try {
    $pdo = Database::getConnection();
} catch (Exception $e) {
    die("Erro ao conectar no banco de dados: " . $e->getMessage());
}

// Busca o estoque usando a coluna correta: quantidade_estoque
$query = "
    SELECT pv.id, p.nome as produto, c.nome as cor, t.nome as tamanho, pv.quantidade_estoque 
    FROM produto_variantes pv
    INNER JOIN produtos p ON pv.produto_id = p.id
    INNER JOIN cores c ON pv.cor_id = c.id
    INNER JOIN tamanhos t ON pv.tamanho_id = t.id
    ORDER BY p.nome ASC, c.nome ASC, t.nome ASC
";
$stmt = $pdo->query($query);
$estoques = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="/magda-crew/public/assets/images/15.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estoque - Magda Crew</title>
    <link rel="stylesheet" href="/MAGDA-CREW/public/assets/css/gestao.css">
</head>
<body>

<?php include 'sidebar.php'; ?>

    <main class="main-content">
        <header class="header">
            <h2>Gerenciamento de Estoque</h2>
        </header>

        <section class="content">
            <div class="topo-produtos">
                <h1>Estoque Atual</h1>
                <a href="add_estoque.php" class="btn-add">+ Adicionar Estoque</a>
            </div>

            <table class="tabela">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Produto</th>
                        <th>Cor</th>
                        <th>Tamanho</th>
                        <th>Quantidade</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($estoques) > 0): ?>
                        <?php foreach ($estoques as $item): ?>
                            <tr>
                                <td><?= $item['id'] ?></td>
                                <td><?= htmlspecialchars($item['produto']) ?></td>
                                <td><?= htmlspecialchars($item['cor']) ?></td>
                                <td><?= htmlspecialchars($item['tamanho']) ?></td>
                                <td><strong><?= $item['quantidade_estoque'] ?></strong></td>
                                <td class="acoes">
                                    <a href="edit_estoque.php?id=<?= $item['id'] ?>" class="btn-editar">Editar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">Nenhum item em estoque encontrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>

</body>
</html>