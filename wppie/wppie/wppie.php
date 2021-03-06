<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * Dashboard. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/japorito/SimplePie-WordPress-Plugin
 * @since             0.0.1
 * @package           WordPress_Pie
 *
 * @wordpress-plugin
 * Plugin Name:       WordPress Pie
 * Plugin URI:        https://github.com/japorito/SimplePie-WordPress-Plugin
 * Description:       This is a very simple RSS client plugin for wordpress. Allows embedding RSS feeds via shortcode.
 * Version:           0.0.1
 * Author:            Jacob Saporito
 * Author URI:        https://github.com/japorito/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wppie
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wppie-activator.php
 */
function activate_wppie() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wppie-activator.php';
	WordPress_Pie_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wppie-deactivator.php
 */
function deactivate_wppie() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wppie-deactivator.php';
	WordPress_Pie_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wppie' );
register_deactivation_hook( __FILE__, 'deactivate_wppie' );

/**
 * The core plugin class that is used to define internationalization,
 * dashboard-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wppie.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    0.0.1
 */
function run_wppie() {

	$plugin = new WordPress_Pie();
	$plugin->run();

}
run_wppie();
