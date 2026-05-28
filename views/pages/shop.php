<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. CARREGA OS MODELS DO SEU PROJETO
require_once __DIR__ . '/../../src/Models/Categoria.php';
require_once __DIR__ . '/../../src/Models/Produto.php';

try {
    $categoriaModel = new Categoria();
    $produtoModel = new Produto();
    
    $categorias = $categoriaModel->buscarTodas();
    $produtos = $produtoModel->buscarTodos();

} catch (Exception $e) {
    die("Erro ao carregar os dados da loja: " . $e->getMessage());
}

// -------------------------------------------------------------
// 2. LÓGICA DE FILTRAGEM (SEM MEXER NO MODEL) E PREÇO DINÂMICO
// -------------------------------------------------------------
$tamanhosMarcados = isset($_GET['tamanho']) ? explode(',', $_GET['tamanho']) : [];
$faixaPreco = isset($_GET['preco']) ? $_GET['preco'] : '';

$precoMin = 0;
$precoMax = 1000;
if (!empty($faixaPreco) && strpos($faixaPreco, '-') !== false) {
    list($precoMin, $precoMax) = explode('-', $faixaPreco);
    $precoMin = (float)$precoMin;
    $precoMax = (float)$precoMax;
}

if (!empty($produtos)) {
    $produtos = array_filter($produtos, function($produto) use ($tamanhosMarcados, $precoMin, $precoMax, $faixaPreco, $produtoModel) {
        $passouTamanho = true;
        $passouPreco = true;

        // Filtro de Tamanho
        if (!empty($tamanhosMarcados)) {
            $passouTamanho = false;
            $filtrosUpper = array_map('strtoupper', $tamanhosMarcados);

            // Busca as variantes deste produto específico no banco de dados
            $variantes = $produtoModel->buscarVariantes($produto['id']);
            
            $tamanhosDoProduto = [];
            if (!empty($variantes)) {
                foreach ($variantes as $variante) {
                    if (!empty($variante['tamanho_nome'])) {
                        $tamanhosDoProduto[] = strtoupper(trim($variante['tamanho_nome']));
                    }
                }
            }

            if (!empty(array_intersect($filtrosUpper, $tamanhosDoProduto))) {
                $passouTamanho = true;
            }
        }

        // Filtro de Preço
        if (!empty($faixaPreco) && isset($produto['preco'])) {
            $precoProduto = (float) $produto['preco'];
            if ($precoProduto < $precoMin || $precoProduto > $precoMax) {
                $passouPreco = false;
            }
        }

        return $passouTamanho && $passouPreco;
    });
}

// -------------------------------------------------------------
// 3. LÓGICA DE ORDENAÇÃO
// -------------------------------------------------------------
if (!empty($produtos)) {
    usort($produtos, function($a, $b) {
        $estoqueA = $a['total_estoque'] ?? $a['estoque'] ?? $a['qtd'] ?? $a['quantidade'] ?? 1;
        $estoqueB = $b['total_estoque'] ?? $b['estoque'] ?? $b['qtd'] ?? $b['quantidade'] ?? 1;
        
        $esgotadoA = ((int)$estoqueA <= 0) ? 1 : 0;
        $esgotadoB = ((int)$estoqueB <= 0) ? 1 : 0;
        
        if ($esgotadoA !== $esgotadoB) {
            return $esgotadoA <=> $esgotadoB;
        }

        $order = $_GET['order'] ?? '';
        return match ($order) {
            'price_asc' => $a['preco'] <=> $b['preco'],
            'price_desc' => $b['preco'] <=> $a['preco'],
            'name_az' => strnatcasecmp($a['nome'], $b['nome']),
            'name_za' => strnatcasecmp($b['nome'], $a['nome']),
            default => 0, 
        };
    });
}

// 4. CONFIGURAÇÕES DO CABEÇALHO
$tituloDaPagina = 'Todos os Itens - Magda Crew';

include_once $_SERVER['DOCUMENT_ROOT'] . '/magda-crew/views/components/header.php'; 
?>

<link rel="stylesheet" href="/magda-crew/public/assets/css/footer.css">
<link rel="stylesheet" href="/magda-crew/public/assets/css/style.css">

<main>

    <div class="shop-header-controls">
        
        <ul class="categorias">
            <?php foreach ($categorias as $cat): ?>
                <?php 
                    if (isset($cat['ativo']) && $cat['ativo'] == 0) {
                        continue; 
                    }
                ?>
                <li>
                    <button 
                        class="btn-filtro-categoria <?= ($cat['nome'] == 'Tudo') ? 'active' : '' ?>" 
                        data-id="<?= $cat['id'] ?>">
                        <?= htmlspecialchars($cat['nome']) ?>
                    </button>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="filtros-container">
            
            <div class="dropdown-group">
                <button class="btn-filtro-topo" id="btnFiltrar">
                    Filtrar 
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 21v-7m0-4V3m8 18v-9m0-4V3m8 18v-5m0-4V3M1 14h6m2-6h6m2 8h6"></path></svg>
                </button>
                
                <div class="dropdown-menu dark-glass" id="menuFiltrar">
                    <div class="dropdown-header">Filtrar:</div>
                    
                    <div class="dropdown-section-header" id="headerTamanho">
                        Tamanho
                        <svg class="dropdown-arrow" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M19 12l-7 7-7-7"></path></svg>
                    </div>
                    <div class="dropdown-panel" id="panelTamanho">
                        <label class="checkbox-label"><input type="checkbox" class="check-tamanho" value="PP" <?= in_array('PP', array_map('strtoupper', $tamanhosMarcados)) ? 'checked' : '' ?>> PP</label>
                        <label class="checkbox-label"><input type="checkbox" class="check-tamanho" value="P" <?= in_array('P', array_map('strtoupper', $tamanhosMarcados)) ? 'checked' : '' ?>> P</label>
                        <label class="checkbox-label"><input type="checkbox" class="check-tamanho" value="M" <?= in_array('M', array_map('strtoupper', $tamanhosMarcados)) ? 'checked' : '' ?>> M</label>
                        <label class="checkbox-label"><input type="checkbox" class="check-tamanho" value="G" <?= in_array('G', array_map('strtoupper', $tamanhosMarcados)) ? 'checked' : '' ?>> G</label>
                        <label class="checkbox-label"><input type="checkbox" class="check-tamanho" value="GG" <?= in_array('GG', array_map('strtoupper', $tamanhosMarcados)) ? 'checked' : '' ?>> GG</label>
                        <label class="checkbox-label"><input type="checkbox" class="check-tamanho" value="XGG" <?= in_array('XGG', array_map('strtoupper', $tamanhosMarcados)) ? 'checked' : '' ?>> XGG</label>
                    </div>

                    <div class="dropdown-section-header" id="headerPreco">
                        Preço
                        <svg class="dropdown-arrow" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M19 12l-7 7-7-7"></path></svg>
                    </div>
                    <div class="dropdown-panel" id="panelPreco">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 5px;">
                            <input type="number" id="inputPrecoMin" placeholder="Mín" min="0" max="1000" style="width: 100%; padding: 8px; border-radius: 5px; border: 1px solid rgba(255,255,255,0.2); background: rgba(0,0,0,0.5); color: white;">
                            <span style="color: white;">-</span>
                            <input type="number" id="inputPrecoMax" placeholder="Máx" min="0" max="1000" style="width: 100%; padding: 8px; border-radius: 5px; border: 1px solid rgba(255,255,255,0.2); background: rgba(0,0,0,0.5); color: white;">
                        </div>
                        <small style="color: #aaa;">Máximo: R$ 1000,00</small>
                    </div>

                    <div class="dropdown-actions">
                        <button class="btn-aplicar">Aplicar</button>
                        <button class="btn-remover">Remover tudo</button>
                    </div>
                </div>
            </div>

            <div class="dropdown-group">
                <button class="btn-filtro-topo" id="btnOrdenar">
                    Ordenar por 
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M19 12l-7 7-7-7"></path></svg>
                </button>
                
                <div class="dropdown-menu dark-glass" id="menuOrdenar">
                    <div class="dropdown-header">
                        Ordenar por <span class="close-menu" id="closeOrdenar">X</span>
                    </div>
                    
                    <div class="dropdown-section">Preço</div>
                    <label class="radio-label"><input type="radio" name="ordem" value="price_asc"> Preço, ordem crescente</label>
                    <label class="radio-label"><input type="radio" name="ordem" value="price_desc"> Preço, ordem decrescente</label>
                    
                    <div class="dropdown-section">Título</div>
                    <label class="radio-label"><input type="radio" name="ordem" value="name_az"> Ordem alfabética, A-Z</label>
                    <label class="radio-label"><input type="radio" name="ordem" value="name_za"> Ordem alfabética, Z-A</label>
                </div>
            </div>

        </div>
    </div>

    <div class="vitrine vitrine-grade" id="vitrine-container">
        
        <?php if (!empty($produtos)): ?>
            <?php foreach ($produtos as $produto): ?>
                <div class="card-produto">
                    <a href="/magda-crew/public/produtos/detalhes/<?= $produto['id'] ?>" class="link-card-produto">
                        
                        <div class="imagem-produto">
                            <?php 
                            $estoqueProd = $produto['total_estoque'] ?? $produto['estoque'] ?? $produto['qtd'] ?? $produto['quantidade'] ?? 1;
                            if ((int)$estoqueProd <= 0): 
                            ?>
                                <div class="overlay-esgotado"></div>
                                <span class="tag-esgotado">Esgotado</span>
                            <?php endif; ?>

                            <?php 
                            $caminhoImagem = $produto['caminho_imagem'] ?? $produto['imagem'] ?? $produto['foto'] ?? $produto['imagem_url'] ?? '';
                            
                            if (!empty($caminhoImagem)): ?>
                                <img src="/magda-crew/<?= $caminhoImagem ?>" 
                                     alt="<?= htmlspecialchars($produto['nome']) ?>">
                            <?php else: ?>
                                <div class="imagem-placeholder">
                                    <span>Sem Imagem</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <h3><?= htmlspecialchars($produto['nome']) ?></h3>
                        <div class="preco">R$ <?= number_format($produto['preco'], 2, ',', '.') ?></div>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color: white; padding: 20px;">Nenhum produto encontrado com os filtros selecionados.</p>
        <?php endif; ?>
    </div>

    <button id="btnTop" class="btn-topo" aria-label="Voltar ao topo">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="19" x2="12" y2="5"></line>
            <polyline points="5 12 12 5 19 12"></polyline>
        </svg>
    </button>

</main>

<div style="padding: 15px 55px;">
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/magda-crew/views/components/footer.php'; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    
    // Lógica do botão Voltar ao Topo
    const btnTop = document.getElementById('btnTop');
    if (btnTop) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) {
                btnTop.classList.add('show');
            } else {
                btnTop.classList.remove('show');
            }
        });
        btnTop.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // Lógica dos Menus Dropdown (Filtros e Ordem)
    const btnFiltrar = document.getElementById('btnFiltrar');
    const menuFiltrar = document.getElementById('menuFiltrar');
    const btnOrdenar = document.getElementById('btnOrdenar');
    const menuOrdenar = document.getElementById('menuOrdenar');
    const closeOrdenar = document.getElementById('closeOrdenar');

    btnFiltrar?.addEventListener('click', (e) => {
        e.stopPropagation();
        menuFiltrar.classList.toggle('show');
        menuOrdenar.classList.remove('show'); 
    });

    btnOrdenar?.addEventListener('click', (e) => {
        e.stopPropagation();
        menuOrdenar.classList.toggle('show');
        menuFiltrar.classList.remove('show'); 
    });

    closeOrdenar?.addEventListener('click', () => {
        menuOrdenar.classList.remove('show');
    });

    document.addEventListener('click', (e) => {
        if (menuFiltrar && !menuFiltrar.contains(e.target) && !btnFiltrar.contains(e.target)) {
            menuFiltrar.classList.remove('show');
        }
        if (menuOrdenar && !menuOrdenar.contains(e.target) && !btnOrdenar.contains(e.target)) {
            menuOrdenar.classList.remove('show');
        }
    });

    // Lógica das Abas Expansíveis do Filtro
    const headers = document.querySelectorAll('.dropdown-section-header');
    headers.forEach(header => {
        header.addEventListener('click', () => {
            const panelId = header.id.replace('header', 'panel');
            const panel = document.getElementById(panelId);
            const arrow = header.querySelector('.dropdown-arrow');

            panel?.classList.toggle('visible');
            arrow?.classList.toggle('rotated');
        });
    });

    // Lógica de Ordenação
    const radioOrdemList = document.querySelectorAll('input[name="ordem"]');
    radioOrdemList.forEach(radio => {
        radio.addEventListener('click', () => {
            const ordemValue = radio.value;
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('order', ordemValue);
            window.location.search = urlParams.toString();
        });
    });

    const currentOrder = new URLSearchParams(window.location.search).get('order');
    if (currentOrder) {
        const radioToSelect = document.querySelector(`input[name="ordem"][value="${currentOrder}"]`);
        if (radioToSelect) radioToSelect.checked = true;
    }

    // Lógica dos Botões de Filtro (Aplicar e Remover)
    const btnAplicar = document.querySelector('.btn-aplicar');
    const btnRemover = document.querySelector('.btn-remover');

    // Preenche os inputs de preço caso a página já tenha sido filtrada
    const precoUrl = new URLSearchParams(window.location.search).get('preco');
    if (precoUrl) {
        const [minUrl, maxUrl] = precoUrl.split('-');
        const inputPrecoMin = document.getElementById('inputPrecoMin');
        const inputPrecoMax = document.getElementById('inputPrecoMax');
        if (inputPrecoMin) inputPrecoMin.value = minUrl;
        if (inputPrecoMax) inputPrecoMax.value = maxUrl;
    }

    btnAplicar?.addEventListener('click', () => {
        const urlParams = new URLSearchParams(window.location.search);

        // Pega os tamanhos marcados
        const tamanhosMarcados = Array.from(document.querySelectorAll('.check-tamanho:checked')).map(cb => cb.value);
        if (tamanhosMarcados.length > 0) {
            urlParams.set('tamanho', tamanhosMarcados.join(','));
        } else {
            urlParams.delete('tamanho');
        }

        // Pega os preços digitados
        const inputPrecoMin = document.getElementById('inputPrecoMin');
        const inputPrecoMax = document.getElementById('inputPrecoMax');
        
        let minVal = inputPrecoMin && inputPrecoMin.value !== '' ? parseFloat(inputPrecoMin.value) : 0;
        let maxVal = inputPrecoMax && inputPrecoMax.value !== '' ? parseFloat(inputPrecoMax.value) : 1000;

        // Limita ao máximo de R$ 1000 e mínimo de R$ 0
        minVal = Math.max(0, minVal); 
        maxVal = Math.min(1000, maxVal); 

        // Se o usuário digitar o mínimo maior que o máximo, o código inverte automaticamente
        if (minVal > maxVal) {
            let temp = minVal;
            minVal = maxVal;
            maxVal = temp;
        }

        // Só aplica na URL se o usuário filtrou algo diferente do padrão (0 a 1000)
        if (minVal > 0 || maxVal < 1000) {
            urlParams.set('preco', `${minVal}-${maxVal}`);
        } else {
            urlParams.delete('preco');
        }

        window.location.search = urlParams.toString();
    });

    btnRemover?.addEventListener('click', () => {
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.delete('tamanho');
        urlParams.delete('preco');
        window.location.search = urlParams.toString();
    });
});

// -------------------------------------------------------------
    // RESETAR FILTROS AO TROCAR DE CATEGORIA (URL + TELA)
    // -------------------------------------------------------------
    const botoesCategoria = document.querySelectorAll('.btn-filtro-categoria');
    botoesCategoria.forEach(botao => {
        botao.addEventListener('click', () => {
            // 1. Limpa visualmente as caixinhas de tamanho
            const checkboxes = document.querySelectorAll('.check-tamanho');
            checkboxes.forEach(cb => cb.checked = false);

            // 2. Limpa visualmente os campos de preço
            const inputPrecoMin = document.getElementById('inputPrecoMin');
            const inputPrecoMax = document.getElementById('inputPrecoMax');
            if (inputPrecoMin) inputPrecoMin.value = '';
            if (inputPrecoMax) inputPrecoMax.value = '';

            // 3. Limpa a URL para que o sistema não use os filtros antigos
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.delete('tamanho');
            urlParams.delete('preco');
            
            const novaUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
            window.history.replaceState(null, '', novaUrl);
        });
    });
</script>

<script src="/magda-crew/public/assets/js/script.js"></script>

</body>
</html>