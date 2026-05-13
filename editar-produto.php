<?php
require_once __DIR__ . '/src/Config/Database.php';

$pdo = Database::getConnection();

$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT * FROM produtos WHERE id = ?
");

$stmt->execute([$id]);

$produto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produto) {
    die("Produto não encontrado.");
}

$categorias = $pdo->query("
    SELECT * FROM categorias ORDER BY nome ASC
")->fetchAll(PDO::FETCH_ASSOC);

$imagens = $pdo->prepare("
    SELECT * FROM produto_imagens
    WHERE produto_id = ?
");

$imagens->execute([$id]);

$listaImagens = $imagens->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $preco = $_POST['preco'];
    $categoria_id = $_POST['categoria_id'];

    $destaque = isset($_POST['destaque']) ? 1 : 0;
    $ativo = isset($_POST['ativo']) ? 1 : 0;

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
                $id,
                $caminho,
                $isPrincipal,
                $key
            ]);
        }
    }

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
    header("Location: produtos.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Editar Produto</title>

<link rel="stylesheet" href="/MAGDA-CREW/public/assets/css/gestao.css">
</head>
<body>

<main class="main-content">

<section class="content">

<h1>Editar Produto</h1>

<form method="POST" enctype="multipart/form-data" class="form-admin">

    <input type="text" name="nome" value="<?= $produto['nome'] ?>" required>

    <textarea name="descricao" rows="5"><?= $produto['descricao'] ?></textarea>

    <input type="number" step="0.01" name="preco" value="<?= $produto['preco'] ?>" required>

    <select name="categoria_id">

        <?php foreach($categorias as $categoria): ?>

            <option 
                value="<?= $categoria['id'] ?>"
                <?= $produto['categoria_id'] == $categoria['id'] ? 'selected' : '' ?>
            >
                <?= $categoria['nome'] ?>
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

    <?php foreach($listaImagens as $index => $img): ?>

        <div class="preview-item">

            <img 
                src="/magda_crew/<?= $img['caminho_imagem'] ?>"
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

    <select name="imagem_principal">

        <option value="0">Primeira imagem</option>
        <option value="1">Segunda imagem</option>
        <option value="2">Terceira imagem</option>

    </select>

    <button type="submit" class="btn-add">
        Salvar Alterações
    </button>

</form>

</section>

</main>

</body>
</html>