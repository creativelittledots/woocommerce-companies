<?php
/**
 * Load assets.
 *
 * @author     	Creative Little Dots
 * @category    Admin
 * @package     WooCommerce Companies/Admin
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_Companies_Admin_Assets' ) ) :

/**
 * WC_Admin_Assets Class
 */
class WC_Companies_Admin_Assets {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_filter( 'woocommerce_screen_ids', array($this, 'add_screen_ids') );
	}

	/**
	 * Enqueue styles
	 */
	public function admin_styles() {
		
		$screen = get_current_screen();
	
		switch ( $screen->id ) {
			case 'users' :
			case 'user' :
			case 'profile' :
			case 'user-edit' :
			
			wp_enqueue_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_Companies()->version );
		
			break;
		}

	}

	/**
	 * Enqueue scripts
	 */
	public function admin_scripts() {
		
		$screen = get_current_screen();
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	
		switch ( $screen->id ) {
			case 'users' :
			case 'user' :
			case 'profile' :
			case 'user-edit' :
			case 'wc-company' :
			case 'wc-address' :
			
			wp_register_script( 'chosen', WC()->plugin_url() . '/assets/js/chosen/chosen.jquery' . $suffix . '.js', array('jquery'), WC_Companies()->version );
			wp_enqueue_script( 'chosen' );
			wp_register_script( 'ajax-chosen', WC()->plugin_url() . '/assets/js/chosen/ajax-chosen.jquery' . $suffix . '.js', array('jquery', 'chosen'), WC_Companies()->version );
			wp_enqueue_script( 'ajax-chosen' );
			
				switch ( $screen->id ) {
			
					case 'users' :
					case 'user' :
					case 'profile' :
					case 'user-edit' :
					
					global $user_id;
					
					$user = get_user_by('id', $user_id);
					
					wp_register_script( 'wc-companies-profile', WC_Companies()->plugin_url() . '/assets/js/admin/wc-companies-profile' . $suffix . '.js', array('jquery'), WC_Companies()->version );
					
					wp_localize_script( 'wc-companies-profile', 'wc_companies_user', array(
						'billing' => $user->billing_addresses ? $user->billing_addresses : array(),
						'shipping' => $user->shipping_addresses ? $user->shipping_addresses : array(),
						'companies' => $user->companies ? $user->companies : array(),
					));
					
					wp_enqueue_script( 'wc-companies-profile' );
				
					break;
					
				}
				
			break;
			
		}	
			
	}
	
	/**
	 * Add screen ids
	 */
	public function add_screen_ids($screen_ids) {
		
		$screen_ids[] = 'wc-company';
		
		$screen_ids[] = 'wc-address';
			
		return $screen_ids;
		
	}

}

endif;

return new WC_Companies_Admin_Assets();
