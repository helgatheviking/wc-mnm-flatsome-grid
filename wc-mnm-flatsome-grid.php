<?php
/*
* Plugin Name: WooCommerce Mix and Match: Grid Layout for Flatsome
* Plugin URI: https://woocommerce.com/products/woocommerce-mix-and-match-products/
* Description: Use Flatsome markup for grid layout.
* Version: 1.0.0-beta-1
* Author: Kathy Darling
* Author URI: http://kathyisawesome.com/
*
* Text Domain: wc-mnm-flatsome-grid
* Domain Path: /languages/
*
* Requires at least: 4.9
* Tested up to: 4.9
*
* WC requires at least: 3.3
* WC tested up to: 3.4
*
* Copyright: © 2018 Kathy Darling
* License: GNU General Public License v3.0
* License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WC_MNM_Flatsome_Grid {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	public static $version = '1.0.0-beta-1';

	/**
	 * Min required PB version.
	 *
	 * @var string
	 */
	public static $req_mnm_version = '1.3.0';

	/**
	 * HFire in the hole!
	 */
	public static function init() {

		// Check dependencies.
		if ( ! function_exists( 'WC_Mix_and_Match' ) || version_compare( WC_Mix_and_Match()->version, self::$req_mnm_version ) < 0 ) {
			add_action( 'admin_notices', array( __CLASS__, 'version_notice' ) );
			return false;
		}

		/*
		 * Templates.
		 */
		add_filter( 'woocommerce_locate_template', array( __CLASS__, 'use_local_templates' ), 10, 2 );

		/*
		 * Products.
		 */
		add_action( 'woocommerce_before_mnm_items', array( __CLASS__, 'modify_product_display' ) );

	}

	/*
	|--------------------------------------------------------------------------
	| Environment failure notice.
	|--------------------------------------------------------------------------
	*/

	/**
	 * PB version check notice.
	 */
	public static function version_notice() {
		echo '<div class="error"><p>' . sprintf( __( '<strong>WooCommerce Mix and Match Products &ndash; Flatsome Grid</strong> requires Mix and Match Products <strong>%s</strong> or higher.', 'wc-mnm-flatsome-grid' ), self::$req_mnm_version ) . '</p></div>';
	}

	/*
	|--------------------------------------------------------------------------
	| Templates.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Applies discount on bundled cart items based on overall cart quantity.
	 *
	 * @param  string  $template
	 * @param  string  $template_name
	 * @param  string  $template_path
	 * @return string
	 */
	public static function use_local_templates( $template, $template_name ) { 
		if( strpos( $template_name, 'single-product/mnm' ) !== false ) {
			$path = trailingslashit( plugin_dir_path( __FILE__ ) ) . 'templates/' . $template_name;
			$template = file_exists( $path ) ? $path : $template;
		}
		return $template;
	}

	/*
	|--------------------------------------------------------------------------
	| Products.
	|--------------------------------------------------------------------------
	*/


	
	/**
	 * Modify the single product output.
	 *
	 * @param obj WC_Mix_and_Match $product the parent container
	 */
	public static function modify_product_display( $product ) {

		// Swap the thumbnail div with some special classes.
		remove_action( 'woocommerce_mnm_child_item_details', 'wc_mnm_template_child_item_thumbnail_open', 10, 2 );
		add_action( 'woocommerce_mnm_child_item_details', array( __CLASS__, 'flatsome_thumbnail_open' ), 10, 2 );
		
		// Swap this description div with some special classes.
		remove_action( 'woocommerce_mnm_child_item_details', 'wc_mnm_template_child_item_details_open', 40, 2 );
		add_action( 'woocommerce_mnm_child_item_details', array( __CLASS__, 'flatsome_text_open' ), 40, 2 );
		
		// Add per-item prices.
		add_action( 'woocommerce_mnm_child_item_details', array( __CLASS__, 'add_individual_prices' ), 65, 2 );

	}


	/**
	 * Open the thumbnail sub-section.
	 *
	 * @param obj WC_Product $mnm_item the child product
	 * @param obj WC_Mix_and_Match $product the parent container
	 */
	public static function flatsome_thumbnail_open( $mnm_item, $product ) {
		wc_get_template(
			'single-product/mnm/'. $product->get_layout() . '/mnm-child-item-detail-wrapper-open.php',
			array(
				'classes' => 'product-thumbnail box-image',
			),
			'',
			WC_Mix_and_Match()->plugin_path() . '/templates/'
		);
	}

	/**
	 * Add a 'details' sub-section.
	 *
	 * @param obj WC_Product $mnm_item the child product
	 * @param obj WC_Mix_and_Match $product the parent container
	 */
	public static function flatsome_text_open( $mnm_item, $product ) {
		$classes = function_exists( 'flatsome_product_box_text_class' ) ? flatsome_product_box_text_class(): '';
		
		wc_get_template(
			'single-product/mnm/'. $product->get_layout() . '/mnm-child-item-detail-wrapper-open.php',
			array(
				'classes' => 'box-text product-details ' . $classes,
			),
			'',
			WC_Mix_and_Match()->plugin_path() . '/templates/'
		);
	}

	/**
	 * Display individual MNM option prices
	 * 
	 * @param obj $mnm_item WC_Product of child item
	 * @param obj WC_Mix_and_Match $product the parent container
	 */
	public static function add_individual_prices( $mnm_item, $parent_product ) {
		if( $parent_product->is_priced_per_product() ) {
			echo '<div class="price-wrapper">';
				echo '<span class="price">' . $mnm_item->get_price_html() . '</span>';
			echo '</div>';
		}
	}

}

add_action( 'woocommerce_init', array( 'WC_MNM_Flatsome_Grid', 'init' ) );
