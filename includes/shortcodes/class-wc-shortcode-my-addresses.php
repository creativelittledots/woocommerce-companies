<?php
/**
 * Addresses Shortcodes
 *
 * Shows the 'my account' section where the customer can view past orders and update their information.
 *
 * @author 		Creative Little Dots
 * @category 	Shortcodes
 * @package 	WooCommerce Companies/Shortcodes/My_Addresses
 * @version     1.0.0
 */
class WC_Shortcode_My_Addresses {

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

			$message = apply_filters( 'woocommerce_my_addresses_message', '' );

			if ( ! empty( $message ) ) {
				wc_add_notice( $message );
			}

			wc_get_template( 'myaccount/form-login.php' );

		} else {
			
			if ( ! empty( $wp->query_vars['address_action'] ) ) {
				
				switch($wp->query_vars['address_action']) {
					
					case 'edit' :
					
						global $current_user;
					
						if( isset ($wp->query_vars['address_id'] )  && isset( $wp->query_vars['address_type'] ) )
							self::edit_address( absint( $wp->query_vars['address_id'] ) , wc_edit_address_i18n( sanitize_title( $wp->query_vars['address_type'] ), true ));
							
						else if ( isset( $wp->query_vars['address_type'] ) && $current_user->{ 'primary_' . $wp->query_vars['address_type'] . '_address' } )
							self::edit_address( absint( $current_user->{ 'primary_' . $wp->query_vars['address_type'] . '_address' } ) , wc_edit_address_i18n( sanitize_title( $wp->query_vars['address_type'] ), true ));
							
						else
							self::my_addresses( $atts );
					
					break;
					
					case 'remove' :
					
						if( isset( $wp->query_vars['address_id'] ) )
							self::remove_address( absint( $wp->query_vars['address_id'] ) );
							
						else
							self::my_addresses( $atts );
							
					break;
					
					case 'add' :
					
						if( isset ( $wp->query_vars['address_type'] ) )
							self::add_address( wc_edit_address_i18n( sanitize_title( $wp->query_vars['address_type'] ), true ) );
						else
							self::my_addresses( $atts );
						
					break;
					
					case 'primary' :
					
						if( isset ($wp->query_vars['address_id'] ) && isset ( $wp->query_vars['address_type'] ) )
							self::make_primary( absint( $wp->query_vars['address_id'] ), wc_edit_address_i18n( sanitize_title( $wp->query_vars['address_type'] ), true ) );
						else
							self::my_addresses( $atts );
							
					break;
					
				}
				
			} else {
				
				self::my_addresses( $atts );
								
			}
			
		}
		
	}
	
	/**
	 * My addresses page
	 *
	 * @param  array $atts
	 */
	public function my_addresses( $atts ) {
		extract( shortcode_atts( array(
	    	'address_count' => 15
		), $atts ) );
		
		$addresses = get_user_all_addresses( get_current_user_id() );
		
		foreach($addresses as $key => $address) {
			
			if( in_array($address->id, get_user_primary_addresses( get_current_user_id(), 'ids' )) ) {
				
				unset($addresses[$key]);
				
			}
			
			$addresses = get_user_primary_addresses( get_current_user_id() ) + $addresses;
			
		}
				
		wc_get_template('myaccount/view-addresses.php', array(
			'object' 	=> get_user_by( 'id', get_current_user_id() ),
			'address_count' 	=> 'all' == $address_count ? -1 : $address_count,
			'addresses' => $addresses,
		), '', WC_Companies()->plugin_path() . '/templates/');
		
	}
	
	/**
	 * Edit address page
	 *
	 * @param  int $address_id
	 */
	public function edit_address( $address_id, $load_address = 'billing' ) {
		
		$user_id      	= get_current_user_id();
		$address		= wc_get_address( $address_id );
		
		if ( ! current_user_can( 'edit_address', $address_id ) ) {
			
			do_action( 'woocommerce_before_my_account' );
			
			wc_add_notice(__( 'Invalid address.', 'woocommerce-companies' ) . ' <a href="' . wc_get_page_permalink( 'myaccount' ).'" class="wc-forward">'. __( 'My Account', 'woocommerce' ) .'</a>', 'error');
			
			wc_print_notices();
			
			do_action( 'woocommerce_after_my_account' );
			
			return;
		}
		
		$fields = WC_Companies()->addresses->get_address_fields();
		
		foreach($fields as $key => $field) {
			
			$field['value'] = $address->$key;
			
			$fields[$load_address . '_' . $key] = $field;
			
			unset($fields[$key]);
			
		}
		
		do_action( 'woocommerce_before_my_account' );
		
		wc_get_template( 'myaccount/form-edit-address.php', array(
			'load_address' 	=> $load_address,
			'address'		=> apply_filters( 'woocommerce_companies_address_to_edit', $fields )
		) );
		
		do_action( 'woocommerce_after_my_account' );
		
	}
	
	/**
	 * Add address page
	 *
	 * @param  string $address_type
	 */
	public function add_address( $load_address = 'billing' ) {
		
		$user_id      	= get_current_user_id();
		
		if ( ! current_user_can( 'add_address' ) ) {
			wc_add_notice(__( 'Invalid address.', 'woocommerce-companies' ) . ' <a href="' . wc_get_page_permalink( 'myaccount' ).'" class="wc-forward">'. __( 'My Account', 'woocommerce' ) .'</a>', 'error');
			wc_print_notices();
			return;
		}
		
		$fields = WC_Companies()->addresses->get_address_fields();
		
		foreach($fields as $key => $field) {
			
			$fields[$load_address . '_' . $key] = $field;
			
			unset($fields[$key]);
			
		}
		
		do_action( 'woocommerce_before_my_account' );
		
		wc_get_template( 'myaccount/form-edit-address.php', array(
			'load_address' 	=> $load_address,
			'address'		=> apply_filters( 'woocommerce_companies_address_to_edit', $fields )
		) );
		
		do_action( 'woocommerce_after_my_account' );
		
	}
	
	/**
	 * Remove address
	 *
	 */
	public function remove_address( $address_id ) {
		
		$user_id = get_current_user_id();
		
		if ( ! current_user_can( 'remove_address', $address_id ) ) {
			
			do_action( 'woocommerce_before_my_account' );
			
			wc_add_notice(__( 'Invalid address.', 'woocommerce-companies' ) . ' <a href="' . wc_get_page_permalink( 'myaccount' ).'" class="wc-forward">'. __( 'My Account', 'woocommerce' ) .'</a>', 'error');
			
			wc_print_notices();
			
			do_action( 'woocommerce_after_my_account' );
			
		}
		else {
			
			$address = wc_get_address($address_id);
		
			if($address->delete()) {
				
				wc_add_notice( __( 'Address removed successfully.', 'woocommerce-companies' ) );
				
				do_action( 'woocommerce_companies_remove_address', $user_id, $address_id );
	
				wp_safe_redirect( wc_get_endpoint_url( 'my-addresses', '', wc_get_page_permalink('myaccount') ) );
				
				exit;
				
			}
			
		}
		
	}
	
	/**
	 * Make primary
	 *
	 */
	public function make_primary( $address_id, $load_address = 'billing' ) {
		
		$user_id = get_current_user_id();
		
		if ( ! current_user_can( 'make_primary_address', $address_id ) ) {
			
			do_action( 'woocommerce_before_my_account' );
			
			wc_add_notice(__( 'Invalid address.', 'woocommerce-companies' ) . ' <a href="' . wc_get_page_permalink( 'myaccount' ).'" class="wc-forward">'. __( 'My Account', 'woocommerce' ) .'</a>', 'error');
			
			wc_print_notices();
			
			do_action( 'woocommerce_after_my_account' );
			
		}
		else {
		
			if( update_user_meta( $user_id, 'primary_' . $load_address . '_address', $address_id) ) {
				
				wc_add_notice( __( 'Address saved as primary ' . $load_address . ' address successfully.', 'woocommerce-companies' ) );
				
				do_action( 'woocommerce_companies_make_primary_address', $user_id, $address_id, $load_address );
	
				wp_safe_redirect( wc_get_endpoint_url( 'my-addresses', '', wc_get_page_permalink('myaccount') ) );
				
				exit;
				
			}
			
		}
		
	} 
		
}
