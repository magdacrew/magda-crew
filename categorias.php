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
                <th>Ações</th>
            </tr>

            <?php foreach($categorias as $categoria): ?>
                <?php 
                    // Verifica se a categoria está ativa para o botão switch
                    $is_ativo = isset($categoria['ativo']) ? $categoria['ativo'] : 1; 
                ?>
            <tr>
                <td><?= $categoria['id'] ?></td>
                <td><?= htmlspecialchars($categoria['nome']) ?></td>
                
                <td class="acoes">
                    <a href="editar-categoria.php?id=<?= $categoria['id'] ?>" class="btn-editar-img" title="Editar Categoria">
                        <img src="/magda-crew/public/assets/images/BlackPencil.png" alt="Editar" class="icon-editar">
                    </a>

                    <label class="switch" title="Ativar/Desativar">
                        <input 
                            type="checkbox" 
                            <?= ($is_ativo == 1) ? 'checked' : '' ?>
                            onchange="window.location.href='status-categoria.php?id=<?= $categoria['id'] ?>'"
                        >
                        <span class="slider round"></span>
                    </label>
                </td>
                
            </tr>
            <?php endforeach; ?>
        </table>
    </section>
</main>

</body>
</html>