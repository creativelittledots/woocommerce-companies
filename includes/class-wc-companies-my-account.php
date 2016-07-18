<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WooCommerce WC_Companies_Display
 *
 *
 * @class 		WC_Companies_Display
 * @version		1.0.0
 * @package		WooCommerce Companies/Classes
 * @category	Class
 * @author 		Creative Little Dots
 */
class WC_Companies_My_Account {
	
	/**
	 * Hook in methods
	 */
	public function __construct() {
		
		add_filter( 'woocommerce_account_menu_items', array($this, 'account_menu_items') );
		
		add_action( 'woocommerce_account_companies_endpoint', array($this, 'display_companies') );
		
		add_action( 'woocommerce_account_edit-company_endpoint', array($this, 'edit_company') );
		
		add_action( 'woocommerce_account_add-company_endpoint', array($this, 'add_company') );
		
		add_action( 'woocommerce_account_remove-company_endpoint', array($this, 'remove_company') );
		
		add_action( 'woocommerce_account_primary-company_endpoint', array($this, 'primary_company') );
		
		add_action( 'woocommerce_account_company-addresses_endpoint', array($this, 'company_addresses') );
		
		add_action( 'woocommerce_account_company-primary-address_endpoint', array($this, 'company_primary_address') );
		
		add_action( 'woocommerce_account_company-remove-address_endpoint', array($this, 'company_remove_address') );
		
		add_action( 'woocommerce_account_addresses_endpoint', array($this, 'display_addresses') );
		
		add_action( 'woocommerce_account_view-address_endpoint', array($this, 'view_address') );
		
		add_action( 'woocommerce_account_add-address_endpoint', array($this, 'add_address') );

		add_action( 'woocommerce_account_remove-address_endpoint', array($this, 'remove_address') );
		
		add_action( 'woocommerce_account_primary-address_endpoint', array($this, 'primary_address') );
		
		add_filter( 'woocommerce_my_account_edit_address_title', array($this, 'my_account_edit_address_title') );
		
		add_filter( 'woocommerce_companies_view_addresses_title', array($this, 'companies_view_addresses_title') );
		
		add_filter( 'woocommerce_my_account_my_orders_query', array($this, 'my_account_my_orders_query') );
		
		add_action( 'woocommerce_view_order', array($this, 'display_company_and_user') );
		
		add_filter( 'woocommerce_companies_edit_company_fields', array($this, 'companies_edit_company_fields'), 10, 2 );
		
		add_filter( 'woocommerce_companies_add_company_fields', array($this, 'companies_add_company_fields'), 10 );
		
		add_filter( 'woocommerce_my_account_edit_address_field_value', array($this, 'my_account_edit_address_field_value'), 10, 3);
		
		add_filter( 'woocommerce_my_account_my_address_formatted_address', array($this, 'my_account_my_address_formatted_address'), 10, 3);
		
	}
	
	public function account_menu_items( $items ) {
			
		$items = array_slice($items, 0, 3, true) + array(
			'companies' => __( 'Companies', 'woocommerce' ),
			'addresses' => __( 'Addresses', 'woocommerce' ),
		) + array_slice($items, 3, null, true);
		
		unset( $items['edit-address'] );
	    
	    return $items;
		
	}
	
	public function display_companies() {
		
		global $current_user;
		
		$companies = wc_get_user_companies();
		
		if($current_user->primary_company) {
			
			if( $found_company = array_search($current_user->primary_company, $companies) ) {
				
				unset($companies[$found_company]);
				
			}
			
			$companies = array_unshift($companies, wc_get_company($current_user->primary_company));
			
		}
		
		wc_get_template('myaccount/companies.php', array(
			'current_user' 	=> get_user_by( 'id', get_current_user_id() ),
			'company_count' 	=> 'all' == $company_count ? -1 : $company_count,
			'companies' => wc_get_user_companies(),
		), '', WC_Companies()->plugin_path() . '/templates/');
		
	}
	
	public function primary_company( $company_id ) {
		
		$user_id = get_current_user_id();
		
		if ( ! current_user_can( 'make_primary_company', $company_id ) ) {
			
			wc_print_notice(__( 'Invalid company.', 'woocommerce-companies' ) . ' <a href="' . wc_get_page_permalink( 'myaccount' ).'" class="wc-forward">'. __( 'My Account', 'woocommerce' ) .'</a>', 'error');
			
		}
		else {
		
			if( update_user_meta( $user_id, 'primary_company', $company_id) ) {
				
				wc_add_notice( __( 'Company saved as primary company successfully.', 'woocommerce-companies' ) );
				
				do_action( 'woocommerce_companies_make_primary_company', $user_id, $company_id );
	
				wp_safe_redirect( wc_get_account_endpoint_url( 'companies' ) );
				
				exit();
				
			}
			
		}
		
	}
	
	public function edit_company( $company_id ) {
		
		$user_id      	= get_current_user_id();
		$company 		= wc_get_company( $company_id );
		
		if ( ! empty( $_POST[ 'action' ] ) && 'save_company' == $_POST[ 'action' ] && ! empty( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'woocommerce-save_company' ) ) {
			
			foreach(WC_Companies()->addresses->get_company_fields() as $key => $field) {
				
				$key = preg_replace('/[^A-Za-z0-9_\-]/', '', $key);
				
				if ( $field['public'] && $field['required'] && empty($_POST[$key]) ) {
					
					wc_add_notice( __( $field['label'] . ' is a required field.', 'woocommerce-companies' ), 'error' );
					
				}
				
				if( ! empty( $_POST[$key] ) ) {
					
					$company->$key = $_POST[$key];
					
				}
					
			}
	
			if ( wc_notice_count( 'error' ) == 0 ) {
				
				$company->save();
				
				wc_add_notice( __( 'Company updated successfully.', 'woocommerce-companies' ) );
	
				do_action( 'woocommerce_companies_save_company', $user_id, $company->id );
				
			}
			
		}
		
		do_action( 'woocommerce_before_my_account' );
		
		if ( ! current_user_can( 'edit_company', $company_id ) ||  empty($company->post) ) {
			wc_print_notice(__( 'Invalid company.', 'woocommerce-companies' ) . ' <a href="' . wc_get_page_permalink( 'myaccount' ).'" class="wc-forward">'. __( 'My Account', 'woocommerce' ) .'</a>', 'error');
			return;
		}
		
		wc_get_template('myaccount/edit-company.php', array(
			'current_user' 	=> get_user_by( 'id', get_current_user_id() ),
			'company'   => $company,
			'fields'		=> apply_filters('woocommerce_companies_edit_company_fields', WC_Companies()->addresses->get_company_fields(), $company_id),
		), '', WC_Companies()->plugin_path() . '/templates/');
		
	}
	
	public function add_company() {
		
		$user_id      	= get_current_user_id();
		
		do_action( 'woocommerce_before_my_account' );
		
		if ( ! current_user_can( 'add_company' ) ) {
			wc_print_notice(__( 'You do not have the privelages to add companies.', 'woocommerce-companies' ) . ' <a href="' . wc_get_page_permalink( 'myaccount' ).'" class="wc-forward">'. __( 'My Account', 'woocommerce' ) .'</a>', 'error');
		}
		
		wc_get_template('myaccount/edit-company.php', array(
			'current_user' 	=> get_user_by( 'id', get_current_user_id() ),
			'company'   => false,
			'fields'		=> apply_filters('woocommerce_companies_add_company_fields', WC_Companies()->addresses->get_company_fields()),
		), '', WC_Companies()->plugin_path() . '/templates/');
		
	}
	
	public function remove_company( $company_id ) {
		
		$user_id = get_current_user_id();
		
		if ( ! current_user_can( 'remove_company', $company_id ) ) {
			
			wc_print_notice(__( 'You do not have the privelages to remove this company.', 'woocommerce-companies' ) . ' <a href="' . wc_get_page_permalink( 'myaccount' ).'" class="wc-forward">'. __( 'My Account', 'woocommerce' ) .'</a>', 'error');
			
		}
		else {
			
			$company = wc_get_company($company_id);
		
			if($company->delete()) {
				
				wc_add_notice( __( 'Company removed successfully.', 'woocommerce-companies' ) );
				
				do_action( 'woocommerce_companies_remove_company', $user_id, $company_id );
	
				wp_safe_redirect( wc_get_account_endpoint_url( 'companies' ) );
				
				exit();
				
			}
			
		}
		
	}
	
	public function company_addresses( $company_id, $address_count = 'all' ) {
		
		$company = wc_get_company($company_id);
		
		$addresses = $company->get_billing_addresses() + $company->get_shipping_addresses();
				
		wc_get_template('myaccount/addresses.php', array(
			'object' 	=>  $company,
			'address_count' 	=> 'all' == $address_count ? -1 : $address_count,
			'addresses' => $addresses,
		), '', WC_Companies()->plugin_path() . '/templates/');
		
	}
	
	public function company_primary_address( $company_id ) {
		
		$address_id = ! empty( $_REQUEST['address_id'] ) ? $_REQUEST['address_id'] : false;
		$address_type = ! empty( $_REQUEST['address_type'] ) ? $_REQUEST['address_type'] : false;
		
		$user_id = get_current_user_id();
		
		if ( ! current_user_can( 'make_primary_company_address', $company_id, $address_id ) ) {
			
			wc_add_notice(__( 'Invalid company or address.', 'woocommerce-companies' ) . ' <a href="' . wc_get_page_permalink( 'myaccount' ).'" class="wc-forward">'. __( 'My Account', 'woocommerce' ) .'</a>', 'error');
			
		}
		else {
		
			if( update_post_meta( $company_id, '_primary_' . $address_type . '_address', $address_id) ) {
				
				wc_add_notice( __( 'Address saved as primary company ' . $address_type . ' address successfully.', 'woocommerce-companies' ) );
				
				do_action( 'woocommerce_companies_make_primary_company_address', $user_id, $company_id, $address_id, $address_type );
				
			} else {
				
				wc_add_notice( __( 'There was an error, please try again.', 'woocommerce-companies' ), 'error' );
				
			}
			
		}
		
		$company = wc_get_company( $company_id );
	
		wp_safe_redirect( $company->get_view_company_addresses_url() );
		
		exit();
		
	}
	
	public function company_remove_address() {
		
		
		
	}
	
	public function display_addresses() {
		
		
		$addresses = wc_get_user_all_addresses( get_current_user_id() );
				
		wc_get_template('myaccount/addresses.php', array(
			'object' 	=> get_user_by( 'id', get_current_user_id() ),
			'address_count' 	=> 'all' == $address_count ? -1 : $address_count,
			'addresses' => $addresses,
		), '', WC_Companies()->plugin_path() . '/templates/');
		
	}
	
	public function view_address( $address_id, $load_address = 'billing' ) {
		
		$user_id      	= get_current_user_id();
		$address		= wc_get_address( $address_id );
		
		if ( ! current_user_can( 'edit_address', $address_id ) ) {
			
			wc_print_notice(__( 'Invalid address.', 'woocommerce-companies' ) . ' <a href="' . wc_get_page_permalink( 'myaccount' ).'" class="wc-forward">'. __( 'My Account', 'woocommerce' ) .'</a>', 'error');

			return;

		}
		
		$fields = WC_Companies()->addresses->get_address_fields();
		
		foreach($fields as $key => $field) {
			
			$field['value'] = $address->$key;
			
			$fields[$load_address . '_' . $key] = $field;
			
			unset($fields[$key]);
			
		}

		wc_get_template( 'myaccount/form-edit-address.php', array(
			'load_address' 	=> $load_address,
			'address'		=> apply_filters( 'woocommerce_companies_address_to_edit', $fields )
		) );
		
	}
	
	public function add_address() {
		
		$user_id      	= get_current_user_id();
		
		if ( ! current_user_can( 'add_address' ) ) {
			
			wc_print_notice(__( 'Invalid address.', 'woocommerce-companies' ) . ' <a href="' . wc_get_page_permalink( 'myaccount' ).'" class="wc-forward">'. __( 'My Account', 'woocommerce' ) .'</a>', 'error');

		}
		
		$fields = WC_Companies()->addresses->get_address_fields();
		
		foreach($fields as $key => $field) {
			
			$fields[$load_address . '_' . $key] = $field;
			
			unset($fields[$key]);
			
		}
		
		wc_get_template( 'myaccount/form-edit-address.php', array(
			'load_address' 	=> $load_address,
			'address'		=> apply_filters( 'woocommerce_companies_address_to_edit', $fields )
		) );
		
	}
	
	public function remove_address( $address_id ) {
		
		$user_id = get_current_user_id();
		
		if ( ! current_user_can( 'remove_address', $address_id ) ) {
			
			wc_print_notice(__( 'Invalid address.', 'woocommerce-companies' ) . ' <a href="' . wc_get_page_permalink( 'myaccount' ).'" class="wc-forward">'. __( 'My Account', 'woocommerce' ) .'</a>', 'error');
			
		}
		
		else {
			
			$address = wc_get_address($address_id);
		
			if( $address->delete() ) {
				
				wc_add_notice( __( 'Address removed successfully.', 'woocommerce-companies' ) );
				
				do_action( 'woocommerce_companies_remove_address', $user_id, $address_id );
	
				wp_safe_redirect( wc_get_endpoint_url( 'addresses', '', wc_get_page_permalink('myaccount') ) );
				
				exit();
				
			}
			
		}
		
	}
	
	public function primary_address( $address_id ) {
		
		$address_type = ! empty( $_REQUEST['address_type'] ) ? $_REQUEST['address_type'] : false;
		
		$user_id = get_current_user_id();
		
		if ( ! current_user_can( 'make_primary_address', $address_id ) ) {
			
			wc_add_notice(__( 'Invalid address.', 'woocommerce-companies' ) . ' <a href="' . wc_get_page_permalink( 'myaccount' ).'" class="wc-forward">'. __( 'My Account', 'woocommerce' ) .'</a>', 'error');
			
		}
		else {
		
			if( update_user_meta( $user_id, 'primary_' . $address_type . '_address', $address_id) ) {
				
				wc_add_notice( __( 'Address saved as primary ' . $address_type . ' address successfully.', 'woocommerce-companies' ) );
				
				do_action( 'woocommerce_companies_make_primary_address', $user_id, $address_id, $address_type );
	
				
				
			}
			
		}
		
		wp_safe_redirect( wc_get_endpoint_url( 'addresses', '', wc_get_page_permalink('myaccount') ) );
				
		exit();
		
	}
	
	/**
	 * display title on edit address template
	 *
	 * @param array $title String of heading title
	 */
	public function my_account_edit_address_title($title) {
		
		$title = __('Edit Address', 'woocommerce-companies');
		
		return $title;
		
	}
	
	/**
	 * my addressses title on my companies area
	 *
	 * @param string $title String of heading title
	 */
	public function companies_view_addresses_title($title) {
		
		if( get_query_var('company_id') ) {
			
			$company = wc_get_company( get_query_var('company_id') );
			
			$title = __('Addresses for company: ', 'woocommerce-companies') . $company->get_title();
			
		}
		
		return $title;
		
	}
	
	public function my_account_my_orders_query($args) {
		
		unset( $args['meta_key'] );
		unset( $args['meta_value'] );
		
		$args['meta_query'] = array_merge(isset($args['meta_query']) && is_array($args['meta_query']) ? $args['meta_query'] : array(), array(
			array(
				'key' => '_company_id',
				'value' => wc_get_user_companies( get_current_user_id(), 'ids'),
				'compare' => 'IN'
			)
		));
		
		$args['meta_query']['relation'] = 'OR';
		$args['meta_query'][] = array(
			'key' => '_customer_user',
			'value' => get_current_user_id(),
		);
		
		return $args;
		
	}
	
	public function display_company_and_user($order_id) {
		
		$order = wc_get_order($order_id);
		
		if( $order->company_id && in_array($order->company_id, wc_get_user_companies($order->get_user_id())) ) {
			
			if ( $company = wc_get_company( $order->company_id ) ) {
				
				$customer = $order->get_user();
				
				echo wpautop("Order by {$customer->display_name} on behalf of <a href=\"{$company->get_view_company_url()}\">{$company->get_title()}</a>.");
				
			}
			
		}
		
	}
	
	/**
	 * retriveing edit comapny fields for front end
	 *
	 * @param string $comapny_id Int of company id
	 */
	public function companies_edit_company_fields($fields, $company_id) {
		
		$company = wc_get_company($company_id);
		
		$billing_addresses = array(0 => 'None');
		
		foreach($company->get_billing_addresses() as $address) {
			
			$billing_addresses[$address->id] = $address->get_title();
			
		}
		
		$shipping_addresses = array(0 => 'None');
		
		foreach($company->get_shipping_addresses() as $address) {
			
			$shipping_addresses[$address->id] = $address->get_title();
			
		}
		
		$user_addresses = array();
		
		foreach(wc_get_user_all_addresses( get_current_user_id() ) as $address) {
			
			$user_addresses[$address->id] = $address->get_title();
			
		}
		
		$fields['primary_billing_address']['options'] = $billing_addresses;
		
		$fields['primary_shipping_address']['options'] = $shipping_addresses;
		
		$fields['billing_addresses']['options'] = $fields['shipping_addresses']['options'] = $user_addresses;
		
		return $fields;
		
	}
	
	/**
	 * retriveing add comapny fields for front end
	 *
	 * @param string $comapny_id Int of company id
	 */
	public function companies_add_company_fields($fields) {
		
		$user_addresses = array();
		
		foreach(get_user_all_addresses( get_current_user_id() ) as $address) {
			
			$user_addresses[$address->id] = $address->get_title();
			
		}
		
		$fields['primary_billing_address']['options'] = array();
		
		$fields['primary_shipping_address']['options'] = array();
		
		$fields['billing_addresses']['options'] = $fields['shipping_addresses']['options'] = $user_addresses;
		
		return $fields;
		
	}
	
	/**
	 * retrieves address data from stored primary address
	 *
	 * @param string $value String of field value
	 * @param string $key String of field key
	 * @param string $load_address String of type of address
	 */
	public function my_account_edit_address_field_value($value, $key, $load_address) {
		
		$address = get_user_by('id', get_current_user_id())->{ 'primary_' . $load_address . '_address' };
		
		$key = ltrim(str_replace($load_address, '', $key), '_');
			
		return $address->$key ? $address->$key : $value;
		
	}
	
	/**
	 * retrieves address data from stored primary address
	 *
	 * @param array $address Array of address
	 * @param string $customer_id int of user id
	 * @param string $name String of type of address
	 */
	public function my_account_my_address_formatted_address($address, $customer_id, $load_address) {
		
		$primary_address = get_user_by('id', $customer_id)->{ 'primary_' . $load_address . '_address' };
		
		if($primary_address) {
			
			$primary_address = wc_get_address($primary_address);
			
			$address = $primary_address->get_meta_data();
			
		}
		
		return $address;
		
	}

}