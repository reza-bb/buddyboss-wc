<?php
/**
 * Class Hooks.
 *
 * @package BuddyBoss_WC\Includes\Blocks
 */
namespace BuddyBoss_WC\Includes\Blocks;

defined( 'ABSPATH' ) || exit;

class Hooks {

    /**
     * Class instance.
     *
     * @var object
     */
    protected static $instance;

    /**
     * Hooks constructor.
     *
     * @return void
     */
    private function __construct() {
        /** Nothing here. */
    }

    /**
     * Instance of Hooks class.
     *
     * @return object
     */
    public static function instance() {

        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Initialize All Hooks
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function hooks() {
        add_filter( 'bbapp_editor_allowed_cpt', array( $this, 'allow_wordcamp_cpt' ) );
        add_filter( 'bbapp_disallowed_block_for_other_cpt', array( $this, 'allow_bbapp_blocks_cpt' ) );
        add_action( 'rest_api_init', array( $this, 'register_content_native_field' ), 99 );
    }

    /**
     * Allow CPT's to use app page editor.
     *
     * @param array $allowed_cpt
     *
     * @return array
     */
    public function allow_wordcamp_cpt( $allowed_cpt ) {
        $allowed_cpt[] = 'keynotes';
        $allowed_cpt[] = 'booths';
        $allowed_cpt[] = 'workshop';
        return $allowed_cpt;
    }

    /**
     * Register 'content_native' REST field for CPTs
     *
     * @return void
     */
    public function register_content_native_field() {
        $object_types = array(
            'keynotes',
            'booths',
            'workshop',
        );

        foreach ( $object_types as $object_type ) {
            register_rest_field(
                $object_type,
                'content_native',
                array(
                    'get_callback'    => array( \BuddyBossApp\NativeAppPage\Gutenberg::instance(), 'get_content_native' ),
                    'update_callback' => null,
                    'schema'          => null,
                )
            );
        }

    }

    /**
     * Allow BB App blocks for other CPTs
     *
     * @param array $disallowed_blocks
     *
     * @return array
     */
    public function allow_bbapp_blocks_cpt( $disallowed_blocks ) {
        return array_diff(
            $disallowed_blocks,
            array( 'bbapp/posts' )
        );
    }

}
