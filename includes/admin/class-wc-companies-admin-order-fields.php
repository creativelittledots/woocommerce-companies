<?php

if ( ! defined( 'ABSPATH' ) ) {
    
	exit; // Exit if accessed directly
	
}

class WC_Companies_Admin_Order_Fields {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
    	
    	add_filter( 'woocommerce_admin_shipping_fields', array($this, 'remove_company_field') );
    	add_filter( 'woocommerce_admin_billing_fields', array($this, 'remove_company_field') );
		
		add_filter( 'woocommerce_admin_shipping_fields', array($this, 'add_shipping_address_field') );
		add_filter( 'woocommerce_admin_billing_fields', array($this, 'add_billing_address_field') );
		
		add_action( 'woocommerce_admin_order_data_after_order_details', array( $this, 'add_create_customer_button' ), 30 );
		add_action( 'woocommerce_admin_order_data_after_order_details', array( $this, 'add_company_field' ), 40 );
		
		add_action( 'save_post_shop_order', array( $this, 'maybe_save_company_to_order' ), 20 );
		add_action( 'save_post_shop_order', array( $this, 'maybe_save_addresses_to_company' ), 30 );
		add_action( 'save_post_shop_order', array( $this, 'maybe_save_addresses_to_customer' ), 40 );
		add_action( 'save_post_shop_order', array( $this, 'maybe_save_company_to_customer' ), 60 );
		
		add_action( 'wp_ajax_get_address', array($this, 'ajax_get_address') );
		add_action( 'admin_enqueue_scripts', array($this, 'maybe_enqueue_order_fields_script') );
			
	}
	
	public function remove_company_field($fields) {
    	
    	unset($fields['company']);
    	
    	return $fields;
    	
	}
	
	public function add_shipping_address_field($fields) {
		
		return $this->add_address_field('shipping') + $fields;

		
	}
	
	public function add_billing_address_field($fields) {
		
		return $this->add_address_field() + $fields;
		
	}

	private function add_address_field($type = 'billing') {
		
		$addresses = array(
			0 => 'None',
		);
		
		foreach(wc_get_addresses() as $address) {
			
			$addresses[$address->id] = $address->get_title();
			
		}
		
		return array($type . '_address_id' => array(
    		'id' => '_' . $type . '_address_id',
			'label' => __( 'Address', 'woocommerce' ),
			'class' => 'wc-enhanced-select js-address-select',
			'wrapper_class' => 'form-field-wide',
			'custom_attributes' => array(
    			'data-address_type' => $type,
			),
			'type' => 'select',
			'description' => 'Please select ' . $type . ' address',
			'options' => $addresses,
		));
		
	}
	
	public function add_create_customer_button() {
    	
		echo '<p class="form-field form-field-wide"><a href="#" class="js-customer-button button">'.__('Create a Customer', 'woocommerce').'</a></p>';

	}
	
	public function add_company_field() {

		echo woocommerce_wp_select( array(
    		'id' => '_company_id',
			'label' => __( 'Companies' ),
			'class' => 'wc-enhanced-select company',
			'wrapper_class' => 'form-field-wide',
			'type' => 'select',
			'description' => 'Please select company',
			'options' => $this->get_companies(),
		));
		
		
		echo '<p class="form-field form-field-wide"><a href="#" class="js-company-button button">'.__('Create a Company', 'woocommerce').'</a></p>';
	}
	
	private function get_companies() {
    	
		$companies = array();

		foreach(wc_get_companies() as $company) {

			$companies[$company->id] = $company->title;

		}

		return $companies;
		
	}

	public function maybe_save_company_to_order( $post_id ) {
			
		if( isset( $_POST['_company_id'] ) ) {
    		
    		if( $company = wc_get_company( $_POST['_company_id'] ) ) {
    		
    		    update_post_meta($post_id, '_company_id', $_POST['_company_id']);
    		    
    		    update_post_meta($post_id, '_billing_company', $company->get_title());
    		    update_post_meta($post_id, '_shipping_company', $company->get_title());
    		    
            }
    		
		}
		
	}
	
	public function maybe_save_addresses_to_company( $post_id ) {
			
		if( isset( $_POST['_company_id'] ) ) {
    		
    		if( $company = wc_get_company( $_POST['_company_id'] ) ) {
        		
        		if( isset( $_POST['billing_address_id'] ) ) {
            		
            		add_company_address( $_POST['_company_id'], $_POST['billing_address_id'] );
            		
        		}
        		
        		if( isset( $_POST['shipping_address_id'] ) ) {
            		
            		add_company_address( $_POST['_company_id'], $_POST['shipping_address_id'], 'shipping' );
            		
        		}
        		
    		}
    		
        }
		
	}

	public function maybe_save_addresses_to_customer( $post_id ) {
			
		if( isset( $_POST['customer_user'] ) ) {
    		
    		if( $customer = get_user_by( 'id', $_POST['customer_user'] ) ) {
        		
        		if( isset( $_POST['billing_address_id'] ) ) {
            		
            		add_user_address( $_POST['customer_user'], $_POST['billing_address_id'] );
            		
        		}
        		
        		if( isset( $_POST['shipping_address_id'] ) ) {
            		
            		add_user_address( $_POST['customer_user'], $_POST['shipping_address_id'], 'shipping' );
            		
        		}
        		
    		}
    		
        }
		
	}
	
	public function maybe_save_company_to_customer( $post_id ) {
    	
    	if( isset( $_POST['_company_id'] ) && isset( $_POST['customer_user'] ) ) {
        	
        	$company = wc_get_company( $_POST['_company_id'] );
        	$customer = get_user_by( 'id', $_POST['customer_user'] );
    		
    		if( $company && $customer ) {
        		
        		add_user_company( $_POST['customer_user'], $_POST['_company_id'] );
        		
    		}
    		
        }
    	
	}
	
	public function ajax_get_address() {
    	
    	$reponse = array(
        	'request' => $_POST
    	);
    	
    	if( isset( $_POST['address_id'] ) ) {
        	
        	if( $address = wc_get_address($_POST['address_id']) ) {
            	
            	$reponse['address'] = $address;
            	
        	} 
        	 	
    	}
    	
    	echo json_encode($reponse);
    	
    	exit();
    	
	}
	
	public function maybe_enqueue_order_fields_script() {
    	
    	$screen = get_current_screen();
    	
    	if( $screen->id === 'shop_order' ) {
        	
        	wp_enqueue_script( 'order-fields-js', WC_Companies()->plugin_url() . '/assets/js/admin/wc-companies-order-fields.js', array('jquery'), '1.0.0', true );
        	
    	}
    	
	}

}


return new WC_Companies_Admin_Order_Fields();
