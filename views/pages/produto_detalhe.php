<?php
// 1. CONEXÃO COM A BASE DE DADOS (Caminho corrigido com ../../)
require_once __DIR__ . '/../../src/Config/Database.php';
$pdo = Database::getConnection();

// 2. BUSCAR AS IMAGENS DA GALERIA
// A variável $produto já vem do teu ProdutosController, por isso usamos o ID dela!
$galeria_imagens = [];

if (isset($produto['id'])) {
    $stmt_imagens = $pdo->prepare("
        SELECT caminho_imagem 
        FROM produto_imagens 
        WHERE produto_id = ? 
        ORDER BY is_principal DESC, ordem ASC
    ");
    $stmt_imagens->execute([$produto['id']]);
    $resultados_imagens = $stmt_imagens->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($resultados_imagens as $img) {
        $galeria_imagens[] = $img['caminho_imagem'];
    }
}

// Fallback: Se não houver imagens na tabela nova, usa a imagem principal do produto
if (empty($galeria_imagens) && !empty($produto['caminho_imagem'])) {
    $galeria_imagens[] = $produto['caminho_imagem'];
}

// 3. CARREGA O HEADER DA PÁGINA
require_once __DIR__ . '/../components/header.php'; 
?>

<link rel="stylesheet" href="/MAGDA-CREW/public/assets/css/footer.css">
<link rel="stylesheet" href="/MAGDA-CREW/public/assets/css/index.css">

<div class="container-produto">
    <div class="produto-galeria">
        <div class="imagem-placeholder-detalhe">
            <div class="imagem-produto" style="position: relative;">

                <?php if (count($galeria_imagens) > 0): ?>
                    <?php if (count($galeria_imagens) > 1): ?>
                        <button class="seta-galeria esq" onclick="mudarImagem(-1)">&#10094;</button>
                    <?php endif; ?>

                    <?php 
                        // Verificação para garantir que o caminho da imagem está correto
                        $src_imagem = $galeria_imagens[0];
                        if (strpos($src_imagem, 'public/') === false && strpos($src_imagem, 'http') === false) {
                            $src_imagem = 'public/assets/images/produtos/' . $src_imagem;
                        }
                    ?>

                    <img id="imagem-principal" src="/magda-crew/<?= $src_imagem ?>" 
                         alt="<?= htmlspecialchars($produto['nome']) ?>" 
                         style="width: 100%; height: auto; border-radius: 8px; object-fit: cover; aspect-ratio: 1/1;">
                    
                    <?php if (count($galeria_imagens) > 1): ?>
                        <button class="seta-galeria dir" onclick="mudarImagem(1)">&#10095;</button>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div class="imagem-placeholder">
                        <span>Sem Imagem</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (count($galeria_imagens) > 1): ?>
        <div class="miniaturas-container">
            <?php foreach ($galeria_imagens as $index => $caminho): ?>
                <?php 
                    if (strpos($caminho, 'public/') === false && strpos($caminho, 'http') === false) {
                        $caminho = 'public/assets/images/produtos/' . $caminho;
                    }
                ?>
                <img src="/magda-crew/<?= $caminho ?>" 
                     class="miniatura <?= $index === 0 ? 'ativa' : '' ?>" 
                     onclick="selecionarImagem(<?= $index ?>)" 
                     data-src="/magda-crew/<?= $caminho ?>"
                     alt="Miniatura <?= $index + 1 ?>">
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="produto-info">
        <p class="categoria-badge"><?= htmlspecialchars($produto['categoria_nome'] ?? '') ?></p>
        <h1><?= htmlspecialchars($produto['nome'] ?? '') ?></h1>
        
        <div class="preco">R$ <?= number_format($produto['preco'] ?? 0, 2, ',', '.') ?></div>
        
        <p class="descricao"><?= nl2br(htmlspecialchars($produto['descricao'] ?? '')) ?></p>

        <form action="/MAGDA-CREW/public/carrinho/adicionar" method="POST" class="form-compra">
            
            <?php if (isset($variantes) && count($variantes) > 0): ?>
                <label for="variante_id">Escolha a opção (Cor / Tamanho):</label>
                <select name="variante_id" id="variante_id" required>
                    <option value="" disabled selected>Selecione...</option>
                    <?php foreach ($variantes as $var): ?>
                        <option value="<?= $var['id'] ?>">
                            <?= htmlspecialchars($var['cor_nome'] ?? '') ?> - Tamanho <?= htmlspecialchars($var['tamanho_nome'] ?? '') ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="btn-comprar">Adicionar ao Carrinho</button>
            <?php else: ?>
                <p style="color: red; font-weight: bold;">Produto Esgotado no Momento.</p>
                <button type="button" class="btn-comprar" disabled style="background: #ccc;">Indisponível</button>
            <?php endif; ?>
            
        </form>
    </div>
</div>

<?php include $_SERVER['DOCUMENT_ROOT']. '/magda-crew/views/components/footer.php';?>
<script src="/MAGDA-CREW/public/assets/js/script.js"></script>

<script>
    let imagemAtual = 0;
    const miniaturas = document.querySelectorAll('.miniatura');
    const imagemPrincipal = document.getElementById('imagem-principal');

    function mudarImagem(direcao) {
        if (miniaturas.length === 0) return;
        
        imagemAtual += direcao;
        
        // Se passar da última volta para a primeira, e vice-versa
        if (imagemAtual >= miniaturas.length) {
            imagemAtual = 0;
        } else if (imagemAtual < 0) {
            imagemAtual = miniaturas.length - 1;
        }
        
        atualizarGaleria();
    }

    function selecionarImagem(index) {
        imagemAtual = index;
        atualizarGaleria();
    }

    function atualizarGaleria() {
        if (miniaturas.length === 0) return;
        
        // Troca o src da imagem principal usando o data-src da miniatura clicada
        const novaSrc = miniaturas[imagemAtual].getAttribute('data-src');
        imagemPrincipal.src = novaSrc;

        // Remove a classe 'ativa' de todas as miniaturas
        miniaturas.forEach(min => min.classList.remove('ativa'));
        
        // Adiciona a classe 'ativa' apenas na miniatura selecionada
        miniaturas[imagemAtual].classList.add('ativa');
    }
</script>