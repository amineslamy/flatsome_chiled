<?php
/**
 * Legacy hardcoded event metabox removed in favor of the ACF field.
 */

if ( ! function_exists( 'sahab_display_dates' ) ) {
	function sahab_display_dates() {
		// گرفتن تاریخ ثبت به صورت شمسی خام از وردپرس
		$date_registered = get_the_date('Y/m/d');
		$date_event      = get_post_meta( get_the_ID(), 'event_date', true );

		echo '<div class="sahab-meta-dates" style="direction:rtl; text-align:right; font-size:12px; color:#666;">';
		echo '<span>📅 تاریخ ثبت: ' . esc_html( $date_registered ) . '</span>';

		if ( ! empty( $date_event ) ) {
			echo ' <span style="margin: 0 8px; color: #ccc;">|</span> <span>⏱️ تاریخ وقوع: ' . esc_html( $date_event ) . '</span>';
		}

		echo '</div>';
	}
}

	/**
	 * Enqueue a lightweight Persian/Jalali datepicker on post edit screens (post.php, post-new.php).
	 * Loads minimal CDN assets and initializes the picker on the `event_date` input.
	 */
	if ( ! function_exists( 'flatsome_child_admin_enqueue_datepicker' ) ) {
		function flatsome_child_admin_enqueue_datepicker( $hook ) {
			// Only enqueue on post edit / add screens in admin
			if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
				return;
			}

			// Only enqueue the CSS for the datepicker in admin. JavaScript files are loaded inline in the metabox render callback.
			$base = get_stylesheet_directory_uri() . '/assets/admin';

			wp_enqueue_style( 'persian-datepicker-css', $base . '/css/persian-datepicker.min.css', array(), '1.2.0' );

		}
		add_action( 'admin_enqueue_scripts', 'flatsome_child_admin_enqueue_datepicker' );
	}

add_action('wp_footer', 'sahab_inject_event_date_styles_and_script');
function sahab_inject_event_date_styles_and_script() {
    // فقط در صفحات عمومی اجرا شود
    if (is_admin()) return;
    
    // گرفتن پست‌های صفحه جاری که تاریخ وقوع دارند و ساخت یک آبجکت دیتای کوچک
    $mapped_dates = [];
    global $wp_query;
    if (have_posts()) {
        while (have_posts()) {
            the_post();
            $ev_date = get_post_meta(get_the_ID(), 'event_date', true);
            if (!empty($ev_date)) {
                $mapped_dates[get_the_ID()] = esc_html($ev_date);
            }
        }
        wp_reset_postdata();
    }

    if (empty($mapped_dates)) return;
    ?>
    <script>
    jQuery(document).ready(function($) {
        var eventDates = <?php echo json_encode($mapped_dates); ?>;
        
        // پیدا کردن کارت‌های وبلاگ و صفحات داخلی در فلتسام
        $('.post-item, .box-text, .entry-header, .blog-post').each(function() {
            var $card = $(this);
            // پیدا کردن آی‌دی پست از روی کلاس‌های وردپرس (مثل post-2849)
            var classList = $card.attr('class') || '';
            if ($card.closest('article').length) {
                classList += ' ' + $card.closest('article').attr('class');
            }
            
            var match = classList.match(/post-(\d+)/);
            if (match && match[1]) {
                var postId = match[1];
                if (eventDates[postId]) {
                    // ابتدا مطمئن می‌شویم که این کارت قبلاً تاریخ وقوع را دریافت نکرده است
                    if (!$card.find('.sahab-final-ev').length) {
                        var $meta = $card.find('header .entry-meta, .post-meta, .entry-meta-bar').first();
                        if ($meta.length) {
                            $meta.append('<span class="sahab-final-ev" style="color: #d9534f; font-weight: bold; margin-right: 5px; margin-left: 5px;"> | ⏱️ وقوع: ' + eventDates[postId] + '</span>');
                        }
                    }
                }
            }
        });
    });
    </script>
    <?php
}

add_filter('get_search_form', 'sahab_add_advanced_search_link_to_form', 99);
function sahab_add_advanced_search_link_to_form($form) {
    // ساخت لینک به برگه جستجوی پیشرفته (آدرس برگه را بعدا با نامک advanced-search خواهیم ساخت)
    $advanced_search_url = home_url('/advanced-search/');
    
    $link_html = '<div class="advanced-search-link-wrapper" style="margin-top: 8px; text-align: right; direction: rtl;">';
    $link_html .= '<a href="' . esc_url($advanced_search_url) . '" style="font-size: 13px; color: #d9534f; font-weight: bold; text-decoration: underline;">⚙️ ورود به صفحه جستجوی پیشرفته</a>';
    $link_html .= '</div>';
    
    // تزریق لینک درست قبل از بسته شدن تگ فرم
    return str_replace('</form>', $link_html . '</form>', $form);
}
 
add_action('wp_enqueue_scripts', 'sahab_enqueue_datepicker_in_frontend');
function sahab_enqueue_datepicker_in_frontend() {
    if (is_page_template('page-advanced-search.php') || is_page('advanced-search')) {
        // لود فایل استایل اصلاح شده
        wp_enqueue_style('jalali-datepicker-css', get_stylesheet_directory_uri() . '/assets/admin/css/jalali-datepicker.min.css');
        
        // لود فایل جاوااسکریپت دیت‌پیکر کمالی
        wp_enqueue_script('jalali-datepicker-js', get_stylesheet_directory_uri() . '/assets/admin/js/jalali-datepicker.min.js', array(), null, true);
    }
}

/**
 * بازنویسی متای پست‌ها برای سیستم سحاب
 * نمایش تاریخ وقوع حادثه، تاریخ آخرین به‌روزرسانی شمسی و دکمه ویرایش سریع
 */
function flatsome_posted_on()
{
    $time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
    if (get_the_time('U') !== get_the_modified_time('U')) {
        $time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
    }

    $time_string = sprintf(
        $time_string,
        esc_attr(get_the_date('c')),
        esc_html(get_the_date()),
        esc_attr(get_the_modified_date('c')),
        esc_html(get_the_modified_date())
    );

    // ۱. واکشی تاریخ وقوع حادثه از متادیتا
    $date_event = get_post_meta(get_the_ID(), 'event_date', true);
    $event_html = '';
    if (!empty($date_event)) {
        $event_html = ' <span style="margin: 0 6px; color: #ccc;">|</span> <span class="sahab-event-meta" style="color: #d9534f; font-weight: bold;">⏱️ وقوع: ' . esc_html($date_event) . '</span>';
    }

    // ۲. واکشی یا تولید تاریخ آخرین به‌روزرسانی
    $last_modified = get_post_meta(get_the_ID(), 'last_modified', true);
    $display_modified = '';

    if (!empty($last_modified)) {
        // اگر فیلد متای سیستم سینک پر بود
        $display_modified = $last_modified;
        if (function_exists('jdate')) {
            $timestamp = strtotime($last_modified);
            if ($timestamp) {
                $display_modified = jdate('Y/m/d ساعت H:i', $timestamp);
            }
        }
    } else {
        // بلاک جایگزین اصلاح شده با اصلاح اختلاف ساعت تهران (تایم‌زون وردپرس)
        $display_modified = get_the_modified_date('Y/m/d') . ' ساعت ' . get_post_modified_time('H:i', false, get_the_ID(), true);
    }

    $modified_html = ' <span style="margin: 0 6px; color: #ccc;">|</span> <span class="sahab-modified-meta" style="color: #08a12e; font-weight: bold;">🔄 به‌روزرسانی: ' . esc_html($display_modified) . '</span> <span style="margin: 0 6px; color: #ccc;">|</span>';
    // ۳. خروجی نهایی متای فلتسام
    echo sprintf(
        /* translators: %1$s: post date, %2$s: post author */
        wp_kses_post(_x('<span class="posted-on">انتشار در %1$s</span> <span class="byline">کارشناس: %2$s</span>', 'post date by post author', 'flatsome')),
        '<a href="' . esc_url(get_permalink()) . '" rel="bookmark">' . $time_string . '</a>' . $event_html . $modified_html,
        '<span class="meta-author vcard"><a class="url fn n" href="' . esc_url(get_author_posts_url(get_the_author_meta('ID'))) . '">' . esc_html(get_the_author()) . '</a></span>'
    );

    // ۴. افزودن لینک ویرایش مستقیم نوشته برای مدیران و کارشناسان دسترسی‌دار
    edit_post_link(
        __('ویرایش', 'flatsome'),
        '<span class="edit-link" style="margin-right: 10px; background: #f1f5f9; padding: 2px 8px; border-radius: 4px; border: 1px solid #cbd5e1; font-size: 11px;"><i class="icon-edit"></i> ',
        '</span>'
    );
}