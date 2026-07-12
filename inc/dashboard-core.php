<?php
/**
 * پلتفرم سحاب - ماژول هسته و زیرساخت میز کار مرکزی (DataTables Backend)
 */

if ( ! function_exists( 'sahab_dashboard_should_enqueue_assets' ) ) {
	/**
	 * بررسی هوشمند برای بارگذاری دارایی‌ها فقط در برگه داشبورد
	 */
	function sahab_dashboard_should_enqueue_assets() {
		if ( is_admin() || ! is_singular() ) {
			return false;
		}

		$post = get_post();
		if ( ! $post instanceof WP_Post ) {
			return false;
		}

		if ( has_shortcode( $post->post_content, 'sahab_dashboard' ) ) {
			return true;
		}

		$dashboard_slugs = array( 'dashboard', 'sahab-dashboard', 'central-dashboard' );
		return in_array( $post->post_name, $dashboard_slugs, true );
	}
}

if ( ! function_exists( 'sahab_dashboard_enqueue_assets' ) ) {
	/**
	 * بارگذاری استاندارد و مشروط کتابخانه‌ها و فایل‌های استایل/اسکریپت اختصاصی
	 */
	function sahab_dashboard_enqueue_assets() {
		if ( ! sahab_dashboard_should_enqueue_assets() ) {
			return;
		}

		// لود دیتاتیبلز اصلی
		wp_enqueue_style( 'sahab-datatables-css', 'https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css', array(), null );
		wp_enqueue_script( 'sahab-datatables-js', 'https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js', array( 'jquery' ), null, true );

		// لود فایل CSS تفکیک‌شده و فشرده سحاب
		wp_enqueue_style( 'sahab-dashboard-custom-css', get_stylesheet_directory_uri() . '/assets/public/css/sahab-dashboard.css', array(), '1.4.1' );

		// لود فایل جاوااسکریپت داشبورد
		wp_enqueue_script( 'sahab-dashboard-js', get_stylesheet_directory_uri() . '/assets/public/js/sahab-dashboard.js', array( 'jquery', 'sahab-datatables-js' ), '1.4.1', true );

		// انتقال امن آدرس AJAX به جاوااسکریپت
		wp_localize_script( 'sahab-dashboard-js', 'sahab_dashboard_vars', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
		) );
	}
	add_action( 'wp_enqueue_scripts', 'sahab_dashboard_enqueue_assets' );
}

if ( ! function_exists( 'sahab_dashboard_shortcode' ) ) {
	/**
	 * رندر شورت‌کد اسکلت جدول ۱۱ ستونه سحاب
	 */
	function sahab_dashboard_shortcode() {
		ob_start();
		?>
		<div class="sahab-dashboard-wrapper" dir="rtl">
			<table id="sahab-main-dashboard" class="display responsive nowrap" style="width:100%;">
				<thead>
					<tr>
						<th>شماره</th>
						<th>عنوان خبر</th>
						<th>کیس</th>
						<th>موضوع</th>
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
						<td colspan="11">در حال بارگذاری و آماده‌سازی اطلاعات میز کار سحاب...</td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php
		return ob_get_clean();
	}
	add_shortcode( 'sahab_dashboard', 'sahab_dashboard_shortcode' );
}

// هوک‌های رسمی اتصال دیتاتیبلز به موتورخانه داتا
add_action( 'wp_ajax_sahab_get_dashboard_data', 'flatsome_child_get_dashboard_data' );
add_action( 'wp_ajax_nopriv_sahab_get_dashboard_data', 'flatsome_child_get_dashboard_data' );

if ( ! function_exists( 'sahab_dashboard_translate_value' ) ) {
	/**
	 * مترجم هوشمند مقادیر و اسلاگ‌های انگلیسی ACF به لیبل‌های فارسی
	 */
	function sahab_dashboard_translate_value( $value, $field_name, $post_id ) {
		if ( empty( $value ) || array() === $value ) {
			return '---';
		}

		// تلاش برای دریافت مستقیم Label از تنظیمات فیلد ACF
		if ( function_exists( 'get_field_object' ) ) {
			$field_object = get_field_object( $field_name, $post_id, false );
			if ( is_array( $field_object ) && ! empty( $field_object['choices'] ) ) {
				if ( is_array( $value ) ) {
					$labels = array();
					foreach ( $value as $item ) {
						$item_key = (string) $item;
						$labels[] = isset( $field_object['choices'][ $item_key ] ) ? (string) $field_object['choices'][ $item_key ] : $item_key;
					}
					return implode( '، ', array_filter( $labels ) );
				}
				$value_key = (string) $value;
				if ( isset( $field_object['choices'][ $value_key ] ) ) {
					return (string) $field_object['choices'][ $value_key ];
				}
			}
		}

		// دیکشنری بومی پشتیبان برای سناریوهای خاص
		$normalized = strtolower( str_replace( array( '_', '-', ' ' ), '', (string) $value ) );
		$translations = array(
			'urgent'           => 'فوری',
			'important'        => 'مهم',
			'high'             => 'مهم',
			'normal'           => 'عادی',
			'low'              => 'کم',
			'medium'           => 'متوسط',
			'valid'            => 'صحت دارد',
			'underreview'      => 'در حال بررسی',
			'under_review'     => 'در حال بررسی',
			'politicalclerics' => 'روحانیون سیاسی',
			'institutions'     => 'موسسات',
		);

		return isset( $translations[ $normalized ] ) ? $translations[ $normalized ] : (string) $value;
	}
}

if ( ! function_exists( 'flatsome_child_get_dashboard_comment_summary' ) ) {
	/**
	 * استخراج آمار عددی دقیق پی‌نوشت‌ها به تفکیک دسته‌بندی برای رندر دایره‌ها
	 */
	function flatsome_child_get_dashboard_comment_summary( $post_id ) {
		$summary = array( 'note' => 0, 'theory' => 0, 'rewrite' => 0, 'misc' => 0 );
		$comments = get_comments( array( 'post_id' => absint( $post_id ), 'status' => 'approve', 'fields' => 'ids' ) );

		foreach ( $comments as $comment_id ) {
			$type = get_comment_meta( $comment_id, 'comment_type', true );
			if ( in_array( $type, array( 'note', 'theory', 'rewrite' ), true ) ) {
				$summary[ $type ]++;
			} else {
				$summary['misc']++;
			}
		}
		return $summary;
	}
}

if ( ! function_exists( 'flatsome_child_get_dashboard_data' ) ) {
	/**
	 * موتورخانه توزیع دیتای فرآوری شده اخبار به دیتاتیبلز
	 */
	function flatsome_child_get_dashboard_data() {
		if ( ! is_user_logged_in() || ! current_user_can( 'read' ) ) {
			wp_send_json_error( array( 'message' => 'سطح دسترسی غیرمجاز.' ), 403 );
		}

		$query = new WP_Query( array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => -1,
			'orderby'             => 'date',
			'order'               => 'DESC',
			'no_found_rows'       => true,
			'ignore_sticky_posts' => true,
		) );

		$result_array = array();

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$post_id = get_the_ID();
				
				// ۱. ستون شماره
				$automation_id = get_post_meta( $post_id, 'automation_id', true );
				$final_num = ! empty( $automation_id ) ? (string) $automation_id : sprintf( 'AUTO-%d', $post_id );

				// ۲. کیس (بر اساس دسته‌بندی‌های بومی پروژه سحاب)
				$categories = get_the_category( $post_id );
				$case_label = '---';
				if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
					$cat_names = array();
					foreach ( $categories as $category ) {
						if ( 'uncategorized' !== $category->slug && 'دسته-بندی-نشده' !== $category->name ) {
							$cat_names[] = $category->name;
						}
					}
					if ( empty( $cat_names ) && isset( $categories[0] ) ) {
						$cat_names[] = $categories[0]->name;
					}
					$case_label = implode( '، ', $cat_names );
				}

				// ۳. موضوع (ACF)
				$subject_val = get_post_meta( $post_id, 'subject', true );
				if ( empty( $subject_val ) && function_exists( 'get_field' ) ) { 
					$subject_val = get_field( 'subject', $post_id ); 
				}
				$subject_label = sahab_dashboard_translate_value( $subject_val, 'subject', $post_id );

				// ۴. ارزیابی و ارجحیت (بدون بج، تفکیک متنی ساده با |)
				$eval_val = get_post_meta( $post_id, 'evaluation', true );
				$prio_val = get_post_meta( $post_id, 'priority', true );
				$eval_label = sahab_dashboard_translate_value( $eval_val, 'evaluation', $post_id );
				$prio_label = sahab_dashboard_translate_value( $prio_val, 'priority', $post_id );
				
				$eval_summary = array_filter( array( $eval_label, $prio_label ) );
				$final_evaluation = ! empty( $eval_summary ) ? implode( ' | ', $eval_summary ) : 'عادی';

				// ۵. کارشناس و ثبت‌کننده (لینک به آرشیو نویسندگان)
				$author_id = (int) get_post_field( 'post_author', $post_id );
				$expert_name = get_the_author_meta( 'display_name', $author_id );
				$expert_link = sprintf( '<a href="%s" target="_blank" class="sahab-user-link">%s</a>', esc_url( get_author_posts_url( $author_id ) ), esc_html( $expert_name ) );

				$creator_id = get_post_meta( $post_id, 'news_creator_id', true );
				$creator_user = $creator_id ? get_userdata( (int) $creator_id ) : false;
				$creator_name = ( $creator_user && ! empty( $creator_user->display_name ) ) ? $creator_user->display_name : $expert_name;
				$creator_link = sprintf( '<a href="%s" target="_blank" class="sahab-user-link">%s</a>', esc_url( get_author_posts_url( $creator_id ? (int)$creator_id : $author_id ) ), esc_html( $creator_name ) );

				// ۶. تواریخ
				$event_date = get_post_meta( $post_id, 'event_date', true );
				$publish_date = get_the_date( 'Y/m/d', $post_id );

				// ۷. ساخت ۳ آیکون خطی با چینش عمودی دو ردیفه
				$actions_html = '<div class="sahab-dashboard-actions">'
					. '<div class="sahab-actions-top-row">'
					. '<a href="' . esc_url( get_permalink( $post_id ) ) . '" target="_blank" class="sahab-btn-svg sahab-btn-view" title="مشاهده خبر">'
					. '<svg viewBox="0 0 24 24" width="18" height="18" stroke="#0284c7" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>'
					. '</a>'
					. '<a href="' . esc_url( admin_url( 'post.php?post=' . absint( $post_id ) . '&action=edit' ) ) . '" target="_blank" class="sahab-btn-svg sahab-btn-edit" title="ویرایش خبر">'
					. '<svg viewBox="0 0 24 24" width="18" height="18" stroke="#0f766e" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4Z"></path></svg>'
					. '</a>'
					. '</div>'
					. '<button type="button" class="sahab-btn-svg sahab-btn-delete" title="حذف خبر">'
					. '<svg viewBox="0 0 24 24" width="18" height="18" stroke="#dc2626" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>'
					. '</button>'
					. '</div>';

				$result_array[] = array(
					'automation_id'          => $final_num,
					'title'                  => get_the_title( $post_id ),
					'case'                   => $case_label,
					'subject'                => $subject_label,
					'evaluation'             => $final_evaluation,
					'expert'                 => $expert_link,
					'creator'                => $creator_link,
					'event_date'             => ! empty( $event_date ) ? (string) $event_date : '---',
					'publish_date'           => ! empty( $publish_date ) ? $publish_date : '---',
					'comments_count_summary' => flatsome_child_get_dashboard_comment_summary( $post_id ),
					'permalink'              => get_permalink( $post_id ),
					'actions'                => $actions_html,
				);
			}
		}

		wp_reset_postdata();
		echo wp_json_encode( array( 'data' => $result_array ) );
		wp_die();
	}
}