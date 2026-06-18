<?php
/*
	plugin images functions
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
 * Custom plugin image path.
 */
function custom_image_path( $path ) {
    if (strpos($path, PLUGIN_SLUG) !== false){
        $path = plugin_dir_path( PLUGIN_FILE ).'assets/images';
    }
    return $path;
}

/**
 * Custom plugin image url.
 */
function custom_image_url( $url ) {
    if (strpos($url, PLUGIN_SLUG) !== false){
        $url = plugin_dir_url(__FILE__).'assets/images';
    }
    return $url;
}
