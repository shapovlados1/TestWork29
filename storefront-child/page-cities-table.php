<?php get_header(); ?>
<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
        <?php while ( have_posts() ) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <header class="entry-header">
                    <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
                </header>
                <div class="entry-content">
                    <input type="text" id="cities-search-input" placeholder="<?php esc_attr_e( 'Search cities...', 'storefront-child' ); ?>">
                    <?php do_action( 'before_cities_table' ); ?>
                    <table id="cities-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'Country', 'storefront-child' ); ?></th>
                                <th><?php esc_html_e( 'City', 'storefront-child' ); ?></th>
                                <th><?php esc_html_e( 'Temperature', 'storefront-child' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            global $wpdb;
                            $results = $wpdb->get_results(
                                "SELECT p.ID, p.post_title AS city, t.name AS country
                                 FROM {$wpdb->posts} p
                                 LEFT JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
                                 LEFT JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                                 LEFT JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
                                 WHERE p.post_type = 'cities' AND p.post_status = 'publish' AND tt.taxonomy = 'countries'
                                 ORDER BY t.name, p.post_title"
                            );
                            foreach ( $results as $row ) {
                                $lat = get_post_meta( $row->ID, '_city_latitude', true );
                                $lon = get_post_meta( $row->ID, '_city_longitude', true );
                                $temp = ( new City_Temperature_Widget() )->get_temperature( $lat, $lon );
                                echo '<tr><td>' . esc_html( $row->country ) . '</td><td>' . esc_html( $row->city ) . '</td><td>' . esc_html( $temp ) . '°C</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                    <?php do_action( 'after_cities_table' ); ?>
                </div>
            </article>
        <?php endwhile; ?>
    </main>
</div>
<?php
get_sidebar();
get_footer();