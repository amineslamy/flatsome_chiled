<?php
/**
 * پلتفرم هوشمند سحاب (OSINT)
 * ماژول ماژولار پردازش آماری و تجمیع داده‌های ادارات و کارشناسان
 * مسیر فایل: inc/sahab-bi-reporting.php
 */

// جلوگیری از دسترسی مستقیم به فایل
if (!defined('ABSPATH')) {
    exit;
}

/**
 * دریافت گزارش داینامیک بر اساس بازه زمانی و سلسله‌مراتب سازمانی
 */
function sahab_get_dynamic_bi_report($start_date = '', $end_date = '') {
    // اگر تاریخی ارسال نشده باشد، بازه ۳۰ روز گذشته را پیش‌فرض قرار می‌دهد
    if (empty($start_date)) {
        $start_date = date('Y-m-d', strtotime('-30 days'));
    }
    if (empty($end_date)) {
        $end_date = date('Y-m-d');
    }

    // ۱. دریافت تمامی کاربران سیستم به صورت داینامیک
    $all_users = get_users(array('fields' => array('ID', 'display_name', 'roles')));
    
    $managers = array();
    $analysts = array();

    // تفکیک داینامیک مدیران و کارشناسان بر اساس نقش وردپرس
    foreach ($all_users as $user) {
        // سپر امنیتی: تبدیل نقش‌ها به آرایه برای جلوگیری از خطای Argument #2 must be of type array
        $user_roles = is_array($user->roles) ? $user->roles : array();

        if (in_array('editor', $user_roles)) {
            $managers[$user->ID] = array(
                'name' => $user->display_name,
                'total_news' => 0,
                'subordinates' => array()
            );
        } elseif (in_array('author', $user_roles) || in_array('administrator', $user_roles)) {
            // پیدا کردن مدیر مستقیم کارشناس از طریق فیلد ACF
            $manager_id = get_field('reports_to', 'user_' . $user->ID);
            $analysts[$user->ID] = array(
                'name' => $user->display_name,
                'manager_id' => $manager_id ? intval($manager_id) : 0,
                'news_count' => 0,
                'footnotes' => array('molaheze' => 0, 'nazarie' => 0, 'baznevisi' => 0)
            );
            
            // اضافه کردن کارشناس به لیست زیرمجموعه مدیرش به صورت داینامیک
            if ($manager_id && isset($managers[$manager_id])) {
                $managers[$manager_id]['subordinates'][] = $user->ID;
            }
        }
    }

    // ۲. کوئری به دیتابیس برای دریافت اخبار در بازه زمانی مشخص شده
    $args = array(
        'post_type'      => 'post', // یا پست‌تایپ اختصاصی اخبار سحاب شما
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'date_query'     => array(
            array(
                'after'     => $start_date,
                'before'    => $end_date,
                'inclusive' => true,
            ),
        ),
    );
    
    $news_ids = get_posts($args);

    // ۳. پردازش داینامیک اسناد خبری و تجمیع آمار
    foreach ($news_ids as $post_id) {
        $author_id = intval(get_post_field('post_author', $post_id));
        
        // اگر نویسنده خبر جزو کارشناسان تعریف شده باشد
        if (isset($analysts[$author_id])) {
            $analysts[$author_id]['news_count']++;
            
            // بررسی داینامیک پی‌نوشت‌ها بر اساس ساختار ACF خبر شما
            $footnote_type = get_field('footnote_type', $post_id); 
            if ($footnote_type === 'molaheze') {
                $analysts[$author_id]['footnotes']['molaheze']++;
            }
            if ($footnote_type === 'nazarie') {
                $analysts[$author_id]['footnotes']['nazarie']++;
            }
            if ($footnote_type === 'baznevisi') {
                $analysts[$author_id]['footnotes']['baznevisi']++;
            }

            // اضافه کردن به آمار کل مدیر/اداره تابعه به صورت داینامیک
            $m_id = $analysts[$author_id]['manager_id'];
            if ($m_id && isset($managers[$m_id])) {
                $managers[$m_id]['total_news']++;
            }
        }
    }

    return array(
        'departments' => $managers,
        'analysts'    => $analysts,
        'total_processed' => count($news_ids)
    );
}