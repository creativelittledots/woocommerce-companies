<?php
/**
 * Companies Shortcodes
 *
 * Shows the 'my account' section where the customer can view past orders and update their information.
 *
 * @author 		Creative Little Dots
 * @category 	Shortcodes
 * @package 	WooCommerce Companies/Shortcodes/My_Companies
 * @version     1.0.0
 */
class WC_Shortcode_My_Companies {

	/**
	 * Get the shortcode content.
	 *
	 * @access public
	 * @param array $atts
	 * @return string
	 */
	public static function get( $atts ) {
		return WC_Shortcodes::shortcode_wrapper( array( __CLASS__, 'output' ), $atts );
	}

	/**
	 * Output the shortcode.
	 *
	 * @access public
	 * @param array $atts
	 * @return void
	 */
	public static function output( $atts ) {
		
		global $wp;

		if ( ! is_user_logged_in() ) {

			$message = apply_filters( 'woocommerce_my_companies_message', '' );

			if ( ! empty( $message ) ) {
				wc_add_notice( $message );
			}

			wc_get_template( 'myaccount/form-login.php' );

		} else {

			if ( ! empty( $wp->query_vars['company_action'] ) ) {
				
				switch($wp->query_vars['company_action']) {
					
					case 'edit' :
					
						if($wp->query_vars['company_id'])
							self::edit_company( absint( $wp->query_vars['company_id'] ) );
							
						else
							self::my_companies( $atts );
					
					break;
					
					case 'remove' :
					
						if($wp->query_vars['company_id'])
							self::remove_company( absint( $wp->query_vars['company_id'] ) );
							
						else
							self::my_companies( $atts );
							
					break;
					
					case 'add' :
					
						self::add_company();
						
					break;
					
					case 'primary' : 
					
						if( $wp->query_vars['company_id'] )
							self::make_primary( absint( $wp->query_vars['company_id'] ) );
							
						else
							self::my_companies( $atts );
							
					break;
					
					case 'addresses' : 
					
						if( $wp->query_vars['company_id'] )
							self::view_addresses( absint( $wp->query_vars['company_id'] ) );
							
						else
							self::my_companies( $atts );
							
					break;
					
					case 'remove-address' : 
					
						if( $wp->query_vars['company_id'] && $wp->query_vars['address_id'] )
							self::remove_address( absint( $wp->query_vars['company_id'] ), absint( $wp->query_vars['address_id'] ) );
							
						else
							self::my_companies( $atts );
							
					break;
					
					case 'primary-address' : 
					
						if( $wp->query_vars['company_id'] && $wp->query_vars['address_id'] && $wp->query_vars['address_type'] )
							self::make_primary_address( absint( $wp->query_vars['company_id'] ), absint( $wp->query_vars['address_id'] ), wc_edit_address_i18n( sanitize_title( $wp->query_vars['address_type'] ), true ) );
							
						else
							self::my_companies( $atts );
							
					break;
					
				}

			} else {
				
				self::my_companies( $atts );
				
			}
			
		}
		
	}
	
	/**
	 * My companies page
	 *
	 * @param  array $atts
	 */
	public function my_companies( $atts ) {
		extract( shortcode_atts( array(
	    	'company_count' => 15
		), $atts ) );
		
		global $current_user;
		
		$companies = wc_get_user_companies();
		
		if($current_user->primary_company) {
			
			if( $found_company = array_search($current_user->primary_company, $companies) ) {
				
				unset($companies[$found_company]);
				
			}
			
			$companies = array_unshift($companies, wc_get_company($current_user->primary_company));
			
		}
		
		wc_get_template('myaccount/view-companies.php', array(
			'current_user' 	=> get_user_by( 'id', get_current_user_id() ),
			'company_count' 	=> 'all' == $company_count ? -1 : $company_count,
			'companies' => wc_get_user_companies(),
		), '', WC_Companies()->plugin_path() . '/templates/');
		
	}
	
	/**
	 * View company page
	 *
	 * @param  int $company_id
	 */
	public function edit_company( $company_id ) {
		
		$user_id      	= get_current_user_id();
		$company 		= wc_get_company( $company_id );
		
		do_action( 'woocommerce_before_my_account' );
		
		if ( ! current_user_can( 'edit_company', $company_id ) ||  empty($company->post) ) {
			wc_add_notice(__( 'Invalid company.', 'woocommerce-companies' ) . ' <a href="' . wc_get_page_permalink( 'myaccount' ).'" class="wc-forward">'. __( 'My Account', 'woocommerce' ) .'</a>', 'error');
			wc_print_notices();
			return;
		}
		
		wc_get_template('myaccount/edit-company.php', array(
			'current_user' 	=> get_user_by( 'id', get_current_user_id() ),
			'company'   => $company,
			'fields'		=> apply_filters('woocommerce_companies_edit_company_fields', WC_Companies()->addresses->get_company_fields(), $company_id),
		), '', WC_Companies()->plugin_path() . '/templates/');
		
		do_action( 'woocommerce_after_my_account' );
		
	}
	
	/**
	 * Add company page
	 *
	 */
	public function add_company( ) {
		
		$user_id      	= get_current_user_id();
		
		do_action( 'woocommerce_before_my_account' );
		
		if ( ! current_user_can( 'add_company' ) ) {
			wc_add_notice(__( 'You do not have the privelages to add companies.', 'woocommerce-companies' ) . ' <a href="' . wc_get_page_permalink( 'myaccount' ).'" class="wc-forward">'. __( 'My Account', 'woocommerce' ) .'</a>', 'error');
			wc_print_notices();
			return;
		}
		
		wc_get_template('myaccount/edit-company.php', array(
			'current_user' 	=> get_user_by( 'id', get_current_user_id() ),
			'company'   => false,
			'fields'		=> apply_filters('woocommerce_companies_add_company_fields', WC_Companies()->addresses->get_company_fields()),
		), '', WC_Companies()->plugin_path() . '/templates/');
		
		do_action( 'woocommerce_after_my_account' );
		
	}
	
	/**
	 * Remove company
	 *
	 */
	public function remove_company( $company_id ) {
		
		$user_id = get_current_user_id();
		
		if ( ! current_user_can( 'remove_company', $company_id ) ) {
			
			do_action( 'woocommerce_before_my_account' );
			
			wc_add_notice(__( 'You do not have the privelages to remove this company.', 'woocommerce-companies' ) . ' <a href="' . wc_get_page_permalink( 'myaccount' ).'" class="wc-forward">'. __( 'My Account', 'woocommerce' ) .'</a>', 'error');
			
			wc_print_notices();
			
			do_action( 'woocommerce_after_my_account' );
			
		}
		else {
			
			$company = wc_get_company($company_id);
		
			if($company->delete()) {
				
				wc_add_notice( __( 'Company removed successfully.', 'woocommerce-companies' ) );
				
				do_action( 'woocommerce_companies_remove_company', $user_id, $company_id );
	
				wp_safe_redirect( wc_get_endpoint_url( 'my-companies', '', wc_get_page_permalink('myaccount') ) );
				
				exit;
				
			}
			
		}
		
	}
	
	/**
	 * Make primary
	 *
	 */
	public function make_primary( $company_id  ) {
		
		$user_id = get_current_user_id();
		
		if ( ! current_user_can( 'make_primary_company', $company_id ) ) {
			
			do_action( 'woocommerce_before_my_account' );
			
			wc_add_notice(__( 'Invalid company.', 'woocommerce-companies' ) . ' <a href="' . wc_get_page_permalink( 'myaccount' ).'" class="wc-forward">'. __( 'My Account', 'woocommerce' ) .'</a>', 'error');
			
			wc_print_notices();
			
			do_action( 'woocommerce_after_my_account' );
			
		}
		else {
		
			if( update_user_meta( $user_id, 'primary_company', $company_id) ) {
				
				wc_add_notice( __( 'Company saved as primary company successfully.', 'woocommerce-companies' ) );
				
				do_action( 'woocommerce_companies_make_primary_company', $user_id, $company_id );
	
				wp_safe_redirect( wc_get_endpoint_url( 'my-companies', '', wc_get_page_permalink('myaccount') ) );
				
				exit;
				
			}
			
		}
		
	}
	
	/**
	 * View company addresses page
	 *
	 * @param  array $atts
	 */
	public function view_addresses( $company_id, $address_count = 'all' ) {
		
		$company = wc_get_company($company_id);
		
		$primary_addresses = array();
		
		if ( $company->get_primary_billing_address() ) {
			
			$primary_addresses['billing'] = $company->get_primary_billing_address();
			
		}
		
		if ( $company->get_primary_shipping_address() ) {
			
			$primary_addresses['shipping'] = $company->get_primary_shipping_address();
			
		}
		
		$addresses = $company->get_billing_addresses() + $company->get_shipping_addresses();
		
		foreach($addresses as $key => $address) {
			
			if( in_array($address->id, array_map(function($address){return $address->id;}, $primary_addresses) ) ) {
				
				unset($addresses[$key]);
				
			}
			
		}
				
		wc_get_template('myaccount/view-addresses.php', array(
			'object' 	=>  $company,
			'address_count' 	=> 'all' == $address_count ? -1 : $address_count,
			'primary_addresses' => $primary_addresses,
			'addresses' => $addresses,
		), '', WC_Companies()->plugin_path() . '/templates/');
		
	}
	
	/**
	 * Make primary address
	 *
	 */
	public function make_primary_address( $company_id, $address_id, $load_address = 'billing'  ) {
		
		$user_id = get_current_user_id();
		
		if ( ! current_user_can( 'make_primary_company_address', $company_id, $address_id ) ) {
			
			do_action( 'woocommerce_before_my_account' );
			
			wc_add_notice(__( 'Invalid company or address.', 'woocommerce-companies' ) . ' <a href="' . wc_get_page_permalink( 'myaccount' ).'" class="wc-forward">'. __( 'My Account', 'woocommerce' ) .'</a>', 'error');
			
			wc_print_notices();
			
			do_action( 'woocommerce_after_my_account' );
			
		}
		else {
		
			if( update_post_meta( $company_id, 'primary_' . $load_address . 'address', $address_id) ) {
				
				wc_add_notice( __( 'Address saved as primary company ' . $load_address . ' address successfully.', 'woocommerce-companies' ) );
				
				do_action( 'woocommerce_companies_make_primary_company_address', $user_id, $company_id, $address_id, $load_address );
	
				wp_safe_redirect( wc_get_endpoint_url( 'my-companies/addresses', $company_id, wc_get_page_permalink( 'myaccount' ) ) );
				
				exit;
				
			}
			
		}
		
	}
	
	/**
	 * Remove address
	 *
	 */
	public function remove_address( $address_id ) {
		
		$user_id = get_current_user_id();
		
		if ( ! current_user_can( 'remove_company_address', $company_id, $address_id ) ) {
			
			do_action( 'woocommerce_before_my_account' );
			
			wc_add_notice(__( 'Invalid company or address.', 'woocommerce-companies' ) . ' <a href="' . wc_get_page_permalink( 'myaccount' ).'" class="wc-forward">'. __( 'My Account', 'woocommerce' ) .'</a>', 'error');
			
			wc_print_notices();
			
			do_action( 'woocommerce_after_my_account' );
			
		}
		else {
			
			$address = wc_get_address($address_id);
		
			if($address->delete()) {
				
				wc_add_notice( __( 'Address removed successfully.', 'woocommerce-companies' ) );
				
				do_action( 'woocommerce_companies_remove_address', $user_id, $address_id );
	
				wp_safe_redirect( wc_get_endpoint_url( 'my-companies/addresses', $company_id, wc_get_page_permalink( 'myaccount' ) ) );
				
				exit;
				
			}
			
		}
		
	}
		
}
