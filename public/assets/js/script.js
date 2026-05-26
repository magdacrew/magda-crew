document.addEventListener("DOMContentLoaded", function() {

    // ==========================================
    // 1. FILTRO DE CATEGORIAS (AJAX)
    // ==========================================
    const botoesFiltro = document.querySelectorAll('.btn-filtro-categoria');
    const containerVitrine = document.getElementById('vitrine-container');

    if (containerVitrine) {
        botoesFiltro.forEach(botao => {
            botao.addEventListener('click', function() {
                // Controle visual: remove 'active' de todos e coloca no clicado
                botoesFiltro.forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                // Pega o ID (1 para Tudo, 6 para Camisas, etc)
                const categoriaId = this.getAttribute('data-id');

                // Feedback visual de carregamento
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
                            // Substitui o conteúdo da vitrine atual pelo novo
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
    }

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
            if (newBg) {
                hero.style.backgroundImage = `url('${newBg}')`;
            }
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

document.addEventListener("DOMContentLoaded", function() {
    // Pega todas as miniaturas e a imagem principal
    const miniaturas = document.querySelectorAll('.miniatura');
    const imagemPrincipal = document.getElementById('imagem-principal');
    
    // Se não houver galeria nesta página, encerra o script
    if (!imagemPrincipal || miniaturas.length === 0) return;

    // Cria um array com os links (src) de todas as imagens
    const imagensSrc = Array.from(miniaturas).map(miniatura => miniatura.getAttribute('data-src'));
    let imagemAtualIndex = 0;

    // Função que atualiza a foto e a marcação da miniatura
    window.atualizarGaleria = function(index) {
        // Lógica para as setas fazerem um loop (voltar pro início ou fim)
        if (index < 0) {
            index = imagensSrc.length - 1;
        } else if (index >= imagensSrc.length) {
            index = 0;
        }
        
        imagemAtualIndex = index;
        
        // Troca a foto principal com uma transição suave
        imagemPrincipal.style.opacity = 0; 
        setTimeout(() => {
            imagemPrincipal.src = imagensSrc[imagemAtualIndex];
            imagemPrincipal.style.opacity = 1;
        }, 150); // 150ms para trocar a imagem enquanto está invisível

        // Atualiza qual miniatura está com a bordinha branca (classe 'ativa')
        miniaturas.forEach(m => m.classList.remove('ativa'));
        miniaturas[imagemAtualIndex].classList.add('ativa');
    };

    // Função disparada pelas setas (recebe -1 para voltar, +1 para avançar)
    window.mudarImagem = function(direcao) {
        atualizarGaleria(imagemAtualIndex + direcao);
    };

    // Função disparada ao clicar direto na miniatura lá embaixo
    window.selecionarImagem = function(index) {
        atualizarGaleria(index);
    };
    
    // Adiciona uma transição CSS via JS para a imagem principal piscar suave
    imagemPrincipal.style.transition = "opacity 0.2s ease-in-out";
});