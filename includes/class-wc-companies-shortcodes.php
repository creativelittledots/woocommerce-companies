<?php
/**
 * WC_Companies_Shortcodes class.
 *
 * @class 		WC_Companies_Shortcodes
 * @version		1.0.0
 * @package		WooCommerce Companies/Classes
 * @category	Class
 * @author 		Creative Little Dots
 */
class WC_Companies_Shortcodes extends WC_Shortcodes {

	/**
	 * Init shortcodes
	 */
	public static function init() {
		// Define shortcodes
		$shortcodes = array(
			'woocommerce_my_companies'     => __CLASS__ . '::my_companies',
			'woocommerce_edit_company'     => __CLASS__ . '::edit_company',
			'woocommerce_my_addresses'     => __CLASS__ . '::my_addresses',
		);

		foreach ( $shortcodes as $shortcode => $function ) {
			add_shortcode( apply_filters( "{$shortcode}_shortcode_tag", $shortcode ), $function );
		}
	}
	
	/**
	 * My Companies shortcode.
	 *
	 * @return string
	 */
	public static function my_companies() {
		return self::shortcode_wrapper( array( 'WC_Shortcode_My_Companies', 'output' ) );
	}
	
	/**
	 * Edit Company shortcode.
	 *
	 * @return string
	 */
	public static function edit_company() {
		return self::shortcode_wrapper( array( 'WC_Shortcode_My_Companies', 'output' ) );
	}
	
	/**
	 * My Addresses shortcode.
	 *
	 * @return string
	 */
	public static function my_addreses() {
		return self::shortcode_wrapper( array( 'WC_Shortcode_My_Adresses', 'output' ) );
	}
	
}
