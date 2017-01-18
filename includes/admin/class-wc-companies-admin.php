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
		add_action( 'woocommerce_form_field_advanced_search', array($this, 'advanced_search_field'), 10, 4 );
		add_action( 'admin_enqueue_scripts', array($this, 'enqueue_scripts') );
		add_action( 'restrict_manage_posts', array($this, 'display_merge_fields') );
		add_action( 'load-edit.php', array($this, 'merge_records') );
		add_action( 'admin_notices', array($this, 'merge_admin_notice') );
	}
	
	public function display_merge_fields( $post_type) {
		
		if( in_array( $post_type, array( 'wc-company', 'wc-address' ) ) ) {
			
			$fields = $post_type == 'wc-company' ? WC_Companies()->addresses->get_company_fields(true) : WC_Companies()->addresses->get_admin_address_fields(true);
			
			wc_get_template( 'admin/merge-fields.php', array(
				'fields' => $fields,
			), '', WC_Companies()->plugin_path() . '/templates/' );
			
		}
		
	}
	
	public function merge_admin_notice() {
 
		global $post_type, $pagenow;
		
		if( $pagenow == 'edit.php' && in_array( $post_type, array( 'wc-company', 'wc-address' ) ) && ! empty( $_REQUEST['merged'] ) ) {
			
			$message = sprintf( _n( 'Post merged.', '%s Posts merged.', $_REQUEST['merged'] ), number_format_i18n( $_REQUEST['merged'] ) );
			
			echo "<div class=\"updated\"><p>{$message}</p></div>";
			
		}
		
	}
	
	public function merge_records() {
		
		// 1. get the action
		$wp_list_table = _get_list_table('WP_Posts_List_Table');
		$action = $wp_list_table->current_action();
		
		switch($action) {
			
			// 3. Perform the action
			case 'merge':
			
				// 2. security check
				check_admin_referer('bulk-posts');
				
				
				// do the merge
				
				if( empty($_REQUEST['post']) || empty($_REQUEST['post_type']) || empty($_REQUEST['wc-companies-merge']) ) {
					
					break;
					
				}
				
				$ids = $_REQUEST['post'];
				$post_type = $_REQUEST['post_type'];
				$fields = $_REQUEST['wc-companies-merge'];
				
				$posts = get_posts( array( 
			    	'post_type' => $post_type,
					'post__in' => $ids,
					'order' => 'DESC',
					'orderby' => 'modified',
					'showposts' => -1
				) );
				
				$new_post = reset( $posts );
				
				foreach($posts as $i => $post) {
					
					if( $i > 0 ) {
						
						if( wp_delete_post( $post->ID, true) ) {
							
							global $wpdb;
						
							switch( $post_type ) {
								
								case 'wc-company' :
									
									// update User Primary Company
									$wpdb->update( $wpdb->usermeta, [
										'meta_value' => $new_post->ID
									], [
										'meta_key' => 'primary_company',
										'meta_value' => $post->ID
									]);
									
									// update User Companies
									$this->update_merge_serialized_meta($wpdb->usermeta, $post, $new_post, array( 'companies' ), 'umeta_id');
									
									// upate Order Company ID
									$wpdb->update( $wpdb->postmeta, [
										'meta_value' => $new_post->ID
									], [
										'meta_key' => '_company_id',
										'meta_value' => $post->ID
									]);
								
								break;
								
								case 'wc-address' :
								
									// update Company Primary Billing Address
									$wpdb->update( $wpdb->postmeta, [
										'meta_value' => $new_post->ID
									], [
										'meta_key' => '_primary_billing_address_id',
										'meta_value' => $post->ID
									]);
									
									// updae Company Primary Shipping Address
									$wpdb->update( $wpdb->postmeta, [
										'meta_value' => $new_post->ID
									], [
										'meta_key' => '_primary_shipping_address_id',
										'meta_value' => $post->ID
									]);
									
									// update Company Billing Addresses
									// update Company Shipping Addresses
									
									$meta_keys = array(
										'_billing_addresses',
										'_billing_addresses',
									);
									
									$this->update_merge_serialized_meta($wpdb->postmeta, $post, $new_post, $meta_keys);
									
									// update User Primary Billing Address
									$wpdb->update( $wpdb->usermeta, [
										'meta_value' => $new_post->ID
									], [
										'meta_key' => 'primary_billing_address_id',
										'meta_value' => $post->ID
									]);
									
									// updae User Primary Shipping Address
									$wpdb->update( $wpdb->usermeta, [
										'meta_value' => $new_post->ID
									], [
										'meta_key' => 'primary_shipping_address_id',
										'meta_value' => $post->ID
									]);
									
									// update User Billing Addresses
									// update User Shipping Addresses
									
									$meta_keys = array(
										'_billing_addresses',
										'_billing_addresses',
									);
																		
									$this->update_merge_serialized_meta($wpdb->usermeta, $post, $new_post, $meta_keys, 'umeta_id');
									
									// update Order Billing Address
									$wpdb->update( $wpdb->postmeta, [
										'meta_value' => $new_post->ID
									], [
										'meta_key' => '_billing_address_id',
										'meta_value' => $post->ID
									]);
									
									// updae Order Shipping Address
									$wpdb->update( $wpdb->postmeta, [
										'meta_value' => $new_post->ID
									], [
										'meta_key' => '_shipping_address_id',
										'meta_value' => $post->ID
									]);
								
								break;
								
							}
							
						}
						
					} else {
						
						foreach($fields as $key => $value) {
							
							switch($key) {
								
								case 'billing_addresses' :
								case 'shipping_addresses' :
								
									$value = array_map('trim', explode(',', $value));
									
								break;
								
								case 'company_name' :
								case 'address_1' :
								
									wp_update_post( array(
										'ID' => $post->ID,
										'post_title' => $value
									) );
								
								break;
								
							}
							
							update_post_meta( $post->ID, '_' . $key, $value );
							
						}
						
					}
					
				}
				
				// build the redirect url
				$sendback = add_query_arg( array( 'merged' => count($ids) ), wp_get_referer() );
				
				// ...
		
				// 4. Redirect client
				wp_redirect($sendback);
				
				exit();
			
			break;
			
		}
			
	}
	
	private function update_merge_serialized_meta($table, WP_Post $post, WP_Post $new_post, Array $meta_keys = array(), $primary_key = 'meta_id') {
		
		global $wpdb;
		
		$meta_keys = implode( ', ', $meta_keys );
		
		$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE meta_key IN ( %s ) AND meta_value LIKE '%:\"%s\"%'", $meta_keys, $post->ID), OBJECT );
									
		foreach($results as $result) {
			
			$meta_value = maybe_unserialize( $result->meta_value );
			
			if( is_array( $meta_value ) ) {
				
				$index = array_search( $post->ID, $meta_value );
				
				if( $index > -1 ) {
					
					$meta_value = array_replace($meta_value, array(
						$index => $new_post->ID
					));
				
					$wpdb->update( $table, [
						'meta_value' => serialize( $meta_value )
					], [
						$primary_key => $result->$primary_key
					]);
					
				}
				
			}
			
		}
		
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
		
		include_once( 'class-wc-companies-admin-meta-boxes.php' );
		
		include_once( 'class-wc-companies-admin-order-fields.php');
		
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
	
	/**
	 * Advanced Search Field
	 */
	public function advanced_search_field($field, $key, $args, $value) {
    	
    	// Custom attribute handling
        $custom_attributes = array();

        if ( ! empty( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
            foreach ( $args['custom_attributes'] as $attribute => $attribute_value ) {
                $custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
            }
        }        
        
        $label_id = $args['id'];
        $field_container = '<p class="form-row %1$s" id="%2$s">%3$s</p>';
        
        if ( $args['label'] && 'checkbox' != $args['type'] ) {
            $field .= '<label for="' . esc_attr( $label_id ) . '" class="' . esc_attr( implode( ' ', $args['label_class'] ) ) .'">' . $args['label'] . '</label>';
        }
        
        $field .= '<input type="hidden" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) .'" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" ' . $args['maxlength'] . ' value="' . esc_attr( $value ) . '" ' . implode( ' ', $custom_attributes ) . ' />';

        if ( $args['description'] ) {
            $field .= '<span class="description">' . esc_html( $args['description'] ) . '</span>';
        }

        $container_class = 'form-row ' . esc_attr( implode( ' ', $args['class'] ) );
        $container_id = esc_attr( $args['id'] ) . '_field';

        $after = ! empty( $args['clear'] ) ? '<div class="clear"></div>' : '';

        return sprintf( $field_container, $container_class, $container_id, $field ) . $after;
    	
	}
	
	public function enqueue_scripts() {
    
    	$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
    
        wp_enqueue_script( 'wc-companies-admin-general', WC_Companies()->plugin_url() . '/assets/js/admin/wc-admin-general' . $suffix . '.js', array('jquery'), '1.0.0', true );	
        
        $screen = get_current_screen();
        
        if( is_post_type_archive( 'wc-company' ) || is_post_type_archive( 'wc-address' ) ) {
	        
	        wp_enqueue_style( 'wc-companies-admin-merge', WC_Companies()->plugin_url() . '/assets/css/admin/wc-admin-merge' . $suffix . '.css' );	
	        
	        wp_enqueue_script( 'wc-companies-admin-merge', WC_Companies()->plugin_url() . '/assets/js/admin/wc-admin-merge' . $suffix . '.js', array('jquery'), '1.0.0', true );	
	        
        }
    	
	}
	

}

return new WC_Companies_Admin();
