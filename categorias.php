<?php
require_once __DIR__ . '/src/Config/Database.php';

$pdo = Database::getConnection();

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
</head>
<body>

<?php include 'sidebar.php'; ?>

<main class="main-content">
    <section class="content">
        <h1>Categorias</h1>

        <table class="tabela">
            <tr>
                <th>ID</th>
                <th>Categoria</th>
            </tr>

            <?php foreach($categorias as $categoria): ?>
            <tr>
                <td><?= $categoria['id'] ?></td>
                <td><?= $categoria['nome'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </section>
</main>

</body>
</html>