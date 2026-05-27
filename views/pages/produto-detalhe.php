<?php
// 1. CONEXÃO COM A BASE DE DADOS
require_once __DIR__ . '/../../src/Config/Database.php';
$pdo = Database::getConnection();

// 2. BUSCAR AS IMAGENS DA GALERIA
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

if (empty($galeria_imagens) && !empty($produto['caminho_imagem'])) {
    $galeria_imagens[] = $produto['caminho_imagem'];
}

// 3. CARREGA O HEADER
require_once __DIR__ . '/../components/header.php'; 
?>

<link rel="stylesheet" href="/MAGDA-CREW/public/assets/css/detalhe-produto.css">
<link rel="stylesheet" href="/MAGDA-CREW/public/assets/css/footer.css">

<div class="container-produto">
    <div class="produto-galeria">
        <div class="imagem-placeholder-detalhe">
            <div class="imagem-produto" style="position: relative; width: 100%;">
                <?php if (count($galeria_imagens) > 0): ?>
                    <?php 
                        $src_imagem = $galeria_imagens[0];
                        if (strpos($src_imagem, 'public/') === false && strpos($src_imagem, 'http') === false) {
                            $src_imagem = 'public/assets/images/produtos/' . $src_imagem;
                        }
                    ?>
                    <img id="imagem-principal" src="/MAGDA-CREW/<?= $src_imagem ?>" alt="<?= htmlspecialchars($produto['nome']) ?>">
                    
                    <?php if (count($galeria_imagens) > 1): ?>
                        <button class="seta-galeria esq" onclick="mudarImagem(-1)">&#10094;</button>
                        <button class="seta-galeria dir" onclick="mudarImagem(1)">&#10095;</button>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <?php if (count($galeria_imagens) > 1): ?>
        <div class="miniaturas-container">
            <?php foreach ($galeria_imagens as $index => $caminho): ?>
                <?php 
                    $thumb = (strpos($caminho, 'public/') === false) ? 'public/assets/images/produtos/' . $caminho : $caminho;
                ?>
                <img src="/MAGDA-CREW/<?= $thumb ?>" class="miniatura <?= $index === 0 ? 'ativa' : '' ?>" onclick="selecionarImagem(<?= $index ?>)" data-src="/MAGDA-CREW/<?= $thumb ?>">
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="produto-info">
        <p class="categoria-badge"><?= htmlspecialchars($produto['categoria_nome'] ?? '') ?></p>
        <h1><?= htmlspecialchars($produto['nome'] ?? '') ?></h1>
        <div class="preco">R$ <?= number_format($produto['preco'] ?? 0, 2, ',', '.') ?></div>
        <p class="descricao"><?= nl2br(htmlspecialchars($produto['descricao'] ?? '')) ?></p>

        <?php
        // Lógica para descobrir qual o primeiro tamanho disponível (menor tamanho em estoque)
        $tamanho_pre_selecionado = null;
        foreach ($variantes as $variante) {
            if ($variante['quantidade_estoque'] > 0) {
                $tamanho_pre_selecionado = $variante['id'];
                break; // Para no primeiro tamanho que encontrar com estoque
            }
        }
        ?>

        <form class="form-compra" method="POST" action="adicionar-carrinho.php">
            <input type="hidden" name="variante_id" id="variante-selecionada" value="<?php echo $tamanho_pre_selecionado; ?>">

            <label>Escolha o Tamanho:</label>
            
            <div class="tamanhos-grid">
                <?php foreach ($variantes as $variante): 
                    $tem_estoque = $variante['quantidade_estoque'] > 0;
                    $is_selecionado = ($variante['id'] == $tamanho_pre_selecionado);
                    
                    // Define as classes CSS dinamicamente com base no estoque
                    $classe_status = '';
                    if (!$tem_estoque) {
                        $classe_status = 'sem-estoque';
                    } elseif ($is_selecionado) {
                        $classe_status = 'selecionado';
                    }
                ?>
                    <button type="button" 
                            class="tamanho-opcao <?php echo $classe_status; ?>" 
                            data-id="<?php echo $variante['id']; ?>"
                            <?php echo !$tem_estoque ? 'disabled' : ''; ?>>
                        <?php echo htmlspecialchars($variante['tamanho_nome']); ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <button type="submit" class="btn-comprar">Adicionar à Sacola</button>
        </form>
    </div>
</div>

<div style="padding: 15px 55px;">
    <?php include $_SERVER['DOCUMENT_ROOT']. '/MAGDA-CREW/views/components/footer.php';?>
</div>

<script>
    // LÓGICA DA GALERIA
    let imagemAtual = 0;
    const miniaturas = document.querySelectorAll('.miniatura');
    const imagemPrincipal = document.getElementById('imagem-principal');

    function mudarImagem(direcao) {
        if (miniaturas.length === 0) return;
        imagemAtual = (imagemAtual + direcao + miniaturas.length) % miniaturas.length;
        atualizarGaleria();
    }

    function selecionarImagem(index) {
        imagemAtual = index;
        atualizarGaleria();
    }

    function atualizarGaleria() {
        const novaSrc = miniaturas[imagemAtual].getAttribute('data-src');
        imagemPrincipal.src = novaSrc;
        miniaturas.forEach(min => min.classList.remove('ativa'));
        miniaturas[imagemAtual].classList.add('ativa');
    }

    // LÓGICA AJAX (CARRINHO)
    const formCompra = document.querySelector('.form-compra');
    
    if (formCompra) {
        formCompra.addEventListener('submit', function(e) {
            e.preventDefault(); 
            const btnSubmit = this.querySelector('.btn-comprar');
            const textoOriginal = btnSubmit.innerText;

            btnSubmit.innerText = 'Adicionando...';
            btnSubmit.disabled = true;
            
            fetch(this.action, {
                method: 'POST',
                body: new FormData(this)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    fetch(window.location.href + '?v=' + Math.random())
                        .then(res => res.text())
                        .then(html => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            
                            const novoConteudo = doc.querySelector('.cart-content').innerHTML;
                            const novoTotal = doc.querySelector('.cart-total').innerHTML;
                            const novaContagem = doc.querySelector('.cart-count').innerHTML;

                            document.querySelector('.cart-content').innerHTML = novoConteudo;
                            document.querySelector('.cart-total').innerHTML = novoTotal;
                            document.querySelector('.cart-count').innerHTML = novaContagem;
                            
                            btnSubmit.innerText = 'Adicionado!';
                            btnSubmit.style.backgroundColor = '#111';
                            btnSubmit.style.color = '#fff';
                            btnSubmit.style.borderColor = '#fff';
                            
                            if(typeof abrirCarrinho === 'function') abrirCarrinho();

                            setTimeout(() => {
                                btnSubmit.innerText = textoOriginal;
                                btnSubmit.style.backgroundColor = ''; 
                                btnSubmit.style.color = '';
                                btnSubmit.style.borderColor = '';
                                btnSubmit.disabled = false;
                            }, 2000);
                        });
                } else {
                    alert(data.message);
                    btnSubmit.innerText = textoOriginal;
                    btnSubmit.disabled = false;
                }
            })
            .catch(err => {
                console.error(err);
                btnSubmit.disabled = false;
                btnSubmit.innerText = textoOriginal;
            });
        });
    }

    document.querySelectorAll('.tamanho-opcao').forEach(botao => {
        botao.addEventListener('click', function() {
            // Ignora o clique se o botão não tiver estoque
            if (this.classList.contains('sem-estoque')) return;

            // Remove a seleção visual de todos os outros botões
            document.querySelectorAll('.tamanho-opcao').forEach(b => b.classList.remove('selecionado'));

            // Adiciona a cor branca ao botão clicado
            this.classList.add('selecionado');

            // Atualiza o valor do input oculto com o ID da variante correspondente
            document.getElementById('variante-selecionada').value = this.getAttribute('data-id');
        });
    });
</script>