<?php
/**
 * Base GHC plugin class
 *
 * @author AndrewRMinion Design
 * @package GHC_Functionality
 */

if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

/**
 * Base GHC class
 */
class GHC_Base {
	/**
	 * Plugin version.
	 *
	 * @var string Plugin version string for cache-busting.
	 */
	protected $version = '4.0.0';

	/**
	 * All active conventions.
	 *
	 * @var array
	 */
	protected $conventions = array();

	/**
	 * Convention abbreviations.
	 *
	 * @var array
	 */
	protected $conventions_abbreviations = array();

	/**
	 * Get this plugin directory path.
	 *
	 * @param  string [ $path       = ''] Optional path to append.
	 *
	 * @return string Base path for this plugin’s directory.
	 */
	protected function plugin_dir_path( $path = '' ) : string {
		return plugin_dir_path( GHC_PLUGIN_FILE ) . $path;
	}

	/**
	 * Get this plugin directory URL.
	 *
	 * @param  string [ $path       = ''] Optional path to append.
	 *
	 * @return string Base URL for this plugin’s directory
	 */
	protected function plugin_dir_url( $path = '' ) : string {
		return plugin_dir_url( GHC_PLUGIN_FILE ) . $path;
	}

	/**
	 * Class instance.
	 *
	 * @var null
	 */
	private static $instance = null;

	/**
	 * Return only one instance of this class.
	 *
	 * @return GHC_Base class.
	 */
	public static function get_main_instance() : GHC_Base {
		if ( null === self::$instance ) {
			self::$instance = new GHC_Base();
		}

		return self::$instance;
	}


	/**
	 * Kick things off.
	 *
	 * @access public
	 */
	private function __construct() {

		include_once 'class-ghc-post-types.php'; // Loaded in class.
		include_once 'class-ghc-conventions.php'; // Loaded in class.

		include_once 'class-ghc-acf.php'; // Loaded in class.
		include_once 'class-ghc-content.php'; // Loaded in class.
		include_once 'class-ghc-images.php'; // Loaded in class.
		include_once 'class-ghc-shortcodes.php'; // Loaded in class.
		include_once 'class-ghc-speakers.php'; // Loaded in class.
		include_once 'class-ghc-woocommerce.php'; // Loaded in class.

		// Set up convention info.
		add_action( 'after_setup_theme', array( $this, 'get_conventions_info' ) );
		add_action( 'after_setup_theme', array( $this, 'get_conventions_abbreviations' ) );

		// Register/enqueue assets.
		add_action( 'wp_enqueue_scripts', array( $this, 'register_frontend_assets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_backend_assets' ) );
	}

	/**
	 * Get all convention info.
	 *
	 * @return array All convention info.
	 */
	public function get_conventions_info() : array {
		if ( 0 === count( $this->conventions ) ) {
			$conventions       = GHC_Conventions::get_instance();
			$this->conventions = $conventions->get_conventions_info();
		}

		return $this->conventions;
	}

	/**
	 * Get convention abbreviations.
	 *
	 * @return array Convention abbreviations.
	 */
	public function get_conventions_abbreviations() : array {
		if ( 0 === count( $this->conventions_abbreviations ) ) {
			$conventions                     = GHC_Conventions::get_instance();
			$this->conventions_abbreviations = $conventions->get_conventions_abbreviations();
		}

		return $this->conventions_abbreviations;
	}

	/**
	 * Get info for a single convention.
	 *
	 * @param  string $convention Two-letter convention abbreviation.
	 *
	 * @return array  Convention info.
	 */
	public function get_single_convention_info( string $convention = '' ) : array {
		if ( ! empty( $convention ) ) {
			return $this->get_conventions_info()[ strtolower( $convention ) ];
		}

		return array();
	}

	/**
	 * Get abbreviation for a single convention.
	 *
	 * @param  string $convention Two-letter convention abbreviation.
	 *
	 * @return array  Convention abbreviation.
	 */
	public function get_single_convention_abbreviation( string $convention = '' ) : string {
		if ( ! empty( $convention ) ) {
			return $this->get_conventions_abbreviations()[ strtolower( $convention ) ];
		}

		return '';
	}

	/**
	 * Get formatted date range for a single convention.
	 *
	 * @param  string $convention Two-letter convention abbreviation.
	 *
	 * @return string Convention dates.
	 */
	public function get_single_convention_date( string $convention = '' ) : string {
		if ( ! empty( $convention ) ) {
			$this_convention = $this->get_single_convention_info( $convention );
			return $this->format_date_range( $this_convention['begin_date'], $this_convention['end_date'], 'Ymd' );
		}

		return '';
	}

	/**
	 * Register or enqueue frontend assets.
	 *
	 * @return  void Enqueues assets.
	 */
	public function register_frontend_assets() {
		wp_enqueue_style( 'ghc-functionality', $this->plugin_dir_url( 'dist/css/style.min.css' ), array(), $this->version );

		wp_enqueue_script( 'ghc-popups', $this->plugin_dir_url( 'dist/js/popups.min.js' ), array( 'jquery', 'popup-maker-site' ), $this->version, true );

		wp_register_script( 'ghc-robly-lists', $this->plugin_dir_url( 'dist/js/robly-lists.min.js' ), array( 'jquery' ), $this->version, true );

		wp_register_script( 'slick', $this->plugin_dir_url( 'dist/js/slick.min.js' ), array( 'jquery' ), $this->version, true );
		wp_register_style( 'slick', $this->plugin_dir_url( 'dist/css/slick.min.css' ), array(), $this->version, false );
	}

	/**
	 * Register backend assets.
	 *
	 * @return void Enqueues assets.
	 */
	public function register_backend_assets() {
		global $post_type;
		if ( 'exhibitor' === $post_type ) {
			wp_enqueue_script( 'ghc-exhibitor-backend', $this->plugin_dir_url( 'js/exhibitor-backend.min.js' ), array( 'jquery' ), $this->version, true );
		}
	}

	/**
	 * Format date range.
	 *
	 * @link https://codereview.stackexchange.com/a/78303 Adapted from this answer.
	 *
	 * @param  mixed  $d1      Start DateTime object or string.
	 * @param  mixed  $d2      End DateTime object or string.
	 * @param  string $format  Input date format if passed as strings.
	 *
	 * @return  string Formatted date string.
	 */
	public function format_date_range( $d1, $d2, string $format = '' ) : string {
		if ( is_string( $d1 ) && is_string( $d2 ) && ! empty( $format ) ) {
			$d1 = date_create_from_format( $format, $d1 );
			$d2 = date_create_from_format( $format, $d2 );
		}

		if ( $d1->format( 'Y-m-d' ) === $d2->format( 'Y-m-d' ) ) {
			// Same day.
			return $d1->format( 'F j, Y' );
		} elseif ( $d1->format( 'Y-m' ) === $d2->format( 'Y-m' ) ) {
			// Same calendar month.
			return $d1->format( 'F j' ) . '&ndash;' . $d2->format( 'j, Y' );
		} elseif ( $d1->format( 'Y' ) === $d2->format( 'Y' ) ) {
			// Same calendar year.
			return $d1->format( 'F j' ) . '&ndash;' . $d2->format( 'F j, Y' );
		} else {
			// General case (spans calendar years).
			return $d1->format( 'F j, Y' ) . '&ndash;' . $d2->format( 'F j, Y' );
		}
	}

}
