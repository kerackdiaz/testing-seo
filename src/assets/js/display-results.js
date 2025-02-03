// Función principal para mostrar los resultados de forma similar a PageSpeed Insights,
// separando puntuaciones, métricas y oportunidades de optimización.
function displayResults(results) {
    const resultsContainer = document.getElementById('results-container');

    // Caso 1: Resultados completos separados para mobile y desktop:
    if (
        results &&
        ('mobile' in results) &&
        ('desktop' in results) &&
        results.mobile &&
        results.desktop
    ) {
        resultsContainer.innerHTML = `
            <div class="ps-results">
                <header class="ps-header">
                    <h2>Reporte de PageSpeed Insights</h2>
                    <p>URL analizada: <strong>${results.mobile.url}</strong></p>
                </header>
                <section class="ps-section">
                    <h3>Puntuaciones Globales</h3>
                    <div class="ps-scores-container">
                        <div class="ps-device">
                            <h4>Móvil</h4>
                            ${createScoresCard(results.mobile)}
                        </div>
                        <div class="ps-device">
                            <h4>Escritorio</h4>
                            ${createScoresCard(results.desktop)}
                        </div>
                    </div>
                </section>
                <section class="ps-section">
                    <h3>Métricas Clave</h3>
                    <div class="ps-metrics-container">
                        <div class="ps-device">
                            <h4>Móvil</h4>
                            ${createMetricsCard(results.mobile)}
                        </div>
                        <div class="ps-device">
                            <h4>Escritorio</h4>
                            ${createMetricsCard(results.desktop)}
                        </div>
                    </div>
                </section>
                <section class="ps-section">
                    <h3>Oportunidades de Optimización</h3>
                    <div class="ps-opportunities">
                        ${createOpportunitiesList(results.mobile.opportunities)}
                    </div>
                </section>
            </div>
        `;
    }
    // Caso 2: Resultado único con puntuación simple (por ejemplo, lo que se ve actualmente en la web)
    else if (results && 'score' in results) {
        resultsContainer.innerHTML = `
            <div class="ps-results">
                <header class="ps-header">
                    <h2>Reporte</h2>
                    <p>URL analizada: <strong>${results.url}</strong></p>
                </header>
                <section class="ps-section">
                    <h3>Puntuación</h3>
                    <p>${results.score}/100</p>
                </section>
                <section class="ps-section">
                    <h3>Métricas Clave</h3>
                    <div class="ps-metrics">
                        <div class="ps-metric">
                            <label>LCP:</label>
                            <span>${results.lcp}</span>
                        </div>
                        <div class="ps-metric">
                            <label>FID:</label>
                            <span>${results.fid}</span>
                        </div>
                        <div class="ps-metric">
                            <label>CLS:</label>
                            <span>${results.cls}</span>
                        </div>
                    </div>
                </section>
                <section class="ps-section">
                    <h3>Oportunidades de Optimización</h3>
                    <div class="ps-opportunities">
                        ${createOpportunitiesList(results.opportunities)}
                    </div>
                </section>
            </div>
        `;
    } else {
        alert('Error al procesar los resultados. Inténtalo de nuevo más tarde.');
    }
}

// Crea una tarjeta de puntuaciones con Performance, Accesibilidad, Best Practices y SEO
function createScoresCard(data) {
    return `
        <div class="ps-scores">
            ${createScoreItem("Performance", data.scores.performance)}
            ${createScoreItem("Accesibilidad", data.scores.accessibility)}
            ${createScoreItem("Best Practices", data.scores.best_practices)}
            ${createScoreItem("SEO", data.scores.seo)}
        </div>
    `;
}

// Crea un ítem individual de puntuación
function createScoreItem(label, score) {
    return `
        <div class="ps-score-item">
            <span class="ps-label">${label}:</span>
            <div class="ps-circle" style="background-color: ${getScoreColor(score)}">
                <span class="ps-value">${score}</span>
            </div>
        </div>
    `;
}

// Crea una tarjeta con las métricas clave
function createMetricsCard(data) {
    return `
        <div class="ps-metrics">
            <div class="ps-metric">
                <label>LCP:</label>
                <span>${data.metrics.lcp}</span>
            </div>
            <div class="ps-metric">
                <label>FID:</label>
                <span>${data.metrics.fid}</span>
            </div>
            <div class="ps-metric">
                <label>CLS:</label>
                <span>${data.metrics.cls}</span>
            </div>
        </div>
    `;
}

// Crea una lista de oportunidades de optimización
function createOpportunitiesList(opportunities) {
    const oppArray = Object.values(opportunities || {});
    if (oppArray.length === 0) {
        return `<p>No se encontraron oportunidades de optimización.</p>`;
    }
    return `
        <ul class="ps-opportunities-list">
            ${oppArray.map(opp => `
                <li class="ps-opportunity">
                    <strong>${opp.title}</strong>
                    <p>${opp.description}</p>
                    <span>Ahorro estimado: ${opp.savings} ms</span>
                </li>
            `).join('')}
        </ul>
    `;
}

// Devuelve el color correspondiente según la puntuación
function getScoreColor(score) {
    if (score >= 90) {
        return '#4caf50'; // Verde
    } else if (score >= 50) {
        return '#ff9800'; // Naranja
    } else {
        return '#f44336'; // Rojo
    }
}