<?php
/**
 * Flatsome Child — Meta box: "جزئیات پرونده سحاب"
 * Adds an `event_date` text field to post edit screen and saves it to postmeta.
 */

if ( ! function_exists( 'flatsome_child_add_event_meta_box' ) ) {
	function flatsome_child_add_event_meta_box() {
		add_meta_box(
			'flatsome_child_event_details',
			'جزئیات پرونده سحاب',
			'flatsome_child_render_event_metabox',
			'post',
			'side',
			'default'
		);
	}
	add_action( 'add_meta_boxes', 'flatsome_child_add_event_meta_box' );
}

if ( ! function_exists( 'flatsome_child_render_event_metabox' ) ) {
	function flatsome_child_render_event_metabox( $post ) {
		wp_nonce_field( 'sahab_save_event_date', 'sahab_event_date_nonce' );

		$value = get_post_meta( $post->ID, 'event_date', true );

		echo '<link rel="stylesheet" href="' . get_stylesheet_directory_uri() . '/assets/admin/css/jalali-datepicker.min.css">';
		echo '<label for="flatsome_child_event_date">تاریخ وقوع پرونده:</label>';
		echo '<input type="text" id="flatsome_child_event_date" name="event_date" value="' . esc_attr( $value ) . '" style="width:100%;" placeholder="۱۴۰۵/۰۴/۱۸" data-jdp />';

		$js_url = get_stylesheet_directory_uri() . '/assets/admin/js/jalali-datepicker.min.js';
		?>
		<script src="<?php echo esc_url( $js_url ); ?>"></script>
		<script>
		document.addEventListener("DOMContentLoaded", function() {
		    // بررسی وجود کتابخانه نسخه 0.6.0
		    if (typeof jalaliDatepicker !== 'undefined') {
		        jalaliDatepicker.startWatch();
		    } else {
		        console.error("سحاب: کتابخانه jalaliDatepicker یافت نشد.");
		    }
		});
		</script>
		<?php
	}
}

if ( ! function_exists( 'flatsome_child_save_event_meta_box' ) ) {
	function flatsome_child_save_event_meta_box( $post_id ) {
		if ( isset( $_POST['event_date'] ) ) {
			update_post_meta( $post_id, 'event_date', sanitize_text_field( wp_unslash( $_POST['event_date'] ) ) );
		}
	}
	add_action( 'save_post', 'flatsome_child_save_event_meta_box' );
}

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
