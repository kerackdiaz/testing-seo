<?php
// Este archivo se encarga de limpiar la base de datos y eliminar datos del plugin al desinstalarlo.

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Función para eliminar opciones del plugin
function seo_testing_inclup_uninstall() {
    // Eliminar opciones de la base de datos
    delete_option( 'seo_testing_inclup_email' );
    delete_option( 'seo_testing_inclup_api_token' );
    delete_option( 'seo_testing_inclup_custom_texts' );
    delete_option( 'seo_testing_inclup_policy_urls' );
    delete_option( 'seo_testing_inclup_leads_cleanup' );
    // Aquí se pueden agregar más opciones a eliminar si es necesario
}

// Ejecutar la función al desinstalar el plugin
seo_testing_inclup_uninstall();