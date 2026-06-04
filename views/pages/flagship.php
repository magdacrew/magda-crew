<?php
$tituloDaPagina = "Flagship - Magda Crew";
include_once $_SERVER['DOCUMENT_ROOT'] . '/MagdaCrew/views/components/Header.php';

$imagens = [
    '/MagdaCrew/public/assets/images/flagship/loja1.jpg',
    '/MagdaCrew/public/assets/images/flagship/loja2.jpg',
    '/MagdaCrew/public/assets/images/flagship/loja3.jpg',
    '/MagdaCrew/public/assets/images/flagship/loja4.jpg',
];

$hero = '/MagdaCrew/public/assets/images/flagship/hero.jpg';
?>

<link rel="stylesheet" href="/MagdaCrew/public/assets/css/Flagship.css">

<main class="flagship-page">

    <section class="flagship-hero">
        <img src="<?= $hero ?>" alt="Flagship Store" onerror="this.style.display='none'; this.parentElement.classList.add('sem-imagem');">
        <div class="hero-overlay">
            <span>MAGDA CREW</span>
            <h1>FLAGSHIP STORE</h1>
            <p>Conheça nossa loja física em Joinville - SC.</p>
        </div>
    </section>

    <section class="flagship-info">
        <div class="mapa">
            <iframe 
                src="https://maps.app.goo.gl/CfN4uGKEB7HLukYP8"
                loading="lazy">
            </iframe>
        </div>

        <div class="info-texto">
            <span class="tag">STORE LOCATION</span>
            <h2>MAGDA FLAGSHIP STORE</h2>

            <div class="info-lista">
                <p>Rua Av. Cel Procópio Gomes, 911 - Bucarein</p>
                <p>Joinville - Santa Catarina </p>
                <p>09090-720</p>
                <p>Terça a Sábado - 11h às 20h</p>
            </div>

            <a class="btn-rota" href="https://maps.app.goo.gl/zSRDkUnrapQoF7HL8" target="_blank">
                Ver rota
            </a>
        </div>
    </section>

    <section class="galeria-loja">
        <div class="thumbs">
            <?php foreach ($imagens as $index => $img): ?>
                <button class="thumb <?= $index === 0 ? 'active' : '' ?>" data-index="<?= $index ?>">
                    <img src="<?= $img ?>" alt="Imagem <?= $index + 1 ?>" onerror="this.style.display='none';">
                </button>
            <?php endforeach; ?>
        </div>

        <div class="imagem-principal">
            <button class="seta esquerda" id="btnAnterior">‹</button>

            <img 
                id="imagemGrande" 
                src="<?= $imagens[0] ?>" 
                alt="Loja Magda Crew"
                onerror="this.style.display='none'; this.parentElement.classList.add('sem-imagem');"
            >

            <button class="seta direita" id="btnProximo">›</button>
        </div>
    </section>

</main>

<button id="btnTop" class="btn-topo" aria-label="Voltar ao topo">↑</button>

<div style="padding: 15px 55px;">
    <?php include $_SERVER['DOCUMENT_ROOT']. '/MagdaCrew/views/components/footer.php'; ?>
</div>

<script>
const imagens = <?= json_encode($imagens) ?>;
let imagemAtual = 0;

const imagemGrande = document.getElementById("imagemGrande");
const thumbs = document.querySelectorAll(".thumb");
const imagemBox = document.querySelector(".imagem-principal");

function atualizarImagem() {
    imagemBox.classList.remove("sem-imagem");
    imagemGrande.style.display = "block";
    imagemGrande.src = imagens[imagemAtual];

    thumbs.forEach((thumb, index) => {
        thumb.classList.toggle("active", index === imagemAtual);
    });
}

document.getElementById("btnProximo").addEventListener("click", () => {
    imagemAtual = (imagemAtual + 1) % imagens.length;
    atualizarImagem();
});

document.getElementById("btnAnterior").addEventListener("click", () => {
    imagemAtual = (imagemAtual - 1 + imagens.length) % imagens.length;
    atualizarImagem();
});

thumbs.forEach((thumb) => {
    thumb.addEventListener("click", () => {
        imagemAtual = Number(thumb.dataset.index);
        atualizarImagem();
    });
});

const btnTop = document.getElementById("btnTop");

window.addEventListener("scroll", () => {
    btnTop.classList.toggle("show", window.scrollY > 300);
});

btnTop.addEventListener("click", () => {
    window.scrollTo({ top: 0, behavior: "smooth" });
});
</script>

</body>
</html>