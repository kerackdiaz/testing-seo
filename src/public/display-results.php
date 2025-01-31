<?php
// Mostrar resultados limitados de la verificación del dominio
function seo_testing_inclup_display_results($results) {
    if (empty($results)) {
        echo '<p>No se encontraron resultados para el dominio ingresado.</p>';
        return;
    }

    // Mostrar resultados limitados
    echo '<div class="seo-results">';
    echo '<h2>Resultados de PageSpeed Insights</h2>';
    echo '<p>Dominio: ' . esc_html($results['url']) . '</p>';
    echo '<p>Puntuación: ' . esc_html($results['score']) . '</p>';
    echo '<button id="view-full-info" class="button">Ver toda la info</button>';
    echo '</div>';

    // Popup para mostrar información completa
    echo '<div id="full-info-popup" style="display:none;">';
    echo '<h3>Información Completa</h3>';
    foreach ($results['metrics'] as $metric) {
        echo '<p>' . esc_html($metric['title']) . ': ' . esc_html($metric['displayValue']) . '</p>';
    }
    echo '<form id="lead-form">';
    echo '<label for="name">Nombre:</label>';
    echo '<input type="text" id="name" name="name" required>';
    echo '<label for="company">Nombre de la empresa:</label>';
    echo '<input type="text" id="company" name="company" required>';
    echo '<label for="phone">Celular:</label>';
    echo '<input type="text" id="phone" name="phone" required>';
    echo '<label for="email">Email:</label>';
    echo '<input type="email" id="email" name="email" required>';
    echo '<label for="terms">Acepto las <a href="' . esc_url(get_option('seo_testing_inclup_terms_url')) . '" target="_blank">políticas de tratamiento de datos</a> y <a href="' . esc_url(get_option('seo_testing_inclup_privacy_url')) . '" target="_blank">términos y condiciones</a>:</label>';
    echo '<input type="checkbox" id="terms" name="terms" required>';
    echo '<button type="submit" class="button">Enviar</button>';
    echo '</form>';
    echo '<button id="close-popup" class="button">Cerrar</button>';
    echo '</div>';

    // Añadir el HTML para el loading (debe estar fuera del popup y visible desde el principio)
    echo '<div id="loading-animation" style="display:none;">
            <div class="spinner"></div>
          </div>';
}

// Enqueue scripts para manejar el popup
function seo_testing_inclup_enqueue_popup_scripts() {
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const viewFullInfoButton = document.getElementById('view-full-info');
            const fullInfoPopup = document.getElementById('full-info-popup');
            const closePopupButton = document.getElementById('close-popup');
            const leadForm = document.getElementById('lead-form');

            if (viewFullInfoButton) {
                viewFullInfoButton.addEventListener('click', function() {
                    fullInfoPopup.style.display = 'block';
                });
            }

            if (closePopupButton) {
                closePopupButton.addEventListener('click', function() {
                    fullInfoPopup.style.display = 'none';
                });
            }

            if (leadForm) {
                leadForm.addEventListener('submit', function(event) {
                    event.preventDefault();
                    // Aquí puedes agregar la lógica para enviar el formulario y almacenar el lead
                    fullInfoPopup.style.display = 'none';
                    alert('Formulario enviado. Ahora puedes ver la información completa.');
                });
            }
        });
    </script>
    <?php
}
add_action('wp_footer', 'seo_testing_inclup_enqueue_popup_scripts');
?>