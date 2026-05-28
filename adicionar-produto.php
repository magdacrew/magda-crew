<?php
require_once __DIR__ . '/admin_guard.php';

require_once __DIR__ . '/src/Config/Database.php';

$pdo = Database::getConnection();

// Inicializa a variável de erro
$erro_mensagem = "";

$categorias = $pdo->query("
    SELECT * FROM categorias ORDER BY nome ASC
")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Limpa os dados contra espaços extras
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao']);
    $preco = $_POST['preco'];
    $categoria_id = $_POST['categoria_id'];

    $destaque = isset($_POST['destaque']) ? 1 : 0;
    $ativo = isset($_POST['ativo']) ? 1 : 0;

    // 1. VERIFICAÇÃO: Campos obrigatórios vazios
    if (empty($nome) || empty($preco) || empty($categoria_id)) {
        $erro_mensagem = "Por favor, preencha todos os campos obrigatórios (Nome, Preço e Categoria).";
    } else {
        
        // 2. VERIFICAÇÃO: Se já existe um produto com o mesmo nome
        $checkNome = $pdo->prepare("SELECT id FROM produtos WHERE nome = ?");
        $checkNome->execute([$nome]);
        
        if ($checkNome->rowCount() > 0) {
            $erro_mensagem = "Já existe um produto cadastrado com o nome '<strong>" . htmlspecialchars($nome) . "</strong>'. Escolha outro nome.";
        } else {
            
            // Se passou nas validações, faz o cadastro do produto
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

            // Verifica se arquivos foram enviados de fato (e se não houve erro no upload do array)
            if (!empty($_FILES['imagens']['name'][0])) {

                foreach ($_FILES['imagens']['tmp_name'] as $key => $tmp_name) {
                    
                    // Pula caso haja algum erro específico com esse arquivo do array
                    if ($_FILES['imagens']['error'][$key] !== UPLOAD_ERR_OK) {
                        continue;
                    }

                    $nomeArquivo = time() . '_' . $_FILES['imagens']['name'][$key];
                    $caminho = 'public/assets/images/produtos/' . $nomeArquivo;

                    // Move o arquivo e salva no banco apenas se o upload deu certo
                    if (move_uploaded_file($tmp_name, $caminho)) {
                        
                        // Garante que o índice enviado pelo radio do JS bate com o índice do loop
                        $isPrincipal = (isset($_POST['imagem_principal']) && $_POST['imagem_principal'] == $key) ? 1 : 0;

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
            }

            // Redireciona apenas se tudo deu certo
            header("Location: produtos.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <link class="icon" type="image/png" href="/magda-crew/public/assets/images/15.png">
    <title>Novo Produto</title>
    <link rel="stylesheet" href="/MAGDA-CREW/public/assets/css/adicionar-produto.css">
    
    <style>
        .alerta-erro {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>

<main class="container-admin">
    <a href="javascript:history.back()">
        <img src="/magda-crew/public/assets/images/X.png" alt="Voltar" class="botao-x">
    </a>

    <h1>Novo Produto</h1>
    <p class="subtitle">Adicione as informações do novo item abaixo.</p>

    <?php if (!empty($erro_mensagem)): ?>
        <div class="alerta-erro">
            <?= $erro_mensagem ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        
        <div class="form-group">
            <input type="text" name="nome" placeholder="Nome do produto" value="<?= isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : '' ?>" required>
        </div>

        <div class="form-group">
            <textarea name="descricao" rows="5" placeholder="Descrição"><?= isset($_POST['descricao']) ? htmlspecialchars($_POST['descricao']) : '' ?></textarea>
        </div>

        <div class="form-group">
            <input type="number" step="0.01" name="preco" placeholder="Preço" value="<?= isset($_POST['preco']) ? htmlspecialchars($_POST['preco']) : '' ?>" required>
        </div>

        <div class="form-group">
            <select name="categoria_id" required>
                <option value="">Selecione uma categoria</option>
                <?php foreach($categorias as $categoria): ?>
                    <option value="<?= $categoria['id'] ?>" <?= (isset($_POST['categoria_id']) && $_POST['categoria_id'] == $categoria['id']) ? 'selected' : '' ?>>
                        <?= $categoria['nome'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <label class="check-area">
            <input type="checkbox" name="destaque" <?= isset($_POST['destaque']) ? 'checked' : '' ?>>
            Produto em destaque
        </label>

        <label class="check-area">
            <input type="checkbox" name="ativo" <?= ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['ativo'])) ? '' : 'checked' ?>>
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

let arquivosAcumulados = [];

function atualizarInputFiles() {
    const dataTransfer = new DataTransfer();
    arquivosAcumulados.forEach(file => {
        dataTransfer.items.add(file);
    });
    inputImagens.files = dataTransfer.files;
}

function renderizarPreview() {
    preview.innerHTML = ''; 

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

inputImagens.addEventListener('change', function() {
    const novosArquivos = Array.from(this.files);
    arquivosAcumulados = arquivosAcumulados.concat(novosArquivos);
    atualizarInputFiles(); 
    renderizarPreview();   
});

window.removerImagem = function(index) {
    arquivosAcumulados.splice(index, 1); 
    atualizarInputFiles();               
    renderizarPreview();                 
};
</script>
</body>
</html>