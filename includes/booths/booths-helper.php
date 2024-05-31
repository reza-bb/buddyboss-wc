<?php
/**
 * Booths helper class.
 *
 * @package BuddyBoss_WC\Includes\Booths
 */

namespace BuddyBoss_WC\Includes\Booths;

/**
 * Class Booths_Helper
 */
class Booths_Helper {

	/**
	 * Class instance.
	 *
	 * @var $instance
	 */
	private static $instance;

	/**
	 * Class constructor
	 */
	public function __construct() {}

	/**
	 * Get the instance of the class.
	 *
	 * @return Helpers
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			$class          = __CLASS__;
			self::$instance = new $class();
			self::$instance->load();
		}

		return self::$instance;
	}


	/**
	 * BuddyBossApp Product(for InAppPurchase) by id
	 *
	 * @param int $id Product id.
	 *
	 * @return array {object}
	 */
	public static function get_product_by_id( $id ) {

		global $wpdb;

		$global_prefix = \bbapp_iap()->get_global_dbprefix();
		$result        = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$global_prefix}bbapp_iap_products WHERE id = %d AND status = 'published'", $id ), OBJECT ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		$product = array();

		if ( ! empty( $result ) ) {

			$store_data          = maybe_unserialize( $result->store_data );
			$misc_settings       = self::bbapp_iap_product_mics_setting( $result );
			$integration_data    = self::bbapp_iap_product_integration_data( $result );
			$integration_type    = isset( $misc_settings['integration_type'] ) ? $misc_settings['integration_type'] : '';
			$integrated_item_ids = self::bbapp_iap_product_integration_ids( $integration_type, $integration_data );

			$ios                = self::bbapp_iap_ios_product_info( $store_data );
			$android            = self::bbapp_iap_android_product_info( $store_data );
			$bbapp_product_type = isset( $store_data['bbapp_product_type'] ) ? $store_data['bbapp_product_type'] : 'free';

			// Check Product is configure properly or not. If not it should not return in api response.
			if (
				( isset( $store_data['device_platforms'] ) && in_array( 'ios', $store_data['device_platforms'], true ) && ( empty( $ios['status'] ) || ( 'free' !== $bbapp_product_type && empty( $ios['store_product_id'] ) ) ) ) ||
				( isset( $store_data['device_platforms'] ) && in_array( 'android', $store_data['device_platforms'], true ) && ( empty( $android['status'] ) || ( 'free' !== $bbapp_product_type && empty( $android['store_product_id'] ) ) ) ) ||
				empty( $integrated_item_ids )
			) {
				return $product;
			}

			if ( is_user_logged_in() ) {
				// NOTE : Get user_id and check into bbapp_orders if this bbapp.
				$has_access = ProductHelper::has_active_order( $result, get_current_user_id() );

				// Check Any order exist for same group product.
				$group_active_product = 0;
				if ( ! empty( $result->iap_group ) ) {
					$group_active_product = ProductHelper::get_group_active_order_product_id( $result->iap_group, get_current_user_id() );
				}
			} else {
				$has_access           = false;
				$group_active_product = 0;
			}
			if ( false === self::instance()->is_enabled_integration( $integration_type ) ) {
				return $product;
			}
			$product = array(
				'product_id'           => (int) $result->id,
				'product_name'         => $result->name,
				'product_tagline'      => $result->tagline,
				'product_desc'         => $result->description,
				'benefits'             => $misc_settings['benefits'],
				'global_subscription'  => $misc_settings['global_subscription'] ? true : false,
				'bbapp_product_type'   => $bbapp_product_type,
				'ios'                  => $ios,
				'android'              => $android,
				'integration_type'     => $misc_settings['integration_type'],
				'integrated_item_ids'  => $integrated_item_ids,
				'has_access'           => $has_access,
				'group_active_product' => $group_active_product,
				'sort_order'           => (int) $result->menu_order,
			);
		}

		return $product;

	}

	/**
	 * BuddyBoss WordCamp App Booths
	 *
	 * @param array $args argument.
	 *
	 * @return array {array}
	 */
	public static function get_booths( $args = array() ) {
		global $wpdb;

		$global_prefix = $wpdb->prefix;
		$where         = array();

		$query   = "SELECT * FROM {$global_prefix}posts AS booth";
		$where[] = "booth.post_type = '" . bbwc_booths_post_type() . "' AND booth.post_status = 'publish'";

		if ( isset( $args['title'] ) && ! empty( $args['title'] ) ) {
			$where[] = "booth.post_title LIKE '%" . $args['title'] . "%'";
		}

		if ( isset( $args['number'] ) && ! empty( $args['number'] ) ) {
			$query  .= " INNER JOIN {$global_prefix}postmeta AS meta ON (booth.ID = meta.post_id )";
			$where[] = "meta.meta_key='bbwc-booths_booth_number'";
			$where[] = 'meta.meta_value=' . $args['number'];
		}

		if ( isset( $args['category'] ) && ! empty( $args['category'] ) ) {
			$query  .= " INNER JOIN {$global_prefix}term_relationships AS category ON (booth.ID = category.object_id )";
			$where[] = 'category.term_taxonomy_id=' . $args['category'];
		}

		$where_conditions = implode( ' AND ', $where );
		$query           .= " WHERE " . $where_conditions;
		$query           .= " LIMIT %d, %d";
		$query            = $wpdb->prepare( $query, $args['offset'], $args['limit'] );

		$booths = array();
		$results  = $wpdb->get_results( $query, OBJECT ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

		foreach ( $results as $result ) {

			$booths[] = array(
				'id'           => (int) $result->ID,
				'title'        => $result->post_title,
				'desc'         => $result->post_content,
				'image'        => wp_get_attachment_image_src( get_post_thumbnail_id( $result->ID ) ),
				'booth_number' => get_post_meta( $result->ID, 'bbwc-booths_booth_number', true ),
				'category'     => join(', ', wp_list_pluck(get_the_terms( $result->ID, bbwc_booths_category() ), 'name')),
			);
		}
		return $booths;
	}
}
