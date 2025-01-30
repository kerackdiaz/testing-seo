<?php
// Página de administración para gestionar los leads generados por el plugin

// Evitar el acceso directo al archivo
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Función para mostrar la página de leads
function seo_testing_inclup_leads_page() {
    global $wpdb;

    // Obtener leads de la base de datos
    $leads_table = $wpdb->prefix . 'seo_testing_leads';
    $leads = $wpdb->get_results( "SELECT * FROM $leads_table ORDER BY created_at DESC" );

    // Manejo de la descarga de CSV
    if ( isset( $_POST['download_csv'] ) ) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="leads.csv"');
        $output = fopen('php://output', 'w');
        fputcsv($output, array('ID', 'Dominio', 'Nombre', 'Empresa', 'Celular', 'Email', 'Fecha', 'Cliente Potencial'));

        foreach ( $leads as $lead ) {
            fputcsv($output, array($lead->id, $lead->domain, $lead->name, $lead->company, $lead->phone, $lead->email, $lead->created_at, $lead->potential_client ? 'Sí' : 'No'));
        }
        fclose($output);
        exit;
    }

    // Mostrar la interfaz de la página
    ?>
    <div class="wrap">
        <h1>Leads Generados</h1>
        <form method="post">
            <input type="submit" name="download_csv" class="button button-primary" value="Descargar CSV">
        </form>
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