document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('seo-testing-form');
    const loadingAnimation = document.getElementById('loading-animation');
    const loadingText = document.getElementById('loading-text');
    const resultsContainer = document.getElementById('results-container');
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

    // Se elimina la función displayResults local ya que se usará la definida en display-results.js

    if (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            let domain = document.getElementById('domain-input').value.trim();
            domain = domain.replace(/\/$/, '');
            if (!isValidDomain(domain)) {
                alert('Por favor, ingresa un dominio válido.');
                console.log("Dominio no válido:", domain);
                return;
            }
            if (!domain.startsWith('http://') && !domain.startsWith('https://')) {
                domain = 'https://' + domain;
            }

            const controller = new AbortController();
            const timeoutId = setTimeout(() => {
                console.log("Abortando solicitud por timeout");
                controller.abort();
            }, 60000);

            if (loadingAnimation) {
                loadingAnimation.style.display = 'flex';
                startLoadingAnimation();
            }

            console.log("Enviando solicitud con dominio:", domain);
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
            .then(response => {
                console.log("Respuesta de fetch recibida:", response);
                return response.json();
            })
            .then(data => {
                console.log("Datos parseados de JSON:", data);
                clearTimeout(timeoutId);
                try {
                    if (data.success) {
                        if (data.data && data.data.results) {
                            console.log("Procesando resultados:", data.data.results);
                            // Se utiliza la función displayResults definida en display-results.js
                            displayResults(data.data.results);
                        } else {
                            console.log("Respuesta exitosa pero sin resultados:", data.data);
                            alert('Error al procesar los resultados. Inténtalo de nuevo más tarde.');
                        }
                    } else {
                        console.log("Respuesta no exitosa:", data);
                        alert('Error al obtener los resultados. Inténtalo de nuevo más tarde.');
                    }
                } catch (e) {
                    console.error("Error en el procesamiento de datos:", e);
                    throw e;
                }
            })
            .catch(error => {
                console.error("Error en la solicitud fetch:", error);
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