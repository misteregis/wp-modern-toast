<?php
/*
Plugin Name: Modern Toast
Description: Plugin de Toast moderno com suporte a HTML e pause on hover.
Version: 1.1.0
Author: Misteregis
Author URI:  https://github.com/misteregis/
*/

if (!defined('ABSPATH')) {
    exit;
}

/*--------------------------------------------------------------
# REGISTRAR CONFIGURAÇÕES
--------------------------------------------------------------*/
function modern_toast_sanitize_options($input) {
    return [
        'success'    => sanitize_text_field($input['success'] ?? '#22c55e'),
        'error'      => sanitize_text_field($input['error'] ?? '#ef4444'),
        'info'       => sanitize_text_field($input['info'] ?? '#3b82f6'),
        'bg'         => sanitize_text_field($input['bg'] ?? '#1e293b'),
        'duration'   => intval($input['duration'] ?? 4000),
        'allow_html' => isset($input['allow_html']) ? 1 : 0,
        'custom_css' => wp_strip_all_tags($input['custom_css'] ?? '')
    ];
}

function modern_toast_register_settings() {
    register_setting(
        'modern_toast_group',
        'modern_toast_options',
        'modern_toast_sanitize_options'
    );
}
add_action('admin_init', 'modern_toast_register_settings');

/*--------------------------------------------------------------
# MENU ADMIN
--------------------------------------------------------------*/
function modern_toast_menu() {
    add_options_page(
        'Modern Toast',
        'Modern Toast',
        'manage_options',
        'modern-toast',
        'modern_toast_settings_page'
    );
}
add_action('admin_menu', 'modern_toast_menu');

/*--------------------------------------------------------------
# PÁGINA DE CONFIGURAÇÃO
--------------------------------------------------------------*/
function modern_toast_settings_page() {
    $options = get_option('modern_toast_options');

    ?>
    <div class="wrap">
        <h1>Configurações - Modern Toast</h1>

        <form method="post" action="options.php">
            <?php settings_fields('modern_toast_group'); ?>

            <table class="form-table">
                <tr>
                    <th>Permitir HTML nas mensagens</th>
                    <td>
                        <label>
                            <input type="checkbox" name="modern_toast_options[allow_html]" value="1"
                                <?php checked($options['allow_html'] ?? 0, 1); ?>>
                            Ativar renderização de HTML no toast
                        </label>
                        <p class="description">
                            ⚠️ Cuidado: habilitar HTML pode permitir XSS se o conteúdo vier do usuário.
                        </p>
                    </td>
                </tr>

                <tr>
                    <th>Cor mensagem de sucesso</th>
                    <td>
                        <input type="text"
                               class="modern-toast-color"
                               name="modern_toast_options[success]"
                               value="<?php echo esc_attr($options['success'] ?? '#22c55e'); ?>">
                    </td>
                </tr>

                <tr>
                    <th>Cor mensagem de erro</th>
                    <td>
                        <input type="text"
                               class="modern-toast-color"
                               name="modern_toast_options[error]"
                               value="<?php echo esc_attr($options['error'] ?? '#ef4444'); ?>">
                    </td>
                </tr>

                <tr>
                    <th>Cor mensagem de informação</th>
                    <td>
                        <input type="text"
                               class="modern-toast-color"
                               name="modern_toast_options[info]"
                               value="<?php echo esc_attr($options['info'] ?? '#3b82f6'); ?>">
                    </td>
                </tr>

                <tr>
                    <th>Cor de fundo</th>
                    <td>
                        <input type="text"
                               class="modern-toast-color"
                               name="modern_toast_options[bg]"
                               value="<?php echo esc_attr($options['bg'] ?? '#1e293b'); ?>">
                    </td>
                </tr>

                <tr>
                    <th>Duração padrão (ms)</th>
                    <td>
                        <input type="number" name="modern_toast_options[duration]" value="<?php echo esc_attr($options['duration'] ?? 4000); ?>">
                    </td>
                </tr>

                <tr>
                    <th>CSS Personalizado</th>
                    <td>
                        <textarea name="modern_toast_options[custom_css]" rows="8" cols="50"><?php echo esc_textarea($options['custom_css'] ?? ''); ?></textarea>
                        <p class="description">CSS extra aplicado ao toast.</p>
                    </td>
                </tr>

            </table>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

/*--------------------------------------------------------------
# ENQUEUE FRONTEND
--------------------------------------------------------------*/
function modern_toast_enqueue_assets() {
    wp_enqueue_style(
        'modern-toast-style',
        plugin_dir_url(__FILE__) . 'assets/css/toast.css',
        [],
        '1.1.0'
    );

    wp_enqueue_script(
        'modern-toast-script',
        plugin_dir_url(__FILE__) . 'assets/js/toast.js',
        [],
        '1.1.0',
        true
    );

    // Passar configurações para o JS
    $options = get_option('modern_toast_options');

    wp_localize_script('modern-toast-script', 'ModernToastSettings', [
        'success' => $options['success'] ?? '#22c55e',
        'error' => $options['error'] ?? '#ef4444',
        'info' => $options['info'] ?? '#3b82f6',
        'bg' => $options['bg'] ?? '#1e293b',
        'duration' => intval($options['duration'] ?? 4000),
        'allowHTML'   => isset($options['allow_html']) ? (bool)$options['allow_html'] : false
    ]);
}
add_action('wp_enqueue_scripts', 'modern_toast_enqueue_assets');

function modern_toast_admin_assets($hook) {
    if ($hook !== 'settings_page_modern-toast') {
        return;
    }

    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');

    wp_enqueue_script(
        'modern-toast-admin',
        plugin_dir_url(__FILE__) . 'assets/js/admin.js',
        ['wp-color-picker'],
        '1.0',
        true
    );
}
add_action('admin_enqueue_scripts', 'modern_toast_admin_assets');

/*--------------------------------------------------------------
# CSS DINÂMICO
--------------------------------------------------------------*/
function modern_toast_dynamic_css() {
    $options = get_option('modern_toast_options');

    ?>
    <style>
        :root {
            --success: <?php echo esc_html($options['success'] ?? '#22c55e'); ?>;
            --error: <?php echo esc_html($options['error'] ?? '#ef4444'); ?>;
            --info: <?php echo esc_html($options['info'] ?? '#3b82f6'); ?>;
            --bg: <?php echo esc_html($options['bg'] ?? '#1e293b'); ?>;
        }

        <?php echo esc_html($options['custom_css'] ?? ''); ?>
    </style>
    <?php
}
add_action('wp_head', 'modern_toast_dynamic_css');

/*--------------------------------------------------------------
# CONTAINER
--------------------------------------------------------------*/
function modern_toast_container() {
    echo '<div class="toast-container" id="mt-toast-container"></div>';
}
add_action('wp_footer', 'modern_toast_container');