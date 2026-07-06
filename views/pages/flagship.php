<?php
$tituloDaPagina = "Flagship - Magda Crew";
include_once $_SERVER['DOCUMENT_ROOT'] . '/MagdaCrew/views/components/header.php';

function flagshipAsset($path, $fallback) {
    $root = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\');
    $absolute = $root . $path;
    return file_exists($absolute) ? $path : $fallback;
}

$hero = flagshipAsset(
    '/MagdaCrew/public/assets/images/flagship/hero.jpg',
    '/MagdaCrew/public/assets/images/background4.jpg'
);

$imagens = [
    flagshipAsset('/MagdaCrew/public/assets/images/flagship/loja1.jpg', '/MagdaCrew/public/assets/images/background.png'),
    flagshipAsset('/MagdaCrew/public/assets/images/flagship/loja2.jpg', '/MagdaCrew/public/assets/images/background2.png'),
    flagshipAsset('/MagdaCrew/public/assets/images/flagship/loja3.jpg', '/MagdaCrew/public/assets/images/background3.png'),
    flagshipAsset('/MagdaCrew/public/assets/images/flagship/loja4.jpg', '/MagdaCrew/public/assets/images/background5.png'),
];

$endereco = 'Av. Cel. Procópio Gomes, 911 - Bucarein, Joinville - SC';
$rota = 'https://maps.app.goo.gl/zSRDkUnrapQoF7HL8';
$mapa = 'https://www.google.com/maps?q=' . rawurlencode($endereco) . '&output=embed';
?>

<link rel="stylesheet" href="/MagdaCrew/public/assets/css/Flagship.css">

<main class="flagship-page">
    <section class="flagship-hero" style="background-image: url('<?= htmlspecialchars($hero) ?>');">
        <div class="hero-glow"></div>
        <div class="hero-overlay">
            <span class="eyebrow">MAGDA CREW • JOINVILLE</span>
            <h1>FLAGSHIP STORE</h1>
            <p>Um espaço feito para o cliente conhecer a marca, provar as peças e viver a experiência Magda de perto.</p>

            <div class="hero-actions">
                <a class="btn-principal" href="<?= htmlspecialchars($rota) ?>" target="_blank" rel="noopener">Ver rota</a>
                <a class="btn-secundario" href="/MagdaCrew/views/pages/shop.php">Ver coleção</a>
            </div>
        </div>
    </section>
 <br><br>

    <section class="flagship-info">
        <div class="mapa-card">
            <iframe
                title="Mapa da Magda Flagship Store"
                src="<?= htmlspecialchars($mapa) ?>"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>

        <div class="info-card">
            <span class="tag">STORE LOCATION</span>
            <h2>MAGDA FLAGSHIP STORE</h2>
            <p class="descricao">Visite nossa loja física e veja de perto as peças da coleção.</p>

            <div class="info-lista">
                <div class="info-item">
                    <strong>Endereço</strong>
                    <p><?= htmlspecialchars($endereco) ?></p>
                </div>
                <div class="info-item">
                    <strong>Horário</strong>
                    <p>Terça a sábado • 11h às 20h</p>
                </div>
                <div class="info-item">
                    <strong>CEP</strong>
                    <p>09090-720</p>
                </div>
            </div>

            <a class="btn-rota" href="<?= htmlspecialchars($rota) ?>" target="_blank" rel="noopener">Abrir no Google Maps</a>
        </div>
    </section>

    <section class="galeria-loja">
        <div class="galeria-header">
            <span class="tag">AMBIENTE</span>
            <h2>Conheça a loja</h2>

        </div>

        <div class="galeria-grid">
            <div class="thumbs" aria-label="Miniaturas da galeria">
                <?php foreach ($imagens as $index => $img): ?>
                    <button class="thumb <?= $index === 0 ? 'active' : '' ?>" data-index="<?= $index ?>" aria-label="Ver imagem <?= $index + 1 ?>">
                        <img src="<?= htmlspecialchars($img) ?>" alt="Imagem <?= $index + 1 ?> da loja">
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="imagem-principal">
                <button class="seta esquerda" id="btnAnterior" aria-label="Imagem anterior">‹</button>
                <img id="imagemGrande" src="<?= htmlspecialchars($imagens[0]) ?>" alt="Loja Magda Crew">
                <div class="imagem-legenda">
                    <span id="contadorGaleria">01 / <?= str_pad((string) count($imagens), 2, '0', STR_PAD_LEFT) ?></span>
                    <strong>Flagship Experience</strong>
                </div>
                <button class="seta direita" id="btnProximo" aria-label="Próxima imagem">›</button>
            </div>
        </div>
    </section>
</main>

<button id="btnTop" class="btn-topo" aria-label="Voltar ao topo">↑</button>

<div class="footer-flagship">
    <?php include $_SERVER['DOCUMENT_ROOT']. '/MagdaCrew/views/components/footer.php'; ?>
</div>

<script>
const imagens = <?= json_encode($imagens, JSON_UNESCAPED_SLASHES) ?>;
let imagemAtual = 0;

const imagemGrande = document.getElementById("imagemGrande");
const thumbs = document.querySelectorAll(".thumb");
const contadorGaleria = document.getElementById("contadorGaleria");

function atualizarImagem() {
    imagemGrande.classList.remove("trocar");
    void imagemGrande.offsetWidth;
    imagemGrande.classList.add("trocar");
    imagemGrande.src = imagens[imagemAtual];

    thumbs.forEach((thumb, index) => {
        thumb.classList.toggle("active", index === imagemAtual);
    });

    contadorGaleria.textContent = String(imagemAtual + 1).padStart(2, "0") + " / " + String(imagens.length).padStart(2, "0");
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
