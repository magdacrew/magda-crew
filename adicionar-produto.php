<?php
require_once __DIR__ . '/src/Config/Database.php';

$pdo = Database::getConnection();

$categorias = $pdo->query("
    SELECT * FROM categorias ORDER BY nome ASC
")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $preco = $_POST['preco'];
    $categoria_id = $_POST['categoria_id'];

    $destaque = isset($_POST['destaque']) ? 1 : 0;
    $ativo = isset($_POST['ativo']) ? 1 : 0;

    $insert = $pdo->prepare("
        INSERT INTO produtos
        (
            nome,
            descricao,
            preco,
            categoria_id,
            destaque,
            ativo
        )
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $insert->execute([
        $nome,
        $descricao,
        $preco,
        $categoria_id,
        $destaque,
        $ativo
    ]);

    $produto_id = $pdo->lastInsertId();

    if (!empty($_FILES['imagens']['name'][0])) {

        foreach ($_FILES['imagens']['tmp_name'] as $key => $tmp_name) {

            $nomeArquivo = time() . '_' . $_FILES['imagens']['name'][$key];

            $caminho = 'public/assets/images/produtos/' . $nomeArquivo;

            move_uploaded_file($tmp_name, $caminho);

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
                $produto_id,
                $caminho,
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
<link rel="icon" type="image/png" href="/magda-crew/public/assets/images/15.png">
<title>Novo Produto</title>

<link rel="stylesheet" href="/MAGDA-CREW/public/assets/css/gestao.css">
</head>
<body>

<main class="main-content">

<section class="content">

<h1>Novo Produto</h1>

<form method="POST" enctype="multipart/form-data" class="form-admin">

    <input type="text" name="nome" placeholder="Nome do produto" required>

    <textarea name="descricao" rows="5" placeholder="Descrição"></textarea>

    <input type="number" step="0.01" name="preco" placeholder="Preço" required>

    <select name="categoria_id" required>

        <option value="">Selecione uma categoria</option>

        <?php foreach($categorias as $categoria): ?>

            <option value="<?= $categoria['id'] ?>">
                <?= $categoria['nome'] ?>
            </option>

        <?php endforeach; ?>

    </select>

    <label>
        <input type="checkbox" name="destaque">
        Produto em destaque
    </label>

    <label>
        <input type="checkbox" name="ativo" checked>
        Produto ativo
    </label>

    <hr>

    <h3>Imagens do Produto</h3>

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

    <p>Escolha qual será a imagem principal:</p>

    <select name="imagem_principal">

        <option value="0">Primeira imagem</option>
        <option value="1">Segunda imagem</option>
        <option value="2">Terceira imagem</option>
        <option value="3">Quarta imagem</option>

    </select>

    <button type="submit" class="btn-add">
        Cadastrar Produto
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
                <img src="${e.target.result}">

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