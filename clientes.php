<?php
require_once __DIR__ . '/admin_guard.php';

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
    <link rel="icon" type="image/png" href="/magda-crew/public/assets/images/15.png">
    <title>Clientes - Magda Crew</title>
    <link rel="stylesheet" href="/MAGDA-CREW/public/assets/css/gestao.css">
    <style>
        .btn-acao {
            background-color: #333;
            color: #fff;
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 0.85rem;
            transition: background 0.3s;
        }
        .btn-acao:hover { background-color: #555; }
        .text-center { text-align: center; }
        .badge-pedidos {
            background-color: #222;
            color: #fff;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<main class="main-content">
    <section class="content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <h1>Clientes</h1>
                <p style="color: #666; margin-top: 5px;">Gerencie os usuários cadastrados na sua loja.</p>
            </div>
        </div>

        <table class="tabela">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Email</th>
                    <th class="text-center">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($usuarios as $usuario): ?>
                <tr>
                    <td><strong>#<?= $usuario['id'] ?></strong></td>
                    
                    
                    <td><?= htmlspecialchars($usuario['email']) ?></td>
                    
                    
                    <td class="text-center">
                        <a href="cliente_detalhes.php?id=<?= $usuario['id'] ?>" class="btn-acao">Ver Detalhes</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</main>

</body>
</html>