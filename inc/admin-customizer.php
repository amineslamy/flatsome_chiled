<?php
/**
 * شخصی‌سازی تمیز، سبک و اتوماسیون کامل چیدمان صفحه ایجاد خبر سحاب
 */

function sahab_custom_admin_editor_styles() {
    $screen = get_current_screen();
    if ( $screen && isset( $screen->post_type ) && $screen->post_type === 'post' ) {
        $custom_css = "
            /* ۱. شخصی‌سازی رنگ بارها و فضا */
            #wpadminbar { background: #1e293b !important; }
            #wpcontent { 
                padding-top: 55px !important; 
                background: #f8fafc !important; 
            }
            
            /* ۲. فلت کردن باکس‌های فرعی */
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

            /* ۳. استایل اختصاصی برای چکیده اتوماتیک شده زیر عنوان */
            #postexcerpt {
                background: transparent !important;
                border: none !important;
                box-shadow: none !important;
                margin: 15px 0 !important;
                padding: 0 !important;
            }
            #postexcerpt .postbox-header { display: none !important; } /* حذف هدر زشت باکس */
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
                box-shadow: inset 0 1px 2px rgba(0,0,0,0.05) !important;
            }
            #postexcerpt .inside textarea::placeholder { color: #94a3b8; }
            
            /* ۴. شیک کردن دکمه اصلی انتشار */
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

// ۵. تزریق نوار ناوبری شبیه‌سازی شده فرانت‌آند
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

// ۶. جابجایی اتوماتیک و فوری چکیده به زیر عنوان با جی‌کوئری
function sahab_automate_layout_features() {
    $screen = get_current_screen();
    if ( $screen && $screen->post_type === 'post' ) {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                var excerptBox = $('#postexcerpt');
                var titleDiv = $('#titlediv');
                if (excerptBox.length && titleDiv.length) {
                    // قرار دادن چکیده دقیقا زیر کادر عنوان
                    excerptBox.insertAfter(titleDiv);
                    // اضافه کردن خودکار یک Placeholder شیک که کاربر بداند اینجا چکیده است
                    excerptBox.find('textarea').attr('placeholder', '✍️ چکیده گزارش را اینجا بنویسید (خلاصه کوتاه و کلیدی)...');
                }
            });
        </script>
        <?php
    }
}
add_action( 'admin_footer', 'sahab_automate_layout_features' );

// ۷. تمیزکاری اجباری منوی تنظیمات صفحه و فعال‌سازی چکیده به صورت پیش‌فرض برای همه
function sahab_force_enable_default_meta_boxes($hidden, $screen) {
    if ( $screen->id === 'post' ) {
        // فیلدهای بلااستفاده که باید مخفی شوند
        $hidden = array('postcustom', 'trackbacksdiv', 'commentstatusdiv', 'commentsdiv', 'slugdiv', 'formatdiv');
        
        // اطمینان از اینکه چکیده (postexcerpt) هرگز در این آرایه مخفی‌ها قرار نمی‌گیرد
        if (($key = array_search('postexcerpt', $hidden)) !== false) {
            unset($hidden[$key]);
        }

        // همچنین اطمینان حاصل می‌کنیم که تصویر شاخص همیشه نمایش داده شود
        if (($key2 = array_search('_thumbnail_id', $hidden)) !== false) {
            unset($hidden[$key2]);
        }
    }
    return $hidden;
}
add_filter( 'hidden_meta_boxes', 'sahab_force_enable_default_meta_boxes', 99, 2 );
add_filter( 'default_hidden_meta_boxes', 'sahab_force_enable_default_meta_boxes', 99, 2 );
