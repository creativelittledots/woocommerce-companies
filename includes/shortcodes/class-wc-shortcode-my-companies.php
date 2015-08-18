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

			if ( ! empty( $wp->query_vars['edit-company'] ) ) {

				self::edit_company( absint( $wp->query_vars['edit-company'] ) );

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
		
		wc_get_template('myaccount/view-companies.php', array(
			'current_user' 	=> get_user_by( 'id', get_current_user_id() ),
			'company_count' 	=> 'all' == $order_count ? -1 : $company_count,
			'companies' => get_user_companies(),
		), '', WC_Companies()->plugin_path() . '/templates/');
		
	}
	
	/**
	 * View company page
	 *
	 * @param  int $company_id
	 */
	public function edit_company( $company_id ) {
		
		$user_id      	= get_current_user_id();
		$company 		= new WC_Company( $company_id );
		
		if ( ! current_user_can( 'view_company', $company_id ) ) {
			echo '<div class="woocommerce-error">' . __( 'Invalid company.', 'woocommerce-companies' ) . ' <a href="' . wc_get_page_permalink( 'myaccount' ).'" class="wc-forward">'. __( 'My Account', 'woocommerce' ) .'</a>' . '</div>';
			return;
		}
		
		wc_get_template('myaccount/edit-company.php', array(
			'current_user' 	=> get_user_by( 'id', get_current_user_id() ),
			'company'   => $company,
		), '', WC_Companies()->plugin_path() . '/templates/');
		
	}
		
}
