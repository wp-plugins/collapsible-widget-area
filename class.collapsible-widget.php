<?php
/**
 * Define the class for the actual Collapsible widget
 */
class collapsible_widget extends WP_Widget {
	var $tabbed_accordion = null;
	var $collapsible = false;
	var $closed = false;
	var $cookie = false;
	/**
	 * Construct our widget item
	 */
	function __construct() {
		/**
		 * Attempt to determine which version of jQueryUI is being used
		 * v3.5 uses jQueryUI v1.9.x
		 * v3.1-3.4.2 uses jQueryUI 1.8.x
		 * v2.8-3.0 uses jQueryUI 1.7.x
		 */
		global $wp_version;
		if ( version_compare( $wp_version, '3.1', '<' ) ) {
			$uivers = 1.7;
		} elseif ( version_compare( $wp_version, '3.5', '<' ) ) {
			$uivers = 1.8;
		} else {
			$uivers = 1.9;
		}
		
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
			$theme = sprintf( 'http://ajax.googleapis.com/ajax/libs/jqueryui/%1$s/themes/%2$s/jquery-ui.css', $uivers, $options['uitheme'] );
		} else {
			$theme = $options['uitheme'];
		}
		$theme = apply_filters( 'collapsible-widget-ui-theme', $theme, $options['uitheme'] );
		if ( ! empty( $theme ) )
			wp_register_style( 'jquery-ui', $theme, array(), '1.8.17', 'screen' );
		
		wp_register_style( 'collapsible-widgets', plugins_url( 'css/collapsible-widgets.css', __FILE__ ), array( 'jquery-ui' ), '0.2a', true );
		
		wp_register_script( 'collapsible-widgets', plugins_url( 'scripts/collapsible-widgets.js', __FILE__ ), array(), '0.2.3a', true );
		/*wp_register_script( 'jquery-cookie', plugins_url( 'scripts/jquery.cookie.js', __FILE__ ), array( 'jquery-ui' ), '1.0', true );*/
		if ( version_compare( $GLOBALS['wp_version'], '3.3', '<' ) ) {
			/*print( "\n<!-- This is a version lower than 3.3 -->\n" );*/
			/*wp_register_script( 'jquery-ui', includes_url( 'js/jquery/ui.core.js' ), array( 'jquery' ), '1.8.12', true );*/
			/*wp_register_script( 'jquery-ui-accordion', plugins_url( 'scripts/jquery.ui.accordion.min.js', __FILE__ ), array( 'jquery-ui', 'jquery-ui-widget' ), '1.8.16', true );*/
			/*wp_register_script( 'jquery-ui-tabs', includes_url( 'js/jquery/ui.tabs.js' ), array( 'jquery-ui' ), '1.8.16', true );*/
			/*wp_enqueue_script( 'jquery-ui-accordion' );*/
			wp_enqueue_script( 'jquery-ui-tabs' );
			wp_enqueue_script( 'collapsible-widgets' );
			
			wp_enqueue_style( 'collapsible-widgets' );
		} else {
			if ( is_active_sidebar( $collapsible_widget_area->sidebar_id ) )
				wp_enqueue_style( 'collapsible-widgets' );
		}
	}
	
	function defaults() {
		return apply_filters( 'collapsible-widget-defaults', array( 
			'type'        => 'tabbed', 
			'collapsible' => false, 
			'closed'      => false, 
			'cookie'      => false, 
		) );
	}
	
	function form( $instance ) {
		$instance = wp_parse_args( $instance, $this->defaults() );
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
	<p><input type="checkbox" name="<?php echo $this->get_field_name( 'collapsible' ) ?>" id="<?php echo $this->get_field_id( 'collapsible' ) ?>" value="1"<?php checked( $instance['collapsible'] ) ?>/> <label for="<?php echo $this->get_field_id( 'collapsible' ) ?>"><?php _e( 'Allow entire accordion to be closed (if applicable)?' ) ?></label></p>
	<p><input type="checkbox" name="<?php echo $this->get_field_name( 'closed' ) ?>" id="<?php echo $this->get_field_id( 'closed' ) ?>" value="1"<?php checked( $instance['closed'] ) ?>/> <label for="<?php echo $this->get_field_id( 'closed' ) ?>"><?php _e( 'Start with the entire accordion collapsed (only applicable if the above option is checked)' ) ?></label></p>
	<p><input type="checkbox" name="<?php echo $this->get_field_name( 'cookie' ) ?>" id="<?php echo $this->get_field_id( 'cookie' ) ?>" value="1"<?php checked( $instance['cookie'] ) ?>/> <label for="<?php echo $this->get_field_id( 'cookie' ) ?>"><?php _e( 'Persist active tab across page views? (Currently only applicable to tabbed interface)' ) ?></label></p>
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
					'order' => empty( $new_instance['order'][$widget] ) ? 0 : (int) $new_instance['order'][$widget],
				);
			}
		}
		$instance['show_what'] = array_key_exists( 'show_what', $new_instance ) && 'accordion' == $new_instance['show_what'] ? 'accordion' : 'tabbed';
		$instance['collapsible'] = in_array( $new_instance['collapsible'], array( '1', 1, true ) );
		$instance['closed'] = in_array( $new_instance['closed'], array( '1', 1, true ) );
		$instance['cookie'] = in_array( $new_instance['cookie'], array( '1', 1, true ) );
		return $instance;
	}
	
	function widget( $args, $instance ) {
		extract( $args );
		$instance = wp_parse_args( $instance, $this->defaults() );
		global $collapsible_widget_area;
		if ( ! is_active_sidebar( $collapsible_widget_area->sidebar_id ) )
			return;
		
		add_action( 'wp_print_footer_scripts', array( $this, 'print_footer_scripts' ), 1 );
		$this->tabbed_accordion = $instance['show_what'];
		$this->collapsible = $instance['collapsible'];
		$this->closed = $instance['closed'];
		$this->cookie = $instance['cookie'];
		echo $before_widget;
		echo '<div class="collapsible-widget-container">';
		dynamic_sidebar( $collapsible_widget_area->sidebar_id );
		echo '</div>';
		echo $after_widget;
	}
	
	function print_footer_scripts() {
		$c = array();
		$c['type'] = 'accordion' === $this->tabbed_accordion ? 'accordion' : 'tabbed';
		$c['collapsible'] = $this->collapsible;
		$c['closed'] = $this->closed;
		$c['cookie'] = $this->cookie;
		echo '<script type="text/javascript">var collapsible_widget_area = ' . json_encode( $c ) . ';</script>';
		if ( 'accordion' === $this->tabbed_accordion ) {
			echo '<script type="text/javascript">var collapsible_widget_area = { "type" : "accordion" };</script>';
			wp_enqueue_script( 'jquery-ui-accordion' );
		} else {
			wp_enqueue_script( 'jquery-cookie' );
			wp_enqueue_script( 'jquery-ui-tabs' );
		}
		
		wp_enqueue_script( 'collapsible-widgets' );
		return;
	}
}
?>