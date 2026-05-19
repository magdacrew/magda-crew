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
    
    <link rel="stylesheet" href="/magda-crew/public/assets/css/adicionar-produto.css">
</head>
<body>

<main class="container-admin">
    <a href="javascript:history.back()">
        <img src="/magda-crew/public/assets/images/X.png" alt="Voltar" class="botao-x">
    </a>

    <h1>Editar Produto</h1>
    <p class="subtitle">Edite as informações do item abaixo.</p>

    <form method="POST" enctype="multipart/form-data">

        <div class="form-group">
            <input 
                type="text"
                name="nome"
                value="<?= htmlspecialchars($produto['nome']) ?>"
                placeholder="Nome do produto"
                required
            >
        </div>

        <div class="form-group">
            <textarea 
                name="descricao"
                rows="5"
                placeholder="Descrição"
            ><?= htmlspecialchars($produto['descricao']) ?></textarea>
        </div>

        <div class="form-group">
            <input 
                type="number"
                step="0.01"
                name="preco"
                value="<?= $produto['preco'] ?>"
                placeholder="Preço"
                required
            >
        </div>

        <div class="form-group">
            <select name="categoria_id" required>
                <?php foreach($categorias as $categoria): ?>
                    <option 
                        value="<?= $categoria['id'] ?>"
                        <?= $produto['categoria_id'] == $categoria['id'] ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars($categoria['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <label class="check-area">
            <input 
                type="checkbox"
                name="destaque"
                <?= $produto['destaque'] ? 'checked' : '' ?>
            >
            Produto em destaque
        </label>

        <label class="check-area">
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
                <?php $caminhoImagem = '/magda-crew/' . trim($img['caminho_imagem']); ?>
                <div class="preview-item">
                    <img src="<?= $caminhoImagem ?>" alt="">
                    <label class="principal-label">
                        <input 
                            type="radio"
                            name="imagem_existente_principal"
                            value="<?= $img['id'] ?>"
                            <?= $img['is_principal'] ? 'checked' : '' ?>
                        >
                        <span>Principal</span>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>

        <hr>

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

        <div id="previewImagens" class="preview-imagens" style="color: #666; font-size: 12px; margin-top: 10px;"></div>

        <button type="submit" class="btn-add">
            Salvar Alterações
        </button>

    </form>
</main>

<script>
const inputImagens = document.getElementById('imagens');
const preview = document.getElementById('previewImagens');

// Variável para armazenar todos os novos arquivos selecionados
let arquivosAcumulados = [];

// Função que atualiza o input original para o PHP receber os arquivos certos
function atualizarInputFiles() {
    const dataTransfer = new DataTransfer();
    arquivosAcumulados.forEach(file => {
        dataTransfer.items.add(file);
    });
    inputImagens.files = dataTransfer.files;
}

// Função para desenhar as novas imagens na tela
function renderizarPreview() {
    preview.innerHTML = ''; // Limpa a tela para redesenhar

    arquivosAcumulados.forEach((arquivo, index) => {
        const reader = new FileReader();

        reader.onload = function(e) {
            const div = document.createElement('div');
            div.classList.add('preview-item');

            div.innerHTML = `
                <button type="button" class="btn-remover" onclick="removerImagem(${index})"></button>
                
                <img src="${e.target.result}">

                <label class="principal-label">
                    <input 
                        type="radio"
                        name="imagem_principal"
                        value="${index}"
                        ${index === 0 ? 'checked' : ''}
                    >
                    <span>Principal</span>
                </label>
            `;

            preview.appendChild(div);
        }

        reader.readAsDataURL(arquivo);
    });
}

// Evento quando o usuário escolhe novas imagens
inputImagens.addEventListener('change', function() {
    const novosArquivos = Array.from(this.files);
    
    // Junta as imagens antigas com as novas
    arquivosAcumulados = arquivosAcumulados.concat(novosArquivos);
    
    atualizarInputFiles(); // Atualiza o campo <input> escondido
    renderizarPreview();   // Mostra na tela
});

// Função para remover uma nova imagem específica antes do upload
window.removerImagem = function(index) {
    arquivosAcumulados.splice(index, 1); // Remove 1 item do array na posição selecionada
    atualizarInputFiles();               // Atualiza o <input> para o PHP não receber a imagem apagada
    renderizarPreview();                 // Redesenha a tela
};
</script>

</body>
</html>