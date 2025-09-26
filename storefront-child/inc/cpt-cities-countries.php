<?php
/**
 * Cities CPT, Countries taxonomy & Coordinates Metabox
 *
 * @package StorefrontChild
 */

/**
 * Register Cities Custom Post Type.
 */
function register_cpt_cities() {
    $labels = array(
        'name'          => __( 'Cities', 'storefront-child' ),
        'singular_name' => __( 'City', 'storefront-child' ),
        'menu_name'     => __( 'Cities', 'storefront-child' ),
        'add_new'       => __( 'Add City', 'storefront-child' ),
        'add_new_item'  => __( 'Add New City', 'storefront-child' ),
        'edit_item'     => __( 'Edit City', 'storefront-child' ),
        'new_item'      => __( 'New City', 'storefront-child' ),
        'view_item'     => __( 'View City', 'storefront-child' ),
        'search_items'  => __( 'Search Cities', 'storefront-child' ),
        'not_found'     => __( 'No cities found', 'storefront-child' ),
    );

    $args = array(
        'labels'        => $labels,
        'public'        => true,
        'has_archive'   => true,
        'menu_position' => 5,
        'supports'      => array( 'title', 'editor', 'thumbnail' ),
        'show_in_rest'  => true,
    );

    register_post_type( 'cities', $args );
}
add_action( 'init', 'register_cpt_cities' );

/**
 * Register Countries Taxonomy for Cities.
 */
function register_taxonomy_countries() {
    $labels = array(
        'name'          => __( 'Countries', 'storefront-child' ),
        'singular_name' => __( 'Country', 'storefront-child' ),
        'search_items'  => __( 'Search Countries', 'storefront-child' ),
        'all_items'     => __( 'All Countries', 'storefront-child' ),
        'edit_item'     => __( 'Edit Country', 'storefront-child' ),
        'update_item'   => __( 'Update Country', 'storefront-child' ),
        'add_new_item'  => __( 'Add New Country', 'storefront-child' ),
        'menu_name'     => __( 'Countries', 'storefront-child' ),
    );

    $args = array(
        'labels'            => $labels,
        'hierarchical'      => true,
        'public'            => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
    );

    register_taxonomy( 'countries', array( 'cities' ), $args );
}
add_action( 'init', 'register_taxonomy_countries' );

/**
 * Add Coordinates Metabox to Cities CPT.
 */
function cities_add_coordinates_metabox() {
    add_meta_box(
        'cities_coordinates',
        __( 'City Coordinates', 'storefront-child' ),
        'cities_coordinates_metabox_html',
        'cities',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'cities_add_coordinates_metabox' );

/**
 * Metabox HTML.
 */
function cities_coordinates_metabox_html( $post ) {
    wp_nonce_field( 'cities_coordinates_action', 'cities_coordinates_nonce' );

    $latitude  = get_post_meta( $post->ID, '_city_latitude', true );
    $longitude = get_post_meta( $post->ID, '_city_longitude', true );
    ?>
    <p>
        <label for="city_latitude"><?php esc_html_e( 'Latitude:', 'storefront-child' ); ?></label>
        <input type="text" id="city_latitude" name="city_latitude" value="<?php echo esc_attr( $latitude ); ?>" class="widefat" />
    </p>
    <p>
        <label for="city_longitude"><?php esc_html_e( 'Longitude:', 'storefront-child' ); ?></label>
        <input type="text" id="city_longitude" name="city_longitude" value="<?php echo esc_attr( $longitude ); ?>" class="widefat" />
    </p>
    <?php
}

/**
 * Save Coordinates Metabox data.
 */
function cities_save_coordinates( $post_id ) {
    if ( ! isset( $_POST['cities_coordinates_nonce'] ) ||
        ! wp_verify_nonce( $_POST['cities_coordinates_nonce'], 'cities_coordinates_action' ) ) {
        return;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    $fields = [ 'city_latitude' => '_city_latitude', 'city_longitude' => '_city_longitude' ];

    foreach ( $fields as $form_field => $meta_key ) {
        if ( isset( $_POST[ $form_field ] ) ) {
            update_post_meta( $post_id, $meta_key, sanitize_text_field( $_POST[ $form_field ] ) );
        }
    }
}
add_action( 'save_post_cities', 'cities_save_coordinates' );
