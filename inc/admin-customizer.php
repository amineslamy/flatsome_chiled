<?php
/**
 * شخصی‌سازی تمیز، سبک و امن صفحه ایجاد خبر سحاب - نسخه پایدار بومی
 */

function sahab_custom_admin_editor_styles() {
    $screen = get_current_screen();
    if ( $screen && isset( $screen->post_type ) && $screen->post_type === 'post' ) {
        $custom_css = "
            /* ۱. شخصی‌سازی رنگ بارها بدون تغییر در هندسه صفحه */
            #wpadminbar { background: #1e293b !important; }
            #wpcontent { 
                padding-top: 55px !important; 
                background: #f8fafc !important; 
            }
            
            /* ۲. مدرن و فلت کردن باکس‌ها بدون دستکاری موقعیت آن‌ها */
            .postbox { 
                border-radius: 8px !important; 
                border: 1px solid #e2e8f0 !important; 
                box-shadow: none !important; 
                background: #ffffff !important;
            }
            .postbox-header { 
                border-bottom: 1px solid #f1f5f9 !important; 
                background: #f8fafc !important; 
            }
            .postbox-header h2 { 
                font-weight: bold !important; 
                color: #334155 !important; 
            }

            /* ۳. راست‌چین کردن اصولی فیلدهای متنی */
            #postexcerpt .inside textarea {
                direction: rtl !important;
                text-align: right !important;
                width: 100% !important;
            }
            
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

            /* استایل عمومی دکمه‌های ناوبری بالای صفحه */
            .sahab-admin-top-nav a { text-decoration: none !important; font-size: 13px !important; font-family: tahoma, sans-serif !important; }
        ";
        wp_register_style( 'sahab-admin-inline', false );
        wp_enqueue_style( 'sahab-admin-inline' );
        wp_add_inline_style( 'sahab-admin-inline', $custom_css );
    }
}
add_action( 'admin_enqueue_scripts', 'sahab_custom_admin_editor_styles' );

// ۵. تزریق نوار ناوبری شبیه‌سازی شده فرانت‌آند در بالای صفحه
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
