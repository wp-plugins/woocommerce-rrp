<?php
/*
Plugin Name: WooCommerce RRP
Plugin URI: http://bradley-davis.com/wordpress-plugins/woocommerce-rrp/
Description: WooCommerce RRP allows users to add text before the regular price and sale price of a product from within WooCommerce General settings.
Version: 1.0
Author: Bradley Davis
Author URI: http://bradley-davis.com
License: GPL3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Text Domain: woocommerce-rrp

@author		 Bradley Davis
@category  Admin
@package	 WooCommerce RRP
@since		 1.0

WooCommerce RRP. A Plugin that works with the WooCommerce plugin for WordPress.
Copyright (C) 2014 Bradley Davis - bd@bradley-davis.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see http://www.gnu.org/licenses/gpl-3.0.html.
*/

if ( ! defined( 'ABSPATH' ) ) :
	exit; // Exit if accessed directly
endif;

/**
 * Check if WooCommerce is active
 * @since 1.0
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) :
		/**
		 * Localisation
		 * @since 1.0
		 */
		load_plugin_textdomain( 'woocommerce-rrp', false,  trailingslashit( dirname( plugin_basename( __FILE__ ) ) ) . 'langs/' );

		class Woo_RRP {
			/**
			 * The Constructor!
			 * @since 1.0
			 */
			public function __construct() {
				if ( ! is_admin() ) :
					add_filter( 'woocommerce_get_price_html', array( &$this, 'woo_rrp_price_html' ), 100, 2 );
				endif;
				add_filter( 'woocommerce_general_settings', array( &$this, 'woo_rrp_input' ), 100, 1 );
			}

			/**
			 * Create and add input fields to the WooCommerce UI
			 * @since 1.0
			 */
			public function woo_rrp_input( $settings ) {

				$woo_rrp_update = array();

			  foreach ( $settings as $section ) :
			    if ( isset( $section['id'] ) && 'pricing_options' == $section['id'] && isset( $section['type'] ) && 'sectionend' == $section['type'] ) :    	
			      $woo_rrp_update[] = array(
			        'name'     => __( 'Product Price Text', 'woocommerce-rrp' ), // WC < 2.0
			        'title'    => __( 'Product Price Text', 'woocommerce-rrp' ), // WC >= 2.0
			        'desc_tip' => __( 'This is the text that will appear before the regular price of the product.', 'woocommerce-rrp' ),
			        'id'       => 'woo_rrp_before_price',
			        'type'     => 'text',
			        'css'      => 'min-width:200px;',
			        'std'      => '',  // WC < 2.0
			        'default'  => '',  // WC >= 2.0
			        'desc'     => __( 'For example, "RRP:" or "MRRP:"', 'woocommerce-rrp' ),
			      );
				    $woo_rrp_update[] = array(
			    		'name'     => __( 'Sale Price Text', 'woocommerce-rrp' ), // WC < 2.0
			        'title'    => __( 'Sale Price Text', 'woocommerce-rrp' ), // WC >= 2.0
			        'desc_tip' => __( 'This is the text that will appear before the sale price of the product.', 'woocommerce-rrp' ),
			        'id'       => 'woo_rrp_before_sale_price',
			        'type'     => 'text',
			        'css'      => 'min-width:200px;',
			        'std'      => '',  // WC < 2.0
			        'default'  => '',  // WC >= 2.0
			        'desc'     => __( 'For example, "Sale Price:" or "Our Price:"', 'woocommerce-rrp' ),
				    );
			    	$woo_rrp_update[] = array(
			        'name'     => __( 'Show Text On Archives', 'woocommerce-rrp' ), // WC < 2.0
			        'title'    => __( 'Show Text On Archives', 'woocommerce-rrp' ), // WC >= 2.0
			        'desc'     => __( 'Enable Archive Template Display', 'woocommerce-rrp'),
			        'desc_tip' => __( 'Tick to display on archive templates, eg, product archive, product tag etc. Please be aware you may need to do some archive re-styling to keep everything looking nice and tidy.', 'woocommerce-rrp' ),
			        'id'       => 'woo_rrp_archive_option',
			        'type'     => 'checkbox',
			        'css'      => 'min-width:200px;',
			        'std'      => '',  // WC < 2.0
			        'default'  => '',  // WC >= 2.0
				    );
			   	endif;
			    $woo_rrp_update[] = $section;
			  endforeach;
		  	return $woo_rrp_update;
			}

			/**
			 * Output the field values to the product price on the front end
			 * @since 1.0
			 */
			public function woo_rrp_price_html( $price, $product ) {
				// Let's get the data we entered in the WC UI
				$woo_rrp_before_price = apply_filters( 'woo_rrp_before_price', get_option( 'woo_rrp_before_price', 1 ) ) . '&nbsp;';
				$woo_rrp_before_sale_price = apply_filters( 'woo_rrp_before_sale_price', get_option( 'woo_rrp_before_sale_price', 1 ) ) . '&nbsp;';
				$woo_rrp_archive_option = get_option( 'woo_rrp_archive_option', 1 );

				// Enable archive template display selected
				if ( 'yes' === $woo_rrp_archive_option ) :
					// Product is on sale
					if ( $product->is_on_sale() ) :
						$woo_rrp_replace = array(
							'<del>' => '<del>' . $woo_rrp_before_price,
							'<ins>' => '<br>' . $woo_rrp_before_sale_price . '<ins>'
						);
						$string_return = str_replace(array_keys( $woo_rrp_replace ), array_values( $woo_rrp_replace ), $price);
					// Product is not on sale
					else :
						$string_return = $woo_rrp_before_price . $price;
					endif;
				// Single product display only
				else :
					// Is single product and is on sale
					if ( is_product() && $product->is_on_sale() ) :
						$woo_rrp_replace = array(
							'<del>' => '<del>' . $woo_rrp_before_price,
							'<ins>' => '<br>' . $woo_rrp_before_sale_price . '<ins>'
						);
						$string_return = str_replace(array_keys( $woo_rrp_replace ), array_values( $woo_rrp_replace ), $price);
					// Single product
					elseif ( is_product() ) :
						$string_return = $woo_rrp_before_price . $price;
					// Return price without additional text on all other instances
					else :
						$string_return = $price;
					endif;

				endif;
				return $string_return;
			}
		} // END class Woo_RRP

		$woo_rrp = new Woo_RRP();

endif;