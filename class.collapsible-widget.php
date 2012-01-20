<?php
/**
 * Define the class for the actual Collapsible widget
 */
class collapsible_widget extends WP_Widget {
	var $tabbed_accordion = null;
	/**
	 * Construct our widget item
	 */
	function __construct() {
		$widget_ops = array( 
			'classname'   => 'collapsible-widget', 
			'description' => 'Display multiple widgets in a collapsible (accordion or tabbed) interface.' 
		);
		$control_ops = array( 
			'id_base' => 'collapsible-widget' 
		);
		parent::WP_Widget( 'collapsible-widget', __( 'Collapsible Widget', 'collapsible-widget-area' ), $widget_ops, $control_ops );
		
		global $collapsible_widget_area;
		$options = $collapsible_widget_area->_get_options();
		if ( ! array_key_exists( 'uitheme', $options ) || empty( $options['uitheme'] ) )
			$options['uitheme'] = 'base';
		if ( 'none' == $options['uitheme'] ) {
			$theme = null;
		} else if ( ! stristr( '//', $options['uitheme'] ) ) {
			$theme = 'http://ajax.googleapis.com/ajax/libs/jqueryui/1/themes/' . $options['uitheme'] . '/jquery-ui.css';
		} else {
			$theme = $options['uitheme'];
		}
		$theme = apply_filters( 'collapsible-widget-ui-theme', $theme, $options['uitheme'] );
		wp_register_script( 'collapsible-widgets', plugins_url( 'scripts/collapsible-widgets.js', __FILE__ ), array(), '0.2a', true );
		if ( ! empty( $theme ) )
			wp_register_style( 'jquery-ui', $theme, array(), '1.8.17', 'screen' );
		wp_register_style( 'collapsible-widgets', plugins_url( 'css/collapsible-widgets.css', __FILE__ ), array( 'jquery-ui' ), '0.2a', true );
		
		if ( is_active_sidebar( $collapsible_widget_area->sidebar_id ) )
			wp_enqueue_style( 'collapsible-widgets' );
	}
	
	function form( $instance ) {
		global $wp_registered_widgets, $collapsible_widget_area;
		$widgets = wp_get_sidebars_widgets();
		if ( ! array_key_exists( $collapsible_widget_area->sidebar_id, $widgets ) ) {
			_e( 'The collapsible widget area appears to be empty, or does not exist. Please drag some widgets into that area in order to have them available in this widget.', 'collapsible-widget-area' );
			return;
		}
		/*$this->widgets_list();*/
?>
	<p><?php _e( 'Display widgets in which manner?' ) ?><br/>
		<input type="radio" name="<?php echo $this->get_field_name( 'show_what' ) ?>" id="<?php echo $this->get_field_id( 'show_what_tabbed' ) ?>" value="tabbed"<?php checked( $instance['show_what'], 'tabbed' ) ?>/> <label for="<?php echo $this->get_field_id( 'show_what_tabbed' ) ?>"><?php _e( 'Tabs' ) ?></label><br/>
		<input type="radio" name="<?php echo $this->get_field_name( 'show_what' ) ?>" id="<?php echo $this->get_field_id( 'show_what_accordion' ) ?>" value="accordion"<?php checked( $instance['show_what'], 'accordion' ) ?>/> <label for="<?php echo $this->get_field_id( 'show_what_accordion' ) ?>"><?php _e( 'Accordion' ) ?></label></p>
<?php
	}
	
	function widgets_list() {
		
		print( '<ul class="collapsible-widget-options">' );
		
		$widgets = $widgets[$collapsible_widget_area->sidebar_id];
		foreach ( $widgets as $wid ) {
			if ( ! array_key_exists( $wid, $wp_registered_widgets ) )
				continue;
			$widget_info = $wp_registered_widgets[$wid];
			if ( 'collapsible-widget' == $widget_info['classname'] )
				continue;
			
			/*print( "\n<!--\n" );
			var_dump( $widget_info );
			print( "\n-->\n" );*/
?>
	<li class="collapsible-widget-option">
		<input type="checkbox" name="<?php echo $this->get_field_name( 'on' ) . '[' . $wid . ']' ?>" id="<?php echo $this->get_field_id( 'on_' . $wid ) ?>" value="<?php echo $wid ?>"<?php checked( array_key_exists( $wid, $instance['widgets'] ) ) ?>/> 
		<label for="<?php echo $this->get_field_id( 'on_' . $wid ) ?>"><strong><?php echo $widget_info['name'] ?></strong> <em>(<?php echo $widget_info['id'] ?>)</em></label>
		<br/>
		<label for="<?php echo $this->get_field_id( 'order_' . $wid ) ?>"><?php _e( 'Order: ' ) ?></label>
		<input type="text" name="<?php echo $this->get_field_name( 'order' ) . '[' . $wid . ']' ?>" id="<?php echo $this->get_field_id( 'order_' . $wid ) ?>" value="<?php echo array_key_exists( $wid, $instance['widgets'] ) && ! empty( $instance['widgets'][$wid]['order'] ) ? $instance['widgets'][$wid]['order'] : '' ?>"/>
	</li>
<?php
		}
		
		print( '</ul>' );
		
	}
	
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['widgets'] = array();
		if ( array_key_exists( 'on', $new_instance ) ) {
			foreach ( $new_instance['on'] as $widget ) {
				$instance['widgets'][$widget] = array(
					'id'    => $widget,
					'order' => empty( $new_instance['order'][$widget] ) ? 0 : (int)$new_instance['order'][$widget],
				);
			}
		}
		$instance['show_what'] = array_key_exists( 'show_what', $new_instance ) && 'accordion' == $new_instance['show_what'] ? 'accordion' : 'tabbed';
		return $instance;
	}
	
	function widget( $args, $instance ) {
		extract( $args );
		global $collapsible_widget_area;
		if ( ! is_active_sidebar( $collapsible_widget_area->sidebar_id ) )
			return;
		
		add_action( 'wp_print_footer_scripts', array( $this, 'print_footer_scripts' ), 1 );
		$this->tabbed_accordion = $instance['show_what'];
		echo $before_widget;
		echo '<div class="collapsible-widget-container">';
		dynamic_sidebar( $collapsible_widget_area->sidebar_id );
		echo '</div>';
		echo $after_widget;
	}
	
	function print_footer_scripts() {
		if ( 'accordion' === $this->tabbed_accordion ) {
			echo '<script type="text/javascript">var collapsible_widget_area = { "type" : "accordion" };</script>';
			wp_enqueue_script( 'jquery-ui-accordion' );
		} else {
			echo '<script type="text/javascript">var collapsible_widget_area = { "type" : "tabbed" };</script>';
			wp_enqueue_script( 'jquery-ui-tabs' );
		}
		
		wp_enqueue_script( 'collapsible-widgets' );
		return;
	}
}
?>