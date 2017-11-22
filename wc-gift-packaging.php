<?php
/*
Plugin Name: TMSM WooCommerce Order Gift Wrap
Plugin URI: https://github.com/thermesmarins/tmsm-woocommerce-order-gift-wrap
Description: Enable a per-order gift wrap option on checkout page
Version: 1.0.0
Author: Nicolas Mollet
Author URI: http://www.nicolasmollet.com
Requires at least: 4.5
Tested up to: 4.8
Text Domain: tmsm-woocommerce-order-gift-wrap
Domain Path: /languages/
Github Plugin URI: https://github.com/thermesmarins/tmsm-woocommerce-product-gift-wrap
Github Branch: master
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Original Author: "WC Gift Packaging" plugin by Johannes Grandy http://johannesgrandy.com/, Arpit Tambi http://aheadzen.com/
*/


/*********************************** TRANSLATION *********************************/

load_plugin_textdomain( 'tmsm-woocommerce-order-gift-wrap', false, plugin_basename( __DIR__ ) . '/languages/' );

/**
 * TMSM_WooCommerce_Product_Gift_Wrap class.
 */
class TMSM_WooCommerce_Product_Gift_Wrap {

	/**
	 * TMSM_WooCommerce_Product_Gift_Wrap constructor.
	 * Hook us in :)
	 *
	 * @access public
	 */
	public function __construct() {
		$default_message                 = sprintf( __( 'Gift wrap this order for %s?', 'tmsm-woocommerce-order-gift-wrap' ), '{price}' );
		$this->order_gift_wrap_cost            = get_option( 'order_gift_wrap_cost', 0 );
		$this->order_gift_wrap_message = get_option( 'order_gift_wrap_message' );

		if ( ! $this->order_gift_wrap_message ) {
			$this->order_gift_wrap_message = $default_message;
		}

		add_option( 'order_gift_wrap_cost', '0' );
		add_option( 'order_gift_wrap_message', $default_message );

		// Init settings
		$this->settings = array(
			array(
				'name' 		=> __( 'Default Gift Wrap Cost', 'tmsm-woocommerce-order-gift-wrap' ),
				'desc' 		=> __( 'The cost of gift wrap unless overridden per-product.', 'tmsm-woocommerce-order-gift-wrap' ),
				'id' 		=> 'order_gift_wrap_cost',
				'type' 		=> 'text',
				'desc_tip'  => true
			),
			array(
				'name' 		=> __( 'Gift Wrap Message', 'tmsm-woocommerce-order-gift-wrap' ),
				'id' 		=> 'order_gift_wrap_message',
				'desc' 		=> __( 'Note: <code>{checkbox}</code> will be replaced with a checkbox and <code>{price}</code> will be replaced with the gift wrap cost.', 'tmsm-woocommerce-product-gift-wrap' ),
				'type' 		=> 'text',
				'desc_tip'  => __( 'The checkbox and label shown to the user on the frontend.', 'tmsm-woocommerce-order-gift-wrap' )
			),
		);

		$tmsm_woocommerce_order_gift_wrap_field_hook = apply_filters( 'tmsm_woocommerce_order_gift_wrap_field', 'woocommerce_after_checkout_billing_form' );
		add_action( $tmsm_woocommerce_order_gift_wrap_field_hook, 'order_gift_wrap_field' );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_action( 'woocommerce_cart_calculate_fees', 'cart_calculate_fees' );

		add_action( 'woocommerce_checkout_update_order_meta', 'update_order_meta' );

		add_action( 'woocommerce_admin_order_data_after_billing_address', 'admin_note', 10, 1 );

		add_action( 'woocommerce_order_details_after_order_table', 'order_note', 10, 1 );

		add_action( 'woocommerce_email_after_order_table', 'email_note' );


		// Admin
		add_action( 'woocommerce_settings_general_options_end', array( $this, 'admin_settings' ) );
		add_action( 'woocommerce_update_options_general', array( $this, 'save_admin_settings' ) );
	}

	/**
	 * admin_settings function.
	 *
	 * @access public
	 * @return void
	 */
	public function admin_settings() {
		woocommerce_admin_fields( $this->settings );
	}

	/**
	 * save_admin_settings function.
	 *
	 * @access public
	 * @return void
	 */
	public function save_admin_settings() {
		woocommerce_update_options( $this->settings );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts(){
		wp_enqueue_script( 'tmsm-woocommerce-order-gift-wrap', plugin_dir_url( __FILE__ ) . 'assets/js/tmsm-woocommerce-order-gift-wrap.js', array( 'jquery' ), null, true );
	}


	/**
	 * Creates checkox on checkout page
	 *
	 * @param $checkout
	 */
	function order_gift_wrap_field( $checkout ) {

		do_action( 'tmsm_woocommerce_order_gift_wrap_field_before' );

		$label = $this->order_gift_wrap_message;
		$value = $this->order_gift_wrap_cost;

		woocommerce_form_field( 'order-gift-wrap-checkbox', array(
			'type'  => 'checkbox',
			'class' => array( 'form-row-wide' ),
			'label' => $label,
		), $value );

		do_action( 'tmsm_woocommerce_order_gift_wrap_field_after' );

	}

	/**
	 * Calculates cart fee
	 */
	function cart_calculate_fees() {
		global $woocommerce;
		if ( $_POST ):
			parse_str( $_POST['post_data'], $data );
			if ( ( $data['order-gift-wrap-checkbox'] OR $_POST['order-gift-wrap-checkbox'] ) AND ! empty( $this->order_gift_wrap_cost ) ):
				WC()->cart->add_fee( __( 'Order Gift Wrap', 'tmsm-woocommerce-product-gift-wrap' ), $this->order_gift_wrap_cost );
			endif;
		endif;
	}

	/**
	 * Updates order meta
	 *
	 * @param $order_id
	 */
	function update_order_meta( $order_id ) {
		if ( $_POST['order-gift-wrap-checkbox'] ):
			$wc_gift_packaging = isset( $_POST['order-gift-wrap-checkbox'] ) ? 1 : 0;
			update_post_meta( $order_id, 'order-gift-wrap', $wc_gift_packaging );
		endif;
	}

	/**
	 * Admin note
	 *
	 * @param WC_Order $order
	 */
	function admin_note( $order ) {

		$status = get_post_meta( $order->get_order_number(), 'order-gift-wrap', true );

		$has_packaging = $status ? __( 'Yes', 'tmsm-woocommerce-product-gift-wrap' ) : __( 'No', 'tmsm-woocommerce-product-gift-wrap' );

		$note = '<p><strong>' . __( 'Order Gift Wrap', 'tmsm-woocommerce-product-gift-wrap' ) . ':</strong> ' . $has_packaging . '</p>';
		$note = apply_filters( 'tmsm_woocommerce_order_gift_wrap_admin_note', $note, $status );
		echo $note;

	}

	/**
	 * Order note
	 *
	 * @param WC_Order $order
	 */
	function order_note( $order ) {

		$status = get_post_meta( $order->get_order_number(), 'order-gift-wrap', true );

		$has_packaging = $status ? __( 'Yes', 'tmsm-woocommerce-product-gift-wrap' ) : __( 'No', 'tmsm-woocommerce-product-gift-wrap' );

		$note = '<p><strong>' . __( 'Order Gift Wrap', 'tmsm-woocommerce-product-gift-wrap' ) . ':</strong> ' . $has_packaging . '</p>';
		$note = apply_filters( 'tmsm_woocommerce_order_gift_wrap_order_note', $note, $status );
		echo $note;

	}

	/**
	 * Email note
	 *
	 * @param WC_Order $order
	 */
	function email_note( $order ) {

		$status = get_post_meta( $order->get_order_number(), 'order-gift-wrap', true );

		$has_packaging = $status ? __( 'Yes', 'tmsm-woocommerce-product-gift-wrap' ) : __( 'No', 'tmsm-woocommerce-product-gift-wrap' );

		$note = __( 'Order Gift Wrap', 'tmsm-woocommerce-product-gift-wrap' ) . ': ' . $has_packaging;
		$note = apply_filters( 'tmsm_woocommerce_order_gift_wrap_email_note', $note, $status );
		echo $note;

	}

}


new TMSM_WooCommerce_Product_Gift_Wrap();
