<?php
/**
 * Filter helper class.
 *
 * @package BuddyBoss_WC\Includes\Filter
 */

namespace BuddyBoss_WC\Includes\Filter;

/**
 * Class Filter_Helper
 * 
 * @package BuddyBoss_WC\Includes\Filter\Filter_Helper
 */
class Filter_Helper {

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
	 * BuddyBoss WordCamp App CPT's
	 *
	 * @param array $args argument.
	 *
	 * @return array {array}
	 */
	public static function get_result( $args = array() ) {
		$filter_args = array(
			'post_type'      => $args['post_type'],
			'post_status'    => 'publish',
			'posts_per_page' => $args['limit'], 
			'offset'         => $args['offset'],
		);

		if ( ! empty( $args['title'] ) ) {
			$filter_args['s'] = $args['title'];
		}

		if ( is_array( $args['tax_query'] ) && ! empty( $args['tax_query'] ) ) {
			$filter_args['tax_query'] = array(
				'relation' => 'AND'
			);

			foreach( $args['tax_query'] as $key => $value ) {
				array_push( 
					$filter_args['tax_query'], 
					array(
						'taxonomy' => $key,
						'field'    => 'slug',
						'terms'    => array( $value ),
					)
				);
			}
		}

		$filter_query = new \WP_Query( $filter_args );

		$results = ! empty( $filter_query->posts ) ? $filter_query->posts : array();

		// foreach ( $results as $result ) {

		// 	$booths[] = array(
		// 		'id'           => (int) $result->ID,
		// 		'title'        => $result->post_title,
		// 		'desc'         => $result->post_content,
		// 		'image'        => wp_get_attachment_image_src( get_post_thumbnail_id( $result->ID ) ),
		// 		'booth_number' => get_post_meta( $result->ID, 'bbwc-booths_booth_number', true ),
		// 		'category'     => join(', ', wp_list_pluck(get_the_terms( $result->ID, bbwc_booths_category() ), 'name')),
		// 	);
		// }
		return $results;
	}
}
