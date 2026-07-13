<?php
/**
 * شخصی‌سازی تمیز، مینی‌مال و پایدار صفحه ایجاد خبر سحاب همراه با منوی جمع‌شده
 */

// ۱. حذف گزینه‌های اضافی از منوی کناری به صورت بومی
function sahab_remove_unwanted_admin_menus() {
    remove_menu_page( 'tools.php' );          // حذف ابزارها
    remove_menu_page( 'edit-comments.php' );  // حذف دیدگاه‌ها
}
add_action( 'admin_menu', 'sahab_remove_unwanted_admin_menus', 999 );

// ۲. اجبار به جمع شدن (Collapse) منوی بغل برای همه کاربران در این صفحه
function sahab_force_folded_admin_menu( $classes ) {
    $screen = get_current_screen();
    if ( $screen && $screen->post_type === 'post' ) {
        $classes .= ' folded'; // اضافه کردن کلاس بومی وردپرس برای جمع شدن منو
    }
    return $classes;
}
add_filter( 'admin_body_class', 'sahab_force_folded_admin_menu' );

// ۳. استایل‌های ظاهری مینی‌مال و هماهنگ‌سازی فضا
function sahab_custom_admin_editor_styles() {
    $screen = get_current_screen();
    if ( $screen && isset( $screen->post_type ) && $screen->post_type === 'post' ) {
        $custom_css = "
            /* شخصی‌سازی رنگ بارها بدون دستکاری هندسه اصلی */
            #wpadminbar { background: #1e293b !important; }
            #wpcontent { 
                padding-top: 55px !important; 
                background: #f8fafc !important; 
            }
            
            /* فلت کردن باکس‌ها */
            .postbox { 
                border-radius: 8px !important; 
                border: 1px solid #e2e8f0 !important; 
                box-shadow: none !important; 
                background: #ffffff !important;
                margin-bottom: 20px !important;
            }
            .postbox-header { 
                border-bottom: 1px solid #f1f5f9 !important; 
                background: #f8fafc !important; 
            }
            .postbox-header h2 { font-weight: bold !important; color: #334155 !important; }

            /* استایل اختصاصی برای چکیده اتوماتیک شده زیر عنوان */
            #postexcerpt {
                background: transparent !important;
                border: none !important;
                box-shadow: none !important;
                margin: 15px 0 !important;
                padding: 0 !important;
            }
            #postexcerpt .postbox-header { display: none !important; }
            #postexcerpt .inside { padding: 0 !important; margin: 0 !important; }
            #postexcerpt .inside textarea {
                direction: rtl !important;
                text-align: right !important;
                width: 100% !important;
                height: 80px !important;
                font-size: 13px !important;
                padding: 12px !important;
                border-radius: 6px !important;
                border: 1px solid #cbd5e1 !important;
                box-sizing: border-box !important;
            }
            
            /* شیک کردن دکمه انتشار */
            #publish { 
                background: #d9534f !important; 
                color: #fff !important; 
                border: none !important; 
                box-shadow: none !important; 
                text-shadow: none !important; 
                border-radius: 6px !important; 
                padding: 6px 20px !important; 
                height: auto !important; 
                font-weight: bold !important; 
            }
            #publish:hover { background: #c9302c !important; }

            .sahab-admin-top-nav a { text-decoration: none !important; font-size: 13px !important; font-family: tahoma, sans-serif !important; }
        ";
        wp_register_style( 'sahab-admin-inline', false );
        wp_enqueue_style( 'sahab-admin-inline' );
        wp_add_inline_style( 'sahab-admin-inline', $custom_css );
    }
}
add_action( 'admin_enqueue_scripts', 'sahab_custom_admin_editor_styles' );

// ۴. تزریق نوار ناوبری شبیه‌سازی شده فرانت‌آند
function sahab_inject_menu_to_admin_editor() {
    $screen = get_current_screen();
    if ( $screen && $screen->post_type === 'post' ) {
        ?>
        <div class="sahab-admin-top-nav" style="background: #fff; border-bottom: 1px solid #e2e8f0; padding: 12px 30px; direction: rtl; display: flex; gap: 20px; align-items: center; position: fixed; top: 32px; right: 0; left: 0; z-index: 99999; height: 45px; box-sizing: border-box; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <a href="<?php echo home_url('/dashboard/'); ?>" style="color: #475569; font-weight: bold;">میز کار سحاب</a>
            <a href="<?php echo admin_url('post-new.php'); ?>" style="color: #d9534f; font-weight: bold; border-bottom: 2px solid #d9534f; padding-bottom: 11px;">ایجاد خبر</a>
            <a href="<?php echo home_url('/report/'); ?>" style="color: #475569; font-weight: bold;">گزارش</a>
            <a href="<?php echo home_url('/advanced-search/'); ?>" style="color: #475569; font-weight: bold;">جستجوی پیشرفته</a>
        </div>
        <?php
    }
}
add_action( 'in_admin_header', 'sahab_inject_menu_to_admin_editor' );

// ۵. جابجایی اتوماتیک چکیده به زیر عنوان با جی‌کوئری کاملاً ایمن
function sahab_automate_layout_features() {
    $screen = get_current_screen();
    if ( $screen && $screen->post_type === 'post' ) {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                var excerptBox = $('#postexcerpt');
                var titleDiv = $('#titlediv');
                if (excerptBox.length && titleDiv.length) {
                    excerptBox.insertAfter(titleDiv);
                    excerptBox.find('textarea').attr('placeholder', '✍️ چکیده گزارش را اینجا بنویسید (خلاصه کوتاه و کلیدی)...');
                }
            });
        </script>
        <?php
    }
}
add_action( 'admin_footer', 'sahab_automate_layout_features' );

// ۶. تنظیم المان‌های مخفی صفحه پیش‌فرض
function sahab_force_enable_default_meta_boxes($hidden, $screen) {
    if ( $screen->id === 'post' ) {
        $hidden = array('postcustom', 'trackbacksdiv', 'commentstatusdiv', 'commentsdiv', 'slugdiv', 'formatdiv');
        if (($key = array_search('postexcerpt', $hidden)) !== false) {
            unset($hidden[$key]);
        }
    }
    return $hidden;
}
add_filter( 'hidden_meta_boxes', 'sahab_force_enable_default_meta_boxes', 99, 2 );
add_filter( 'default_hidden_meta_boxes', 'sahab_force_enable_default_meta_boxes', 99, 2 );

// ۱. ست کردن تم رنگی ادمین به صورت پیش‌فرض برای کاربران جدید
function sahab_set_default_admin_color_theme($user_id) {
    $args = array(
        'ID' => $user_id,
        'admin_color' => 'ocean' // می‌توانید آن را به fresh، light یا midnight هم تغییر دهید
    );
    wp_update_user($args);
}
add_action('user_register', 'sahab_set_default_admin_color_theme');

// ۲. اجبار تم رنگی برای کاربران فعلی سامانه (وقتی وارد ادمین می‌شوند)
function sahab_force_admin_color_theme() {
    global $_wp_admin_css_colors;
    $current_color = get_user_option('admin_color');
    
    // اگر کاربر تمی غیر از تم مدنظر ما داشت، تم او را به اقیانوس (ocean) تغییر بده
    if ($current_color !== 'ocean' && is_array($_wp_admin_css_colors) && array_key_exists('ocean', $_wp_admin_css_colors)) {
        update_user_meta(get_current_user_id(), 'admin_color', 'ocean');
    }
}
add_action('admin_init', 'sahab_force_admin_color_theme');
