document.addEventListener("DOMContentLoaded", function() {

    // ==========================================
    // 1. FILTRO DE CATEGORIAS (AJAX)
    // ==========================================
    const botoesFiltro = document.querySelectorAll('.btn-filtro-categoria');
    const containerVitrine = document.getElementById('vitrine-container');

    botoesFiltro.forEach(botao => {
        botao.addEventListener('click', function() {
            // Controle visual dos botões (Fundo branco no ativo)
            botoesFiltro.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const categoriaId = this.getAttribute('data-id');
            if (!containerVitrine) return;

            containerVitrine.style.opacity = '0.5';

            // URL para o filtro no PHP
            const url = `/MAGDA-CREW/public/produtos/categoria/${categoriaId}`;

            fetch(url)
                .then(response => {
                    if (!response.ok) throw new Error('Erro na requisição');
                    return response.text();
                })
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    // Busca a div .vitrine dentro do HTML que o servidor retornou
                    const novaVitrine = doc.querySelector('.vitrine');

                    if (novaVitrine) {
                        // Injeta o conteúdo filtrado na vitrine da página atual
                        containerVitrine.innerHTML = novaVitrine.innerHTML;
                    }
                    containerVitrine.style.opacity = '1';
                })
                .catch(error => {
                    console.error('Erro ao filtrar:', error);
                    containerVitrine.style.opacity = '1';
                });
        });
    });


    // ==========================================
    // 2. BOTÃO VOLTAR AO TOPO
    // ==========================================
    const btnBackToTop = document.getElementById("backToTop");
    if (btnBackToTop) {
        btnBackToTop.addEventListener("click", () => {
            window.scrollTo({ top: 0, behavior: "smooth" });
        });

        window.addEventListener("scroll", () => {
            if (window.pageYOffset > 300) {
                btnBackToTop.style.opacity = "1";
                btnBackToTop.style.pointerEvents = "auto";
            } else {
                btnBackToTop.style.opacity = "0.5";
            }
        });
    }


    // ==========================================
    // 3. CARROSSEL HERO
    // ==========================================
    const hero = document.getElementById('hero-container');
    const slides = document.querySelectorAll('.hero-content');
    const dots = document.querySelectorAll('.dot');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');

    if (hero && slides.length > 0) {
        let currentSlide = 0;
        let autoPlayTimer;

        function updateSlider(index) {
            slides.forEach(s => s.classList.remove('active'));
            dots.forEach(d => d.classList.remove('active'));

            if (index >= slides.length) currentSlide = 0;
            else if (index < 0) currentSlide = slides.length - 1;
            else currentSlide = index;

            slides[currentSlide].classList.add('active');
            if (dots[currentSlide]) dots[currentSlide].classList.add('active');

            const newBg = slides[currentSlide].getAttribute('data-bg');
            hero.style.backgroundImage = `url('${newBg}')`;
        }

        function startAutoPlay() {
            autoPlayTimer = setInterval(() => { updateSlider(currentSlide + 1); }, 5000); 
        }

        function resetAutoPlay() {
            clearInterval(autoPlayTimer);
            startAutoPlay();
        }

        if (nextBtn) nextBtn.addEventListener('click', () => { updateSlider(currentSlide + 1); resetAutoPlay(); });
        if (prevBtn) prevBtn.addEventListener('click', () => { updateSlider(currentSlide - 1); resetAutoPlay(); });

        dots.forEach((dot, i) => {
            dot.addEventListener('click', () => { updateSlider(i); resetAutoPlay(); });
        });

        updateSlider(0);
        startAutoPlay();
    }
});