<?php

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'BuddyBoss_WC' ) ) {

    /**
     * BuddyBoss_WC class.
     *
     * @since 1.0.0
     */
    final class BuddyBoss_WC {

        /**
         * @var BuddyBoss_WC The Actual BuddyBoss_WC instance
         *
         * @since 1.0.0
         */
        private static $instance;

        /**
         * Main File
         */
        private $file = '';

        /**
         * Plugin Version
         */
        private $version = '';

        /**
         * Throw Error While Trying To Clone Object
         *
         * @since 1.0.0
         * @return void
         */
        public function __clone() {
            _doing_it_wrong( __FUNCTION__, __( 'Cloning is forbidden.', 'buddyboss-wc' ), '1.0.0' );
        }

        /**
         * Disabling Un-serialization Of This Class
         */
        public function __wakeup() {
            _doing_it_wrong( __FUNCTION__, __( 'Unserializing instances of this class is forbidden.', 'buddyboss-wc' ), '1.0.0' );
        }

        /**
         * The actual BuddyBoss_WC instance
         *
         * @since 1.0.0
         *
         * @param string $file
         *
         * @return void
         */
        public static function instantiate( $file = '' ) {

// Return if already instantiated
            if ( self::instantiated() ) {
                return self::$instance;
            }

            self::prepare_instance( $file );

            self::$instance->initialize_constants();
            self::$instance->define_tables();
            self::$instance->include_files();
            self::$instance->initialize_components();
            self::$instance->initialize_hooks();

            return self::$instance;

        }

        /**
         * Return If The Main Class has Already Been Instantiated Or Not
         *
         * @since 1.0.0
         * @return boolean
         */
        private static function instantiated() {
            if ( ( null !== self::$instance ) && ( self::$instance instanceof BuddyBoss_WC ) ) {
                return true;
            }

            return false;
        }

        /**
         * Prepare Singleton Instance
         *
         * @since 1.0.0
         *
         * @param string $file
         *
         * @return void
         */
        private static function prepare_instance( $file = '' ) {
            self::$instance          = new self();
            self::$instance->file    = $file;
            self::$instance->version = BuddyBoss_WC_Prepare::get_version();
        }

        /**
         * Assets Directory URL
         *
         * @since 1.0.0
         *
         * @return void
         */
        public function get_assets_url() {
            return trailingslashit( BBWC_PLUGIN_URL . 'assets' );
        }

        /**
         * Assets Directory Path
         *
         * @since 1.0.0
         * @return void
         */
        public function get_assets_dir() {
            return trailingslashit( BBWC_PLUGIN_DIR . 'assets' );
        }

        /**
         * Plugin Directory URL
         *
         * @return void
         */
        public function get_plugin_url() {
            return trailingslashit( plugin_dir_url( BBWC_PLUGIN_FILE ) );
        }

        /**
         * Plugin Directory Path
         *
         * @return void
         */
        public function get_plugin_dir() {
            return BuddyBoss_WC_Prepare::get_plugin_dir();
        }

        /**
         * Plugin Basename
         *
         * @return void
         */
        public function get_plugin_basename() {
            return plugin_basename( BBWC_PLUGIN_FILE );
        }

        /**
         * Setup Plugin Constants
         *
         * @since 1.0.0
         * @return void
         */
        private function initialize_constants() {

            // Plugin Version
            self::$instance->define( 'BBWC_VERSION', BuddyBoss_WC_Prepare::get_version() );

            // Plugin Main File
            self::$instance->define( 'BBWC_PLUGIN_FILE', $this->file );

            // Plugin File Basename
            self::$instance->define( 'BBWC_PLUGIN_BASE', $this->get_plugin_basename() );

            // Plugin Main Directory Path
            self::$instance->define( 'BBWC_PLUGIN_DIR', $this->get_plugin_dir() );

            // Plugin Main Directory URL
            self::$instance->define( 'BBWC_PLUGIN_URL', $this->get_plugin_url() );

            // Plugin Assets Directory URL
            self::$instance->define( 'BBWC_ASSETS_URL', $this->get_assets_url() );

            // Plugin Assets Directory Path
            self::$instance->define( 'BBWC_ASSETS_DIR', $this->get_assets_dir() );

        }

        /**
         * Define constant if not already set.
         *
         * @since 1.0.0
         * @param string      $name  Constant name.
         * @param string|bool $value Constant value.
         */
        private function define( $name, $value ) {

            if ( ! defined( $name ) ) {
                define( $name, $value );
            }

        }

        /**
         * Define DB Tables Required For This Plugin
         *
         * @since 1.0.0
         * @return void
         */
        private function define_tables() {
            // To Be Implemented.
        }

        /**
         * Include All Required Files
         *
         * @since 1.0.0
         * @return void
         */
        private function include_files() {
        }

        /**
         * Initialize All Components
         *
         * @since 1.0.0
         * @return void
         */
        private function initialize_components() {
            \BuddyBoss_WC\Includes\Filter\Filter_Rest::instance();
        }

        /**
         * Initialize All Hooks
         *
         * @since 1.0.0
         * @return void
         */
        private function initialize_hooks() {
            add_filter( 'bbapp_editor_allowed_cpt', array( $this, 'allow_wordcamp_cpt' ) );
            add_filter( 'bbapp_disallowed_block_for_other_cpt', array( $this, 'allow_bbapp_blocks_cpt' ) );
            add_action( 'rest_api_init', array( $this, 'register_content_native_field' ), 99 );
        }

        /**
         * Undocumented function
         *
         * @param [type] $allowed_cpt
         * @return void
         */
        public function allow_wordcamp_cpt( $allowed_cpt ) {
            $allowed_cpt[] = 'keynotes';
            $allowed_cpt[] = 'booths';
            $allowed_cpt[] = 'workshop';
            return $allowed_cpt;
        }

        /**
         * Undocumented function
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
         * Undocumented function
         *
         * @param [type] $disallowed_blocks
         * @return void
         */
        public function allow_bbapp_blocks_cpt( $disallowed_blocks ) {
            return array_diff(
                $disallowed_blocks,
                array( 'bbapp/posts' )
            );
        }

    }

}

/**
 * Returns The Instance Of BuddyBoss_WC.
 * The main function that is responsible for returning BuddyBoss_WC instance.
 *
 * @since 1.0.0
 * @return BuddyBoss_WC
 */
function BBWC() {
    return BuddyBoss_WC::instantiate();
}
