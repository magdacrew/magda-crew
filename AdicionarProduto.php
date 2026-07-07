<?php
require_once __DIR__ . '/AdminGuard.php';

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/MagdaCrew/public/assets/images/MgdWhite.png">
    <title>Novo Produto</title>
    <link rel="stylesheet" href="/MagdaCrew/public/assets/css/AdicionarProduto.css">
</head>
<body>

<main class="container-admin">
    <a href="javascript:history.back()">
        <img src="/MagdaCrew/public/assets/images/X.png" alt="Voltar" class="botao-x">
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
                        <?= htmlspecialchars($categoria['nome']) ?>
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

        <div id="previewImagens" class="preview-imagens preview-novas"></div>

        <button type="submit" class="btn-add">
            Cadastrar Produto
        </button>

    </form>
</main>

<div class="modal-confirmacao" id="modalRemoverImagem" aria-hidden="true">
    <div class="modal-confirmacao-card" role="dialog" aria-modal="true" aria-labelledby="modalRemoverTitulo">
        <button type="button" class="modal-fechar" id="btnFecharModal" aria-label="Fechar">×</button>

        <div class="modal-icone">!</div>

        <h2 id="modalRemoverTitulo">Remover imagem?</h2>
        <p>
            Essa imagem será removida da seleção do produto. 
            <strong>Ela não será enviada ao cadastrar.</strong>
        </p>

        <div class="modal-acoes">
            <button type="button" class="btn-modal btn-modal-cancelar" id="btnCancelarRemocao">
                Cancelar
            </button>
            <button type="button" class="btn-modal btn-modal-apagar" id="btnConfirmarRemocao">
                Apagar imagem
            </button>
        </div>
    </div>
</div>

<script>
const inputImagens = document.getElementById('imagens');
const preview = document.getElementById('previewImagens');
const modalRemoverImagem = document.getElementById('modalRemoverImagem');
const btnFecharModal = document.getElementById('btnFecharModal');
const btnCancelarRemocao = document.getElementById('btnCancelarRemocao');
const btnConfirmarRemocao = document.getElementById('btnConfirmarRemocao');

let arquivosAcumulados = [];
let imagemPendenteRemocao = null;
let imagemPrincipalAtual = 0;

function atualizarInputFiles() {
    const dataTransfer = new DataTransfer();
    arquivosAcumulados.forEach(file => {
        dataTransfer.items.add(file);
    });
    inputImagens.files = dataTransfer.files;
}

function renderizarPreview() {
    preview.innerHTML = '';

    if (arquivosAcumulados.length === 0) {
        imagemPrincipalAtual = 0;
        return;
    }

    if (imagemPrincipalAtual >= arquivosAcumulados.length) {
        imagemPrincipalAtual = 0;
    }

    arquivosAcumulados.forEach((arquivo, index) => {
        const reader = new FileReader();

        reader.onload = function(e) {
            const div = document.createElement('div');
            div.classList.add('preview-item');

            div.innerHTML = `
                <button type="button" class="btn-remover" onclick="abrirModalRemoverImagem(${index})"></button>
                
                <img src="${e.target.result}" alt="Imagem selecionada">

                <label class="principal-label">
                    <input 
                        type="radio"
                        name="imagem_principal"
                        value="${index}"
                        ${index === imagemPrincipalAtual ? 'checked' : ''}
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

    if (arquivosAcumulados.length === 0 && novosArquivos.length > 0) {
        imagemPrincipalAtual = 0;
    }

    arquivosAcumulados = arquivosAcumulados.concat(novosArquivos);
    atualizarInputFiles();
    renderizarPreview();
});

preview.addEventListener('change', function(event) {
    if (event.target.name === 'imagem_principal') {
        imagemPrincipalAtual = parseInt(event.target.value, 10);
    }
});

function abrirModalRemoverImagem(index) {
    imagemPendenteRemocao = index;
    modalRemoverImagem.classList.add('show');
    modalRemoverImagem.setAttribute('aria-hidden', 'false');
    document.body.classList.add('modal-open');
    btnConfirmarRemocao.focus();
}

function fecharModalRemoverImagem() {
    imagemPendenteRemocao = null;
    modalRemoverImagem.classList.remove('show');
    modalRemoverImagem.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('modal-open');
}

function removerImagemConfirmada() {
    if (imagemPendenteRemocao === null) {
        return;
    }

    const indexRemovido = imagemPendenteRemocao;
    arquivosAcumulados.splice(indexRemovido, 1);

    if (indexRemovido === imagemPrincipalAtual) {
        imagemPrincipalAtual = 0;
    } else if (indexRemovido < imagemPrincipalAtual) {
        imagemPrincipalAtual--;
    }

    atualizarInputFiles();
    renderizarPreview();
    fecharModalRemoverImagem();
}

btnFecharModal.addEventListener('click', fecharModalRemoverImagem);
btnCancelarRemocao.addEventListener('click', fecharModalRemoverImagem);
btnConfirmarRemocao.addEventListener('click', removerImagemConfirmada);

modalRemoverImagem.addEventListener('click', function(event) {
    if (event.target === modalRemoverImagem) {
        fecharModalRemoverImagem();
    }
});

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape' && modalRemoverImagem.classList.contains('show')) {
        fecharModalRemoverImagem();
    }
});
</script>
</body>
</html>
