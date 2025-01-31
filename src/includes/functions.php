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

/**
 * Manejar la solicitud AJAX para guardar el lead.
 */
function seo_testing_inclup_handle_ajax_save_lead() {
    // Registrar el inicio de la función
    error_log("seo_testing_inclup_handle_ajax_save_lead: Iniciando función.");

    if (!isset($_POST['lead'])) {
        error_log("seo_testing_inclup_handle_ajax_save_lead: 'lead' no está definido en \$_POST.");
        wp_send_json_error(['message' => 'Datos del lead no proporcionados.']);
    }

    $lead = json_decode(stripslashes($_POST['lead']), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("seo_testing_inclup_handle_ajax_save_lead: Error al decodificar JSON. Error: " . json_last_error_msg());
        wp_send_json_error(['message' => 'Datos del lead no válidos.']);
    }

    // Registrar los datos recibidos
    error_log("seo_testing_inclup_handle_ajax_save_lead: Datos recibidos - " . print_r($lead, true));

    if (empty($lead['name']) || empty($lead['company']) || empty($lead['phone']) || empty($lead['email']) || empty($lead['domain'])) {
        error_log("seo_testing_inclup_handle_ajax_save_lead: Faltan campos requeridos.");
        wp_send_json_error(['message' => 'Todos los campos son obligatorios.']);
    }

    // Calcular scores
    $performanceScore = isset($lead['performanceScore']) ? intval($lead['performanceScore']) : 0;
    $accessibilityScore = isset($lead['accessibilityScore']) ? intval($lead['accessibilityScore']) : 0;
    $seoScore = isset($lead['seoScore']) ? intval($lead['seoScore']) : 0;

    // Definir umbrales para determinar si es cliente potencial
    // Por ejemplo, si alguno de los scores es menor a 70
    if ($performanceScore < 70 || $accessibilityScore < 70 || $seoScore < 70) {
        $potential_client = 1;
    } else {
        $potential_client = 0;
    }

    $lead_data = [
        'name' => sanitize_text_field($lead['name']),
        'company' => sanitize_text_field($lead['company']),
        'phone' => sanitize_text_field($lead['phone']),
        'email' => sanitize_email($lead['email']),
        'domain' => esc_url_raw($lead['domain']),
        'created_at' => current_time('mysql'),
        'potential_client' => $potential_client
    ];

    $save_result = seo_testing_inclup_save_lead( $lead_data );

    if ( !$save_result ) {
        error_log("seo_testing_inclup_handle_ajax_save_lead: Error al guardar el lead en la base de datos.");
        wp_send_json_error(['message' => 'Error al guardar el lead en la base de datos.']);
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'seo_testing_inclup_leads';
    $lead_id = $wpdb->insert_id;
    error_log("seo_testing_inclup_handle_ajax_save_lead: Lead insertado con ID - " . $lead_id);

    // Obtener la fecha de creación del lead
    $created_at = $wpdb->get_var($wpdb->prepare("SELECT created_at FROM $table_name WHERE id = %d", $lead_id));
    error_log("seo_testing_inclup_handle_ajax_save_lead: Fecha de creación - " . $created_at);

    // Actualizar el campo 'created_at' en el lead
    $wpdb->update(
        $table_name,
        [ 'created_at' => $created_at ],
        [ 'id' => $lead_id ]
    );

    // Agregar 'created_at' al array $lead
    $lead_data['created_at'] = $created_at;

    // Enviar correo electrónico
    seo_testing_inclup_send_lead_email($lead_data);

    wp_send_json_success([
        'message' => 'Lead guardado y correo enviado.',
        'potential_client' => $lead_data['potential_client']
    ]);
}
add_action('wp_ajax_seo_testing_inclup_save_lead', 'seo_testing_inclup_handle_ajax_save_lead');
add_action('wp_ajax_nopriv_seo_testing_inclup_save_lead', 'seo_testing_inclup_handle_ajax_save_lead');
?>