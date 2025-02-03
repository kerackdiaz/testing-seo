<?php
// Funciones para interactuar con la API de Google PageSpeed Insights

/**
 * Realiza una solicitud a la API de Google PageSpeed Insights.
 *
 * @param string $url La URL del sitio web a analizar.
 * @param string $api_key La clave de API de Google PageSpeed.
 * @param string $strategy Estrategia: 'mobile' o 'desktop'.
 * @return array|WP_Error Los resultados de la API o un error.
 */
function seo_testing_inclup_get_pagespeed_data($url, $api_key, $strategy = 'mobile') {
    if (empty($api_key)) {
        return new WP_Error('missing_api_key', 'La clave de API no está configurada.');
    }

    // Asegurarse de que la URL tenga el protocolo correcto
    if (!preg_match('/^https?:\/\//', $url)) {
        $url = 'https://' . $url;
    }

    $api_url = add_query_arg([
        'url' => $url,
        'key' => $api_key,
        'strategy' => $strategy,
        'category' => ['performance', 'accessibility', 'best-practices', 'seo'],
    ], 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed');

    $response = wp_remote_get($api_url, [
        'timeout' => 60, // Aumentar el tiempo de espera a 60 segundos
    ]);

    if (is_wp_error($response)) {
        return $response;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (isset($data['error'])) {
        return new WP_Error('api_error', $data['error']['message']);
    }

    return $data;
}

/**
 * Procesa los resultados de la API y extrae información relevante.
 *
 * @param array $data Los datos devueltos por la API.
 * @return array Información procesada.
 */
function seo_testing_inclup_process_pagespeed_data($data) {
    if (!isset($data['lighthouseResult'])) {
        return [];
    }

    $categories = $data['lighthouseResult']['categories'];
    $audits = $data['lighthouseResult']['audits'];

    return [
        'scores' => [
            'performance'   => (($categories['performance']['score'] ?? 0) * 100),
            'accessibility' => (($categories['accessibility']['score'] ?? 0) * 100),
            'best_practices'=> (($categories['best-practices']['score'] ?? 0) * 100),
            'seo'           => (($categories['seo']['score'] ?? 0) * 100),
        ],
        'url' => $data['lighthouseResult']['finalUrl'],
        'metrics' => [
            'lcp' => $audits['largest-contentful-paint']['displayValue'] ?? 'N/A',
            'fid' => $audits['first-input-delay']['displayValue'] ?? 'N/A',
            'cls' => $audits['cumulative-layout-shift']['displayValue'] ?? 'N/A',
        ],
        'opportunities' => array_filter(array_map(function ($audit) {
            if (isset($audit['details']['type']) && $audit['details']['type'] === 'opportunity') {
                return [
                    'title' => $audit['title'],
                    'description' => $audit['description'],
                    'savings' => $audit['details']['overallSavingsMs'] ?? 0,
                ];
            }
            return null;
        }, $audits)),
    ];
}

/**
 * Obtiene y procesa datos tanto para móvil como para escritorio.
 *
 * @param string $url La URL del sitio web a analizar.
 * @param string $api_key La clave de API de Google PageSpeed.
 * @return array Datos procesados para móvil y escritorio.
 */
function seo_testing_inclup_get_complete_analysis($url, $api_key) {
    $mobile_data = seo_testing_inclup_get_pagespeed_data($url, $api_key, 'mobile');
    $desktop_data = seo_testing_inclup_get_pagespeed_data($url, $api_key, 'desktop');

    if (is_wp_error($mobile_data) || is_wp_error($desktop_data)) {
        return new WP_Error('api_error', 'Error al obtener datos de la API.');
    }

    return [
        'mobile' => seo_testing_inclup_process_pagespeed_data($mobile_data),
        'desktop' => seo_testing_inclup_process_pagespeed_data($desktop_data),
    ];
}