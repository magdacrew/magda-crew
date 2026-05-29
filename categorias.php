<?php
require_once __DIR__ . '/admin_guard.php';

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
    <title>Categorias - Magda Crew</title>
    
    <!-- CSS Global (Sidebar, Cores de Fundo, Tabela Padrão) -->
    <link rel="stylesheet" href="/magda-crew/public/assets/css/gestao.css">
    
    <!-- CSS dos elementos visuais compartilhados (Botões, Topo e Ações) -->
    <link rel="stylesheet" href="/magda-crew/public/assets/css/produtos.css">
</head>
<body>

<?php include 'sidebar.php'; ?>

<main class="main-content">
    <section class="content">
        
        <!-- Cabeçalho Padronizado -->
        <div class="topo-produtos">
            <div>
                <h1>Categorias</h1>
                <p style="margin-bottom: 20px; color: #666;">
                    Gerencie as categorias dos produtos da loja.
                </p>
            </div>
            <a href="adicionar-categoria.php" class="btn-adicionar">+ Nova Categoria</a>
        </div>

        <table class="tabela">
            <thead>
                <tr>
                    <th style="width: 80px;">ID</th>
                    <th>Categoria</th>
                    <th style="width: 150px; text-align: center;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($categorias as $categoria): ?>
                    <?php 
                        // Verifica se a categoria está ativa para o botão switch
                        $is_ativo = isset($categoria['ativo']) ? $categoria['ativo'] : 1; 
                    ?>
                <tr>
                    <td><?= $categoria['id'] ?></td>
                    
                    <td>
                        <span class="produto-nome-texto"><?= htmlspecialchars($categoria['nome']) ?></span>
                    </td>
                    
                    <td style="text-align: center;">
                        <!-- Div Ações padronizada para o Lápis e o Switch -->
                        <div class="acoes">
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