<?php
/**
 * @package neo_book_and_pay
 * @version 1.0
 * Plugin Name: Neo Book and Pay - Group Addon
 * Plugin Dependencies: neo-book-and-pay/neo-book-and-pay.php
 * Description: This is an addon for Neo Book and Pay which allows group booking.
 * Author: Neoventis
 * Version: 1.0
 * Author URI: http://www.neobookandpay.com/
 * Text Domain: neo-book-and-pay-group
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html 
 * Domain Path: /languages
*/

// exist if directly accessed.
defined( "ABSPATH" ) || exit;

define("NBAP_GRP_LOCATION_PATH", dirname( __FILE__));
define("NBAP_GRP_LOCATION_URL", plugins_url( "", __FILE__));
define("NBAP_GRP_PLUGIN_VERSION", "1.0");
define("NBAP_GRP_PLUGIN_PREFIX", "nbap_");
define("NBAP_GRP_DEPENDENCY", "neo-book-and-pay/neo-book-and-pay.php");

register_activation_hook(__FILE__, 'nbap_group_activate');
function nbap_group_activate() {
	include_once ABSPATH . 'wp-admin/includes/plugin.php';
    $required_version    = '1.0';
    if (!is_plugin_active(NBAP_GRP_DEPENDENCY)) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die('Neo Book and Pay plugin must be active for the addon to work.');
    }

    $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . NBAP_GRP_DEPENDENCY);
    if (version_compare($plugin_data['Version'], $required_version, '<')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(esc_html('Required plugin version is too old. Please update to at least version ' . $required_version . '.'));
    }

	global $wpdb;
	$table_name = $wpdb->prefix .NBAP_GRP_PLUGIN_PREFIX . 'addon';
	$directory = basename(NBAP_GRP_LOCATION_PATH);	
	$data = [
		'title' => 'Group Addon',
		'directory' => $directory,
		'version' => 1,
		'status' => 1,
	];
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM %i WHERE directory = %s", $table_name, $directory));
	if ($exists) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->update($table_name, $data, [ 'directory' => $directory ]);
	} else {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->insert($table_name, $data);
	}
}

register_deactivation_hook(__FILE__, 'nbap_group_deactivate');
function nbap_group_deactivate() {
	global $wpdb;
	$table_name = $wpdb->prefix .NBAP_GRP_PLUGIN_PREFIX . 'addon';
	$data = [ 'status' => 0 ];
	$where = [ 'directory' => basename(NBAP_GRP_LOCATION_PATH) ];
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$wpdb->update($table_name, $data, $where);
}

include_once NBAP_GRP_LOCATION_PATH . "/includes/filters.php";	
include_once NBAP_GRP_LOCATION_PATH . "/includes/actions.php";