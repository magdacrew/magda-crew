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

// 3. BUSCA TODOS OS TAMANHOS DO SISTEMA E CALCULA O ESTOQUE DINAMICAMENTE
if (isset($produto['id'])) {
    $stmt_vars = $pdo->prepare("
        SELECT 
            t.id AS tamanho_id,
            t.nome AS tamanho_nome,
            COALESCE(pv.quantidade_estoque, 0) AS quantidade_estoque,
            COALESCE(pv.id, 0) AS variante_id
        FROM tamanhos t
        LEFT JOIN produto_variantes pv ON t.id = pv.tamanho_id AND pv.produto_id = ?
        ORDER BY t.id ASC
    ");
    $stmt_vars->execute([$produto['id']]);
    $variantes = $stmt_vars->fetchAll(PDO::FETCH_ASSOC);
} else {
    $variantes = [];
}

// 4. DESCOBRIR O ID DA VARIANTE COM ESTOQUE PARA PRÉ-SELECIONAR
$variante_pre_selecionada = null;
foreach ($variantes as $variante) {
    if ($variante['quantidade_estoque'] > 0) {
        $variante_pre_selecionada = $variante['variante_id'];
        break; 
    }
}

// 5. BUSCAR PRODUTOS RELACIONADOS (MESMA CATEGORIA OU TODOS SE NÃO HOUVER)
$produtos_relacionados = [];

if (isset($produto['id'])) {
    $categoria_id = $produto['categoria_id'] ?? null;
    
    // TENTATIVA 1: Busca produtos da mesma categoria
    if ($categoria_id) {
        $stmt_relacionados = $pdo->prepare("
            SELECT p.*, 
                   COALESCE((SELECT SUM(quantidade_estoque) FROM produto_variantes WHERE produto_id = p.id), 0) AS total_estoque,
                   (SELECT caminho_imagem FROM produto_imagens WHERE produto_id = p.id AND is_principal = 1 LIMIT 1) AS caminho_imagem
            FROM produtos p
            WHERE p.categoria_id = ? AND p.id != ?
            LIMIT 10
        ");
        $stmt_relacionados->execute([$categoria_id, $produto['id']]);
        $produtos_relacionados = $stmt_relacionados->fetchAll(PDO::FETCH_ASSOC);
    }

    // TENTATIVA 2 (PLANO B): Se não achou nenhum produto na mesma categoria, busca de TODAS as categorias
    if (empty($produtos_relacionados)) {
        $stmt_todos = $pdo->prepare("
            SELECT p.*, 
                   COALESCE((SELECT SUM(quantidade_estoque) FROM produto_variantes WHERE produto_id = p.id), 0) AS total_estoque,
                   (SELECT caminho_imagem FROM produto_imagens WHERE produto_id = p.id AND is_principal = 1 LIMIT 1) AS caminho_imagem
            FROM produtos p
            WHERE p.id != ?
            LIMIT 10
        ");
        // Executa passando apenas o ID do produto atual para excluí-lo da lista
        $stmt_todos->execute([$produto['id']]);
        $produtos_relacionados = $stmt_todos->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // ORDENAÇÃO: Coloca os produtos com estoque primeiro e os esgotados no final
    if (!empty($produtos_relacionados)) {
        usort($produtos_relacionados, function($a, $b) {
            $esgotadoA = (int)$a['total_estoque'] <= 0 ? 1 : 0;
            $esgotadoB = (int)$b['total_estoque'] <= 0 ? 1 : 0;
            return $esgotadoA <=> $esgotadoB;
        });
    }
}

// 6. CARREGA O HEADER
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
                    <img id="imagem-principal" src="/MAGDA-CREW/<?= $src_imagem ?>" alt="<?= htmlspecialchars($produto['nome'] ?? '') ?>">
                    
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

        <form class="form-compra" method="POST" action="/MAGDA-CREW/src/Controllers/adicionar.php">
            <input type="hidden" name="variante_id" id="variante-selecionada" value="<?php echo $variante_pre_selecionada; ?>">

            <label>Escolha o Tamanho:</label>
            <div class="tamanhos-grid">
                <?php foreach ($variantes as $variante): 
                    $tem_estoque = $variante['quantidade_estoque'] > 0;
                    $is_selecionado = ($tem_estoque && $variante['variante_id'] == $variante_pre_selecionada);
                    
                    $classe_status = '';
                    if (!$tem_estoque) {
                        $classe_status = 'sem-estoque';
                    } elseif ($is_selecionado) {
                        $classe_status = 'selecionado';
                    }
                ?>
                    <button type="button" 
                            class="tamanho-opcao <?php echo $classe_status; ?>" 
                            data-id="<?php echo $variante['variante_id']; ?>"
                            <?php echo !$tem_estoque ? 'disabled' : ''; ?>>
                        <?php echo htmlspecialchars($variante['tamanho_nome']); ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <button type="submit" class="btn-comprar">ADICIONAR À SACOLA</button>
        </form>
    </div>
</div>

<?php if (!empty($produtos_relacionados)): ?>
<div class="vitrine-wrapper">
    
    <div class="vitrine" id="vitrine-container">
        <?php foreach ($produtos_relacionados as $rel_produto): ?>
            <div class="card-produto">
                <a href="/MAGDA-CREW/public/produtos/detalhes/<?= $rel_produto['id'] ?>" class="link-card-produto">
                    <div class="imagem-produto-vitrine">
                        <?php if (isset($rel_produto['total_estoque']) && $rel_produto['total_estoque'] <= 0): ?>
                            <div class="overlay-esgotado"></div>
                            <span class="tag-esgotado">Esgotado</span>
                        <?php endif; ?>

                        <?php if (!empty($rel_produto['caminho_imagem'])): ?>
                            <img src="/magda-crew/<?= $rel_produto['caminho_imagem'] ?>" 
                                 alt="<?= htmlspecialchars($rel_produto['nome']) ?>">
                        <?php else: ?>
                            <div class="imagem-placeholder">
                                <span>Sem Imagem</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <h3><?= htmlspecialchars($rel_produto['nome']) ?></h3>
                    <div class="preco">R$ <?= number_format($rel_produto['preco'], 2, ',', '.') ?></div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>

    <input class="embla__scrollbar" id="custom-scrollbar" type="range" min="0" max="100" value="0">
</div>
<?php endif; ?>

<div style="padding: 15px 55px;">
    <?php include $_SERVER['DOCUMENT_ROOT']. '/magda-crew/views/components/footer.php';?>
</div>

<script>
    // ----------------------------------------------------
    // LÓGICA DA GALERIA DO PRODUTO
    // ----------------------------------------------------
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

    // ----------------------------------------------------
    // LÓGICA AJAX (CARRINHO) E SELEÇÃO DE TAMANHOS
    // ----------------------------------------------------
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
                body: new FormData(this),
                credentials: 'same-origin' // <-- ADICIONADO PARA ENVIAR O COOKIE DE SESSÃO
            })
            // Leitura segura da resposta para capturar erros do PHP
            .then(async res => {
                const text = await res.text();
                try {
                    return JSON.parse(text);
                } catch (e) {
                    throw new Error("Erro do PHP retornado: " + text);
                }
            })
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
                alert("Ocorreu um erro ao adicionar. Veja o console (F12) para detalhes.");
                btnSubmit.disabled = false;
                btnSubmit.innerText = textoOriginal;
            });
        });
    }

    document.querySelectorAll('.tamanho-opcao').forEach(botao => {
        botao.addEventListener('click', function() {
            if (this.classList.contains('sem-estoque') || this.disabled) return;
            document.querySelectorAll('.tamanho-opcao').forEach(b => b.classList.remove('selecionado'));
            this.classList.add('selecionado');
            document.getElementById('variante-selecionada').value = this.getAttribute('data-id');
        });
    });

    // ----------------------------------------------------
    // LÓGICA DA VITRINE SCROLLBAR (PRODUTOS RELACIONADOS)
    // ----------------------------------------------------
    const vitrine = document.getElementById('vitrine-container');
    const scrollbar = document.getElementById('custom-scrollbar');

    if (vitrine && scrollbar) {
        // Atualiza a barrinha quando o cliente desliza com o dedo/mouse
        vitrine.addEventListener('scroll', () => {
            const maxScrollLeft = vitrine.scrollWidth - vitrine.clientWidth;
            if (maxScrollLeft > 0) {
                const scrollPercentage = (vitrine.scrollLeft / maxScrollLeft) * 100;
                scrollbar.value = scrollPercentage;
            }
        });

        // Atualiza os produtos quando o cliente arrasta a barrinha
        scrollbar.addEventListener('input', () => {
            const maxScrollLeft = vitrine.scrollWidth - vitrine.clientWidth;
            const scrollPos = (scrollbar.value / 100) * maxScrollLeft;
            vitrine.scrollLeft = scrollPos;
        });
    }
</script>