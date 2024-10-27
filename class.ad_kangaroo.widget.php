<?php
class Ad_Kangaroo_Widget extends WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		$widget_ops = array( 
			'classname' => 'ad_kangaroo-widget',
			'description' => __( 'Ad Kangaroo widget, used to show adsense ads selected for widget', 'ad_kangaroo' ),
		);
		parent::__construct( 'ad_kangaroo_widget', __( 'Ad Kangaroo Widget', 'ad_kangaroo' ), $widget_ops );
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		// outputs the content of the widget
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		global $wp_ad_kangaroo;

		if ( isset( $wp_ad_kangaroo->ad_kangaroo_ads[$wp_ad_kangaroo->ad_kangaroo_options['publisher_id']]['widget'] ) ) {
			foreach ( $wp_ad_kangaroo->ad_kangaroo_ads[$wp_ad_kangaroo->ad_kangaroo_options['publisher_id']]['widget'] as $ad_unit_id => $ad_unit ) {
				$ad_code = htmlspecialchars_decode( $ad_unit['code'] );
				$ad_html = sprintf( '<div id="%s" class="ads ads_after">%s</div>', $ad_unit_id, $ad_code );
				echo $ad_html;
				$wp_ad_kangaroo->ads_count ++;

			}
		}

		echo $args['after_widget'];
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		// outputs the options form on admin
		$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Our sponsors', 'ad_kangaroo' );
		?>
		<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'ad_kangaroo' ); ?></label> 
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php 
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance ) {
		// processes widget options to be saved
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}
}