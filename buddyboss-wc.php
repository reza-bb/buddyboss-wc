<?php

/**
 * Plugin Name:       BuddyBoss WordCamp EU
 * Plugin URI:        https://www.buddyboss.com/
 * Description:       The BuddyBoss App plugin for WC Europe.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.3
 * Author:            BuddyBoss
 * Author URI:        https://www.buddyboss.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       buddyboss-wc
 * Domain Path:       /languages
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;
/**
 * The Main Plugin Requirements Checker
 *
 * @since 1.0.0
 */
final class BuddyBoss_WC_Prepare {

    /**
     * Static Property To Hold Singleton Instance
     *
     * @var BuddyBoss_WC_Prepare
     */
    private static $instance;

    /**
     * Requirements Array
     *
     * @since 1.0.0
     * @var array
     */
    private $requirements = [
        'php' => [
            'name'    => 'PHP',
            'minimum' => '7.3',
            'exists'  => true,
            'met'     => false,
            'checked' => false,
            'current' => false,
        ],
        'wp'  => [
            'name'    => 'WordPress',
            'minimum' => '5.2',
            'exists'  => true,
            'checked' => false,
            'met'     => false,
            'current' => false,
        ],
    ];

    /**
     * Singleton Instance
     *
     * @return void
     */
    public static function get_instance() {

        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Setup Plugin Requirements
     *
     * @since 1.0.0
     */
    private function __construct() {
        // Always load translation
        add_action( 'plugins_loaded', [$this, 'load_text_domain'] );

        // Initialize plugin functionalities or quit
        $this->requirements_met() ? $this->initialize_modules() : $this->quit();
    }

    /**
     * Load Localization Files
     *
     * @since 1.0
     * @return void
     */
    public function load_text_domain() {
        $locale = apply_filters( 'plugin_locale', get_user_locale(), 'buddyboss-wc' );

        unload_textdomain( 'buddyboss-wc' );
        load_textdomain( 'buddyboss-wc', WP_LANG_DIR . '/buddyboss-wc/buddyboss-wc-' . $locale . '.mo' );
        load_plugin_textdomain( 'buddyboss-wc', false, self::get_plugin_dir() . 'languages/' );
    }

    /**
     * Initialize Plugin Modules
     *
     * @since 1.0.0
     * @return void
     */
    private function initialize_modules() {
        require_once dirname( __FILE__ ) . '/autoloader.php';

		// Include the bootstraper file if not loaded.
        if ( !class_exists( 'BuddyBoss_WC' ) ) {
            require_once self::get_plugin_dir() . 'includes/class-buddyboss-wc.php';
        }

		// Initialize the bootstraper if exists.
        if ( class_exists( 'BuddyBoss_WC' ) ) {

            // Initialize all modules through plugins_loaded
            add_action( 'plugins_loaded', [$this, 'init'] );
 
            register_activation_hook( self::get_plugin_file(), [$this, 'activate'] );
            register_deactivation_hook( self::get_plugin_file(), [$this, 'deactivate'] );
        }

    }

    /**
     * Check If All Requirements Are Fulfilled.
     *
     * @since 1.0.0
     *
     * @return boolean
     */
    private function requirements_met() {

        $this->prepare_requirement_versions();

        $passed  = true;
        $to_meet = wp_list_pluck( $this->requirements, 'met' );

        foreach ( $to_meet as $met ) {

            if ( empty( $met ) ) {
                $passed = false;
                continue;
            }

        }

        return $passed;

    }

    /**
     * Requirement Version Prepare
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function prepare_requirement_versions() {

        foreach ( $this->requirements as $dependency => $config ) {

            switch ( $dependency ) {
            case 'php':
                $version = phpversion();
                break;
            case 'wp':
                $version = get_bloginfo( 'version' );
                break;
            default:
                $version = false;
            }

            if ( !empty( $version ) ) {
                $this->requirements[$dependency]['current'] = $version;
                $this->requirements[$dependency]['checked'] = true;
                $this->requirements[$dependency]['met']     = version_compare( $version, $config['minimum'], '>=' );
            }

        }

    }

    /**
     * Initialize everything
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function init() {
        BuddyBoss_WC::instantiate( self::get_plugin_file() );
    }

    /**
     * Called Only Once While Activation
     *
     * @return void
     */
    public function activate() {
    }

    /**
     * Called Only Once While Deactivation
     *
     * @return void
     */
    public function deactivate() {
    }

    /**
     * Quit Plugin Execution
     *
     * @return void
     */
    private function quit() {
        add_action( 'admin_head', [$this, 'show_plugin_requirements_not_met_notice'] );
    }

    /**
     * Show Error Notice For Missing Requirements
     *
     * @return void
     */
    public function show_plugin_requirements_not_met_notice() {
        printf( '<div>Minimum requirements for %1$s are not met. Please update requirements to continue.</div>', esc_html( 'BuddyBoss WordCamp EU' ) );
    }

    /**
     * Plugin Current Production Version
     *
     * @return string
     */
    public static function get_version() {
        return '1.0.0';
    }

    /**
     * Plugin Main File
     *
     * @return string
     */
    public static function get_plugin_file() {
        return __FILE__;
    }

    /**
     * Plugin Base Directory Path
     *
     * @return void
     */
    public static function get_plugin_dir() {
        return trailingslashit( plugin_dir_path( self::get_plugin_file() ) );
    }

}

BuddyBoss_WC_Prepare::get_instance();
