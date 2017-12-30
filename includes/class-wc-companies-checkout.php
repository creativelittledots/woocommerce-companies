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
	
	public $fillable = array(
		'company_name',
		'company_number',
		'billing_phone',
		'billing_address_1', 
		'billing_address_2', 
		'billing_city', 
		'billing_state', 
		'billing_postcode', 
		'billing_country',
		'shipping_phone',
		'shipping_address_1', 
		'shipping_address_2', 
		'shipping_city', 
		'shipping_state', 
		'shipping_postcode', 
		'shipping_country'
	);

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
		
		add_filter( 'woocommerce_checkout_fields', array($this, 'maybe_add_checkout_fields') );
				
		add_action( 'woocommerce_checkout_update_order_meta', array($this, 'update_order_addresses'), 10, 2);
		
		add_action( 'woocommerce_checkout_update_user_meta', array($this, 'update_user_addresses'), 10, 2);
		
		add_filter( 'woocommerce_checkout_update_customer_data', '__return_false' );
		
		add_filter( 'woocommerce_ship_to_different_address_checked', array($this, 'set_ship_to_different_address') );
		
		add_filter( 'woocommerce_shipping_free_shipping_is_available', array($this, 'free_shipping_when_company_has_free_shipping') );
		
		add_filter( 'woocommerce_billing_fields', array($this, 'rearrange_billing_fields') );
		
		add_filter( 'woocommerce_checkout_get_value', array($this, 'get_checkout_values'), 10, 2 );
		
	}
	
	/**
	 * add company, billing & shipping address field to the checkout
	 *
	 */
	public function maybe_add_checkout_fields( $checkout_fields ) {
		
		$checkout = $this;
		
		$fields = array(
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
		
		if( is_user_logged_in() ) {
		
			$companies = wc_get_user_companies();
			$company = $checkout->get_value( 'company' );
			
			global $current_user;
			
			$display_companies = array();
			
			foreach($companies as $display_company) {
				
				$display_companies[$display_company->id] = $display_company->get_title();
				
			}
			
			$billing_addresses = $shipping_addresses = array();
			
			foreach(wc_get_user_all_addresses() as $address) {
				
				$billing_addresses[$address->id] = $address->get_title();
				$shipping_addresses[$address->id] = $address->get_title();
				
			}
			
			if( $display_companies ) {
			
				$fields['company_id'] = array(
					'label' => __('Which Company are you representing?', 'woocommerce'),
					'type' => 'select',
					'options' => array(
					    -1 => 'Add new Company'
					) + $display_companies,
					'default' => $checkout->get_value( 'checkout_type' ) == 'company' ? ( $company ? $company->id : 0 ) : 0,
					'input_class' => array('company_select'),
				);
				
			}
			
			$fields = $fields + array(
				'company_name' => array(
					'label' => __('Company / Trading Name', 'woocommerce'),
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
					'placeholder' => __('Company Number', 'woocommerce'),
					'class' => array('form-row form-row-last'),
					'default' => $checkout->get_value('company_number'),
					'input_class' => array('widefat'),
				)
			);
			
			if( $billing_addresses ) {
			
				$fields['billing_address_id'] = array(
					'label' => __('Billing Address', 'woocommerce'),
					'type' => 'select',
					'options' => array(
		    		    -1 => 'Add new Address'
		            ) + $billing_addresses,
					'input_class' => array('address_select'),
					'default' => $checkout->get_value( 'checkout_type' ) == 'company' ? ( $company && $company->primary_billing_address ? $company->primary_billing_address : 0 ) : ( $current_user->primary_billing_address ? $current_user->primary_billing_address : 0 ),
					'custom_attributes' => array(
						'data-address_type' => 'billing',	
					)
				);
				
			}
			
			$offset = array_search( 'billing_email', array_keys( $checkout_fields['billing'] ) );
			
			$checkout_fields['billing'] = array_slice($checkout_fields['billing'], 0, $offset+1, true) + $fields + array_slice($checkout_fields['billing'], $offset+1, null, true);
				
			if( $shipping_addresses ) {
			
				$checkout_fields['shipping'] = apply_filters( 'woocommerce_shipping_fields', array(
					'shipping_address_id' => array(
						'label' => __('Shipping Address', 'woocommerce'),
						'type' => 'select',
						'options' => array(
							-1 => 'Add new Address'
						) + $shipping_addresses,
						'input_class' => array('address_select'),
						'custom_attributes' => array(
							'data-address_type' => 'shipping',	
						)
					)
				) + $checkout_fields['shipping'] );
				
			}
			
			$checkout_fields['billing']['billing_first_name']['custom_attributes']['default'] = $checkout_fields['shipping']['shipping_first_name']['custom_attributes']['default'] = $current_user->first_name;
			$checkout_fields['billing']['billing_last_name']['custom_attributes']['default'] = $checkout_fields['shipping']['shipping_last_name']['custom_attributes']['default'] = $current_user->last_name;
			$checkout_fields['billing']['billing_email']['custom_attributes']['default'] = $current_user->user_email;
			
		}
	
		return $checkout_fields;
		
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
	
	public function get_checkout_values( $value, $input ) {
		
		if( in_array( $input, $this->fillable ) || in_array( $input, array(
			'billing_address_id',
			'shipping_address_id',
			'shipping_company',
			'company_id',
			'checkout_type'
		) ) ) {
			
			$value = $this->get_value( $input );
			
		}
		
		return $value;
		
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
		
		if( in_array( $input, $this->fillable ) ) {
			
			if ( strpos( $input, 'billing_' ) !== false ) {
				
				if( $billing_address = $this->get_value( 'billing_address' ) ) {
					
					$field_name = str_replace( 'billing_', '', $input );
				
					return $billing_address->$field_name;
					
				} else {
					
					return '';
					
				}
				
			} else if (strpos( $input, 'shipping_' ) !== false ) {
				
				if( $shipping_address = $this->get_value( 'shipping_address' ) ) {
					
					$field_name = str_replace( 'shipping_', '', $input );
				
					return $shipping_address->$field_name;
					
				} else {
					
					return '';
					
				}
				
			} else {
				
				switch( $input ) {
					
					case 'company_name' :
				
						if( $company = $this->get_value( 'company' ) ) {
							
							return $company->name;
							
						}
					
					break;
					
					case 'company_number' :
					
						if( $company = $this->get_value( 'company' ) ) {
								
							return $company->number;
							
						}
					
					break;
					
				}
				
			}
			
		} else {
	
			switch( $input ) {
				
				case 'billing_address_id' :
				
					$billing_address_id = WC()->session->get( 'billing_address_id' );
					
					if( ! $billing_address_id ) {
						
						if( $this->get_value( 'checkout_type' ) == 'company' && $company = $this->get_value( 'company' ) ) {
							
							$billing_address_id = $company->primary_billing_address;
							
						} else {
						
							global $current_user;
						
							$billing_address_id = $current_user->primary_billing_address;
							
						}
						
					}
					
					return $billing_address_id;
					
				break;
				
				case 'shipping_address_id' :
				
					$shipping_address_id = WC()->session->get( 'shipping_address_id' );
					
					return $shipping_address_id;
					
				break;
				
				case 'checkout_type' :
				
					return is_user_logged_in() ? 'company' : 'customer';
					
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
				
					if( $this->get_value('checkout_type') == 'company' ) {
						
						global $current_user;
						
						if( $company_id = $current_user->primary_company ) {
							
							return $company_id;
							
						}
						
					}
					
					return null;
				
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
				
				case 'shipping_company' :
				
					return $this->get_value( 'company_name' );
					
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
		
	}
	
	public function is_type( $type ) {
		
		return $this->get_value( 'checkout_type' ) == $type;
		
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
		
		if( ! empty( $posted['company_name'] ) ) {
			
			update_post_meta($order_id, '_billing_company', $posted['company_name']);
			
			if( empty( $posted['shipping_company'] ) ) {
				
				update_post_meta($order_id, '_shipping_company', $posted['company_name']);
				
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
		
		$this->company_id = $this->billing_address_id  = $this->shipping_address_id  = null;
		
		$posted['checkout_type'] = ! empty( $posted['checkout_type'] ) ? $posted['checkout_type'] : ( is_user_logged_in() ? 'company' : 'customer' );
		
		if( ! empty( $posted['checkout_type'] ) && $posted['checkout_type'] == 'company' ) {
			
			$company_id = null;
			
			if( empty( $posted['company_id'] ) || 0 > $posted['company_id'] ) {
			
				$company_id = wc_create_company(array(
						'company_name' => $posted['company_name'], 
						'company_number' => $posted['company_number'],
				));
				
			} else {
				
				$company_id = $posted['company_id'];
				
			}
				
			if( $company_id && ! is_wp_error($company_id) ) {
					
				$companies = get_user_meta($user_id, 'companies', true);
			
				$companies = is_array($companies) ? $companies : array();
				
				array_unshift($companies, $company_id);
				
				update_user_meta($user_id, 'companies', $companies);
				
				update_user_meta($user_id, 'primary_company', $company_id);
				
				do_action('checkout_updated_company_meta', $company_id, $posted);
				
				$this->company_id = $company_id;
				
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
				
				if( $this->company_id ) {
					
					$billing_address['company'] = get_post_meta($this->company_id, '_company_name', true);
					$billing_address['accounting_reference'] = get_post_meta($this->company_id, '_accounting_reference', true);
					
				}
				
				$billing_address_id = wc_create_address( $billing_address );
				
			}
			
		}
		
		if( $billing_address_id && ! is_wp_error( $billing_address_id ) ) {
				
			if( $this->company_id ) {
				
				wc_add_company_address( $this->company_id, $billing_address_id, 'billing' );
				
				update_post_meta($this->company_id, '_primary_billing_address', $billing_address_id);
				
			} else {
				
				wc_add_user_address( $user_id, $billing_address_id, 'billing' );
				
				update_user_meta($user_id, 'primary_billing_address', $billing_address_id);
				
			}
			
			do_action('checkout_updated_billing_address_meta', $billing_address_id, $posted);
	
			$this->billing_address_id = $billing_address_id;
			
		}
		
		$shipping_address_id = ! empty( $posted['ship_to_different_address'] ) || ! $billing_address_id || is_wp_error( $billing_address_id ) ? null : $billing_address_id;
		
		if( ! $shipping_address_id ) {

			// Shipping address.
			$shipping_address = array();
			
			if ( $shippingFields = array_keys(WC()->countries->get_address_fields( '', 'shipping_' )) ) {
				
				foreach ( $shippingFields as $field ) {
					
					delete_user_meta($user_id, $field);
					
					$shipping_address[ str_replace('shipping_', '', $field) ] = ! empty( $posted[$field] ) ? esc_sql( $posted[$field] ) : '';
					
				}
				
			}
			
			if( $shipping_address ) {
				
				$shipping_address_id = wc_create_address( $shipping_address );
				
			}
				
		}
		
		if( $shipping_address_id && ! is_wp_error( $shipping_address_id ) ) {
				
			if( $this->company_id ) {
				
				wc_add_company_address( $this->company_id, $shipping_address_id, 'shipping' );
				
				update_post_meta($this->company_id, '_primary_shipping_address', $shipping_address_id);
				
			} else {
				
				wc_add_user_address( $user_id, $shipping_address_id, 'shipping' );
				
				update_user_meta($user_id, 'primary_shipping_address', $shipping_address_id);
				
			}
			
			do_action('checkout_updated_shipping_address_meta', $shipping_address_id, $posted);
	
			$this->shipping_address_id = $shipping_address_id;
			
		}
		
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
	 * Rearranges billing fields and hides billing company field on checkout
	 *
	 * @param array $fields Array of billing fields
	 */
	public function rearrange_billing_fields($fields) {
		
		$offsetA = array_search( 'billing_last_name', array_keys( $fields ) );
		$offsetB = array_search( 'billing_phone', array_keys( $fields ) );
		
		$fields = array_slice($fields, 0, $offsetA + 1 , true) + [ 'billing_phone' => $fields['billing_phone'], 'billing_email' => $fields['billing_email']] + array_slice($fields, $offsetA + 1, $offsetB - $offsetA - 1, true);
		
		if( is_user_logged_in() ) {
			
			unset( $fields['billing_company'] );
			
		}
		
		return $fields;
		
	}
	
}
	
?>