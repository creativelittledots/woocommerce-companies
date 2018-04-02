<?php

if ( ! defined( 'ABSPATH' ) ) {
    
	exit; // Exit if accessed directly
	
}

class WC_Companies_Admin_Order_Fields {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
    	
    	//add_filter( 'woocommerce_admin_shipping_fields', array($this, 'remove_company_field') );
    	//add_filter( 'woocommerce_admin_billing_fields', array($this, 'remove_company_field') );
		
		add_filter( 'woocommerce_admin_shipping_fields', array($this, 'add_shipping_address_field') );
		add_filter( 'woocommerce_admin_billing_fields', array($this, 'add_billing_address_field') );
		
		add_action( 'woocommerce_admin_order_data_after_order_details', array( $this, 'add_create_user_button' ), 30 );
		add_action( 'woocommerce_admin_order_data_after_order_details', array( $this, 'add_company_field' ), 40 );
		add_action( 'woocommerce_admin_order_data_after_order_details', array( $this, 'add_create_company_button' ), 50 );
		
		add_action( 'admin_footer', array($this, 'display_create_user_modal') );
		add_action( 'admin_footer', array($this, 'display_create_company_modal') );
		
		add_action( 'save_post', array( $this, 'maybe_save_company_to_order' ), 20, 2 );
		add_action( 'save_post', array( $this, 'maybe_create_addresses' ), 30, 2 );
		add_action( 'save_post', array( $this, 'maybe_save_company_to_customer' ), 40, 2 );
		
		add_action( 'admin_enqueue_scripts', array($this, 'enqueue_scripts') );
			
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
		
		global $post;
		
		if( $order = wc_get_order( $post ) ) {
    	
        	$addressesFound = array();
        		
    		if( $order->get_user_id() ) {
    		
        		$addressesFound = $addressesFound + wc_get_user_all_addresses( $order->get_user_id() );
        		
            }
            
            if( $order->get_meta('_company_id') ) {
                
                $addressesFound = $addressesFound + wc_get_company_addresses( $order->get_meta('_company_id') );
                
            }
        	
        	$addressesFound = array_unique( $addressesFound );
    		
    		foreach($addressesFound as $address) {
        		
        		$addresses[$address->id] = $address->get_title();
        		
    		}
    		
        }
		
		return array($type . '_address_id' => array(
    		'id' => '_' . $type . '_address_id',
			'label' => __( 'Address', 'woocommerce' ),
			'class' => 'wc-enhanced-select js-address-select',
			'wrapper_class' => 'form-field-wide',
			'custom_attributes' => array(
    			'data-address_type' => $type,
    			'data-nonce' => wp_create_nonce( 'get-address' )
			),
			'type' => 'select',
			'description' => 'Please select ' . $type . ' address',
			'options' => $addresses,
		));
		
	}
	
	public function add_create_user_button() {
    	
		echo '<p class="form-field"><a href="#TB_inline?width=600&height=550&inlineId=create-user-form" title="Create a Customer" data-object="User" class="thickbox button">'.__('Create a Customer', 'wo$ocommerce').'</a></p>';
		
	}
	
	public function add_company_field($order) {
    	
    	$company = wc_get_company($order->get_meta('_company_id'));

		woocommerce_form_field( '_company_id', array(
			'label' => __( 'Company:' ),
			'class' => array('form-field', 'form-field-wide'),
			'input_class' => array('wc-advanced-search wc-company-search'),
			'defaults' => $company ? [$company->get_id() => $company->get_title()] : [],
			'custom_attributes' => array(
    			'data-allow_clear' => true,
    			'data-placeholder' => 'Company',
    			'data-action' => 'woocommerce_json_search_companies',
                'data-nonce' => wp_create_nonce( 'search-companies' )
			),
			'type' => 'advanced_search',
			'default' => $company ? $company->id : '',
		) );
		
	}
	
	public function add_create_company_button() {
    	
    	echo '<p class="form-field"><a href="#TB_inline?width=600&height=550&inlineId=create-company-form" title="Create a Company" data-object="Company" class="thickbox button">'.__('Create a Company', 'woocommerce').'</a></p>';
		
	}
	
	public function display_create_user_modal() {
    	
    	global $post;
    	
    	if( $post ) {
    	
	    	if( 'shop_order' === $post->post_type ) {
	        	
	        	$fields = array(
	            	'user_login' => array(
	    				'label' => __('Username', 'woocommerce'),
	    				'type' => 'text',
	    				'required' => true,
	    				'input_class' => array('widefat'),
	    			),
	    			'user_email' => array(
	    				'label' => __('Email Address', 'woocommerce'),
	    				'type' => 'email',
	    				'required' => true,
	    				'input_class' => array('widefat'),
	    			),
	    			'first_name' => array(
	    				'label' => __('First Name', 'woocommerce'),
	    				'type' => 'text',
	    				'required' => true,
	    				'input_class' => array('widefat'),
	    			),
	    			'last_name' => array(
	    				'label' => __('Last Name', 'woocommerce'),
	    				'type' => 'text',
	    				'required' => true,
	    				'input_class' => array('widefat'),
	    			),
	    			'send_user_notification' => array(
	    				'label' => __('Send User Notification?', 'woocommerce'),
	    				'type' => 'checkbox',
	    			)
	        	);
	        	
	        	ob_start();
			
	    		include('meta-boxes/views/html-create-user.php');
	    		
	    		$html = ob_get_contents();
	    		
	    		ob_end_clean();
	    		
	    		echo $html;
	        	
	        }
	        
		}
    	
	}
	
	public function display_create_company_modal() {
    	
        global $post;
    	
    	if( is_object($post) && 'shop_order' === $post->post_type ) {
        	
        	$fields = array_filter(WC_Meta_Box_Company_Data::init_company_fields(), function($field) {
            	return isset($field['quick_edit']) && $field['quick_edit'];
            });
        	
        	ob_start();
		
    		include('meta-boxes/views/html-create-company.php');
    		
    		$html = ob_get_contents();
    		
    		ob_end_clean();
    		
    		echo $html;
        	
        }
    	
	}

	public function maybe_save_company_to_order( $post_id, $post ) {
    	
    	if( $post->post_type !== 'shop_order' ) {
        	return;
    	}
    	
    	$company_id = ! empty( $_POST['_company_id'] ) ? $_POST['_company_id'] : null;
    	
    	update_post_meta($post_id, '_company_id', $company_id);
			
		if( $company_id && ( $company = wc_get_company( $company_id ) ) ) {
		    
		    update_post_meta($post_id, '_billing_company', $company->get_title());
		    
		    update_post_meta($post_id, '_shipping_company', $company->get_title());
		    
        }
		
	}
	
	public function maybe_create_addresses( $post_id, $post ) {
    	
    	if( $post->post_type !== 'shop_order' ) {
        	return;
    	}
    	
    	if( $order = wc_get_order( $post_id ) ) {
        	
        	$billing_address = $order->get_address();
        	
        	if( $billing_address && ! empty( $billing_address['address_1'] ) ) {
            	
            	$billing_address_id = wc_create_address( $billing_address );
            	
            	if( $billing_address_id && ! is_wp_error( $billing_address_id ) ) {
                	
                	if( $order->get_meta('_company_id') && $company = wc_get_company( $order->get_meta('_company_id') ) ) {
                    	
                    	wc_add_company_address( $company->id, $billing_address_id );
                    	
                	}
                	
                	if( $user_id = $order->get_user_id() ) {
        		
                		wc_add_user_address( $user_id, $billing_address_id );
                		
            		}
            		
            		update_post_meta($post_id, '_billing_address_id', $billing_address_id);
                	
            	}
            	
        	}
        	
        	$shipping_address = $order->get_address( 'shipping' );
        	
        	if( $shipping_address && ! empty( $shipping_address['address_1'] ) ) {
            	
            	$shipping_address_id = wc_create_address( $shipping_address );
            	
            	if( $shipping_address_id && ! is_wp_error( $shipping_address_id ) ) {
                	
                	if( $order->get_meta('_company_id') && $company = wc_get_company( $order->get_meta('_company_id') ) ) {
                    	
                    	wc_add_company_address( $company->id, $shipping_address_id, 'shipping' );
                    	
                	}
                	
                	if( $user_id = $order->get_user_id() ) {
        		
                		wc_add_user_address( $user_id, $shipping_address_id, 'shipping' );
                		
            		}
            		
            		update_post_meta($post_id, '_shipping_address_id', $shipping_address_id);
                	
            	}
            	
        	}
        	
    	}
    	
	}
	
	public function maybe_save_company_to_customer( $post_id, $post ) {
    	
    	if( $post->post_type !== 'shop_order' ) {
        	return;
    	}
    	
    	if( $order = wc_get_order( $post_id ) ) {
        	
            if( $order->get_meta('_company_id') && ( $company = wc_get_company( $order->get_meta('_company_id') ) ) && $user_id = $order->get_user_id() ) {
            	
                wc_add_user_company( $user_id, $company->id );
            	
            }
        	
        }
    	
	}
	
	public function enqueue_scripts() {
    	
    	$screen = get_current_screen();
    	
    	if( $screen->id === 'shop_order' ) {
    		
    		wp_enqueue_script( 'thickbox' );
            
            wp_enqueue_style( 'thickbox' );
            
            $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
            
            wp_register_script( 'order-fields-js', WC_Companies()->plugin_url() . '/assets/js/admin/wc-companies-order-fields' . $suffix . '.js', array('jquery', 'wc-companies-admin-general'), '1.0.0', true );
            
            wp_localize_script( 'order-fields-js', 'wc_companies_order_fields', apply_filters( 'wc_companies_admin_order_fields_data', array( 'ignore_fields' => array(
	            '_billing_first_name',
	            '_billing_last_name',
	            '_billing_email',
	            '_shipping_first_name',
	            '_shipping_last_name',
	            '_shipping_method_title'
            ) ) ) );
            
            wp_enqueue_script( 'order-fields-js' );

    	}
    	
	}
	
}


return new WC_Companies_Admin_Order_Fields();
