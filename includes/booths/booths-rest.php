<?php
/**
 * Class Booths_Rest.
 *
 * @package BuddyBoss_WC\Includes\Booths
 */

namespace BuddyBoss_WC\Includes\Booths;

use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use BuddyBoss_WC\Includes\Booths\Booths_Helper as Helper;

/**
 * Class Booths_Rest
 *
 * @package BuddyBoss_WC\Includes\Booths\Booths_Rest
 */
class Booths_Rest extends WP_REST_Controller {

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
	protected $rest_base = 'booths';

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
			self::$instance->hooks(); // run the hooks.
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
		$retval = true;

		// if ( ! is_user_logged_in() ) {
		// 	$retval = new WP_Error(
		// 		'bbwc_rest_authorization_required',
		// 		__( 'Sorry, you are not allowed to see the content.', 'buddyboss-wc' ),
		// 		array(
		// 			'status' => rest_authorization_required_code(),
		// 		)
		// 	);
		// }

		return $retval;
	}

	/**
	 * Retrieve Booths.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response | WP_Error
	 * @since 1.0
	 *
	 * @api            {GET} /wp-json/buddyboss-app-wc/v1/booths Booths
	 * @apiName        GetBBAccountSettings
	 * @apiGroup       Booths
	 * @apiDescription Retrieve all booths.
	 * @apiVersion     1.0.0
	 * @apiPermission  LoggedInUser
	 */
	public function get_items( $request ) {

        $args             = array();
		$args['title']    = ! empty( $request->get_param( 'title' ) ) ? $request->get_param( 'title' ) : '';
		$args['number']   = ! empty( $request->get_param( 'number' ) ) ? $request->get_param( 'number' ) : 0;
		$args['category'] = ! empty( $request->get_param( 'category' ) ) ? $request->get_param( 'category' ) : 0;
		$page             = ! empty( $request->get_param( 'page' ) ) ? $request->get_param( 'page' ) : 1;
		$per_page         = ! empty( $request->get_param( 'per_page' ) ) ? $request->get_param( 'per_page' ) : 20;
		$args['offset']   = ( $page - 1 ) * $per_page;
        $args['limit']    = $per_page;

		$booths = Helper::get_booths( $args );

		if ( is_wp_error( $booths ) ) {
			return new WP_Error( 
                'error_while_retrieving', 
                __( 'Error encountered while retrieving Booths.', 'buddyboss-wc' ), 
                array( 'status' => 500 ) 
            );
		}

		return rest_ensure_response( $booths );

	}

	/**
	 * Prepare Rest response.
	 *
	 * @param object          $nav     Navigation object.
	 * @param WP_REST_Request $request Request used to generate the response.
	 *
	 * @return mixed|void|WP_Error|WP_REST_Response
	 */
	public function prepare_item_for_response( $nav, $request ) {
		$data = array(
			'name'     => $nav->name,
			'slug'     => $nav->slug,
			'position' => $nav->position,
			'link'     => $nav->link,
		);

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data    = $this->add_additional_fields_to_object( $data, $request );
		$data    = $this->filter_response_by_context( $data, $context );

		// @todo add prepare_links
		$response = rest_ensure_response( $data );

		$response->add_links( $this->prepare_links( $data['slug'] ) );

		/**
		 * Filter a notification value returned from the API.
		 *
		 * @param WP_REST_Response $response The response data.
		 * @param WP_REST_Request  $request  Request used to generate the response.
		 * @param object           $nav      Navigation object.
		 *
		 * @since 0.1.0
		 */
		return apply_filters( 'bbapp_rest_account_setting_prepare_value', $response, $request, $nav );
	}

	/**
	 * Prepare links for the request.
	 *
	 * @param string $nav_slug Navigation slug.
	 * @return array Links for the given group.
	 */
	protected function prepare_links( $nav_slug ) {
		$base  = '/' . $this->namespace . '/' . $this->rest_base;
		$links = array(
			'options' => array(
				'embeddable' => true,
				'href'       => rest_url( trailingslashit( $base ) . $nav_slug ),
			),
		);

		return $links;
	}


	/**
	 * Dispatch the request item.
	 *
	 * @param WP_REST_Request $request Rest request.
	 *
	 * @return mixed
	 */
	protected function dispatch( $request ) {

		$query_params = $request->get_params();

		if ( isset( $request->get_query_params()['_embed'] ) ) {
			$query_params['_embed'] = $request->get_query_params()['_embed'];
		}

		$request->set_query_params( $query_params );

		$server   = rest_get_server();
		$response = $server->dispatch( $request );

		return $response;
	}
}
