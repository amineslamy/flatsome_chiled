<?php
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
	public function load_text_domain(  ) {
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

		// Javascript Files
		//wp_enqueue_script('zhaket-theme', get_template_directory_uri().'/assets/public/js/theme.min.js', array('jquery'), self::$version, true);

	}


	public function admin_enqueue_scripts() {
		// // Javascript Files
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
	 *
	 * Ensures only one instance of this class is loaded or can be loaded.
	 *
	 * @static
	 * @return Flatsome_Child_Theme_Init - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
}
Flatsome_Child_Theme_Init::instance();

function flatsome_child_disable_automation_id_field( $field ) {
	$field['disabled'] = true;
	return $field;
}

add_filter( 'acf/load_field/name=automation_id', 'flatsome_child_disable_automation_id_field' );

function flatsome_child_generate_automation_id( $post_id ) {
	if ( get_post_type( $post_id ) !== 'post' ) {
		return;
	}

	if ( get_post_meta( $post_id, 'automation_id', true ) ) {
		return;
	}

	global $wpdb;

	$meta_key = 'automation_id';
	$max_value = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT MAX(CAST(meta_value AS UNSIGNED)) FROM $wpdb->postmeta WHERE meta_key = %s",
			$meta_key
		)
	);

	$next_id = empty( $max_value ) ? 1234567 : (int) $max_value + 1;
	update_post_meta( $post_id, $meta_key, $next_id );
}

add_action( 'acf/save_post', 'flatsome_child_generate_automation_id', 5 );

function flatsome_child_enqueue_jalali_datepicker_assets() {
	wp_enqueue_style(
		'flatsome-child-jalali-datepicker-css',
		get_stylesheet_directory_uri() . '/assets/admin/css/jalali-datepicker.min.css',
		array(),
		'1.0.0'
	);

	wp_enqueue_script(
		'flatsome-child-jalali-datepicker-js',
		get_stylesheet_directory_uri() . '/assets/admin/js/jalali-datepicker.min.js',
		array(),
		'1.0.0',
		true
	);
}

add_action( 'admin_enqueue_scripts', 'flatsome_child_enqueue_jalali_datepicker_assets' );
add_action( 'wp_enqueue_scripts', 'flatsome_child_enqueue_jalali_datepicker_assets' );

function flatsome_child_configure_event_date_field( $field ) {
	// Inject data-jdp and autocomplete="off" directly into the HTML input tag attributes
	if ( ! isset( $field['custom_attributes'] ) || ! is_array( $field['custom_attributes'] ) ) {
		$field['custom_attributes'] = array();
	}
	$field['custom_attributes']['data-jdp'] = 'true';
	$field['custom_attributes']['autocomplete'] = 'off';

	return $field;
}

add_filter( 'acf/load_field/name=event_date', 'flatsome_child_configure_event_date_field' );

function flatsome_child_initialize_jalali_datepicker() {
	echo '<script>
	document.addEventListener("DOMContentLoaded", function() {
		if (typeof jalaliDatepicker !== "undefined") {
			var acfEventDateInput = document.querySelector("input[name=\"event_date\"], input[data-jdp]");
			if (acfEventDateInput) {
				jalaliDatepicker.startWatch({
					minDate: "attr",
					maxDate: "attr",
					autoReadOnlyInput: true
				});
			}
		}
	});
	</script>';
}

add_action( 'admin_footer', 'flatsome_child_initialize_jalali_datepicker' );
add_action( 'wp_footer', 'flatsome_child_initialize_jalali_datepicker' );