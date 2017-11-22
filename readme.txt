=== WC Gift Packaging ===
Contributors: Grandy
Tags: Woocommerce, Checkout, Gift, Packaging, Wrapping
Requires at least: 3.0
Tested up to: 4.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Copyright: Arpit Tambi
Copyright URI: http://aheadzen.com/
Stable tag: 1.2

This plugin adds a 'Send this order packaged as gift' option on the WooCommerce checkout.


== Description ==

WC Gift Packaging allows your customers to send their orders packaged as gift. For this a checkbox is added to the WooCommerce checkout to allow the customers to choose if the order is a gift or not. It is optionally possible to set a price for the packaging.

If you want multiple gift packagings and other fancy stuff this is not the plugin for you.

This plugin is a fork of the [WooCommerce Gift Options Plugin](https://de.wordpress.org/plugins/woocommerce-gift-options/) by [Arpit Tambi](https://profiles.wordpress.org/aheadzen/).


== Frequently Asked Questions ==

= Is it possible to add a price for the packaging  =

Yes. You can change this under Woocommerce > Settings > Checkout in the 'Gift packaging cost' field.


= Is it possible to change the position of the checkbox =

Yes. You can change the hook of the checkbox like this:

`
add_filter( 'wc_gift_packaging_field_hook', 'custom_hook_position' );

function custom_hook_position( $text ) {

    return 'woocommerce_after_order_notes';

}
`

= Is it possible to change the text of the checkbox =

Yes. You can change the text of the checkbox with the `wc_gift_packaging_checkout_field` filter:

`
add_filter( 'wc_gift_packaging_checkout_field', 'my_checkbox_text' );

function my_checkbox_text( $text ) {

    return __( "Send this order as awesome packaged gift" );

}
`

= Is it possible to wrap the checkbox in some html =

Yes. You can use the `before_wc_gift_packaging_checkout_field` and `after_wc_gift_packaging_checkout_field` hooks like this:

`
add_action( 'before_wc_gift_packaging_checkout_field', 'custom_start' );

function custom_start() {

    echo '<div class="my-custom-html">';

}

add_action( 'after_wc_gift_packaging_checkout_field', 'custom_end' );

function custom_end() {

    echo '</div>';

}
`

= Is it possible to customize the note in the mail, order details or backend =

Yes. You can use the `wc_gift_packaging_admin_note`, `wc_gift_packaging_order_note` or `wc_gift_packaging_email_note` filters to completely change the note. Here are two examples:

`
add_filter( 'wc_gift_packaging_admin_note', 'custom_note', 10, 2 );

function custom_note( $text, $is_gift ) {
	
	if( $is_gift ):

		return '<h3>' . __( "This is a regular order" ) . '</h3>';

	else:

		return '<h3>' . __( "This order is a gift" ) . '</h3>';

	endif;
    

}
`
`
add_filter( 'wc_gift_packaging_order_note', 'html_wrap', 10, 2 );

function html_wrap( $text, $is_gift ) {
	
	return '<div class="my-custom-html">' . $text . '</div>';
    

}
`

== Installation ==

Here's how to install the plugin:

1. Upload 'woocommerce-gift-options' to the '/wp-content/plugins/' directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go to WordPress Admin > WooCommerce > Settings > Checkout and set the 'Gift packaging cost' field if you want to charge something for the packaging


== Changelog ==

= 1.2 =
* Bugfix that changes `$order->id` to `$order->get_order_number()`

= 1.1 =
* Bugfix wich makes the `$checkout` parameter in the `wc_gift_packaging_field` function optional to allows the usage of more hooks 

= 1.0 =
* Initial release
