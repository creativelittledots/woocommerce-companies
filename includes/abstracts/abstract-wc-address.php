<?php
/**
 * Abstract Address
 *
 * The WooCommerce address class handles order data.
 *
 * @class       WC_Address
 * @version     2.2.0
 * @package     WooCommerce/Classes
 * @category    Class
 * @author      WooThemes
 *
 * @property    string $id The address id
 * @property    string $title The address title
 * @property    string $slug The address slug
 * @property    string $first_name The address first name
 * @property    string $last_name The address last name
 * @property    string $company The company name
 * @property    string $accounting_reference The acounting reference
 * @property    string $address_1 The address line 1
 * @property    string $address_2 The address line 2
 * @property    string $city The baddress city
 * @property    string $state The address state
 * @property    string $postcode The address postcode
 * @property    string $country The address country
 * @property    string $email The address email
 * @property    string $phone The address phone
 * @property    object $post The address post object
 * @property    string $post_status The address post status
 * @property    string $formatted_addeess The formatted addresses
 */
abstract class WC_Abstract_Address {

	/** @public int Address (post) ID */
	public $id = 0;
	
	/** @public string Address (post) post_title */
	public $title = '';
	
	/** @public string Address (post) post_name */
	public $slug = '';
	
	/** @public string First Name */
	public $first_name = '';
	
	/** @public string Last Name */
	public $last_name = '';
	
	/** @public string Company */
	public $company = '';
	
	/** @public string Accounting Reference */
	public $accounting_reference = '';
	
	/** @public string Address 1 */
	public $address_1 = '';
	
	/** @public string Address 2 */
	public $address_2 = '';
	
	/** @public string City */
	public $city = '';
	
	/** @public string Post Code */
	public $postcode = '';
	
	/** @public string State */
	public $state = '';
	
	/** @public string Country */
	public $country = '';
	
	/** @public string Email Address */
	public $email = '';
		
	/** @public string Phone */
	public $phone = '';

	/** @var $post WP_Post */
	public $post = null;

	/** @public string Order Status */
	public $post_status = '';

	/** @protected string Formatted address. Accessed via get_formatted_address() */
	protected $formatted_address  = '';

	/**
	 * Get the address if ID is passed, otherwise the order is new and empty.
	 * This class should NOT be instantiated, but the get_order function or new WC_Order_Factory
	 * should be used. It is possible, but the aforementioned are preferred and are the only
	 * methods that will be maintained going forward.
	 *
	 * @param int $address
	 */
	public function __construct( $address = 0 ) {
		$this->init( $address );
	}

	/**
	 * Init/load the address object. Called from the constructor.
	 *
	 * @param  int|object|WC_Address $address Address to init
	 */
	protected function init( $address ) {
		if ( is_numeric( $address ) && $address ) {
			$this->id   = absint( $address );
			$this->post = get_post( $address );
			$this->get_address( $this->id );
		} elseif ( $address instanceof WC_Address ) {
			$this->id   = absint( $address->id );
			$this->post = $address->post;
			$this->get_address( $this->id );
		} elseif ( isset( $address->ID ) ) {
			$this->id   = absint( $address->ID );
			$this->post = $address;
			$this->get_address( $this->id );
		}
	}

	/**
	 * Gets an address from the database.
	 *
	 * @param int $id (default: 0)
	 * @return bool
	 */
	public function get_address( $id = 0 ) {

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
		$this->post_status         	= $result->post_status;
		
		$this->company           	= $result->_company;
		$this->accounting_reference = $result->_accounting_reference;
		
		$this->first_name           = $result->_first_name;
		$this->last_name            = $result->_last_name;
		$this->address_1            = $result->_address_1;
		$this->address_2            = $result->_address_2;
		$this->city                 = $result->_city;
		$this->state                = $result->_state;
		$this->postcode             = $result->_postcode;
		$this->country              = $result->_country;
		$this->email                = $result->_email;
		$this->phone                = $result->_phone;
		
		$this->title				= $this->get_title();
		
		$this->get_formatted_address();

		// Email can default to user if set
		if ( empty( $this->email ) && ! empty( $this->post_author ) && ( $user = get_user_by( 'id', $this->post_author) ) ) {
			$this->email = $user->user_email;
		}

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
		
        return (string) $this->title;
        
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
			$value = ( $value = $this->post_author ) ? absint( $value ) : '';
		} elseif ( 'status' === $key ) {
			$value = $this->get_status();
		} else {
			$value = get_post_meta( $this->id, '_' . $key, true );
		}

		return $value;
	}

	/**
	 * Return the address statuses without wc- internal prefix.
	 *
	 * Queries get_post_status() directly to avoid having out of date statuses, if updated elsewhere.
	 *
	 * @return string
	 */
	public function get_status() {
		$this->post_status = get_post_status( $this->id );
		return apply_filters( 'woocommerce_address_get_status', 'wc-' === substr( $this->post_status, 0, 3 ) ? substr( $this->post_status, 3 ) : $this->post_status, $this );
	}

	/**
	 * Checks the address status against a passed in status.
	 *
	 * @return bool
	 */
	public function has_status( $status ) {
		return apply_filters( 'woocommerce_address_has_status', ( is_array( $status ) && in_array( $this->get_status(), $status ) ) || $this->get_status() === $status ? true : false, $this, $status );
	}

	/**
	 * Gets the user ID associated with the address. Guests are 0.
	 *
	 * @since  1.0
	 * @return int
	 */
	public function get_user_id() {
		return $this->post->post_author ? intval( $this->post->post_author ) : 0;
	}

	/**
	 * Get the user associated with the address. False for guests.
	 *
	 * @since  1.0
	 * @return WP_User|false
	 */
	public function get_user() {
		return $this->get_user_id() ? get_user_by( 'id', $this->get_user_id() ) : false;
	}

	/**
	 * Check if an order key is valid.
	 *
	 * @param mixed $key
	 * @return bool
	 */
	public function key_is_valid( $key ) {

		if ( $key == $this->address_key ) {
			return true;
		}

		return false;
	}
	
	/**
	 * Get a formatted address for the address.
	 *
	 * @return string
	 */
	public function get_array() {

		return apply_filters( 'woocommerce_company_formatted_address', array(
			'first_name'    => $this->first_name,
			'last_name'     => $this->last_name,
			'company'     	=> $this->company,
			'address_1'     => $this->address_1,
			'address_2'     => $this->address_2,
			'city'          => $this->city,
			'state'         => $this->state,
			'postcode'      => $this->postcode,
			'country'       => $this->country
		), $this );
	}

	/**
	 * Get a formatted address for the address.
	 *
	 * @return string
	 */
	public function get_formatted_address() {
		
		if ( ! $this->formatted_address ) {

			// Formatted Addresses
			$address = $this->get_array();

			$this->formatted_address = WC()->countries->get_formatted_address( $address );
			
		}

		return $this->formatted_address;
	}

	/**
	 * Updates status of address
	 *
	 * @param string $new_status Status to change the address to.
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
			do_action( 'woocommerce_address_status_' . $new_status, $this->id );
			do_action( 'woocommerce_address_status_' . $old_status . '_to_' . $new_status, $this->id );
			do_action( 'woocommerce_address_status_changed', $this->id, $old_status, $new_status );

			wc_delete_address_transients( $this->id );
			
		}
	}
	
	/**
	 * Cancel the address
	 *
	 * @param string $note (default: '') Optional note to add
	 */
	public function delete_address() {
		$this->update_status( 'trash' );
	}
	
	/**
	* Get Address Title
	*
	**/
	public function get_title() {
		
		$elements = array_filter(array(
			$this->address_1,
			$this->postcode
		));
		
		return apply_filters('wc_companies_address_get_title', implode(', ', $elements), $this);
		
	}
	
	/**
	 * Generates a URL to edit an address from the my account page
	 *
	 * @return string
	 */
	public function get_edit_address_url() {

		$edit_address_url = wc_get_endpoint_url( 'view-address', $this->id, wc_get_page_permalink( 'myaccount' ) );

		return apply_filters( 'woocommerce_get_view_address_url', $edit_address_url, $this );
	}
	
	/**
	 * Generates a URL to make an address primary for a user or a company
	 *
	 * @return string
	 */
	public function get_make_primary_address_url($address_type, $object) {
		
		if( $object instanceof WC_Company ) {
			
			$make_primary_url = add_query_arg( array(
				'address_id' => $this->id,
				'address_type' => $address_type,
			), wc_get_endpoint_url( 'company-primary-address', $object->id, wc_get_page_permalink( 'myaccount' ) ) );
			
		} else {
		
			$make_primary_url = add_query_arg( array(
				'address_type' => $address_type
			), wc_get_endpoint_url( 'primary-address', $this->id, wc_get_page_permalink( 'myaccount' ) ) );
			
		}

		return apply_filters( 'woocommerce_get_make_primary_address_url', $make_primary_url, $this );
	}
	
	
	/**
	 * Generates a URL to remove an address
	 *
	 * @return string
	 */
	public function get_remove_address_url($object) {
		
		if( $object instanceof WC_Company ) {
			
			$remove_address_url = add_query_arg( array(
				'address_id' => $this->id,
			), wc_get_endpoint_url( 'company-remove-address', $object->id, wc_get_page_permalink( 'myaccount' ) ) );
			
		} else {
		
			$remove_address_url = wc_get_endpoint_url( 'remove-address', $this->id, wc_get_page_permalink( 'myaccount' ) );
			
		}		

		return apply_filters( 'woocommerce_get_remove_address_url', $remove_address_url, $this );
	}
	
	/**
	 * Retrive meta data for address	 
	 *
	 * @return string
	 */
	public function get_meta_data() {

		$meta = array();
		
		foreach(array_keys(WC_Companies()->addresses->get_address_fields()) as $key) {
			
			$key = preg_replace('/[^A-Za-z0-9_\-]/', '', $key);
			
			$meta[$key] = $this->$key;
			
		}
		
		return $meta;
		
	}
	
	/**
	 * Update current object as post
	 *
	 * @return int
	 */
	public function update() {
		
		return $this->save();
		
	}
	
	/**
	 * Save current object as post
	 *
	 * @return int
	 */
	public function save() {
    	
    	if( empty( $this->address_1 ) ) {
    		
    		return new WP_Error( 'broke', __( "Address 1 cannot be empty", 'woocommerce-companies' ) );
    		
		}
		
		if( empty( $this->postcode ) ) {
    		
    		return new WP_Error( 'broke', __( "Postcode cannot be empty", 'woocommerce-companies' ) );
    		
		}
		
		$data = array_merge($this->get_meta_data(), array(
			'post_title' => $this->address_1 . ($this->postcode ? ', ' . $this->postcode : ''), 
			'post_type' => 'wc-address', 
			'post_status' => 'publish'
		));
		
		if($this->id) {
			
			$data['ID'] = $this->id;
			
			$this->id = wp_update_post($data, true);
			
		} else {
			
			$this->id = wp_insert_post($data, true);
			
		}
		
		foreach($this->get_meta_data() as $key => $value) {
			
			$key = preg_replace('/[^A-Za-z0-9_\-]/', '', $key);
		
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
	 * Check if an address exists already
	 *
	 * @return boolean
	 */
	public function check_exists() {
		
		$args['meta_query'] = array();
		
		foreach(WC_Companies()->addresses->get_address_fields() as $key => $field) {
    		
    		if( isset($field['required']) && $field['required'] ) {
        		
        		$key = preg_replace('/[^A-Za-z0-9_\-]/', '', $key);
			
    			$args['meta_query'][$key] = array(
    				'key' => '_' . $key,
    				'value' => $this->$key,
    			);
        		
    		}
			
		}
		
		if($addresses = self::find( $args )) {
			
			return reset($addresses)->id;
			
		}
		
		return false;
		
	}
	
	/**
	 * find addresses based on arguments
	 *
	 * var $args Array
	 * @return boolean
	 */
	public static function find( $args, $output = 'objects' ) {
			
		$args = array_merge(array(
			'post_type' => 'wc-address',
			'showposts' => -1,
			'orderby' => 'title',
			'order' => 'ASC'
		), $args);
		
		$addresses = get_posts($args);
		
		foreach($addresses as &$address) {
			
			switch($output) {
				
				case 'ids' :
				
					$address = $address->ID;
					
				break;
				
				default :
				
					$address = wc_get_address($address->ID);
					
				break;	
				
			}
			
		}
		
		return $addresses;
		
	}
	
}
