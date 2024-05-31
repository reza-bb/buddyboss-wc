<?php

/**
 * Register nav menu elements.
 *
 * @since 1.0.0
 */
function bbwc_add_main_menu_page_admin_menu() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    add_menu_page(
        __( 'BuddyBoss WC', 'buddyboss-wc' ),
        __( 'BuddyBoss WC', 'buddyboss-wc' ),
        'manage_options',
        'buddyboss-wc',
        null,
        '',
        4
    );
}

add_action( 'admin_menu', 'bbwc_add_main_menu_page_admin_menu' );