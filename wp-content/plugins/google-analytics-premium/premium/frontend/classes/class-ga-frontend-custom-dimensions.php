<?php
/**
 * @package GoogleAnalytics\Premium
 */

/**
 * Class Yoast_GA_Frontend_Custom_Dimensions
 *
 * Some custom dimensions depend on other plugins. These are disabled when the plugin they depend on is not activated.
 * That has a couple of somewhat complex UX implications:
 *
 * - When the disabled custom dimension was not yet added, it is shown as a disabled option in the dropdown so it cannot be added anymore.
 * - When the disabled custom dimension was already added, it's still shown on the custom dimensions tab. The select element used to select the type is disabled
 *   and a message is shown telling the user the custom dimension has been disabled because the plugin it depended on was deactivated. It can then be
 *   removed but not added again. A hidden field is added to the page to make sure that the disabled custom dimension's type is still saved to the database.
 * - When adding new custom dimensions in the custom dimensions tab, the disabled custom dimensions are not taken into account in determining the
 *   limit of custom dimensions that can be added. That way the limit is always reached when all enabled dimensions have been added to the form.
 *
 * Some custom dimensions are not inserted directly into the head, but are sent with an event. These are outputted with output_async_custom_dimensions().
 */
class Yoast_GA_Frontend_Custom_Dimensions extends Yoast_GA_Custom_Dimensions {

	/**
	 * @var array Contains the content for the custom dimensions as they need to be tracked.
	 */
	protected $custom_dimensions_content;

	/**
	 * @var array Contains the content for the async custom dimensions (are sent using JavaScript) as they need to be tracked.
	 */
	protected $async_dimensions = array();

	/**
	 * Overrides instance to set with this class as class
	 *
	 * @param string $class_name
	 *
	 * @return Yoast_GA_Custom_Dimensions
	 */
	public static function get_instance( $class_name = __CLASS__ ) {
		return parent::get_instance( $class_name );
	}

	/**
	 * Protected constructor to prevent creating a new instance of the
	 * *Singleton* via the `new` operator from outside of this class.
	 *
	 */
	protected function __construct() {
		parent::__construct();
		$this->set_dimensions();
	}

	/**
	 * Output the custom dimensions in the Universal tracking code
	 * Also output the async dimensions that need to be read out using JavaScript
	 *
	 * @link https://developers.google.com/analytics/devguides/collection/analyticsjs/custom-dims-mets
	 *
	 * @param array $gaq_push
	 *
	 * @return array
	 */
	public static function hook_custom_dimensions( $gaq_push ) {
		$instance = self::get_instance();

		$instance->output_async_dimensions();

		return $instance->output_custom_dimensions( $gaq_push );
	}

	/**
	 * Output the custom dimension type and id in a hidden span so JavaScript can read it out
	 */
	public function output_async_dimensions() {
		$output = '';
		foreach ( $this->async_dimensions as $async_dimension ) {
			$output .= '<span style="display:none;" id="custom-dimension-' . $async_dimension['type'] . '" data-yoast-cd-id="dimension' . $async_dimension['id'] . '"></span>';
		}

		echo $output;
	}

	/**
	 * Convert the current dimension into a value, by calling the correct function
	 */
	public function set_dimensions() {
		foreach ( $this->active_custom_dimensions as $dimension ) {
			$method = $dimension['type'];
			if ( method_exists( $this, $method ) ) {
				$this->$method( $dimension );
			}
		}
	}

	/**
	 * Return the custom dimensions
	 *
	 * @return array
	 */
	public function get_dimensions() {
		return $this->custom_dimensions_content;
	}

	/**
	 * Add a dimension (before output to Universal, not to save a new one)
	 *
	 * @param array  $dimension
	 * @param string $value
	 */
	protected function add_dimension( $dimension, $value ) {
		$this->custom_dimensions_content[] = array(
			'type'  => $dimension['type'],
			'id'    => $dimension['id'],
			'value' => $value,
		);
	}

	/**
	 * @param array $gaq_push
	 *
	 * @return array Returns the array with custom dimensions to be outputted
	 */
	protected function output_custom_dimensions( $gaq_push ) {
		$last_key   = array_pop( $gaq_push );
		$gaq_push   = $this->add_custom_dimensions_to_tracking_code( $gaq_push );
		$gaq_push[] = $last_key;

		return $gaq_push;
	}

	/**
	 * @param array $gaq_push
	 *
	 * @return array Contains all of the custom dimensions to be outputted in the head
	 */
	protected function add_custom_dimensions_to_tracking_code( $gaq_push ) {
		if ( ! empty ( $this->custom_dimensions_content ) ) {
			$gaq_push = array_merge( $gaq_push, array_map( array( $this, 'set_dimension_output' ), $this->custom_dimensions_content ) );
		}

		return $gaq_push;
	}

	/**
	 * @param array $dimension
	 *
	 * @return string Contains the arguments to pass in to the JS tracker object
	 */
	protected function set_dimension_output( $dimension ) {
		return "'set', 'dimension" . $dimension['id'] . "', '" . addslashes( $dimension['value'] ) . "'";

	}

	/**
	 * Add an async custom dimension
	 *
	 * @param array $dimension
	 */
	protected function add_async_dimension( $dimension ) {
		$this->async_dimensions[] = $dimension;
	}


	/**
	 * Handle the logged in custom dimensionsf
	 *
	 * @param array $dimension
	 */
	protected function logged_in( $dimension ) {
		$value = var_export( is_user_logged_in(), true );

		$this->add_dimension( $dimension, $value );
	}

	/**
	 * Handle the post type in custom dimensions
	 *
	 * @param array $dimension
	 */
	protected function post_type( $dimension ) {
		if ( is_singular() ) {
			$post_type = get_post_type( get_the_ID() );

			if ( $post_type ) {
				$this->add_dimension( $dimension, $post_type );
			}
		}
	}

	/**
	 * Handle the author in custom dimensions
	 *
	 * @param array $dimension
	 */
	protected function author( $dimension ) {
		if ( is_singular() ) {
			if ( have_posts() ) {
				while ( have_posts() ) {
					the_post();
				}
			}

			$firstname = get_the_author_meta( 'user_firstname' );
			$lastname  = get_the_author_meta( 'user_lastname' );

			if ( ! empty( $firstname ) || ! empty( $lastname ) ) {
				$value = trim( $firstname . ' ' . $lastname );
			}
			else {
				$value = 'user-' . get_the_author_meta( 'ID' );
			}

			$this->add_dimension( $dimension, $value );
		}
	}

	/**
	 * Handle the category in custom dimensions
	 *
	 * @param array $dimension
	 */
	protected function category( $dimension ) {
		if ( is_single() ) {
			$categories = get_the_category( get_the_ID() );

			if ( $categories ) {
				foreach ( $categories as $category ) {
					$category_names[] = $category->slug;
				}

				$this->add_dimension( $dimension, implode( ',', $category_names ) );
			}
		}
	}

	/**
	 * Handle the tags in custom dimensions
	 *
	 * @param array $dimension
	 */
	protected function tags( $dimension ) {
		if ( is_single() ) {
			$tag_names = 'untagged';

			$tags = get_the_tags( get_the_ID() );

			if ( $tags ) {
				$tag_names = implode( ',', wp_list_pluck( $tags, 'name' ) );
			}

			$this->add_dimension( $dimension, $tag_names );
		}
	}


	/**
	 * Handle the published at in custom dimensions
	 *
	 * @param array $dimension
	 */
	protected function published_at( $dimension ) {
		if ( is_singular() ) {
			$this->add_dimension( $dimension, get_the_date( 'c' ) );
		}
	}

	/**
	 * Handle the scroll depth in custom dimensions
	 * Because we want to know the highest scroll depth when the user closes the page, this custom dimension needs to be sent with JavaScript when the user closes the page
	 *
	 * @param array $dimension
	 */
	protected function scroll_depth( $dimension ) {
		if ( is_singular() ) {

			wp_enqueue_script( 'custom_dimensions_scroll_depth', GAWP_URL . 'premium/frontend/js/bam-percent-page-viewed.min.js' );
			wp_enqueue_script( 'scroll-depth', GAWP_URL . 'premium/frontend/js/scroll-depth.min.js' );

			$this->add_async_dimension( $dimension );
		}
	}

	/**
	 * Handle the focus keyword in custom dimensions
	 *
	 * @param array $dimension
	 */
	protected function focus_keyword( $dimension ) {
		// Make sure WP SEO or WP SEO Premium is active and if a singular post is displayed
		if ( $this->wp_seo_active() && is_singular() ) {
			$focus_keyword = get_post_meta( get_the_ID(), '_yoast_wpseo_focuskw', true );

			if ( empty( $focus_keyword ) ) {
				/* translators: Default value shown in Google Analytics when no focus keyword has been set. Use underscores to differentiate from normal focus keywords. */
				$focus_keyword = __( 'focus_keyword_not_set', 'ga-premium' );
			}

			// Do not call add_dimension if $focus_keyword is empty
			if ( ! empty( $focus_keyword ) ) {
				$this->add_dimension( $dimension, $focus_keyword );
			}
		}
	}

	/**
	 * Handle the SEO scores in custom dimensions
	 *
	 * @param array $dimension
	 */
	protected function seo_score( $dimension ) {
		// Make sure WP SEO or WP SEO Premium is active and if a singular post is displayed
		if ( $this->wp_seo_active() && is_singular() ) {
			$score_label = $this->get_wp_seo_score( get_the_ID() );

			$this->add_dimension( $dimension, $score_label );
		}
	}

	/**
	 * Get SEO score for post from WordPress SEO Plugin
	 *
	 * @param int $post_id
	 *
	 * @return string
	 */
	protected function get_wp_seo_score( $post_id ) {
		// Get seo score from WordPress SEO
		$score = WPSEO_Metabox::get_value( 'linkdex', $post_id );
		if ( $score !== '' ) {
			return $this->wpseo_translate_score( $score );
		}

		return 'na';
	}

	/**
	 * wpseo_translate_score has been deprecated in newer versions of wordpress-seo
	 *
	 * @param int $nr
	 *
	 * @return mixed
	 */
	protected function wpseo_translate_score( $nr ) {
		if ( method_exists( 'WPSEO_Utils', 'translate_score' ) ) {
			return WPSEO_Utils::translate_score( $nr );
		}
		return wpseo_translate_score( $nr );
	}
}