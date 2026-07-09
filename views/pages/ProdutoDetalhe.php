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
require_once __DIR__ . '/../components/Header.php'; 
?>

<link rel="stylesheet" href="/MagdaCrew/public/assets/css/ProdutoDetalhe.css">
<link rel="stylesheet" href="/MagdaCrew/public/assets/css/Footer.css">

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
                    <img id="imagem-principal" src="/MagdaCrew/<?= $src_imagem ?>" alt="<?= htmlspecialchars($produto['nome'] ?? '') ?>">
                    
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
                <img src="/MagdaCrew/<?= $thumb ?>" class="miniatura <?= $index === 0 ? 'ativa' : '' ?>" onclick="selecionarImagem(<?= $index ?>)" data-src="/MagdaCrew/<?= $thumb ?>">
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="produto-info">
        <p class="categoria-badge"><?= htmlspecialchars($produto['categoria_nome'] ?? '') ?></p>
        <h1><?= htmlspecialchars($produto['nome'] ?? '') ?></h1>
        <div class="preco">R$ <?= number_format($produto['preco'] ?? 0, 2, ',', '.') ?></div>
        <div class="descricao-area" id="descricao-area">
            <p class="descricao" id="descricao-produto"><?= nl2br(htmlspecialchars($produto['descricao'] ?? '')) ?></p>
            <button type="button" class="btn-descricao" id="btn-descricao" aria-expanded="false">Mostrar mais</button>
        </div>

        <form class="form-compra" method="POST" action="/MagdaCrew/src/Controllers/adicionar.php">
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

            <?php if ($variante_pre_selecionada === null): ?>
            <button type="button" class="btn-comprar btn-esgotado" disabled>ESGOTADO</button>
        <?php else: ?>
            <button type="submit" class="btn-comprar">ADICIONAR À SACOLA</button>
        <?php endif; ?>
        </form>
    </div>
</div>

<?php if (!empty($produtos_relacionados)): ?>
<div class="vitrine-wrapper">
    
    <div class="vitrine" id="vitrine-container">
        <?php foreach ($produtos_relacionados as $rel_produto): ?>
            <div class="card-produto">
                <a href="/MagdaCrew/public/produtos/detalhes/<?= $rel_produto['id'] ?>" class="link-card-produto">
                    <div class="imagem-produto-vitrine">
                        <?php if (isset($rel_produto['total_estoque']) && $rel_produto['total_estoque'] <= 0): ?>
                            <div class="overlay-esgotado"></div>
                            <span class="tag-esgotado">Esgotado</span>
                        <?php endif; ?>

                        <?php if (!empty($rel_produto['caminho_imagem'])): ?>
                            <img src="/MagdaCrew/<?= $rel_produto['caminho_imagem'] ?>" 
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
    <?php include $_SERVER['DOCUMENT_ROOT']. '/MagdaCrew/views/components/footer.php';?>
</div>

<script>
    // ----------------------------------------------------
    // 1. LÓGICA DA GALERIA DO PRODUTO
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
        if (imagemPrincipal && novaSrc) {
            imagemPrincipal.src = novaSrc;
            miniaturas.forEach(min => min.classList.remove('ativa'));
            miniaturas[imagemAtual].classList.add('ativa');
        }
    }

    // ----------------------------------------------------
    // 2. LÓGICA MOSTRAR MAIS / MENOS DA DESCRIÇÃO
    // ----------------------------------------------------
    const descricaoProduto = document.getElementById('descricao-produto');
    const btnDescricao = document.getElementById('btn-descricao');
    const imagemBoxDescricao = document.querySelector('.imagem-placeholder-detalhe');
    const produtoInfoDescricao = document.querySelector('.produto-info');
    let resizeDescricaoTimer;

    function configurarDescricaoProduto() {
        if (!descricaoProduto || !btnDescricao || !imagemBoxDescricao || !produtoInfoDescricao) return;

        // Em telas pequenas a imagem fica acima do texto, então não precisa limitar pela altura dela.
        if (window.innerWidth <= 900) {
            descricaoProduto.classList.remove('descricao-limitada', 'descricao-aberta');
            descricaoProduto.style.maxHeight = '';
            btnDescricao.classList.remove('visivel');
            btnDescricao.setAttribute('aria-expanded', 'false');
            btnDescricao.innerText = 'Mostrar mais';
            return;
        }

        const estavaAberta = btnDescricao.getAttribute('aria-expanded') === 'true';

        descricaoProduto.classList.remove('descricao-limitada', 'descricao-aberta');
        descricaoProduto.style.maxHeight = '';

        const alturaImagem = imagemBoxDescricao.getBoundingClientRect().height;
        const topoInfo = produtoInfoDescricao.getBoundingClientRect().top;
        const topoDescricao = descricaoProduto.getBoundingClientRect().top;
        const alturaDisponivel = Math.max(160, alturaImagem - (topoDescricao - topoInfo) - 18);
        const precisaCortar = descricaoProduto.scrollHeight > alturaDisponivel + 6;

        if (!precisaCortar) {
            btnDescricao.classList.remove('visivel');
            btnDescricao.setAttribute('aria-expanded', 'false');
            btnDescricao.innerText = 'Mostrar mais';
            return;
        }

        btnDescricao.classList.add('visivel');

        if (estavaAberta) {
            descricaoProduto.classList.add('descricao-aberta');
            descricaoProduto.style.maxHeight = descricaoProduto.scrollHeight + 'px';
            btnDescricao.innerText = 'Mostrar menos';
        } else {
            descricaoProduto.classList.add('descricao-limitada');
            descricaoProduto.style.maxHeight = alturaDisponivel + 'px';
            btnDescricao.innerText = 'Mostrar mais';
        }
    }

    if (descricaoProduto && btnDescricao) {
        btnDescricao.addEventListener('click', () => {
            const estaAberta = btnDescricao.getAttribute('aria-expanded') === 'true';

            if (estaAberta) {
                btnDescricao.setAttribute('aria-expanded', 'false');
                configurarDescricaoProduto();
            } else {
                descricaoProduto.classList.remove('descricao-limitada');
                descricaoProduto.classList.add('descricao-aberta');
                descricaoProduto.style.maxHeight = descricaoProduto.scrollHeight + 'px';
                btnDescricao.setAttribute('aria-expanded', 'true');
                btnDescricao.innerText = 'Mostrar menos';
            }
        });

        window.addEventListener('load', configurarDescricaoProduto);
        window.addEventListener('resize', () => {
            clearTimeout(resizeDescricaoTimer);
            resizeDescricaoTimer = setTimeout(configurarDescricaoProduto, 150);
        });

        if (imagemPrincipal) {
            imagemPrincipal.addEventListener('load', configurarDescricaoProduto);
        }

        configurarDescricaoProduto();
    }

    // ----------------------------------------------------
    // 3. LÓGICA DE SELEÇÃO DE TAMANHO
    // ----------------------------------------------------
    document.querySelectorAll('.tamanho-opcao').forEach(botao => {
        botao.addEventListener('click', function() {
            if (this.classList.contains('sem-estoque') || this.disabled) return;
            
            document.querySelectorAll('.tamanho-opcao').forEach(b => b.classList.remove('selecionado'));
            this.classList.add('selecionado');
            
            const inputVariante = document.getElementById('variante-selecionada');
            if (inputVariante) {
                inputVariante.value = this.getAttribute('data-id');
            }
        });
    });

    // ----------------------------------------------------
    // 4. LÓGICA AJAX (CARRINHO) COM FAILSAFE (+1)
    // ----------------------------------------------------
    const formCompra = document.querySelector('.form-compra');
    
    if (formCompra) {
        formCompra.addEventListener('submit', function(e) {
            e.preventDefault(); 
            const btnSubmit = this.querySelector('.btn-comprar');
            const textoOriginal = btnSubmit.innerText;

            btnSubmit.innerText = 'ADICIONANDO...';
            btnSubmit.disabled = true;
            
            fetch(this.action, {
                method: 'POST',
                body: new FormData(this),
                credentials: 'same-origin'
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    fetch(window.location.href.split('?')[0] + '?t=' + new Date().getTime(), { method: 'GET' })
                        .then(res => res.text())
                        .then(html => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            
                            // A. Atualiza miolo e rodapé do carrinho
                            const novoCartContent = doc.querySelector('.cart-content');
                            if (novoCartContent) document.querySelector('.cart-content').innerHTML = novoCartContent.innerHTML;
                            
                            const novoFooter = doc.querySelector('.cart-footer');
                            if (novoFooter) document.querySelector('.cart-footer').innerHTML = novoFooter.innerHTML;

                            // B. ATUALIZA A BOLINHA DE DENTRO DO CARRINHO (Com Trava de Atraso)
                            const countAtual = document.querySelector('.cart-count');
                            const novoCount = doc.querySelector('.cart-count');
                            if (countAtual) {
                                let valAtual = parseInt(countAtual.innerText.replace(/\D/g, '')) || 0;
                                let valNovo = novoCount ? (parseInt(novoCount.innerText.replace(/\D/g, '')) || 0) : 0;
                                
                                if (valNovo <= valAtual) valNovo = valAtual + 1;
                                
                                // Substitui apenas o número mantendo parênteses ou formatação
                                countAtual.innerText = countAtual.innerText.replace(/\d+/, valNovo);
                            }
                            
                            // C. ATUALIZA A BOLINHA DA NAVBAR (Com Trava de Atraso)
                            const badgeAtual = document.querySelector('.sacola-badge');
                            const novoBadge = doc.querySelector('.sacola-badge');
                            let badgeValAtual = badgeAtual ? parseInt(badgeAtual.innerText) || 0 : 0;
                            let badgeValNovo = novoBadge ? parseInt(novoBadge.innerText) || 0 : 0;
                            
                            if (badgeValNovo <= badgeValAtual) badgeValNovo = badgeValAtual + 1;

                            if (badgeAtual) {
                                badgeAtual.innerText = badgeValNovo;
                            } else {
                                const sacolaIcon = document.querySelector('a[onclick*="abrirCarrinho"]');
                                if (sacolaIcon) {
                                    sacolaIcon.insertAdjacentHTML('beforeend', `<span class="sacola-badge">${badgeValNovo}</span>`);
                                }
                            }
                            
                            // D. Corrige o botão FINALIZAR COMPRA
                            const btnFinalizar = document.querySelector('.cart-footer .btn-finalizar');
                            if (btnFinalizar) {
                                btnFinalizar.innerText = 'FINALIZAR COMPRA';
                                btnFinalizar.href = '/MagdaCrew/views/pages/checkout.php';
                            }
                            
                            // E. Finaliza a ação
                            btnSubmit.innerText = 'ADICIONADO!';
                            if(typeof abrirCarrinho === 'function') abrirCarrinho();

                            setTimeout(() => {
                                btnSubmit.innerText = textoOriginal;
                                btnSubmit.disabled = false;
                            }, 2000);
                        });
                } else {
                    alert(data.message || "Erro ao adicionar item.");
                    btnSubmit.innerText = textoOriginal;
                    btnSubmit.disabled = false;
                }
            })
            .catch(err => {
                console.error("Erro na requisição:", err);
                btnSubmit.disabled = false;
                btnSubmit.innerText = textoOriginal;
            });
        });
    }

    // ----------------------------------------------------
    // 5. LÓGICA DA VITRINE (PRODUTOS RELACIONADOS)
    // ----------------------------------------------------
    const vitrine = document.getElementById('vitrine-container');
    const scrollbar = document.getElementById('custom-scrollbar');

    if (vitrine && scrollbar) {
        vitrine.addEventListener('scroll', () => {
            const maxScrollLeft = vitrine.scrollWidth - vitrine.clientWidth;
            if (maxScrollLeft > 0) scrollbar.value = (vitrine.scrollLeft / maxScrollLeft) * 100;
        });

        scrollbar.addEventListener('input', () => {
            vitrine.scrollLeft = (scrollbar.value / 100) * (vitrine.scrollWidth - vitrine.clientWidth);
        });
    }
</script>