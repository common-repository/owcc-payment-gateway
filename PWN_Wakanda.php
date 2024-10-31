<?php
/* OWCC Payment Gateway Class */
class PWN_OWCC_gateway extends WC_Payment_Gateway {

	// Setup our Gateway's id, description and other values
	function __construct() {

		// The global ID for this Payment method
		$this->id = "pwn_OWCC_gateway";

		// The Title shown on the top of the Payment Gateways Page next to all the other Payment Gateways
		$this->method_title = __( "OWCC Payments (Wakanda Coin)", 'pwn-OWCC-gateway' );

		// The description for this Payment Gateway, shown on the actual Payment options page on the backend
		$this->method_description = __( "OWCC Payment Gateway Plug-in for WooCommerce", 'pwn-OWCC-gateway' );
		
		

		// The title to be used for the vertical tabs that can be ordered top to bottom
		$this->title = __( "OWCC gateway", 'pwn-OWCC-gateway' );

		// If you want to show an image next to the gateway's name on the frontend, enter a URL to an image.
		$this->icon = null;

		// Bool. Can be set to true if you want payment fields to show on the checkout 
		// if doing a direct integration, which we are doing in this case
		$this->has_fields = true;

		// Supports the default credit card form
		$this->supports = array( 'products' );

		// This basically defines your settings which are then loaded with init_settings()
		$this->init_form_fields();

		// After init_settings() is called, you can get the settings and load them into variables, e.g:
		 $this->title = $this->get_option( 'title' );
		$this->init_settings();
		
		// Turn these settings into variables we can use
		foreach ( $this->settings as $setting_key => $value ) {
			$this->$setting_key = $value;
		}
		
	    //	add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
		// Lets check for SSL
		add_action( 'admin_notices', array( $this,	'do_ssl_check' ) );
		
		// Save settings
		if ( is_admin() ) {
			// Versions over 2.0
			// Save our administration options. Since we are not going to be doing anything special
			// we have not defined 'process_admin_options' in this class so the method in the parent
			// class will be used instead
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		}		
	} // End __construct()

	// Build the administration fields for this specific Gateway
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'		=> __( 'Enable / Disable', 'pwn-OWCC-gateway' ),
				'label'		=> __( 'Enable this payment gateway', 'pwn-OWCC-gateway' ),
				'type'		=> 'checkbox',
				'default'	=> 'no',
			),
			'title' => array(
				'title'		=> __( 'Title', 'pwn-OWCC-gateway' ),
				'type'		=> 'text',
				'desc_tip'	=> __( 'Payment title the customer will see during the checkout process.', 'pwn-OWCC-gateway' ),
				'default'	=> __( 'OWCC ', 'pwn-OWCC-gateway' ),
			),
			'description' => array(
				'title'		=> __( 'Description', 'pwn-OWCC-gateway' ),
				'type'		=> 'textarea',
				'desc_tip'	=> __( 'Payment description the customer will see during the checkout process.', 'pwn-OWCC-gateway' ),
				'default'	=> __( 'Pay securely using your OWCC Address.', 'pwn-OWCC-gateway' ),
				'css'		=> 'max-width:350px;'
			),
			'apiLogin' => array(
				'title'		=> __( 'OWCC Address', 'pwn-OWCC-gateway' ),
				'type'		=> 'text',
				'desc_tip'	=> __( 'This is the API Login provided by OWCC when you signed up for an account.', 'pwn-OWCC-gateway' ),
				
			),
			
			'environment' => array(
				'title'		=> __( 'OWCC Test Mode', 'pwn-OWCC-gateway' ),
				'label'		=> __( 'Enable Test Mode', 'pwn-OWCC-gateway' ),
				'type'		=> 'checkbox',
				'description' => __( 'Place the payment gateway in test mode.', 'pwn-OWCC-gateway' ),
				'default'	=> 'no',
			)
		);		
	}
	
	
	// Submit payment and handle response
	public function process_payment($order_id) {
		global $woocommerce;
		$order = new WC_Order( $order_id );

		// Mark as on-hold (we're awaiting the cheque)
		$order->update_status('on-hold', __( 'Awaiting OWCC payment', 'woocommerce' ));

		// Remove cart
		//$woocommerce->cart->empty_cart();

		// Return thankyou redirect
		return array(
			'result' => 'success',
			'redirect' => $this->get_return_url( $order ).'&sorted='. $order_id 
		);
		
	}
	
	
	
	
	
	// Validate fields
	public function validate_fields() {
		return true;
	}
	
	// Check if we are forcing SSL on checkout pages
	// Custom function not required by the Gateway
	public function do_ssl_check() {
			
	}
	
	
	
	

} 
