<?php
/**
 * پلتفرم گزارش‌دهی، دسته‌بندی و مدیریت داده‌های پژوهشی «سحاب»
 * تم چایلد اختصاصی بر روی قالب Flatsome
 */

/**
 * Use Relevanssi excerpts on the search results page only.
 * This stays scoped to is_search() so category/blog archives keep their normal excerpts.
 */
function flatsome_child_search_excerpt($excerpt, $post = null)
{
	// شرط را بازتر می‌کنیم تا علاوه بر صفحه جستجو، در صفحات فیلتر پیشرفته یا اگر کوئری حاوی پارامتر جستجو بود نیز عمل کند
	if (!is_search() && !isset($_GET['s']) && !isset($_GET['reg_date'])) {
		return $excerpt;
	}

	if (function_exists('relevanssi_the_excerpt')) {
		ob_start();
		relevanssi_the_excerpt();
		$relevanssi_excerpt = ob_get_clean();

		if (!empty(trim($relevanssi_excerpt))) {
			return $relevanssi_excerpt;
		}
	}

	return $excerpt;
}

/**
 * نمایش نوع پی‌نوشت (ACF comment_type) به‌صورت یک نشان زیبا در کنار متن دیدگاه
 */
add_filter('the_excerpt', 'flatsome_child_search_excerpt', 20);
add_filter('get_the_excerpt', 'flatsome_child_search_excerpt', 20);


class Flatsome_Child_Theme_Init
{

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
	public function __construct()
	{
		$this->load_dependencies();

		// Load text domain
		add_action('after_setup_theme', array($this, 'load_text_domain'));

		// Frontend assets
		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));

		// Add assets to admin
		add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));

		Flatsome_Child_Options::instance();
	}

	/**
	 * Load plugin text_domain.
	 *
	 * @since 1.0.0
	 */
	public function load_text_domain()
	{
		// load custom translation file for the parent theme
		load_theme_textdomain('flatsome', get_stylesheet_directory() . '/languages/parent');
		// load translation file for the child theme
		load_child_theme_textdomain('flatsome-child', get_stylesheet_directory() . '/languages');
	}

	public function enqueue_scripts()
	{
		// Css Files
		wp_enqueue_style('flatsome-child-theme', get_stylesheet_directory_uri() . '/assets/public/css/flatsome-child-theme.css', array(), self::$version);

		$font = Flatsome_Child_Options::get_option('font', 'typography');
		if (isset($font) && !empty($font)) {
			wp_enqueue_style('flatsome-child-' . $font . 'font', get_stylesheet_directory_uri() . '/assets/public/css/' . $font . '-font.css', array(), self::$version);
		}
	}

	public function admin_enqueue_scripts()
	{
		// Javascript Files
		wp_enqueue_script('flatsome-child', get_stylesheet_directory_uri() . '/assets/admin/js/flatsome-child.js', array('jquery'), self::$version, true);
	}

	/**
	 * Load child theme dependency files
	 *
	 * @since 1.0.0
	 */
	private function load_dependencies()
	{
		require_once get_stylesheet_directory() . '/inc/override_functions.php';
		require_once get_stylesheet_directory() . '/inc/Flatsome_Child_Options.php';
	}

	/**
	 * Main Class Instance.
	 */
	public static function instance()
	{
		if (is_null(self::$_instance)) {
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
function sahab_add_user_manager_column($columns)
{
	$columns['user_manager'] = 'مدیر مستقیم (اداره)';
	return $columns;
}
add_filter('manage_users_columns', 'sahab_add_user_manager_column');

// ۲. پر کردن دیتای ستون مدیر با استفاده از فیلد ACF
function sahab_show_user_manager_column_content($value, $column_name, $user_id)
{
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


/*=======================================================================
 =  ماژول بازنویسی پیوند یکتای گزارش‌ها بر اساس شماره اتوماسیون (automation_id)  =
 =======================================================================*/

/**
 * ۱. جایگزینی اتوماسیون با نامک نوشته در زمان فیلتر پیوند یکتا
 */
add_filter('post_link', 'sahab_custom_post_permalink', 10, 3);
function sahab_custom_post_permalink($permalink, $post, $leavename)
{
	if ($post->post_type === 'post') {
		$automation_id = get_post_meta($post->ID, 'automation_id', true);
		if (!empty($automation_id)) {
			// ساخت آدرس مستقیم به صورت sahab.test/12345/
			return home_url('/' . user_trailingslashit($automation_id));
		}
	}
	return $permalink;
}

/**
 * ۲. افزودن قانون رایت برای هدایت ساختار عددی به متادیتا
 */
add_action('init', 'sahab_custom_post_rewrite_rules');
function sahab_custom_post_rewrite_rules()
{
	// افزودن قانون جدید در بالاترین اولویت (top)
	add_rewrite_rule('^([0-9]+)/?$', 'index.php?meta_key=automation_id&meta_value=$matches[1]', 'top');
}

/**
 * ۳. ثبت متغیرهای سفارشی در کوئری
 */
add_filter('query_vars', 'sahab_add_custom_query_vars');
function sahab_add_custom_query_vars($vars)
{
	$vars[] = 'meta_key';
	$vars[] = 'meta_value';
	return $vars;
}

/**
 * ۴. بازنویسی WP_Query اصلی برای واکشی بر اساس شماره اتوماسیون
 */
add_action('pre_get_posts', 'sahab_parse_automation_id_request');
function sahab_parse_automation_id_request($query)
{
	if (!is_admin() && $query->is_main_query()) {
		$meta_key = $query->get('meta_key');
		$meta_value = $query->get('meta_value');

		if ($meta_key === 'automation_id' && !empty($meta_value)) {
			$query->set('post_type', 'post');
			$query->set('meta_query', array(
				array(
					'key' => 'automation_id',
					'value' => $meta_value,
					'compare' => '='
				)
			));

			// شبیه‌سازی لود تک‌پست برای جلوگیری از خطای ۴۰۴
			$query->is_single = true;
			$query->is_home = false;
			$query->is_archive = false;
		}
	}
}

/**
 * ۵. اصلاح قطعی عنوان تب مرورگر برای سیستم آدرس‌دهی اتوماسیون سحاب
 */
add_filter('document_title_parts', 'sahab_custom_automation_document_title', 9999);
add_filter('wp_title', 'sahab_custom_automation_wp_title', 9999, 3);

function sahab_custom_automation_document_title($title_parts)
{
	// بررسی متغیر کوئری بر اساس ساختار فعال روی دیتابیس شما
	$meta_key = get_query_var('meta_key');
	$meta_value = get_query_var('meta_value');

	if ($meta_key === 'automation_id' && !empty($meta_value)) {
		global $wp_query;
		if (!empty($wp_query->posts)) {
			$current_post = $wp_query->posts[0];
			// قرار دادن ترکیب "عنوان خبر + شماره اتوماسیون" در تب
			$title_parts['title'] = esc_html($current_post->post_title) . ' (' . esc_html($meta_value) . ')';
		} else {
			$title_parts['title'] = 'گزارش شماره ' . esc_html($meta_value);
		}
	}
	return $title_parts;
}

// این تابع برای سازگاری کامل با تم‌هایی است که از ساختارهای قدیمی‌تر عنوان استفاده می‌کنند
function sahab_custom_automation_wp_title($title, $sep, $seplocation)
{
	$meta_key = get_query_var('meta_key');
	$meta_value = get_query_var('meta_value');

	if ($meta_key === 'automation_id' && !empty($meta_value)) {
		global $wp_query;
		if (!empty($wp_query->posts)) {
			$current_post = $wp_query->posts[0];
			if ($seplocation === 'right') {
				return esc_html($current_post->post_title) . ' (' . esc_html($meta_value) . ') ' . $sep . ' ';
			} else {
				return ' ' . $sep . ' ' . esc_html($current_post->post_title) . ' (' . esc_html($meta_value) . ')';
			}
		}
	}
	return $title;
}

/**
 * ۶. ست کردن نهایی نمونه پست روی سیستم برای لود کامل کامپوننت‌های Flatsome
 */
add_action('wp', 'sahab_force_global_post_init');
function sahab_force_global_post_init()
{
	$meta_key = get_query_var('meta_key');
	if ($meta_key === 'automation_id') {
		global $wp_query, $post;
		if (!empty($wp_query->posts) && empty($post)) {
			$post = $wp_query->posts[0];
			setup_postdata($post);
		}
	}
}


// فراخوانی سایر متعلقات و فایل‌های هسته چایلد تم سحاب
require_once get_stylesheet_directory() . '/inc/comments-handler.php';
require_once get_stylesheet_directory() . '/inc/acf-automation.php';
require_once get_stylesheet_directory() . '/inc/jalali-datepicker.php';
require_once get_stylesheet_directory() . '/inc/admin-labels.php';
require_once get_stylesheet_directory() . '/inc/dashboard-core.php';
require_once get_stylesheet_directory() . '/inc/admin-customizer.php';
// لود کردن ماژول گزارشات تحلیلی سحاب به صورت ماژولار
require_once get_stylesheet_directory() . '/inc/sahab-bi-reporting.php';