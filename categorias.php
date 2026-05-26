<?php
require_once __DIR__ . '/src/Config/Database.php';

$pdo = Database::getConnection();

// Busca as categorias
$stmt = $pdo->query("SELECT * FROM categorias ORDER BY id DESC");
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="/magda-crew/public/assets/images/15.png">
    <title>Categorias</title>
    <link rel="stylesheet" href="/MAGDA-CREW/public/assets/css/gestao.css">
    <style>
        .topo-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .badge-ativo { color: #17be01; font-weight: bold; }
        .badge-inativo { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<main class="main-content">
    <section class="content">
        <div class="topo-header">
            <h1>Categorias</h1>
            <a href="adicionar-categoria.php" class="btn-novo">+ Nova Categoria</a>
        </div>

        <table class="tabela">
            <tr>
                <th>ID</th>
                <th>Categoria</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>

            <?php foreach($categorias as $categoria): ?>
                <?php 
                    // Verifica se a categoria está ativa (se a coluna não existir, assume 1)
                    $is_ativo = isset($categoria['ativo']) ? $categoria['ativo'] : 1; 
                ?>
            <tr>
                <td><?= $categoria['id'] ?></td>
                <td><?= htmlspecialchars($categoria['nome']) ?></td>
                <td>
                    <?php if($is_ativo == 1): ?>
                        <span class="badge-ativo">Ativo</span>
                    <?php else: ?>
                        <span class="badge-inativo">Inativo</span>
                    <?php endif; ?>
                </td>
                <td class="acoes" style="display: flex; gap: 10px;">
                    <a href="editar-categoria.php?id=<?= $categoria['id'] ?>" class="btn-editar">Editar</a>
                    
                    <?php if($is_ativo == 1): ?>
                        <a href="status-categoria.php?id=<?= $categoria['id'] ?>" class="btn-status">Desativar</a>
                    <?php else: ?>
                        <a href="status-categoria.php?id=<?= $categoria['id'] ?>" class="btn-status" style="background: #17be01;">Ativar</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </section>
</main>

</body>
</html>