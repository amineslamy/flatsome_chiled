<?php
/**
 * پلتفرم هوشمند سحاب (OSINT)
 * ماژول ماژولار پردازش آماری و تجمیع داده‌ها بر پایه فیلد سایه شمسی
 * مسیر فایل: inc/sahab-bi-reporting.php
 */

if (!defined('ABSPATH')) {
    exit;
}

function sahab_get_advanced_bi_report($start_date = '', $end_date = '', $filter_dept = '', $filter_analyst = '')
{
    // اصلاح فرمت تاریخ‌های ورودی (تبدیل احتمالی خط تیره به اسلش برای مطابقت با دیتابیس)
    if (empty($start_date))
        $start_date = '1405/03/01';
    if (empty($end_date))
        $end_date = '1405/04/31';

    $start_date = str_replace('-', '/', $start_date);
    $end_date = str_replace('-', '/', $end_date);

    // ۱. استخراج خودکار و داینامیک گزینه‌های ACF (شامل کیس و سایر فیلدهای ارزیابی)
    $acf_fields = array('subject', 'classification', 'priority', 'news_type', 'evaluation', 'case');
    $dynamic_choices = array();
    foreach ($acf_fields as $field_name) {
        $field_info = acf_get_field($field_name);
        $dynamic_choices[$field_name] = ($field_info && isset($field_info['choices'])) ? $field_info['choices'] : array();
    }

    // ۲. استخراج داینامیک کاربران سیستم و ساختار درختی ادارات
    $all_users = get_users(array('fields' => array('ID', 'display_name')));
    $managers = array();
    $analysts = array();

    // ابتدا ایجاد لیست مدیران/ادارات معتبر
    foreach ($all_users as $user) {
        if ($user->ID === 1 || strtolower($user->display_name) === 'administrator') {
            continue;
        }
        $manager_id = get_field('reports_to', 'user_' . $user->ID);
        if ($manager_id) {
            $manager_id = intval($manager_id);
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

    // سپس ایجاد اطلاعات پایه کارشناسان
    foreach ($all_users as $user) {
        if ($user->ID === 1 || strtolower($user->display_name) === 'administrator') {
            continue;
        }
        $manager_id = get_field('reports_to', 'user_' . $user->ID);
        $manager_id = $manager_id ? intval($manager_id) : 0;

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
    }

    // ۳. تعیین پرسنل هدف بر اساس فیلترهای بالا
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

    // ۴. کوئری هوشمند مگا-دیتا مستقیماً روی فیلد سایه شمسی سحاب
    $args = array(
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'author__in' => $target_author_ids,
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key' => 'sahab_reg_date_shamsi',
                'value' => array($start_date, $end_date),
                'compare' => 'BETWEEN',
                'type' => 'CHAR' // ساختار متنی اسلش‌دار رشته‌ای
            )
        )
    );

    $news_ids = get_posts($args);

    $global_stats = array(
        'subjects' => array_fill_keys(array_keys($dynamic_choices['subject']), 0),
        'classifications' => array_fill_keys(array_keys($dynamic_choices['classification']), 0),
        'priorities' => array_fill_keys(array_keys($dynamic_choices['priority']), 0),
        'news_types' => array_fill_keys(array_keys($dynamic_choices['news_type']), 0),
        'evaluations' => array_fill_keys(array_keys($dynamic_choices['evaluation']), 0),
        'timeline' => array()
    );

    // ۵. تجمیع دقیق اسناد یافت شده
    foreach ($news_ids as $post_id) {
        $author_id = intval(get_post_field('post_author', $post_id));

        // استخراج مستقیم تاریخ شمسی از فیلد سایه برای نمودار روند زمان
        $post_jalali_date = get_post_meta($post_id, 'sahab_reg_date_shamsi', true);
        if (empty($post_jalali_date)) {
            continue;
        }

        if (isset($analysts[$author_id])) {
            $analysts[$author_id]['news_count']++;

            // آمار روند زمان نمودار
            if (!isset($global_stats['timeline'][$post_jalali_date])) {
                $global_stats['timeline'][$post_jalali_date] = 0;
            }
            $global_stats['timeline'][$post_jalali_date]++;

            // پردازش موضوع (چک‌باکس)
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

            // مابقی فیلدهای انتخابی ACF
            foreach (array('classification', 'priority', 'news_type', 'evaluation') as $f_key) {
                $plural_key = $f_key . 's';
                $val = get_field($f_key, $post_id);
                if ($val && isset($global_stats[$plural_key][$val])) {
                    $global_stats[$plural_key][$val]++;
                    $analysts[$author_id][$plural_key][$val]++;
                }
            }

            $m_id = $analysts[$author_id]['manager_id'];
            if ($m_id && isset($managers[$m_id])) {
                $managers[$m_id]['total_news']++;
            }
        }
    }

    // حذف تحلیلگران بدون فعالیت در این بازه یا فیلتر جاری برای خلوت شدن کارنامه (اختیاری اما حرفه‌ای)
    if (!empty($filter_analyst) || !empty($filter_dept)) {
        foreach ($analysts as $a_id => $data) {
            if (!in_array($a_id, $target_author_ids)) {
                unset($analysts[$a_id]);
            }
        }
    }

    // مرتب‌سازی کلیدهای تاریخ روند
    if (!empty($global_stats['timeline'])) {
        ksort($global_stats['timeline']);
    }

    // استخراج داینامیک تعداد کیس‌های فعال در این بازه گزارش‌گیری
    $active_cases_count = 0;
    $cases_in_posts = array();
    foreach ($news_ids as $post_id) {
        $post_case = get_field('case', $post_id);
        if (!empty($post_case)) {
            $cases_in_posts[] = $post_case;
        }
    }
    $active_cases_count = count(array_unique($cases_in_posts));
    if ($active_cases_count === 0) {
        $active_cases_count = count($dynamic_choices['case']); // برگشت به تعداد کل فیلد در صورت عدم وجود داده در بازه
    }

    return array(
        'departments' => $managers,
        'analysts' => $analysts,
        'total_processed' => count($news_ids),
        'total_active_cases' => $active_cases_count,
        'global_stats' => $global_stats,
        'choices' => $dynamic_choices
    );
}