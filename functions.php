<?php
/**
 * پلتفرم گزارش‌دهی، دسته‌بندی و مدیریت داده‌های پژوهشی «سحاب»
 * تم چایلد اختصاصی بر روی قالب Flatsome
 */

/**
 * Use Relevanssi excerpts on the search results page only.
 * This stays scoped to is_search() so category/blog archives keep their normal excerpts.
 */
function flatsome_child_search_excerpt( $excerpt, $post = null ) {
	if ( ! is_search() ) {
		return $excerpt;
	}

	if ( function_exists( 'relevanssi_the_excerpt' ) ) {
		ob_start();
		relevanssi_the_excerpt();
		$relevanssi_excerpt = ob_get_clean();

		if ( ! empty( trim( $relevanssi_excerpt ) ) ) {
			return $relevanssi_excerpt;
		}
	}

	return $excerpt;
}

/**
 * ۹. نمایش نوع پی‌نوشت (ACF `comment_type`) به‌صورت یک نشان زیبا در کنار متن دیدگاه
 */
add_filter( 'the_excerpt', 'flatsome_child_search_excerpt', 20 );
add_filter( 'get_the_excerpt', 'flatsome_child_search_excerpt', 20 );


class Flatsome_Child_Theme_Init {

	/**
	 * The single instance of the class.
	 *
	 * @var Flatsome_Child_Theme_Init
	 */
	protected static $_instance = null;

	public static $version = '3.7.2';

	/**
	 * Flatsome_Child_Theme_Init constructor.
	 */
	public function __construct() {
		$this->load_dependencies();

		// Load text domain
		add_action( 'after_setup_theme', array($this, 'load_text_domain') );

		// Frontend assets
		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));

		// Add assets to admin
		add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts') );

		Flatsome_Child_Options::instance();
	}

	/**
	 * Load plugin text_domain.
	 *
	 * @since 1.0.0
	 */
	public function load_text_domain() {
		// load custom translation file for the parent theme
		load_theme_textdomain( 'flatsome', get_stylesheet_directory() . '/languages/parent' );
		// load translation file for the child theme
		load_child_theme_textdomain( 'flatsome-child', get_stylesheet_directory() . '/languages' );
	}

	public function enqueue_scripts() {
		// Css Files
		wp_enqueue_style('flatsome-child-theme', get_stylesheet_directory_uri().'/assets/public/css/flatsome-child-theme.css', array(), self::$version);

		$font = Flatsome_Child_Options::get_option('font', 'typography');
		if( isset($font) && !empty($font) ) {
			wp_enqueue_style('flatsome-child-'.$font.'font', get_stylesheet_directory_uri().'/assets/public/css/'.$font.'-font.css', array(), self::$version);
		}
	}

	public function admin_enqueue_scripts() {
		// Javascript Files
		wp_enqueue_script( 'flatsome-child', get_stylesheet_directory_uri() . '/assets/admin/js/flatsome-child.js', array( 'jquery' ), self::$version, true );
	}

	/**
	 * Load child theme dependency files
	 *
	 * @since 1.0.0
	 */
	private function load_dependencies() {
		require_once get_stylesheet_directory() . '/includes/override_functions.php';
		require_once get_stylesheet_directory() . '/includes/Flatsome_Child_Options.php';
	}

	/**
	 * Main Class Instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
}
Flatsome_Child_Theme_Init::instance();


require_once get_stylesheet_directory() . '/inc/comments-handler.php';
require_once get_stylesheet_directory() . '/inc/acf-automation.php';
require_once get_stylesheet_directory() . '/inc/jalali-datepicker.php';
require_once get_stylesheet_directory() . '/inc/admin-labels.php';

