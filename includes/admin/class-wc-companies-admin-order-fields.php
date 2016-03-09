<?php

if ( ! defined( 'ABSPATH' ) ) {
    
	exit; // Exit if accessed directly
	
}

class WC_Companies_Admin_Order_Fields {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
    	
    	add_filter( 'woocommerce_admin_billing_fields', array($this, 'remove_billing_company_field') );
		
		add_filter( 'woocommerce_admin_shipping_fields', array($this, 'add_shipping_address_field') );
		add_filter( 'woocommerce_admin_billing_fields', array($this, 'add_billing_address_field') );
		
		add_action( 'woocommerce_admin_order_data_after_order_details', array( $this, 'add_create_customer_button' ), 30 );
		add_action( 'woocommerce_admin_order_data_after_order_details', array( $this, 'add_company_field' ), 40 );
		
		add_action( 'save_post_shop_order', array( $this, 'maybe_save_company_to_order' ), 20 );
		add_action( 'save_post_shop_order', array( $this, 'maybe_save_addresses_to_company' ), 30 );
		add_action( 'save_post_shop_order', array( $this, 'maybe_save_addresses_to_customer' ), 40 );
		add_action( 'save_post_shop_order', array( $this, 'maybe_save_company_to_customer' ), 60 );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

		add_action( 'wp_ajax_get_address', array($this, 'ajax_get_address') );
			
	}
	
	public function remove_billing_company_field($fields) {
    	
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

				$reponse['data'] = [
					'_billing_first_name' => get_post_meta($_POST['address_id'],'_first_name',true),
					'_billing_last_name' => get_post_meta($_POST['address_id'],'_last_name',true),
					'_billing_address_1' => get_post_meta($_POST['address_id'],'_address_1',true),
					'_billing_address_2' => get_post_meta($_POST['address_id'],'_address_2',true),
					'_billing_city' => get_post_meta($_POST['address_id'],'_city',true),
					'_billing_postcode' => get_post_meta($_POST['address_id'],'_postcode',true),
					'_billing_country' => get_post_meta($_POST['address_id'],'_country',true),
					'_billing_state' => get_post_meta($_POST['address_id'],'_state',true),
					'_billing_email' => get_post_meta($_POST['address_id'],'_email',true),
					'_billing_phone' => get_post_meta($_POST['address_id'],'_phone',true),
				];
            	
        	} 
        	 	
    	}
    	
    	echo json_encode($reponse);
    	
    	die();
    	
	}

	public function admin_scripts()
	{
		wp_register_script('companies-order-admin', plugins_url() . '/woocommerce-companies/assets/js/admin/wc-companies-order.min.js');
		wp_enqueue_script('companies-order-admin');
		wp_localize_script('companies-order-admin', 'ajax_object', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
		));
	}

}


return new WC_Companies_Admin_Order_Fields();
