<?php

if ( class_exists( 'WC_Payment_Gateway' ) ) {

	class WC_Gateway_Credit_Limit extends WC_Payment_Gateway {
		
		public $version 	= '1.0.0';
		
		public function __construct() { 
			
			global $woocommerce;
			
			$this->id			= 'CreditLimit';
			$this->has_fields 	= true;
			$this->method_title = __('Credit Limit', 'woocommerce-companies');
			
			// Load the form fields.
			$this->init_form_fields();
			
			// Load the settings.
			$this->init_settings();
			
			// Define user set variables
			$this->title 								= $this->settings['title'];
			$this->description 							= $this->settings['description'];
			$this->utiliste_available_credit			= $this->settings['utiliste_available_credit'];
			$this->woo_version 		= $this->get_woo_version();
			
			// Actions
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			add_filter( 'woocommerce_available_payment_gateways', array($this, 'remove_gateway_if_company_not_set') );

		}
		
		public function plugin_url() {
		
			return plugins_url( basename( plugin_dir_path(__FILE__) ), basename( __FILE__ ) );
			
		}
	
		public function plugin_path() {
			
			return untrailingslashit( plugin_dir_path( __FILE__ ) );
			
		}
		
		/**
		 * Initialise Gateway Settings Form Fields
		 */
		function init_form_fields() {
		
			$this->form_fields = array(
				'enabled' => array(
								'title' => __( '<b>Enable/Disable:</b>', 'woocommerce-companies' ), 
								'type' => 'checkbox', 
								'label' => __( 'Enable Credit Limit Payment Gateway.', 'woocommerce-companies' ), 
								'default' => 'yes'
							), 
				'title' => array(
								'title' => __( '<b>Title:</b>', 'woocommerce-companies' ), 
								'type' => 'text', 
								'description' => __( 'The title which the user sees during checkout.', 'woocommerce-companies' ), 
								'default' => __( 'Credit Limit', 'woocommerce-companies' )
							),
				'description' => array(
								'title' => __( '<b>Description:</b>', 'woocommerce-companies' ), 
								'type' => 'textarea', 
								'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce-companies' ), 
								'default' => __('Pay with Debit/Credit Card.', 'woocommerce-companies')
							),
				'utiliste_available_credit' => array(
								'title' => __( '<b>Utilise Available Credit?</b>', 'woocommerce-companies' ), 
								'type' => 'select',
								'options' => array(
									1 => 'On',
									0 => 'Off',
								), 
								'description' => __( 'This is a toggle to switch on and off the utilisation of Available Credit for companies. If this is switched off, the available credit will not be used in validation or and will need be calculated and updated in the post order creation processes.', 'woocommerce-companies' ), 
								'default' => __(1, 'woocommerce-companies')
							)
				);
		
		} // End init_form_fields()
		
		/**
	    * Payment fields for credit limit
	    **/
		public function payment_fields() {  
								
			wc_get_template('checkout/gateways/credit-limit/payment-fields.php', array(
			), '', WC_Companies()->plugin_path() . '/templates/');
			
		}
		
		/**
	    * Validate payment fields
	    */
	    function validate_fields() {
		    
	        global $woocommerce;
			
	        if (!$_POST['purchase_order_number']){
				if($this->woo_version >= 2.1){
					wc_add_notice( __('Purchase Order Number is a required field.', 'woocommerce-companies'), 'error' );
				} else if( $this->woo_version < 2.1 ){
					$woocommerce->add_error( __('Purchase Order Number is a required field.', 'woocommerce-companies') );
				} else{
					$woocommerce->add_error( __('Purchase Order Number is a required field.', 'woocommerce-companies') );
				}
			}

		}
		
		/**
		 * Admin Panel Options 
		 * - Options for bits like 'title' and availability on a country-by-country basis
		 *
		 * @since 1.0.0
		 */
		public function admin_options() {

			?>
			<h3>Credit Limit Payment Gateway</h3>
			<p>Allow your customers to choose to pay with their Credit Limit account</p>
			<p><b>Module Version:</b> 1.0 (For WooCommerce v2.1+)<br />
			<b>Module Date:</b> 20 April 2015</p>
			<table class="form-table">
			<?php
				// Generate the HTML For the settings form.
				$this->generate_settings_html();
			?>
			</table><!--/.form-table-->
			<?php
		} // End admin_options()
		
		/**
		 * Remove the Credit Limit gateway if company is not set
		 **/
		public function remove_gateway_if_company_not_set($_available_gateways) {
			
			if( ( ! WC_Companies()->checkout()->get_company() && ! WC_Companies()->checkout()->get_value('company_name') ) || WC_Companies()->checkout()->get_value('checkout_type') != 'company' || ( $this->utiliste_available_credit && $company->get_available_credit() ) ) {
				
				unset($_available_gateways['CreditLimit']);
				
			}
			
			return $_available_gateways;
			
		}
		
		/**
		* 
	    * process payment
	    * 
	    */
	    function process_payment( $order_id ) {
			
	        $order = new WC_Order( $order_id );
	        
	        $company = WC_Companies()->checkout()->get_company();
	        
	        update_post_meta($order->id, '_purchase_order_number', WC()->checkout()->get_value('purchase_order_number'));
	        
	        if($company) {
		        
		        if($this->utiliste_available_credit) { 
		        
					if($company->get_available_credit(false) > $order->get_total()) {
			        
				        $company->reduce_available_credit($order->get_total());
				        
				        $order->add_order_note( sprintf( __('<a href="%s">%s</a> credit limit used. Credit has been reduced by %s, new available credit %s', 'woocommerce-companies'), get_edit_post_link($company->id), $company->get_title(), $order->get_formatted_order_total(), $company->get_available_credit()) );
					        
						$order->payment_complete();
						
						$redirect_url = $this->get_return_url( $order );
						
						return array(
							'result' 	=> 'success',
							'redirect'	=>  $redirect_url,
						);
						
					}
					
					else {
						
						if($this->woo_version >= 2.1){
							wc_add_notice( sprintf( __( '%s does not have enough credit to complete this transaction.', 'woocommerce-companies'), $company->get_title() ), 'error' );
						} else if( $this->woo_version < 2.1 ){
							$woocommerce->add_error( sprintf( __( '%s does not have enough credit to complete this transaction.', 'woocommerce-companies'), $company->get_title() ) );
						} else{
							$woocommerce->add_error( sprintf( __( '%s does not have enough credit to complete this transaction.', 'woocommerce-companies'), $company->get_title() ) );
						}
						
					}
					
				} else {
					
					$order->payment_complete();
					
					$redirect_url = $this->get_return_url( $order );
						
					return array(
						'result' 	=> 'success',
						'redirect'	=>  $redirect_url,
					);
					
				}
		        
	        }
	        
	        else {
		        
		       if($this->woo_version >= 2.1){
					wc_add_notice( __( 'Company ID is a reqired field.', 'woocommerce-companies') , 'error' );
				} else if( $this->woo_version < 2.1 ){
					$woocommerce->add_error( __( 'Company ID is a reqired field.', 'woocommerce-companies') );
				} else{
					$woocommerce->add_error( __( 'Company ID is a reqired field.', 'woocommerce-companies') );
				}
		        
	        }	
	        
	        
		}
		
		public function get_woo_version() {
		    
			// If get_plugins() isn't available, require it
			if ( ! function_exists( 'get_plugins' ) )
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			
		    // Create the plugins folder and file variables
			$plugin_folder = get_plugins( '/woocommerce' );
			$plugin_file = 'woocommerce.php';
			
			// If the plugin version number is set, return it 
			if ( isset( $plugin_folder[$plugin_file]['Version'] ) ) {
				return $plugin_folder[$plugin_file]['Version'];
		
			} else {
				// Otherwise return null
				return NULL;
			}
		}
		
	}
	
}
	
?>