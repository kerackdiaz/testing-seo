<?php
/**
 * Plugin Name: SEO TESTING INCLUP
 * Description: Un plugin para analizar un sitio web utilizando la API de Google PageSpeed Insights.
 * Version: 1.0.0
 * Author: Tu Nombre
 * Author URI: Tu URL
 * License: GPL2
 */

// Evitar el acceso directo al archivo
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Definir constantes
define( 'SEO_TESTING_INCLUP_VERSION', '1.0.0' );
define( 'SEO_TESTING_INCLUP_DIR', plugin_dir_path( __FILE__ ) );
define( 'SEO_TESTING_INCLUP_URL', plugin_dir_url( __FILE__ ) );

// Incluir archivos necesarios
require_once SEO_TESTING_INCLUP_DIR . 'src/includes/api.php';
require_once SEO_TESTING_INCLUP_DIR . 'src/includes/functions.php';
require_once SEO_TESTING_INCLUP_DIR . 'src/includes/recaptcha.php';
require_once SEO_TESTING_INCLUP_DIR . 'src/admin/settings-page.php';
require_once SEO_TESTING_INCLUP_DIR . 'src/admin/leads-page.php';
require_once SEO_TESTING_INCLUP_DIR . 'src/public/display-results.php';
require_once SEO_TESTING_INCLUP_DIR . 'src/public/form-handler.php';

// Activar el plugin
function seo_testing_inclup_activate() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'seo_testing_inclup_leads';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        domain varchar(255) NOT NULL,
        name varchar(255) NOT NULL,
        company varchar(255) NOT NULL,
        phone varchar(20) NOT NULL,
        email varchar(100) NOT NULL,
        response longtext NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        potential_client tinyint(1) DEFAULT 0 NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}
register_activation_hook( __FILE__, 'seo_testing_inclup_activate' );

// Desactivar el plugin
function seo_testing_inclup_deactivate() {
    // Código para ejecutar al desactivar el plugin
}
register_deactivation_hook( __FILE__, 'seo_testing_inclup_deactivate' );

// Cargar scripts y estilos
function seo_testing_inclup_enqueue_scripts() {
    wp_enqueue_style( 'seo-testing-inclup-style', SEO_TESTING_INCLUP_URL . 'src/assets/css/style.css' );
    wp_enqueue_script( 'seo-testing-inclup-script', SEO_TESTING_INCLUP_URL . 'src/assets/js/script.js', array( 'jquery' ), null, true );

    wp_localize_script( 'seo-testing-inclup-script', 'seoTestingInclup', array(
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'termsUrl' => get_option( 'seo_testing_inclup_terms_url' ),
        'privacyUrl' => get_option( 'seo_testing_inclup_privacy_url' ),
    ));
}
add_action( 'wp_enqueue_scripts', 'seo_testing_inclup_enqueue_scripts' );

// Agregar menú en el panel de administración
function seo_testing_inclup_add_admin_menu() {
    add_menu_page( 'SEO Testing Inclup', 'SEO Testing', 'manage_options', 'seo-testing-inclup', 'seo_testing_inclup_settings_page' );
    add_submenu_page( 'seo-testing-inclup', 'Leads', 'Leads', 'manage_options', 'seo-testing-inclup-leads', 'seo_testing_inclup_leads_page' );
}
add_action( 'admin_menu', 'seo_testing_inclup_add_admin_menu' );

// Shortcode para mostrar el formulario de SEO Testing
function seo_testing_inclup_form_shortcode() {
    ob_start();
    ?>
    <div class="seo-testing-inclup-container">
        <form id="seo-testing-form">
            <label for="domain-input">Ingresa el dominio:</label>
            <input type="text" id="domain-input" name="domain" required>
            <button type="submit" class="button">Enviar</button>
        </form>
        <div id="results-container"></div>
        <div id="full-info-popup" style="display:none;"></div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'seo_testing_inclup_form', 'seo_testing_inclup_form_shortcode' );

// Manejar la solicitud AJAX para el formulario
function seo_testing_inclup_handle_ajax() {
    if ( ! isset( $_POST['domain'] ) ) {
        wp_send_json_error( array( 'message' => 'Dominio no proporcionado.' ) );
    }

    $domain = esc_url_raw( $_POST['domain'] );

    if ( ! seo_testing_inclup_validate_url( $domain ) ) {
        wp_send_json_error( array( 'message' => 'Dominio no válido.' ) );
    }

    $api_key = get_option( 'seo_testing_inclup_api_token' );
    $api_response = seo_testing_inclup_get_pagespeed_data( $domain, $api_key );

    if ( is_wp_error( $api_response ) ) {
        wp_send_json_error( array( 'message' => $api_response->get_error_message() ) );
    }

    $processed_data = seo_testing_inclup_process_pagespeed_data( $api_response );
    wp_send_json_success( array( 'results' => $processed_data ) );
}
add_action( 'wp_ajax_seo_testing_inclup_submit', 'seo_testing_inclup_handle_ajax' );
add_action( 'wp_ajax_nopriv_seo_testing_inclup_submit', 'seo_testing_inclup_handle_ajax' );