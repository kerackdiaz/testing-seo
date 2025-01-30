<?php
// Este archivo gestiona la integración de Google reCAPTCHA para evitar el spam en los formularios de leads.

function seo_testing_inclup_recaptcha_load() {
    ?>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <?php
}
add_action('wp_head', 'seo_testing_inclup_recaptcha_load');

function seo_testing_inclup_recaptcha_verify($response) {
    $secret_key = get_option('seo_testing_inclup_recaptcha_secret');
    $remote_ip = $_SERVER['REMOTE_ADDR'];
    $url = 'https://www.google.com/recaptcha/api/siteverify';

    $response = wp_remote_post($url, array(
        'body' => array(
            'secret' => $secret_key,
            'response' => $response,
            'remoteip' => $remote_ip
        )
    ));

    $body = wp_remote_retrieve_body($response);
    $result = json_decode($body);

    return isset($result->success) && $result->success;
}
?>