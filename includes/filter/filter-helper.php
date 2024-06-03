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

        if ( ! empty( $args['s'] ) ) {
            $filter_args['s'] = $args['s'];
        }

        if ( is_array( $args['tax_query'] ) && ! empty( $args['tax_query'] ) ) {
            $filter_args['tax_query'] = array(
                'relation' => 'AND',
            );

            foreach ( $args['tax_query'] as $key => $value ) {
                array_push(
                    $filter_args['tax_query'],
                    array(
                        'taxonomy' => $key,
                        'field'    => 'slug',
                        'terms'    => is_array( $value ) ? $value : array( $value ),
                    )
                );
            }

        }

        $filter_query = new \WP_Query( $filter_args );

        $results              = array();
        $results['posts']     = ! empty( $filter_query->posts ) ? $filter_query->posts : array();
        $results['total']     = ! empty( $filter_query->found_posts ) ? $filter_query->found_posts : 0;
        $results['max_pages'] = ! empty( $filter_query->max_num_pages ) ? $filter_query->max_num_pages : 0;
        return $results;
    }

}
