<?php
require_once __DIR__ . '/src/Config/Database.php';

$pdo = Database::getConnection();

$id = (int) ($_GET['id'] ?? 0);

// Inicializa a variável de erro vazia
$erro_mensagem = "";

// 1. CARREGA OS DADOS ORIGINAIS DO BANCO DE DADOS (Sempre roda ao abrir a página)
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


// 2. PROCESSA O FORMULÁRIO APENAS SE FOR UM ENVIO (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao']);
    $preco = $_POST['preco'];
    $categoria_id = $_POST['categoria_id'];

    $destaque = isset($_POST['destaque']) ? 1 : 0;
    $ativo = isset($_POST['ativo']) ? 1 : 0;

    // Criamos um rascunho temporário com o que o usuário digitou para não perder caso dê erro
    $dados_digitados = [
        'id'           => $id,
        'nome'         => $nome,
        'descricao'    => $descricao,
        'preco'        => $preco,
        'categoria_id' => $categoria_id,
        'destaque'     => $destaque,
        'ativo'        => $ativo
    ];

    // VERIFICAÇÃO 1: Campos obrigatórios vazios
    if (empty($nome) || empty($preco) || empty($categoria_id)) {
        $erro_mensagem = "Por favor, preencha todos os campos obrigatórios.";
        $produto = $dados_digitados;
    } else {

        // VERIFICAÇÃO 2: Se já existe OUTRO produto com o mesmo nome
        $checkNome = $pdo->prepare("SELECT id FROM produtos WHERE nome = ? AND id != ?");
        $checkNome->execute([$nome, $id]);

        if ($checkNome->rowCount() > 0) {
            $erro_mensagem = "Não foi possível salvar. Já existe outro produto cadastrado com o nome '<strong>" . htmlspecialchars($nome) . "</strong>'.";
            $produto = $dados_digitados;
        } else {

            // TUDO CERTO! Atualiza os dados textuais do produto
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

            // === 3. REMOVER IMAGENS SELECIONADAS DO BANCO ===
            if (!empty($_POST['imagens_removidas'])) {
                $idsParaRemover = array_map('intval', explode(',', $_POST['imagens_removidas']));
                
                if (count($idsParaRemover) > 0) {
                    $inQuery = implode(',', array_fill(0, count($idsParaRemover), '?'));
                    
                    $deletarImagens = $pdo->prepare("
                        DELETE FROM produto_imagens 
                        WHERE id IN ($inQuery) AND produto_id = ?
                    ");
                    
                    $parametrosDelecao = array_merge($idsParaRemover, [$id]);
                    $deletarImagens->execute($parametrosDelecao);
                }
            }

            // === 4. TRATAMENTO DA IMAGEM PRINCIPAL UNIFICADA ===
            $tipoPrincipal = '';
            $idPrincipalAlvo = null;

            if (isset($_POST['imagem_selecionada_principal'])) {
                $parts = explode('_', $_POST['imagem_selecionada_principal']);
                $tipoPrincipal = $parts[0]; // 'existente' ou 'nova'
                $idPrincipalAlvo = (int)$parts[1]; // ID do banco ou index do array
            }

            // Reseta temporariamente todas as imagens atuais do produto para 0
            $pdo->prepare("
                UPDATE produto_imagens
                SET is_principal = 0
                WHERE produto_id = ?
            ")->execute([$id]);

            // Se o rádio selecionado pertencia a uma imagem antiga, ativa ela de volta
            if ($tipoPrincipal === 'existente' && $idPrincipalAlvo > 0) {
                $pdo->prepare("
                    UPDATE produto_imagens
                    SET is_principal = 1
                    WHERE id = ? AND produto_id = ?
                ")->execute([$idPrincipalAlvo, $id]);
            }

            // === 5. ADICIONAR NOVAS IMAGENS (Idêntico ao adicionar-produtos.php) ===
            if (!empty($_FILES['imagens']['name'][0])) {
                foreach ($_FILES['imagens']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['imagens']['error'][$key] !== UPLOAD_ERR_OK) {
                        continue;
                    }

                    $nomeArquivo = time() . '_' . $_FILES['imagens']['name'][$key];
                    $caminhoBanco = 'public/assets/images/produtos/' . $nomeArquivo;
                    $arquivoFisico = __DIR__ . '/' . $caminhoBanco;

                    $pastaFisica = dirname($arquivoFisico);
                    if (!is_dir($pastaFisica)) {
                        mkdir($pastaFisica, 0777, true);
                    }

                    if (move_uploaded_file($tmp_name, $arquivoFisico)) {
                        
                        // Verifica se esta nova imagem do loop atual foi a marcada no rádio global
                        $isPrincipal = ($tipoPrincipal === 'nova' && $idPrincipalAlvo === $key) ? 1 : 0;

                        $insertImagem = $pdo->prepare("
                            INSERT INTO produto_imagens
                            (produto_id, caminho_imagem, is_principal, ordem)
                            VALUES (?, ?, ?, ?)
                        ");

                        $insertImagem->execute([$id, $caminhoBanco, $isPrincipal, $key]);
                    }
                }
            }

            // Redireciona com sucesso para a listagem
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
    <title>Editar Produto</title>
    <link rel="icon" type="image/png" href="/magda-crew/public/assets/images/15.png">
    <link rel="stylesheet" href="/magda-crew/public/assets/css/adicionar-produto.css">
    
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

    <h1>Editar Produto</h1>
    <p class="subtitle">Edite as informações do item abaixo.</p>

    <?php if (!empty($erro_mensagem)): ?>
        <div class="alerta-erro">
            <?= $erro_mensagem ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        
        <input type="hidden" name="imagens_removidas" id="imagensRemovidas" value="">

        <div class="form-group">
            <input 
                type="text"
                name="nome"
                value="<?= htmlspecialchars($produto['nome'] ?? '') ?>"
                placeholder="Nome do produto"
                required
            >
        </div>

        <div class="form-group">
            <textarea 
                name="descricao"
                rows="5"
                placeholder="Descrição"
            ><?= htmlspecialchars($produto['descricao'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <input 
                type="number"
                step="0.01"
                name="preco"
                value="<?= htmlspecialchars($produto['preco'] ?? '') ?>"
                placeholder="Preço"
                required
            >
        </div>

        <div class="form-group">
            <select name="categoria_id" required>
                <option value="">Selecione uma categoria</option>
                <?php foreach($categorias as $categoria): ?>
                    <option 
                        value="<?= $categoria['id'] ?>"
                        <?= (isset($produto['categoria_id']) && $produto['categoria_id'] == $categoria['id']) ? 'selected' : '' ?>
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
                <?= (!empty($produto['destaque'])) ? 'checked' : '' ?>
            >
            Produto em destaque
        </label>

        <label class="check-area">
            <input 
                type="checkbox"
                name="ativo"
                <?= (!empty($produto['ativo'])) ? 'checked' : '' ?>
            >
            Produto ativo
        </label>

        <hr>

        <h3>Imagens atuais</h3>
        <div class="preview-imagens">
            <?php foreach($listaImagens as $img): ?>
                <?php $caminhoImagem = '/magda-crew/' . trim($img['caminho_imagem']); ?>
                <div class="preview-item" id="imagem-existente-<?= $img['id'] ?>">
                    
                    <button type="button" class="btn-remover" onclick="removerImagemExistente(<?= $img['id'] ?>)"></button>
                    
                    <img src="<?= $caminhoImagem ?>" alt="">
                    <label class="principal-label">
                        <input 
                            type="radio"
                            name="imagem_selecionada_principal"
                            value="existente_<?= $img['id'] ?>"
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
const inputRemovidas = document.getElementById('imagensRemovidas');

let idsRemovidos = [];

// Gerencia a remoção das imagens vindas do banco
window.removerImagemExistente = function(id) {
    if(confirm("Deseja realmente remover esta imagem?")) {
        idsRemovidos.push(id);
        inputRemovidas.value = idsRemovidos.join(',');
        
        const elementoImagem = document.getElementById(`imagem-existente-${id}`);
        if(elementoImagem) {
            elementoImagem.remove();
        }
    }
};

// Gerencia a lógica de acúmulo e renderização de novos uploads
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

    // Verifica se o usuário já tem algum rádio de imagem atual selecionado na tela
    const temPrincipalExistente = document.querySelector('input[name="imagem_selecionada_principal"]:checked') !== null;

    arquivosAcumulados.forEach((arquivo, index) => {
        const reader = new FileReader();

        reader.onload = function(e) {
            const div = document.createElement('div');
            div.classList.add('preview-item');

            // Se nenhuma imagem do banco estiver marcada (ou todas foram excluídas), marca a primeira nova
            const debrucarChecked = (!temPrincipalExistente && index === 0) ? 'checked' : '';

            div.innerHTML = `
                <button type="button" class="btn-remover" onclick="removerImagem(${index})"></button>
                
                <img src="${e.target.result}">

                <label class="principal-label">
                    <input 
                        type="radio"
                        name="imagem_selecionada_principal"
                        value="nova_${index}"
                        ${debrucarChecked}
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