<?php
/**
 * پلتفرم هوشمند سحاب (OSINT)
 * ماژول ماژولار پردازش آماری و تجمیع داده‌ها بر پایه فیلد سایه شمسی
 * مسیر فایل: inc/sahab-bi-reporting.php
 */

if (!defined('ABSPATH')) {
    exit;
}

function sahab_get_advanced_bi_report($start_date = '', $end_date = '', $filter_dept = '', $filter_analyst = '', $filter_case = '', $filter_subject = '')
{
    // محاسبه پویا و بومی تاریخ امروز و یک ماه قبل با تابع پارسی‌دیت فعال در سحاب
    if (empty($end_date)) {
        $end_date = function_exists('parsidate') ? parsidate('Y/m/d', current_time('mysql'), 'eng') : '1405/04/31';
    }
    if (empty($start_date)) {
        if (function_exists('parsidate')) {
            $thirty_days_ago_mysql = date('Y-m-d H:i:s', current_time('timestamp') - (30 * DAY_IN_SECONDS));
            $start_date = parsidate('Y/m/d', $thirty_days_ago_mysql, 'eng');
        } else {
            $start_date = '1405/03/01';
        }
    }

    $start_date = str_replace('-', '/', $start_date);
    $end_date = str_replace('-', '/', $end_date);

    // ۱. استخراج خودکار و داینامیک گزینه‌های ACF و دسته‌بندی‌های بومی (کیس‌ها)
    $acf_fields = array('subject', 'classification', 'priority', 'news_type', 'evaluation');
    $dynamic_choices = array();
    foreach ($acf_fields as $field_name) {
        $field_info = acf_get_field($field_name);
        $dynamic_choices[$field_name] = ($field_info && isset($field_info['choices'])) ? $field_info['choices'] : array();
    }

    // واکشی داینامیک کیس‌ها به عنوان دسته‌بندی‌های بومی (category) وردپرس
    $categories = get_categories(array('hide_empty' => false));
    $dynamic_choices['case'] = array();
    foreach ($categories as $cat) {
        $dynamic_choices['case'][$cat->slug] = $cat->name;
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
        return array('departments' => $managers, 'analysts' => array(), 'total_processed' => 0, 'total_active_cases' => 0, 'global_stats' => array(), 'choices' => $dynamic_choices);
    }

    // ۴. ساختار شرطی مگا-کوئری روی فیلد سایه شمسی و فیلترهای انتخابی سحاب
    $meta_query = array(
        'relation' => 'AND',
        array(
            'key' => 'sahab_reg_date_shamsi',
            'value' => array($start_date, $end_date),
            'compare' => 'BETWEEN',
            'type' => 'CHAR'
        )
    );

    // فیلتر انتخابی موضوع (ACF Checkbox)
    if (!empty($filter_subject)) {
        $meta_query[] = array(
            'key' => 'subject',
            'value' => '"' . $filter_subject . '"',
            'compare' => 'LIKE'
        );
    }

    $args = array(
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'author__in' => $target_author_ids,
        'meta_query' => $meta_query
    );

    // اعمال فیلتر کیس به عنوان دسته‌بندی بومی وردپرس
    if (!empty($filter_case)) {
        $args['category_name'] = $filter_case;
    }

    $news_ids = get_posts($args);

    $global_stats = array(
        'subjects'        => array_fill_keys(array_keys($dynamic_choices['subject']), 0),
        'classifications' => array_fill_keys(array_keys($dynamic_choices['classification']), 0),
        'priorities'      => array_fill_keys(array_keys($dynamic_choices['priority']), 0),
        'news_types'      => array_fill_keys(array_keys($dynamic_choices['news_type']), 0),
        'evaluations'     => array_fill_keys(array_keys($dynamic_choices['evaluation']), 0),
        'cases'           => array(),
        'timeline'        => array()
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

            $post_cats = wp_get_post_categories($post_id, array('fields' => 'all'));
            foreach ($post_cats as $cat) {
                if (!isset($global_stats['cases'][$cat->slug])) {
                    $global_stats['cases'][$cat->slug] = array('name' => $cat->name, 'count' => 0);
                }
                $global_stats['cases'][$cat->slug]['count']++;
            }

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

    // استخراج داینامیک تعداد کیس‌های فعال بومی در این بازه گزارش‌گیری
    $active_cases_count = 0;
    $cases_in_posts = array();
    foreach ($news_ids as $post_id) {
        $post_cats = wp_get_post_categories($post_id, array('fields' => 'ids'));
        if (!empty($post_cats)) {
            $cases_in_posts = array_merge($cases_in_posts, $post_cats);
        }
    }
    $active_cases_count = count(array_unique($cases_in_posts));
    if ($active_cases_count === 0) {
        $active_cases_count = count($dynamic_choices['case']);
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