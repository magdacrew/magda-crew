<?php
require_once __DIR__ . '/AdminGuard.php';
require_once __DIR__ . '/src/Config/Database.php';

$pdo = Database::getConnection();
$erro_mensagem = "";

if (!isset($_GET['id'])) {
    header("Location: Banners.php");
    exit;
}

$id = intval($_GET['id']);

// Busca o banner atual
$stmt = $pdo->prepare("SELECT * FROM home_banners WHERE id = ?");
$stmt->execute([$id]);
$banner = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$banner) {
    header("Location: Banners.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo = $_POST['tipo'] ?? $banner['tipo'];
    $titulo = trim($_POST['titulo']);
    $texto_botao = trim($_POST['texto_botao']);
    $link_botao = trim($_POST['link_botao']);
    $ordem = intval($_POST['ordem'] ?? 0);
    $ativo = isset($_POST['ativo']) ? 1 : 0;
    
    $caminhoWeb = $banner['imagem_fundo']; // Mantém a imagem atual por padrão

    if (empty($titulo) || empty($texto_botao) || empty($link_botao)) {
        $erro_mensagem = "Por favor, preencha todos os campos obrigatórios.";
    } else {
        // Verifica se uma nova imagem foi enviada
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $pastaFisica = __DIR__ . '/public/assets/images/banners/';
            $pastaWeb = '/MagdaCrew/public/assets/images/banners/';
            
            if (!is_dir($pastaFisica)) {
                mkdir($pastaFisica, 0777, true);
            }

            $extensao = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
            $permitidas = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($extensao, $permitidas)) {
                $erro_mensagem = "Formato de imagem inválido. Use JPG, PNG ou WEBP.";
            } else {
                $nomeArquivo = 'banner_' . $id . '_' . time() . '_' . uniqid() . '.' . $extensao;
                $caminhoFisico = $pastaFisica . $nomeArquivo;
                
                if (move_uploaded_file($_FILES['imagem']['tmp_name'], $caminhoFisico)) {
                    $caminhoWeb = $pastaWeb . $nomeArquivo;
                } else {
                    $erro_mensagem = "Erro ao fazer upload da nova imagem.";
                }
            }
        }

        if (empty($erro_mensagem)) {
            $update = $pdo->prepare("
                UPDATE home_banners 
                SET tipo = ?, titulo = ?, texto_botao = ?, link_botao = ?, imagem_fundo = ?, ativo = ?, ordem = ?
                WHERE id = ?
            ");

            $update->execute([$tipo, $titulo, $texto_botao, $link_botao, $caminhoWeb, $ativo, $ordem, $id]);

            header("Location: Banners.php?sucesso=editado");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="/MagdaCrew/public/assets/images/MgdWhite.png">
    <title>Editar Banner - Magda Crew</title>
    <link rel="stylesheet" href="/MagdaCrew/public/assets/css/AdicionarProduto.css">
    
    <style>
        .alerta-erro {
            background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;
            padding: 12px 15px; border-radius: 4px; margin-bottom: 20px; font-size: 14px;
        }
    </style>
</head>
<body>

<main class="container-admin">
    <a href="Banners.php">
        <img src="/MagdaCrew/public/assets/images/X.png" alt="Voltar" class="botao-x">
    </a>

    <h1>Editar Banner #<?= $banner['id'] ?></h1>
    <p class="subtitle">Modifique as informações da vitrine abaixo.</p>

    <?php if (!empty($erro_mensagem)): ?>
        <div class="alerta-erro"><?= $erro_mensagem ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        
        <div class="form-group">
            <select name="tipo" required>
                <option value="hero_topo" <?= $banner['tipo'] == 'hero_topo' ? 'selected' : '' ?>>Banner do Topo</option>
                <option value="banner_baixo" <?= $banner['tipo'] == 'banner_baixo' ? 'selected' : '' ?>>Banner de Baixo</option>
            </select>
        </div>

        <div class="form-group">
            <input type="text" name="titulo" placeholder="Título do banner" value="<?= htmlspecialchars($banner['titulo']) ?>" required>
        </div>

        <div class="form-group">
            <input type="text" name="texto_botao" placeholder="Texto do botão" value="<?= htmlspecialchars($banner['texto_botao']) ?>" required>
        </div>

        <div class="form-group">
            <input type="text" name="link_botao" placeholder="Link do botão" value="<?= htmlspecialchars($banner['link_botao']) ?>" required>
        </div>

        <div class="form-group">
            <input type="number" name="ordem" placeholder="Ordem de exibição" value="<?= htmlspecialchars($banner['ordem']) ?>">
        </div>

        <label class="check-area">
            <input type="checkbox" name="ativo" <?= $banner['ativo'] ? 'checked' : '' ?>>
            Banner ativo
        </label>

        <hr>

        <h3>Imagem do Banner</h3>

        <div class="upload-area">
            <label for="imagem" class="upload-box">
                Clique para trocar a imagem (Opcional)
            </label>
            <input type="file" id="imagem" name="imagem" accept="image/png,image/jpeg,image/jpg,image/webp" hidden>
        </div>

        <div id="previewImagem" class="preview-imagens">
            <div class="preview-item" style="width: 100%; height: auto;">
                <img src="<?= htmlspecialchars($banner['imagem_fundo']) ?>" style="width: 100%; height: 120px; object-fit: cover;">
                <span style="font-size: 11px; color:#aaa; margin-top:5px;">Imagem Atual</span>
            </div>
        </div>

        <button type="submit" class="btn-add">
            Salvar Alterações
        </button>

    </form>
</main>

<script>
const inputImagem = document.getElementById('imagem');
const preview = document.getElementById('previewImagem');

inputImagem.addEventListener('change', function() {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `
                <div class="preview-item" style="width: 100%; height: auto;">
                    <img src="${e.target.result}" style="width: 100%; height: 120px; object-fit: cover;">
                    <span style="font-size: 11px; color:#aaa; margin-top:5px;">Nova Imagem</span>
                </div>
            `;
        }
        reader.readAsDataURL(file);
    }
});
</script>
</body>
</html>