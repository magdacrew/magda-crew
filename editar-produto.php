<?php
require_once __DIR__ . '/src/Config/Database.php';

$pdo = Database::getConnection();

$id = (int) ($_GET['id'] ?? 0);

// PRODUTO
$stmt = $pdo->prepare("
    SELECT * FROM produtos WHERE id = ?
");

$stmt->execute([$id]);

$produto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produto) {
    die("Produto não encontrado.");
}

// CATEGORIAS
$categorias = $pdo->query("
    SELECT * FROM categorias ORDER BY nome ASC
")->fetchAll(PDO::FETCH_ASSOC);

// IMAGENS
$imagens = $pdo->prepare("
    SELECT * FROM produto_imagens
    WHERE produto_id = ?
    ORDER BY ordem ASC
");

$imagens->execute([$id]);

$listaImagens = $imagens->fetchAll(PDO::FETCH_ASSOC);

// SALVAR
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $preco = $_POST['preco'];
    $categoria_id = $_POST['categoria_id'];

    $destaque = isset($_POST['destaque']) ? 1 : 0;
    $ativo = isset($_POST['ativo']) ? 1 : 0;

    // UPDATE PRODUTO
    $update = $pdo->prepare("
        UPDATE produtos
        SET
            nome = ?,
            descricao = ?,
            preco = ?,
            categoria_id = ?,
            destaque = ?,
            ativo = ?
        WHERE id = ?
    ");

    $update->execute([
        $nome,
        $descricao,
        $preco,
        $categoria_id,
        $destaque,
        $ativo,
        $id
    ]);

    // ALTERAR IMAGEM PRINCIPAL
    if(isset($_POST['imagem_existente_principal'])) {

        $imagemPrincipal = $_POST['imagem_existente_principal'];

        $pdo->prepare("
            UPDATE produto_imagens
            SET is_principal = 0
            WHERE produto_id = ?
        ")->execute([$id]);

        $pdo->prepare("
            UPDATE produto_imagens
            SET is_principal = 1
            WHERE id = ?
        ")->execute([$imagemPrincipal]);
    }

    // NOVAS IMAGENS
    if (!empty($_FILES['imagens']['name'][0])) {

        foreach ($_FILES['imagens']['tmp_name'] as $key => $tmp_name) {

            $nomeArquivo = time() . '_' . basename($_FILES['imagens']['name'][$key]);

            // CAMINHO FÍSICO
            $pastaFisica = __DIR__ . '/public/assets/images/produtos/';

            // CRIA PASTA SE NÃO EXISTIR
            if (!is_dir($pastaFisica)) {
                mkdir($pastaFisica, 0777, true);
            }

            // CAMINHO DO ARQUIVO
            $arquivoFisico = $pastaFisica . $nomeArquivo;

            // CAMINHO SALVO NO BANCO
            $caminhoBanco = $nomeArquivo;

            // UPLOAD
            move_uploaded_file($tmp_name, $arquivoFisico);

            $isPrincipal = ($_POST['imagem_principal'] == $key) ? 1 : 0;

            $insertImagem = $pdo->prepare("
                INSERT INTO produto_imagens
                (
                    produto_id,
                    caminho_imagem,
                    is_principal,
                    ordem
                )
                VALUES (?, ?, ?, ?)
            ");

            $insertImagem->execute([
                $id,
                $caminhoBanco,
                $isPrincipal,
                $key
            ]);
        }
    }

    header("Location: produtos.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Editar Produto</title>

<link rel="icon" type="image/png" href="/magda-crew/public/assets/images/15.png">

<link rel="stylesheet" href="/magda-crew/public/assets/css/gestao.css">

</head>

<body>

<main class="main-content">

<section class="content">

<h1>Editar Produto</h1>

<form method="POST" enctype="multipart/form-data" class="form-admin">

    <input 
        type="text"
        name="nome"
        value="<?= htmlspecialchars($produto['nome']) ?>"
        required
    >

    <textarea 
        name="descricao"
        rows="5"
    ><?= htmlspecialchars($produto['descricao']) ?></textarea>

    <input 
        type="number"
        step="0.01"
        name="preco"
        value="<?= $produto['preco'] ?>"
        required
    >

    <select name="categoria_id">

        <?php foreach($categorias as $categoria): ?>

            <option 
                value="<?= $categoria['id'] ?>"
                <?= $produto['categoria_id'] == $categoria['id'] ? 'selected' : '' ?>
            >
                <?= htmlspecialchars($categoria['nome']) ?>
            </option>

        <?php endforeach; ?>

    </select>

    <label>
        <input 
            type="checkbox"
            name="destaque"
            <?= $produto['destaque'] ? 'checked' : '' ?>
        >
        Produto em destaque
    </label>

    <label>
        <input 
            type="checkbox"
            name="ativo"
            <?= $produto['ativo'] ? 'checked' : '' ?>
        >
        Produto ativo
    </label>

    <hr>

    <h3>Imagens atuais</h3>

<div class="preview-imagens">

<?php foreach($listaImagens as $img): ?>

    <?php

    // CAMINHO COMPLETO DA IMAGEM
    $caminhoImagem = '/magda-crew/public/assets/images/produtos/' . trim($img['caminho_imagem']);

    ?>

    <div class="preview-item">

        <!-- MOSTRA O CAMINHO -->
        
    
        <!-- IMAGEM -->
        <img 
         src="<?= $caminhoImagem ?>"
    alt=""
    style="
        width:100%;
        height:160px;
        object-fit:cover;
        border-radius:10px;
    "
        >

        <label class="principal-label">

            <input 
                type="radio"
                name="imagem_existente_principal"
                value="<?= $img['id'] ?>"
                <?= $img['is_principal'] ? 'checked' : '' ?>
            >

            Principal

        </label>

    </div>

<?php endforeach; ?>

</div>

    <h3>Adicionar novas imagens</h3>

    <div class="upload-area">

        <label for="imagens" class="upload-box">
            Clique para selecionar imagens
        </label>

        <input 
            type="file"
            id="imagens"
            name="imagens[]"
            multiple
            accept="image/*"
            hidden
        >

    </div>

    <div id="previewImagens" class="preview-imagens"></div>

    <button type="submit" class="btn-add">
        Salvar Alterações
    </button>

</form>

</section>

</main>

<script>

const inputImagens = document.getElementById('imagens');

const preview = document.getElementById('previewImagens');

inputImagens.addEventListener('change', function() {

    preview.innerHTML = '';

    const arquivos = this.files;

    for(let i = 0; i < arquivos.length; i++) {

        const reader = new FileReader();

        reader.onload = function(e) {

            const div = document.createElement('div');

            div.classList.add('preview-item');

            div.innerHTML = `
                <img 
                    src="${e.target.result}"
                    style="width:100%; height:160px; object-fit:cover;"
                >

                <label class="principal-label">

                    <input 
                        type="radio"
                        name="imagem_principal"
                        value="${i}"
                        ${i === 0 ? 'checked' : ''}
                    >

                    Principal

                </label>
            `;

            preview.appendChild(div);
        }

        reader.readAsDataURL(arquivos[i]);
    }

});

</script>

</body>
</html>