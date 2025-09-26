<?php

/**
 * City Temperature Widget Class.
 */
class City_Temperature_Widget extends WP_Widget {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct(
            'city_temperature_widget',
            __( 'City Temperature', 'storefront-child' ),
            array( 'description' => __( 'Displays city name and current temperature.', 'storefront-child' ) )
        );
    }

    /**
     * Front-end display.
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget( $args, $instance ) {
        echo $args['before_widget'];
        if ( ! empty( $instance['title'] ) ) {
            echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
        }
        $city_id = ! empty( $instance['city_id'] ) ? $instance['city_id'] : '';
        if ( $city_id ) {
            $city_name = get_the_title( $city_id );
            $latitude = get_post_meta( $city_id, '_city_latitude', true );
            $longitude = get_post_meta( $city_id, '_city_longitude', true );
            $temperature = $this->get_temperature( $latitude, $longitude );
            echo '<p>' . esc_html( $city_name ) . ': ' . esc_html( $temperature ) . '°C</p>';
        }
        echo $args['after_widget'];
    }

    /**
     * Back-end widget form.
     *
     * @param array $instance Previously saved values from database.
     */
    public function form( $instance ) {
        $title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'City Temperature', 'storefront-child' );
        $city_id = ! empty( $instance['city_id'] ) ? $instance['city_id'] : '';
        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'storefront-child' ); ?></label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'city_id' ) ); ?>"><?php esc_attr_e( 'Select City:', 'storefront-child' ); ?></label>
            <select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'city_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'city_id' ) ); ?>">
                <option value=""><?php esc_html_e( 'Select a city', 'storefront-child' ); ?></option>
                <?php
                $cities = get_posts( array( 'post_type' => 'cities', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );
                foreach ( $cities as $city ) {
                    echo '<option value="' . esc_attr( $city->ID ) . '" ' . selected( $city_id, $city->ID, false ) . '>' . esc_html( $city->post_title ) . '</option>';
                }
                ?>
            </select>
        </p>
        <?php
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     * @return array Updated safe values to be saved.
     */
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
        $instance['city_id'] = ( ! empty( $new_instance['city_id'] ) ) ? absint( $new_instance['city_id'] ) : '';
        return $instance;
    }

    /**
     * Get temperature from OpenWeatherMap API.
     *
     * @param string $lat Latitude.
     * @param string $lon Longitude.
     * @return string Temperature or error message.
     */
    public function get_temperature( $lat, $lon ) {
        if ( empty( $lat ) || empty( $lon ) ) {
            return __( 'Coordinates missing', 'storefront-child' );
        }
        // uncomment if need cache
        //$cache_key = 'city_temp_' . md5( $lat . $lon );
        //$temperature = get_transient( $cache_key );
        //if ( false === $temperature ) {
            $api_key = 'b109ffc256714ad8809171929252609';
            $url = "http://api.weatherapi.com/v1/current.json?key={$api_key}&q={$lat},{$lon}";
            $response = wp_remote_get($url);
            if (is_wp_error($response)) {
                return __('API Error', 'storefront-child');
            }
            $data = json_decode(wp_remote_retrieve_body($response));
            $temperature = isset($data->current->temp_c) ? $data->current->temp_c : __('N/A', 'storefront-child');
            //set_transient( $cache_key, $temperature, 600 );
        //}
        return $temperature;

        }
}

// Register the widget.
function register_city_temperature_widget() {
    register_widget( 'City_Temperature_Widget' );
}
add_action( 'widgets_init', 'register_city_temperature_widget' );