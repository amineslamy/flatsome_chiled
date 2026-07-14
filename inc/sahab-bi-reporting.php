<?php
/**
 * پلتفرم هوشمند سحاب (OSINT)
 * ماژول پردازش آماری و تجمیع داده‌های ادارات و کارشناسان (نسخه داینامیک مستقل از نقش)
 * مسیر فایل: inc/sahab-bi-reporting.php
 */

if (!defined('ABSPATH')) {
    exit;
}

function sahab_get_dynamic_bi_report($start_date = '', $end_date = '') {
    if (empty($start_date)) $start_date = date('Y-m-d', strtotime('-30 days'));
    if (empty($end_date)) $end_date = date('Y-m-d');

    // ۱. دریافت تمامی کاربران بدون فیلتر کردن روی نقش انگلیسی برای پایداری ۱۰۰٪
    $all_users = get_users(array('fields' => array('ID', 'display_name')));
    
    $managers = array();
    $analysts = array();

    // گام اول: شناسایی کارشناسان و مدیران بر اساس اتصالات واقعی ACF
    foreach ($all_users as $user) {
        if ($user->ID === 1 || strtolower($user->display_name) === 'administrator') {
            continue; // حذف کاربر ادمین اصلی از جدول آماری
        }

        $manager_id = get_field('reports_to', 'user_' . $user->ID);

        if ($manager_id) {
            $manager_id = intval($manager_id);
            
            // ثبت کارشناس به صورت داینامیک
            $analysts[$user->ID] = array(
                'name' => $user->display_name,
                'manager_id' => $manager_id,
                'news_count' => 0,
                'footnotes' => array('molaheze' => 0, 'nazarie' => 0, 'baznevisi' => 0)
            );

            // ایجاد داینامیک ساختار اداره برای مدیر، اگر قبلاً ثبت نشده باشد
            if (!isset($managers[$manager_id])) {
                $manager_data = get_userdata($manager_id);
                $managers[$manager_id] = array(
                    'name' => $manager_data ? $manager_data->display_name : 'اداره تابعه سحاب',
                    'total_news' => 0,
                    'subordinates' => array()
                );
            }
            $managers[$manager_id]['subordinates'][] = $user->ID;
        }
    }

    // ۲. کوئری به دیتابیس برای دریافت اخبار در بازه زمانی فیلتر شده
    $args = array(
        'post_type'      => 'post', 
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

    // ۳. تجمیع داده‌ها زنجیره‌ای
    foreach ($news_ids as $post_id) {
        $author_id = intval(get_post_field('post_author', $post_id));
        
        if (isset($analysts[$author_id])) {
            $analysts[$author_id]['news_count']++;
            
            // بررسی پی‌نوشت‌ها (با نام فیلد سفارشی ACF شما در پلتفرم)
            $footnote_type = get_field('footnote_type', $post_id); 
            if ($footnote_type === 'molaheze')  $analysts[$author_id]['footnotes']['molaheze']++;
            if ($footnote_type === 'nazarie')   $analysts[$author_id]['footnotes']['nazarie']++;
            if ($footnote_type === 'baznevisi')  $analysts[$author_id]['footnotes']['baznevisi']++;

            $m_id = $analysts[$author_id]['manager_id'];
            if (isset($managers[$m_id])) {
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