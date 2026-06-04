<?php
require_once __DIR__ . '/AdminGuard.php';
require_once __DIR__ . '/src/Config/Database.php';

$pdo = Database::getConnection();
$erro_mensagem = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo = $_POST['tipo'] ?? 'hero_topo';
    $titulo = trim($_POST['titulo']);
    $texto_botao = trim($_POST['texto_botao']);
    $link_botao = trim($_POST['link_botao']);
    $ordem = intval($_POST['ordem'] ?? 0);
    $ativo = isset($_POST['ativo']) ? 1 : 0;

    if (empty($titulo) || empty($texto_botao) || empty($link_botao)) {
        $erro_mensagem = "Por favor, preencha todos os campos obrigatórios (Título, Texto do Botão e Link).";
    } elseif (!isset($_FILES['imagem']) || $_FILES['imagem']['error'] !== UPLOAD_ERR_OK) {
        $erro_mensagem = "Por favor, selecione uma imagem válida para o banner.";
    } else {
        // Upload da Imagem
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
            $nomeArquivo = 'banner_novo_' . time() . '_' . uniqid() . '.' . $extensao;
            $caminhoFisico = $pastaFisica . $nomeArquivo;
            $caminhoWeb = $pastaWeb . $nomeArquivo;

            if (move_uploaded_file($_FILES['imagem']['tmp_name'], $caminhoFisico)) {
                $insert = $pdo->prepare("
                    INSERT INTO home_banners (tipo, titulo, texto_botao, link_botao, imagem_fundo, ativo, ordem)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");

                $insert->execute([$tipo, $titulo, $texto_botao, $link_botao, $caminhoWeb, $ativo, $ordem]);

                header("Location: Banners.php?sucesso=adicionado");
                exit;
            } else {
                $erro_mensagem = "Erro ao fazer upload da imagem.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="/MagdaCrew/public/assets/images/MgdWhite.png">
    <title>Novo Banner - Magda Crew</title>
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

    <h1>Novo Banner</h1>
    <p class="subtitle">Adicione as informações da nova vitrine abaixo.</p>

    <?php if (!empty($erro_mensagem)): ?>
        <div class="alerta-erro"><?= $erro_mensagem ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        
        <div class="form-group">
            <select name="tipo" required>
                <option value="hero_topo" <?= (isset($_POST['tipo']) && $_POST['tipo'] == 'hero_topo') ? 'selected' : '' ?>>Banner do Topo</option>
                <option value="banner_baixo" <?= (isset($_POST['tipo']) && $_POST['tipo'] == 'banner_baixo') ? 'selected' : '' ?>>Banner de Baixo</option>
            </select>
        </div>

        <div class="form-group">
            <input type="text" name="titulo" placeholder="Título do banner" value="<?= isset($_POST['titulo']) ? htmlspecialchars($_POST['titulo']) : '' ?>" required>
        </div>

        <div class="form-group">
            <input type="text" name="texto_botao" placeholder="Texto do botão (Ex: Compre Agora)" value="<?= isset($_POST['texto_botao']) ? htmlspecialchars($_POST['texto_botao']) : 'Compre Agora' ?>" required>
        </div>

        <div class="form-group">
            <input type="text" name="link_botao" placeholder="Link do botão (Ex: /produtos)" value="<?= isset($_POST['link_botao']) ? htmlspecialchars($_POST['link_botao']) : '#' ?>" required>
        </div>

        <div class="form-group">
            <input type="number" name="ordem" placeholder="Ordem de exibição (Ex: 1)" value="<?= isset($_POST['ordem']) ? htmlspecialchars($_POST['ordem']) : '0' ?>">
        </div>

        <label class="check-area">
            <input type="checkbox" name="ativo" <?= ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['ativo'])) ? '' : 'checked' ?>>
            Banner ativo
        </label>

        <hr>

        <h3>Imagem do Banner</h3>

        <div class="upload-area">
            <label for="imagem" class="upload-box">
                Clique para selecionar a imagem
            </label>
            <input type="file" id="imagem" name="imagem" accept="image/png,image/jpeg,image/jpg,image/webp" hidden required>
        </div>

        <div id="previewImagem" class="preview-imagens"></div>

        <button type="submit" class="btn-add">
            Cadastrar Banner
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
                </div>
            `;
        }
        reader.readAsDataURL(file);
    } else {
        preview.innerHTML = '';
    }
});
</script>
</body>
</html>