<?php
/**
 * Admin menu and post object label renaming for the Sahab child theme.
 */

function flatsome_child_rename_posts_menu() {
	global $menu, $submenu;

	foreach ( $menu as $key => $item ) {
		if ( isset( $item[0] ) && $item[0] === 'Posts' ) {
			$menu[ $key ][0] = 'مدیریت اخبار';
			break;
		}
	}

	if ( isset( $submenu['edit.php'] ) && is_array( $submenu['edit.php'] ) ) {
		foreach ( $submenu['edit.php'] as $index => $subitem ) {
			if ( isset( $subitem[0] ) && $subitem[0] === 'All Posts' ) {
				$submenu['edit.php'][ $index ][0] = 'همه اخبار';
			}
			if ( isset( $subitem[0] ) && $subitem[0] === 'Add New' ) {
				$submenu['edit.php'][ $index ][0] = 'ایجاد خبر';
			}
		}
	}
}
add_action( 'admin_menu', 'flatsome_child_rename_posts_menu' );

function flatsome_child_rename_post_labels() {
	global $wp_post_types;

	if ( isset( $wp_post_types['post'] ) ) {
		$labels = &$wp_post_types['post']->labels;
		$labels->name = 'اخبار';
		$labels->singular_name = 'اخبار';
		$labels->add_new = 'ایجاد خبر';
		$labels->add_new_item = 'ایجاد خبر جدید';
		$labels->edit_item = 'ویرایش خبر';
		$labels->new_item = 'خبر جدید';
		$labels->view_item = 'مشاهده خبر';
		$labels->search_items = 'جستجوی اخبار';
		$labels->not_found = 'خبری یافت نشد';
		$labels->not_found_in_trash = 'خبری در زباله‌دان یافت نشد';
	}
}
add_action( 'init', 'flatsome_child_rename_post_labels' );
