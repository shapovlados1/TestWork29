<?php

// Enqueue parent and child styles.
function storefront_child_enqueue_styles() {
    wp_enqueue_style( 'storefront-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'storefront-child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array( 'storefront-style' ),
        wp_get_theme()->get('Version')
    );
}
add_action( 'wp_enqueue_scripts', 'storefront_child_enqueue_styles' );

require_once get_stylesheet_directory() . '/inc/cpt-cities-countries.php';
require_once get_stylesheet_directory() . '/inc/widget-city-temperature.php';
require_once get_stylesheet_directory() . '/inc/ajax-search-cities.php';