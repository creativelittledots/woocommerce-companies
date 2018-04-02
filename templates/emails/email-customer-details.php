<?php
/**
 * Additional Customer Details
 *
 * This is extra customer data which can be filtered by plugins. It outputs below the order item table.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/email-addresses.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you (the theme developer).
 * will need to copy the new files to your theme to maintain compatibility. We try to do this.
 * as little as possible, but it does happen. When this occurs the version of the template file will.
 * be bumped and the readme will list any important changes.
 *
 * @see 	    http://docs.woothemes.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates/Emails
 * @version     2.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<table id="details" cellspacing="0" cellpadding="0" style="width: 100%; vertical-align: top;text-align: left;" border="0">
	<tr>
		<td class="td" style="text-align:left; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; border: 0;" valign="top" width="50%">
			<h3><?php _e( 'Customer details', 'woocommerce' ); ?></h3>
			<p>
				<?php foreach ( $customer_fields as $field ) : ?>
			        <strong><?php echo wp_kses_post( $field['label'] ); ?>:</strong> <span class="text"><?php echo wp_kses_post( $field['value'] ); ?></span><br>
			    <?php endforeach; ?>
			</p>
		</td>
		<?php if ( $company_fields ) : ?>
			<td class="td" style="text-align:left; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; border: 0;" valign="top" width="50%">
				<h3><?php _e( 'Company details', 'woocommerce' ); ?></h3>
				<p>
					<?php foreach ( $company_fields as $field ) : ?>
				        <strong><?php echo wp_kses_post( $field['label'] ); ?>:</strong> <span class="text"><?php echo wp_kses_post( $field['value'] ); ?></span><br>
				    <?php endforeach; ?>
				</p>
			</td>
		<?php endif; ?>
	</tr>
</table>
