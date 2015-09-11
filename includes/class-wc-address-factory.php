<?php
/**
 * Address Factory Class
 *
 * The WooCommerce address factory creating the right address objects
 *
 * @class 		WC_Address_Factory
 * @version		2.2.0
 * @package		WooCommerce/Classes
 * @category	Class
 * @author 		WooThemes
 */
class WC_Address_Factory {

	/**
	 * get_address function.
	 *
	 * @param bool $the_address (default: false)
	 * @return WC_Address|bool
	 */
	public function get_address( $the_address = false ) {
		global $post;

		if ( false === $the_address ) {
			$the_address = $post;
		} elseif ( is_numeric( $the_address ) ) {
			$the_address = get_post( $the_address );
		} elseif ( $the_address instanceof WC_Address ) {
			$the_address = get_post( $the_address->id );
		}

		if ( ! $the_address || ! is_object( $the_address ) ) {
			return false;
		}

		$address_id  = absint( $the_address->ID );
		$post_type = $the_address->post_type;

		if ( $address_type = wc_get_address_type( $post_type ) ) {
			$classname = $address_type['class_name'];
		} else {
			$classname = false;
		}

		// Filter classname so that the class can be overridden if extended.
		$classname = apply_filters( 'woocommerce_address_class', $classname, $post_type, $address_id, $the_address );

		if ( ! class_exists( $classname ) ) {
			return false;
		}

		return new $classname( $the_address );
	}
}
