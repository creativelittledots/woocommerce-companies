<?php
/**
 * Abstract Company
 *
 * The WooCommerce company class handles company data.
 *
 * @class       WC_Company
 * @version     2.2.0
 * @package     WooCommerce/Classes
 * @category    Class
 * @author      WooThemes
 *
 * @property    string $title The company title
 * @property    string $company_name The company name
 * @property    string $company_number The company number
 * @property    string $internal_company_id The company internal id
 * @property    string $available_credit The company available credit limit
 * @property    string $billing_addresses The company billing addresses
  * @property    string $shipping_addresses The company shipping addresses
 */
abstract class WC_Abstract_Company {

	/** @public int Company (post) ID */
	public $id                          = 0;
	
	/** @public string Company (post) post_title */
	public $title                       = '';

	/** @var $post WP_Post */
	public $post                        = null;

	/** @public string Order Date */
	public $company_date                  = '';

	/** @public string Order Modified Date */
	public $modified_date               = '';

	/** @public string Order Status */
	public $post_status                 = '';

	/** @protected string Formatted address. Accessed via get_formatted_billing_address() */
	protected $formatted_billing_address  = '';
	
		/** @protected string Formatted address. Accessed via get_formatted_billing_address() */
	protected $formatted_shipping_addeess  = '';

	/**
	 * Get the company if ID is passed, otherwise the order is new and empty.
	 * This class should NOT be instantiated, but the get_order function or new WC_Order_Factory
	 * should be used. It is possible, but the aforementioned are preferred and are the only
	 * methods that will be maintained going forward.
	 *
	 * @param int $company
	 */
	public function __construct( $company = 0 ) {
		$this->init( $company );
	}

	/**
	 * Init/load the company object. Called from the constructor.
	 *
	 * @param  int|object|WC_Company $company Company to init
	 */
	protected function init( $company ) {
		if ( is_numeric( $company ) && $company ) {
			$this->id   = absint( $company );
			$this->post = get_post( $company );
			$this->get_company( $this->id );
		} elseif ( $company instanceof WC_Company ) {
			$this->id   = absint( $company->id );
			$this->post = $company->post;
			$this->get_company( $this->id );
		} elseif ( isset( $company->ID ) ) {
			$this->id   = absint( $company->ID );
			$this->post = $company;
			$this->get_company( $this->id );
		}
	}

	/**
	 * Gets an company from the database.
	 *
	 * @param int $id (default: 0)
	 * @return bool
	 */
	public function get_company( $id = 0 ) {

		if ( ! $id ) {
			return false;
		}

		if ( $result = get_post( $id ) ) {
			$this->populate( $result );
			return true;
		}

		return false;
	}

	/**
	 * Populates an order from the loaded post data.
	 *
	 * @param mixed $result
	 */
	public function populate( $result ) {

		// Standard post data
		$this->id                  	= $result->ID;
		$this->slug					= $result->post_name;
		$this->title				= $result->post_title;
		$this->post_status         	= $result->post_status;

	}

	/**
	 * __isset function.
	 *
	 * @param mixed $key
	 * @return bool
	 */
	public function __isset( $key ) {

		if ( ! $this->id ) {
			return false;
		}

		return metadata_exists( 'post', $this->id, '_' . $key );
	}
	
	/**
	 * __toString function.
	 *
	 * @return string
	 */
	public function __toString() {
		
        return (string) $this->id;
        
    }

	/**
	 * __get function.
	 *
	 * @param mixed $key
	 * @return mixed
	 */
	public function __get( $key ) {
		// Get values or default if not set
		if ( 'user_id' === $key ) {
			$value = ( $value = $this->get_user_id() ) ? absint( $value ) : '';
		} elseif ( 'status' === $key ) {
			$value = $this->get_status();
		} else {
			$value = get_post_meta( $this->id, '_' . $key, true );
		}

		return $value;
	}

	/**
	 * Return the company statuses without wc- internal prefix.
	 *
	 * Queries get_post_status() directly to avoid having out of date statuses, if updated elsewhere.
	 *
	 * @return string
	 */
	public function get_status() {
		$this->post_status = get_post_status( $this->id );
		return apply_filters( 'woocommerce_company_get_status', 'wc-' === substr( $this->post_status, 0, 3 ) ? substr( $this->post_status, 3 ) : $this->post_status, $this );
	}

	/**
	 * Checks the company status against a passed in status.
	 *
	 * @return bool
	 */
	public function has_status( $status ) {
		return apply_filters( 'woocommerce_company_has_status', ( is_array( $status ) && in_array( $this->get_status(), $status ) ) || $this->get_status() === $status ? true : false, $this, $status );
	}

	/**
	 * Gets the user ID associated with the company. Guests are 0.
	 *
	 * @since  1.0
	 * @return int
	 */
	public function get_user_id() {
		return $this->post->post_author ? intval( $this->post->post_author ) : 0;
	}

	/**
	 * Get the user associated with the company. False for guests.
	 *
	 * @since  1.0
	 * @return WP_User|false
	 */
	public function get_user() {
		return $this->get_user_id() ? get_user_by( 'id', $this->get_user_id() ) : false;
	}
	
	/**
	 * Get billing addresses, set wether object or id
	 *
	 * @since  1.0
	 */
	public function get_billing_addresses($output = 'objects') {
		
		switch($output) {		
			
			case 'ids' :
			
				return array_filter($this->billing_addresses, function($address) { return get_post($address); });
				
			break;
			
			default :
			
				return array_map(function($address) { return wc_get_address($address); }, array_filter($this->billing_addresses, function($address) { return get_post($address); }));
				
			break;
			
		}
		
		return false;
	}
	
	/**
	 * Get shipping addresses, set wether object or id
	 *
	 * @since  1.0
	 */
	public function get_shipping_addresses($output = 'objects') {
		
		switch($output) {		
			
			case 'ids' :
			
				return array_filter($this->shipping_addresses, function($address) { return get_post($address); });
				
			break;
			
			default :
			
				return array_map(function($address) { return wc_get_address($address); }, array_filter($this->shipping_addresses, function($address) { return get_post($address); }));
				
			break;
			
		}
		
		return false;
	}
	
	/**
	 * Get primary billing address, set wether object or id
	 *
	 * @since  1.0
	 */
	public function get_primary_billing_address($output = 'object') {
		
		if( $this->primary_billing_address && get_post( $this->primary_billing_address ) ) {
			
			switch($output) {		
			
				case 'id' :
				
					return $this->primary_billing_address;
					
				break;
				
				default :
				
					return wc_get_address($this->primary_billing_address);
					
				break;
				
			}
			
		}
		
		return false;
		
	}
	
	/**
	 * Check if company has free shipping
	 *
	 * @since  1.0
	 */
	public function has_free_shipping() {
		return apply_filters('woocommerce_companies_company_has_free_shipping', $this->free_shipping, $this);
	}
	
	/**
	 * Get primary shipping address, set wether object or id
	 *
	 * @since  1.0
	 */
	public function get_primary_shipping_address($output = 'object') {
		
		if( $this->primary_shipping_address && get_post( $this->primary_shipping_address ) ) {
			
			switch($output) {		
			
				case 'id' :
				
					return $this->primary_shipping_address;
					
				break;
				
				default :
				
					return wc_get_address($this->primary_shipping_address);
					
				break;
				
			}
			
		}
		
		return false;
		
	}

	/**
	 * Check if an order key is valid.
	 *
	 * @param mixed $key
	 * @return bool
	 */
	public function key_is_valid( $key ) {

		if ( $key == $this->company_key ) {
			return true;
		}

		return false;
	}

	/**
	 * Get a formatted billing address for the company.
	 *
	 * @return string
	 */
	public function get_formatted_billing_address() {
		
		if ( $address = $this->get_primary_billing_address()  ) {

			$this->formatted_billing_address = $address->get_formatted_address(); 
			
		}

		return $this->formatted_billing_address;
	}
	
	/**
	 * Get a formatted shipping company for the company.
	 *
	 * @return string
	 */
	public function get_formatted_shipping_address() {
		
		if ( $address = $this->get_primary_shipping_address()  ) {

			$this->formatted_shipping_address = $address->get_formatted_address(); 
		}

		return $this->formatted_shipping_address;
			
	}

	/**
	 * Updates status of company
	 *
	 * @param string $new_status Status to change the company to.
	 */
	public function update_status( $new_status ) {
		if ( ! $this->id ) {
			return;
		}

		// Standardise status names.
		$new_status = 'wc-' === substr( $new_status, 0, 3 ) ? substr( $new_status, 3 ) : $new_status;
		$old_status = $this->get_status();

		// Only update if they differ - and ensure post_status is a 'wc' status.
		if ( $new_status !== $old_status ) {

			// Update the order
			wp_update_post( array( 'ID' => $this->id, 'post_status' => $new_status ) );
			$this->post_status = $new_status;

			// Status was changed
			do_action( 'woocommerce_company_status_' . $new_status, $this->id );
			do_action( 'woocommerce_company_status_' . $old_status . '_to_' . $new_status, $this->id );
			do_action( 'woocommerce_company_status_changed', $this->id, $old_status, $new_status );

			wc_delete_company_transients( $this->id );
			
		}
	}
	
	/**
	 * Cancel the company
	 *
	 * @param string $note (default: '') Optional note to add
	 */
	public function delete_company() {
		$this->update_status( 'trash' );
	}
	
	/**
	* Get available credit limit in money format
	*
	* var boolean $curreny Weather or not to output using 'wc_price'
	**/
	public function get_available_credit($currency = true) {
		
		if(!$this->available_credit)
			return false;
		
		if(!$currency) 
			return $this->available_credit;
			
		return wc_price($this->available_credit);
		
	}
	
	/**
	* Reduce available credit limit by amount
	*
	* var int $amount Amount to reduce credit limit by
	**/
	public function reduce_available_credit($amount) {
		
		update_post_meta($this->id, '_available_credit', max(0, $this->available_credit-$amount));
		
	}
	
	/**
	* Get Company Title
	*
	**/
	public function get_title() {
		
		return apply_filters('wc_companies_company_get_title', $this->title, $this);
		
	}
	
	/**
	 * Generates a URL to view a company from the my account page
	 *
	 * @return string
	 */
	public function get_view_company_url() {

		$view_company_url = wc_get_endpoint_url( 'my-companies/edit', $this->id, wc_get_page_permalink( 'myaccount' ) );

		return apply_filters( 'woocommerce_get_view_company_url', $view_company_url, $this );
	}
	
	/**
	 * Retrive meta data for company
	 *
	 * @return string
	 */
	public function get_meta_data() {

		$meta = array();
		
		foreach(array_keys(WC_Companies()->addresses->get_company_fields()) as $key) {
			
			$key = preg_replace('/[^A-Za-z0-9_\-]/', '', $key);
			
			$meta[$key] = $this->$key;
			
		}
		
		return $meta;
		
	}
	
	/**
	 * Save current object as post
	 *
	 * @return int
	 */
	public function save() {
		
		if($exists = $this->check_exists()) {
			
			return $exists;
			
		}
		
		$data = array(
			'post_title' => $this->company_name, 
			'post_type' => 'wc-company', 
			'post_status' => 'publish',
			'post_author' => $this->get_user_id(),
		);
		
		if($this->id) {
			
			$data['ID'] = $this->id;
			
			$this->id = wp_update_post($data);
			
		} else {
			
			$this->id = wp_insert_post($data);
			
		}
		
		foreach($this->get_meta_data() as $key => $value) {
			
			$value = apply_filters('woocommerce_companies_company_meta_save_data', $value, $key, $this->id);
		
			update_post_meta($this->id, '_' . $key, is_string($value) ? stripslashes($value) : $value);
			
		}
		
		return $this->id;
		
	}
	
	
	/**
	 * Delete company
	 *
	 * @return boolean
	 */
	public function delete() {
		
		return wp_delete_post($this->id);
		
	}
	
	/**
	 * Check if a company exists already
	 *
	 * @return boolean
	 */
	public function check_exists() {
			
		$args = array(
			'slug' => $this->slug
		);
		
		$args['meta_query'] = array();
		
		foreach(WC_Companies()->addresses->get_company_fields() as $key => $field) {
			
			$key = preg_replace('/[^A-Za-z0-9_\-]/', '', $key);
			
			$args['meta_query'][$key] = array(
				'key' => $key,
				'value' => $this->$key,
			);
			
		}
		
		if($companies = self::find( $args )) {
			
			return reset($companies)->id;
			
		}
		
		return false;
		
	}
	
	/**
	 * find companies based on arguments
	 *
	 * var $args Array
	 * @return boolean
	 */
	public static function find( $args, $output = 'objects' ) {
			
		$args = array_merge(array(
			'post_type' => 'wc-company',
			'showposts' => -1,
		), $args);
		
		$companies = get_posts($args);
		
		foreach($companies as &$company) {
			
			switch($output) {
				
				case 'ids' :
				
					$company = $company->ID;
					
				break;
				
				default :
				
					$company = wc_get_company($company->ID);
					
				break;		
				
			}
			
		}
		
		return $companies;
		
	}
	
}
