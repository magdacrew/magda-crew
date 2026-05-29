<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$base_path = "C:/xampp/htdocs/magda-crew";

require_once $base_path . '/src/Config/Database.php'; 
require_once $base_path . '/src/Models/Produto.php';

try {
    $db = Database::getConnection();
    $produtoModel = new Produto($db);

    // 1. CAPTURA DE PARÂMETROS
    $termo = isset($_GET['q']) ? trim($_GET['q']) : '';
    $tamanhosMarcados = isset($_GET['tamanho']) ? explode(',', $_GET['tamanho']) : [];
    $faixaPreco = isset($_GET['preco']) ? $_GET['preco'] : '';
    $ordem = $_GET['order'] ?? '';

    // 2. BUSCA INICIAL POR NOME
    $produtos = [];
    if (!empty($termo)) {
        $produtos = $produtoModel->buscarPorNome($termo);
    }

    // 3. LÓGICA DE FILTRAGEM (Caso existam produtos na busca)
    if (!empty($produtos)) {
        $produtos = array_filter($produtos, function($produto) use ($tamanhosMarcados, $faixaPreco, $produtoModel) {
            $passouTamanho = true;
            $passouPreco = true;

            // Filtro de Tamanho
            if (!empty($tamanhosMarcados)) {
                $passouTamanho = false;
                $filtrosUpper = array_map('strtoupper', $tamanhosMarcados);
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
            if (!empty($faixaPreco)) {
                list($min, $max) = explode('-', $faixaPreco);
                $precoProd = (float)$produto['preco'];
                if ($precoProd < (float)$min || $precoProd > (float)$max) {
                    $passouPreco = false;
                }
            }

            return $passouTamanho && $passouPreco;
        });
    }

    // 4. LÓGICA DE ORDENAÇÃO
    if (!empty($produtos)) {
        usort($produtos, function($a, $b) use ($ordem) {
            // Primeiro: Esgotados para o final
            $estoqueA = $a['total_estoque'] ?? 0;
            $estoqueB = $b['total_estoque'] ?? 0;
            $esgotadoA = ($estoqueA <= 0) ? 1 : 0;
            $esgotadoB = ($estoqueB <= 0) ? 1 : 0;
            
            if ($esgotadoA !== $esgotadoB) return $esgotadoA <=> $esgotadoB;

            // Segundo: Ordem escolhida
            return match ($ordem) {
                'price_asc' => $a['preco'] <=> $b['preco'],
                'price_desc' => $b['preco'] <=> $a['preco'],
                'name_az' => strnatcasecmp($a['nome'], $b['nome']),
                'name_za' => strnatcasecmp($b['nome'], $a['nome']),
                default => 0, 
            };
        });
    }

} catch (Exception $e) {
    error_log($e->getMessage());
}

$tituloDaPagina = "Resultados para: " . htmlspecialchars($termo);
include __DIR__ . '/../components/header.php';
?>

<link rel="stylesheet" href="/magda-crew/public/assets/css/search.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<main class="search-page">
    <div class="search-container">
        
        <h1 class="search-title">Resultados da pesquisa</h1>

        <div class="search-bar-wrapper">
            <form action="search.php" method="GET" class="search-input-group">
                <input type="text" name="q" id="internalSearch" placeholder="Buscar" value="<?= htmlspecialchars($termo) ?>">
                <i class="fas fa-times" onclick="document.getElementById('internalSearch').value=''"></i>
                <button type="submit" style="background:none;border:none;color:#888;"><i class="fas fa-search"></i></button>
            </form>
        </div>

        <div class="filtros-container">
            
            <div class="dropdown-group">
                <button class="btn-filtro-topo" id="btnFiltrar">
                    Filtrar <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 21v-7m0-4V3m8 18v-9m0-4V3m8 18v-5m0-4V3M1 14h6m2-6h6m2 8h6"></path></svg>
                </button>
                
                <div class="dropdown-menu dark-glass" id="menuFiltrar">
                    <div class="dropdown-header">Filtrar por:</div>
                    
                    <div class="dropdown-section-header" id="headerTamanho">
                        Tamanho <svg class="dropdown-arrow" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M19 12l-7 7-7-7"></path></svg>
                    </div>
                    <div class="dropdown-panel" id="panelTamanho">
                        <?php $opcoes = ['PP', 'P', 'M', 'G', 'GG', 'XGG']; 
                        foreach($opcoes as $op): ?>
                            <label class="checkbox-label">
                                <input type="checkbox" class="check-tamanho" value="<?= $op ?>" <?= in_array($op, $tamanhosMarcados) ? 'checked' : '' ?>> <?= $op ?>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <div class="dropdown-section-header" id="headerPreco">
                        Preço <svg class="dropdown-arrow" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M19 12l-7 7-7-7"></path></svg>
                    </div>
                    <div class="dropdown-panel" id="panelPreco">
                        <div style="display: flex; gap: 5px;">
                            <input type="number" id="inputPrecoMin" placeholder="Mín" style="width:50%">
                            <input type="number" id="inputPrecoMax" placeholder="Máx" style="width:50%">
                        </div>
                    </div>

                    <div class="dropdown-actions">
                        <button type="button" class="btn-aplicar">Aplicar</button>
                        <button type="button" class="btn-remover" id="btnRemoverTudo">Limpar</button>
                    </div>
                </div>
            </div>

            <div class="dropdown-group">
                <button class="btn-filtro-topo" id="btnOrdenar">
                    Ordenar por <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M19 12l-7 7-7-7"></path></svg>
                </button>
                <div class="dropdown-menu dark-glass" id="menuOrdenar">
                    <label class="radio-label"><input type="radio" name="ordem" value="price_asc" <?= $ordem == 'price_asc' ? 'checked' : '' ?>> Menor Preço</label>
                    <label class="radio-label"><input type="radio" name="ordem" value="price_desc" <?= $ordem == 'price_desc' ? 'checked' : '' ?>> Maior Preço</label>
                    <label class="radio-label"><input type="radio" name="ordem" value="name_az" <?= $ordem == 'name_az' ? 'checked' : '' ?>> Nome A-Z</label>
                </div>
            </div>

        </div>

        <div class="magda-grid">
            <?php if (empty($produtos)): ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 100px 0;">
                    <p style="color: #666;">Nenhum resultado para "<?= htmlspecialchars($termo) ?>".</p>
                </div>
            <?php else: ?>
                <?php foreach ($produtos as $produto): ?>
                    <a href="/magda-crew/public/produtos/detalhes/<?= $produto['id'] ?>" class="product-card">
                        <div class="product-img-box">
                            <?= ($produto['total_estoque'] <= 0) ? '<span class="badge-status">Esgotado</span>' : '' ?>
                            <img src="/magda-crew/<?= $produto['caminho_imagem'] ?>" alt="<?= htmlspecialchars($produto['nome']) ?>">
                        </div>
                        <div class="product-info">
                            <h3><?= htmlspecialchars($produto['nome']) ?></h3>
                            <p>R$ <?= number_format($produto['preco'], 2, ',', '.') ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Abre/Fecha Dropdowns
    const setupDropdown = (btnId, menuId) => {
        const btn = document.getElementById(btnId);
        const menu = document.getElementById(menuId);
        btn?.addEventListener('click', (e) => {
            e.stopPropagation();
            document.querySelectorAll('.dropdown-menu').forEach(m => m !== menu && m.classList.remove('show'));
            menu.classList.toggle('show');
        });
    };

    setupDropdown('btnFiltrar', 'menuFiltrar');
    setupDropdown('btnOrdenar', 'menuOrdenar');

    // Accordion dentro do filtro
    document.querySelectorAll('.dropdown-section-header').forEach(header => {
        header.addEventListener('click', () => {
            const panel = header.nextElementSibling;
            panel.classList.toggle('visible');
            header.querySelector('.dropdown-arrow').classList.toggle('rotated');
        });
    });

    // Aplicar Filtros
    document.querySelector('.btn-aplicar')?.addEventListener('click', () => {
        const url = new URL(window.location.href);
        const tamanhos = Array.from(document.querySelectorAll('.check-tamanho:checked')).map(c => c.value);
        const min = document.getElementById('inputPrecoMin').value;
        const max = document.getElementById('inputPrecoMax').value;

        if (tamanhos.length) url.searchParams.set('tamanho', tamanhos.join(','));
        else url.searchParams.delete('tamanho');

        if (min || max) url.searchParams.set('preco', `${min || 0}-${max || 1000}`);
        else url.searchParams.delete('preco');

        window.location.href = url.toString();
    });

    // Ordenação
    document.querySelectorAll('input[name="ordem"]').forEach(radio => {
        radio.addEventListener('change', () => {
            const url = new URL(window.location.href);
            url.searchParams.set('order', radio.value);
            window.location.href = url.toString();
        });
    });

    // Fechar ao clicar fora
    document.addEventListener('click', () => document.querySelectorAll('.dropdown-menu').forEach(m => m.classList.remove('show')));
});
</script>

<div style="padding: 15px 55px;">
    <?php include $_SERVER['DOCUMENT_ROOT']. '/magda-crew/views/components/footer.php';?>
</div>