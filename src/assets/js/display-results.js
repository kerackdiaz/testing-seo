function displayResults(results) {
    const resultsContainer = document.getElementById('results-container');
    if (!results.metrics) {
        alert('Error al procesar los resultados. Inténtalo de nuevo más tarde.');
        return;
    }

    const metrics = results.metrics;
    resultsContainer.innerHTML = '';

    const title = document.createElement('h3');
    title.textContent = `Resultados para ${results.url}`;
    resultsContainer.appendChild(title);

    const scoresContainer = document.createElement('div');
    scoresContainer.className = 'scores-container';

    const performanceScore = calculatePerformanceScore(metrics);
    const accessibilityScore = calculateAccessibilityScore(metrics);
    const seoScore = calculateSeoScore(metrics);

    scoresContainer.innerHTML = `
        <div class="score-card performance">
            <h4>Rendimiento</h4>
            <div class="score-circle tooltip" style="background-color: ${getScoreColor(performanceScore)};" data-tooltip="Medida de la eficiencia de carga de la página.">
                <span class="score-value">${performanceScore}</span>
            </div>
        </div>
        <div class="score-card accessibility">
            <h4>Accesibilidad</h4>
            <div class="score-circle tooltip" style="background-color: ${getScoreColor(accessibilityScore)};" data-tooltip="Evaluación de la accesibilidad para todos los usuarios.">
                <span class="score-value">${accessibilityScore}</span>
            </div>
        </div>
        <div class="score-card seo">
            <h4>SEO</h4>
            <div class="score-circle tooltip" style="background-color: ${getScoreColor(seoScore)};" data-tooltip="Optimización para motores de búsqueda.">
                <span class="score-value">${seoScore}</span>
            </div>
        </div>
    `;

    resultsContainer.appendChild(scoresContainer);

    const viewMoreButton = document.createElement('button');
    viewMoreButton.id = 'view-more-button';
    viewMoreButton.className = 'button';
    viewMoreButton.textContent = 'Ver toda la info';
    resultsContainer.appendChild(viewMoreButton);

    viewMoreButton.addEventListener('click', function() {
        openPopup(resultsContainer, results);
    });
}

function displayAdditionalContent(container, results) {
    const metrics = results.metrics;

    const metricsContainer = document.createElement('div');
    metricsContainer.className = 'metrics-container';

    const relevantMetrics = [
        { key: 'first-contentful-paint', title: 'First Contentful Paint (FCP)', tooltip: 'Tiempo que tarda en renderizarse el primer contenido en la pantalla.' },
        { key: 'largest-contentful-paint', title: 'Largest Contentful Paint (LCP)', tooltip: 'Tiempo que tarda en cargarse el contenido más grande visible en la pantalla.' },
        { key: 'cumulative-layout-shift', title: 'Cumulative Layout Shift (CLS)', tooltip: 'Medida de la estabilidad visual durante la carga.' },
        { key: 'interactive', title: 'Time to Interactive (TTI)', tooltip: 'Tiempo que tarda la página en volverse completamente interactiva.' },
        { key: 'total-blocking-time', title: 'Total Blocking Time (TBT)', tooltip: 'Tiempo total de bloqueo que afecta la interactividad.' },
    ];

    relevantMetrics.forEach(metric => {
        const metricData = metrics[metric.key];
        if (metricData && metricData.displayValue) {
            const metricElement = document.createElement('div');
            metricElement.className = 'metric';
            metricElement.innerHTML = `
                <h4 class="tooltip" data-tooltip="${metric.tooltip}">${metric.title}</h4>
                <div class="progress-bar tooltip" data-tooltip="Indicador visual del valor de ${metric.title.toLowerCase()}">
                    <div class="progress-bar-fill" style="width: ${getMetricProgress(metricData.displayValue)}%;" data-tooltip="Progreso de ${metric.displayValue}"></div>
                </div>
                <p class="tooltip" data-tooltip="Valor actual de ${metric.title.toLowerCase()}">${metricData.displayValue}</p>
            `;
            metricsContainer.appendChild(metricElement);
        }
    });

    container.appendChild(metricsContainer);

    const opportunitiesContainer = document.createElement('div');
    opportunitiesContainer.className = 'opportunities-container';

    const opportunities = [
        { key: 'render-blocking-resources', title: 'Eliminar recursos bloqueantes', icon: '🗑️', details: 'Potential savings of 250 ms', tooltip: 'Reducir recursos que bloquean la renderización de la página.' },
        { key: 'unused-javascript', title: 'Reducir JavaScript no utilizado', icon: '📜', details: 'Potential savings of 150 ms', tooltip: 'Eliminar JavaScript que no se utiliza para optimizar el rendimiento.' },
        { key: 'unused-css-rules', title: 'Reducir CSS no utilizado', icon: '🎨', details: 'Potential savings of 100 ms', tooltip: 'Eliminar reglas CSS que no se utilizan para mejorar la carga.' },
        { key: 'modern-image-formats', title: 'Usar formatos de imagen modernos', icon: '🖼️', details: 'Potential savings of 200 ms', tooltip: 'Utilizar formatos de imagen más eficientes como WebP.' },
    ];

    opportunities.forEach(opportunity => {
        const opportunityData = metrics[opportunity.key];
        if (opportunityData && opportunityData.displayValue) {
            const opportunityElement = document.createElement('div');
            opportunityElement.className = 'opportunity';
            opportunityElement.innerHTML = `
                <div class="opportunity-header tooltip" data-tooltip="${opportunity.tooltip}">
                    <span class="opportunity-icon tooltip" data-tooltip="${opportunity.tooltip}">${opportunity.icon}</span>
                    <h4>${opportunity.title}</h4>
                </div>
                <p class="opportunity-details tooltip" data-tooltip="Descripción detallada de ${opportunity.title.toLowerCase()}">${opportunity.details}</p>
                <p class="tooltip" data-tooltip="Descripción: ${opportunityData.displayValue}">${opportunityData.displayValue}</p>
            `;
            opportunitiesContainer.appendChild(opportunityElement);
        }
    });

    container.appendChild(opportunitiesContainer);
}