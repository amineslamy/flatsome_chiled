<?php
/**
 * پلتفرم هوشمند سحاب (OSINT)
 * ماژول پردازش آماری و تجمیع داده‌ها (نسخه ۱۰۰٪ داینامیک و متصل به فیلدهای واقعی ACF)
 * مسیر فایل: inc/sahab-bi-reporting.php
 */

if (!defined('ABSPATH')) {
    exit;
}

function sahab_get_advanced_bi_report($start_date = '', $end_date = '', $filter_dept = '', $filter_analyst = '') {
    if (empty($start_date)) $start_date = date('Y-m-d', strtotime('-30 days'));
    if (empty($end_date)) $end_date = date('Y-m-d');

    // ۱. استخراج خودکار و داینامیک گزینه‌ها و برچسب‌های فارسی از تنظیمات ACF شما
    $acf_fields = array('subject', 'classification', 'priority', 'news_type', 'evaluation');
    $dynamic_choices = array();
    
    foreach ($acf_fields as $field_name) {
        $field_info = acf_get_field($field_name);
        $dynamic_choices[$field_name] = ( $field_info && isset($field_info['choices']) ) ? $field_info['choices'] : array();
    }

    // ۲. استخراج داینامیک کاربران سیستم
    $all_users = get_users(array('fields' => array('ID', 'display_name')));
    $managers = array();
    $analysts = array();

    foreach ($all_users as $user) {
        if ($user->ID === 1 || strtolower($user->display_name) === 'administrator') {
            continue; 
        }

        $manager_id = get_field('reports_to', 'user_' . $user->ID);

        if ($manager_id) {
            $manager_id = intval($manager_id);
            
            // مقداردهی اولیه سبد آماری کارشناس بر اساس گزینه‌های زنده ACF
            $analysts[$user->ID] = array(
                'name' => $user->display_name,
                'manager_id' => $manager_id,
                'news_count' => 0,
                'subjects' => array_fill_keys(array_keys($dynamic_choices['subject']), 0),
                'classifications' => array_fill_keys(array_keys($dynamic_choices['classification']), 0),
                'priorities' => array_fill_keys(array_keys($dynamic_choices['priority']), 0),
                'news_types' => array_fill_keys(array_keys($dynamic_choices['news_type']), 0),
                'evaluations' => array_fill_keys(array_keys($dynamic_choices['evaluation']), 0)
            );

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

    // ۳. اعمال فیلترهای سلسله‌مراتب (کل سامانه / اداره خاص / کارشناس خاص)
    $target_author_ids = array();
    if (!empty($filter_analyst)) {
        $target_author_ids = array(intval($filter_analyst));
    } elseif (!empty($filter_dept) && isset($managers[$filter_dept])) {
        $target_author_ids = $managers[$filter_dept]['subordinates'];
    } else {
        $target_author_ids = array_keys($analysts);
    }

    if (empty($target_author_ids)) {
        return array('departments' => $managers, 'analysts' => array(), 'total_processed' => 0, 'global_stats' => array(), 'choices' => $dynamic_choices);
    }

    // ۴. کوئری به دیتابیس برای دریافت اسناد خبری
    $args = array(
        'post_type'      => 'post', 
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'author__in'     => $target_author_ids,
        'date_query'     => array(
            array(
                'after'     => $start_date,
                'before'    => $end_date,
                'inclusive' => true,
            ),
        ),
    );
    
    $news_ids = get_posts($args);

    // مقداردهی سبد آمار کلان سیستم بر اساس گزینه‌های زنده ACF
    $global_stats = array(
        'subjects' => array_fill_keys(array_keys($dynamic_choices['subject']), 0),
        'classifications' => array_fill_keys(array_keys($dynamic_choices['classification']), 0),
        'priorities' => array_fill_keys(array_keys($dynamic_choices['priority']), 0),
        'news_types' => array_fill_keys(array_keys($dynamic_choices['news_type']), 0),
        'evaluations' => array_fill_keys(array_keys($dynamic_choices['evaluation']), 0),
        'timeline' => array() // برای نمودار روند زمان
    );

    // ۵. پردازش اسناد و تجمیع داده‌ها بر اساس کلیدهای واقعی دیتابیس
    foreach ($news_ids as $post_id) {
        $author_id = intval(get_post_field('post_author', $post_id));
        $post_date = get_the_date('Y-m-d', $post_id);
        
        if (isset($analysts[$author_id])) {
            $analysts[$author_id]['news_count']++;
            
            // الف) محاسبه روند زمانی
            if (!isset($global_stats['timeline'][$post_date])) {
                $global_stats['timeline'][$post_date] = 0;
            }
            $global_stats['timeline'][$post_date]++;

            // ب) پردازش موضوع (چک‌باکس)
            $sub_values = get_field('subject', $post_id);
            if (!empty($sub_values)) {
                $sub_values = is_array($sub_values) ? $sub_values : array($sub_values);
                foreach ($sub_values as $val) {
                    if (isset($global_stats['subjects'][$val])) {
                        $global_stats['subjects'][$val]++;
                        $analysts[$author_id]['subjects'][$val]++;
                    }
                }
            }

            // ج) بقیه فیلدهای انتخابی (رشته واحد)
            foreach (array('classification', 'priority', 'news_type', 'evaluation') as $f_key) {
                $plural_key = $f_key . 's';
                $val = get_field($f_key, $post_id);
                if ($val && isset($global_stats[$plural_key][$val])) {
                    $global_stats[$plural_key][$val]++;
                    $analysts[$author_id][$plural_key][$val]++;
                }
            }

            // د) تجمیع در آمار اداره
            $m_id = $analysts[$author_id]['manager_id'];
            if (isset($managers[$m_id])) {
                $managers[$m_id]['total_news']++;
            }
        }
    }

    // مرتب‌سازی تاریخ‌های نمودار روند از قدیم به جدید
    ksort($global_stats['timeline']);

    return array(
        'departments'     => $managers,
        'analysts'        => $analysts,
        'total_processed' => count($news_ids),
        'global_stats'    => $global_stats,
        'choices'         => $dynamic_choices
    );
}