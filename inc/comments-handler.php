<?php

/**
 * Sahab comment handling module.
 *
 * This file contains custom comment meta box rendering, AJAX handlers,
 * badge presentation, creator tracking, and dashboard counters.
 */

/**
 * Display a styled badge for the Sahab comment type.
 */
function flatsome_child_comment_type_badge( $comment_text, $comment = null ) {
    $comment_id = 0;
    if ( is_object( $comment ) && isset( $comment->comment_ID ) ) {
        $comment_id = $comment->comment_ID;
    } elseif ( is_numeric( $comment ) ) {
        $comment_id = (int) $comment;
    } elseif ( isset( $GLOBALS['comment'] ) && is_object( $GLOBALS['comment'] ) ) {
        $comment_id = $GLOBALS['comment']->comment_ID;
    }

    if ( ! $comment_id ) {
        return $comment_text;
    }

    $type = get_comment_meta( $comment_id, 'comment_type', true );
    if ( empty( $type ) ) {
        return $comment_text;
    }

    $map = array(
        'rewrite' => array( 'label' => 'بازنویسی خبر', 'color' => '#2196F3' ),
        'note'    => array( 'label' => 'ملاحظه',       'color' => '#FF9800' ),
        'theory'  => array( 'label' => 'نظریه',        'color' => '#9C27B0' ),
    );

    if ( ! isset( $map[ $type ] ) ) {
        return $comment_text;
    }

    $label = esc_html( $map[ $type ]['label'] );
    $color = esc_attr( $map[ $type ]['color'] );

    $badge = '<span class="sahab-badge" style="background: ' . $color . '; color: #fff; padding: 2px 8px; border-radius: 3px; font-size: 11px; margin-left: 5px; display: inline-block; font-weight: bold;">' . $label . '</span>';

    return $badge . ' ' . $comment_text;
}
add_filter( 'comment_text', 'flatsome_child_comment_type_badge', 10, 2 );

add_action( 'comment_post', 'flatsome_child_save_native_comment_type_meta', 10, 3 );
function flatsome_child_save_native_comment_type_meta( $comment_id, $comment_approved, $commentdata ) {
    if ( isset( $_POST['comment_type'] ) ) {
        $type = sanitize_text_field( wp_unslash( $_POST['comment_type'] ) );
        if ( in_array( $type, array( 'rewrite', 'note', 'theory' ), true ) ) {
            update_comment_meta( $comment_id, 'comment_type', $type );
            update_comment_meta( $comment_id, '_comment_type', 'field_6a50f060ebcfb' );
        }
    }
}

add_action( 'wp_insert_post', 'flatsome_child_track_post_creator', 20, 3 );
function flatsome_child_track_post_creator( $post_id, $post, $update ) {
    if ( $update ) {
        return;
    }

    if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
        return;
    }

    if ( get_post_type( $post_id ) !== 'post' ) {
        return;
    }

    if ( metadata_exists( 'post', $post_id, 'news_creator_id' ) ) {
        return;
    }

    $current_user_id = get_current_user_id();
    if ( $current_user_id ) {
        add_post_meta( $post_id, 'news_creator_id', $current_user_id, true );
    }
}

/**
 * Render the Sahab frontend comment dashboard with counts by comment type.
 */
function flatsome_child_render_comment_type_dashboard( $post_id = 0 ) {
    if ( ! $post_id ) {
        $post_id = get_the_ID();
    }

    if ( ! $post_id ) {
        return '';
    }

    $comments = get_comments( array(
        'post_id' => $post_id,
        'status'  => 'approve',
        'order'   => 'ASC',
    ) );

    $counts = array(
        'note'    => 0,
        'theory'  => 0,
        'rewrite' => 0,
        'plain'   => 0,
    );

    foreach ( $comments as $comment ) {
        $type = get_comment_meta( $comment->comment_ID, 'comment_type', true );
        if ( $type === 'note' ) {
            $counts['note']++;
        } elseif ( $type === 'theory' ) {
            $counts['theory']++;
        } elseif ( $type === 'rewrite' ) {
            $counts['rewrite']++;
        } else {
            $counts['plain']++;
        }
    }

    $items = array(
        array( 'key' => 'note',    'label' => 'ملاحظه',   'color' => '#ff9800' ),
        array( 'key' => 'theory',  'label' => 'نظریه',    'color' => '#9c27b0' ),
        array( 'key' => 'rewrite', 'label' => 'بازنویسی', 'color' => '#2196f3' ),
        array( 'key' => 'plain',   'label' => 'متفرقه',   'color' => '#757575' ),
    );

    ob_start();
    ?>
    <div style="margin:12px 0 16px; padding:12px 14px; border:1px solid #e0e0e0; border-radius:6px; background:#f9f9f9; display:flex; flex-wrap:wrap; align-items:center; gap:10px;">
        <span style="font-weight:600; color:#333;">📊 خلاصه وضعیت پی‌نوشت‌ها:</span>
        <?php foreach ( $items as $item ) : $value = isset( $counts[ $item['key'] ] ) ? (int) $counts[ $item['key'] ] : 0; ?>
            <span style="display:inline-flex; align-items:center; gap:6px; padding:4px 10px; border-radius:999px; color:#fff; background:<?php echo esc_attr( $item['color'] ); ?>; font-size:12px; font-weight:600; white-space:nowrap;">
                <span><?php echo esc_html( '[' . $value . '] ' . $item['label'] ); ?></span>
            </span>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}

add_filter( 'comments_number', 'flatsome_child_override_comments_number_title', 20, 5 );
function flatsome_child_override_comments_number_title( $output, $number = 0, $zero = '', $one = '', $more = '' ) {
    $post_id = get_the_ID();
    if ( ! $post_id ) {
        return '';
    }
    return flatsome_child_render_comment_type_dashboard( $post_id );
}

add_action( 'comment_form_before', 'flatsome_child_inject_frontend_dashboard' );
function flatsome_child_inject_frontend_dashboard() {
    if ( is_admin() ) {
        return;
    }

    $post_id = get_the_ID();
    if ( ! $post_id || ! comments_open( $post_id ) ) {
        return;
    }

    echo flatsome_child_render_comment_type_dashboard( $post_id );
}

add_filter( 'comment_form_defaults', 'flatsome_child_force_comment_form_strings' );
function flatsome_child_force_comment_form_strings( $defaults ) {
    $defaults['logged_in_as'] = '';
    $defaults['title_reply']  = 'ثبت پی‌نوشت جدید';
    $defaults['label_submit'] = 'ثبت و ارسال';
    return $defaults;
}

add_filter( 'comment_form_fields', 'flatsome_child_reorder_and_enrich_comment_fields' );
function flatsome_child_reorder_and_enrich_comment_fields( $fields ) {
    if ( ! is_admin() ) {
        wp_enqueue_editor();
    }

    $acf_html = '<p class="comment-form-comment_type"><label style="display:block;font-weight:bold;margin-bottom:5px;">نوع پی‌نوشت</label><span style="display:flex; gap:15px; align-items:center; flex-wrap:wrap;"><label style="font-weight:normal;"><input type="radio" name="comment_type" value="" checked style="margin-left:5px;">هیچ‌کدام</label><label style="font-weight:normal;"><input type="radio" name="comment_type" value="rewrite" style="margin-left:5px;">بازنویسی خبر</label><label style="font-weight:normal;"><input type="radio" name="comment_type" value="note" style="margin-left:5px;">ملاحظه</label><label style="font-weight:normal;"><input type="radio" name="comment_type" value="theory" style="margin-left:5px;">نظریه</label></span></p>';

    ob_start();
    $editor_settings = array(
        'textarea_name' => 'comment',
        'media_buttons' => false,
        'teeny'         => true,
        'textarea_rows' => 6,
        'tinymce'       => array(
            'toolbar1' => 'bold italic underline bullist',
            'toolbar2' => '',
        ),
        'quicktags'     => false,
    );
    wp_editor( '', 'sahab_comment_editor', $editor_settings );
    $editor_html = ob_get_clean();

    $custom_fields = array();
    $custom_fields['comment_field'] = '<div class="sahab-comment-fields-wrapper">' . $acf_html . '<p class="comment-form-comment"><label for="sahab_comment_editor">متن پی‌نوشت <span class="required">*</span></label>' . $editor_html . '</p></div>';

    return $custom_fields;
}

add_action( 'add_meta_boxes', 'flatsome_child_remove_core_comments_meta_box', 99 );
function flatsome_child_remove_core_comments_meta_box() {
    remove_meta_box( 'commentsdiv', 'post', 'normal' );
}

add_action( 'add_meta_boxes', 'flatsome_child_add_sahab_custom_comments_meta_box' );
function flatsome_child_add_sahab_custom_comments_meta_box() {
    add_meta_box(
        'sahab_custom_comments_meta_box',
        'مدیریت و ثبت پی‌نوشت‌های سحاب (ملاحظات، نظریات و بازنویسی)',
        'flatsome_child_render_sahab_custom_comments_meta_box',
        'post',
        'normal',
        'high'
    );
}

function flatsome_child_render_sahab_custom_comments_meta_box( $post ) {
    wp_nonce_field( 'sahab_custom_comments_meta_box', 'sahab_custom_comments_meta_box_nonce' );

    $creator_id = get_post_meta( $post->ID, 'news_creator_id', true );
    if ( $creator_id ) {
        $creator = get_userdata( $creator_id );
        if ( $creator ) {
            echo '<div style="margin-bottom:12px; padding:8px 12px; background:#e8f5e9; border-left:4px solid #4caf50; font-size:12px; color:#2e7d32;">';
            echo '<strong>✓ ثبت‌کننده:</strong> ' . esc_html( $creator->display_name ) . ' (ID: ' . esc_html( $creator_id ) . ')';
            echo '</div>';
        }
    }

    $comments = get_comments( array(
        'post_id' => $post->ID,
        'status'  => 'approve',
        'order'   => 'DESC',
    ) );

    echo '<div class="sahab-backend-comments-box" style="margin-bottom:20px;">';
    echo flatsome_child_render_comment_type_dashboard( $post->ID );
    echo '<div id="sahab-backend-comment-form" class="sahab-backend-comment-entry" style="border:1px solid #ddd; padding:15px; border-radius:8px; background:#fbfbfb; margin-bottom:20px;">';
    echo '<input type="hidden" id="sahab_backend_comment_post_id" value="' . esc_attr( $post->ID ) . '">';
    echo '<input type="hidden" id="sahab_active_comment_id" value="">';
    echo '<p style="margin:0 0 10px;"><label style="display:block;font-weight:bold;margin-bottom:5px;">نوع پی‌نوشت جدید</label><span style="display:flex; gap:15px; align-items:center; flex-wrap:wrap;"><label style="font-weight:normal;"><input type="radio" name="sahab_backend_comment_type" value="" checked style="margin-left:5px;">هیچ‌کدام</label><label style="font-weight:normal;"><input type="radio" name="sahab_backend_comment_type" value="rewrite" style="margin-left:5px;">بازنویسی خبر</label><label style="font-weight:normal;"><input type="radio" name="sahab_backend_comment_type" value="note" style="margin-left:5px;">ملاحظه</label><label style="font-weight:normal;"><input type="radio" name="sahab_backend_comment_type" value="theory" style="margin-left:5px;">نظریه</label></span></p>';
    ob_start();
    $editor_settings = array(
        'textarea_name' => 'sahab_backend_comment',
        'media_buttons' => false,
        'teeny'         => true,
        'textarea_rows' => 6,
        'tinymce'       => array(
            'toolbar1' => 'bold italic underline bullist',
            'toolbar2' => '',
        ),
        'quicktags'     => false,
    );
    wp_editor( '', 'sahab_backend_comment_editor', $editor_settings );
    $backend_editor_html = ob_get_clean();
    echo '<p style="margin:20px 0 0; font-weight:bold;">متن پی‌نوشت جدید</p>' . $backend_editor_html;
    echo '<button type="button" id="sahab-submit-backend-comment" class="button button-primary" style="margin-top:10px;">ثبت و ارسال پی‌نوشت</button>';
    echo '<div id="sahab-backend-comment-feedback" style="margin-top:10px; min-height:20px;"></div>';
    echo '</div>';
    echo '<h4 style="margin:0 0 10px; font-size:14px; font-weight:bold;">پی‌نوشت‌های ثبت‌شده</h4>';
    echo '<div id="sahab-backend-comments-list">';
    if ( empty( $comments ) ) {
        echo '<p style="color:#666;">هنوز پی‌نوشتی ثبت نشده است.</p>';
    } else {
        echo '<ul style="list-style:none; margin:0; padding:0;">';
        foreach ( $comments as $comment ) {
            $type = get_comment_meta( $comment->comment_ID, 'comment_type', true );
            $map = array(
                'rewrite' => array( 'label' => 'بازنویسی خبر', 'color' => '#2196F3' ),
                'note'    => array( 'label' => 'ملاحظه',       'color' => '#FF9800' ),
                'theory'  => array( 'label' => 'نظریه',        'color' => '#9C27B0' ),
            );
            $badge = '';
            if ( isset( $map[ $type ] ) ) {
                $badge = '<span style="background:' . esc_attr( $map[ $type ]['color'] ) . '; color:#fff; padding:2px 8px; border-radius:3px; font-size:11px; display:inline-block; font-weight:bold; margin-left:8px;">' . esc_html( $map[ $type ]['label'] ) . '</span>';
            }
            $author = get_comment_author( $comment->comment_ID );
            $date   = get_comment_date( 'Y/m/d H:i', $comment->comment_ID );
            $raw_content = wp_strip_all_tags( $comment->comment_content );
            echo '<li class="sahab-comment-item" data-comment-id="' . esc_attr( $comment->comment_ID ) . '" data-comment-content="' . esc_attr( trim( $raw_content ) ) . '" data-comment-type="' . esc_attr( $type ) . '" style="border-bottom:1px solid #eee; padding:10px 0;">';
            echo '<div style="font-size:13px; margin-bottom:4px;"><strong>' . esc_html( $author ) . '</strong> ' . $badge . '<span class="sahab-action-links" style="margin-right:15px;"><a href="#" class="sahab-edit-comment" data-comment-id="' . esc_attr( $comment->comment_ID ) . '" style="color:#007cba; text-decoration:none; margin-left:10px;">✏️ ویرایش</a><a href="#" class="sahab-delete-comment" data-comment-id="' . esc_attr( $comment->comment_ID ) . '" style="color:#d94f4f; text-decoration:none;">❌ حذف</a></span></div>';
            echo '<div style="font-size:12px; color:#555; margin-bottom:6px;">' . esc_html( $date ) . '</div>';
            echo '<div style="font-size:13px; color:#333;">' . wp_kses_post( wpautop( $comment->comment_content ) ) . '</div>';
            echo '</li>';
        }
        echo '</ul>';
    }
    echo '</div>';
    echo '</div>';
}

add_action( 'save_post', 'flatsome_child_save_backend_comment_from_meta_box', 20, 3 );
function flatsome_child_save_backend_comment_from_meta_box( $post_id, $post, $update ) {
    if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
        return;
    }

    if ( ! isset( $_POST['sahab_custom_comments_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['sahab_custom_comments_meta_box_nonce'], 'sahab_custom_comments_meta_box' ) ) {
        return;
    }

    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    if ( isset( $_POST['sahab_backend_comment'] ) ) {
        $comment_content = wp_kses_post( wp_unslash( $_POST['sahab_backend_comment'] ) );
        $comment_type    = isset( $_POST['sahab_backend_comment_type'] ) ? sanitize_text_field( wp_unslash( $_POST['sahab_backend_comment_type'] ) ) : '';
        $valid_types     = array( 'rewrite', 'note', 'theory' );

        if ( ! empty( $comment_content ) ) {
            $comment_id = wp_insert_comment( array(
                'comment_post_ID'      => $post_id,
                'comment_author'       => wp_get_current_user()->display_name,
                'comment_author_email' => wp_get_current_user()->user_email,
                'comment_author_url'   => '',
                'comment_content'      => $comment_content,
                'comment_type'         => '',
                'comment_parent'       => 0,
                'user_id'              => get_current_user_id(),
                'comment_approved'     => 1,
            ) );

            if ( $comment_id && ! is_wp_error( $comment_id ) && ! empty( $comment_type ) && in_array( $comment_type, $valid_types, true ) ) {
                update_comment_meta( $comment_id, 'comment_type', $comment_type );
                update_comment_meta( $comment_id, '_comment_type', 'field_6a50f060ebcfb' );
            }
        }
    }
}

add_action( 'wp_ajax_sahab_submit_backend_comment', 'flatsome_child_ajax_submit_backend_comment' );
function flatsome_child_ajax_submit_backend_comment() {
    if ( ! isset( $_POST['post_id'], $_POST['comment_type'], $_POST['comment_content'], $_POST['sahab_custom_comments_meta_box_nonce'] ) ) {
        wp_send_json_error( 'missing_fields' );
    }

    if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sahab_custom_comments_meta_box_nonce'] ) ), 'sahab_custom_comments_meta_box' ) ) {
        wp_send_json_error( 'invalid_nonce' );
    }

    $post_id         = absint( $_POST['post_id'] );
    $comment_type    = isset( $_POST['comment_type'] ) ? sanitize_text_field( wp_unslash( $_POST['comment_type'] ) ) : '';
    $comment_content = wp_kses_post( wp_unslash( $_POST['comment_content'] ) );
    $valid_types     = array( 'rewrite', 'note', 'theory' );

    if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) || empty( $comment_content ) ) {
        wp_send_json_error( 'invalid_data' );
    }

    $current_user = wp_get_current_user();
    $comment_id   = wp_insert_comment( array(
        'comment_post_ID'      => $post_id,
        'comment_author'       => $current_user->display_name,
        'comment_author_email' => $current_user->user_email,
        'comment_author_url'   => '',
        'comment_content'      => $comment_content,
        'comment_type'         => '',
        'comment_parent'       => 0,
        'user_id'              => $current_user->ID,
        'comment_approved'     => 1,
    ) );

    if ( ! $comment_id || is_wp_error( $comment_id ) ) {
        wp_send_json_error( 'insert_failed' );
    }

    if ( ! empty( $comment_type ) && in_array( $comment_type, $valid_types, true ) ) {
        update_comment_meta( $comment_id, 'comment_type', $comment_type );
        update_comment_meta( $comment_id, '_comment_type', 'field_6a50f060ebcfb' );
    }

    $comment = get_comment( $comment_id );
    if ( ! $comment ) {
        wp_send_json_error( 'comment_not_found' );
    }

    $map   = array(
        'rewrite' => array( 'label' => 'بازنویسی خبر', 'color' => '#2196F3' ),
        'note'    => array( 'label' => 'ملاحظه',       'color' => '#FF9800' ),
        'theory'  => array( 'label' => 'نظریه',        'color' => '#9C27B0' ),
    );
    $badge = '';
    if ( isset( $map[ $comment_type ] ) ) {
        $badge = '<span style="background:' . esc_attr( $map[ $comment_type ]['color'] ) . '; color:#fff; padding:2px 8px; border-radius:3px; font-size:11px; display:inline-block; font-weight:bold; margin-left:8px;">' . esc_html( $map[ $comment_type ]['label'] ) . '</span>';
    }

    $html  = '<li style="border-bottom:1px solid #eee; padding:10px 0;">';
    $html .= '<div style="font-size:13px; margin-bottom:4px;"><strong>' . esc_html( $current_user->display_name ) . '</strong> ' . $badge . '</div>';
    $html .= '<div style="font-size:12px; color:#555; margin-bottom:6px;">' . esc_html( get_comment_date( 'Y/m/d H:i', $comment ) ) . '</div>';
    $html .= '<div style="font-size:13px; color:#333;">' . wp_kses_post( wpautop( $comment->comment_content ) ) . '</div>';
    $html .= '</li>';

    wp_send_json_success( $html );
}

add_action( 'wp_ajax_sahab_delete_backend_comment', 'flatsome_child_ajax_delete_backend_comment' );
function flatsome_child_ajax_delete_backend_comment() {
    if ( ! isset( $_POST['comment_id'] ) ) {
        wp_send_json_error( 'missing_comment_id' );
    }

    $comment_id = absint( $_POST['comment_id'] );
    $comment    = get_comment( $comment_id );
    if ( ! $comment || ! current_user_can( 'edit_post', $comment->comment_post_ID ) ) {
        wp_send_json_error( 'forbidden' );
    }

    $deleted = wp_delete_comment( $comment_id, true );
    if ( $deleted ) {
        wp_send_json_success( array( 'deleted_id' => $comment_id ) );
    }

    wp_send_json_error( 'delete_failed' );
}

add_action( 'wp_ajax_sahab_edit_backend_comment', 'flatsome_child_ajax_edit_backend_comment' );
function flatsome_child_ajax_edit_backend_comment() {
    if ( ! isset( $_POST['comment_id'], $_POST['comment_type'], $_POST['comment_content'] ) ) {
        wp_send_json_error( 'missing_fields' );
    }

    $comment_id      = absint( $_POST['comment_id'] );
    $comment_type    = sanitize_text_field( wp_unslash( $_POST['comment_type'] ) );
    $comment_content = wp_kses_post( wp_unslash( $_POST['comment_content'] ) );
    $valid_types     = array( 'rewrite', 'note', 'theory' );
    $comment         = get_comment( $comment_id );

    if ( ! $comment || empty( $comment_content ) || ! current_user_can( 'edit_post', $comment->comment_post_ID ) ) {
        wp_send_json_error( 'invalid_data' );
    }

    $updated = wp_update_comment( array(
        'comment_ID'      => $comment_id,
        'comment_content' => $comment_content,
        'comment_approved' => 1,
    ) );

    if ( ! $updated || is_wp_error( $updated ) ) {
        wp_send_json_error( 'update_failed' );
    }

    if ( ! empty( $comment_type ) && in_array( $comment_type, $valid_types, true ) ) {
        update_comment_meta( $comment_id, 'comment_type', $comment_type );
        update_comment_meta( $comment_id, '_comment_type', 'field_6a50f060ebcfb' );
    } else {
        delete_comment_meta( $comment_id, 'comment_type' );
        delete_comment_meta( $comment_id, '_comment_type' );
    }

    wp_send_json_success( array( 'updated_id' => $comment_id ) );
}

add_action( 'admin_footer', 'flatsome_child_backend_comment_ajax_script' );
function flatsome_child_backend_comment_ajax_script() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        var activeCommentId = '';

        function getEditorContent() {
            if (window.tinymce && tinymce.get('sahab_backend_comment_editor')) {
                return tinymce.get('sahab_backend_comment_editor').getContent().trim();
            }
            return $('#sahab_backend_comment_editor').val().trim();
        }

        function clearEditor() {
            if (window.tinymce && tinymce.get('sahab_backend_comment_editor')) {
                tinymce.get('sahab_backend_comment_editor').setContent('');
            } else {
                $('#sahab_backend_comment_editor').val('');
            }
        }

        function resetComposer() {
            clearEditor();
            $('input[name="sahab_backend_comment_type"]').prop('checked', false);
            $('input[name="sahab_backend_comment_type"][value=""]').prop('checked', true);
            activeCommentId = '';
            $('#sahab_active_comment_id').val('');
            $('#sahab-submit-backend-comment').text('ثبت و ارسال پی‌نوشت');
        }

        $('#sahab-submit-backend-comment').on('click', function() {
            var button = $(this);
            var postId = $('#sahab_backend_comment_post_id').val();
            var commentType = $('input[name="sahab_backend_comment_type"]:checked').val() || '';
            var content = getEditorContent();

            if ( ! content ) {
                alert('لطفاً متن پی‌نوشت را وارد کنید.');
                return;
            }

            button.prop('disabled', true).text('در حال ارسال...');
            $('#sahab-backend-comment-feedback').html('');

            var payload = {
                action: 'sahab_submit_backend_comment',
                post_id: postId,
                comment_type: commentType,
                comment_content: content,
                sahab_custom_comments_meta_box_nonce: $('#sahab_custom_comments_meta_box_nonce').val()
            };

            if (activeCommentId) {
                payload.action = 'sahab_edit_backend_comment';
                payload.comment_id = activeCommentId;
            }

            $.post(ajaxurl, payload, function(response) {
                if ( response.success ) {
                    if (activeCommentId) {
                        var item = $('.sahab-comment-item[data-comment-id="' + activeCommentId + '"]');
                        if (item.length) {
                            item.attr('data-comment-content', content);
                            item.attr('data-comment-type', commentType || '');
                            item.find('div:last').html(content);
                        }
                        $('#sahab-backend-comment-feedback').html('<div style="color:green;">پی‌نوشت با موفقیت به‌روزرسانی شد.</div>');
                    } else {
                        var html = response.data;
                        if ( $('#sahab-backend-comments-list ul').length ) {
                            $('#sahab-backend-comments-list ul').prepend(html);
                        } else {
                            $('#sahab-backend-comments-list').html('<ul style="list-style:none; margin:0; padding:0;">' + html + '</ul>');
                        }
                        $('#sahab-backend-comment-feedback').html('<div style="color:green;">پی‌نوشت با موفقیت ثبت شد.</div>');
                    }
                    resetComposer();
                } else {
                    $('#sahab-backend-comment-feedback').html('<div style="color:red;">خطا در ارسال پی‌نوشت. لطفاً دوباره تلاش کنید.</div>');
                }
            }).fail(function() {
                $('#sahab-backend-comment-feedback').html('<div style="color:red;">خطای شبکه رخ داد.</div>');
            }).always(function() {
                button.prop('disabled', false).text(activeCommentId ? 'ذخیره تغییرات پی‌نوشت' : 'ثبت و ارسال پی‌نوشت');
            });
        });

        $(document).on('click', '.sahab-delete-comment', function(e) {
            e.preventDefault();
            var id = $(this).data('comment-id');
            if ( ! confirm('آیا از حذف این پی‌نوشت مطمئن هستید؟') ) {
                return;
            }
            $.post(ajaxurl, {
                action: 'sahab_delete_backend_comment',
                comment_id: id
            }, function(response) {
                if ( response.success ) {
                    $('.sahab-comment-item[data-comment-id="' + id + '"]' ).remove();
                }
            });
        });

        $(document).on('click', '.sahab-edit-comment', function(e) {
            e.preventDefault();
            var id = $(this).data('comment-id');
            var item = $('.sahab-comment-item[data-comment-id="' + id + '"]');
            if ( ! item.length ) {
                return;
            }
            var content = item.attr('data-comment-content') || '';
            var type = item.attr('data-comment-type') || '';
            activeCommentId = id;
            $('#sahab_active_comment_id').val(id);
            if (window.tinymce && tinymce.get('sahab_backend_comment_editor')) {
                tinymce.get('sahab_backend_comment_editor').setContent(content);
            } else {
                $('#sahab_backend_comment_editor').val(content);
            }
            $('input[name="sahab_backend_comment_type"]').prop('checked', false);
            if (type) {
                $('input[name="sahab_backend_comment_type"][value="' + type + '"]').prop('checked', true);
            } else {
                $('input[name="sahab_backend_comment_type"][value=""]').prop('checked', true);
            }
            $('#sahab-submit-backend-comment').text('ذخیره تغییرات پی‌نوشت');
            $('#sahab-backend-comment-feedback').html('<div style="color:#007cba;">در حال ویرایش پی‌نوشت…</div>');
        });
    });
    </script>
    <?php
}
