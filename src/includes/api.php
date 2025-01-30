<?php
// Funciones para interactuar con la API de Google PageSpeed Insights

/**
 * Realiza una solicitud a la API de Google PageSpeed Insights.
 *
 * @param string $url La URL del sitio web a analizar.
 * @param string $api_key La clave de API de Google PageSpeed.
 * @return array|WP_Error Los resultados de la API o un error.
 */
function seo_testing_inclup_get_pagespeed_data( $url, $api_key ) {
    // Asegurarse de que la URL tenga el protocolo correcto
    if (!preg_match('/^https?:\/\//', $url)) {
        $url = 'https://' . $url;
    }

    $api_url = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=' . urlencode( $url ) . '&key=' . $api_key;

    $response = wp_remote_get( $api_url, array(
        'timeout' => 60, // Aumentar el tiempo de espera a 60 segundos
    ));

    if ( is_wp_error( $response ) ) {
        return $response;
    }

    $body = wp_remote_retrieve_body( $response );
    return json_decode( $body, true );
}

/**
 * Procesa los resultados de la API y extrae información relevante.
 *
 * @param array $data Los datos devueltos por la API.
 * @return array Información procesada.
 */
function seo_testing_inclup_process_pagespeed_data( $data ) {
    if ( isset( $data['lighthouseResult'] ) ) {
        return [
            'score' => $data['lighthouseResult']['categories']['performance']['score'] * 100,
            'url' => $data['lighthouseResult']['finalUrl'],
            'metrics' => array_map(function($audit) {
                return [
                    'title' => $audit['title'],
                    'displayValue' => $audit['displayValue']
                ];
            }, $data['lighthouseResult']['audits'])
        ];
    }
    return [];
}

/**
 * Muestra los resultados limitados de PageSpeed en el frontend.
 *
 * @param array $data Los datos procesados de PageSpeed.
 */
function seo_testing_inclup_display_limited_results( $data ) {
    echo '<h3>Resultados de PageSpeed</h3>';
    echo '<p>Puntuación: ' . esc_html( $data['score'] ) . '</p>';
    echo '<p>URL: ' . esc_html( $data['url'] ) . '</p>';
    echo '<button id="view-full-results">Ver toda la información</button>';
}

/**
 * Muestra los resultados completos de PageSpeed en un popup.
 *
 * @param array $data Los datos procesados de PageSpeed.
 */
function seo_testing_inclup_display_full_results( $data ) {
    echo '<div id="full-results-popup" style="display:none;">';
    echo '<h3>Resultados completos de PageSpeed</h3>';
    echo '<p>Puntuación: ' . esc_html( $data['score'] ) . '</p>';
    echo '<p>URL: ' . esc_html( $data['url'] ) . '</p>';
    foreach ( $data['metrics'] as $metric ) {
        echo '<p>' . esc_html( $metric['title'] ) . ': ' . esc_html( $metric['displayValue'] ) . '</p>';
    }
    echo '</div>';
}
?>