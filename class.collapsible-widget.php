<?php
/**
 * Define the class for the actual Collapsible widget
 * @version 0.5
 */
class collapsible_widget extends WP_Widget {
	/**
	 * @var array $instance
	 * Set up a container for the JSON info that needs to be passed
	 */
	var $instance = array();
	
	/**
	 * Construct our widget item
	 */
	function __construct() {
		$this->version = '0.5.1';
		
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		
		$widget_ops = array( 
			'classname'   => 'collapsible-widget', 
			'description' => 'Display multiple widgets in a collapsible (accordion or tabbed) interface.' 
		);
		$control_ops = array( 
			'id_base' => 'collapsible-widget', 
			'class'   => 'collapsible-widget', 
		);
		parent::__construct( 'collapsible-widget', __( 'Collapsible Widget', 'collapsible-widget-area' ), $widget_ops, $control_ops );
	}
	
	/**
	 * Determine which scripts & styles are necessary and register/enqueue them
	 */
	function enqueue_scripts() {
		/**
		 * Attempt to determine which version of jQueryUI is being used
		 * v4.2 uses jQueryUI v1.11.4
		 * v4.1 uses jQueryUI v1.11.2
		 * v4.0 uses jQueryUI v1.10.4
		 * v3.5 uses jQueryUI v1.9.x
		 * v3.1-3.4.2 uses jQueryUI 1.8.x
		 * v2.8-3.0 uses jQueryUI 1.7.x
		 */
		global $wp_version;
		if ( version_compare( $wp_version, '4.1', '<' ) ) {
			$uivers = '1.10.4';
		} else if ( version_compare( $wp_version, '4.2', '<' ) ) {
			$uivers = '1.11.2';
		} else {
			$uivers = '1.11.4';
		}
		
		global $collapsible_widget_area;
		$options = $collapsible_widget_area->_get_options();
		if ( ! array_key_exists( 'uitheme', $options ) || empty( $options['uitheme'] ) )
			$options['uitheme'] = 'smoothness';
		if ( 'none' == $options['uitheme'] ) {
			$theme = null;
		} else if ( ! stristr( '//', $options['uitheme'] ) ) {
			$theme = sprintf( 'http://ajax.googleapis.com/ajax/libs/jqueryui/%1$s/themes/%2$s/jquery-ui.css', $uivers, $options['uitheme'] );
		} else {
			$theme = $options['uitheme'];
		}
		
		$theme = apply_filters( 'collapsible-widget-ui-theme', $theme, $options['uitheme'] );
		if ( ! empty( $theme ) )
			wp_register_style( 'jquery-ui', $theme, array(), $uivers, 'screen' );
		
		wp_register_style( 'collapsible-widgets', plugins_url( 'css/collapsible-widgets.css', __FILE__ ), array( 'jquery-ui' ), $this->version, true );
		
		wp_register_script( 'jquery-cookie', plugins_url( 'scripts/jquery.cookie.js', __FILE__ ), array( 'jquery-ui-tabs' ), '1.0', true );
		wp_register_script( 'collapsible-widgets', plugins_url( 'scripts/collapsible-widgets.js', __FILE__ ), array( 'jquery-cookie', 'jquery-ui-accordion' ), $this->version, true );
		
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
			/*print( "\n<!-- Number of sidebars: {$options['sidebars']} -->\n" );
			print( "\n<!-- Sidebar IDs:\n" );
			var_dump( $collapsible_widget_area->sidebar_id );
			print( "\n-->\n" );*/
			for ( $i = 1; $i <= $options['sidebars']; $i++ ) {
				/*print( "\n<!-- Checking sidebar {$i} to see if it is active -->\n" );*/
				if ( is_active_sidebar( $collapsible_widget_area->sidebar_id[  's-' . $i ] ) )
					wp_enqueue_style( 'collapsible-widgets' );
			}
		}
	}
	
	function defaults() {
		return apply_filters( 'collapsible-widget-defaults', array( 
			'invalid-widget' => false, 
			'sidebar_id'  => 1, 
			'title'       => sprintf( 'Area %d', 1 ), 
			'type'        => 'tabbed', 
			'collapsible' => false, 
			'closed'      => false, 
			'cookie'      => false, 
		) );
	}
	
	function form( $instance ) {
		wp_enqueue_script( 'collapsible-widgets-admin' );
		
		$instance = wp_parse_args( $instance, $this->defaults() );
		$instance['title'] = sprintf( 'Area %d', (int) $instance['sidebar_id'] );
		
		if ( $instance['invalid-widget'] ) {
?>
<?php _e( '<p>You attempted to set up a collapsible widget inside of a collapsible widget area. This could cause an infinite recursion resulting in a tear in the space-time continuum.</p> <p><strong>Please remove this widget from this sidebar</strong> in order to avoid destroying the entire universe. Thank you.</p>' ) ?>
			<div class="hidden"><input type="text" name="<?php echo $this->get_field_name( 'title' ) ?>" id="<?php echo $this->get_field_id( 'title' ) ?>" value="<?php _e( 'Error - Attention Required' ) ?>" readonly /></div>
<?php
			return;
		}
		
		global $wp_registered_widgets, $collapsible_widget_area;
		$widgets = wp_get_sidebars_widgets();
		if ( empty( $collapsible_widget_area->sidebar_id ) ) {
			_e( 'There do not appear to be any collapsible widget sidebars configured. Please update the settings for this plugin to set up at least one collapsible widget area.', 'collapsible-widget-area' );
			return;
		}
		/*$this->widgets_list();*/
?>
	<p><label for="<?php echo $this->get_field_id( 'title' ) ?>"><?php _e( 'Title:' ) ?></label><br />
    	<input type="text" name="<?php echo $this->get_field_name( 'title' ) ?>" id="<?php echo $this->get_field_id( 'title' ) ?>" value="<?php echo $instance['title'] ?>" readonly /><br />
        <em><?php _e( 'The title is used only to differentiate between instances, and is automatically generated based on the collapsible widget area you choose below. It does not show up on the front-end anywhere.' ) ?></em></p>
    <hr />
	<p><label for="<?php echo $this->get_field_id( 'sidebar_id' ) ?>"><?php _e( 'Which collapsible widget area should be used?' ) ?></label><br/>
    	<select name="<?php echo $this->get_field_name( 'sidebar_id' ) ?>" id="<?php echo $this->get_field_id( 'sidebar_id' ) ?>">
<?php
		for( $i=1; $i <= $collapsible_widget_area->options['sidebars']; $i++ ) {
?>
			<option value="<?php echo (int) $i ?>"<?php selected( (int) $instance['sidebar_id'], $i ) ?>><?php echo sprintf( '%s %d', $collapsible_widget_area->args['name'], $i ) ?></option>
<?php
		}
?>
        </select></p>
	<p><?php _e( 'Display widgets in which manner?' ) ?><br/>
		<input type="radio" name="<?php echo $this->get_field_name( 'show_what' ) ?>" id="<?php echo $this->get_field_id( 'show_what_tabbed' ) ?>" value="tabbed"<?php checked( $instance['show_what'], 'tabbed' ) ?>/> <label for="<?php echo $this->get_field_id( 'show_what_tabbed' ) ?>"><?php _e( 'Tabs' ) ?></label><br/>
		<input type="radio" name="<?php echo $this->get_field_name( 'show_what' ) ?>" id="<?php echo $this->get_field_id( 'show_what_accordion' ) ?>" value="accordion"<?php checked( $instance['show_what'], 'accordion' ) ?>/> <label for="<?php echo $this->get_field_id( 'show_what_accordion' ) ?>"><?php _e( 'Accordion' ) ?></label></p>
	<p><input type="checkbox" name="<?php echo $this->get_field_name( 'collapsible' ) ?>" id="<?php echo $this->get_field_id( 'collapsible' ) ?>" value="1"<?php checked( $instance['collapsible'] ) ?>/> <label for="<?php echo $this->get_field_id( 'collapsible' ) ?>"><?php _e( 'Allow entire accordion to be closed (if applicable)?' ) ?></label></p>
	<p><input type="checkbox" name="<?php echo $this->get_field_name( 'closed' ) ?>" id="<?php echo $this->get_field_id( 'closed' ) ?>" value="1"<?php checked( $instance['closed'] ) ?>/> <label for="<?php echo $this->get_field_id( 'closed' ) ?>"><?php _e( 'Start with the entire accordion collapsed (only applicable if the above option is checked)' ) ?></label></p>
	<p><input type="checkbox" name="<?php echo $this->get_field_name( 'cookie' ) ?>" id="<?php echo $this->get_field_id( 'cookie' ) ?>" value="1"<?php checked( $instance['cookie'] ) ?>/> <label for="<?php echo $this->get_field_id( 'cookie' ) ?>"><?php _e( 'Persist active tab across page views? (Currently only applicable to tabbed interface)' ) ?></label></p>
<?php
	}
	
	function widgets_list() {
		
		echo '<ul class="collapsible-widget-options">';
		
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
		
		echo '</ul>';
		
	}
	
	function update( $new_instance, $old_instance ) {
		global $collapsible_widget_area;
		$collapsible_widget_area->get_args();
		if ( array_key_exists( 'sidebar', $_POST ) && stristr( $_POST['sidebar'], $collapsible_widget_area->args['id'] ) ) {
			add_action( 'admin_notice', array( $this, 'recursion_warning' ) );
			return array( 'invalid-widget' => true );
		}
		
		$instance = $old_instance;
		$instance['widgets'] = array();
		$instance['sidebar_id'] = array_key_exists( 'sidebar_id', $new_instance ) && is_numeric( $new_instance['sidebar_id'] ) ? (int) $new_instance['sidebar_id'] : 1;
		$instance['title'] = $instance['sidebar_id'];
		
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
		if ( stristr( $args['id'], 'collapsible-widget-area' ) )
			return;
		
		if ( ! array_key_exists( 'sidebar_id', $instance ) || ! is_numeric( $instance['sidebar_id'] ) )
			$instance['sidebar_id'] = 1;
		
		extract( $args );
		$instance = wp_parse_args( $instance, $this->defaults() );
		global $collapsible_widget_area;
		if ( ! is_active_sidebar( $collapsible_widget_area->sidebar_id[ 's-' . $instance['sidebar_id'] ] ) )
			return;
		
		add_action( 'wp_print_footer_scripts', array( $this, 'print_footer_scripts' ), 1 );
		$id = 'collapsible-widget-container-' . uniqid();
		$this->instance[] = array(
			'id'          => $id, 
			'type'        => 'accordion' == $instance['show_what'] ? 'accordion' : 'tabbed', 
			'collapsible' => $this->is_true( $instance['collapsible'] ), 
			'closed'      => $this->is_true( $instance['closed'] ), 
			'cookie'      => $this->is_true( $instance['cookie'] ), 
		);
		echo $before_widget;
		echo '<div class="collapsible-widget-container" id="' . $id . '">';
		dynamic_sidebar( $collapsible_widget_area->sidebar_id[ 's-' . $instance['sidebar_id'] ] );
		echo '</div>';
		echo $after_widget;
	}
	
	function print_footer_scripts() {
		global $cwa_printed_footer_scripts;
		if ( isset( $cwa_printed_footer_scripts ) && $cwa_printed_footer_scripts )
			return;
		
		$this->instance = apply_filters( 'collapsible-widget-javascript-arguments', $this->instance );
		
		echo '
<!-- Collapsible Widget Area Options -->
<script type="text/javascript">var collapsible_widget_area = ' . json_encode( $this->instance ) . ';</script>
<!-- / Collapsible Widget Area Options -->';
		/*wp_enqueue_script( 'jquery-ui-accordion' );
		wp_enqueue_script( 'jquery-cookie' );
		wp_enqueue_script( 'jquery-ui-tabs' );*/
		
		wp_enqueue_script( 'collapsible-widgets' );
		
		$cwa_printed_footer_scripts = true;
		return;
	}
	
	function is_true( &$val ) {
		$val = ! in_array( $val, array( 'false', false, 0, '0' ), true );
		return $val;
	}
}
?>