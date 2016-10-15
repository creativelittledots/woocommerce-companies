<?php
	
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WooCommerce WC_Companies_Checkout
 *
 * Handles Checkout Processes
 *
 * @class 		WC_Companies_Checkout
 * @version		1.0.0
 * @package		WooCommerce Companies/Classes
 * @category	Class
 * @author 		Creative Little Dots
 */
 
class WC_Companies_Checkout extends WC_Checkout {
	
	/**
	 * var $set_checkout_fields
	
	/**
	 * @var WC_Companies_Checkout The single instance of the class
	 * @since 2.1
	 */
	protected static $_instance = null;

	/**
	 * Main WC_Checkout Instance
	 *
	 * Ensures only one instance of WC_Checkout is loaded or can be loaded.
	 *
	 * @since 2.1
	 * @static
	 * @return WC_Checkout Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) )
			self::$_instance = new self();
		return self::$_instance;
	}

	public function __construct() {
		
		add_filter( 'woocommerce_checkout_fields', array($this, 'add_checkout_fields') );
		
		add_action( 'woocommerce_before_checkout_billing_form', array($this, 'before_checkout_billing_fields') );
		
		add_action( 'woocommerce_after_checkout_billing_form', array($this, 'after_checkout_billing_fields') );
		
		add_action( 'woocommerce_before_checkout_shipping_form', array($this, 'before_checkout_shipping_fields') );
		
		add_action( 'woocommerce_after_checkout_shipping_form', array($this, 'after_checkout_shipping_fields') );
		
		add_action( 'woocommerce_after_checkout_validation', array($this, 'billing_address_validation') );
		
		add_action( 'woocommerce_after_checkout_validation', array($this, 'shipping_address_validation') );
		
		add_action( 'woocommerce_after_checkout_validation', array($this, 'company_validation') );
		
		add_action( 'woocommerce_checkout_update_order_meta', array($this, 'update_order_addresses'), 10, 2);
		
		add_action( 'woocommerce_checkout_update_user_meta', array($this, 'update_user_addresses'), 10, 2);
		
		add_filter( 'woocommerce_checkout_update_customer_data', '__return_false' );
		
		add_filter( 'woocommerce_ship_to_different_address_checked', array($this, 'set_ship_to_different_address') );
		
		add_filter( 'woocommerce_shipping_free_shipping_is_available', array($this, 'free_shipping_when_company_has_free_shipping') );
		
		add_filter( 'woocommerce_billing_fields', array($this, 'hide_billing_company_on_checkout') );
			
		add_filter( 'woocommerce_shipping_fields', array($this, 'hide_shipping_company_on_checkout') );
		
	}
	
	/**
	 * add company, billing & shipping address field to the checkout
	 *
	 */
	public function add_checkout_fields( $checkout_fields ) {
		
		$checkout = $this;
		$companies = wc_get_user_companies();
		$company = $checkout->get_value( 'company' );
		
		global $current_user;
		
		$display_companies = array();
		
		foreach($companies as $display_company) {
			
			$display_companies[$display_company->id] = $display_company->get_title();
			
		}
		
		$billing_addresses = array();
		
		foreach($company ? $company->get_billing_addresses() : ( $companies ? reset($companies)->get_billing_addresses() : wc_get_user_addresses(get_current_user_id(), 'billing')) as $address) {
			
			$billing_addresses[$address->id] = $address->get_title();
			
		}
		
		$shipping_addresses = array();
		
		foreach($company ? $company->get_shipping_addresses() : ( $companies ? reset($companies)->get_shipping_addresses() : wc_get_user_addresses(get_current_user_id(), 'shipping')) as $address) {
			
			$shipping_addresses[$address->id] = $address->get_title();
			
		}
		
		$checkout_fields['checkout_type'] = array(
			'checkout_type' => array(
				'label' => __('How are you checking out?', 'woocommerce'),
				'type' => 'radio',
				'options' => array(
					'company' => __(' As a Company', 'woocommerce'),
					'customer' =>__( ' As an Individual', 'woocommerce')
				),
				'default' => $checkout->get_value( 'checkout_type' ),
				'label_class' => array('inline'),
			)
		);
		
		$checkout_fields['company_id'] = array(
			'company_id' => array(
				'label' => __('Which Company are you representing?', 'woocommerce'),
				'type' => 'select',
				'options' => array(
				    -1 => 'Add new Company'
				) + ( $display_companies ? $display_companies : array() ),
				'default' => $checkout->get_value( 'checkout_type' ) == 'company' ? ( $company ? $company->id : 0 ) : 0,
				'input_class' => array('company_select'),
			)
		);
		
		$checkout_fields['company'] = array(
			'company_name' => array(
				'label' => __('Company Name', 'woocommerce'),
				'type' => 'text',
				'required' => is_ajax() ? false : true,
				'placeholder' => __('Company Name', 'woocommerce'),
				'class' => array('form-row form-row-first'),
				'default' => $checkout->get_value('company_name'),
				'input_class' => array('widefat'),
			),
			'company_number' => array(
				'label' => __('Company Number', 'woocommerce'),
				'type' => 'text',
				'required' => false,
				'placeholder' => __('Company Number', 'woocommerce'),
				'class' => array('form-row form-row-last'),
				'default' => $checkout->get_value('company_number'),
				'input_class' => array('widefat'),
			)
		);
		
		$checkout_fields['billing_address_id'] = array(
			'billing_address_id' => array(
				'label' => __('Billing Address', 'woocommerce'),
				'type' => 'select',
				'options' => array(
	    		    -1 => 'Add new Address'
	            ) + ( $billing_addresses ? $billing_addresses : array() ),
				'input_class' => array('address_select'),
				'default' => $checkout->get_value( 'checkout_type' ) == 'company' ? ( $company && $company->primary_billing_address ? $company->primary_billing_address : 0 ) : ( $current_user->primary_billing_address ? $current_user->primary_billing_address : 0 ),
				'custom_attributes' => array(
					'data-address_type' => 'billing',	
				)
			)
		);
		
		$checkout_fields['shipping_address_id'] = array(
			'shipping_address_id' => array(
				'label' => __('Shipping Address', 'woocommerce'),
				'type' => 'select',
				'options' => array(
					-1 => 'Add new Address'
				) + ( $shipping_addresses ? $shipping_addresses : array()),
				'input_class' => array('country_select'),
				'default' => $checkout->get_value( 'checkout_type' ) == 'company' ? ( $company && $company->primary_shipping_address ? $company->primary_shipping_address : 0 ) : ( $current_user->primary_shipping_address ? $current_user->primary_shipping_address : 0 ),
				'custom_attributes' => array(
					'data-address_type' => 'shipping',	
				)
			)
		);
	
		return $checkout_fields;
		
	}
	
	/**
	 * add billing address if field to the checkout
	 *
	 */
	public function before_checkout_billing_fields() {
		
		$checkout = $this;
		$companies = wc_get_user_companies();
		$company = $checkout->get_value( 'company' );
		
		wc_get_template( 'checkout/before-billing-fields.php', array(
			'checkout_fields' => WC()->checkout()->checkout_fields,
			'checkout' => $checkout,
    		'companies' => $companies,
    		'billing_addresses' => $company ? $company->get_billing_addresses() : ( $companies ? reset($companies)->get_billing_addresses() : wc_get_user_addresses(get_current_user_id(), 'billing') ),
		), '', WC_Companies()->plugin_path() . '/templates/' );
		
	}
	
	/**
	 * display closeing div after checkout billing fields
	 *
	 */
	public function after_checkout_billing_fields() {
		
		wc_get_template( 'checkout/after-billing-fields.php', array(), '', WC_Companies()->plugin_path() . '/templates/' );
		
	}
	
	/**
	 * add shipping address if field to the checkout
	 *
	 */
	public function before_checkout_shipping_fields() {
		
		$checkout = $this;
		$companies = wc_get_user_companies();
		$company = $checkout->get_value( 'company' );
		
		wc_get_template( 'checkout/before-shipping-fields.php', array(
			'checkout_fields' => WC()->checkout()->checkout_fields,
			'checkout' => $checkout,
    		'shipping_addresses' => $company ? $company->get_shipping_addresses() : ( $companies ? reset($companies)->get_shipping_addresses() : wc_get_user_addresses(get_current_user_id(), 'shipping')),
		), '', WC_Companies()->plugin_path() . '/templates/' );
		
	}
	
	/**
	 * display closing div after checkout shipping fields
	 *
	 */
	public function after_checkout_shipping_fields() {
		
		wc_get_template( 'checkout/after-shipping-fields.php', array(), '', WC_Companies()->plugin_path() . '/templates/' );
		
	}
	
	/**
	 * Display only free shipping when company has free shipping
	 *
	 * @param array $rates
	 */
	public function free_shipping_when_company_has_free_shipping( $is_available ) {
		
		if( $this->get_company() && $this->get_company()->has_free_shipping() ) {
			
			$is_available = true;
			
		}		
		
		return $is_available;
		
	}
	
	/**
	 * Gets the value either from the posted data, or from the users meta data.
	 *
	 * @access public
	 * @param string $input
	 * @return string|null
	 */
	public function get_value( $input ) {
		
		if ( ! empty( $_POST[ $input ] ) ) {

			return wc_clean( $_POST[ $input ] );

		}
		
		if( ! empty( $_POST['post_data'] ) ) { 
		
			$post_data = array();
			
			parse_str($_POST['post_data'], $post_data);
			
			if ( ! empty( $post_data[ $input ] ) ) {
	
				return wc_clean( $post_data[ $input ] );
	
			}
			
		}
	
		switch( $input ) {
			
			case 'checkout_type' :
			
				if( ! $checkout_type = parent::get_value( $input ) ) {
					
					return 'company';
					
				}
				
			break;
			
			case 'billing_address' :
		
				$billing_address_id = $this->get_value('billing_address_id');
		
				if( $billing_address_id > 0 ) {
					
					return wc_get_address($billing_address_id);
					
				} else {
					
					return false;
					
				}		
			
			break;
			
			case 'shipping_address' :
			
				$shipping_address_id = $this->get_value('shipping_address_id');
		
				if( $shipping_address_id > 0 ) {
					
					return wc_get_address($shipping_address_id);
					
				} else {
					
					return false;
					
				}
			
			break;
			
			case 'company_id' :
			
				if( ! $company_id = parent::get_value( $input ) ) {
					
					if( $this->get_value('checkout_type') == 'company' ) {
					
						global $current_user;
						
						if( $company_id = $current_user->primary_company ) {
							
							return $company_id;
							
						}
						
					}
					
				}
				
				return $company_id;
			
			break;
			
			case 'company' :
			
				$company_id = $this->get_value('company_id');
		
				if( $company_id > 0 ) {
					
					return wc_get_company($company_id);
					
				}
				
				else {
					
					return false;
					
				}
			
			break;
			
			case 'company_name' :
			
				if( ! $company_name = parent::get_value( $input ) ) {
					
					if( $company = $this->get_value( 'company' ) ) {
						
						return $company->name;
						
					}
					
				}
			
			break;
			
			case 'company_number' :
			
				if( ! $company_number = parent::get_value( $input ) ) {
					
					if( $company = $this->get_value( 'company' ) ) {
						
						return $company->number;
						
					}
					
				}
			
			break;
			
			default :
			
				return parent::get_value( $input );
				
			break;
			
		}	
		
	}
	
	public function is_type( $type ) {
		
		return $this->get_value( 'checkout_type' ) == $type;
		
	}
	
	/**
	 * Validation message displayed when billing address is not set
	 *
	 */
	public function billing_address_validation() {
		
		if(is_user_logged_in() && ((isset($_POST['billing_address_id']) && $_POST['billing_address_id'] == 0) || (WC()->checkout()->get_value('billing_address_id') == 0))) {
			
			wc_add_notice( '<strong>Billing Address</strong> ' . __( 'is a required field.', 'woocommerce' ), 'error' );
			
		}
		
	}
	
	/**
	 * Validation message displayed when shipping address is not set
	 *
	 */
	public function shipping_address_validation() {
		
		if(is_user_logged_in() && ((isset($_POST['billing_address_id']) && $_POST['billing_address_id'] == 0) || (WC()->checkout()->get_value('billing_address_id') == 0 && !$_POST['shipping_address_id'] && !WC()->checkout()->get_value('shipping_address_id')))) {
			
			wc_add_notice( '<strong>Shipping Address</strong> ' . __( 'is a required field.', 'woocommerce' ), 'error' );
			
		}

		
	}
	
	/**
	 * Validation message displayed when company is not set
	 *
	 */
	public function company_validation() {
		
		if( ! empty( $_POST['checkout_type'] ) && $_POST['checkout_type'] == 'company' && 1 > $_POST['company_id'] ) {
			
			if( empty( $_POST['company_name'] ) ) {
			
				wc_add_notice( '<strong>Company Name</strong> ' . __( 'is a required field.', 'woocommerce' ), 'error' );
				
			}
		
		}
		
	}
	
	/**
	 * Updates the order addreses during checkout process
	 *
	 * @param int $order_id ID of Order being created
	 * @param array $posted Data sent in $POST
	 */
	public function update_order_addresses($order_id, $posted) {
		
		$order = new WC_Order($order_id);
		
		if( $this->billing_address_id > 0 ) {
			
			// Billing address
			if( $billing_address = wc_get_address( $this->billing_address_id ) ) {
				
				update_post_meta($order_id, '_billing_address_id', $billing_address->id);
				update_post_meta($order_id, '_shipping_address_id', $billing_address->id); // my as well
				
			}
			
		}

		if( $this->shipping_address_id > 0 ) {
			
			// Billing address
			if( $shipping_address = wc_get_address( $this->shipping_address_id ) ) {
				
				update_post_meta($order_id, '_shipping_address_id', $shipping_address->id);
				
			}
			
		}
		
		if( $this->company_id > 0 ) {
		
			// Company
			if( $company = wc_get_company( $this->company_id ) ) {
				
				update_post_meta($order_id, '_company_id', $company->id);
				
			}
			
		}
		
	}
	
	/**
	 * Updates the users addreses during checkout process
	 *
	 * @param int $user_id ID of User checking out
	 * @param array $posted Data sent in $POST
	 */
	public function update_user_addresses($user_id, $posted) {
		
		global $woocommerce_companies;
		
		if( $posted['checkout_type'] == 'company' ) {
			
			$company_id = null;
			
			if( empty( $posted['company_id'] ) || 0 > $posted['company_id'] ) {
			
				$company_id = wc_create_company(array(
						'company_name' => $posted['company_name'], 
						'company_number' => $posted['company_number'],
				));
				
			} else {
				
				$company_id = $posted['company_id'];
				
			}
				
			if( $company_id ) {
					
				$companies = get_user_meta($user_id, 'companies', true);
			
				$companies = is_array($companies) ? $companies : array();
				
				array_unshift($companies, $company_id);
				
				update_user_meta($user_id, 'companies', $companies);
				
				update_user_meta($user_id, 'primary_company', $company_id);
				
				do_action('checkout_updated_company_meta', $company_id, $posted);
				
			}
			
		}
		
		$billing_address_id = null;
		
		if( empty( $posted['billing_address_id'] ) || 0 > $posted['billing_address_id'] ) {
			
			// Billing address
			$billing_address = array();
			
			if ( $billingFields = array_keys(WC()->countries->get_address_fields( '', 'billing_' )) ) {
				
				foreach ( $billingFields as $field ) {
					
					delete_user_meta($user_id, $field);
					
					$billing_address[ str_replace('billing_', '', $field) ] = esc_sql( $posted[$field] );
					
				}
				
			}
			
			if( $billing_address ) {
				
				$billing_address_id = wc_create_address( $billing_address );
				
			}
			
		}
			
		if( $billing_address_id && ! is_wp_error( $billing_address_id ) ) {
			
			$billing_addresses = $posted['checkout_type'] == 'company' ? get_post_meta($company_id, '_billing_addresses', true) : get_user_meta($user_id, 'billing_addresses', true);
				
			$billing_addresses = is_array($billing_addresses) ? $billing_addresses : array();
			
			array_unshift($billing_addresses, $billing_address_id);
				
			$billing_addresses = array_unique($billing_addresses);
			
			$posted['checkout_type'] == 'company' ? update_post_meta($company_id, '_billing_addresses', $billing_addresses) : update_user_meta($user_id, 'billing_addresses', $billing_addresses);
			
			update_user_meta($user_id, 'primary_billing_address', $billing_address_id);
			
			if( $company_id ) {
				
				update_post_meta($company_id, '_primary_billing_address', $billing_address_id);
				
			}
			
			do_action('checkout_updated_billing_address_meta', $billing_address_id, $posted);
			
		}
		
		$shipping_address_id = ! empty( $posted['ship_to_different_address'] ) || ! $billing_address_id || is_wp_error( $billing_address_id ) ? null : $billing_address_id;
		
		if( ! $shipping_address_id ) {

			// Shipping address.
			$shipping_address = array();
			
			if ( $shippingFields = array_keys(WC()->countries->get_address_fields( '', 'shipping_' )) ) {
				
				foreach ( $shippingFields as $field ) {
					
					delete_user_meta($user_id, $field);
					
					$shipping_address[ str_replace('shipping_', '', $field) ] = esc_sql( $posted[$field] );
					
				}
				
			}
			
			if( $shipping_address ) {
				
				$shipping_address_id = wc_create_address( $shipping_address );
				
			}
				
		}
			
		if( $shipping_address_id && ! is_wp_error( $shipping_address_id ) ) {
			
			$shipping_addresses = $posted['checkout_type'] == 'company' ? get_post_meta($company_id, '_shipping_addresses', true) : get_user_meta($user_id, 'shipping_addresses', true);
				
			$shipping_addresses = is_array($shipping_addresses) ? $shipping_addresses : array();
			
			array_unshift($shipping_addresses, $shipping_address_id);
				
			$shipping_addresses = array_unique($shipping_addresses);
			
			$posted['checkout_type'] == 'company' ? update_post_meta($company_id, '_shipping_addresses', $shipping_addresses) : update_user_meta($user_id, 'shipping_addresses', $shipping_addresses);
			
			update_user_meta($user_id, 'primary_shipping_address', $shipping_address_id);
			
			if( $company_id ) {
				
				update_post_meta($company_id, '_primary_shipping_address', $shipping_address_id);
				
			}
			
			do_action('checkout_updated_shipping_address_meta', $shipping_address_id, $posted);
			
		}
		
		$this->company_id = $company_id;
		$this->billing_address_id = $billing_address_id;
		$this->shipping_address_id = $shipping_address_id;
		
	}
	
	/**
	 * Sets ship to different address if shipping address 1 field is not empty
	 *
	 * @param boolean $ship_to_different_address
	 */
	public function set_ship_to_different_address($ship_to_different_address) {
		
		if( WC()->checkout()->get_value('shipping_address_1') ) {
			
			$ship_to_different_address = true;
			
		}
		
		return $ship_to_different_address;
		
	}
	
	/**
	 * Get company from checkout instance
	 *
	 * @param object $checkout WC_Checkout object
	 */
	public function get_company() {
		
		return $this->get_value( 'company' );
		
	}
	
	/**
	 * Get billing_address from checkout instance
	 *
	 * @param object $checkout WC_Checkout object
	 */
	public function get_billing_address() {
		
		return $this->get_value( 'billing_address' );
		
	}
	
	/**
	 * Get shipping_address from checkout instance
	 *
	 * @param object $checkout WC_Checkout object
	 */
	public function get_shipping_address() {
		
		return $this->get_value( 'shipping_address' );
		
	}
	
	/**
	 * hides billing company field on checkout
	 *
	 * @param array $fields Array of billing fields
	 */
	public function hide_billing_company_on_checkout($fields) {
			
		unset($fields['billing_company']);
		
		return $fields;
		
	}	
	
	/**
	 * hides shipping company field on checkout
	 *
	 * @param array $fields Array of shipping fields
	 */
	public function hide_shipping_company_on_checkout($fields) {
		
		unset($fields['shipping_company']);
		
		return $fields;
		
	}
	
}
	
?>