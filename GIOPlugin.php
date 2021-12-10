<?php
/**
 * Plugin Name: GETitOUT Media Exporter
 * Description: This plugin will make GETitOUT able to export your custom content to wordpress, it will allow basic authentication through the wordpress REST API, as well as uploading different types of files to your wordpress media library. It's main use will be exporting your custom GETitOUT landing pages to your own wordpress server.
 * Author: GETitOUT.io
 * Author URI: https://GETitOUT.io
 * Version: 0.1
 * Plugin URI: https://github.com/GETitOUT-io/gio-send-custom-html-wordpress-plugin
 */

// Allows connecting to the REST API via username and password basic auth
function handleAuth( $user ) {
	global $auth;

	$auth = null;

	if ( ! empty( $user ) ) {
		return $user;
	}

	if ( !isset( $_SERVER['PHP_AUTH_USER'] ) ) {
		return $user;
	}

	$username = $_SERVER['PHP_AUTH_USER'];
	$password = $_SERVER['PHP_AUTH_PW'];


	remove_filter( 'determine_current_user', 'handleAuth', 20 );

	$user = wp_authenticate( $username, $password );

	add_filter( 'determine_current_user', 'handleAuth', 20 );

	if ( is_wp_error( $user ) ) {
		$auth = $user;
		return null;
	}

	$auth = true;

	return $user->ID;
}
add_filter( 'determine_current_user', 'handleAuth', 20 );

function handleAuthErrors( $error ) {
	if ( ! empty( $error ) ) {
		return $error;
	}

	global $auth;

	return $auth;
}
add_filter( 'rest_authentication_errors', 'handleAuthErrors' );






///////////////////////////////// CREATION /////////////////////////////////////

//Adds ALLOW_UNFILTERED_UPLOADS to the wp_config.php file
register_activation_hook(__FILE__, 'updateWpConfigMediaSettings');

function updateWpConfigMediaSettings() {
    add_option('runChanges',"1");
}

add_action('admin_init', 'launch_activation_script');

function launch_activation_script() {

    if (get_option('runChanges') == "1") {

        if ( file_exists (ABSPATH . "wp-config.php") && is_writable (ABSPATH . "wp-config.php") ){
            wp_config_put();
        }
        else if (file_exists (dirname (ABSPATH) . "/wp-config.php") && is_writable (dirname (ABSPATH) . "/wp-config.php")){
            wp_config_put('/');
        }
        // else { 
        //     add_warning('Error adding');
        // }
         delete_option('runChanges');
        
        
    }
}


function wp_config_put( $slash = '' ) {
    $config = file_get_contents (ABSPATH . "wp-config.php");
    $config = preg_replace ("/^([\r\n\t ]*)(\<\?)(php)?/i", "<?php define( 'ALLOW_UNFILTERED_UPLOADS', true );", $config);
    file_put_contents (ABSPATH . $slash . "wp-config.php", $config);
}







///////////////////////////////// DELETION /////////////////////////////////////

//removes ALLOW_UNFILTERED_UPLOADS to the wp_config.php file
register_deactivation_hook(__FILE__, 'rollbackWpConfigMediaSettingsChanges');

function rollbackWpConfigMediaSettingsChanges() {
    add_option('runRollback',"1");
}

add_action('deactivate_plugin', 'delete_activation_script');

function delete_activation_script() {
    if (get_option('runRollback') == "1") {
        if (file_exists (ABSPATH . "wp-config.php") && is_writable (ABSPATH . "wp-config.php")) {
            wp_config_delete();
        }
        else if (file_exists (dirname (ABSPATH) . "/wp-config.php") && is_writable (dirname (ABSPATH) . "/wp-config.php")) {
            wp_config_delete('/');
        }
        // else if (file_exists (ABSPATH . "wp-config.php") && !is_writable (ABSPATH . "wp-config.php")) {
        //     add_warning('Error removing');
        // }
        // else if (file_exists (dirname (ABSPATH) . "/wp-config.php") && !is_writable (dirname (ABSPATH) . "/wp-config.php")) {
        //     add_warning('Error removing');
        // }
        // else {
        //     add_warning('Error removing');
        // }
        delete_option('runRollback');


    }
}


function wp_config_delete( $slash = '' ) {

    $config = file_get_contents (ABSPATH . "wp-config.php");
    $config = preg_replace ("/( ?)(define)( ?)(\()( ?)(['\"])ALLOW_UNFILTERED_UPLOADS(['\"])( ?)(,)( ?)(0|1|true|false)( ?)(\))( ?);/i", "", $config);
    file_put_contents (ABSPATH . $slash . "wp-config.php", $config);
}





