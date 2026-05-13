<?php
require_once __DIR__ . '/src/Config/Database.php';

$pdo = Database::getConnection();

$stmt = $pdo->query("SELECT * FROM usuarios ORDER BY id DESC");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Clientes</title>
    <link rel="stylesheet" href="/MAGDA-CREW/public/assets/css/gestao.css">
</head>
<body>

<?php include 'sidebar.php'; ?>

<main class="main-content">
    <section class="content">
        <h1>Clientes</h1>

        <table class="tabela">
            <tr>
                <th>ID</th>
                <th>Email</th>
            </tr>

            <?php foreach($usuarios as $usuario): ?>
            <tr>
                <td><?= $usuario['id'] ?></td>
                <td><?= $usuario['email'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </section>
</main>

</body>
</html>