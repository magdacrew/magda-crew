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

<div class="vitrine" id="vitrine-container">
    <?php if (!empty($produtos)): ?>
        <?php foreach ($produtos as $produto): ?>
            <div class="card-produto">
                <a href="/MAGDA-CREW/public/produtos/detalhes/<?= $produto['id'] ?>" class="link-card-produto">
                    <div class="imagem-produto">
                        <?php if (!empty($produto['caminho_imagem'])): ?>
                            <img src="/magda-crew/public/assets/images/produtos/<?= $produto['caminho_imagem'] ?>" 
                                alt="<?= htmlspecialchars($produto['nome']) ?>" 
                                style="width: 100%; height: auto; border-radius: 8px;">
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

<?php include $_SERVER['DOCUMENT_ROOT']. '/magda-crew/views/components/footer.php';?>
<script src="/MAGDA-CREW/public/assets/js/script.js"></script>

</body>
</html>