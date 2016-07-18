<?php
/**
 * @package GoogleAnalytics\Premium
 */

/**
 * Class MI_Product_GA_Premium
 */
class MI_Product_GA_Premium extends MI_Product {

	/**
	 * Contains the license manager object
	 *
	 * @var object MI_Plugin_License_Manager
	 */
	protected $license_manager;

	/**
	 * Constructor of the class
	 */
	public function __construct() {
		$file = plugin_basename( GAWP_FILE );
		$slug = dirname( $file );

		parent::__construct(
			'https://www.monsterinsights.com',
			'MonsterInsights Pro',
			$slug,
			GA_YOAST_PREMIUM_VERSION,
			'https://www.monsterinsights.com/pricing/',
			'admin.php?page=yst_ga_extensions#top#licenses',
			'yoast-google-analytics-premium',
			'MonsterInsights',
			$file
		);

		$this->setup_license_manager();

	}


	/**
	 * Setting up the license manager
	 *
	 * @since 3.0
	 */
	protected function setup_license_manager() {

		$license_manager = new MI_Plugin_License_Manager( $this );
		$license_manager->setup_hooks();

		add_filter( 'yst_ga_extension_status', array( $this, 'filter_extension_is_active' ), 10, 1 );
		add_action( 'yst_ga_show_license_form', array( $this, 'action_show_license_form' ) );

		$this->license_manager = $license_manager;
	}

	/**
	 * If extension is active, it should be check if its license is valid
	 *
	 * @since 3.0
	 *
	 * @param array $extensions
	 *
	 * @return mixed
	 */
	public function filter_extension_is_active( $extensions ) {

		if ( $this->license_manager->license_is_valid() ) {
			$extensions['ga_premium']->status = 'active';
		}
		else {
			$extensions['ga_premium']->status = 'inactive';
		}

		return $extensions;
	}

	/**
	 * This method will echo the license form for the extension
	 *
	 * @since 3.0
	 */
	public function action_show_license_form() {
		echo $this->license_manager->show_license_form( false );
	}

}