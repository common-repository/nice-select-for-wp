<?php
// Prevent Direct access
if (!defined('ABSPATH')) {
    exit;
}
class NSFW_NiceSelectFrontend {
    public function __construct() {
        // Add action to enqueue scripts and styles on the front end
        add_action('wp_enqueue_scripts', array($this, 'nsfw_frontend_enqueue_scripts'));
    }

    public function nsfw_frontend_enqueue_scripts() {
        // Enqueue Nice Select CSS
        wp_enqueue_style('nice-select-css', plugin_dir_url(__FILE__) . '../assets/css/nice-select.css', array(), '1.0', 'all');

        // Enqueue Nice Select JavaScript
        wp_enqueue_script('nice-select-js', plugin_dir_url(__FILE__) . '../assets/js/jquery.nice-select.min.js', array('jquery'), '1.0', true);

        // Enqueue custom JavaScript for Nice Select
        wp_enqueue_script('nice-select-main-js', plugin_dir_url(__FILE__) . '../assets/js/nice-select-main.js', array('jquery'), time(), true);

        // Localize script with data to be passed to Nice Select JavaScript
        $nice_select_data = array(
            "selector" => get_option("selector"),
            "alignment" => get_option("alignment"),
            "fullWidth" => get_option("fullWidth"),
            "placeholder_text" => get_option("placeholder_text"),
            'custom_css' => get_option("custom_css")
        );
        wp_localize_script( 'nice-select-main-js', 'niceSelectData', $nice_select_data );
    }
}

// Instantiate NiceSelectFrontend class only if not in admin panel
if (!is_admin()) {
    new NSFW_NiceSelectFrontend();
}
