<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="/magda-crew/public/assets/images/15.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tituloDaPagina) ?></title>
    <link rel="stylesheet" href="/magda-crew/public/assets/css/index.css">
    <link rel="stylesheet" href="/MAGDA-CREW/public/assets/css/header.css">
    <link rel="stylesheet" href="/MAGDA-CREW/public/assets/css/footer.css">
    <?php include $_SERVER['DOCUMENT_ROOT']. '/magda-crew/views/components/header.php';?>
</head>
<body>


<main style="padding: 15px 55px;">
  <section class="hero" id="hero-container">
  <div class="arrow left" id="prevBtn">
    ‹</div>

  <div class="hero-content active" data-bg="/magda-crew/public/assets/images/background3.png">
    <h1>FALL ’26 COLLECTION ©</h1>
    <a href="#">Compre Agora</a>
  </div>

  <div class="hero-content" data-bg="/magda-crew/public/assets/images/background2.png">
        <h1>ROMANTIC ’26 ©</h1>
    <a href="#">Compre Agora</a>
  </div>

  <div class="arrow right" id="nextBtn">›</div>
  
  <div class="dots">
    <div class="dot active"></div>
    <div class="dot"></div> </div>
</section>
</main>

<ul class="categorias">
    <?php foreach ($categorias as $cat): ?>
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
        <?php if (!empty($produtos)): ?>
            <?php foreach ($produtos as $produto): ?>
                <div class="card-produto">
                    <a href="/MAGDA-CREW/public/produtos/detalhes/<?= $produto['id'] ?>" class="link-card-produto">
                        
                        <div class="imagem-produto">
                            
                            <?php if (isset($produto['total_estoque']) && $produto['total_estoque'] <= 0): ?>
                                <div class="overlay-esgotado"></div>

                                <span class="tag-esgotado">Esgotado</span>
                            <?php endif; ?>

                            <?php if (!empty($produto['caminho_imagem'])): ?>
                                <img src="/magda-crew/<?= $produto['caminho_imagem'] ?>" 
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
    </div>
<div class="banner-section">
    <div class="banner-overlay" >
        <h1>VAMPETA’26 | T-SHIRTS </h1>
        <a href="#">Explore Agora</a>
    </div>
</div>
</main>

<button id="btnTop" class="btn-topo" aria-label="Voltar ao topo">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="12" y1="19" x2="12" y2="5"></line>
        <polyline points="5 12 12 5 19 12"></polyline>
    </svg>
</button>

<div style="padding: 15px 55px;">
    <?php include $_SERVER['DOCUMENT_ROOT']. '/magda-crew/views/components/footer.php';?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    
    // --- LÓGICA DA VITRINE (Já existente) ---
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

    // --- NOVA LÓGICA DO BOTÃO VOLTAR AO TOPO ---
    const btnTop = document.getElementById('btnTop');

    if (btnTop) {
        // Verifica a rolagem da página inteira
        window.addEventListener('scroll', () => {
            // Se desceu mais de 300 pixels, adiciona a classe "show" que faz ele aparecer
            if (window.scrollY > 300) {
                btnTop.classList.add('show');
            } else {
                // Se subiu de volta para o topo, remove a classe e ele some com fade
                btnTop.classList.remove('show');
            }
        });

        // Quando clicar no botão, sobe a página suavemente
        btnTop.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth' /* O "smooth" faz rolar macio, não dar um pulo seco */
            });
        });
    }
});
</script>

<script src="/MAGDA-CREW/public/assets/js/script.js"></script>

</body>
</html>