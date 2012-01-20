<?php
/**
 * Define the collapsible_widget_area class
 */
class collapsible_widget_area {
	var $args = array();
	var $sidebar_id = null;
	var $is_multinetwork = false;
	var $_is_primary_network = false;
	var $_is_mn_settings_page = false;
	var $_is_network_settings_page = false;
	var $settings_page = 'collapsible-widgets';
	var $options = array();
	var $defaults = array( 'uitheme' => '' );
	var $plugin_file = null;
	var $msg = false;
	
	/**
	 * Construct our collapsible_widget_area object
	 * @uses collapsible_widget_area::get_args()
	 * @uses register_sidebar()
	 * @uses is_multisite()
	 * @uses is_plugin_active_for_network()
	 * @uses is_network_admin()
	 * @uses collapsible_widget_area::$is_multinetwork
	 * @uses $GLOBALS['current_site']
	 * @uses collapsible_widget_area::$_is_primary_network
	 * @uses collapsible_widget_area::$settings_page
	 * @uses collapsible_widget_area::$_is_mn_settings_page
	 * @uses add_action() to call the appropriate functions to set up the admin pages
	 * @uses collapsible_widget_area::add_settings_fields() to add the appropriate settings fields
	 */
	function __construct() {
		$this->plugin_file = trailingslashit( basename( dirname( __FILE__ ) ) ) . str_replace( 'class.', '', basename( __FILE__ ) );
		$this->get_args();
		add_action( 'widgets_init', array( $this, 'register_sidebar' ) );
		
		if ( is_multisite() && is_plugin_active_for_network( $this->plugin_file ) )
			add_action( 'network_admin_menu', array( $this, 'add_network_options_page' ) );
		
		$this->_is_multinetwork();
		if( is_network_admin() && $this->is_multinetwork && 1 == $GLOBALS['current_site']->id ) {
			$this->_is_primary_network = true;
			if( 'sites.php?page=' . $this->settings_page === basename( $_SERVER['REQUEST_URI'] ) ) {
				$this->_is_mn_settings_page = true;
			}
		} else if ( is_network_admin() && is_plugin_active_for_network( $this->plugin_file ) ) {
			if( 'settings.php?page=' . $this->settings_page === basename( $_SERVER['REQUEST_URI'] ) ) {
				$this->_is_network_settings_page = true;
			}
		}
		
		if ( $this->_is_mn_settings_page ) {
			if ( isset( $_POST['collapsible-widget-options'] ) ) {
				$this->msg = $this->save_mn_settings( $_POST['collapsible-widget-options'] );
			}
		} else if ( $this->_is_network_settings_page ) {
			if ( isset( $_POST['collapsible-widget-options'] ) ) {
				$this->msg = $this->save_network_settings( $_POST['collapsible-widget-options'] );
			}
		}
		
		add_action( 'admin_menu', array( $this, 'add_options_page' ) );
	}
	
	function register_sidebar() {
		register_sidebar( $this->args );
	}
	
	/**
	 * Register all of the fields for the settings page
	 */
	function add_settings_fields() {
		add_settings_field( 'uitheme', __( 'Theme to use:' ), array( $this, 'do_settings_field' ), $this->settings_page, 'collapsible_widgets_section', array( 'label_for' => 'uitheme', 'field_name' => 'uitheme' ) );
	}
	
	function settings_section() {
		return;
	}
	
	/**
	 * Output the HTML for the settings fields
	 */
	function do_settings_field( $args ) {
		if ( ! array_key_exists( 'field_name', $args ) )
			return;
		if ( empty( $this->options ) )
			$this->options = $this->_get_admin_opt_vals();
		
		switch ( $args['field_name'] ) {
			case 'uitheme':
				$opts = $this->uiThemesList();
?>
	<select name="collapsible-widget-options[<?php echo $args['field_name'] ?>]" id="<?php echo $args['label_for'] ?>" class="widefat">
    	<option value=""<?php selected( $this->options['uitheme'], '' ) ?>><?php _e( 'Use default' ) ?></option>
<?php
				foreach ( $opts as $v=>$l ) {
?>
		<option value="<?php echo $v ?>"<?php selected( $this->options['uitheme'], $v ) ?>><?php echo esc_attr( $l ) ?></option>
<?php
				}
?>
    </select>
<?php
			break;
		}
	}
	
	/**
	 * Register the general options page for a single site
	 * @uses add_options_page()
	 */
	function add_options_page() {
		add_settings_section( 'collapsible_widgets_section', __( 'Collapsible Widget Area Settings' ), array( $this, 'settings_section' ), $this->settings_page );
		$this->add_settings_fields();
		register_setting( 'collapsible_widgets_section', 'collapsible-widget-options', array( $this, 'sanitize_settings' ) );
		
		$args = array( __( 'Collapsible Widget Options' ), __( 'Collapsible Widget Options' ), 'manage_options', $this->settings_page, array( $this, 'admin_options_page' ) );
		list( $page_title, $menu_title, $cap, $slug, $callback ) = $args;
		add_options_page( $page_title, $menu_title, $cap, $slug, $callback );
	}
	
	function sanitize_settings( $input ) {
		if ( ! array_key_exists( 'uitheme', $input ) || empty( $input['uitheme'] ) )
			$input['uitheme'] = '';
		else
			$input['uitheme'] = esc_attr( $input['uitheme'] );
		
		return $input;
	}
	
	/**
	 * Register the options page for the network and multi-network if applicable
	 * @uses is_multisite()
	 * @uses is_plugin_active_for_network()
	 * @uses __()
	 * @uses add_submenu_page()
	 * @uses collapsible_widget_area::$_is_primary_network
	 */
	function add_network_options_page() {
		if ( ! is_multisite() || ! is_plugin_active_for_network( $this->plugin_file ) )
			return;
		
		add_settings_section( 'collapsible_widgets_section', __( 'Collapsible Widget Area Settings' ), array( $this, 'settings_section' ), $this->settings_page );
		$this->add_settings_fields();
		register_setting( 'collapsible_widgets_section', 'collapsible-widget-options', array( $this, 'sanitize_settings' ) );
		
		$args = array( __( 'Collapsible Widget Options' ), __( 'Collapsible Widget Options' ), 'manage_network_options', $this->settings_page, array( $this, 'admin_options_page' ) );
		list( $page_title, $menu_title, $cap, $slug, $callback ) = $args;
		
		add_submenu_page( 'settings.php', $page_title, $menu_title, $cap, $slug, $callback );
		if ( $this->_is_primary_network ) {
			$page_title = sprintf( __( 'Multinetwork %s' ), $page_title );
			$menu_title = sprintf( __( 'Multinetwork %s' ), $menu_title );
			$cap = 'manage_networks';
			add_submenu_page( 'sites.php', $page_title, $menu_title, $cap, $slug, $callback );
		}
	}
	
	/**
	 * Build the appropriate options page
	 * @uses collapsible_widget_area::_get_admin_opt_vals() to retrieve the right set of options
	 */
	function admin_options_page() {
		$this->options = $this->_get_admin_opt_vals();
?>
<div class="wrap">
	<h2><?php _e( 'Collapsible Widget Area Options' ) ?></h2>
<?php
		if ( $this->_is_mn_settings_page ) {
			$action = '';
?>
	<p><em><?php _e( 'This is the settings page for the entire multi-network installation. Any options set here will act as the default for all sites; but can be overridden at the individual network level <strong>and</strong> at the individual site level.' ) ?></em></p>
<?php
		} else if ( is_multisite() && is_network_admin() ) {
			$action = '';
			if ( ! $this->is_multinetwork ) {
?>
	<p><em><?php _e( 'This is the settings page for the entire network. Any options set here will act as the default for all sites; but can be overridden at the individual site level.' ) ?></em></p>
<?php
			}
		} else {
			$action = 'options.php';
		}
		
		if ( $this->msg ) {
?>
	<p class="updated fade"><?php echo $this->msg ?></p>
<?php
		}
?>
	<form name="collwidopts" action="<?php echo $action ?>" method="post">
    	<?php settings_fields( 'collapsible_widgets_section' ) ?>
        <?php do_settings_sections( $this->settings_page ) ?>
        <p><input type="submit" value="<?php _e( 'Save' ) ?>" class="button-primary"/></p>
    </form>
</div>
<?php
	}
	
	function save_mn_settings( $input ) {
		if ( ! function_exists( 'update_mnetwork_option' ) )
			return save_network_settings( $input );
		
		$done = update_mnetwork_option( 'collapsible-widget-options', $input );
		if ( false === $done )
			return 'There was an error updating the settings.';
		else
			return 'The settings were saved successfully.';
	}
	
	function save_network_settings( $input ) {
		$done = update_site_option( 'collapsible-widget-options', $input );
		if ( false === $done )
			return 'There was an error updating the settings.';
		else
			return 'The settings were saved successfully.';
	}
	
	function _get_options() {
		$options = get_option( 'collapsible-widget-options', array() );
		if ( is_multisite() && is_plugin_active_for_network( $this->plugin_file ) ) {
			if ( is_multisite() && ( empty( $options ) || is_network_admin() ) )
				$options = get_site_option( 'collapsible-widget-options', array() );
			if ( $this->is_multinetwork && function_exists( 'get_mnetwork_option' ) && ( empty( $options ) || ( $this->_is_primary_network && $this->_is_mn_settings_page ) ) )
				$options = get_mnetwork_option( 'collapsible-widget-options', array() );
		}
		
		if ( ! is_array( $options ) )
			$options = array();
		
		$options = array_merge( $this->defaults, $options );
		return $this->options = $options;
	}
	
	function _get_admin_opt_vals() {
		if ( function_exists( 'get_mnetwork_option' ) && $this->_is_mn_settings_page )
			return array_merge( $this->defaults, get_mnetwork_option( 'collapsible-widget-options', array() ) );
		if ( is_multisite() && is_network_admin() )
			return array_merge( $this->defaults, get_site_option( 'collapsible-widget-options', array() ) );
			
		return array_merge( $this->defaults, get_option( 'collapsible-widget-options', array() ) );
	}
	
	function get_args() {
		$this->args = apply_filters( 'collapsible-widget-area-args', array(
			'name'          => __( 'Collapsible Widget Area', 'collapsible-widget-area' ),
			'id'            => 'collapsible-widget-area',
			'description'   => __( 'Drag widgets into this area in order to use them inside of the Collapsible Widget.', 'collapsible-widget-area' ),
			'before_widget' => '<div id="%1$s" class="widget %2$s collapsible-item">',
			'after_widget'  => '</div>',
			'before_title'  => '<h2 class="widgettitle">',
			'after_title'   => '</h2>',
		) );
		$this->sidebar_id = $this->args['id'];
		if ( ! class_exists( 'collapsible_widget' ) ) {
			require_once( 'class.collapsible-widget.php' );
		}
		add_action( 'widgets_init', array( $this, 'register_widget' ) );
	}
	
	function register_widget() {
		register_widget( 'collapsible_widget' );
	}

	function uiThemesList() {
		$list = apply_filters( 'collapsible-widget-theme-list', array( 
			'base'      => 'Base Theme', 
			'black-tie' => 'Black Tie', 
			'blitzer'   => 'Blitzer', 
			'cupertino' => 'Cupertino', 
			'dark-hive' => 'Dark Hive', 
			'dot-luv'   => 'Dot Luv', 
			'eggplant'  => 'Eggplant', 
			'excite-bike' => 'Excite Bike', 
			'flick'     => 'Flick', 
			'hot-sneaks' => 'Hot Sneaks', 
			'humanity'  => 'Humanity', 
			'le-frog'   => 'Le Frog', 
			'mint-choc' => 'Mint Chocolate', 
			'overcast'  => 'Overcast', 
			'pepper-grinder' => 'Pepper Grinder', 
			'redmond'   => 'Redmond', 
			'smoothness' => 'Smoothness', 
			'south-street' => 'South Street', 
			'start'     => 'Start', 
			'sunny'     => 'Sunny', 
			'swanky-purse' => 'Swanky Purse', 
			'trontastic' => 'Trontastic', 
			'ui-darkness' => 'UI Darkness', 
			'ui-lightness' => 'UI Lightness', 
			'vader'     => 'Vader', 
			'none'      => 'None (I have my own)', 
		) );
		
		return $list;
	}
	
	/**
	 * Check to see if this is installed/activated in a multi-network environment
	 */
	protected function _is_multinetwork() {
		if( function_exists( 'is_multinetwork' ) && is_multinetwork() )
			return $this->is_multinetwork = true;
		
		return false;
	} /* _is_multinetwork() */
}
?>