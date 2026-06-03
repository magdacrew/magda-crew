<?php
require_once __DIR__ . '/AdminGuard.php';
require_once __DIR__ . '/src/Config/Database.php';

$pdo = Database::getConnection();
$mensagem = "";
$erro = "";

function uploadBanner($campo, $id = null) {
    if (!isset($_FILES[$campo]) || $_FILES[$campo]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $pastaFisica = __DIR__ . '/public/assets/images/banners/';
    $pastaWeb = '/MagdaCrew/public/assets/images/banners/';

    if (!is_dir($pastaFisica)) {
        mkdir($pastaFisica, 0777, true);
    }

    $nomeOriginal = $_FILES[$campo]['name'];
    $tmpName = $_FILES[$campo]['tmp_name'];
    $extensao = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));
    $permitidas = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($extensao, $permitidas)) {
        return null;
    }

    $nomeArquivo = 'banner_' . ($id ?? 'novo') . '_' . time() . '_' . uniqid() . '.' . $extensao;
    $caminhoFisico = $pastaFisica . $nomeArquivo;
    $caminhoWeb = $pastaWeb . $nomeArquivo;

    if (move_uploaded_file($tmpName, $caminhoFisico)) {
        return $caminhoWeb;
    }

    return null;
}

/* ADICIONAR NOVO BANNER */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'adicionar') {
    $tipo = $_POST['tipo'] ?? 'hero_topo';
    $titulo = trim($_POST['titulo'] ?? '');
    $texto_botao = trim($_POST['texto_botao'] ?? 'Compre Agora');
    $link_botao = trim($_POST['link_botao'] ?? '#');
    $ordem = intval($_POST['ordem'] ?? 0);
    $ativo = isset($_POST['ativo']) ? 1 : 0;

    $imagem = uploadBanner('imagem');

    if (!$imagem) {
        $erro = "Selecione uma imagem válida para o banner.";
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO home_banners 
            (tipo, titulo, texto_botao, link_botao, imagem_fundo, ativo, ordem)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $tipo,
            $titulo,
            $texto_botao,
            $link_botao,
            $imagem,
            $ativo,
            $ordem
        ]);

        header("Location: Banners.php?sucesso=adicionado");
        exit;
    }
}

/* EDITAR BANNER */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'editar') {
    $id = intval($_POST['id']);

    $stmtAtual = $pdo->prepare("SELECT * FROM home_banners WHERE id = ?");
    $stmtAtual->execute([$id]);
    $bannerAtual = $stmtAtual->fetch(PDO::FETCH_ASSOC);

    if ($bannerAtual) {
        $tipo = $_POST['tipo'] ?? $bannerAtual['tipo'];
        $titulo = trim($_POST['titulo'] ?? '');
        $texto_botao = trim($_POST['texto_botao'] ?? '');
        $link_botao = trim($_POST['link_botao'] ?? '#');
        $ordem = intval($_POST['ordem'] ?? 0);
        $ativo = isset($_POST['ativo']) ? 1 : 0;

        $imagem_fundo = $bannerAtual['imagem_fundo'];

        $novaImagem = uploadBanner('imagem', $id);
        if ($novaImagem) {
            $imagem_fundo = $novaImagem;
        }

        $update = $pdo->prepare("
            UPDATE home_banners
            SET tipo = ?, titulo = ?, texto_botao = ?, link_botao = ?, imagem_fundo = ?, ativo = ?, ordem = ?
            WHERE id = ?
        ");

        $update->execute([
            $tipo,
            $titulo,
            $texto_botao,
            $link_botao,
            $imagem_fundo,
            $ativo,
            $ordem,
            $id
        ]);

        header("Location: Banners.php?sucesso=editado");
        exit;
    }
}

/* EXCLUIR BANNER */
if (isset($_GET['excluir'])) {
    $id = intval($_GET['excluir']);

    $delete = $pdo->prepare("DELETE FROM home_banners WHERE id = ?");
    $delete->execute([$id]);

    header("Location: Banners.php?sucesso=excluido");
    exit;
}

if (isset($_GET['sucesso'])) {
    if ($_GET['sucesso'] === 'adicionado') {
        $mensagem = "Banner adicionado com sucesso!";
    } elseif ($_GET['sucesso'] === 'editado') {
        $mensagem = "Banner atualizado com sucesso!";
    } elseif ($_GET['sucesso'] === 'excluido') {
        $mensagem = "Banner excluído com sucesso!";
    }
}

$banners = $pdo->query("
    SELECT * FROM home_banners
    ORDER BY 
        CASE 
            WHEN tipo = 'hero_topo' THEN 1 
            WHEN tipo = 'banner_baixo' THEN 2 
            ELSE 3 
        END,
        ordem ASC,
        id ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="/MagdaCrew/public/assets/images/MgdWhite.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banners - Magda Crew</title>

    <link rel="stylesheet" href="/MagdaCrew/public/assets/css/Gestao.css">
    <link rel="stylesheet" href="/MagdaCrew/public/assets/css/Banners.css">
</head>
<body>

<?php include 'sidebar.php'; ?>

<main class="main-content">
    <section class="content">

        <div class="topo-acoes">
            <div>
                <h1 style="font-size: 24px; color: #111; margin-bottom: 4px;">Banners da Home</h1>
                <p style="color: #666; font-size: 15px;">Gerencie os banners do topo e o banner destaque de baixo.</p>
            </div>
        </div>

        <?php if (!empty($mensagem)): ?>
            <div class="msg-ok"><?= htmlspecialchars($mensagem) ?></div>
        <?php endif; ?>

        <?php if (!empty($erro)): ?>
            <div class="msg-erro"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <div class="banner-form-box">
            <h2 style="font-size: 16px; color: #111; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 20px; border-bottom: 2px solid #f4f7f6; padding-bottom: 12px;">Adicionar Novo Banner</h2>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="acao" value="adicionar">

                <div class="banner-form-grid">
                    <select name="tipo" required>
                        <option value="hero_topo">Banner do topo</option>
                        <option value="banner_baixo">Banner de baixo</option>
                    </select>

                    <input type="text" name="titulo" placeholder="Título do banner" required>
                    <input type="text" name="texto_botao" placeholder="Texto do botão" value="Compre Agora" required>
                    <input type="text" name="link_botao" placeholder="Link do botão" value="#" required>
                    <input type="number" name="ordem" placeholder="Ordem" value="0" min="0">
                    <input type="file" name="imagem" accept="image/png,image/jpeg,image/jpg,image/webp" required>
                </div>

                <div class="banner-form-row">
                    <label class="switch" style="margin-right: auto;" title="Ativar/Desativar Banner">
                        <input type="checkbox" name="ativo" checked>
                        <span class="slider round"></span>
                    </label>

                    <button type="submit" class="btn-preto">Adicionar Banner</button>
                </div>
            </form>
        </div>

        <table class="tabela">
            <thead>
                <tr>
                    <th style="width: 80px;">ID</th>
                    <th style="width: 140px;">Imagem</th>
                    <th>Tipo</th>
                    <th>Título</th>
                    <th>Botão</th>
                    <th>Link</th>
                    <th style="width: 80px;">Ordem</th>
                    <th style="width: 80px; text-align: center;">Status</th>
                    <th style="width: 150px; text-align: center;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($banners) > 0): ?>
                    <?php foreach ($banners as $banner): ?>
                        <tr>
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="acao" value="editar">
                                <input type="hidden" name="id" value="<?= $banner['id'] ?>">

                                <td><?= $banner['id'] ?></td>

                                <td>
                                    <img src="<?= htmlspecialchars($banner['imagem_fundo']) ?>?v=<?= time() ?>" class="banner-thumb" alt="Banner">
                                    <input type="file" name="imagem" accept="image/png,image/jpeg,image/jpg,image/webp" style="margin-top: 8px; font-size: 11px; width: 130px;">
                                </td>

                                <td>
                                    <select name="tipo" class="editar-input">
                                        <option value="hero_topo" <?= $banner['tipo'] === 'hero_topo' ? 'selected' : '' ?>>Topo</option>
                                        <option value="banner_baixo" <?= $banner['tipo'] === 'banner_baixo' ? 'selected' : '' ?>>Baixo</option>
                                    </select>
                                </td>

                                <td>
                                    <input type="text" name="titulo" value="<?= htmlspecialchars($banner['titulo']) ?>" class="editar-input" required>
                                </td>

                                <td>
                                    <input type="text" name="texto_botao" value="<?= htmlspecialchars($banner['texto_botao']) ?>" class="editar-input" required>
                                </td>

                                <td>
                                    <input type="text" name="link_botao" value="<?= htmlspecialchars($banner['link_botao']) ?>" class="editar-input" required>
                                </td>

                                <td>
                                    <input type="number" name="ordem" value="<?= htmlspecialchars($banner['ordem']) ?>" class="editar-input" min="0">
                                </td>

                                <td style="text-align: center;">
                                    <label class="switch" title="Ativar/Desativar Banner">
                                        <input type="checkbox" name="ativo" <?= $banner['ativo'] ? 'checked' : '' ?>>
                                        <span class="slider round"></span>
                                    </label>
                                </td>

                                <td style="text-align: center;">
                                    <div class="acoes">
                                        <button type="submit" class="btn-salvar-mini" title="Salvar Alterações">Salvar</button>
                                        
                                        <a href="Banners.php?excluir=<?= $banner['id'] ?>" class="btn-editar-img" onclick="return confirm('Tem certeza que deseja excluir este banner?')" title="Excluir Banner">
                                            <img src="/MagdaCrew/public/assets/images/BlackTrash.png" alt="Excluir" class="icon-editar">
                                        </a>
                                    </div>
                                </td>
                            </form>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="text-align:center; padding:40px; color:#999; font-size: 15px;">
                            Nenhum banner cadastrado no momento.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

    </section>
</main>

<script src="/MagdaCrew/public/assets/js/main.js"></script>

</body>
</html>