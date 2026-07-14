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
		require_once get_stylesheet_directory() . '/inc/override_functions.php';
		require_once get_stylesheet_directory() . '/inc/Flatsome_Child_Options.php';
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

/**
 * پلتفرم هوشمند سحاب (OSINT)
 * ماژول مدیریت سلسله‌مراتب و ساختار اداری کارشناسان
 */

// ۱. اضافه کردن ستون مدیر به جدول کاربران در پیشخوان
function sahab_add_user_manager_column($columns) {
    $columns['user_manager'] = 'مدیر مستقیم (اداره)';
    return $columns;
}
add_filter('manage_users_columns', 'sahab_add_user_manager_column');

// ۲. پر کردن دیتای ستون مدیر با استفاده از فیلد ACF
function sahab_show_user_manager_column_content($value, $column_name, $user_id) {
    if ($column_name === 'user_manager') {
        // دریافت شناسه مدیر از فیلد ACF کاربر
        $manager_id = get_field('reports_to', 'user_' . $user_id);
        
        if ($manager_id) {
            $manager_data = get_userdata($manager_id);
            if ($manager_data) {
                // نمایش نام مدیر مستقیم کارشناس
                return '<strong style="color: #0891a1;">' . esc_html($manager_data->display_name) . '</strong>';
            }
        }
        
        // اگر کاربر خودش نقش ویرایشگر (مدیر) داشته باشد یا سرپرست نداشته باشد
        $user_data = get_userdata($user_id);
        if (in_array('editor', (array) $user_data->roles)) {
            return '<span class="badge" style="background: #eae9fd; color: #4c46c9; padding: 3px 8px; border-radius: 10px; font-size: 11px;">سرپرست اداره</span>';
        }
        
        return '<span style="color: #94a3b8;">تعیین نشده</span>';
    }
    return $value;
}
add_filter('manage_users_custom_column', 'sahab_show_user_manager_column_content', 10, 3);

require_once get_stylesheet_directory() . '/inc/comments-handler.php';
require_once get_stylesheet_directory() . '/inc/acf-automation.php';
require_once get_stylesheet_directory() . '/inc/jalali-datepicker.php';
require_once get_stylesheet_directory() . '/inc/admin-labels.php';
require_once get_stylesheet_directory() . '/inc/dashboard-core.php';
require_once get_stylesheet_directory() . '/inc/admin-customizer.php';
// لود کردن ماژول گزارشات تحلیلی سحاب به صورت ماژولار
require_once get_stylesheet_directory() . '/inc/sahab-bi-reporting.php';