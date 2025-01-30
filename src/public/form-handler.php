<?php
// Este archivo procesa el formulario enviado por el usuario, valida los datos y envía la solicitud a la API de PageSpeed. 
// También maneja la lógica para almacenar los leads en la base de datos.

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Evitar el acceso directo al archivo
}

function seo_testing_inclup_handle_form_submission() {
    if ( isset( $_POST['seo_testing_inclup_submit'] ) ) {
        // Validar el dominio ingresado
        $domain = sanitize_text_field( $_POST['domain'] );

        // Asegurarse de que el dominio tenga el protocolo correcto
        if (!preg_match('/^https?:\/\//', $domain)) {
            $domain = 'https://' . $domain;
        }

        // Remover la barra al final si existe
        $domain = rtrim($domain, '/');

        // Validar la URL completa
        if ( ! filter_var( $domain, FILTER_VALIDATE_URL ) ) {
            wp_die( 'Dominio no válido. Por favor, ingresa un dominio correcto.' );
        }

        // Verificar reCAPTCHA
        if ( ! seo_testing_inclup_verify_recaptcha( $_POST['g-recaptcha-response'] ) ) {
            wp_die( 'Error de reCAPTCHA. Por favor, intenta nuevamente.' );
        }

        // Llamar a la API de Google PageSpeed Insights
        $api_response = seo_testing_inclup_get_page_speed_data( $domain );
        if ( is_wp_error( $api_response ) ) {
            wp_die( 'Error al obtener datos de la API: ' . $api_response->get_error_message() );
        }

        // Almacenar el lead en la base de datos
        seo_testing_inclup_store_lead( $domain, $api_response );

        // Redirigir a la página de resultados
        wp_redirect( home_url( '/resultados/?domain=' . urlencode( $domain ) ) );
        exit;
    }
}
add_action( 'init', 'seo_testing_inclup_handle_form_submission' );

function seo_testing_inclup_get_page_speed_data( $domain ) {
    $api_key = get_option( 'seo_testing_inclup_api_key' );
    $url = "https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=" . urlencode($domain) . "&key={$api_key}";

    $response = wp_remote_get( $url, array(
        'timeout' => 60, // Aumentar el tiempo de espera a 60 segundos
    ));

    if ( is_wp_error( $response ) ) {
        return $response;
    }

    $body = wp_remote_retrieve_body( $response );
    return json_decode( $body, true );
}

function seo_testing_inclup_store_lead( $domain, $api_response ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'seo_testing_leads';

    $wpdb->insert( 
        $table_name, 
        array( 
            'domain' => $domain, 
            'response' => json_encode( $api_response ),
            'created_at' => current_time( 'mysql' )
        ) 
    );
}

function seo_testing_inclup_verify_recaptcha( $recaptcha_response ) {
    $recaptcha_secret = get_option( 'seo_testing_inclup_recaptcha_secret' );
    $response = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', array(
        'body' => array(
            'secret' => $recaptcha_secret,
            'response' => $recaptcha_response
        )
    ));

    $response_body = wp_remote_retrieve_body( $response );
    $result = json_decode( $response_body, true );

    return isset( $result['success'] ) && $result['success'] == true;
}
?>