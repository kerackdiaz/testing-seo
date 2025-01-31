function calculatePerformanceScore(metrics) {
    const fcp = parseFloat(metrics['first-contentful-paint'].displayValue) || 0;
    const lcp = parseFloat(metrics['largest-contentful-paint'].displayValue) || 0;
    const cls = parseFloat(metrics['cumulative-layout-shift'].displayValue) || 0;
    const tbt = parseFloat(metrics['total-blocking-time'].displayValue) || 0;

    const score = 100 - (fcp * 0.2 + lcp * 0.3 + cls * 20 + tbt * 0.1);
    return Math.max(0, Math.min(100, Math.round(score)));
}

function calculateAccessibilityScore(metrics) {
    let score = 100;

    if (metrics['color-contrast'] && metrics['color-contrast'].displayValue) {
        score -= 10;
    }

    if (metrics['image-alt'] && metrics['image-alt'].displayValue) {
        score -= 5;
    }

    if (metrics['aria-valid-attr-value'] && metrics['aria-valid-attr-value'].displayValue) {
        score -= 5;
    }

    return Math.max(0, Math.min(100, score));
}

function calculateSeoScore(metrics) {
    let score = 100;

    if (metrics['meta-description'] && metrics['meta-description'].displayValue) {
        score -= 5;
    }

    if (metrics['heading-order'] && metrics['heading-order'].displayValue) {
        score -= 5;
    }

    if (metrics['modern-image-formats'] && metrics['modern-image-formats'].displayValue) {
        score -= 10;
    }

    if (metrics['speed-index'] && parseFloat(metrics['speed-index'].displayValue) > 3.0) {
        score -= 15;
    }

    return Math.max(0, Math.min(100, score));
}

function getScoreColor(score) {
    if (score >= 90) return '#4caf50';
    if (score >= 50) return '#ffc107';
    return '#ff5722';
}

function getMetricProgress(value) {
    const numericValue = parseFloat(value);
    return Math.min(100, numericValue * 10);
}