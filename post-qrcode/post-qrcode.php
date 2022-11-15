<?php

/**
 * Plugin Name:       Post Qrcode
 * Plugin URI:        https://pobitro.me/plugins/post-qrcode
 * Description:       Qrcode will show for every post
 * Version:           1.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Pobitro Mondal
 * Author URI:        https://pobitro.me
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://example.com/my-plugin/
 * Text Domain:       post-qrcode
 * Domain Path:       /languages
 */

// Activation
function pqrc_activation_hook() {
}

register_activation_hook( __FILE__, 'pqrc_activation_hook' );

// Deactivation
function pqrc_deactivation_hook() {
}

register_deactivation_hook( __FILE__, 'pqrc_deactivation_hook' );

//uninstall
function pqrc_uninstall_hook() {
}

register_uninstall_hook( __FILE__, 'pqrc_uninstall_hook' );

function pqrc_loaded_textdomain() {
    load_plugin_textdomain( 'word-count', false, dirname( __FILE__ ) . '/languages' );
}

add_action( 'plugins_loaded', 'pqrc_loaded_textdomain' );

function pqrc_display_qrcode( $content ) {
    $current_post_id    = get_the_ID();
    $current_post_url   = urlencode( get_the_permalink( $current_post_id ) );
    $current_post_title = get_the_title();
    $current_post_type  = get_post_type( $current_post_id );

    // post type check
    $excluded_post_types = apply_filters( 'pqrc_excluded_post_types', array() );
    if ( in_array( $current_post_type, $excluded_post_types ) ) {
        return $content;
    }

    //qrcode dimension
    $width  = get_option( 'pqrc_width' );
    $height = get_option( 'pqrc_height' );

    $width     = $width ? $width : 220;
    $height    = $height ? $height : 220;
    $dimension = apply_filters( 'pqrc_dimension', "{$width}x{$height}" );

    $img_src = sprintf( "https://api.qrserver.com/v1/create-qr-code/?data=%s&size=%s&margin=0", $current_post_url, $dimension );

    $qrcode_title = get_option('pqrc_title');
    $pqrc_title = $qrcode_title ? $qrcode_title : __( 'Scan this QRCode to get the post link easily', 'post-qrcode' );
    $content .= sprintf( "<div class='qrcode'>
    <h5><u>%s</u></h5>
    <img src='%s' alt='%s' />
    </div>", $pqrc_title, $img_src, $current_post_title );

    return $content;
}
add_filter( 'the_content', 'pqrc_display_qrcode' );

// Settings field
function pqrc_general_fields() {

    add_settings_section(
        'pqrc_settings_section',                    // Section ID
        __( 'Posts Qrcode Settings', 'post-qrcode' ), // Section Title
        'pqrc_settings_section_callback',           // Callback
        'general'                                   // What Page?  This makes the section show up on the General Settings Page
    );
    
    add_settings_field(                      // Option 1
        'pqrc_title',                           // Option ID
        __( 'Post Qrcode Title', 'post-qrcode' ), // Label
        'pqrc_display_field_callback',          // !important - This is where the args go!
        'general',                              // Page it will be displayed (General Settings)
        'pqrc_settings_section',                // Name of our section
        array(                                   // The $args
            'pqrc_title',                           // Should match Option ID
        )
    );
    
    add_settings_field(                      // Option 1
        'pqrc_width',                           // Option ID
        __( 'Post Qrcode Width', 'post-qrcode' ), // Label
        'pqrc_display_field_callback',          // !important - This is where the args go!
        'general',                              // Page it will be displayed (General Settings)
        'pqrc_settings_section',                // Name of our section
        array(                                   // The $args
            'pqrc_width',                           // Should match Option ID
        )
    );

    add_settings_field(                       // Option 1
        'pqrc_height',                           // Option ID
        __( 'Post Qrcode Height', 'post-qrcode' ), // Label
        'pqrc_display_field_callback',           // !important - This is where the args go!
        'general',                               // Page it will be displayed (General Settings)
        'pqrc_settings_section',                 // Name of our section
        array(                                    // The $args
            'pqrc_height',                           // Should match Option ID
        )
    );

    register_setting( 'general', 'pqrc_width', 'esc_attr' );
    register_setting( 'general', 'pqrc_height', 'esc_attr' );
    register_setting( 'general', 'pqrc_title', 'esc_attr' );
}

function pqrc_settings_section_callback() { // Section Callback
    echo '<p>' . __( 'Settings for post qrcode plugin', 'post-qrcode' ) . '</p>';
}

function pqrc_display_field_callback( $args ) { // Textbox Callback
    $option = get_option( $args[0] );
    printf( "<input type='text' class='regular-text' id='%s' name='%s' value='%s' />", $args[0], $args[0], $option );
}

add_filter( 'admin_init', 'pqrc_general_fields' );

// Settings link add
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'pqrc_settings_link' );
function pqrc_settings_link( $links ) {
    $links[] = '<a href="' .
    admin_url( 'options-general.php' ) .
    '">' . __( 'Settings' ) . '</a>';
    return $links;
}