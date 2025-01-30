document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('seo-testing-form');
    const resultsContainer = document.getElementById('results-container');
    let resultsData = null; // Variable global para almacenar los resultados de la API

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
            const timeoutId = setTimeout(() => controller.abort(), 60000); // 60 segundos

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
                console.log('Respuesta de la API:', data);
                if (data.success) {
                    if (data.data && data.data.results) {
                        resultsData = data.data.results; // Almacenar los resultados en la variable global
                        displayResults(resultsData);
                    } else {
                        console.error('Estructura de datos inesperada:', data);
                        alert('Error al procesar los resultados. Inténtalo de nuevo más tarde.');
                    }
                } else {
                    alert('Error al obtener los resultados. Inténtalo de nuevo más tarde.');
                }
            })
            .catch(error => {
                if (error.name === 'AbortError') {
                    console.error('Error: La solicitud fue cancelada debido a un tiempo de espera excedido.');
                    alert('La solicitud tardó demasiado tiempo. Por favor, intenta de nuevo.');
                } else {
                    console.error('Error:', error);
                    alert('Ocurrió un error. Por favor, intenta de nuevo.');
                }
            });
        });
    }

    function isValidDomain(domain) {
        const domainPattern = /^((https?:\/\/)?([a-z0-9-]+\.)+[a-z]{2,})$/i;
        return domainPattern.test(domain);
    }

    function displayResults(results) {
        console.log('Resultados completos:', results);

        if (!results.metrics) {
            console.error('Métricas no encontradas en los resultados:', results);
            alert('Error al procesar los resultados. Inténtalo de nuevo más tarde.');
            return;
        }

        const metrics = results.metrics;
        resultsContainer.innerHTML = ''; // Limpiar el contenedor

        // Mostrar la URL analizada
        const title = document.createElement('h3');
        title.textContent = `Resultados para ${results.url}`;
        resultsContainer.appendChild(title);

        // Mostrar los scores principales
        const scoresContainer = document.createElement('div');
        scoresContainer.className = 'scores-container';

        const performanceScore = calculatePerformanceScore(metrics);
        const accessibilityScore = calculateAccessibilityScore(metrics);
        const seoScore = calculateSeoScore(metrics);

        scoresContainer.innerHTML = `
            <div class="score-card" style="background-color: ${getScoreColor(performanceScore)};">
                <h4>Rendimiento</h4>
                <p class="score-value">${performanceScore}</p>
                <p class="score-description">Basado en métricas clave como FCP, LCP, CLS y TBT.</p>
            </div>
            <div class="score-card" style="background-color: ${getScoreColor(accessibilityScore)};">
                <h4>Accesibilidad</h4>
                <p class="score-value">${accessibilityScore}</p>
                <p class="score-description">Evaluación de la accesibilidad del sitio.</p>
            </div>
            <div class="score-card" style="background-color: ${getScoreColor(seoScore)};">
                <h4>SEO</h4>
                <p class="score-value">${seoScore}</p>
                <p class="score-description">Optimización para motores de búsqueda.</p>
            </div>
        `;

        resultsContainer.appendChild(scoresContainer);

        // Botón para ver más información (abre el popup con el formulario)
        const viewMoreButton = document.createElement('button');
        viewMoreButton.id = 'view-more-button';
        viewMoreButton.className = 'button';
        viewMoreButton.textContent = 'Ver toda la info';
        resultsContainer.appendChild(viewMoreButton);

        viewMoreButton.addEventListener('click', function() {
            openPopup();
        });
    }

    function calculatePerformanceScore(metrics) {
        // Calcular un score de rendimiento basado en métricas clave
        const fcp = parseFloat(metrics['first-contentful-paint'].displayValue) || 0;
        const lcp = parseFloat(metrics['largest-contentful-paint'].displayValue) || 0;
        const cls = parseFloat(metrics['cumulative-layout-shift'].displayValue) || 0;
        const tbt = parseFloat(metrics['total-blocking-time'].displayValue) || 0;

        // Ponderar las métricas (esto es un ejemplo, puedes ajustar los pesos)
        const score = 100 - (fcp * 0.2 + lcp * 0.3 + cls * 20 + tbt * 0.1);
        return Math.max(0, Math.min(100, Math.round(score))); // Asegurar que esté entre 0 y 100
    }

    function calculateAccessibilityScore(metrics) {
        let score = 100; // Puntaje inicial

        // Penalizar si hay problemas de contraste de colores
        if (metrics['color-contrast'] && metrics['color-contrast'].displayValue) {
            score -= 10; // Penalización por problemas de contraste
        }

        // Penalizar si hay imágenes sin atributos alt
        if (metrics['image-alt'] && metrics['image-alt'].displayValue) {
            score -= 5; // Penalización por imágenes sin alt
        }

        // Penalizar si hay problemas de uso de ARIA
        if (metrics['aria-valid-attr-value'] && metrics['aria-valid-attr-value'].displayValue) {
            score -= 5; // Penalización por problemas de ARIA
        }

        // Asegurar que el score esté entre 0 y 100
        return Math.max(0, Math.min(100, score));
    }

    function calculateSeoScore(metrics) {
        let score = 100; // Puntaje inicial

        // Penalizar si hay problemas de metaetiquetas
        if (metrics['meta-description'] && metrics['meta-description'].displayValue) {
            score -= 5; // Penalización por falta de meta descripción
        }

        // Penalizar si hay problemas de encabezados
        if (metrics['heading-order'] && metrics['heading-order'].displayValue) {
            score -= 5; // Penalización por problemas en el orden de encabezados
        }

        // Penalizar si hay imágenes no optimizadas
        if (metrics['modern-image-formats'] && metrics['modern-image-formats'].displayValue) {
            score -= 10; // Penalización por no usar formatos de imagen modernos
        }

        // Penalizar si hay problemas de velocidad
        if (metrics['speed-index'] && parseFloat(metrics['speed-index'].displayValue) > 3.0) {
            score -= 15; // Penalización por velocidad lenta
        }

        // Asegurar que el score esté entre 0 y 100
        return Math.max(0, Math.min(100, score));
    }

    function getScoreColor(score) {
        if (score >= 90) return '#4caf50'; // Verde
        if (score >= 50) return '#ff9800'; // Naranja
        return '#f44336'; // Rojo
    }

    function getMetricProgress(value) {
        // Convierte el valor de la métrica en un porcentaje para la barra de progreso
        const numericValue = parseFloat(value);
        return Math.min(100, numericValue * 10); // Ajusta según la métrica
    }

    function openPopup() {
        const popup = document.getElementById('full-info-popup');
        if (popup) {
            popup.style.display = 'flex';

            // Crear el contenido del popup (solo el formulario)
            const popupContent = `
                <div class="popup-content">
                    <span class="close">&times;</span>
                    <h3>Formulario de contacto</h3>
                    <form id="lead-form">
                        <label for="name">Nombre:</label>
                        <input type="text" id="name" name="name" required>
                        <label for="company">Nombre de la empresa:</label>
                        <input type="text" id="company" name="company" required>
                        <label for="phone">Celular:</label>
                        <input type="text" id="phone" name="phone" required>
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required>
                        <div class="terms-container">
                            <input type="checkbox" id="terms" name="terms" required>
                            <label for="terms">Acepto las <a href="${seoTestingInclup.termsUrl}" target="_blank">políticas de tratamiento de datos</a> y <a href="${seoTestingInclup.privacyUrl}" target="_blank">términos y condiciones</a></label>
                        </div>
                        <button type="submit" class="button">Enviar</button>
                    </form>
                </div>
            `;

            popup.innerHTML = popupContent;

            const closePopupButton = popup.querySelector('.close');
            if (closePopupButton) {
                closePopupButton.addEventListener('click', function() {
                    popup.style.display = 'none';
                });
            }

            const leadForm = document.getElementById('lead-form');
            if (leadForm) {
                leadForm.addEventListener('submit', function(event) {
                    event.preventDefault();
                    // Aquí puedes agregar la lógica para enviar el formulario y almacenar el lead
                    popup.style.display = 'none';
                    alert('Formulario enviado. Ahora puedes ver la información completa.');

                    // Mostrar el contenido adicional (métricas y oportunidades de mejora)
                    displayAdditionalContent(resultsContainer, resultsData);

                    // Ocultar el botón "Ver toda la info"
                    const viewMoreButton = document.getElementById('view-more-button');
                    if (viewMoreButton) {
                        viewMoreButton.style.display = 'none';
                    }
                });
            }
        }
    }

    function displayAdditionalContent(container, results) {
        const metrics = results.metrics;

        // Mostrar métricas clave
        const metricsContainer = document.createElement('div');
        metricsContainer.className = 'metrics-container';

        const relevantMetrics = [
            { key: 'first-contentful-paint', title: 'First Contentful Paint (FCP)' },
            { key: 'largest-contentful-paint', title: 'Largest Contentful Paint (LCP)' },
            { key: 'cumulative-layout-shift', title: 'Cumulative Layout Shift (CLS)' },
            { key: 'interactive', title: 'Time to Interactive (TTI)' },
            { key: 'total-blocking-time', title: 'Total Blocking Time (TBT)' },
        ];

        relevantMetrics.forEach(metric => {
            const metricData = metrics[metric.key];
            if (metricData && metricData.displayValue) {
                const metricElement = document.createElement('div');
                metricElement.className = 'metric';
                metricElement.innerHTML = `
                    <h4>${metric.title}</h4>
                    <div class="progress-bar">
                        <div class="progress-bar-fill" style="width: ${getMetricProgress(metricData.displayValue)}%;"></div>
                    </div>
                    <p>${metricData.displayValue}</p>
                `;
                metricsContainer.appendChild(metricElement);
            }
        });

        container.appendChild(metricsContainer);

        // Mostrar oportunidades de mejora
        const opportunitiesContainer = document.createElement('div');
        opportunitiesContainer.className = 'opportunities-container';

        const opportunities = [
            { key: 'render-blocking-resources', title: 'Eliminar recursos bloqueantes' },
            { key: 'unused-javascript', title: 'Reducir JavaScript no utilizado' },
            { key: 'unused-css-rules', title: 'Reducir CSS no utilizado' },
            { key: 'modern-image-formats', title: 'Usar formatos de imagen modernos' },
        ];

        opportunities.forEach(opportunity => {
            const opportunityData = metrics[opportunity.key];
            if (opportunityData && opportunityData.displayValue) {
                const opportunityElement = document.createElement('div');
                opportunityElement.className = 'opportunity';
                opportunityElement.innerHTML = `
                    <h4>${opportunity.title}</h4>
                    <p>${opportunityData.displayValue}</p>
                `;
                opportunitiesContainer.appendChild(opportunityElement);
            }
        });

        container.appendChild(opportunitiesContainer);
    }
});