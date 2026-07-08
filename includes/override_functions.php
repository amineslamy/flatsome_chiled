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

		echo '<link rel="stylesheet" href="' . get_stylesheet_directory_uri() . '/assets/admin/css/jalali-datepicker.min.css.css">';
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
		// Autosave, do nothing
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Verify nonce
		if ( ! isset( $_POST['flatsome_child_event_meta_box_nonce_field'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( $_POST['flatsome_child_event_meta_box_nonce_field'], 'flatsome_child_event_meta_box_nonce' ) ) {
			return;
		}

		// Capability check
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Only for posts
		$post_type = get_post_type( $post_id );
		if ( 'post' !== $post_type ) {
			return;
		}

		if ( isset( $_POST['event_date'] ) ) {
			$sanitized = sanitize_text_field( wp_unslash( $_POST['event_date'] ) );

			if ( '' === $sanitized ) {
				delete_post_meta( $post_id, 'event_date' );
			} else {
				update_post_meta( $post_id, 'event_date', $sanitized );
			}
		}
	}
	add_action( 'save_post', 'flatsome_child_save_event_meta_box' );
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

