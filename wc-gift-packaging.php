<?php


	/*

		Plugin Name: WC Gift Packaging
		Description: This plugin adds a 'Send this order packaged as gift' option on the WooCommerce checkout.
		Text Domain: wc-gift-packaging
		Author: Johannes Grandy
		Author URI: http://johannesgrandy.com/
		Copyright: Arpit Tambi
		Copyright URI: http://aheadzen.com/
		Version: 1.2

	*/




	/*********************************** TRANSLATION *********************************/
	
	load_plugin_textdomain( 'wc-gift-packaging', false, plugin_basename( __DIR__ ) . '/languages/' );





	/*********************************** GET PACKAGING COST *********************************/

	global $gift_packaging_fee;

	$gift_packaging_fee = get_option( 'gift_packaging_fee' );
	$gift_packaging_fee = is_numeric( $gift_packaging_fee ) ? $gift_packaging_fee : 0;

			


	/*********************************** CHECKOUT FIELD *********************************/

	$wc_gift_packaging_field_hook = apply_filters( 'wc_gift_packaging_field_hook', 'woocommerce_after_checkout_billing_form' );

	add_action( $wc_gift_packaging_field_hook, 'wc_gift_packaging_field' );

	function wc_gift_packaging_field( $checkout ) {
	 	

		do_action( 'before_wc_gift_packaging_checkout_field' );


		$label = apply_filters( 'wc_gift_packaging_checkout_field', __( 'Send this order packaged as gift', 'wc-gift-packaging' ) );
	    
	    $value = method_exists( $checkout, 'get_value' ) ? $checkout->get_value( 'wc-gift-packaging' ) : '';

	    
	    woocommerce_form_field( 'wc-gift-packaging', array(

	        'type' => 'checkbox',
	        'class' => array( 'wc-gift-packaging-checkbox form-row-wide' ),
	        'label' => $label,
			
		), $value );


		do_action( 'after_wc_gift_packaging_checkout_field' );


		?>

		<script>

			jQuery( '.wc-gift-packaging-checkbox input[type="checkbox"]' ).on( 'click', function() {

					jQuery( 'body' ).trigger( 'update_checkout' );

			} );

		</script>

		<?php

	}



	/*********************************** CHECKOUT INFO *********************************/

	add_action( 'woocommerce_cart_calculate_fees', 'wc_gift_packaging_fee' );

	function wc_gift_packaging_fee() { 

		global $woocommerce, $gift_packaging_fee;

	 	if( $_POST ):

			parse_str( $_POST[ 'post_data' ], $data );

			if( ( $data[ 'wc-gift-packaging' ] OR $_POST[ 'wc-gift-packaging' ] ) AND !empty( $gift_packaging_fee ) ):

				$woocommerce->cart->add_fee( __( 'Gift packaging', 'wc-gift-packaging' ), $gift_packaging_fee );
			
			endif;
		
		endif;
		
	}



	/*********************************** UPDATE ORDER META *********************************/

	add_action( 'woocommerce_checkout_update_order_meta', 'wc_gift_packaging_update_order_meta' ); 

	function wc_gift_packaging_update_order_meta( $order_id ) {

		if( $_POST[ 'wc-gift-packaging' ] ):

			$wc_gift_packaging = isset( $_POST[ 'wc-gift-packaging' ] ) ? 1 : 0;

			update_post_meta( $order_id, 'wc-gift-packaging', $wc_gift_packaging );
		
		endif;

	}




	/*********************************** ADMIN ORDER NOTE *********************************/

	add_action( 'woocommerce_admin_order_data_after_billing_address', 'wc_gift_packaging_admin_note', 10, 1 );

	function wc_gift_packaging_admin_note( $order ) {

		$status = get_post_meta( $order->get_order_number(), 'wc-gift-packaging', true );
		
		$has_packaging = $status ? __( 'Yes', 'wc-gift-packaging' ) : __( 'No', 'wc-gift-packaging' );

		$note = '<p><strong>'.__( 'Gift packaging', 'wc-gift-packaging' ).':</strong> ' . $has_packaging . '</p>';
		$note = apply_filters( 'wc_gift_packaging_admin_note', $note, $status );
	   	echo $note;
	
	}



	/*********************************** PUBLIC ORDER NOTE *********************************/

	add_action( 'woocommerce_order_details_after_order_table', 'wc_gift_packaging_order_note', 10, 1 );

	function wc_gift_packaging_order_note( $order ) {

		$status = get_post_meta( $order->get_order_number(), 'wc-gift-packaging', true );

		$has_packaging = $status ? __( 'Yes', 'wc-gift-packaging' ) : __( 'No', 'wc-gift-packaging' );

		$note = '<p><strong>'.__( 'Gift packaging', 'wc-gift-packaging' ).':</strong> ' . $has_packaging . '</p>';
		$note = apply_filters( 'wc_gift_packaging_order_note', $note, $status );
	   	echo $note;
	
	}




	/*********************************** EMAIL NOTE *********************************/

	add_action( 'woocommerce_email_after_order_table', 'wc_gift_packaging_email_note' );

	function wc_gift_packaging_email_note( $order ) {
		
		$status = get_post_meta( $order->get_order_number(), 'wc-gift-packaging', true );

		$has_packaging = $status ? __( 'Yes', 'wc-gift-packaging' ) : __( 'No', 'wc-gift-packaging' );

		$note = __( 'Gift packaging', 'wc-gift-packaging' ) . ': ' . $has_packaging;
		$note = apply_filters( 'wc_gift_packaging_email_note', $note, $status );
	   	echo $note;

	}





	/*********************************** ADD FIELD IN WC SETTINGS *********************************/

	$gift_packaging_settings = array(

		array(

			'name' => __( 'Gift packaging cost', 'wc-gift-packaging' ),
			'desc' => __( 'Set here the cost for gift packaging', 'wc-gift-packaging' ),
			'id' => 'gift_packaging_fee',
			'type' => 'text',
			'desc_tip' => true

		)

	);
	
	
	add_action( 'woocommerce_settings_checkout_process_options', 'gift_pack_admin_settings');

	function gift_pack_admin_settings() {

		global $gift_packaging_settings;
		woocommerce_admin_fields( $gift_packaging_settings );

	}


	add_action( 'woocommerce_update_options_checkout', 'gift_pack_save_admin_settings' );

	function gift_pack_save_admin_settings() {

		global $gift_packaging_settings;
		woocommerce_update_options( $gift_packaging_settings );

	}



