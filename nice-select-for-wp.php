<?php
/*
Plugin Name: Nice select for WP
Plugin URI: https://github.com/Rupashdas/nice-select-for-wp
Description: Enhance select elements with a nicer UI using nice select library.
Version: 1.0.1
Author: Devrupash
Author URI: https://devrupash.com
Requires at least: 4.0
Requires PHP: 5.2
Tested up to: 6.4.3
Text Domain: nice-select-for-wp
Domain Path: /languages/
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/
// Prevent Direct access
if (!defined('ABSPATH')) {
    exit;
}
require_once plugin_dir_path(__FILE__) . 'inc/nice_select_frontend.php';
require_once plugin_dir_path(__FILE__) . 'inc/nice_select_admin.php';

function nsfw_plugin_settings_link($links){
    // Add link to the plugin settings page
    $nice_select_new_link = sprintf("<a href='%s'>%s</a>", "options-general.php?page=niceselect", __("Settings", "nice-select-for-wp" ));
    $links[] = $nice_select_new_link;
    return $links;
}
add_filter( 'plugin_action_links_'.plugin_basename(__FILE__), 'nsfw_plugin_settings_link' );

