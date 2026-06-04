<?php
require_once __DIR__ . '/AdminGuard.php';
require_once __DIR__ . '/src/Config/Database.php';

try {
    $pdo = Database::getConnection();
} catch (Exception $e) {
    die("Erro ao conectar: " . $e->getMessage());
}

// Processa a exclusão de forma direta (assim como ações administrativas limpas)
if (isset($_GET['excluir'])) {
    $id = intval($_GET['excluir']);
    $delete = $pdo->prepare("DELETE FROM home_banners WHERE id = ?");
    $delete->execute([$id]);
    header("Location: Banners.php?sucesso=excluido");
    exit;
}

$mensagem = "";
if (isset($_GET['sucesso']) && $_GET['sucesso'] === 'excluido') {
    $mensagem = "Banner excluído com sucesso!";
}

// Busca os banners cadastrados organizados por tipo e ordem
$stmt = $pdo->query("
    SELECT * FROM home_banners 
    ORDER BY 
        CASE 
            WHEN tipo = 'hero_topo' THEN 1 
            WHEN tipo = 'banner_baixo' THEN 2 
            ELSE 3 
        END,
        ordem ASC,
        id DESC
");
$banners = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/MagdaCrew/public/assets/images/MgdWhite.png">
    <title>Banners - Magda Crew</title>
    
    <link rel="stylesheet" href="/MagdaCrew/public/assets/css/Gestao.css">
    
    <link rel="stylesheet" href="/MagdaCrew/public/assets/css/Banners.css">
</head>
<body>

<?php include 'sidebar.php'; ?>

<main class="main-content">
    <section class="content">

        <?php if (!empty($mensagem)): ?>
            <div style="background: #dcfce7; color: #166534; border-left: 4px solid #10b981; padding: 12px 15px; border-radius: 6px; margin-bottom: 20px; font-size: 14px; font-weight: 500;">
                <?= htmlspecialchars($mensagem) ?>
            </div>
        <?php endif; ?>

        <div class="topo-produtos">
            <div>
                <h1>Banners</h1>
                <p style="margin-bottom: 20px; color: #666;">
                    Gerencie os banners e vitrines da página inicial.
                </p>
            </div>
            <a href="AdicionarBanner.php" class="btn-adicionar">
                + Novo Banner
            </a>
        </div>

        <table class="tabela">
            <thead>
                <tr>
                    <th style="width: 80px;">ID</th>
                    <th>Banner</th>
                    <th style="width: 200px;">Tipo</th>
                    <th style="width: 100px; text-align: center;">Ordem</th>
                    <th style="width: 180px; text-align: center;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($banners as $banner): ?>
                <tr>
                    <td><?= $banner['id'] ?></td>

                    <td>
                        <div class="produto-info-cell">
                            <?php if (!empty($banner['imagem_fundo'])): ?>
                                <img src="<?= htmlspecialchars($banner['imagem_fundo']) ?>" alt="<?= htmlspecialchars($banner['titulo']) ?>" class="thumb-banner">
                            <?php else: ?>
                                <div class="thumb-banner placeholder">Sem Foto</div>
                            <?php endif; ?>
                            <span class="produto-nome-texto"><?= htmlspecialchars($banner['titulo'] ?: 'Banner sem Título') ?></span>
                        </div>
                    </td>

                    <td>
                        <?= $banner['tipo'] === 'hero_topo' ? 'Banner do Topo' : 'Banner de Baixo' ?>
                    </td>

                    <td style="text-align: center;">
                        <?= $banner['ordem'] ?>
                    </td>

                    <td style="text-align: center;">
                        <div class="acoes">
                            <a href="EditarBanner.php?id=<?= $banner['id'] ?>" class="btn-editar-img" title="Editar Banner">
                                <img src="/MagdaCrew/public/assets/images/BlackPencil.png" alt="Editar" class="icon-editar">
                            </a>

                            <label class="switch" title="Ativar/Desativar">
                                <input 
                                    type="checkbox" 
                                    <?= (isset($banner['ativo']) && $banner['ativo'] == 1) ? 'checked' : '' ?>
                                    onchange="window.location.href='ToggleBanner.php?id=<?= $banner['id'] ?>'"
                                >
                                <span class="slider round"></span>
                            </label>

                            <a href="Banners.php?excluir=<?= $banner['id'] ?>" class="btn-editar-img" onclick="return confirm('Tem certeza que deseja excluir este banner?')" title="Excluir Banner">
                                <img src="/MagdaCrew/public/assets/images/BlackTrash.png" alt="Excluir" class="icon-editar">
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                
                <?php if(count($banners) === 0): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: #999; padding: 30px;">Nenhum banner cadastrado no momento.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

    </section>
</main>

<script src="public/assets/js/main.js"></script>

</body>
</html>