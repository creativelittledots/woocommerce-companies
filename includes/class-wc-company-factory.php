<?php
/**
 * Company Factory Class
 *
 * The WooCommerce company factory creating the right company objects
 *
 * @class 		WC_Company_Factory
 * @version		2.2.0
 * @package		WooCommerce/Classes
 * @category	Class
 * @author 		WooThemes
 */
class WC_Company_Factory {

	/**
	 * get_company function.
	 *
	 * @param bool $the_company (default: false)
	 * @return WC_Company|bool
	 */
	public function get_company( $the_company = false ) {
		global $post;

		if ( false === $the_company ) {
			$the_company = $post;
		} elseif ( is_numeric( $the_company ) ) {
			$the_company = get_post( $the_company );
		} elseif ( $the_company instanceof WC_Company ) {
			$the_company = get_post( $the_company->id );
		}

		if ( ! $the_company || ! is_object( $the_company ) ) {
			return false;
		}

		$company_id  = absint( $the_company->ID );
		$post_type = $the_company->post_type;

		if ( $company_type = wc_get_company_type( $post_type ) ) {
			$classname = $company_type['class_name'];
		} else {
			$classname = false;
		}

		// Filter classname so that the class can be overridden if extended.
		$classname = apply_filters( 'woocommerce_company_class', $classname, $post_type, $company_id, $the_company );

		if ( ! class_exists( $classname ) ) {
			return false;
		}

		return new $classname( $the_company );
	}
}
