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
