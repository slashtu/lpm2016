<?php
/**
 * Plugin update class
 */


// Prevent direct call
if ( !defined( 'WPINC' ) ) die;
if ( !class_exists( 'GW_GoPricing' ) ) die;	

// Class
class GW_GoPricing_Update {

	protected static $instance = null;
	protected $globals;
		
	protected static $plugin_version;
	protected static $db_version;
	protected static $plugin_prefix;
	protected static $plugin_slug;
	protected static $plugin_path;
	
	protected $plugin_file;
	protected $plugin_base;	
	
	protected $api_url = 'http://granthweb.com/api';	


	/**
	 * Initialize the class
	 *
	 * @return void
	 */
	
	public function __construct() {
		
		$this->globals = GW_GoPricing::instance();
		self::$plugin_version = $this->globals['plugin_version'];
		self::$db_version = $this->globals['db_version'];		
		self::$plugin_prefix = $this->globals['plugin_prefix'];
		self::$plugin_slug = $this->globals['plugin_slug'];
		self::$plugin_path = $this->globals['plugin_path'];
		
		$this->plugin_file = $this->globals['plugin_file'];
		$this->plugin_base = $this->globals['plugin_base'];		

		add_action( 'init', array( $this, 'update_filters' ) );
	
	}	
	
	
	/**
	 * Return an instance of this class
	 *
	 * @return object
	 */
	 
	public static function instance() {
		
		if ( self::$instance == null ) self::$instance = new self;
		return self::$instance;
		
	}
	
	
	/**
	 * Update fileters
	 *
	 * @return void
	 */	


	public function update_filters() {
		
		// Check for update
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ) );

		// Show plugin information
		add_filter( 'plugins_api', array( $this, 'update_info' ), 10, 3 );
		

	}
		
	
	/**
	 * Check for plugin updates
	 *
	 * @return array
	 */		  
		 
	public function check_update( $transient ) {
		
		global $wp_version;
		
		$response = wp_remote_post( 
			$this->api_url, 
			array(
				'method' => 'POST',
				'timeout' => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking' => true,
				'user-agent'  => 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' ),
				'headers' => array(),
				'body' => array( 
					'product' => 'go_pricing'
				)
			)
		);
		
		if ( is_wp_error( $response ) || empty( $response['body'] ) || !isset( $response['response']['code'] ) || $response['response']['code'] != '200' || !isset( $response['headers']['content-type'] ) || $response['headers']['content-type'] != 'application/json' ) {
		
			return $transient;	
		
		} else {
			
			$remote_version = json_decode( $response['body'] );
			
			update_option( self::$plugin_prefix . '_update_data', $remote_version->data );
						
			if ( version_compare( self::$plugin_version, $remote_version->data->version, '<' ) ) {
	
				$obj = new stdClass();
				$obj->slug = basename( $this->plugin_file );
				$obj->plugin = self::$plugin_slug;
				$obj->new_version = $remote_version->data->version;
				$obj->package = '';		
				$obj->upgrade_notice = '';
				$transient->response[$this->plugin_base] = $obj;
	
			}
			
			return $transient;	
		
		}	

	}
	
	
	/**
	 * Show update details
	 *
	 * @return array | bool
	 */		
	
	public function update_info( $false, $action, $args ) {
		
		$plugin_base = explode( '/', $this->plugin_base );
		$plugin_slug = $plugin_base[count($plugin_base)-1];
		$plugin_data = get_option( self::$plugin_prefix . '_update_data', array() );
		
		if ( empty( $plugin_data ) ) return false;
		
		$change_log = '';
		
		if ( !empty( $plugin_data->log ) ) {
			
			foreach( $plugin_data->log as $version => $info ) {
				
				$change_log .= sprintf( '<h4>%s</h4>', $version );
				$change_log .= '<em>' . sprintf( '%1$s - %2$s', __( 'Release Date', 'go_pricing_textdomain' ), date_i18n( get_option( 'date_format' ), strtotime( $info->date ) ) ). '</em>';
				$change_log .= !empty( $info->description ) ? sprintf( '<p>%s</p>', $info->description ) : ''; 
				$change_log .= $info->log;
				
			}
			
		}
		
		if ( isset( $args->slug ) && $args->slug == $plugin_slug ) {
			$obj = new stdClass();
			$obj->slug = $plugin_slug;  
			$obj->name = $plugin_data->name;
			$obj->plugin_name = $plugin_slug;
			$obj->version = $plugin_data->version;			
			$obj->requires = $plugin_data->wp_min;  
			$obj->tested = $plugin_data->wp_max;  
			$obj->last_updated = $plugin_data->date;  
			$obj->sections = array(  
				'description' => !empty( $plugin_data->description ) ? wpautop( $plugin_data->description ) : '',
				'changelog' => $change_log 
			);
			$obj->author = '<a href="http://granthweb.com" target="_blank">Granth</a>';
			$obj->homepage = $plugin_data->url;
			return $obj;
		}
		
		return false;
		
	}

}

?>