<?php
/*
Plugin Name: Honeypot Anti Spam for Forminator Forms
Version: 0.2.2
Description: New type of honeypot anti spam for Forminator Forms.
Author: KGM Servizi
Author URI: https://kgmservizi.com
Text Domain: honeypot-for-forminator-form
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

// GLOBALS
$GLOBALS['hfff_version']	 = '0.2.1';
$GLOBALS['hfff_text_domain'] = 'honeypot-for-forminator-form';
$GLOBALS['hfff_field_name']  = get_option('hfff_field_name');
$GLOBALS['hfff_log']         = get_option('hfff_log');

// Installation
register_activation_hook( __FILE__, 'hfff_plugin_activation' );
function hfff_plugin_activation(){
    add_option( 'hfff_field_name', hfff_field_name_generator() );
    add_option( 'hfff_log', 0 );
}
// Field name generator
function hfff_field_name_generator() {
	$array_name = ['name', 'surname', 'email', 'phone', 'telephone', 'mobile'];
	return $array_name[ array_rand( $array_name ) ] . rand(100,999);
}

// Uninstallation
register_uninstall_hook( __FILE__, 'hfff_plugin_uninstall' );
function hfff_plugin_uninstall() {
    delete_option( 'hfff_field_name' );
    delete_option( 'hfff_log' );
}

// Enqueue JS for add field
add_action( 'wp_enqueue_scripts', 'hfff_assets' );
function hfff_assets() {
    $settings = array(
        'hfff_field_name' => esc_attr( $GLOBALS['hfff_field_name'] ),
    );

    wp_register_script( 'hfff-script', plugins_url( '/js/honey.js' , __FILE__ ), array ( 'jquery' ), $GLOBALS['hfff_version'], true );
    wp_add_inline_script( 'hfff-script', 'hfff_settings = ' . wp_json_encode( $settings ) );
    wp_enqueue_script( 'hfff-script' );
}

// Validation
add_filter('forminator_custom_form_submit_errors', 'hfff_form_validation', 10, 3);
function hfff_form_validation( $submit_errors, $form_id, $field_data_array ) {
    $valid      = true; // no spam
    $field_name = $GLOBALS['hfff_field_name'];

    if ( 'not-exists' === get_option( 'hfff_log', 'not-exists' ) ) {
        add_option( 'hfff_log', 0 );
        $log = get_option('hfff_log');
    } else {
        $log = $GLOBALS['hfff_log'];
    }

    // First check because some bots don't load JS so don't load honeypot field!
    if ( $_SERVER['REQUEST_METHOD'] == 'POST' && !array_key_exists( $field_name, $_POST ) ) {
        $valid = false; // spam
    } else {
        // Second check because if field is filled it is a bot
        if ( !empty( $_POST[$field_name] ) ) {
            $valid = false; // spam
        }
    }
    if ( $valid == false ) {
        $submit_errors[]['email-1'] = 'Seems there is an error, retry or send a message via email.';
        update_option( 'hfff_log', ++$log );
    }

    return $submit_errors;
}

// Load options page
add_action( 'plugins_loaded', 'hfff_after_plugin_load' );
function hfff_after_plugin_load() {
    include 'includes/options-page.php';
}
