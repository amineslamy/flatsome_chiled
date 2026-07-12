<?php
/**
 * The template for displaying comments.
 *
 * This is the template that displays the area of the page that contains both the current comments
 * and the comment form.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 * @package          Flatsome\Templates
 * @flatsome-version 3.16.0
 */

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */
if ( post_password_required() ) {
	return;
}
?>

<?php do_action('flatsome_before_comments'); ?>

<div id="comments" class="comments-area">

	<?php if ( comments_open() ) : ?>
		<?php comment_form(); ?>
	<?php endif; ?>

	<?php if ( have_comments() ) : ?>
		<h3 class="comments-title uppercase">
			<?php printf( '<strong>%1$s پی‌نوشت روی خبر «%2$s» درج شده است</strong>', get_comments_number(), get_the_title() ); ?>
		</h3>

		<ol class="comment-list">
			<?php
				wp_list_comments( array( 'callback' => 'flatsome_comment' ) );
			?>
		</ol>

		<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // Are there comments to navigate through? ?>
		<nav id="comment-nav-below" class="navigation comment-navigation" role="navigation">
			<h2 class="screen-reader-text"><?php esc_html_e( 'Comment navigation', 'flatsome' ); ?></h2>
			<div class="nav-links nex-prev-nav">
				<div class="nav-previous"><?php previous_comments_link( esc_html__( 'Older Comments', 'flatsome' ) ); ?></div>
				<div class="nav-next"><?php next_comments_link( esc_html__( 'Newer Comments', 'flatsome' ) ); ?></div>
			</div>
		</nav>
		<?php endif; // Check for comment navigation. ?>

	<?php endif; // Check for have_comments(). ?>

	<?php
		// If comments are closed and there are comments, let's leave a little note, shall we?
		if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) :
	?>
		<p class="no-comments"><?php esc_html_e( 'Comments are closed.', 'flatsome' ); ?></p>
	<?php endif; ?>

</div>
