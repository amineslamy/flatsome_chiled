<?php
/**
 * Sahab Central Dashboard foundation module.
 * Registers DataTables assets conditionally and provides the main dashboard shortcode.
 */

if ( ! function_exists( 'sahab_dashboard_should_enqueue_assets' ) ) {
	/**
	 * Determine whether the current request should load the dashboard assets.
	 *
	 * @return bool
	 */
	function sahab_dashboard_should_enqueue_assets() {
		if ( is_admin() ) {
			return false;
		}

		if ( ! is_singular() ) {
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

if ( ! function_exists( 'sahab_dashboard_asset_version' ) ) {
	/**
	 * Return a lightweight asset version for the dashboard bundle.
	 *
	 * @return string
	 */
	function sahab_dashboard_asset_version() {
		return '1.0.0';
	}
}

if ( ! function_exists( 'sahab_dashboard_enqueue_assets' ) ) {
	/**
	 * Enqueue DataTables assets only when the dashboard shortcode or dashboard slug is active.
	 */
	function sahab_dashboard_enqueue_assets() {
		if ( ! sahab_dashboard_should_enqueue_assets() ) {
			return;
		}

		wp_enqueue_style(
			'sahab-datatables-css',
			'https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css',
			array(),
			null
		);

		wp_enqueue_script(
			'sahab-datatables-js',
			'https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js',
			array( 'jquery' ),
			null,
			true
		);

		wp_enqueue_script(
			'sahab-dashboard-js',
			get_stylesheet_directory_uri() . '/assets/public/js/sahab-dashboard.js',
			array( 'jquery', 'sahab-datatables-js' ),
			sahab_dashboard_asset_version(),
			true
		);

		wp_localize_script(
			'sahab-dashboard-js',
			'sahab_dashboard_vars',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
			)
		);
	}
	add_action( 'wp_enqueue_scripts', 'sahab_dashboard_enqueue_assets' );
}

if ( ! function_exists( 'sahab_dashboard_shortcode' ) ) {
	/**
	 * Render the main dashboard table shell.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	function sahab_dashboard_shortcode( $atts = array() ) {
		$atts = shortcode_atts( array(), $atts, 'sahab_dashboard' );

		ob_start();
		?>
		<div class="sahab-dashboard-wrapper" dir="rtl">
			<table id="sahab-main-dashboard" class="display responsive nowrap" style="width:100%; text-align: right; direction: rtl;">
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
						<td colspan="11">در حال آماده‌سازی داده‌های داشبورد...</td>
					</tr>
				</tbody>
				<tfoot>
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
				</tfoot>
			</table>
		</div>
		<?php
		return ob_get_clean();
	}
	add_shortcode( 'sahab_dashboard', 'sahab_dashboard_shortcode' );
}

add_action( 'wp_ajax_sahab_get_dashboard_data', 'flatsome_child_get_dashboard_data' );
add_action( 'wp_ajax_nopriv_sahab_get_dashboard_data', 'flatsome_child_get_dashboard_data' );

if ( ! function_exists( 'flatsome_child_get_dashboard_comment_summary' ) ) {
	/**
	 * Build a comment summary array grouped by the main custom comment types.
	 *
	 * @param int $post_id Post ID.
	 * @return array
	 */
	function flatsome_child_get_dashboard_comment_summary( $post_id ) {
		$post_id = absint( $post_id );
		$summary = array(
			'ملاحظه'  => 0,
			'نظریه'   => 0,
			'بازنویسی' => 0,
			'متفرقه'  => 0,
		);

		$comments = get_comments( array(
			'post_id' => $post_id,
			'status'  => 'approve',
			'number'  => 0,
			'fields'  => 'ids',
		) );

		foreach ( $comments as $comment_id ) {
			$type = get_comment_meta( $comment_id, 'comment_type', true );
			switch ( $type ) {
				case 'note':
					$summary['ملاحظه']++;
					break;
				case 'theory':
					$summary['نظریه']++;
					break;
				case 'rewrite':
					$summary['بازنویسی']++;
					break;
				default:
					$summary['متفرقه']++;
					break;
			}
		}

		return $summary;
	}
}

if ( ! function_exists( 'flatsome_child_get_dashboard_data' ) ) {
	/**
	 * Return dashboard rows as JSON for DataTables.
	 */
	function flatsome_child_get_dashboard_data() {
		if ( ! is_user_logged_in() || ! current_user_can( 'read' ) ) {
			wp_send_json_error( array( 'message' => 'Authentication required.' ), 403 );
		}

		$query = new WP_Query( array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'no_found_rows'  => true,
			'ignore_sticky_posts' => true,
		) );

		$result_array = array();

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();

				$post_id = get_the_ID();
				$post_author_id = (int) get_post_field( 'post_author', $post_id );
				$automation_id = get_post_meta( $post_id, 'automation_id', true );
				$case_value = get_post_meta( $post_id, 'case', true );
				$subject_value = get_post_meta( $post_id, 'subject', true );
				$evaluation_value = get_post_meta( $post_id, 'evaluation', true );
				$priority_value = get_post_meta( $post_id, 'priority', true );
				$event_date_value = get_post_meta( $post_id, 'event_date', true );
				$creator_id = get_post_meta( $post_id, 'news_creator_id', true );
				$creator_user = $creator_id ? get_userdata( (int) $creator_id ) : false;
				$creator_name = $creator_user && ! empty( $creator_user->display_name ) ? $creator_user->display_name : get_the_author_meta( 'display_name', $post_author_id );
				$expert_name = get_the_author_meta( 'display_name', $post_author_id );

				if ( is_array( $subject_value ) ) {
					$subject_value = implode( ', ', array_filter( array_map( 'trim', $subject_value ) ) );
				}

				$evaluation_parts = array();
				if ( ! empty( $evaluation_value ) ) {
					$evaluation_parts[] = (string) $evaluation_value;
				}
				if ( ! empty( $priority_value ) ) {
					$evaluation_parts[] = '[' . (string) $priority_value . ']';
				}
				$evaluation_summary = implode( ' ', $evaluation_parts );

				$publish_date = get_the_date( '', $post_id );
				if ( empty( $publish_date ) ) {
					$publish_date = get_the_date();
				}

				$result_array[] = array(
					'automation_id'          => ! empty( $automation_id ) ? (string) $automation_id : sprintf( 'AUTO-%d', $post_id ),
					'title'                  => get_the_title( $post_id ),
					'case'                   => ! empty( $case_value ) ? (string) $case_value : '---',
					'subject'                => ! empty( $subject_value ) ? (string) $subject_value : '---',
					'evaluation'             => ! empty( $evaluation_summary ) ? $evaluation_summary : '---',
					'expert'                 => ! empty( $expert_name ) ? $expert_name : '---',
					'creator'                => ! empty( $creator_name ) ? $creator_name : '---',
					'event_date'             => ! empty( $event_date_value ) ? (string) $event_date_value : '---',
					'publish_date'           => ! empty( $publish_date ) ? $publish_date : '---',
					'comments_count_summary' => flatsome_child_get_dashboard_comment_summary( $post_id ),
					'actions'                => '<button type="button" data-id="' . absint( $post_id ) . '">عملیات</button>',
				);
			}
		}

		wp_reset_postdata();

		echo wp_json_encode( array( 'data' => $result_array ) );
		wp_die();
	}
}
