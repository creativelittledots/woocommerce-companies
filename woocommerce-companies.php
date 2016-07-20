<?php
/*
* Plugin Name: WooCommerce Companies
* Description: Extends WooCommerce to have Companies and Addresses related to Users, also includes a payment gateway to utilise a companies credit limit
* Version: 1.0.0
* Author: Creative Little Dots
* Author URI: http://creativelittledots.co.uk
*
* Text Domain: woocommerce-companies
* Domain Path: /languages/
*
* Requires at least: 3.8
* Tested up to: 4.1.1
*
* Copyright: © 2009-2015 Creative Little Dots
* License: GNU General Public License v3.0
* License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( ! function_exists('is_woocommerce_active') ) {
	return;
}

// Check if WooCommerce is active
if ( ! is_woocommerce_active() ) {
	
	return;
	
}

class WC_Companies {
	
	/**
	 * @var string
	 */
	public $version 	= '1.0.0';
	
	/**
	 * @var WooCommerce The single instance of the class
	 * @since 2.1
	 */
	protected static $_instance = null;
	
	/**
	 * @var WC_Company_Factory $company_factory
	 */
	public $company_factory = null;
	
	/**
	 * @var WC_Address_Factory $address_factory
	 */
	public $address_factory = null;
	
	/**
	 * @var WC_Customer $customer
	 */
	public $customer = null;
	
	/**
	 * Main WooCommerce Instance
	 *
	 * Ensures only one instance of WooCommerce is loaded or can be loaded.
	 *
	 * @since 2.1
	 * @static
	 * @see WC()
	 * @return WooCommerce - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	
	/**
	 * Define WC Companies Constants
	 */
	private function define_constants() {

		$this->define( 'WC_COMPANIES_PLUGIN_FILE', __FILE__ );
		
	}
	
	/**
	 * Define constant if not already set
	 * @param  string $name
	 * @param  string|bool $value
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Cloning is forbidden.
	 * @since 2.1
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'woocommerce' ), '2.1' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 * @since 2.1
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'woocommerce' ), '2.1' );
	}
	
	public function __construct() {
		
		add_action( 'admin_init', array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
		
		add_action( 'init', array( $this, 'init' ), 2 );
		
		add_filter('woocommerce_payment_gateways', array($this, 'add_credit_limit_gateway'), 12 );
		
	}
	
	/**
	 * What type of request is this?
	 * string $type ajax, frontend or admin
	 * @return bool
	 */
	private function is_request( $type ) {
		switch ( $type ) {
			case 'admin' :
				return is_admin();
			case 'ajax' :
				return defined( 'DOING_AJAX' );
			case 'cron' :
				return defined( 'DOING_CRON' );
			case 'frontend' :
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}
	}
	
	public function activate() {
		
		global $wpdb;

		$version = get_option( 'woocommerce_companies', false );
		
		if ( $version == false ) {
			
			add_option( 'woocommerce_companies', $this->version );

			// Update from previous versions

			// delete old option
			delete_option( 'woocommerce_companies_active' );
				
		} elseif ( version_compare( $version, $this->version, '<' ) ) {

			update_option( 'woocommerce_companies', $this->version );
		}

	}
	
	/**
	 * Deactivate extension.
	 * @return void
	 */
	public function deactivate() {

		delete_option( 'woocommerce_companies' );
		
	}
	
	public function plugin_url() {
		
		return plugins_url( basename( plugin_dir_path(__FILE__) ), basename( __FILE__ ) );
		
	}

	public function plugin_path() {
		
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
		
	}
	
	public function includes() {
		
		include_once( 'includes/class-wc-companies-autoloader.php' );
		include_once( 'includes/class-wc-companies-install.php' );
		
		include( 'includes/wc-company-functions.php' );
		include( 'includes/wc-address-functions.php' );
		include( 'includes/wc-user-functions.php' );
		
		if ( $this->is_request( 'ajax' ) ) {
			$this->ajax_includes();
		}
		
		if ( $this->is_request( 'admin' ) ) {
			include_once( 'includes/admin/class-wc-companies-admin.php' );
		}
		
		if ( $this->is_request( 'frontend' ) ) {
			$this->frontend_includes();
		}
		
		$this->addresses = include( 'includes/class-wc-companies-addresses.php' );               		// Addresses class
		$this->query = include( 'includes/class-wc-companies-query.php' );        // The main query class
		$this->emails = include( 'includes/class-wc-companies-emails.php' );   // The email class

		include_once( 'includes/class-wc-companies-post-types.php' );   // Registers post types
		include_once( 'includes/abstracts/abstract-wc-company.php');	// Companies
		include_once( 'includes/abstracts/abstract-wc-address.php');	// Addresses
		include_once( 'includes/class-wc-company-factory.php' );                // Company factory
		include_once( 'includes/class-wc-address-factory.php' );                // Address factory
		
		
	}
	
	/**
	 * Include required ajax files.
	 */
	public function ajax_includes() {
		include_once( 'includes/class-wc-companies-ajax.php' );                           // Ajax functions for admin and the front-end
	}

	/**
	 * Include required frontend files.
	 */
	public function frontend_includes() {
		include_once( 'includes/class-wc-companies-frontend-scripts.php' );             // Frontend Scripts
		include_once( 'includes/class-wc-companies-customer.php' );                     // Customer class
		include_once( 'includes/class-wc-companies-form-handler.php' );                   // Form Handlers
		include_once( 'includes/class-wc-companies-display.php' );               		// Display class
		include_once( 'includes/class-wc-companies-my-account.php' );               		// My Account class
	}

	public function init() {
		
		$this->define_constants();
		$this->includes();
		
		// Set up localisation
		$this->load_plugin_textdomain();
		
		// Load class instances
		$this->company_factory = new WC_Company_Factory();                      // Company Factory to create new product instances
		$this->address_factory   = new WC_Address_Factory();                        // Address Factory to create new order instances
		
		if ( $this->is_request( 'frontend' ) ) {
			
			$this->display = new WC_Companies_Display();
			$this->customer = new WC_Companies_Customer();		
			$this->my_account = new WC_Companies_My_Account();
			
		}
		
		$this->checkout();
		
	}
	
	/**
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
	 *
	 * Admin Locales are found in:
	 * 		- WP_LANG_DIR/woocommerce-companies/woocommerce-companies-admin-LOCALE.mo
	 * 		- WP_LANG_DIR/plugins/woocommerce-companies-admin-LOCALE.mo
	 *
	 * Frontend/global Locales found in:
	 * 		- WP_LANG_DIR/woocommerce/woocommerce-companies-LOCALE.mo
	 * 	 	- woocommerce/i18n/languages/woocommerce-companies-LOCALE.mo (which if not found falls back to:)
	 * 	 	- WP_LANG_DIR/plugins/woocommerce-companies-LOCALE.mo
	 */
	public function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'woocommerce' );

		if ( $this->is_request( 'admin' ) ) {
			load_textdomain( 'woocommerce-companies', WP_LANG_DIR . '/woocommerce-companies/woocommerce-companies-admin-' . $locale . '.mo' );
			load_textdomain( 'woocommerce-companies', WP_LANG_DIR . '/plugins/woocommerce-companies-admin-' . $locale . '.mo' );
		}

		load_textdomain( 'woocommerce', WP_LANG_DIR . '/woocommerce-companies/woocommerce-companies-' . $locale . '.mo' );
		load_plugin_textdomain( 'woocommerce', false, plugin_basename( dirname( __FILE__ ) ) . "/i18n/languages" );
	}
	
	/**
	 * Get Checkout Class.
	 * @return WC_Companies_Checkout
	 */
	public function checkout() {
    	
		return WC_Companies_Checkout::instance();
		
	}

	/**
	 * Get gateways class
	 * @return WC_Companies_Payment_Gateways
	 */
	public function payment_gateways() {
    	
		return WC_Companies_Payment_Gateways::instance();
		
	}
	
	public function add_credit_limit_gateway( $methods ) {
		
		if( class_exists('WC_Gateway_Credit_Limit') ) {
    		
			$methods[] = 'WC_Gateway_Credit_Limit';
			
		}
		
		return $methods;
		
	}	
	
}

function WC_Companies() {
    
	return WC_Companies::instance();
	
}

$GLOBALS[ 'woocommerce_companies' ] = WC_Companies();