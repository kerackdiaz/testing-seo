<?php
// Página de administración para gestionar los leads generados por el plugin

// Evitar el acceso directo al archivo
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Función para mostrar la página de leads
function seo_testing_inclup_leads_page() {
    global $wpdb;

    // Obtener parámetros de filtrado desde $_GET
    $potential_client_filter = isset($_GET['potential_client']) ? sanitize_text_field($_GET['potential_client']) : '';
    $start_date = isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : '';
    $end_date = isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : '';
    $search_query = isset($_GET['search_query']) ? sanitize_text_field($_GET['search_query']) : '';

    // Construir la consulta SQL con filtros
    $where_clauses = [];
    $query_args = [];

    if ( $potential_client_filter === 'sí' ) {
        $where_clauses[] = "potential_client = %d";
        $query_args[] = 1;
    } elseif ( $potential_client_filter === 'no' ) {
        $where_clauses[] = "potential_client = %d";
        $query_args[] = 0;
    }

    if ( !empty($start_date) ) {
        $where_clauses[] = "created_at >= %s";
        $query_args[] = $start_date . ' 00:00:00';
    }

    if ( !empty($end_date) ) {
        $where_clauses[] = "created_at <= %s";
        $query_args[] = $end_date . ' 23:59:59';
    }

    if ( !empty($search_query) ) {
        $where_clauses[] = "(domain LIKE %s OR name LIKE %s OR company LIKE %s)";
        $like_query = '%' . $wpdb->esc_like( $search_query ) . '%';
        $query_args[] = $like_query;
        $query_args[] = $like_query;
        $query_args[] = $like_query;
    }

    $where_sql = '';
    if ( !empty($where_clauses) ) {
        $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
    }

    // Obtener leads de la base de datos con filtros
    $leads_table = $wpdb->prefix . 'seo_testing_inclup_leads'; // Nombre de tabla corregido
    if ( !empty( $query_args ) ) {
        $query = "SELECT * FROM $leads_table $where_sql ORDER BY created_at DESC";
        $leads = $wpdb->get_results( $wpdb->prepare( $query, $query_args ) );
    } else {
        $query = "SELECT * FROM $leads_table ORDER BY created_at DESC";
        $leads = $wpdb->get_results( $query );
    }

    // Manejo de la descarga de CSV
    if ( isset( $_POST['download_csv'] ) ) {
        // Construir la misma consulta para la descarga
        if ( !empty( $query_args ) ) {
            $csv_leads = $wpdb->get_results( $wpdb->prepare( $query, $query_args ) );
        } else {
            $csv_leads = $wpdb->get_results( $query );
        }

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="leads.csv"');
        $output = fopen('php://output', 'w');
        fputcsv($output, array('ID', 'Dominio', 'Nombre', 'Empresa', 'Celular', 'Email', 'Fecha', 'Cliente Potencial'));

        foreach ( $csv_leads as $lead ) {
            fputcsv($output, array(
                $lead->id,
                $lead->domain,
                $lead->name,
                $lead->company,
                $lead->phone,
                $lead->email,
                $lead->created_at,
                $lead->potential_client ? 'Sí' : 'No'
            ));
        }
        fclose($output);
        exit;
    }

    // Mostrar la interfaz de la página
    ?>
    <div class="wrap seo-testing-inclup-leads-page">
        <h1>Leads Generados</h1>

        <!-- Formulario de Filtros -->
        <form method="get" class="filters-form">
            <input type="hidden" name="page" value="seo-testing-inclup-leads">

            <div class="filter-item">
                <label for="potential_client">Cliente Potencial</label>
                <select name="potential_client" id="potential_client">
                    <option value="">Todos</option>
                    <option value="sí" <?php selected( $potential_client_filter, 'sí' ); ?>>Sí</option>
                    <option value="no" <?php selected( $potential_client_filter, 'no' ); ?>>No</option>
                </select>
            </div>

            <div class="filter-item">
                <label for="start_date">Desde</label>
                <input type="date" name="start_date" id="start_date" value="<?php echo esc_attr( $start_date ); ?>">
            </div>

            <div class="filter-item">
                <label for="end_date">Hasta</label>
                <input type="date" name="end_date" id="end_date" value="<?php echo esc_attr( $end_date ); ?>">
            </div>

            <div class="filter-item">
                <label for="search_query">Buscar</label>
                <input type="text" name="search_query" id="search_query" value="<?php echo esc_attr( $search_query ); ?>" placeholder="Dominio, Nombre o Empresa">
            </div>

            <?php submit_button( 'Filtrar', 'secondary', 'filter_action' ); ?>
        </form>

        <!-- Formulario para Descargar CSV con Filtros -->
        <form method="post" class="download-csv-form">
            <?php
            // Incluir los filtros actuales como campos ocultos
            if ( !empty($potential_client_filter) ) {
                echo '<input type="hidden" name="potential_client" value="' . esc_attr($potential_client_filter) . '">';
            }
            if ( !empty($start_date) ) {
                echo '<input type="hidden" name="start_date" value="' . esc_attr($start_date) . '">';
            }
            if ( !empty($end_date) ) {
                echo '<input type="hidden" name="end_date" value="' . esc_attr($end_date) . '">';
            }
            if ( !empty($search_query) ) {
                echo '<input type="hidden" name="search_query" value="' . esc_attr($search_query) . '">';
            }
            ?>
            <input type="submit" name="download_csv" class="button button-primary" value="Descargar CSV">
        </form>

        <!-- Tabla de Leads -->
        <table class="widefat">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Dominio</th>
                    <th>Nombre</th>
                    <th>Empresa</th>
                    <th>Celular</th>
                    <th>Email</th>
                    <th>Fecha</th>
                    <th>Cliente Potencial</th>
                </tr>
            </thead>
            <tbody>
                <?php if ( ! empty( $leads ) ) : ?>
                    <?php foreach ( $leads as $lead ) : ?>
                        <tr>
                            <td><?php echo esc_html( $lead->id ); ?></td>
                            <td><?php echo esc_html( $lead->domain ); ?></td>
                            <td><?php echo esc_html( $lead->name ); ?></td>
                            <td><?php echo esc_html( $lead->company ); ?></td>
                            <td><?php echo esc_html( $lead->phone ); ?></td>
                            <td><?php echo esc_html( $lead->email ); ?></td>
                            <td><?php echo esc_html( $lead->created_at ); ?></td>
                            <td><?php echo $lead->potential_client ? 'Sí' : 'No'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="8">No se encontraron leads.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}
?>