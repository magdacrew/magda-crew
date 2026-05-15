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

<link rel="stylesheet" href="/MAGDA-CREW/public/assets/css/adicionar-produto.css">
</head>
<body>

<main class="container-admin">
    <a href="javascript:history.back()">
        <img src="/magda-crew/public/assets/images/X.png" alt="Voltar" class="botao-x">
    </a>

    <h1>Novo Produto</h1>
    <p class="subtitle">Adicione as informações do novo item abaixo.</p>

    <form method="POST" enctype="multipart/form-data">
        
        <div class="form-group">
            <input type="text" name="nome" placeholder="Nome do produto" required>
        </div>

        <div class="form-group">
            <textarea name="descricao" rows="5" placeholder="Descrição"></textarea>
        </div>

        <div class="form-group">
            <input type="number" step="0.01" name="preco" placeholder="Preço" required>
        </div>

        <div class="form-group">
            <select name="categoria_id" required>
                <option value="">Selecione uma categoria</option>
                <?php foreach($categorias as $categoria): ?>
                    <option value="<?= $categoria['id'] ?>">
                        <?= $categoria['nome'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <label class="check-area">
            <input type="checkbox" name="destaque">
            Produto em destaque
        </label>

        <label class="check-area">
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

        <div id="previewImagens" class="preview-imagens" style="color: #666; font-size: 12px; margin-top: 10px;"></div>

        <button type="submit" class="btn-add">
            Cadastrar Produto
        </button>

    </form>
</main>
<script>

const inputImagens = document.getElementById('imagens');
const preview = document.getElementById('previewImagens');

// Variável para armazenar todos os arquivos selecionados
let arquivosAcumulados = [];

// Função que atualiza o input original para o PHP receber os arquivos certos
function atualizarInputFiles() {
    const dataTransfer = new DataTransfer();
    arquivosAcumulados.forEach(file => {
        dataTransfer.items.add(file);
    });
    inputImagens.files = dataTransfer.files;
}

// Função para desenhar as imagens na tela
function renderizarPreview() {
    preview.innerHTML = ''; // Limpa a tela para redesenhar

    arquivosAcumulados.forEach((arquivo, index) => {
        const reader = new FileReader();

        reader.onload = function(e) {
            const div = document.createElement('div');
            div.classList.add('preview-item');

            // Agora usando o botão vazio (o CSS coloca o X.png) e a estrutura correta
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

// Função para remover uma imagem específica
window.removerImagem = function(index) {
    arquivosAcumulados.splice(index, 1); // Remove 1 item do array na posição selecionada
    atualizarInputFiles();               // Atualiza o <input> para o PHP não receber a imagem apagada
    renderizarPreview();                 // Redesenha a tela
};

</script>
</body>
</html>