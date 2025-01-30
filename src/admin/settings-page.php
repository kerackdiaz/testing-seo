<?php
// Página de configuración del plugin SEO TESTING INCLUP

// Evitar el acceso directo al archivo
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Función para mostrar la página de configuración
function seo_testing_inclup_settings_page() {
    // Verificar si el usuario tiene permisos
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // Guardar opciones si se envía el formulario
    if ( isset( $_POST['seo_testing_inclup_save_settings'] ) ) {
        check_admin_referer( 'seo_testing_inclup_settings_verify' );

        // Guardar las opciones en la base de datos
        update_option( 'seo_testing_inclup_email', sanitize_email( $_POST['seo_testing_inclup_email'] ) );
        update_option( 'seo_testing_inclup_api_token', sanitize_text_field( $_POST['seo_testing_inclup_api_token'] ) );
        update_option( 'seo_testing_inclup_button_text', sanitize_text_field( $_POST['seo_testing_inclup_button_text'] ) );
        update_option( 'seo_testing_inclup_privacy_url', esc_url( $_POST['seo_testing_inclup_privacy_url'] ) );
        update_option( 'seo_testing_inclup_terms_url', esc_url( $_POST['seo_testing_inclup_terms_url'] ) );
        update_option( 'seo_testing_inclup_recaptcha_secret', sanitize_text_field( $_POST['seo_testing_inclup_recaptcha_secret'] ) );
        update_option( 'seo_testing_inclup_clean_db_days', intval( $_POST['seo_testing_inclup_clean_db_days'] ) );

        // Mensaje de éxito
        add_settings_error( 'seo_testing_inclup_messages', 'seo_testing_inclup_message', 'Configuraciones guardadas.', 'updated' );
    }

    // Limpiar la base de datos de leads si se hace clic en el botón
    if ( isset( $_POST['seo_testing_inclup_clean_db'] ) ) {
        $days = intval( get_option( 'seo_testing_inclup_clean_db_days', 30 ) );
        $deleted_rows = seo_testing_inclup_cleanup_old_leads( $days );
        add_settings_error( 'seo_testing_inclup_messages', 'seo_testing_inclup_message', "$deleted_rows leads eliminados.", 'updated' );
    }

    // Obtener las opciones actuales
    $email = get_option( 'seo_testing_inclup_email', get_option( 'admin_email' ) );
    $api_token = get_option( 'seo_testing_inclup_api_token', '' );
    $button_text = get_option( 'seo_testing_inclup_button_text', 'Ver toda la info' );
    $privacy_url = get_option( 'seo_testing_inclup_privacy_url', '' );
    $terms_url = get_option( 'seo_testing_inclup_terms_url', '' );
    $recaptcha_secret = get_option( 'seo_testing_inclup_recaptcha_secret', '' );
    $clean_db_days = get_option( 'seo_testing_inclup_clean_db_days', 30 );

    // Mostrar el formulario de configuración
    ?>
    <div class="wrap">
        <h1>Configuración de SEO TESTING INCLUP</h1>
        <?php settings_errors( 'seo_testing_inclup_messages' ); ?>
        <form method="post" action="">
            <?php wp_nonce_field( 'seo_testing_inclup_settings_verify' ); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Correo para recibir leads</th>
                    <td>
                        <input type="email" name="seo_testing_inclup_email" value="<?php echo esc_attr( $email ); ?>" />
                        <p class="description">Dejar en blanco para usar el correo de administración de WordPress: <?php echo get_option( 'admin_email' ); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Token de la API de PageSpeed</th>
                    <td><input type="text" name="seo_testing_inclup_api_token" value="<?php echo esc_attr( $api_token ); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Texto del botón</th>
                    <td><input type="text" name="seo_testing_inclup_button_text" value="<?php echo esc_attr( $button_text ); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">URL de políticas de privacidad</th>
                    <td><input type="url" name="seo_testing_inclup_privacy_url" value="<?php echo esc_url( $privacy_url ); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">URL de términos y condiciones</th>
                    <td><input type="url" name="seo_testing_inclup_terms_url" value="<?php echo esc_url( $terms_url ); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Token de reCAPTCHA</th>
                    <td><input type="text" name="seo_testing_inclup_recaptcha_secret" value="<?php echo esc_attr( $recaptcha_secret ); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Días para limpiar la base de datos</th>
                    <td><input type="number" name="seo_testing_inclup_clean_db_days" value="<?php echo esc_attr( $clean_db_days ); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Limpiar base de datos de leads</th>
                    <td>
                        <form method="post" action="">
                            <?php wp_nonce_field( 'seo_testing_inclup_settings_verify' ); ?>
                            <input type="submit" name="seo_testing_inclup_clean_db" class="button button-secondary" value="Limpiar ahora">
                        </form>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Shortcode</th>
                    <td>
                        <input type="text" id="seo-testing-inclup-shortcode" value="[seo_testing_inclup_form]" readonly />
                        <button type="button" class="button button-secondary" onclick="copyShortcode()">Copiar Shortcode</button>
                    </td>
                </tr>
            </table>
            <?php submit_button( 'Guardar configuraciones', 'primary', 'seo_testing_inclup_save_settings' ); ?>
        </form>
    </div>
    <script>
        function copyShortcode() {
            var copyText = document.getElementById("seo-testing-inclup-shortcode");
            copyText.select();
            copyText.setSelectionRange(0, 99999); // Para dispositivos móviles
            document.execCommand("copy");
            alert("Shortcode copiado: " + copyText.value);
        }
    </script>
    <?php
}
?>