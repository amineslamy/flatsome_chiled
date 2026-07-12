<?php
/**
 * Jalali datepicker assets and initialization for the Sahab child theme.
 */

function flatsome_child_jalali_datepicker_setup() {
	$base = get_stylesheet_directory_uri() . '/assets/admin';
	wp_enqueue_style( 'flatsome-child-jalali-datepicker-css', $base . '/css/jalali-datepicker.min.css', array(), null );
	wp_enqueue_script( 'flatsome-child-jalali-datepicker-js', $base . '/js/jalali-datepicker.min.js', array(), null, true );
}
add_action( 'admin_enqueue_scripts', 'flatsome_child_jalali_datepicker_setup' );
add_action( 'wp_enqueue_scripts', 'flatsome_child_jalali_datepicker_setup' );

function flatsome_child_jalali_datepicker_footer_init() {
	?>
	<script>
	document.addEventListener('DOMContentLoaded', function() {
		try {
			var selectors = [
				'[data-name="event_date"] input',
				'input[name*="event_date"]',
				'input[id*="event_date"]'
			];

			var nodes = [];
			selectors.forEach(function(sel) {
				document.querySelectorAll(sel).forEach(function(n) {
					if (nodes.indexOf(n) === -1) nodes.push(n);
				});
			});

			if (nodes.length) {
				nodes.forEach(function(input) {
					try {
						input.setAttribute('data-jdp', 'true');
						input.setAttribute('autocomplete', 'off');
					} catch (e) {}
				});
			}

			if (typeof jalaliDatepicker !== 'undefined') {
				jalaliDatepicker.startWatch({
					minDate: 'attr',
					maxDate: 'attr',
					autoReadOnlyInput: true
				});
			}
		} catch (e) {
			if (window.console && console.error) console.error('JDP init error', e);
		}
	});
	</script>
	<?php
}
add_action( 'admin_footer', 'flatsome_child_jalali_datepicker_footer_init' );
add_action( 'wp_footer', 'flatsome_child_jalali_datepicker_footer_init' );
