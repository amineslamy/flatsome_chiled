<?php
/**
 * Template Name: Create Report Page
 */

// ۱. مدیریت و پردازش فرم پس از سابمیت (سمت سرور)
if ( isset($_POST['sahab_submit_report']) && wp_verify_nonce($_POST['sahab_report_nonce'], 'sahab_create_report') ) {
    
    // ثبت پست اصلی در وردپرس
    $post_data = array(
        'post_title'   => sanitize_text_field($_POST['report_title']),
        'post_content' => wp_kses_post($_POST['report_content']),
        'post_excerpt' => sanitize_textarea_field($_POST['report_excerpt']),
        'post_status'  => 'publish',
        'post_type'    => 'post',
        'post_author'  => !empty($_POST['report_author']) ? intval($_POST['report_author']) : get_current_user_id(),
    );

    $post_id = wp_insert_post($post_data);

    if ( !is_wp_error($post_id) ) {
        // ثبت کیس‌ها (دسته‌ها)
        if ( !empty($_POST['report_cats']) ) {
            wp_set_post_categories($post_id, array_map('intval', $_POST['report_cats']));
        }

        // ثبت برچسب‌ها
        if ( !empty($_POST['report_tags']) ) {
            wp_set_post_tags($post_id, sanitize_text_field($_POST['report_tags']), false);
        }

        // ثبت تصویر شاخص (در صورت آپلود)
        if ( !empty($_FILES['report_thumbnail']['name']) ) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');
            $attachment_id = media_handle_upload('report_thumbnail', $post_id);
            if ( !is_wp_error($attachment_id) ) {
                set_post_thumbnail($post_id, $attachment_id);
            }
        }

        // ثبت فیلدهای اختصاصی سامانه سحاب (ACF)
        if ( !empty($_POST['acf']) ) {
            foreach ( $_POST['acf'] as $key => $value ) {
                update_field($key, $value, $post_id);
            }
        }

        // ثبت فیلد نوع پی‌نوشت (گروه دوم ACF که روی دیدگاه‌ها بود یا به عنوان متای پست)
        if ( !empty($_POST['comment_type_select']) ) {
            update_post_meta($post_id, 'comment_type', sanitize_text_field($_POST['comment_type_select']));
        }

        $success_message = "🎉 گزارش با موفقیت ثبت شد و در پیشخوان قرار گرفت.";
    }
}

get_header(); ?>

<div id="content" class="content-area page-wrapper" style="padding: 40px 0; background: #f8fafc;">
    <div class="row">
        <div class="large-9 col-inner" style="margin: 0 auto; float: none; background: #fff; padding: 35px; border-radius: 8px; border: 1px solid #e2e8f0; direction: rtl; text-align: right;">
            
            <header class="entry-header text-center" style="margin-bottom: 30px; border-bottom: 2px solid #f1f5f9; padding-bottom: 15px;">
                <h1 class="entry-title" style="color: #1e293b; font-weight: bold; font-size: 24px;">📝 ثبت و ایجاد گزارش جدید سحاب</h1>
                <p style="color: #64748b; font-size: 13px; margin-top: 5px;">اطلاعات گزارش را با دقت وارد نمایید. تمامی فیلدهای بومی و اختصاصی یکپارچه شده‌اند.</p>
            </header>

            <?php if ( isset($success_message) ) : ?>
                <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 6px; margin-bottom: 20px; font-weight: bold; text-align: center;">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <form method="post" action="" enctype="multipart/form-data" class="sahab-front-form">
                <?php wp_nonce_field('sahab_create_report', 'sahab_report_nonce'); ?>

                <!-- ۱. عنوان گزارش -->
                <div class="sahab-field-group">
                    <label>عنوان گزارش *</label>
                    <input type="text" name="report_title" required placeholder="عنوان گزارش را وارد کنید...">
                </div>

                <!-- ۲. چکیده (دقیقا زیر عنوان) -->
                <div class="sahab-field-group">
                    <label>چکیده گزارش</label>
                    <textarea name="report_excerpt" rows="3" placeholder="خلاصه‌ای کوتاه از گزارش را اینجا بنویسید..."></textarea>
                </div>

                <!-- ۳. متن اصلی گزارش -->
                <div class="sahab-field-group">
                    <label>متن اصلی گزارش</label>
                    <?php 
                    wp_editor('', 'report_content', array(
                        'media_buttons' => true,
                        'textarea_rows' => 12,
                        'teeny'         => false,
                        'quicktags'     => true
                    )); 
                    ?>
                </div>

                <!-- ۴. تزریق خودکار فیلدهای اختصاصی سحاب (ACF Group 1) -->
                <div style="background: #f8fafc; padding: 20px; border-radius: 6px; border: 1px solid #e2e8f0; margin-bottom: 25px;">
                    <h3 style="font-size: 15px; font-weight: bold; margin-bottom: 15px; color: #0284c7;">⚙️ اطلاعات اختصاصی سامانه سحاب</h3>
                    <?php 
                    // لود کردن فیلدهای گروه اختصاصی سحاب
                    $fields = acf_get_fields('group_6a4f9f009ab06');
                    if ( $fields ) {
                        foreach ( $fields as $field ) {
                            acf_render_field_wrap($field);
                        }
                    }
                    ?>
                </div>

                <!-- ۵. فیلد پی‌نوشت سحاب -->
                <div class="sahab-field-group" style="background: #fff7ed; padding: 20px; border-radius: 6px; border: 1px solid #ffedd5;">
                    <label style="color: #c2410c;">💬 مدیریت و ثبت پی‌نوشت‌های سحاب</label>
                    <select name="comment_type_select" style="width: 100%; max-width: 300px;">
                        <option value="">هیچ‌کدام</option>
                        <option value="rewrite">بازنویسی خبر</option>
                        <option value="note">ملاحظه</option>
                        <option value="theory">نظریه</option>
                        <option value="misc">متفرقه</option>
                    </select>
                </div>

                <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 30px 0;">

                <div class="row">
                    <!-- دسته‌ها / کیس‌ها -->
                    <div class="large-4 col">
                        <div class="sahab-field-group">
                            <label>📌 انتخاب کیس‌ها (دسته‌ها)</label>
                            <div style="max-height: 150px; overflow-y: auto; border: 1px solid #cbd5e1; padding: 10px; border-radius: 6px; background: #fff;">
                                <?php
                                $categories = get_categories(array('hide_empty' => false));
                                foreach ($categories as $cat) {
                                    echo '<label style="display:block; font-weight:normal; margin-bottom:5px;"><input type="checkbox" name="report_cats[]" value="' . $cat->term_id . '"> ' . esc_html($cat->name) . '</label>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <!-- برچسب‌ها و تصویر شاخص -->
                    <div class="large-4 col">
                        <div class="sahab-field-group">
                            <label>🏷️ برچسب‌ها (با کاما جدا کنید)</label>
                            <input type="text" name="report_tags" placeholder="مثال: حوزه، بیانیه، انتخابات">
                        </div>
                        <div class="sahab-field-group" style="margin-top: 15px;">
                            <label>🖼️ تصویر شاخص گزارش</label>
                            <input type="file" name="report_thumbnail" accept="image/*">
                        </div>
                    </div>

                    <!-- انتخاب نویسنده -->
                    <div class="large-4 col">
                        <div class="sahab-field-group">
                            <label>👤 ثبت کننده گزارش (نویسنده)</label>
                            <select name="report_author">
                                <?php
                                $users = get_users(array('fields' => array('ID', 'display_name')));
                                $current_user = get_current_user_id();
                                foreach ($users as $user) {
                                    echo '<option value="' . $user->ID . '" ' . selected($user->ID, $current_user, false) . '>' . esc_html($user->display_name) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- دکمه ثبت نهایی -->
                <div style="margin-top: 25px;">
                    <input type="submit" name="sahab_submit_report" value="🎯 ثبت نهایی و انتشار گزارش سحاب" style="background-color: #d9534f; color: #fff; border: none; padding: 12px 25px; font-weight: bold; border-radius: 6px; cursor: pointer; font-size: 15px; width: 100%; text-align: center;">
                </div>

            </form>
            
        </div>
    </div>
</div>

<style>
    .sahab-field-group { margin-bottom: 20px; }
    .sahab-front-form label { font-weight: bold !important; color: #1e293b !important; margin-bottom: 8px !important; display: block; font-size: 13px; }
    .sahab-front-form input[type="text"], .sahab-front-form select, .sahab-front-form textarea { width: 100%; border-radius: 6px; border: 1px solid #cbd5e1; padding: 8px 12px; font-size: 13px; background: #fff; }
    .sahab-front-form textarea { resize: vertical; }
    /* هماهنگی فیلدهای داخلی ای سی اف */
    .acf-field { margin-bottom: 15px !important; }
    .acf-label label { font-weight: bold !important; font-size: 13px !important; }
</style>

<?php get_footer(); ?>
