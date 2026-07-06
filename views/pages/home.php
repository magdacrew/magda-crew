<?php
if (!isset($bannersTopo) || !is_array($bannersTopo)) {
    $bannersTopo = [];
}

if (!isset($bannerBaixo) || !is_array($bannerBaixo)) {
    $bannerBaixo = null;
}

if (!function_exists('resolverLinkBanner')) {
    function resolverLinkBanner($link) {
        $link = trim((string)$link);

        if ($link === '' || $link === '#') {
            return '#';
        }

        // Mantém links externos, âncoras, telefone e e-mail funcionando normalmente.
        if (preg_match('~^(https?:)?//|^mailto:|^tel:|^#~i', $link)) {
            return $link;
        }

        $link = str_replace('\\', '/', $link);

        // Se já veio com o caminho correto do projeto, não altera.
        if (stripos($link, '/MagdaCrew/') === 0) {
            return $link;
        }

        $linkLimpo = ltrim($link, '/');

        // Se o admin colocar views/pages/shop.php ou public/index.php, completa só a pasta do projeto.
        if (stripos($linkLimpo, 'views/pages/') === 0 || stripos($linkLimpo, 'public/') === 0) {
            return '/MagdaCrew/' . $linkLimpo;
        }

        // Atalhos: agora basta colocar shop.php, flagship.php, login.php etc.
        $paginasDiretas = [
            'home.php' => '/MagdaCrew/public/index.php',
            'index.php' => '/MagdaCrew/public/index.php',
            'shop.php' => '/MagdaCrew/views/pages/shop.php',
            'flagship.php' => '/MagdaCrew/views/pages/flagship.php',
            'login.php' => '/MagdaCrew/views/pages/login.php',
            'profile.php' => '/MagdaCrew/views/pages/Profile.php',
            'orders.php' => '/MagdaCrew/views/pages/orders.php',
            'search.php' => '/MagdaCrew/views/pages/search.php',
        ];

        $chave = strtolower(basename(parse_url($linkLimpo, PHP_URL_PATH) ?: $linkLimpo));
        $query = parse_url($linkLimpo, PHP_URL_QUERY);

        if (isset($paginasDiretas[$chave])) {
            return $paginasDiretas[$chave] . ($query ? '?' . $query : '');
        }

        // Se for qualquer outro arquivo PHP existente dentro de views/pages, monta o caminho sozinho.
        $arquivoPagina = __DIR__ . '/' . basename($linkLimpo);
        if (preg_match('/\.php$/i', $linkLimpo) && file_exists($arquivoPagina)) {
            return '/MagdaCrew/views/pages/' . basename($linkLimpo) . ($query ? '?' . $query : '');
        }

        // Fallback para rotas do public: produtos/detalhes/1, home, shop, etc.
        return '/MagdaCrew/public/' . $linkLimpo;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="/MagdaCrew/public/assets/images/MgdWhite.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tituloDaPagina) ?></title>
    <link rel="stylesheet" href="/MagdaCrew/public/assets/css/index.css">
    <link rel="stylesheet" href="/MagdaCrew/public/assets/css/Header.css">
    <?php include $_SERVER['DOCUMENT_ROOT']. '/MagdaCrew/views/components/Header.php';?>
</head>
<body>

<main style="padding: 15px 55px;">
  <section class="hero" id="hero-container">
    <div class="arrow left" id="prevBtn">‹</div>

    <?php if (!empty($bannersTopo)): ?>
        <?php foreach ($bannersTopo as $index => $banner): ?>
            <div 
                class="hero-content <?= $index === 0 ? 'active' : '' ?>" 
                data-bg="<?= htmlspecialchars($banner['imagem_fundo']) ?>"
            >
                <h1><?= htmlspecialchars($banner['titulo']) ?></h1>
                <a href="<?= htmlspecialchars(resolverLinkBanner($banner['link_botao'] ?? '#')) ?>">
                    <?= htmlspecialchars($banner['texto_botao']) ?>
                </a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="arrow right" id="nextBtn">›</div>
    
    <div class="dots">
        <?php foreach ($bannersTopo as $index => $banner): ?>
            <div class="dot <?= $index === 0 ? 'active' : '' ?>"></div>
        <?php endforeach; ?>
    </div>
  </section>
</main>
        

<ul class="categorias">
    <?php foreach ($categorias as $cat): ?>
        <?php 
            // Se a categoria tiver a coluna 'ativo' e for igual a 0 (inativa), pula para a próxima!
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

<div class="vitrine-wrapper">
    
    <div class="vitrine" id="vitrine-container">
        <?php 
            if (!empty($produtos)) {
                usort($produtos, function($a, $b) {
                    $esgotadoA = (isset($a['total_estoque']) && $a['total_estoque'] <= 0) ? 1 : 0;
                    $esgotadoB = (isset($b['total_estoque']) && $b['total_estoque'] <= 0) ? 1 : 0;
                    return $esgotadoA <=> $esgotadoB;
                });
            }
        ?>
        
        <?php if (!empty($produtos)): ?>
            <?php foreach ($produtos as $produto): ?>
                <div class="card-produto">
                    <a href="/MagdaCrew/public/produtos/detalhes/<?= $produto['id'] ?>" class="link-card-produto">
                        
                        <div class="imagem-produto">
                            
                            <?php if (isset($produto['total_estoque']) && $produto['total_estoque'] <= 0): ?>
                                <div class="overlay-esgotado"></div>
                                <span class="tag-esgotado">Esgotado</span>
                            <?php endif; ?>

                            <?php if (!empty($produto['caminho_imagem'])): ?>
                                <img src="/MagdaCrew/<?= $produto['caminho_imagem'] ?>" 
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
            <p style="color: white; padding: 20px;">Nenhum produto encontrado nesta categoria.</p>
        <?php endif; ?>
    </div>

    <input class="embla__scrollbar" id="custom-scrollbar" type="range" min="0" max="100" value="0">

</div>

<main style="padding: 15px 55px;">
    <section class="banner" id="banner-section">
        <div 
            class="banner-section"
            style="background-image: url('<?= htmlspecialchars($bannerBaixo['imagem_fundo'] ?? '/MagdaCrew/public/assets/images/background.png') ?>');"
        >
            <div class="banner-overlay">
                <h1><?= htmlspecialchars($bannerBaixo['titulo'] ?? 'VAMPETA’26 | T-SHIRTS') ?></h1>
                <a href="<?= htmlspecialchars(resolverLinkBanner($bannerBaixo['link_botao'] ?? '#')) ?>">
                    <?= htmlspecialchars($bannerBaixo['texto_botao'] ?? 'Explore Agora') ?>
                </a>
            </div>
        </div>
    </section>
</main>

<button id="btnTop" class="btn-topo" aria-label="Voltar ao topo">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="12" y1="19" x2="12" y2="5"></line>
        <polyline points="5 12 12 5 19 12"></polyline>
    </svg>
</button>

<div style="padding: 15px 55px;">
    <?php include $_SERVER['DOCUMENT_ROOT']. '/MagdaCrew/views/components/footer.php';?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const vitrine = document.getElementById('vitrine-container');
    const scrollbar = document.getElementById('custom-scrollbar');

    if (vitrine && scrollbar) {
        vitrine.addEventListener('scroll', () => {
            const maxScrollLeft = vitrine.scrollWidth - vitrine.clientWidth;
            if (maxScrollLeft > 0) {
                const scrollPercentage = (vitrine.scrollLeft / maxScrollLeft) * 100;
                scrollbar.value = scrollPercentage;
            }
        });

        scrollbar.addEventListener('input', () => {
            const maxScrollLeft = vitrine.scrollWidth - vitrine.clientWidth;
            const scrollPos = (scrollbar.value / 100) * maxScrollLeft;
            vitrine.scrollLeft = scrollPos;
        });
    }

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
});
</script>

<script src="/MagdaCrew/public/assets/js/script.js"></script>

</body>
</html>