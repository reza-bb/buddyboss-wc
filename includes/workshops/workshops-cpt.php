<?php
/**
 * Setup Workshops CPT
 *
 * @package BuddyBoss_WC
 */

namespace BuddyBoss_WC\Includes\Workshops;

defined( 'ABSPATH' ) || exit;

class Workshops_CPT {
    /**
     * Instance of Workshops_CPT
     *
     * @var BuddyBoss_WC\Includes\Booths\Workshops_CPT
     */
    private static $instance;

    /**
     * Undocumented variable
     *
     * @var string
     */
    public $post_type = 'bbwc-workshops';

    /**
     * Undocumented variable
     *
     * @var string
     */
    public $post_category = 'bbwc-workshops-category';

    /**
     * Undocumented variable
     *
     * @var string
     */
    public $post_location = 'bbwc-workshops-location';

    /**
     * Undocumented variable
     *
     * @var [type]
     */
    public $type_key;
    public $date_key;
    public $day_key;
    public $speaker_key;

    /**
     * Undocumented function
     *
     * @return Workshops_CPT
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

        $this->type_key    = $this->post_type . '_type';
        $this->date_key    = $this->post_type . '_date';
        $this->day_key     = $this->post_type . '_day';
        $this->speaker_key = $this->post_type . '_speaker';

        add_action( 'init', array( $this, 'register_workshops' ) );
        add_action( 'init', array( $this, 'register_workshops_taxonomy' ) );
        add_action( 'admin_menu', array( $this, 'register_workshops_submenu' ) );
        add_action( 'add_meta_boxes', array( $this, 'add_workshops_meta_boxes' ) );
        add_action( 'save_post', array( $this, 'save_workshops_meta_boxes' ) );
    }

    /**
     * Register custom post type for booth.
     *
     * @return  void
     */
    public function register_workshops() {
        $labels = array(
            'name'               => __( 'BuddyBoss WC Workshops', 'buddyboss-wc' ),
            'singular_name'      => __( 'BuddyBoss WC Workshop', 'buddyboss-wc' ),
            'menu_name'          => __( 'BuddyBoss WC Workshops', 'buddyboss-wc' ),
            'add_new'            => __( 'Add New', 'buddyboss-wc' ),
            'add_new_item'       => __( 'Add New BuddyBoss WC Workshop', 'buddyboss-wc' ),
            'edit'               => __( 'Edit', 'buddyboss-wc' ),
            'edit_item'          => __( 'Edit BuddyBoss WC Workshop', 'buddyboss-wc' ),
            'new_item'           => __( 'New BuddyBoss WC Workshop', 'buddyboss-wc' ),
            'view'               => __( 'View', 'buddyboss-wc' ),
            'view_item'          => __( 'View BuddyBoss WC Workshop', 'buddyboss-wc' ),
            'search_items'       => __( 'Search BuddyBoss WC Workshop', 'buddyboss-wc' ),
            'not_found'          => __( 'No BuddyBoss WC Workshops found', 'buddyboss-wc' ),
            'not_found_in_trash' => __( 'No BuddyBoss WC Workshops found in trash', 'buddyboss-wc' ),
            'parent'             => __( 'Parent BuddyBoss WC Workshops', 'buddyboss-wc' ),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => false,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'bbwc-workshops' ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 9,
            'supports'           => array( 'title', 'editor' ),
        );

        if ( function_exists( 'register_post_type' ) ) {
            register_post_type( $this->post_type, $args );
        }

    }

    public function register_workshops_taxonomy() {
        $category_labels = array(
            'name'                       => __( 'Workshops categories', 'buddyboss-wc' ),
            'singular_name'              => __( 'Workshop category', 'buddyboss-wc' ),
            'menu_name'                  => __( 'Workshops category', 'buddyboss-wc' ),
            'all_items'                  => __( 'Workshops categories', 'buddyboss-wc' ),
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
        $category_args = array(
            'labels'            => $category_labels,
            'hierarchical'      => true,
            'public'            => true,
            'show_ui'           => true,
            'show_admin_column' => false,
            'show_in_nav_menus' => true,
            'show_tagcloud'     => true,
        );

        $location_labels = array(
            'name'                       => __( 'Workshops locations', 'buddyboss-wc' ),
            'singular_name'              => __( 'Workshop location', 'buddyboss-wc' ),
            'menu_name'                  => __( 'Workshops locations', 'buddyboss-wc' ),
            'all_items'                  => __( 'Workshops locations', 'buddyboss-wc' ),
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
        $location_args = array(
            'labels'            => $location_labels,
            'hierarchical'      => true,
            'public'            => true,
            'show_ui'           => true,
            'show_admin_column' => false,
            'show_in_nav_menus' => true,
            'show_tagcloud'     => true,
        );

        if ( function_exists( 'register_taxonomy' ) ) {
            register_taxonomy( $this->post_category, array( $this->post_type ), $category_args );
            register_taxonomy( $this->post_location, array( $this->post_type ), $location_args );
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

        if ( 'bbwc-workshops-category' !== $taxonomy ) {
            return $term;
        }

        return $term;
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function register_workshops_submenu() {
        $cpt_url = 'edit.php?post_type=' . $this->post_type;

        add_submenu_page(
            'buddyboss-wc',
            __( 'Workshops', 'buddyboss-wc' ),
            __( 'Workshops', 'buddyboss-wc' ),
            'manage_options',
            $cpt_url,
            '',
        );

        $category_url = 'edit-tags.php?taxonomy=' . $this->post_category . '&post_type=' . $this->post_type;
        add_submenu_page(
            'buddyboss-wc',
            __( 'Workshops Category', 'buddyboss-wc' ),
            __( 'Workshops Category', 'buddyboss-wc' ),
            'manage_options',
            $category_url,
            '',
        );

        $location_url = 'edit-tags.php?taxonomy=' . $this->post_location . '&post_type=' . $this->post_type;
        add_submenu_page(
            'buddyboss-wc',
            __( 'Workshops Location', 'buddyboss-wc' ),
            __( 'Workshops Location', 'buddyboss-wc' ),
            'manage_options',
            $location_url,
            '',
        );
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function add_workshops_meta_boxes() {
        add_meta_box(
            'bbwc-workshops-meta-box',
            esc_html__( 'Additional data', 'buddyboss-wc' ),
            array( $this, "render_workshops_metabox" ),
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
    public function render_workshops_metabox( $post ) {
        wp_nonce_field( plugin_basename( __FILE__ ), $post->post_type . '_noncename' );
        $this->render_workshops_type( $post );
        $this->render_workshops_speaker( $post );
        $this->render_workshops_day( $post );
        $this->render_workshops_date( $post );
    }

    /**
     * Undocumented function
     *
     * @param [type] $post
     * @return void
     */
    public function render_workshops_type( $post ) {
        $allowed_types = array(
            esc_html__( 'Workshops', 'buddyboss-wc' ),
            esc_html__( 'Keynotes', 'buddyboss-wc' ),
        );

        $type = get_post_meta( $post->ID, $this->type_key, true );
        ?>
        <div>
            <label><?php echo esc_html__( 'Type', 'buddyboss-wc' ); ?></label>
        </div>
        <div>
        <?php
        foreach ( $allowed_types as $value ) {
            ?>
            <input <?php checked( $type, strtolower( $value ), true );?> type="radio" class="bbwc-type-field" id="bbwc-type-<?php echo esc_attr( strtolower( $value ) ); ?>" name="<?php echo esc_attr( $this->type_key ); ?>" value="<?php echo esc_attr( strtolower( $value ) ); ?>">
            <label for="bbwc-type-<?php echo esc_attr( strtolower( $value ) ); ?>"><?php echo esc_html( $value ); ?></label>
            <?php
        }
        ?>
        </div>
        <?php
    }

    /**
     * Undocumented function
     *
     * @param [type] $post
     * @return void
     */
    public function render_workshops_speaker( $post ) {
        $speaker  = get_post_meta( $post->ID, $this->speaker_key, true );
        $users    = get_users();
        $speakers = array();

        foreach ( $users as $user ) {
            $speakers[$user->ID] = $user->display_name;
        }
        ?>
        <div class="">
            <div class="">
                <label for="bbwc-type-speaker"> <?php echo esc_html__( 'Speakers', 'buddyboss-wc' ); ?>  </label>
            </div>
            <div class="">
                <select name="<?php echo esc_attr( $this->speaker_key ); ?>" class="bbwc-speaker-field" id="bbwc-type-speaker">
                    <?php
                        if ( is_array( $speakers ) && ! empty( $speakers ) ) {
                            foreach ( $speakers as $id => $name ) {
                                ?>
                                <option <?php selected( $speaker, $id, true );?> value="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $name ); ?></option>
                                <?php
                            }
                        }
                    ?>
                </select>
            </div>
        </div>
        <?php
    }

    /**
     * Undocumented function
     *
     * @param [type] $post
     * @return void
     */
    public function render_workshops_day( $post ) {
        $day      = intval( get_post_meta( $post->ID, $this->day_key, true ) ) - 1;
        $days     = array(
            esc_html__( 'Day One', 'buddyboss-wc' ),
            esc_html__( 'Day Two', 'buddyboss-wc' ),
        );
        ?>
        <div class="">
            <div class="">
                <label for="bbwc-day-field"> <?php echo esc_html__( 'Day', 'buddyboss-wc' ); ?></label>
            </div>
            <div class="">
                <select name="<?php echo esc_attr( $this->day_key ); ?>" class="bbwc-day-field" id="bbwc-day-field">
                    <?php
                    if ( is_array( $days ) && ! empty( $days ) ) {
                        foreach ( $days as $key => $title ) {
                            ?>
                            <option <?php selected( $day, $key, true );?> value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $title ); ?></option>
                            <?php
                        }
                    }
                    ?>
                </select>
            </div>
        </div>
        <?php
    }

    /**
     * Undocumented function
     *
     * @param [type] $post
     * @return void
     */
    public function render_workshops_date( $post ) {
        $date     = get_post_meta( $post->ID, $this->date_key, true );
        ?>
        <label for="bbwc-date-field">Date</label>
        <input type="date" id="bbwc-date-field" name="<?php echo esc_attr( $this->date_key ); ?>" value="<?php echo esc_attr( $date ); ?>">
        <?php
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function save_workshops_meta_boxes( $post_id ) {

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if (
            'POST' !== $_SERVER['REQUEST_METHOD'] ||
            ! wp_verify_nonce( $_POST[$_POST['post_type'] . '_noncename'], plugin_basename( __FILE__ ) ) ||
            ! current_user_can( 'manage_options' )
        ) {
            return;
        }

        if( ! isset( $_POST[$this->type_key] ) || ! isset( $_POST[$this->day_key] ) || ! isset( $_POST[$this->date_key] ) || ! isset( $_POST[$this->speaker_key] ) ) {
            return;
        }

        $type    = sanitize_text_field( $_POST[$this->type_key] );
        $day     = sanitize_text_field( intval( $_POST[$this->day_key] ) + 1 );
        $date    = sanitize_text_field(  $_POST[$this->date_key] );
        $speaker = sanitize_text_field( intval( $_POST[$this->speaker_key] ) );

        update_post_meta( $post_id, $this->type_key, $type );
        update_post_meta( $post_id, $this->day_key, $day );
        update_post_meta( $post_id, $this->date_key, $date );
        update_post_meta( $post_id, $this->speaker_key, $speaker );
    }

}
