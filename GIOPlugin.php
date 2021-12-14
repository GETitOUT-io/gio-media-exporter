<?php
/**
 * Plugin Name: GETitOUT Media Exporter
 * Description: This plugin will make GETitOUT able to export your custom content to wordpress, it will allow basic authentication through the wordpress REST API, as well as uploading different types of files to your wordpress media library. It's main use will be exporting your custom GETitOUT landing pages to your own wordpress server.
 * Author: GETitOUT.io
 * Author URI: https://GETitOUT.io
 * Version: 1.0
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


//API fields
 
add_action( 'rest_api_init', 'WPCHTML_create_api_fields' ); //or whatever name you give to the function below
 
function WPCHTML_create_api_fields() {
 
    //register_rest_field ( 'name-of-post-type', 'name-of-field-to-return', array-of-callbacks-and-schema() )
    //for custom permalink
    register_rest_field( 'wpchtmlp_page', 'html_permalink', array(
           'get_callback'    => 'WPCHTMLP_get_api_post_meta_permalink', //or whatever you name the callback function below
           'update_callback' => 'WPCHTMLP_update_api_post_meta_permalink', //name of update callback function at bottom
           'schema'          => null,
        )
    );
    //for HTML content
    register_rest_field( 'wpchtmlp_page', 'html_code', array(
           'get_callback'    => 'WPCHTMLP_get_api_post_meta_code', //or whatever you name the callback function below
           'update_callback' => 'WPCHTMLP_update_api_post_meta_code', //name of update callback function at bottom
           'schema'          => null,
        )
    );
}
 
function WPCHTMLP_get_api_post_meta_permalink( $object, $field_name, $request ) {
    $meta = get_post_meta( $object['id'], 'WPCHTMLP_page_meta_box', true );
    //return the post meta
    return $meta['html_permalink'];
}
 
function WPCHTMLP_get_api_post_meta_code( $object, $field_name, $request ) {
    $meta = get_post_meta( $object['id'], 'WPCHTMLP_page_meta_box', true );
    //return the post meta
    return $meta['html_code'];
}
 
function WPCHTMLP_update_api_post_meta_permalink($value, $object, $field_name){
  return update_post_meta($object['id'], 'WPCHTMLP_page_meta_box', array('html_permalink', $value));
}
 
function WPCHTMLP_update_api_post_meta_code($value, $object, $field_name){
 return update_post_meta($object['id'], 'WPCHTMLP_page_meta_box', array('html_code', $value));
}


