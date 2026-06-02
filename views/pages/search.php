<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$base_path = "C:/xampp/htdocs/MagdaCrew";

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

<link rel="stylesheet" href="/MagdaCrew/public/assets/css/Search.css">
<link rel="icon" type="image/png" href="/MagdaCrew/public/assets/images/MgdWhite.png">
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
            Filtrar 
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 21v-7m0-4V3m8 18v-9m0-4V3m8 18v-5m0-4V3M1 14h6m2-6h6m2 8h6"></path>
            </svg>
        </button>
        
        <div class="dropdown-menu dark-glass" id="menuFiltrar">
            <div class="dropdown-header">Filtrar:</div>
            
            <div class="dropdown-section-header" id="headerTamanho">
                Tamanho
                <svg class="dropdown-arrow" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                    <path d="M19 12l-7 7-7-7"></path>
                </svg>
            </div>

            <div class="dropdown-panel" id="panelTamanho">
                <?php 
                $opcoes = ['PP', 'P', 'M', 'G', 'GG', 'XGG']; 
                foreach ($opcoes as $op): 
                ?>
                    <label class="checkbox-label">
                        <input type="checkbox" class="check-tamanho" value="<?= $op ?>" <?= in_array($op, array_map('strtoupper', $tamanhosMarcados)) ? 'checked' : '' ?>>
                        <?= $op ?>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="dropdown-section-header" id="headerPreco">
                Preço
                <svg class="dropdown-arrow" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                    <path d="M19 12l-7 7-7-7"></path>
                </svg>
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
                <button type="button" class="btn-aplicar">Aplicar</button>
                <button type="button" class="btn-remover" id="btnRemoverTudo">Remover tudo</button>
            </div>
        </div>
    </div>

    <div class="dropdown-group">
        <button class="btn-filtro-topo" id="btnOrdenar">
            Ordenar por 
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 5v14M19 12l-7 7-7-7"></path>
            </svg>
        </button>
        
        <div class="dropdown-menu dark-glass" id="menuOrdenar">
            <div class="dropdown-header">
                Ordenar por <span class="close-menu" id="closeOrdenar">X</span>
            </div>
            
            <div class="dropdown-section">Preço</div>
            <label class="radio-label">
                <input type="radio" name="ordem" value="price_asc" <?= $ordem == 'price_asc' ? 'checked' : '' ?>>
                Preço, ordem crescente
            </label>

            <label class="radio-label">
                <input type="radio" name="ordem" value="price_desc" <?= $ordem == 'price_desc' ? 'checked' : '' ?>>
                Preço, ordem decrescente
            </label>
            
            <div class="dropdown-section">Título</div>
            <label class="radio-label">
                <input type="radio" name="ordem" value="name_az" <?= $ordem == 'name_az' ? 'checked' : '' ?>>
                Ordem alfabética, A-Z
            </label>

            <label class="radio-label">
                <input type="radio" name="ordem" value="name_za" <?= $ordem == 'name_za' ? 'checked' : '' ?>>
                Ordem alfabética, Z-A
            </label>
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
                    <a href="/MagdaCrew/public/produtos/detalhes/<?= $produto['id'] ?>" class="product-card">
                        <div class="product-img-box">
                            <?= ($produto['total_estoque'] <= 0) ? '<span class="badge-status">Esgotado</span>' : '' ?>
                            <img src="/MagdaCrew/<?= $produto['caminho_imagem'] ?>" alt="<?= htmlspecialchars($produto['nome']) ?>">
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
    const btnFiltrar = document.getElementById('btnFiltrar');
    const menuFiltrar = document.getElementById('menuFiltrar');
    const btnOrdenar = document.getElementById('btnOrdenar');
    const menuOrdenar = document.getElementById('menuOrdenar');
    const closeOrdenar = document.getElementById('closeOrdenar');

    const urlAtual = new URL(window.location.href);
    const precoUrl = urlAtual.searchParams.get('preco');

    if (precoUrl) {
        const [minUrl, maxUrl] = precoUrl.split('-');
        document.getElementById('inputPrecoMin').value = minUrl;
        document.getElementById('inputPrecoMax').value = maxUrl;
    }

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

    closeOrdenar?.addEventListener('click', (e) => {
        e.stopPropagation();
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

    document.querySelectorAll('.dropdown-section-header').forEach(header => {
        header.addEventListener('click', (e) => {
            e.stopPropagation();

            const panelId = header.id.replace('header', 'panel');
            const panel = document.getElementById(panelId);
            const arrow = header.querySelector('.dropdown-arrow');

            panel?.classList.toggle('visible');
            arrow?.classList.toggle('rotated');
        });
    });

    document.querySelector('.btn-aplicar')?.addEventListener('click', (e) => {
        e.preventDefault();

        const url = new URL(window.location.href);
        const tamanhos = Array.from(document.querySelectorAll('.check-tamanho:checked')).map(c => c.value);

        const min = document.getElementById('inputPrecoMin').value;
        const max = document.getElementById('inputPrecoMax').value;

        if (tamanhos.length > 0) {
            url.searchParams.set('tamanho', tamanhos.join(','));
        } else {
            url.searchParams.delete('tamanho');
        }

        if (min || max) {
            url.searchParams.set('preco', `${min || 0}-${max || 1000}`);
        } else {
            url.searchParams.delete('preco');
        }

        window.location.href = url.toString();
    });

    document.getElementById('btnRemoverTudo')?.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopImmediatePropagation();

        const url = new URL(window.location.href);

        url.searchParams.delete('tamanho');
        url.searchParams.delete('preco');

        document.querySelectorAll('.check-tamanho').forEach(cb => cb.checked = false);
        document.getElementById('inputPrecoMin').value = '';
        document.getElementById('inputPrecoMax').value = '';

        window.location.href = url.toString();
    }, true);

    document.querySelectorAll('input[name="ordem"]').forEach(radio => {
        radio.addEventListener('click', (e) => {
            const url = new URL(window.location.href);
            const ordemAtual = url.searchParams.get('order');

            if (ordemAtual === radio.value) {
                e.preventDefault();
                radio.checked = false;
                url.searchParams.delete('order');
            } else {
                url.searchParams.set('order', radio.value);
            }

            window.location.href = url.toString();
        });
    });
});
</script>
<div style="padding: 15px 55px;">
    <?php include $_SERVER['DOCUMENT_ROOT']. '/MagdaCrew/views/components/footer.php';?>
</div>