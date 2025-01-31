document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('seo-testing-form');
    const loadingAnimation = document.getElementById('loading-animation');
    const loadingText = document.getElementById('loading-text');
    let resultsData = null;
    let loadingInterval;

    const loadingMessages = [
        "Analizando...",
        "Verificando métricas...",
        "Preparando un café...",
        "Obteniendo resultados...",
        "Procesando datos...",
        "Preparando un sandwich...",
        "Casi listo..."
    ];
    let currentMessageIndex = 0;

    function startLoadingAnimation() {
        if (loadingText) {
            loadingInterval = setInterval(() => {
                loadingText.textContent = loadingMessages[currentMessageIndex];
                currentMessageIndex = (currentMessageIndex + 1) % loadingMessages.length;
            }, 1000);
        }
    }

    function stopLoadingAnimation() {
        if (loadingInterval) {
            clearInterval(loadingInterval);
        }
    }

    if (form) {
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            let domain = document.getElementById('domain-input').value.trim();
            domain = domain.replace(/\/$/, '');

            if (!isValidDomain(domain)) {
                alert('Por favor, ingresa un dominio válido.');
                return;
            }

            if (!domain.startsWith('http://') && !domain.startsWith('https://')) {
                domain = 'https://' + domain;
            }

            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 60000);

            if (loadingAnimation) {
                loadingAnimation.style.display = 'flex';
                startLoadingAnimation();
            }

            fetch(seoTestingInclup.ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'seo_testing_inclup_submit',
                    domain: domain
                }),
                signal: controller.signal
            })
            .then(response => response.json())
            .then(data => {
                clearTimeout(timeoutId);
                if (data.success) {
                    if (data.data && data.data.results) {
                        resultsData = data.data.results;
                        displayResults(resultsData);
                    } else {
                        alert('Error al procesar los resultados. Inténtalo de nuevo más tarde.');
                    }
                } else {
                    alert('Error al obtener los resultados. Inténtalo de nuevo más tarde.');
                }
            })
            .catch(error => {
                if (error.name === 'AbortError') {
                    alert('La solicitud tardó demasiado tiempo. Por favor, intenta de nuevo.');
                } else {
                    alert('Ocurrió un error. Por favor, intenta de nuevo.');
                }
            })
            .finally(() => {
                if (loadingAnimation) {
                    loadingAnimation.style.display = 'none';
                    stopLoadingAnimation();
                }
            });
        });
    }
});