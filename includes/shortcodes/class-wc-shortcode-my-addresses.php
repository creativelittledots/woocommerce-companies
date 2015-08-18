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

			if ( ! empty( $wp->query_vars['view-address'] ) ) {

				self::view_address( absint( $wp->query_vars['view-address'] ) );

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
		
		wc_get_template('myaccount/my-addresses.php', array(
			'current_user' 	=> get_user_by( 'id', get_current_user_id() ),
			'address_count' => 'all' == $address_count ? -1 : $address_count
		), '', WC_Companies()->plugin_path() . '/templates/');
		
	}
	
	/**
	 * View address page
	 *
	 * @param  int $company_id
	 */
	public function view_address( $address_id ) {
		
		$user_id      	= get_current_user_id();
		$address		= wc_get_address( $company_id );
		
		if ( ! current_user_can( 'view_address', $address_id ) ) {
			echo '<div class="woocommerce-error">' . __( 'Invalid address.', 'woocommerce-companies' ) . ' <a href="' . wc_get_page_permalink( 'myaccount' ).'" class="wc-forward">'. __( 'My Account', 'woocommerce' ) .'</a>' . '</div>';
			return;
		}
		
		wc_get_template('myaccount/edit-address.php', array(
			'address'   	=> $address,
	        'address_id'  	=> $address_id
		), '', WC_Companies()->plugin_path() . '/templates/');
		
	}
		
}
