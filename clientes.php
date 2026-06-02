<?php
require_once __DIR__ . '/AdminGuard.php';

require_once __DIR__ . '/src/Config/Database.php';

$pdo = Database::getConnection();

// Melhoria na Query: Busca os usuários e já conta quantos pedidos cada um tem
$stmt = $pdo->query("
    SELECT 
        u.*,
        (SELECT COUNT(id) FROM vendas WHERE usuario_id = u.id) as total_pedidos
    FROM usuarios u 
    ORDER BY u.id DESC
");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="/MagdaCrew/public/assets/images/MgdWhite.png">
    <title>Clientes - Magda Crew</title>
    
    <link rel="stylesheet" href="/MagdaCrew/public/assets/css/Gestao.css">
    
    <link rel="stylesheet" href="/MagdaCrew/public/assets/css/Produtos.css">
</head>
<body>

<?php include 'Sidebar.php'; ?>

<main class="main-content">
    <section class="content">
        
        <div class="topo-produtos">
            <div>
                <h1>Clientes</h1>
                <p style="color: #666; margin-top: 5px;">Gerencie os usuários cadastrados na sua loja.</p>
            </div>
            </div>

        <table class="tabela">
            <thead>
                <tr>
                    <th style="width: 80px;">ID</th>
                    <th>Email</th>
                    <th style="width: 150px; text-align: center;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($usuarios as $usuario): ?>
                <tr>
                    <td><?= $usuario['id'] ?></td>
                    
                    <td><?= htmlspecialchars($usuario['email']) ?></td>
                    
                    <td style="text-align: center;">
                        <div class="acoes">
                            <a href="ClienteDetalhe.php?id=<?= $usuario['id'] ?>" class="btn-acao">Ver Detalhes</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</main>

</body>
</html>