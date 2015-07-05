<?php
 /**
 * Adds WOW_Twitter Strean widget.
 */
class WOW_Twitter_Stream extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
	 		'WOW_Twitter_Stream', // Base ID
			'WOW Twitter Stream', // Name
			array( 'description' => __( 'Display Your Twitter Stream', 'text_domain' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
	    $number = absint($instance['number']);
	    $number = ( ! empty( $instance['number'] ) ) ? absint( $instance['number'] ) : 20;
		if ( ! empty( $number ))$number = 20;
		$twitter_widget = wow_twitter_parse_feed('wow_twitter_widget', false, true);
		echo $before_widget;
		if ( ! empty( $title ) )
			echo $before_title . $title . $after_title;
				echo $twitter_widget;
		echo $after_widget;
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['number'] = (int) $new_instance['number'];

		return $instance;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'New title', 'text_domain' );
		}
		$number  = isset( $instance['number'] ) ? absint( $instance['number'] ) : 20;

		if ($number == 0){$number = 20;}
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		   <label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of posts to show:' ); ?></label>
		   <input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>
		<p>
		<?php
	}

}
add_action('widgets_init', create_function('', 'register_widget( "WOW_Twitter_Stream" );'));
