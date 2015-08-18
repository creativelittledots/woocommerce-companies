<?php
/**
 * WooCommerce Meta Boxes
 *
 * Sets up the write panels used by products and orders (custom post types)
 *
 * @author      WooThemes
 * @category    Admin
 * @package     WooCommerce/Admin/Meta Boxes
 * @version     2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WC_Admin_Meta_Boxes
 */
class WC_Companies_Admin_Meta_Boxes extends WC_Admin_Meta_Boxes {
	
	private static $saved_meta_boxes = false;
	public static $meta_box_errors  = array();
	
	/**
	 * Constructor
	 */
	public function __construct() {
		
		// Save Post Action
		add_action( 'save_post', array( $this, 'save_meta_boxes' ), 1, 2 );
		
		// Add our metaboxes
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 30 );

		// Save Company Meta Boxes
		add_action( 'woocommerce_process_wc-company_meta', 'WC_Meta_Box_Company_Data::save', 10, 2 );

		// Save Product Meta Boxes
		add_action( 'woocommerce_process_wc-address_meta', 'WC_Meta_Box_Address_Data::save', 10, 2 );
	}
	
	/**
	 * Add WC Meta boxes
	 */
	public function add_meta_boxes() {
		
		// Companies
		add_meta_box( 'woocommerce-company-data', __( 'Company Details', 'woocommerce-companies' ), 'WC_Meta_Box_Company_Data::output', 'wc-company', 'normal' );
		
		// Addresses
		add_meta_box( 'woocommerce-address-data', __( 'Address Details', 'woocommerce-companies' ), 'WC_Meta_Box_Address_Data::output', 'wc-address', 'normal' );

	}
	
	/**
	 * Check if we're saving, the trigger an action based on the post type
	 *
	 * @param  int $post_id
	 * @param  object $post
	 */
	public function save_meta_boxes( $post_id, $post ) {
		// $post_id and $post are required
		if ( empty( $post_id ) || empty( $post ) || self::$saved_meta_boxes ) {
			return;
		}

		// Dont' save meta boxes for revisions or autosaves
		if ( defined( 'DOING_AUTOSAVE' ) || is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
			return;
		}

		// Check the nonce
		if ( empty( $_POST['woocommerce_meta_nonce'] ) || ! wp_verify_nonce( $_POST['woocommerce_meta_nonce'], 'woocommerce_save_data' ) ) {
			return;
		}

		// Check the post being saved == the $post_id to prevent triggering this call for other save_post events
		if ( empty( $_POST['post_ID'] ) || $_POST['post_ID'] != $post_id ) {
			return;
		}

		// Check user has permission to edit
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// We need this save event to run once to avoid potential endless loops. This would have been perfect:
		//	remove_action( current_filter(), __METHOD__ );
		// But cannot be used due to https://github.com/woothemes/woocommerce/issues/6485
		// When that is patched in core we cna use the above. For now:
		self::$saved_meta_boxes = true;

		// Check the post type
		if ( in_array( $post->post_type, array( 'wc-company', 'wc-address' ) ) ) {
			do_action( 'woocommerce_process_' . $post->post_type . '_meta', $post_id, $post );
		}
	}
	
}

new WC_Companies_Admin_Meta_Boxes();