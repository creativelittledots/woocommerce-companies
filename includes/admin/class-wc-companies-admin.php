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
    
        wp_enqueue_script( 'wc-companies-admin-general', WC_Companies()->plugin_url() . '/assets/js/admin/wc-admin-general.js', array('jquery'), '1.0.0', true );	
    	
	}
	

}

return new WC_Companies_Admin();
