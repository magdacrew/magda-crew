<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. CARREGA OS MODELS DO SEU PROJETO
// (Ajuste os caminhos se necessário)
require_once __DIR__ . '/../../src/Models/Categoria.php';
require_once __DIR__ . '/../../src/Models/Produto.php';

try {
    $categoriaModel = new Categoria();
    $produtoModel = new Produto();
    
    // Puxa as categorias e produtos usando as funções oficiais do seu sistema
    $categorias = $categoriaModel->buscarTodas();
    $produtos = $produtoModel->buscarTodos();

} catch (Exception $e) {
    die("Erro ao carregar os dados da loja: " . $e->getMessage());
}

// -------------------------------------------------------------
// 2. LÓGICA DE ORDENAÇÃO (PHP)
// -------------------------------------------------------------
// Esta função processa a ordenação dos produtos na tela
if (!empty($produtos)) {
    usort($produtos, function($a, $b) {
        
        // ORDENAÇÃO PRIMÁRIA: Coloca os produtos com estoque primeiro e os esgotados no final
        $esgotadoA = (isset($a['total_estoque']) && $a['total_estoque'] <= 0) ? 1 : 0;
        $esgotadoB = (isset($b['total_estoque']) && $b['total_estoque'] <= 0) ? 1 : 0;
        
        if ($esgotadoA !== $esgotadoB) {
            return $esgotadoA <=> $esgotadoB;
        }

        // ORDENAÇÃO SECUNDÁRIA: Processa o tipo de ordenação escolhido pelo usuário
        $order = $_GET['order'] ?? '';
        return match ($order) {
            'price_asc' => $a['preco'] <=> $b['preco'], // Preço, ordem crescente
            'price_desc' => $b['preco'] <=> $a['preco'], // Preço, ordem decrescente
            'name_az' => strnatcasecmp($a['nome'], $b['nome']), // Ordem alfabética, A-Z
            'name_za' => strnatcasecmp($b['nome'], $a['nome']), // Ordem alfabética, Z-A
            default => 0, // Nenhuma ordenação secundária (padrão)
        };
    });
}

// 3. CONFIGURAÇÕES DO CABEÇALHO
$tituloDaPagina = 'Todos os Itens - Magda Crew';

$cssExtra = '
    <link rel="stylesheet" href="/MAGDA-CREW/public/assets/css/footer.css">
    <link rel="stylesheet" href="/MAGDA-CREW/public/assets/css/style.css">
';

// Inclui o Header
include_once $_SERVER['DOCUMENT_ROOT'] . '/MAGDA-CREW/views/components/header.php'; 
?>

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
                        <label class="checkbox-label"><input type="checkbox"> P <span class="count">(55)</span></label>
                        <label class="checkbox-label"><input type="checkbox"> M <span class="count">(55)</span></label>
                        <label class="checkbox-label"><input type="checkbox"> G <span class="count">(55)</span></label>
                        <label class="checkbox-label"><input type="checkbox"> GG <span class="count">(55)</span></label>
                        <label class="checkbox-label"><input type="checkbox"> 3G <span class="count">(50)</span></label>
                        <label class="checkbox-label"><input type="checkbox"> 4G <span class="count">(37)</span></label>
                        <label class="checkbox-label"><input type="checkbox"> 40 <span class="count">(3)</span></label>
                        <label class="checkbox-label"><input type="checkbox"> 42 <span class="count">(3)</span></label>
                        <label class="checkbox-label"><input type="checkbox"> 44 <span class="count">(3)</span></label>
                        <label class="checkbox-label"><input type="checkbox"> 46 <span class="count">(3)</span></label>
                        <label class="checkbox-label"><input type="checkbox"> 48 <span class="count">(3)</span></label>
                        <label class="checkbox-label"><input type="checkbox"> 50 <span class="count">(3)</span></label>
                    </div>

                    <div class="dropdown-section-header" id="headerPreco">
                        Preço
                        <svg class="dropdown-arrow" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M19 12l-7 7-7-7"></path></svg>
                    </div>
                    <div class="dropdown-panel" id="panelPreco">
                        <label class="checkbox-label"><input type="checkbox"> R$ 50 - R$ 100 <span class="count">(10)</span></label>
                        <label class="checkbox-label"><input type="checkbox"> R$ 100 - R$ 200 <span class="count">(25)</span></label>
                        <label class="checkbox-label"><input type="checkbox"> Acima de R$ 200 <span class="count">(5)</span></label>
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

                    <div class="dropdown-section">Data</div>
                    <label class="radio-label"><input type="radio" name="ordem" value="date_desc"> Data, mais recente primeiro</label>
                    <label class="radio-label"><input type="radio" name="ordem" value="date_asc"> Data, mais antiga primeiro</label>
                    
                    <div class="dropdown-section">Outros</div>
                    <label class="radio-label"><input type="radio" name="ordem" value="most_sold"> Mais vendidos</label>
                    <label class="radio-label"><input type="radio" name="ordem" value="featured"> Em destaque</label>
                </div>
            </div>

        </div>
    </div>

    <div class="vitrine vitrine-grade" id="vitrine-container">
        
        <?php if (!empty($produtos)): ?>
            <?php foreach ($produtos as $produto): ?>
                <div class="card-produto">
                    <a href="/MAGDA-CREW/public/produtos/detalhes/<?= $produto['id'] ?>" class="link-card-produto">
                        
                        <div class="imagem-produto">
                            <?php if (isset($produto['total_estoque']) && $produto['total_estoque'] <= 0): ?>
                                <div class="overlay-esgotado"></div>
                                <span class="tag-esgotado">Esgotado</span>
                            <?php endif; ?>

                            <?php 
                            // Tenta encontrar a imagem mapeando os nomes mais comuns que o seu Model pode estar usando
                            $caminhoImagem = $produto['caminho_imagem'] ?? $produto['imagem'] ?? $produto['foto'] ?? $produto['imagem_url'] ?? '';
                            
                            if (!empty($caminhoImagem)): ?>
                                <img src="/MAGDA-CREW/<?= $caminhoImagem ?>" 
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
            <p style="color: white; padding: 20px;">Nenhum produto encontrado.</p>
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
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/MAGDA-CREW/views/components/footer.php'; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // -----------------------------------------
    // Lógica do botão Voltar ao Topo
    // -----------------------------------------
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
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    // -----------------------------------------
    // Lógica dos Menus Dropdown (Filtros e Ordem)
    // -----------------------------------------
    const btnFiltrar = document.getElementById('btnFiltrar');
    const menuFiltrar = document.getElementById('menuFiltrar');

    const btnOrdenar = document.getElementById('btnOrdenar');
    const menuOrdenar = document.getElementById('menuOrdenar');
    const closeOrdenar = document.getElementById('closeOrdenar');

    // Abre/fecha filtro
    btnFiltrar?.addEventListener('click', (e) => {
        e.stopPropagation();
        menuFiltrar.classList.toggle('show');
        menuOrdenar.classList.remove('show'); // fecha o outro
    });

    // Abre/fecha ordenar
    btnOrdenar?.addEventListener('click', (e) => {
        e.stopPropagation();
        menuOrdenar.classList.toggle('show');
        menuFiltrar.classList.remove('show'); // fecha o outro
    });

    // Botão X de fechar o ordenar
    closeOrdenar?.addEventListener('click', () => {
        menuOrdenar.classList.remove('show');
    });

    // Fecha ao clicar fora de qualquer menu
    document.addEventListener('click', (e) => {
        if (menuFiltrar && !menuFiltrar.contains(e.target) && !btnFiltrar.contains(e.target)) {
            menuFiltrar.classList.remove('show');
        }
        if (menuOrdenar && !menuOrdenar.contains(e.target) && !btnOrdenar.contains(e.target)) {
            menuOrdenar.classList.remove('show');
        }
    });

    // -----------------------------------------
    // Lógica para as Abas Expansíveis do Filtro (Tamanho, Preço, etc.)
    // -----------------------------------------
    const headers = document.querySelectorAll('.dropdown-section-header');

    headers.forEach(header => {
        header.addEventListener('click', () => {
            const panelId = header.id.replace('header', 'panel');
            const panel = document.getElementById(panelId);
            const arrow = header.querySelector('.dropdown-arrow');

            // Toca a classe 'visible' no painel correspondente
            panel?.classList.toggle('visible');
            
            // Tira a classe 'rotated' na seta correspondente
            arrow?.classList.toggle('rotated');
        });
    });

    // -----------------------------------------
    // Lógica de Ordenação: Atualiza a URL ao selecionar um radio
    // -----------------------------------------
    const radioOrdemList = document.querySelectorAll('input[name="ordem"]');
    
    // Adiciona o listener de clique em cada rádio
    radioOrdemList.forEach(radio => {
        radio.addEventListener('click', () => {
            // Pega o valor do radio que foi clicado
            const ordemValue = radio.value;
            
            // Redireciona para a mesma página com o parâmetro 'order' na URL
            // Ex: http://localhost/loja/pages/shop.php?order=price_asc
            window.location.href = `?order=${ordemValue}`;
        });
    });

    // Mantém o radio marcado com base na ordenação atual na URL
    const currentOrder = new URLSearchParams(window.location.search).get('order');
    if (currentOrder) {
        document.querySelector(`input[name="ordem"][value="${currentOrder}"]`).checked = true;
    }
});
</script>

<script src="/MAGDA-CREW/public/assets/js/script.js"></script>

</body>
</html>