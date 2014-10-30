<?php
/**
 * Delete RRP data if uninstalled.
 *
 * @since 1.0
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) :
	exit;
endif;
delete_option( 'woo_rrp_before_price' );
delete_option( 'woo_rrp_before_sale_price' );
delete_option( 'woo_rrp_archive_option' );