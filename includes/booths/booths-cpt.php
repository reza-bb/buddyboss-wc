<?php
/**
 * Setup Booths CPT
 *
 * @package BuddyBoss_WC
 */

namespace BuddyBoss_WC\Includes\Booths;

defined( 'ABSPATH' ) || exit;

class Booths_CPT {
    /**
     * Instance of Booths_CPT
     *
     * @var BuddyBoss_WC\Includes\Booths\Booths_CPT
     */
    private static $instance;

    /**
     * Undocumented variable
     *
     * @var string
     */
    private $post_type;

    /**
     * Undocumented variable
     *
     * @var string
     */
    private $post_taxonomy;

    /**
     * Undocumented variable
     *
     * @var [type]
     */
    private $booth_number_meta;

    /**
     * Undocumented function
     *
     * @return Hooks
     */
    public static function instance() {

        if ( ! self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Initialize
     *
     * @return void
     */
    public function init() {
        $this->post_type         = bbwc_booths_post_type();
        $this->post_taxonomy     = bbwc_booths_category();
        $this->booth_number_meta = $this->post_type . '_booth_number';

        add_action( 'init', array($this, 'register_booths') );
        add_action( 'init', array($this, 'register_booths_taxonomy') );
        add_action( 'admin_menu', array($this, 'register_booths_submenu') );
        add_action( 'add_meta_boxes', array( $this, 'add_booth_meta_boxes' ) );
        add_action( 'save_post', array( $this, 'save_booth_meta_boxes' ) );
    }

    /**
     * Register custom post type for booth.
     *
     * @return  void
     */
    public function register_booths() {
        $labels = array(
            'name'               => __( 'BuddyBoss WC Booths', 'buddyboss-wc' ),
            'singular_name'      => __( 'BuddyBoss WC Booth', 'buddyboss-wc' ),
            'menu_name'          => __( 'BuddyBoss WC Booths', 'buddyboss-wc' ),
            'add_new'            => __( 'Add New', 'buddyboss-wc' ),
            'add_new_item'       => __( 'Add New BuddyBoss WC Booth', 'buddyboss-wc' ),
            'edit'               => __( 'Edit', 'buddyboss-wc' ),
            'edit_item'          => __( 'Edit BuddyBoss WC Booth', 'buddyboss-wc' ),
            'new_item'           => __( 'New BuddyBoss WC Booth', 'buddyboss-wc' ),
            'view'               => __( 'View', 'buddyboss-wc' ),
            'view_item'          => __( 'View BuddyBoss WC Booth', 'buddyboss-wc' ),
            'search_items'       => __( 'Search BuddyBoss WC Booth', 'buddyboss-wc' ),
            'not_found'          => __( 'No BuddyBoss WC Booths found', 'buddyboss-wc' ),
            'not_found_in_trash' => __( 'No BuddyBoss WC Booths found in trash', 'buddyboss-wc' ),
            'parent'             => __( 'Parent BuddyBoss WC Booths', 'buddyboss-wc' ),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => false,
            'query_var'          => true,
            'rewrite'            => array('slug' => 'bbwc-booths'),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 9,
            'supports'           => array('title', 'editor', 'thumbnail'),
        );

        if ( function_exists( 'register_post_type' ) ) {
            register_post_type( $this->post_type, $args );
        }

    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function register_booths_taxonomy() {
        $labels = array(
            'name'                       => __( 'Booth categories', 'buddyboss-wc' ),
            'singular_name'              => __( 'Booth category', 'buddyboss-wc' ),
            'menu_name'                  => __( 'Booth category', 'buddyboss-wc' ),
            'all_items'                  => __( 'Booth categories', 'buddyboss-wc' ),
            'parent_item'                => __( 'Parent Genre', 'buddyboss-wc' ),
            'parent_item_colon'          => __( 'Parent Genre:', 'buddyboss-wc' ),
            'new_item_name'              => __( 'New Genre Name', 'buddyboss-wc' ),
            'add_new_item'               => __( 'Add New Genre', 'buddyboss-wc' ),
            'edit_item'                  => __( 'Edit Genre', 'buddyboss-wc' ),
            'update_item'                => __( 'Update Genre', 'buddyboss-wc' ),
            'view_item'                  => __( 'View Genre', 'buddyboss-wc' ),
            'separate_items_with_commas' => __( 'Separate genres with commas', 'buddyboss-wc' ),
            'add_or_remove_items'        => __( 'Add or remove genres', 'buddyboss-wc' ),
            'choose_from_most_used'      => __( 'Choose from the most used genres', 'buddyboss-wc' ),
            'popular_items'              => __( 'Popular Genres', 'buddyboss-wc' ),
            'search_items'               => __( 'Search Genres', 'buddyboss-wc' ),
            'not_found'                  => __( 'Genre Not Found', 'buddyboss-wc' ),
            'no_terms'                   => __( 'No genres', 'buddyboss-wc' ),
            'items_list'                 => __( 'Genres list', 'buddyboss-wc' ),
            'items_list_navigation'      => __( 'Genres list navigation', 'buddyboss-wc' ),
        );

        $args = array(
            'labels'            => $labels,
            'hierarchical'      => true,
            'public'            => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud'     => true,
        );

        if ( function_exists( 'register_taxonomy' ) ) {
            register_taxonomy( $this->post_taxonomy, array($this->post_type), $args ); //
        }

    }

    /**
     * Undocumented function
     *
     * @param [type] $term
     * @param [type] $taxonomy
     * @return object
     */
    public function prepare_term( $term, $taxonomy ) {

        if ( 'bbwc-booths-category' !== $taxonomy ) {
            return $term;
        }

        return $term;
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function register_booths_submenu() {
        $booth_cpt_url = 'edit.php?post_type=' . $this->post_type;
        add_submenu_page(
            'buddyboss-wc',
            __( 'Booths', 'buddyboss-wc' ),
            __( 'Booths', 'buddyboss-wc' ),
            'manage_options',
            $booth_cpt_url,
            '',
        );

        $booth_cat_url = 'edit-tags.php?taxonomy=' . $this->post_taxonomy . '&post_type=' . $this->post_type;
        add_submenu_page(
            'buddyboss-wc',
            __( 'Booths Category', 'buddyboss-wc' ),
            __( 'Booths Category', 'buddyboss-wc' ),
            'manage_options',
            $booth_cat_url,
            '',
        );

        remove_submenu_page( 'buddyboss-wc', 'buddyboss-wc' );
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function add_booth_meta_boxes() {
        add_meta_box(
            'bbwc-booths-meta-box',
            esc_html__( 'Additional data', 'buddyboss-wc' ),
            array( $this, "render_booths_metabox" ),
            $this->post_type,
            'normal',
            'default'
        );
    }

    /**
     * Undocumented function
     *
     * @param [type] $post
     * @return void
     */
    public function render_booths_metabox( $post ) {
        $booth_number = get_post_meta( $post->ID, $this->booth_number_meta, true );
        $booth_number = ! empty( $booth_number ) ? (int) $booth_number : 0;
        wp_nonce_field( plugin_basename( __FILE__ ), $post->post_type . '_noncename' );
        ?>
        <label for="bbwc-number-field"><?php echo esc_html__( 'Booth Number', 'buddyboss-wc' ); ?></label>
        <input type="number" class="bbwc-number-field" id="bbwc-number-field" name="<?php echo esc_attr( $this->booth_number_meta ); ?>" value="<?php echo esc_attr( $booth_number ); ?>">
        <?php
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function save_booth_meta_boxes( $post_id ) {

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if (
            'POST' !== $_SERVER['REQUEST_METHOD'] ||
            ! wp_verify_nonce( $_POST[$_POST['post_type'] . '_noncename'], plugin_basename( __FILE__ ) ) ||
            ! current_user_can( 'manage_options' ) ||
            ! isset( $_POST[$this->booth_number_meta] )
        ) {
            return;
        }

        $booth_number = sanitize_text_field( intval( $_POST[$this->booth_number_meta] ) );
        update_post_meta( $post_id, $this->booth_number_meta, $booth_number );
    }

}
