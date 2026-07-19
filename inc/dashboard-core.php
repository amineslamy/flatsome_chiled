<?php
/**
 * پلتفرم سحاب - ماژول هسته و زیرساخت میز کار مرکزی (DataTables Backend)
 */

if (!function_exists('sahab_dashboard_should_enqueue_assets')) {
	/**
	 * بررسی هوشمند برای بارگذاری دارایی‌ها فقط در برگه داشبورد
	 */
	function sahab_dashboard_should_enqueue_assets()
	{
		if (is_admin() || !is_singular()) {
			return false;
		}

		$post = get_post();
		if (!$post instanceof WP_Post) {
			return false;
		}

		if (has_shortcode($post->post_content, 'sahab_dashboard')) {
			return true;
		}

		$dashboard_slugs = array('dashboard', 'sahab-dashboard', 'central-dashboard');
		return in_array($post->post_name, $dashboard_slugs, true);
	}
}

if (!function_exists('sahab_dashboard_enqueue_assets')) {
	/**
	 * بارگذاری استاندارد و مشروط کتابخانه‌ها و فایل‌های استایل/اسکریپت اختصاصی
	 */
	function sahab_dashboard_enqueue_assets()
	{
		if (!sahab_dashboard_should_enqueue_assets()) {
			return;
		}

		// لود فایل‌های اصلی جلالی دیت‌پیکر از پوشه ادمین/دارایی‌ها
		wp_enqueue_style('sahab-jalali-datepicker-css', get_stylesheet_directory_uri() . '/assets/admin/css/jalali-datepicker.min.css', array(), null);
		wp_enqueue_script('sahab-jalali-datepicker-js', get_stylesheet_directory_uri() . '/assets/admin/js/jalali-datepicker.min.js', array(), null, true);

		// لود دیتاتیبلز اصلی
		wp_enqueue_style('sahab-datatables-css', 'https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css', array(), null);
		wp_enqueue_script('sahab-datatables-js', 'https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js', array('jquery'), null, true);

		// لود فایل CSS تفکیک‌شده و فشرده سحاب
		wp_enqueue_style('sahab-dashboard-custom-css', get_stylesheet_directory_uri() . '/assets/public/css/sahab-dashboard.css', array(), '1.4.2');

		// لود فایل جاوااسکریپت داشبورد با وابستگی به دیت‌پیکر
		wp_enqueue_script('sahab-dashboard-js', get_stylesheet_directory_uri() . '/assets/public/js/sahab-dashboard.js', array('jquery', 'sahab-datatables-js', 'sahab-jalali-datepicker-js'), '1.4.2', true);

		// انتقال امن آدرس AJAX به جاوااسکریپت
		wp_localize_script('sahab-dashboard-js', 'sahab_dashboard_vars', array(
			'ajax_url' => admin_url('admin-ajax.php'),
		));
	}
	add_action('wp_enqueue_scripts', 'sahab_dashboard_enqueue_assets');
}

if (!function_exists('sahab_global_auth_redirect')) {
	/**
	 * هدایت هوشمند بر اساس وضعیت احراز هویت برای داشبورد و صفحه اصلی
	 */
	function sahab_global_auth_redirect()
	{
		// If the user is filtering by subject or date, allow them to view the front page archive
		if (isset($_GET['subject']) || isset($_GET['reg_date']) || isset($_GET['event_date'])) {
			return;
		}

		if (is_front_page() || is_home()) {
			if (is_user_logged_in()) {
				wp_safe_redirect(home_url('/dashboard/'));
				exit;
			}

			wp_safe_redirect(wp_login_url(home_url('/dashboard/')));
			exit;
		}

		if (!is_user_logged_in()) {
			$post = get_post();
			if ($post instanceof WP_Post && (is_page('dashboard') || has_shortcode($post->post_content, 'sahab_dashboard'))) {
				wp_safe_redirect(wp_login_url(get_permalink($post)));
				exit;
			}
		}
	}
	add_action('template_redirect', 'sahab_global_auth_redirect');
}

if (!function_exists('sahab_dashboard_shortcode')) {
	/**
	 * رندر شورت‌کد اسکلت جدول ۱۱ ستونه سحاب به همراه فیلترهای بازه زمانی
	 */
	function sahab_dashboard_shortcode()
	{
		ob_start();
		?>
		<div class="sahab-dashboard-wrapper" dir="rtl">
			<form id="sahab-dashboard-filters" method="get"
				action="<?php echo esc_url(remove_query_arg(array('f_id', 'f_case', 'f_type', 'f_subject', 'f_expert', 'f_author', 'f_notes', 'f_reg_from', 'f_reg_to'))); ?>"
				style="display: flex; align-items: center; justify-content: flex-start; flex-wrap: wrap; gap: 6px; background: #f8fafc; padding: 8px; border-radius: 6px; border: 1px solid #e2e8f0; direction: rtl; margin-bottom: 15px; font-size: 12px; overflow: hidden;">

				<div id="sahab_custom_length" style="width: 45px; margin: 0;"></div>
				<div id="sahab_custom_search" style="flex: 1 1 130px; min-width: 90px; margin: 0;"></div>

				<!-- فیلترهای جدید تاریخ ثبت در ابتدای ردیف فیلترها -->
				<input type="text" name="f_reg_from" id="filter_reg_from" data-jdp
					value="<?php echo esc_attr(isset($_GET['f_reg_from']) ? sanitize_text_field(wp_unslash($_GET['f_reg_from'])) : ''); ?>"
					placeholder="از تاریخ ثبت" autocomplete="off"
					style="max-width: 95px; width: 100%; padding: 4px; border-radius: 4px; border: 1px solid #cbd5e1; height: 30px; font-size: 11px; margin: 0; text-align: center;">

				<input type="text" name="f_reg_to" id="filter_reg_to" data-jdp
					value="<?php echo esc_attr(isset($_GET['f_reg_to']) ? sanitize_text_field(wp_unslash($_GET['f_reg_to'])) : ''); ?>"
					placeholder="تا تاریخ ثبت" autocomplete="off"
					style="max-width: 95px; width: 100%; padding: 4px; border-radius: 4px; border: 1px solid #cbd5e1; height: 30px; font-size: 11px; margin: 0; text-align: center;">

				<input type="text" name="f_id" id="filter_id"
					value="<?php echo esc_attr(isset($_GET['f_id']) ? sanitize_text_field(wp_unslash($_GET['f_id'])) : ''); ?>"
					placeholder="شماره" onkeypress="if ( event.key === 'Enter' ) { this.form.submit(); }"
					style="max-width: 65px; width: 100%; padding: 4px; border-radius: 4px; border: 1px solid #cbd5e1; height: 30px; font-size: 11px; margin: 0;">

				<?php
				$news_type_field = function_exists('acf_get_field') ? acf_get_field('news_type') : false;
				$news_type_choices = ($news_type_field && !empty($news_type_field['choices'])) ? $news_type_field['choices'] : array(
					'open' => 'آشکار',
					'official' => 'رسمی',
					'technical' => 'فنی',
					'cyber' => 'سایبری',
					'hidden' => 'پنهان',
					'ravi' => 'راوی',
				);
				?>

				<select name="f_case" id="filter_case" onchange="this.form.submit();"
					style="max-width: 110px; width: 100%; padding: 4px; border-radius: 4px; border: 1px solid #cbd5e1; height: 30px; font-size: 11px; margin: 0;">
					<option value="">کیس</option>
					<?php foreach (get_categories() as $cat): ?>
						<option value="<?php echo esc_attr($cat->name); ?>" <?php selected(isset($_GET['f_case']) ? sanitize_text_field(wp_unslash($_GET['f_case'])) : '', $cat->name); ?>>
							<?php echo esc_html($cat->name); ?>
						</option>
					<?php endforeach; ?>
				</select>

				<select name="f_type" id="filter_type" onchange="this.form.submit();"
					style="max-width: 110px; width: 100%; padding: 4px; border-radius: 4px; border: 1px solid #cbd5e1; height: 30px; font-size: 11px; margin: 0;">
					<option value="">نوع خبر</option>
					<?php foreach ($news_type_choices as $value => $label): ?>
						<option value="<?php echo esc_attr($value); ?>" <?php selected(isset($_GET['f_type']) ? sanitize_text_field(wp_unslash($_GET['f_type'])) : '', $value); ?>>
							<?php echo esc_html($label); ?>
						</option>
					<?php endforeach; ?>
				</select>

				<select name="f_subject" id="filter_subject" onchange="this.form.submit();"
					style="max-width: 110px; width: 100%; padding: 4px; border-radius: 4px; border: 1px solid #cbd5e1; height: 30px; font-size: 11px; margin: 0;">
					<option value="">موضوع</option>
					<?php
					$field = function_exists('acf_get_field') ? acf_get_field('subject') : false;
					$current_subject = isset($_GET['f_subject']) ? sanitize_text_field(wp_unslash($_GET['f_subject'])) : '';
					if ($field && !empty($field['choices'])):
						foreach ($field['choices'] as $value => $label):
							?>
							<option value="<?php echo esc_attr($value); ?>" <?php selected($current_subject, $value); ?>>
								<?php echo esc_html($label); ?>
							</option>
						<?php endforeach; endif; ?>
				</select>

				<input type="text" name="f_expert" id="filter_expert"
					value="<?php echo esc_attr(isset($_GET['f_expert']) ? sanitize_text_field(wp_unslash($_GET['f_expert'])) : ''); ?>"
					placeholder="کارشناس" onkeypress="if ( event.key === 'Enter' ) { this.form.submit(); }"
					style="max-width: 110px; width: 100%; padding: 4px; border-radius: 4px; border: 1px solid #cbd5e1; height: 30px; font-size: 11px; margin: 0;">

				<input type="text" name="f_author" id="filter_author"
					value="<?php echo esc_attr(isset($_GET['f_author']) ? sanitize_text_field(wp_unslash($_GET['f_author'])) : ''); ?>"
					placeholder="ثبت کننده" onkeypress="if ( event.key === 'Enter' ) { this.form.submit(); }"
					style="max-width: 110px; width: 100%; padding: 4px; border-radius: 4px; border: 1px solid #cbd5e1; height: 30px; font-size: 11px; margin: 0;">

				<select name="f_notes" id="filter_notes" onchange="this.form.submit();"
					style="max-width: 110px; width: 100%; padding: 4px; border-radius: 4px; border: 1px solid #cbd5e1; height: 30px; font-size: 11px; margin: 0;">
					<option value="">پی‌نوشت</option>
					<option value="theory" <?php selected(isset($_GET['f_notes']) ? sanitize_text_field(wp_unslash($_GET['f_notes'])) : '', 'theory'); ?>>نظریه</option>
					<option value="rewrite" <?php selected(isset($_GET['f_notes']) ? sanitize_text_field(wp_unslash($_GET['f_notes'])) : '', 'rewrite'); ?>>بازنویسی</option>
					<option value="note" <?php selected(isset($_GET['f_notes']) ? sanitize_text_field(wp_unslash($_GET['f_notes'])) : '', 'note'); ?>>ملاحظه</option>
					<option value="misc" <?php selected(isset($_GET['f_notes']) ? sanitize_text_field(wp_unslash($_GET['f_notes'])) : '', 'misc'); ?>>متفرقه</option>
				</select>

				<button id="clear_all_filters" type="button"
					style="padding: 5px 10px; background: #ef4444; color: #fff; border-radius: 4px; border: none; cursor: pointer; height: 30px; font-weight: bold; white-space: nowrap; font-size: 11px; margin: 0;">حذف
					فیلترها</button>
			</form>
			<table id="sahab-main-dashboard" class="display responsive nowrap" style="width:100%;">
				<thead>
					<tr>
						<th>شماره</th>
						<th>عنوان خبر</th>
						<th>کیس</th>
						<th>موضوع</th>
						<th>نوع خبر</th>
						<th>ارزیابی / ارجحیت</th>
						<th>کارشناس</th>
						<th>ثبت کننده</th>
						<th>تاریخ وقوع</th>
						<th>تاریخ ثبت</th>
						<th>پی نوشت ها</th>
						<th>عملیات</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td colspan="12">در حال بارگذاری و آماده‌سازی اطلاعات میز کار سحاب...</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="sahab-delete-modal" class="sahab-modal-overlay" style="display:none;">
			<div class="sahab-modal-content" dir="rtl">
				<h3>⚠️ هشدار امنیتی؛ حذف قطعی خبر</h3>
				<p>شما در حال حذف کامل این خبر از سامانه سحاب هستید. این عملیات کاملاً غیرقابل بازگشت بوده و خبر دیگر قابل
					دسترسی نخواهد بود.</p>
				<p class="sahab-modal-instruction">لطفاً برای تایید، کلمه <strong style="color:#dc2626;">delete</strong> را در
					کادر زیر تایپ کنید:</p>
				<div id="sahab-delete-modal-error"
					style="color:#dc2626; font-size:12px; margin-bottom:10px; font-weight:bold; display:none;"></div>
				<input type="text" id="sahab-delete-confirm-input" placeholder="تایپ کلمه delete..." autocomplete="off" />
				<div class="sahab-modal-footer-actions">
					<button type="button" id="sahab-modal-cancel-btn" class="sahab-modal-btn btn-secondary">انصراف</button>
					<button type="button" id="sahab-modal-confirm-btn" class="sahab-modal-btn btn-danger" disabled>حذف
						قطعی</button>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
	add_shortcode('sahab_dashboard', 'sahab_dashboard_shortcode');
}

// هوک‌های رسمی اتصال دیتاتیبلز به موتورخانه داتا
add_action('wp_ajax_sahab_get_dashboard_data', 'flatsome_child_get_dashboard_data');
add_action('wp_ajax_nopriv_sahab_get_dashboard_data', 'flatsome_child_get_dashboard_data');
add_action('wp_ajax_sahab_delete_dashboard_post', 'flatsome_child_delete_dashboard_post');

if (!function_exists('sahab_dashboard_translate_value')) {
	/**
	 * مترجم هوشمند مقادیر و اسلاگ‌های انگلیسی ACF به لیبل‌های فارسی
	 */
	function sahab_dashboard_translate_value($value, $field_name, $post_id)
	{
		if (empty($value) || array() === $value) {
			return '---';
		}

		if (function_exists('get_field_object')) {
			$field_object = get_field_object($field_name, $post_id, false);
			if (is_array($field_object) && !empty($field_object['choices'])) {
				if (is_array($value)) {
					$labels = array();
					foreach ($value as $item) {
						$item_key = (string) $item;
						$labels[] = isset($field_object['choices'][$item_key]) ? (string) $field_object['choices'][$item_key] : $item_key;
					}
					return implode(' | ', array_filter($labels));
				}
				$value_key = (string) $value;
				if (isset($field_object['choices'][$value_key])) {
					return (string) $field_object['choices'][$value_key];
				}
			}
		}

		$normalized = strtolower(str_replace(array('_', '-', ' '), '', (string) $value));
		$translations = array(
			'urgent' => 'فوری',
			'important' => 'مهم',
			'high' => 'مهم',
			'normal' => 'عادی',
			'low' => 'کم',
			'medium' => 'متوسط',
			'valid' => 'صحت دارد',
			'underreview' => 'در حال بررسی',
			'under_review' => 'در حال بررسی',
			'politicalclerics' => 'روحانیون سیاسی',
			'institutions' => 'موسسات',
		);

		return isset($translations[$normalized]) ? $translations[$normalized] : (string) $value;
	}
}

if (!function_exists('flatsome_child_get_dashboard_comment_summary')) {
	/**
	 * استخراج آمار عددی دقیق پی‌نوشت‌ها به تفکیک دسته‌بندی برای رندر دایره‌ها
	 */
	function flatsome_child_get_dashboard_comment_summary($post_id)
	{
		$summary = array('note' => 0, 'theory' => 0, 'rewrite' => 0, 'misc' => 0);
		$comments = get_comments(array('post_id' => absint($post_id), 'status' => 'approve', 'fields' => 'ids'));

		foreach ($comments as $comment_id) {
			$type = get_comment_meta($comment_id, 'comment_type', true);
			if (in_array($type, array('note', 'theory', 'rewrite'), true)) {
				$summary[$type]++;
			} else {
				$summary['misc']++;
			}
		}
		return $summary;
	}
}

if (!function_exists('flatsome_child_get_dashboard_data')) {
	/**
	 * موتورخانه توزیع دیتای فرآوری شده اخبار به دیتاتیبلز همراه با فیلتر محدوده تاریخ ثبت
	 */
	function flatsome_child_get_dashboard_data()
	{
		if (!is_user_logged_in() || !current_user_can('read')) {
			wp_send_json_error(array('message' => 'سطح دسترسی غیرمجاز.'), 403);
		}

		$filter_id = isset($_REQUEST['f_id']) ? sanitize_text_field(wp_unslash($_REQUEST['f_id'])) : '';
		$filter_case = isset($_REQUEST['f_case']) ? sanitize_text_field(wp_unslash($_REQUEST['f_case'])) : '';
		$filter_subject = isset($_REQUEST['f_subject']) ? sanitize_text_field(wp_unslash($_REQUEST['f_subject'])) : '';
		$filter_type = isset($_REQUEST['f_type']) ? sanitize_text_field(wp_unslash($_REQUEST['f_type'])) : '';
		$filter_expert = isset($_REQUEST['f_expert']) ? sanitize_text_field(wp_unslash($_REQUEST['f_expert'])) : '';
		$filter_author = isset($_REQUEST['f_author']) ? sanitize_text_field(wp_unslash($_REQUEST['f_author'])) : '';
		$filter_notes = isset($_REQUEST['f_notes']) ? sanitize_text_field(wp_unslash($_REQUEST['f_notes'])) : '';

		// دریافت مقادیر بازه زمانی جدید
		$filter_reg_from = isset($_REQUEST['f_reg_from']) ? sanitize_text_field(wp_unslash($_REQUEST['f_reg_from'])) : '';
		$filter_reg_to = isset($_REQUEST['f_reg_to']) ? sanitize_text_field(wp_unslash($_REQUEST['f_reg_to'])) : '';

		$news_type_field = function_exists('acf_get_field') ? acf_get_field('news_type') : false;
		$news_type_choices = ($news_type_field && !empty($news_type_field['choices'])) ? $news_type_field['choices'] : array(
			'open' => 'آشکار',
			'official' => 'رسمی',
			'technical' => 'فنی',
			'cyber' => 'سایبری',
			'hidden' => 'پنهان',
			'ravi' => 'راوی',
		);

		$query_args = array(
			'post_type' => 'post',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'orderby' => 'date',
			'order' => 'DESC',
			'no_found_rows' => true,
			'ignore_sticky_posts' => true,
		);

		$meta_query = array('relation' => 'AND');

		if ($filter_case) {
			$category = get_term_by('name', $filter_case, 'category');
			if ($category && !is_wp_error($category)) {
				$query_args['cat'] = (int) $category->term_id;
			}
		}

		if ($filter_subject) {
			$meta_query[] = array(
				'key' => 'subject',
				'value' => '"' . $filter_subject . '"',
				'compare' => 'LIKE',
			);
		}

		if ($filter_type) {
			$meta_query[] = array(
				'key' => 'news_type',
				'value' => $filter_type,
				'compare' => '=',
			);
		}

		// اعمال فیلتر هوشمند بازه زمانی بر اساس ساختار متادیتا سایه پروژه (sahab_reg_date_shamsi)
		if (!empty($filter_reg_from) || !empty($filter_reg_to)) {
			// ساختار ذخیره‌سازی با اسلش است: 1405/04/13
			$db_from = !empty($filter_reg_from) ? str_replace('-', '/', $filter_reg_from) : '1300/01/01';
			$db_to = !empty($filter_reg_to) ? str_replace('-', '/', $filter_reg_to) : '1500/12/29';

			$meta_query[] = array(
				'key' => 'sahab_reg_date_shamsi',
				'value' => array($db_from, $db_to),
				'compare' => 'BETWEEN',
				'type' => 'CHAR' // مقایسه متنی با توجه به فرمت ایزو جلالی Y/m/d کاملاً دقیق کار می‌کند
			);
		}

		if (count($meta_query) > 1) {
			$query_args['meta_query'] = $meta_query;
		}

		$query = new WP_Query($query_args);
		$result_array = array();

		if ($query->have_posts()) {
			while ($query->have_posts()) {
				$query->the_post();
				$post_id = get_the_ID();

				$automation_id = get_post_meta($post_id, 'automation_id', true);
				$automation_id = is_scalar($automation_id) ? (string) $automation_id : '';

				if ($filter_id) {
					$needle = trim($filter_id);
					$match_id = false;

					if ($automation_id !== '' && stripos($automation_id, $needle) !== false) {
						$match_id = true;
					}

					if (!$match_id && preg_match('/^AUTO-(\d+)$/i', $needle, $matches)) {
						$match_id = (int) $matches[1] === $post_id;
					}

					if (!$match_id && preg_match('/^\d+$/', $needle)) {
						$match_id = (int) $needle === $post_id || ($automation_id !== '' && (int) $needle === (int) $automation_id);
					}

					if (!$match_id) {
						continue;
					}
				}

				$author_id = (int) get_post_field('post_author', $post_id);
				$expert_name = get_the_author_meta('display_name', $author_id);
				$creator_id = get_post_meta($post_id, 'news_creator_id', true);
				$creator_user = $creator_id ? get_userdata((int) $creator_id) : false;
				$creator_name = ($creator_user && !empty($creator_user->display_name)) ? $creator_user->display_name : $expert_name;

				if ($filter_expert && stripos($expert_name, $filter_expert) === false) {
					continue;
				}

				if ($filter_author && stripos($creator_name, $filter_author) === false) {
					continue;
				}

				$comments_summary = flatsome_child_get_dashboard_comment_summary($post_id);
				if ($filter_notes) {
					$valid_notes = array('note', 'theory', 'rewrite', 'misc');
					if (in_array($filter_notes, $valid_notes, true)) {
						if (empty($comments_summary[$filter_notes])) {
							continue;
						}
					}
				}

				$final_num = !empty($automation_id) ? (string) $automation_id : sprintf('AUTO-%d', $post_id);

				$categories = get_the_category($post_id);
				$case_label = '---';
				if (!empty($categories) && !is_wp_error($categories)) {
					$case_links = array();
					foreach ($categories as $category) {
						if ('uncategorized' !== $category->slug && 'دسته-بندی-نشده' !== $category->name) {
							$case_links[] = sprintf(
								'<a href="%s" class="sahab-table-link">%s</a>',
								esc_url(get_category_link($category->term_id)),
								esc_html($category->name)
							);
						}
					}
					if (empty($case_links) && isset($categories[0])) {
						$case_links[] = sprintf(
							'<a href="%s" class="sahab-table-link">%s</a>',
							esc_url(get_category_link($categories[0]->term_id)),
							esc_html($categories[0]->name)
						);
					}
					$case_label = implode(' | ', $case_links);
				}

				$subject_val = get_post_meta($post_id, 'subject', true);
				if (empty($subject_val) && function_exists('get_field')) {
					$subject_val = get_field('subject', $post_id);
				}
				$subject_label = sahab_dashboard_translate_value($subject_val, 'subject', $post_id);
				$subject_links = array();
				if (is_array($subject_val)) {
					foreach ($subject_val as $subject_item) {
						$subject_item_raw = (string) $subject_item;
						$subject_item_label = sahab_dashboard_translate_value($subject_item_raw, 'subject', $post_id);
						$subject_links[] = sprintf(
							'<a href="%s" class="sahab-table-link">%s</a>',
							esc_url(home_url('/?subject=' . urlencode($subject_item_raw))),
							esc_html($subject_item_label)
						);
					}
				} elseif (!empty($subject_val)) {
					$subject_item_raw = (string) $subject_val;
					$subject_item_label = sahab_dashboard_translate_value($subject_item_raw, 'subject', $post_id);
					$subject_links[] = sprintf(
						'<a href="%s" class="sahab-table-link">%s</a>',
						esc_url(home_url('/?subject=' . urlencode($subject_item_raw))),
						esc_html($subject_item_label)
					);
				}
				$subject_label = !empty($subject_links) ? implode(' | ', $subject_links) : $subject_label;

				$type_val = get_post_meta($post_id, 'news_type', true);
				if (empty($type_val) && function_exists('get_field')) {
					$type_val = get_field('news_type', $post_id);
				}
				$type_label = isset($news_type_choices[$type_val]) ? $news_type_choices[$type_val] : '---';

				$eval_val = get_post_meta($post_id, 'evaluation', true);
				$prio_val = get_post_meta($post_id, 'priority', true);
				$eval_label = sahab_dashboard_translate_value($eval_val, 'evaluation', $post_id);
				$prio_label = sahab_dashboard_translate_value($prio_val, 'priority', $post_id);

				$eval_summary = array_filter(array($eval_label, $prio_label));
				$final_evaluation = !empty($eval_summary) ? implode(' | ', $eval_summary) : 'عادی';

				$expert_link = sprintf('<a href="%s" target="_blank" class="sahab-user-link">%s</a>', esc_url(get_author_posts_url($author_id)), esc_html($expert_name));
				$creator_link = sprintf('<a href="%s" target="_blank" class="sahab-user-link">%s</a>', esc_url(get_author_posts_url($creator_id ? (int) $creator_id : $author_id)), esc_html($creator_name));

				$event_date_raw = get_post_meta($post_id, 'event_date', true);
				$reg_shamsi = get_post_meta($post_id, 'sahab_reg_date_shamsi', true);
				$event_date_display = !empty($event_date_raw) ? (string) $event_date_raw : '---';
				$event_date_link = '---';
				if (!empty($event_date_raw)) {
					$sanitized_event_date = str_replace('/', '-', (string) $event_date_raw);
					$event_date_link = sprintf(
						'<a href="%s" class="sahab-table-link">%s</a>',
						esc_url(home_url('/?event_date=' . $sanitized_event_date)),
						esc_html($event_date_display)
					);
				}

				global $post;
				$display_reg_date = '---';
				$reg_date_link = '---';
				if (!empty($reg_shamsi)) {
					$display_reg_date = (string) $reg_shamsi;
				} elseif (!empty($post->post_date) && function_exists('parsidate')) {
					$display_reg_date = parsidate('Y/m/d', $post->post_date, 'eng');
				}
				if ($display_reg_date !== '---' && !empty($display_reg_date)) {
					$sanitized_reg_date = str_replace('/', '-', $display_reg_date);
					$reg_date_link = sprintf(
						'<a href="%s" class="sahab-table-link">%s</a>',
						esc_url(home_url('/?reg_date=' . $sanitized_reg_date)),
						esc_html($display_reg_date)
					);
				}

				$actions_html = '<div class="sahab-dashboard-actions">'
					. '<div class="sahab-actions-top-row">'
					. '<a href="' . esc_url(get_permalink($post_id)) . '" target="_blank" class="sahab-btn-svg sahab-btn-view" title="مشاهده خبر">'
					. '<svg viewBox="0 0 24 24" width="18" height="18" stroke="#0284c7" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>'
					. '</a>'
					. '<a href="' . esc_url(admin_url('post.php?post=' . absint($post_id) . '&action=edit')) . '" target="_blank" class="sahab-btn-svg sahab-btn-edit" title="ویرایش خبر">'
					. '<svg viewBox="0 0 24 24" width="18" height="18" stroke="#0f766e" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4Z"></path></svg>'
					. '</a>'
					. '</div>'
					. '<button type="button" class="sahab-btn-svg sahab-btn-delete" title="حذف خبر" data-id="' . absint($post_id) . '">'
					. '<svg viewBox="0 0 24 24" width="18" height="18" stroke="#dc2626" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>'
					. '</button>'
					. '</div>';

				$result_array[] = array(
					'automation_id' => $final_num,
					'title' => get_the_title($post_id),
					'case' => $case_label,
					'subject' => $subject_label,
					'evaluation' => $final_evaluation,
					'expert' => $expert_link,
					'creator' => $creator_link,
					'event_date' => $event_date_link,
					'publish_date' => $reg_date_link,
					'comments_count_summary' => flatsome_child_get_dashboard_comment_summary($post_id),
					'news_type' => esc_html($type_label),
					'permalink' => get_permalink($post_id),
					'actions' => $actions_html,
				);
			}
		}

		wp_reset_postdata();
		echo wp_json_encode(array('data' => $result_array));
		wp_die();
	}
}

if (!function_exists('flatsome_child_delete_dashboard_post')) {
	/**
	 * حذف امن خبر از دیتابیس از طریق AJAX و حذف از دیتاتیبلز
	 */
	function flatsome_child_delete_dashboard_post()
	{
		if (!is_user_logged_in()) {
			wp_send_json_error(array('message' => 'ابتدا باید وارد سیستم شوید.'), 403);
		}

		$post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;

		if (!$post_id || get_post_type($post_id) !== 'post') {
			wp_send_json_error(array('message' => 'خبر مورد نظر یافت نشد.'));
		}

		if (!current_user_can('delete_post', $post_id)) {
			wp_send_json_error(array('message' => 'خطای امنیتی: شما سطح دسترسی لازم برای حذف این خبر را ندارید.'), 403);
		}

		$deleted = wp_delete_post($post_id, true);
		if ($deleted) {
			wp_send_json_success(array('message' => 'خبر با موفقیت و به صورت قطعی حذف شد.'));
		} else {
			wp_send_json_error(array('message' => 'خطا در فرآیند حذف محلی دیتابیس.'));
		}
	}
}

function sahab_register_custom_die_handler($handler)
{
	return function ($message, $title, $args) {
		if (is_admin() && strpos($message, 'اجازهٔ ویرایش') !== false) {
			include get_stylesheet_directory() . '/templates/permission-denied.php';
			exit;
		}
		return _default_wp_die_handler($message, $title, $args);
	};
}
add_filter('wp_die_handler', 'sahab_register_custom_die_handler', 10);

if (!function_exists('flatsome_child_generate_shadow_reg_date')) {
	/**
	 * Generate a shadow Jalali registration date and save as post meta
	 */
	function flatsome_child_generate_shadow_reg_date($post_id)
	{
		if (get_post_type($post_id) !== 'post')
			return;
		if (get_post_meta($post_id, 'sahab_reg_date_shamsi', true))
			return;

		$post = get_post($post_id);
		$jalali_now = parsidate('Y/m/d', $post->post_date, 'eng');
		update_post_meta($post_id, 'sahab_reg_date_shamsi', $jalali_now);
	}
	add_action('acf/save_post', 'flatsome_child_generate_shadow_reg_date', 20);
}

/**
 * Handle homepage query filters for subject, event_date and reg_date
 */
function sahab_handle_homepage_query_filters($query)
{
	if (is_admin() || !$query->is_main_query()) {
		return;
	}

	$meta_query = array('relation' => 'AND');

	if (!empty($_GET['reg_date'])) {
		$reg_date = sanitize_text_field(wp_unslash($_GET['reg_date']));
		$db_reg_date = str_replace('-', '/', $reg_date);
		$meta_query[] = array(
			'key' => 'sahab_reg_date_shamsi',
			'value' => $db_reg_date,
			'compare' => '='
		);
	}

	if (!empty($_GET['event_date'])) {
		$event_date = sanitize_text_field(wp_unslash($_GET['event_date']));
		$db_event_date = str_replace('-', '/', $event_date);
		$meta_query[] = array(
			'key' => 'event_date',
			'value' => $db_event_date,
			'compare' => '='
		);
	}

	if (!empty($_GET['subject'])) {
		$subject = sanitize_text_field(wp_unslash($_GET['subject']));
		$meta_query[] = array(
			'key' => 'subject',
			'value' => '"' . $subject . '"',
			'compare' => 'LIKE'
		);
	}

	if (!empty($_GET['category_filter'])) {
		$query->set('category_name', sanitize_text_field(wp_unslash($_GET['category_filter'])));
	}

	if (count($meta_query) > 1) {
		$query->set('meta_query', $meta_query);
	}
}
add_action('pre_get_posts', 'sahab_handle_homepage_query_filters');