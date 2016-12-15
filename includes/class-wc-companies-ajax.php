<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WooCommerce WC_Companies_AJAX
 *
 * AJAX Event Handler
 *
 * @class 		WC_Companies_AJAX
 * @version		2.2.0
 * @package		WooCommerce Companies/Classes
 * @category	Class
 * @author 		Creative Little Dots
 */
class WC_Companies_AJAX extends WC_Ajax {

	/**
	 * Hook in methods
	 */
	public static function init() {

		// woocommerce_EVENT => nopriv
		$ajax_events = array(
			'json_get_addresses' => false,
			'json_search_addresses' => false,
			'json_search_companies' => false,
			'json_create_user' => false,
			'json_create_company' => false,
			'json_get_address' => false,
			'json_get_company' => false,
			'json_get_user_company_addresses' => false,
			'json_get_merge_field_values' => false
		);

		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_woocommerce_' . $ajax_event, array( __CLASS__, $ajax_event ) );

			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_woocommerce_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			}
		}
		
	}
	
	/**
	 * Get companies addresses
	 */
	public static function json_get_addresses() {
		
		check_ajax_referer( 'get-addresses', 'security' );
		
		global $woocommerce_companies;
		
		$addresses = [];
		
		if( is_user_logged_in() ) {
			
			$checkout_type = $_POST['checkout_type'];
			
			$company_id = $_POST['company_id'];
			
			$address_type = $_POST['address_type'];
		
			if($checkout_type == 'company' && $company_id > 0 && $company = wc_get_company($company_id)) {
			
				$method = sprintf( 'get_%s_addresses', $address_type );
					
				$addresses = $addresses + $company->$method();
				
			} else {
				
				$addresses = wc_get_user_addresses( get_current_user_id(), $address_type );
				
			}
			
		}
		
		// Get messages if reload checkout is not true
		$messages = '';
		if ( ! isset( WC()->session->reload_checkout ) ) {
			ob_start();
			wc_print_notices();
			$messages = ob_get_clean();
		}
		
		$data = array(
			'result'    => empty( $messages ) ? 'success' : 'failure',
			'messages'  => $messages,
			'reload'    => isset( WC()->session->reload_checkout ) ? 'true' : 'false',
			'addresses' => $addresses
		);
		
		wp_send_json( $data );
		
		die();
		
	}
	
	/**
	 * Search for addresses
	 */
	public static function json_search_addresses() {
			
		ob_start();
		
		check_ajax_referer( 'search-addresses', 'security' );

		$term = wc_clean( stripslashes( $_GET['term'] ) );

		if ( empty( $term ) ) {
			die();
		}

		$args = array(
			's' => $term,
		);
		
		$addresses = wc_get_addresses($args);
		
		$addresses_found = array();
		
		foreach( $addresses as $address) {
			
			$addresses_found[$address->id] = $address->get_title();
			
		}

		wp_send_json( $addresses_found );
		
	}
	
	/**
	 * Search for companies
	 */
	public static function json_search_companies() {
    	
    	ob_start();

		check_ajax_referer( 'search-companies', 'security' );

		$term = wc_clean( stripslashes( $_GET['term'] ) );

		if ( empty( $term ) ) {
    		
			die();
			
		}

		$found_companies = array();
		
		global $wpdb;

		$company_ids = $wpdb->get_results("SELECT ID FROM {$wpdb->posts} JOIN {$wpdb->postmeta} ON ID = post_id AND meta_key = '_accounting_reference' WHERE post_type = 'wc-company' AND ( post_title LIKE '%$term%' OR meta_value LIKE '%$term%' )");
		
		$companies = wc_get_companies(array(
    		'post__in' => $company_ids ? array_map(function($company) {
        		return $company->ID;
            }, $company_ids) : array(0)
		));

		if ( ! empty( $companies ) ) {
    		
			foreach ( $companies as $company ) {
    			
				$found_companies[ $company->id ] = $company->get_title() . ( $company->accounting_reference ? ' - ' . $company->accounting_reference : '' );				
			}
			
		}

		wp_send_json( $found_companies );
    	 
	}
	
	public static function json_create_user() {
    	
    	ob_start();

		check_ajax_referer( 'create-user', 'security' );
		
		if ( ! username_exists( $_POST['user_login'] ) && ! email_exists( $_POST['user_email'] ) ) {
    		
    		$user_data = [
        		'user_login' => $_POST['user_login'],
        		'user_email' => $_POST['user_email'],
        		'first_name' => isset( $_POST['first_name'] ) && ! empty( $_POST['first_name'] ) ? $_POST['first_name'] : '',
        		'last_name' => isset( $_POST['last_name'] ) && ! empty( $_POST['last_name'] ) ? $_POST['last_name'] : '',
        		'user_pass' => wp_generate_password( 12, false ),
    		];
    		
    		$user_id = wp_insert_user( $user_data );

			if( $user_id && ! is_wp_error( $user_id ) ) {
    			
    			$user = get_user_by( 'id',  $user_id );

    			$user->set_role( 'customer' );
    
    			if( isset( $_POST['send_user_notification'] ) && $_POST['send_user_notification'] ) {
        			
        			wp_new_user_notification( $user_id );
        			
    			}
    
    			$response = [
    				'response' => 'success',
    				'object_id' => $user_id,
    				'object_title' => $user->display_name,
    				'object' => $user,
    				'message' => sprintf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-success', "User {$_POST['user_login']} was successfully created" )
    			];
    			
			} else {
    			
    			$message = is_wp_error($user_id) ? $user_id->get_error_message() : 'There was an error, please try again';
    			
    			$response = [
    				'response' => 'failure',
    				'message' => sprintf( '<div class="%1$s"><p>%2$s</p></div>', 'error', $message )
    			];
    			
			}
			
		} else {
    		
    		$message = username_exists( $_POST['user_login'] ) ? "User {$_POST['user_login']} already exists" : "Email address {$_POST['user_email']} already exists";
    		
    		$response = [
    			'response' => 'error',
    			'message' => sprintf( '<div class="%1$s"><p>%2$s</p></div>', 'error', $message )
    		];
    		
		}
		
		wp_send_json( $response );
		
	}
	
	public static function json_create_company() {
    	
    	ob_start();

		check_ajax_referer( 'create-company', 'security' );
    	
		if ( ! get_page_by_title( $_POST['company_name'], OBJECT, 'wc-company' ) ) {
    		
    		$fields = array_filter(WC_Meta_Box_Company_Data::init_company_fields(), function($field) {
            	return isset($field['quick_edit']) && $field['quick_edit'];
            });
            
            $args = array();
            
            foreach($fields as $key => $field) {
                
                if( isset( $_POST[$key] ) ) {
                    
                    $args[$key] = $_POST[$key];
                    
                }
                
            }
            
            $company_id = wc_create_company($args);
    		
    		if( $company_id && ! is_wp_error( $company_id ) ) {
        		
        		$company = wc_get_company( $company_id );
        		
        		$response = [
            		'args' => $args,
    				'response' => 'success',
    				'object_id' => $company_id,
    				'object_title' => $company->get_title(),
    				'object' => $company,
    				'message' => sprintf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-success', "Company {$_POST['company_name']} was successfully created" )
    			];
        		
    		} else {
        		
        		$message = is_wp_error($company_id) ? $company_id->get_error_message() : 'There was an error, please try again';
        		
        		$response = [
    				'response' => 'failure',
    				'message' => sprintf( '<div class="%1$s"><p>%2$s</p></div>', 'error', $message )
    			];
        		
    		}
    		
		} else {
    		
    		$response = [
    			'response' => 'error',
    			'message' => sprintf( '<div class="%1$s"><p>%2$s</p></div>', 'error', "Company {$_POST['company_name']} already exists" )
    		];
    		
		}

		wp_send_json( $response );
	
	}
	
	public static function json_get_address() {
    	
    	ob_start();

		check_ajax_referer( 'get-address', 'security' );
    	
    	if( isset( $_POST['address_id'] ) && ! empty( $_POST['address_id'] ) ) {
        	
        	if( $address = wc_get_address($_POST['address_id']) ) {
            	
            	$response['address'] = $address;
            	
        	} 
        	 	
    	}
    	
    	wp_send_json( $response );
    	
	}
	
	public static function json_get_company() {
    	
    	ob_start();

		check_ajax_referer( 'get-company', 'security' );
    	
    	if( isset( $_POST['company_id'] ) && ! empty( $_POST['company_id'] ) ) {
        	
        	if( $company = wc_get_company( $_POST['company_id'] ) ) {
            	
            	$response['company'] = $company;
            	
        	} 
        	 	
    	}
    	
    	wp_send_json( $response );
    	
	}
	
	public static function json_get_user_company_addresses() {
    	
        $response = array(
        	'request' => $_POST,
    	);
    	
    	$addresses = array();
    	
    	if( isset( $_POST['user_id'] ) && ! empty( $_POST['user_id'] )  ) {
        	
        	$addresses = $addresses + wc_get_user_all_addresses( $_POST['user_id'] );
        	
    	}
    	
    	if( isset( $_POST['company_id'] ) && ! empty( $_POST['company_id'] ) ) {
        	
        	$addresses = $addresses + wc_get_company_addresses( $_POST['company_id'] );
        	 	
    	}
    	
    	$addresses = array_unique($addresses);
    	
    	array_unshift($addresses, (object) array(
        	    'id' => 0,
        	    'title' => 'None'
    	    )
        );
        
        $response['addresses'] = $addresses;
    	
    	wp_send_json( $response );
    	
	}
	
	public static function json_get_merge_field_values() {
		
		$response = array(
        	'request' => $_POST,
    	);
    	
    	$response['fields'] = [];
    	
    	$post_type = $_POST['post_type'];
    	
    	$posts = get_posts( array( 
	    	'post_type' => $post_type,
			'post__in' => ! empty( $_POST['ids'] ) ? $_POST['ids'] : array(0),
			'order' => 'DESC',
			'orderby' => 'modified',
			'showposts' => -1
		) );
    	
    	switch( $post_type ) {
	    	
	    	case 'wc-company' :
	    	
	    		$fields = WC_Companies()->addresses->get_company_fields(true);
	    		
	    		foreach($fields as $key => $field) {
		    		
		    		$value = get_post_meta( reset( $posts )->ID, '_' . $key, true );
		    		$options = array();
		    		
		    		switch( $key ) {
			    		
			    		case 'primary_billing_address' :
			    		case 'primary_shipping_address' :
			    		case 'billing_addresses' :
			    		case 'shipping_addresses' :
			    		
			    			$value = is_array($value) ? implode(',', $value) : $value;
			    			
			    			$meta_key = strpos($key, 'billing') > -1 ? 'billing_addresses' : 'shipping_addresses';
			    			
			    			foreach($posts as $post) {
				    			
				    			$addresses = get_post_meta( $post->ID, '_' . $meta_key, true );
				    			
				    			$options = array_merge($options, is_array($addresses) ? $addresses : array());
				    			
			    			}
			    			
			    			$options = array_unique($options);
			    			
			    			$options = array_map(function($address) {
				    			
				    			$address = wc_get_address( $address );
				    			
				    			return [
					    			'id' => $address->id,
					    			'text' => $address->get_title()
				    			];
				    			
			    			}, $options);
			    		
			    		break;
			    		
		    		}
	    	
			    	$response['fields'][$key] = [
			    		'value' => $value,
			    		'options' => $options
			    	];
			    	
		    	}
	    	
	    	break;
	    	
	    	case 'wc-address' :
	    	
	    		$fields = WC_Companies()->addresses->get_admin_address_fields(true);
	    		
	    		foreach($fields as $key => $field) {
	    	
			    	$value = get_post_meta( reset( $posts )->ID, '_' . $key, true );
		    		$options = array();
		    		
		    		$response['fields'][$key] = [
			    		'value' => $value,
			    		'options' => $options
			    	];
			    	
		    	}
	    	
	    	break;
	    	
    	}
    	
    	wp_send_json( $response );
		
	}

}

WC_Companies_AJAX::init();