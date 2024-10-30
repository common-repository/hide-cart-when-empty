<?php
/**
 * Plugin Name: WooCommerce Hide Cart When Empty - WP Fix It
 * Description:  No need to show your WooCommerce shopping cart icon when the shopping cart is empty. This will hide the cart icon on your site until something is added to it.
 * Version:      2.2
 * Author:       WP Fix It
 * Author URI:   https://www.wpfixit.com
 * Text Domain: hide-cart
 */
// Check if WooCommerce is active
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	function wpfi_wc_hide_cart_message_needed_notice() {
		$message = sprintf(

		/* translators: Placeholders: %1$s and %2$s are <strong> tags. %3$s and %4$s are <a> tags */

			esc_html__( '%1$sWooCommerce Hide Cart When Empty %2$s requires WooCommerce to function. Please %3$sinstall WooCommerce%4$s.', 'woocommerce_seal' ),

			'<strong>',

			'</strong>',

			'<a href="' . admin_url( 'plugins.php' ) . '">',

			'&nbsp;&raquo;</a>'

		);

		echo sprintf( '<div class="error"><p>%s</p></div>', $message );
	}
	add_action( 'admin_notices', 'wpfi_wc_hide_cart_message_needed_notice' );
	return;
}
//Load up styling for plugin needs
function wpfi_hide_cart_css() {
    wp_enqueue_style( 'myCSS', plugins_url( 'hc.css', __FILE__ ) );
}
add_action('admin_print_styles', 'wpfi_hide_cart_css');

//Add Cart Icon Tab in WooCommerce Settings
class WC_wpfi_hide_cart_Tab {
    public static function init() {
        add_filter( 'woocommerce_settings_tabs_array', __CLASS__ . '::add_settings_tab', 50 );
        add_action( 'woocommerce_settings_tabs_hide_cart', __CLASS__ . '::settings_tab' );
        add_action( 'woocommerce_update_options_hide_cart', __CLASS__ . '::update_settings' );
    }
    public static function add_settings_tab( $settings_tabs ) {
        $settings_tabs['hide_cart'] = __( 'Cart Icon', 'wc_settings_wpfi_hide_cart_tab' );
        return $settings_tabs;
    }
    public static function settings_tab() {
        woocommerce_admin_fields( self::get_settings() );
    }
    public static function update_settings() {
        woocommerce_update_options( self::get_settings() );
    }
    public static function get_settings() {
    	
       //$settings_hidecart = $settings;
		$settings_hidecart[] = array( 
		'name' => __( 'Hide Cart Icon Settings', 'text-domain' ), 
		'type' => 'title', 
		'desc' => __( 'The following option is used to set the class for your WooCommerce cart icon.<br><br>View the detailed post at <a href="https://wordpress.com/support/how-to-find-your-themes-css/" target="_blank">THIS LINK</a> to see how you can track down the CSS class or id of the cart icon you wish to hide on your site.', 'text-domain' ), 
		'id' => 'wchidecart' );
		
		// Add first checkbox option
		$settings_hidecart[] = array(
			'name'     => __( 'Cart Icon Class or ID Name' ),
			'desc_tip' => __( 'Enter the CSS class or id name of your cart icon.', 'text-domain' ),
			'id'       => 'wpfihidecartclass',
			'type'     => 'text',
			'css'      => 'max-width:200px;',
			'desc'     => __( '<span class="wccm-default">Example:</span> .cart_icon', 'text-domain' ),
		);
	
		
		$settings_hidecart[] = array( 'type' => 'sectionend', 'id' => 'wchidecart' );
		return $settings_hidecart;
		
        return apply_filters( 'wc_wpfi_hide_cart_tab_settings', $settings );
    }
}
WC_wpfi_hide_cart_Tab::init();

// Hide WooCommerce Cart Icon When Empty
add_action( 'wp_footer', function() {
    if ( WC()->cart->is_empty() ) {
wp_enqueue_style( 'wpfi_hide_cart_CSS', plugins_url( 'hc.css', __FILE__ ) );
        $hide_cart_class = get_option( 'wpfihidecartclass' );
        $hide_cart_css = "
                $hide_cart_class{display: none}";
        wp_add_inline_style( 'wpfi_hide_cart_CSS', $hide_cart_css );
}
});
/* Activate the plugin and do something. */
register_activation_hook( __FILE__, 'wc_hide_cart_welcome_message' );
function wc_hide_cart_welcome_message() {
set_transient( 'wc_hide_cart_welcome_message_notice', true, 5 );
}
add_action( 'admin_notices', 'wc_hide_cart_welcome_message_notice' );
function wc_hide_cart_welcome_message_notice(){
/* Check transient, if available display notice */
if( get_transient( 'wc_hide_cart_welcome_message_notice' ) ){
?>
<div class="updated notice is-dismissible">
	<style>div#message {display: none}</style>
<p>&#127881; <strong>WP Fix It - Hide Cart When Empty</strong> has been activated and you can now hide your cart icon until something is added.
</div>
<?php
/* Delete transient, only display this notice once. */
delete_transient( 'wc_hide_cart_welcome_message_notice' );
}
}
// Add settings link to plugin details
function wpfi_wc_hide_cart_message_plugin_action_links( $links ) {
$links = array_merge( array(
'<a href="' . esc_url( admin_url( '/admin.php?page=wc-settings&tab=hide_cart' ) ) . '">' . __( '<b>Settings</b>', 'textdomain' ) . '</a>'
), $links );
$links = array_merge( array(

'<a href="https://www.wpfixit.com/" target="_blank">' . __( '<span id="p-icon" class="dashicons dashicons-cart"></span> <span class="ticket-link" >GET HELP</span>', 'textdomain' ) . '</a>'
), $links );

return $links;
}
add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wpfi_wc_hide_cart_message_plugin_action_links' );
