document.addEventListener("DOMContentLoaded", function() {
    const btnBackToTop = document.getElementById("backToTop");

    // Função para voltar ao topo suavemente
    btnBackToTop.addEventListener("click", function() {
        window.scrollTo({
            top: 0,
            behavior: "smooth"
        });
    });

    // Opcional: Esconder o botão quando já estiver no topo e mostrar ao rolar
    window.addEventListener("scroll", function() {
        if (window.pageYOffset > 300) {
            btnBackToTop.style.opacity = "1";
            btnBackToTop.style.pointerEvents = "auto";
        } else {
            btnBackToTop.style.opacity = "0.5"; // Fica semi-transparente no topo
        }
    });
});
// Seleção de elementos
const hero = document.getElementById('hero-container');
const slides = document.querySelectorAll('.hero-content');
const dots = document.querySelectorAll('.dot');
const prevBtn = document.getElementById('prevBtn');
const nextBtn = document.getElementById('nextBtn');

let currentSlide = 0;
let autoPlayTimer; // Variável para guardar o nosso cronômetro

function updateSlider(index) {
    // 1. Reseta classes de todos os slides e bolinhas
    slides.forEach(s => s.classList.remove('active'));
    dots.forEach(d => d.classList.remove('active'));

    // 2. Lógica para o carrossel ser infinito (loop)
    if (index >= slides.length) currentSlide = 0;
    else if (index < 0) currentSlide = slides.length - 1;
    else currentSlide = index;

    // 3. Ativa o slide atual e a bolinha correspondente
    slides[currentSlide].classList.add('active');
    dots[currentSlide].classList.add('active');

    // 4. Pega o caminho da imagem no 'data-bg' e aplica no fundo
    const newBg = slides[currentSlide].getAttribute('data-bg');
    hero.style.backgroundImage = `url('${newBg}')`;
}

// --- NOVO: FUNÇÕES DE AUTOPLAY ---

function startAutoPlay() {
    // Executa a troca de slide a cada 5000 milissegundos (5 segundos)
    autoPlayTimer = setInterval(() => {
        updateSlider(currentSlide + 1);
    }, 5000); 
}

function resetAutoPlay() {
    // Cancela o cronômetro atual e começa um novo do zero
    clearInterval(autoPlayTimer);
    startAutoPlay();
}

// ----------------------------------

// Escuta os cliques nas setas (agora com reset do tempo)
nextBtn.addEventListener('click', () => {
    updateSlider(currentSlide + 1);
    resetAutoPlay();
});

prevBtn.addEventListener('click', () => {
    updateSlider(currentSlide - 1);
    resetAutoPlay();
});

// Escuta os cliques nas bolinhas (agora com reset do tempo)
dots.forEach((dot, i) => {
    dot.addEventListener('click', () => {
        updateSlider(i);
        resetAutoPlay();
    });
});

// Inicializa a primeira imagem e dá o play automático!
updateSlider(0);
startAutoPlay();