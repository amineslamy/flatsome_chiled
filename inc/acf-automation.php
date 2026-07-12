<?php
/**
 * ACF automation helpers for the Sahab child theme.
 */

function flatsome_child_disable_automation_id_field( $field ) {
	$field['disabled'] = true;
	return $field;
}
add_filter( 'acf/load_field/name=automation_id', 'flatsome_child_disable_automation_id_field' );

function flatsome_child_generate_automation_id( $post_id ) {
	if ( get_post_type( $post_id ) !== 'post' ) {
		return;
	}

	if ( get_post_meta( $post_id, 'automation_id', true ) ) {
		return;
	}

	global $wpdb;

	$meta_key = 'automation_id';
	$max_value = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT MAX(CAST(meta_value AS UNSIGNED)) FROM $wpdb->postmeta WHERE meta_key = %s",
			$meta_key
		)
	);

	$next_id = empty( $max_value ) ? 1234567 : (int) $max_value + 1;
	update_post_meta( $post_id, $meta_key, $next_id );

	if ( isset( $_POST['acf']['field_automation_id'] ) && empty( $_POST['acf']['field_automation_id'] ) ) {
		unset( $_POST['acf']['field_automation_id'] );
	}
}
add_action( 'acf/save_post', 'flatsome_child_generate_automation_id', 20 );
