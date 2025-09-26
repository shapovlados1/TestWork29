<?php

/**
 * Enqueue script for AJAX search.
 */
function enqueue_ajax_search_script() {
    if ( is_page_template( 'page-cities-table.php' ) ) {
        wp_enqueue_script( 'cities-ajax-search', get_stylesheet_directory_uri() . '/js/ajax-search.js', array( 'jquery' ), '1.0', true );
        wp_localize_script( 'cities-ajax-search', 'cities_ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'cities_search_nonce' ),
        ) );
    }
}
add_action( 'wp_enqueue_scripts', 'enqueue_ajax_search_script' );

/**
 * AJAX handler for searching cities.
 */
function ajax_search_cities() {
    check_ajax_referer( 'cities_search_nonce', 'nonce' );
    $search = isset( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '';
    global $wpdb;
    $results = $wpdb->get_results( $wpdb->prepare(
        "SELECT p.ID, p.post_title AS city, t.name AS country
         FROM {$wpdb->posts} p
         LEFT JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
         LEFT JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
         LEFT JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
         WHERE p.post_type = 'cities' AND p.post_status = 'publish' AND tt.taxonomy = 'countries'
         AND p.post_title LIKE %s",
        '%' . $wpdb->esc_like( $search ) . '%'
    ) );

    $html = '';
    foreach ( $results as $row ) {
        $lat = get_post_meta( $row->ID, '_city_latitude', true );
        $lon = get_post_meta( $row->ID, '_city_longitude', true );
        $temp = ( new City_Temperature_Widget() )->get_temperature( $lat, $lon );
        $html .= '<tr><td>' . esc_html( $row->country ) . '</td><td>' . esc_html( $row->city ) . '</td><td>' . esc_html( $temp ) . '°C</td></tr>';
    }
    wp_send_json_success( array( 'html' => $html ) );
}
add_action( 'wp_ajax_search_cities', 'ajax_search_cities' );
add_action( 'wp_ajax_nopriv_search_cities', 'ajax_search_cities' );