<?php

/**
 * @link              http://localleadminer.com/
 * @since             1.0.0
 * @package           Pal_Advanced
 *
 * @wordpress-plugin
 * Plugin Name:       PayPal Advanced for Woo
 * Plugin URI:        http://localleadminer.com/
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            wpremiumdev
 * Author URI:        http://localleadminer.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woo-paypal-advanced
 * Domain Path:       /languages
 */
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-pal-advanced-activator.php
 */
function activate_pal_advanced() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-pal-advanced-activator.php';
    Pal_Advanced_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-pal-advanced-deactivator.php
 */
function deactivate_pal_advanced() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-pal-advanced-deactivator.php';
    Pal_Advanced_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_pal_advanced');
register_deactivation_hook(__FILE__, 'deactivate_pal_advanced');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-pal-advanced.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_pal_advanced() {

    $plugin = new Pal_Advanced();
    $plugin->run();
}

add_action('plugins_loaded', 'load_pal_advanced');

function load_pal_advanced() {
    run_pal_advanced();
}
