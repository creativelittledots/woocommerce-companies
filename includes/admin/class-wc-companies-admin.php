<?php
/**
 * WooCommerce Companies Admin.
 *
 * @class       WC_Companies_Admin
 * @author      WooThemes
 * @category    Admin
 * @package     WooCommerce Companies/Admin
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WC_Companies_Admin class.
 */
class WC_Companies_Admin extends WC_Admin {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'includes' ) );
		add_action( 'current_screen', array( $this, 'conditonal_includes' ) );
	}

	/**
	 * Include any classes we need within admin.
	 */
	public function includes() {
		
		// Classes we only need during non-ajax requests
		if ( ! is_ajax() ) {
			include_once( 'class-wc-companies-admin-assets.php' );
		}
		
		// Functions
		include_once( 'wc-companies-meta-box-functions.php' );

		// Classes
		include_once( 'class-wc-companies-admin-post-types.php' );
		
	}

	/**
	 * Include admin files conditionally
	 */
	public function conditonal_includes() {

		$screen = get_current_screen();

		switch ( $screen->id ) {
			case 'users' :
			case 'user' :
			case 'profile' :
			case 'user-edit' :
				include( 'class-wc-companies-admin-profile.php' );
			break;
		}
	}

}

return new WC_Companies_Admin();
