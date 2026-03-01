<?php
/*
Plugin Name: Modern Toast
Description: Plugin de Toast moderno com suporte a HTML e pause on hover.
Version: 1.0.0
Author: Misteregis
Author URI:  https://github.com/misteregis/
*/

if (!defined('ABSPATH')) {
    exit;
}

// Enqueue CSS e JS
function modern_toast_enqueue_assets() {
    wp_enqueue_style(
        'modern-toast-style',
        plugin_dir_url(__FILE__) . 'assets/css/toast.css',
        [],
        '1.0'
    );

    wp_enqueue_script(
        'modern-toast-script',
        plugin_dir_url(__FILE__) . 'assets/js/toast.js',
        [],
        '1.0',
        true
    );
}
add_action('wp_enqueue_scripts', 'modern_toast_enqueue_assets');

// Container automático no footer
function modern_toast_container() {
    echo '<div class="toast-container" id="mt-toast-container"></div>';
}
add_action('wp_footer', 'modern_toast_container');

// Shortcode para disparar toast
function modern_toast_shortcode($atts, $content = null) {

    $atts = shortcode_atts([
        'type' => 'info',
        'duration' => 4000
    ], $atts);

    $message = do_shortcode($content);

    return '<button onclick="ModernToast.show(`' . esc_js($message) . '`, \'' . esc_js($atts['type']) . '\', ' . intval($atts['duration']) . ', true)">Mostrar Toast</button>';
}
add_shortcode('modern_toast', 'modern_toast_shortcode');