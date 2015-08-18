<?php
/**
 * Installation related functions and actions.
 *
 * @author 		Creative Little Dots
 * @category 	Admin
 * @package 	WooCommerce Companies/Classes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Companies_Install Class
 */
class WC_Companies_Install extends WC_Install {

	/**
	 * Hook in tabs.
	 */
	public static function init() {
		add_filter( 'woocommerce_create_pages', array(__CLASS__, 'install_pages') );
	}

	/**
	 * Create pages that the plugin relies on, storing page id's in variables.
	 */
	public static function install_pages($pages) {

		$pages['myaddresses'] = array(
			'name' 		=> _x( 'my-addresses', 'Page slug', 'woocommerce-companies'),
			'title' 	=> _x( 'My Addresses', 'Page title', 'woocommeerce-companies'),
			'parent' 	=> 'myaccount',
			'content' 	=> '[woocommerce_my_addresses]',
		);
		
		$pages['mycompanies'] = array(
			'name' 		=> _x( 'my-companies', 'Page slug', 'woocommerce-companies'),
			'title' 	=> _x( 'My Companies', 'Page title', 'woocommeerce-companies'),
			'parent' 	=> 'myaccount',
			'content' 	=> '[woocommerce_my_companies]',
		);

		return $pages;
		
	}
	
}

WC_Companies_Install::init();
