<?php
// Funciones auxiliares para el plugin SEO TESTING INCLUP

/**
 * Validar la URL ingresada por el usuario.
 *
 * @param string $url La URL a validar.
 * @return bool Verdadero si la URL es válida, falso en caso contrario.
 */
function seo_testing_inclup_validate_url( $url ) {
    return filter_var( $url, FILTER_VALIDATE_URL ) !== false;
}

/**
 * Guardar un lead en la base de datos.
 *
 * @param array $lead Datos del lead a guardar.
 * @return bool Verdadero si se guardó correctamente, falso en caso contrario.
 */
function seo_testing_inclup_save_lead( $lead ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'seo_testing_inclup_leads';

    return $wpdb->insert( $table_name, $lead );
}

/**
 * Limpiar la base de datos de leads antiguos.
 *
 * @param int $days Número de días para considerar un lead como antiguo.
 * @return int Número de filas eliminadas.
 */
function seo_testing_inclup_cleanup_old_leads( $days ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'seo_testing_inclup_leads';
    $date_limit = date( 'Y-m-d H:i:s', strtotime( "-$days days" ) );

    return $wpdb->query( $wpdb->prepare( "DELETE FROM $table_name WHERE created_at < %s", $date_limit ) );
}

/**
 * Obtener todos los leads de la base de datos.
 *
 * @param bool $potential_client Filtrar por clientes potenciales.
 * @return array Lista de leads.
 */
function seo_testing_inclup_get_all_leads( $potential_client = false ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'seo_testing_inclup_leads';

    if ( $potential_client ) {
        return $wpdb->get_results( "SELECT * FROM $table_name WHERE potential_client = 1", ARRAY_A );
    }

    return $wpdb->get_results( "SELECT * FROM $table_name", ARRAY_A );
}

/**
 * Generar un archivo CSV a partir de los leads.
 *
 * @param array $leads Lista de leads a exportar.
 * @return string Ruta del archivo CSV generado.
 */
function seo_testing_inclup_generate_csv( $leads ) {
    $filename = SEO_TESTING_INCLUP_DIR . 'leads.csv';
    $file = fopen( $filename, 'w' );

    // Agregar encabezados al CSV
    fputcsv( $file, array( 'ID', 'Dominio', 'Nombre', 'Empresa', 'Celular', 'Email', 'Fecha', 'Cliente Potencial' ) );

    // Agregar datos de los leads
    foreach ( $leads as $lead ) {
        fputcsv( $file, array(
            $lead['id'],
            $lead['domain'],
            $lead['name'],
            $lead['company'],
            $lead['phone'],
            $lead['email'],
            $lead['created_at'],
            $lead['potential_client'] ? 'Sí' : 'No'
        ));
    }

    fclose( $file );

    return $filename;
}

/**
 * Enviar un correo electrónico con los detalles del lead.
 *
 * @param array $lead Datos del lead.
 */
function seo_testing_inclup_send_lead_email( $lead ) {
    $to = get_option( 'seo_testing_inclup_email' );
    $subject = 'Nuevo Lead Generado';
    $message = "Se ha generado un nuevo lead:\n\n";
    $message .= "Nombre: " . $lead['name'] . "\n";
    $message .= "Empresa: " . $lead['company'] . "\n";
    $message .= "Celular: " . $lead['phone'] . "\n";
    $message .= "Email: " . $lead['email'] . "\n";
    $message .= "Dominio: " . $lead['domain'] . "\n";
    $message .= "Fecha: " . $lead['created_at'] . "\n";
    $message .= "Cliente Potencial: " . ( $lead['potential_client'] ? 'Sí' : 'No' ) . "\n";

    wp_mail( $to, $subject, $message );
}

/**
 * Marcar un lead como cliente potencial.
 *
 * @param int $lead_id ID del lead.
 * @return bool Verdadero si se actualizó correctamente, falso en caso contrario.
 */
function seo_testing_inclup_mark_as_potential_client( $lead_id ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'seo_testing_inclup_leads';

    return $wpdb->update( $table_name, array( 'potential_client' => 1 ), array( 'id' => $lead_id ) );
}
?>