<?php
/**
 *  Menu Version 4.0
 */

/**
 * Declare the Namespace.
 */
namespace azurecurve\SMTP;

/**
 * Prevent direct access.
 */
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Populate list of available azurecurve plugins.
 *
 * @since 1.0.0
 */
function populate_azurecurve_menu() {

	// Direct file path to THIS plugin's version of the manifest file
	$manifest_path = plugin_dir_path( __FILE__ ) . 'azurecurve-plugins.php';

	if ( ! file_exists( $manifest_path ) ) {
		return;
	}

	// Use require (not require_once) so PHP doesn't skip execution if previously loaded elsewhere
	require $manifest_path;

	// Verify $azurecurve_plugins array was set inside the included file
	if ( empty( $azurecurve_plugins ) || ! is_array( $azurecurve_plugins ) ) {
		return;
	}

	// Safely fetch existing menu option, defaulting to an empty array
	$plugin_menu = get_option( 'azrcrv-plugin-menu', array() );
	if ( ! is_array( $plugin_menu ) ) {
		$plugin_menu = array();
	}

	$original_menu = $plugin_menu;
	
	// Only update entries if the file's entry is strictly newer, or if it doesn't exist yet
	foreach ( $azurecurve_plugins as $plugin_name => $plugin_details ) {
		
		// Ignore malformed entries.
		if ( ! is_array( $plugin_details ) || empty( $plugin_details['updated'] ) ) {
			continue;
		}
		
		// New plugin - simply add it.
		if ( ! isset( $plugin_menu[ $plugin_name ] ) ) {
			$plugin_menu[ $plugin_name ] = $plugin_details;
			continue;
		}

		$existing_updated = strtotime( $plugin_menu[ $plugin_name ]['updated'] ?? '' );
		$new_updated      = strtotime( $plugin_details['updated'] );

		// Update only if the new entry has a valid, newer timestamp.
		if ( false !== $new_updated && ( false === $existing_updated || $new_updated > $existing_updated ) ) {
			$plugin_menu[ $plugin_name ] = $plugin_details;
		}
	}

	ksort( $plugin_menu );

	if ( $plugin_menu !== $original_menu ) {
		update_option( 'azrcrv-plugin-menu', $plugin_menu );
	}
	
}

/**
 * Hook into Plugin Activation in ClassicPress.
 */
register_activation_hook( PLUGIN_FILE, __NAMESPACE__ . '\\populate_azurecurve_menu' );

/**
 * Hook into Plugin Upgrades in ClassicPress.
 *
 * @param \WP_Upgrader $upgrader_object Upgrader instance.
 * @param array        $options         Array of bulk item update data.
 */
function on_azurecurve_plugin_upgrade( $upgrader_object, $options ) {

	if ( 'update' !== ( $options['action'] ?? '' ) || 'plugin' !== ( $options['type'] ?? '' ) ) {
		return;
	}

	$this_plugin = plugin_basename( PLUGIN_FILE );

	// Bulk updates: $options['plugins'] is an array of basenames.
	// Single updates: $options['plugin'] is one basename.
	$updated_plugins = array();
	if ( ! empty( $options['plugins'] ) && is_array( $options['plugins'] ) ) {
		$updated_plugins = $options['plugins'];
	} elseif ( ! empty( $options['plugin'] ) ) {
		$updated_plugins = array( $options['plugin'] );
	}

	if ( ! in_array( $this_plugin, $updated_plugins, true ) ) {
		return;
	}

	populate_azurecurve_menu();
}
add_action( 'upgrader_process_complete', __NAMESPACE__ . '\\on_azurecurve_plugin_upgrade', 10, 2 );