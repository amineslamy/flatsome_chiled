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
		wp_nonce_field( 'flatsome_child_event_meta_box_nonce', 'flatsome_child_event_meta_box_nonce_field' );

		$value = get_post_meta( $post->ID, 'event_date', true );

		echo '<p><label for="flatsome_child_event_date">تاریخ وقوع</label></p>';
		echo '<p><input type="text" id="flatsome_child_event_date" name="event_date" value="' . esc_attr( $value ) . '" class="widefat" /></p>';
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

			// Enqueue dependencies from CDN (persian-date + persian-datepicker)
			wp_enqueue_script( 'persian-date', 'https://cdn.jsdelivr.net/npm/persian-date@0.1.8/dist/persian-date.min.js', array( 'jquery' ), null, true );
			wp_enqueue_script( 'persian-datepicker', 'https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js', array( 'jquery', 'persian-date' ), null, true );
			wp_enqueue_style( 'persian-datepicker-css', 'https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css', array(), null );

			// Initialize the picker on the event_date input - allow typing and calendar selection
			$init_js = <<<'JS'
jQuery(function($){
	var $input = $('#flatsome_child_event_date');
	if ( ! $input.length ) { $input = $('[name=event_date]'); }
	if ( $input.length && typeof $.fn.persianDatepicker === 'function' ) {
		$input.persianDatepicker({
			format: 'YYYY/MM/DD',
			observer: true,
			initialValue: false,
			altField: null
		});
	}
});
JS;

			wp_add_inline_script( 'persian-datepicker', $init_js );
		}
		add_action( 'admin_enqueue_scripts', 'flatsome_child_admin_enqueue_datepicker' );
	}

