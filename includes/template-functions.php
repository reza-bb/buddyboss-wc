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

function bbwc_booths_post_type() {
    return 'bbwc-booths';
}

function bbwc_booths_category() {
    return 'bbwc-booths-category';
}

function bbwc_workshops_post_type() {
    return 'bbwc-workshops';
}

function bbwc_workshops_category() {
    return 'bbwc-workshops-category';
}

function bbwc_workshops_location() {
    return 'bbwc-workshops-location';
}
