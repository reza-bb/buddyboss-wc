<?php
/**
 * Class Filter_Rest.
 *
 * @package BuddyBoss_WC\Includes
 */

namespace BuddyBoss_WC\Includes\Filter;

use BuddyBoss_WC\Includes\Filter\Filter_Helper as Helper;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Class Filter_Rest
 *
 * @package BuddyBoss_WC\Includes\Filter\Filter_Rest
 */
class Filter_Rest extends WP_REST_Controller {

    /**
     * API namespace.
     *
     * @var string
     */
    protected $namespace = 'buddyboss-app-wc/v1';

    /**
     * Rest base.
     *
     * @var string
     */
    protected $rest_base = 'filter';

    protected $post_type;

    /**
     * Class instance.
     *
     * @var object
     */
    protected static $instance;

    /**
     * AccountSettingsRest constructor.
     */
    public function __construct() {
        /** Nothing here */
    }

    /**
     * Get the instance of the class.
     *
     * @return mixed
     */
    public static function instance() {

        if ( ! isset( self::$instance ) ) {
            $class          = __CLASS__;
            self::$instance = new $class();
            self::$instance->hooks();
        }

        return self::$instance;
    }

    /**
     * Class hooks.
     */
    public function hooks() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ), 99 );
    }

    /**
     * Register the component routes.
     *
     * @since 0.1.0
     */
    public function register_routes() {

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_items' ),
                    'permission_callback' => array( $this, 'get_items_permissions_check' ),
                ),
                'schema' => array( $this, 'get_item_schema' ),
            )
        );
    }

    /**
     * Check if a given request has access.
     *
     * @param WP_REST_Request $request Full data about the request.
     *
     * @return WP_Error|bool
     *
     * @since 0.1.0
     */
    public function get_items_permissions_check( $request ) {
        return true;
    }

    /**
     * Retrieve Filters.
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return WP_REST_Response | WP_Error
     * @since 1.0
     *
     * @api            {GET} /wp-json/buddyboss-app-wc/v1/filter Filter CPT's
     * @apiGroup       Filter
     * @apiDescription Retrieve all CPT's according to filter.
     * @apiVersion     1.0.0
     * @apiPermission  LoggedInUser
     */
    public function get_items( $request ) {

        if ( empty( $request->get_param( 'post_type' ) ) ) {
            return new WP_Error(
                'missing_post_type_param',
                __( 'Must provide a post type for filtering.', 'buddyboss-wc' ),
                array( 'status' => 401 )
            );
        }

        $allowed_tax = array(
            'category',
            'post_tag',
            'track',
            'time',
            'past_event',
            'speaker',
        );
        $args              = array();
        $args['post_type'] = $this->post_type = strtolower( $request->get_param( 'post_type' ) );
        $args['s']         = ! empty( $request->get_param( 's' ) ) ? $request->get_param( 's' ) : '';
        $page              = ! empty( $request->get_param( 'page' ) ) ? $request->get_param( 'page' ) : 1;
        $per_page          = ! empty( $request->get_param( 'per_page' ) ) ? $request->get_param( 'per_page' ) : 20;
        $args['offset']    = ( $page - 1 ) * $per_page;
        $args['limit']     = $per_page;

        foreach ( $allowed_tax as $taxonomy ) {

            if ( ! empty( $request->get_param( $taxonomy ) ) ) {
                $args['tax_query'][$taxonomy] = $request->get_param( $taxonomy );
            }

        }

        $results = Helper::get_result( $args );

        if ( is_wp_error( $results ) ) {
            return new WP_Error(
                'error_while_retrieving',
                __( 'Error encountered while retrieving filtered result.', 'buddyboss-wc' ),
                array( 'status' => 500 )
            );
        }

        $retval = array();

        if( is_array( $results['posts'] ) ) {
            foreach ( $results['posts'] as $result ) {
                $retval[] = $this->prepare_response_for_collection(
                    $this->prepare_item_for_response( $result, $request )
                );
            }
        }

        $response = rest_ensure_response( $retval );
        $response = $this->rest_response_add_total_headers( $response, $results['total'], $results['max_pages'], $page );
        return $response;
    }

    /**
     * Prepare Rest response.
     *
     * @param object          $nav     Navigation object.
     * @param WP_REST_Request $request Request used to generate the response.
     *
     * @return mixed|void|WP_Error|WP_REST_Response
     */
    public function prepare_item_for_response( $item, $request ) {
        // Restores the more descriptive, specific name for use within this method.
        $post = $item;

        $GLOBALS['post'] = $post;

        setup_postdata( $post );

        // Base fields for every post.
        $data         = array();
        $data['id']   = $post->ID;
        $data['date'] = $this->prepare_date_response( $post->post_date_gmt, $post->post_date );

        if ( '0000-00-00 00:00:00' === $post->post_date_gmt ) {
            $post_date_gmt = get_gmt_from_date( $post->post_date );
        } else {
            $post_date_gmt = $post->post_date_gmt;
        }

        $data['date_gmt'] = $this->prepare_date_response( $post_date_gmt );
        $data['guid']     = array(
            /** This filter is documented in wp-includes/post-template.php */
            'rendered' => apply_filters( 'get_the_guid', $post->guid, $post->ID ),
            'raw'      => $post->guid,
        );
        $data['modified'] = $this->prepare_date_response( $post->post_modified_gmt, $post->post_modified );

        if ( '0000-00-00 00:00:00' === $post->post_modified_gmt ) {
            $post_modified_gmt = gmdate( 'Y-m-d H:i:s', strtotime( $post->post_modified ) - ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) );
        } else {
            $post_modified_gmt = $post->post_modified_gmt;
        }

        $data['modified_gmt'] = $this->prepare_date_response( $post_modified_gmt );

        $data['password'] = $post->post_password;

        $data['slug'] = $post->post_name;

        $data['status'] = $post->post_status;

        $data['type'] = $post->post_type;

        $data['link'] = get_permalink( $post->ID );

        $data['title'] = array();

        $data['title']['raw'] = $post->post_title;

        add_filter( 'protected_title_format', array( $this, 'protected_title_format' ) );

        $data['title']['rendered'] = get_the_title( $post->ID );

        remove_filter( 'protected_title_format', array( $this, 'protected_title_format' ) );

        $data['content']        = array();
        $data['content']['raw'] = $post->post_content;
        /** This filter is documented in wp-includes/post-template.php */
        $data['content']['rendered'] = post_password_required( $post ) ? '' : apply_filters( 'the_content', $post->post_content );

        $data['content']['protected']     = (bool) $post->post_password;
        $data['content']['block_version'] = block_version( $post->post_content );
        /** This filter is documented in wp-includes/post-template.php */
        $excerpt = apply_filters( 'get_the_excerpt', $post->post_excerpt, $post );

        /** This filter is documented in wp-includes/post-template.php */
        $excerpt = apply_filters( 'the_excerpt', $excerpt );

        $data['excerpt'] = array(
            'raw'       => $post->post_excerpt,
            'rendered'  => post_password_required( $post ) ? '' : $excerpt,
            'protected' => (bool) $post->post_password,
        );

        $data['author'] = (int) $post->post_author;

        $data['featured_media'] = (int) get_post_thumbnail_id( $post->ID );

        $data['parent'] = (int) $post->post_parent;

        $data['menu_order'] = (int) $post->menu_order;

        $data['comment_status'] = $post->comment_status;

        $data['ping_status'] = $post->ping_status;

        $data['sticky'] = is_sticky( $post->ID );

        $template = get_page_template_slug( $post->ID );

        if ( $template ) {
            $data['template'] = $template;
        } else {
            $data['template'] = '';
        }

        $data['format'] = get_post_format( $post->ID );

        if ( empty( $data['format'] ) ) {
            $data['format'] = 'standard';
        }

        $meta_data = array();
        $all_meta  = get_post_meta( $post->ID );

        foreach ( $all_meta as $key => $value ) {
            $meta_data[$key] = $value[0];
        }

        $data['meta'] = $meta_data;

        $taxonomies = wp_list_filter( get_object_taxonomies( $this->post_type, 'objects' ), array( 'show_in_rest' => true ) );

        foreach ( $taxonomies as $taxonomy ) {
            $base        = ! empty( $taxonomy->rest_base ) ? $taxonomy->rest_base : $taxonomy->name;
            $terms       = get_the_terms( $post, $taxonomy->name );
            $data[$base] = $terms ? array_values( wp_list_pluck( $terms, 'term_id' ) ) : array();
        }

        $context = ! empty( $request['context'] ) ? $request['context'] : 'view';
        $data    = $this->add_additional_fields_to_object( $data, $request );
        $data    = $this->filter_response_by_context( $data, $context );

        // Wrap the data in a response object.
        $response = rest_ensure_response( $data );

        $response->add_links( $this->prepare_links( $post ) );

        return apply_filters( "rest_prepare_{$this->post_type}", $response, $post, $request );
    }

    /**
     * Prepare links for the request.
     *
     * @param string $nav_slug Navigation slug.
     * @return array Links for the given group.
     */
    protected function prepare_links( $post ) {
        $base      = '/wp/v2/';
        $post_base = $base . $this->post_type;

        // Entity meta.
        $links = array(
            'self'       => array(
                'href' => rest_url( $post_base . '/' . $post->ID ),
            ),
            'collection' => array(
                'href' => rest_url( $post_base ),
            ),
            'about'      => array(
                'href' => rest_url( $base . 'types/' . $this->post_type ),
            ),
            'author'     => array(
                'href'       => rest_url( $base . 'users/' . $post->post_author ),
                'embeddable' => true,
            ),
        );

        return $links;
    }

    /**
     * Check the post_date_gmt or modified_gmt and prepare any post or
     * modified date for single post output.
     *
     * @param string      $date_gmt GMT date format.
     * @param string|null $date     forum date.
     *
     * @return string|null ISO8601/RFC3339 formatted datetime.
     */
    public function prepare_date_response( $date_gmt, $date = null ) {

		// Use the date if passed.
        if ( isset( $date ) ) {
            return mysql_to_rfc3339( $date ); // phpcs:ignore WordPress.DB.RestrictedFunctions.mysql_to_rfc3339, PHPCompatibility.Extensions.RemovedExtensions.mysql_DeprecatedRemoved
        }

		// Return null if $date_gmt is empty/zeros.
        if ( '0000-00-00 00:00:00' === $date_gmt ) {
            return null;
        }

        // Return the formatted datetime.
        return mysql_to_rfc3339( $date_gmt ); // phpcs:ignore WordPress.DB.RestrictedFunctions.mysql_to_rfc3339, PHPCompatibility.Extensions.RemovedExtensions.mysql_DeprecatedRemoved
    }

    /**
     * Undocumented function
     *
     * @param WP_REST_Response $response
     * @param integer $total
     * @param integer $max_pages
     * @param integer $page
     * @return void
     */
	public function rest_response_add_total_headers( WP_REST_Response $response, $total = 0, $max_pages = 0, $page = 0 ) {
		if ( ! $total || ! $max_pages || ! $page ) {
			return $response;
		}

		$response->header( 'X-WP-Total', (int) $total );
		$response->header( 'X-WP-TotalPages', (int) $max_pages );
        $response->header('X-WP-Page', (int) $page);

		return $response;
	}
}
