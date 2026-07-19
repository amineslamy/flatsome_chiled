<?php
/**
 * Template Name: Advanced Search Page
 */

get_header(); ?>

<div id="content" class="content-area page-wrapper" style="padding: 30px 0; background-color: #fcfcfc;">
    <div class="row">
        <div class="large-12 col">

            <!-- ۱. هدر جدید، شکیل، جمع‌و‌جور و رنگی با تم سازمانی سحاب -->
            <header class="entry-header text-center"
                style="margin-bottom: 25px; background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%); padding: 25px 20px; border-radius: 10px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                <h1 class="entry-title"
                    style="color: #ffffff; font-weight: 800; font-size: 24px; margin-bottom: 8px; letter-spacing: -0.5px;">
                    <span style="vertical-align: middle; margin-left: 8px;">⚙️</span>موتور جامع جستجوی پیشرفته سحاب
                </h1>
                <p style="color: #93c5fd; font-size: 13px; margin-bottom: 0; font-weight: 300;">فیلترهای پیشرفته و
                    هوشمند برای استخراج دقیق اطلاعات و گزارش‌ها</p>
            </header>

            <div class="entry-content">
                <form method="get" action="" class="sahab-adv-search-form"
                    style="direction: rtl; text-align: right; margin: 0;">
                    <div
                        style="background: #ffffff; padding: 25px; border-radius: 10px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 30px;">
                        <input type="hidden" name="load_all" id="sahab-load-all"
                            value="<?php echo isset($_GET['load_all']) ? esc_attr($_GET['load_all']) : '0'; ?>">

                        <!-- ردیف اول: کلمه کلیدی جستجو و شناسه خودکار -->
                        <div class="row">
                            <div class="large-9 col" style="margin-bottom: 15px;">
                                <label
                                    style="font-weight: bold; display: block; margin-bottom: 6px; color: #334155; font-size: 13px;">🔎
                                    کلمه کلیدی جستجو (شامل متن، عنوان و کامنت‌ها):</label>
                                <input type="text" name="as_s"
                                    value="<?php echo isset($_GET['as_s']) ? esc_attr($_GET['as_s']) : ''; ?>"
                                    placeholder="عبارت مورد نظر را وارد کنید..."
                                    style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #cbd5e1; height: 42px;">
                            </div>
                            <div class="large-3 col" style="margin-bottom: 15px;">
                                <label
                                    style="font-weight: bold; display: block; margin-bottom: 6px; color: #334155; font-size: 13px;">🆔
                                    شناسه خودکار:</label>
                                <input type="text" name="as_f_id"
                                    value="<?php echo isset($_GET['as_f_id']) ? esc_attr($_GET['as_f_id']) : ''; ?>"
                                    placeholder="مثال: 1245"
                                    style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #cbd5e1; height: 42px;">
                            </div>
                        </div>

                        <!-- ردیف دوم: فیلترهای کشویی اختصاصی داشبورد (موضوع، نوع خبر، ارزیابی) -->
                        <div class="row">
                            <div class="large-4 col" style="margin-bottom: 15px;">
                                <label
                                    style="font-weight: bold; display: block; margin-bottom: 6px; color: #334155; font-size: 13px;">📌
                                    موضوع:</label>
                                <input type="text" name="as_f_subject"
                                    value="<?php echo isset($_GET['as_f_subject']) ? esc_attr($_GET['as_f_subject']) : ''; ?>"
                                    placeholder="موضوع خبر..."
                                    style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #cbd5e1; height: 42px;">
                            </div>
                            <div class="large-4 col" style="margin-bottom: 15px;">
                                <label
                                    style="font-weight: bold; display: block; margin-bottom: 6px; color: #334155; font-size: 13px;">📰
                                    نوع خبر:</label>
                                <input type="text" name="as_f_type"
                                    value="<?php echo isset($_GET['as_f_type']) ? esc_attr($_GET['as_f_type']) : ''; ?>"
                                    placeholder="نوع خبر..."
                                    style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #cbd5e1; height: 42px;">
                            </div>
                            <div class="large-4 col" style="margin-bottom: 15px;">
                                <label
                                    style="font-weight: bold; display: block; margin-bottom: 6px; color: #334155; font-size: 13px;">📊
                                    ارزیابی:</label>
                                <input type="text" name="as_f_case"
                                    value="<?php echo isset($_GET['as_f_case']) ? esc_attr($_GET['as_f_case']) : ''; ?>"
                                    placeholder="ارزیابی..."
                                    style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #cbd5e1; height: 42px;">
                            </div>
                        </div>

                        <!-- ردیف سوم: فیلترهای تاریخ کامل (ثبت و وقوع) -->
                        <div class="row">
                            <div class="large-6 col" style="margin-bottom: 15px;">
                                <label
                                    style="font-weight: bold; display: block; margin-bottom: 6px; color: #334155; font-size: 13px;">📅
                                    بازه تاریخ ثبت (انتشار در سایت):</label>
                                <div style="display: flex; gap: 10px;">
                                    <input type="text" name="start_reg" class="sahab-pdate" data-jdp
                                        value="<?php echo isset($_GET['start_reg']) ? esc_attr($_GET['start_reg']) : ''; ?>"
                                        placeholder="از تاریخ"
                                        style="width: 50%; padding: 8px; border-radius: 6px; border: 1px solid #cbd5e1; height: 42px;"
                                        autocomplete="off">
                                    <input type="text" name="end_reg" class="sahab-pdate" data-jdp
                                        value="<?php echo isset($_GET['end_reg']) ? esc_attr($_GET['end_reg']) : ''; ?>"
                                        placeholder="تا تاریخ"
                                        style="width: 50%; padding: 8px; border-radius: 6px; border: 1px solid #cbd5e1; height: 42px;"
                                        autocomplete="off">
                                </div>
                            </div>

                            <div class="large-6 col" style="margin-bottom: 15px;">
                                <label
                                    style="font-weight: bold; display: block; margin-bottom: 6px; color: #334155; font-size: 13px;">⏱️
                                    بازه تاریخ وقوع رویداد:</label>
                                <div style="display: flex; gap: 10px;">
                                    <input type="text" name="start_event" class="sahab-pdate" data-jdp
                                        value="<?php echo isset($_GET['start_event']) ? esc_attr($_GET['start_event']) : ''; ?>"
                                        placeholder="از تاریخ"
                                        style="width: 50%; padding: 8px; border-radius: 6px; border: 1px solid #cbd5e1; height: 42px;"
                                        autocomplete="off">
                                    <input type="text" name="end_event" class="sahab-pdate" data-jdp
                                        value="<?php echo isset($_GET['end_event']) ? esc_attr($_GET['end_event']) : ''; ?>"
                                        placeholder="تا تاریخ"
                                        style="width: 50%; padding: 8px; border-radius: 6px; border: 1px solid #cbd5e1; height: 42px;"
                                        autocomplete="off">
                                </div>
                            </div>
                        </div>

                        <!-- ردیف چهارم: کیس، برچسب، کارشناس و ثبت‌کننده (همگی مجهز به باکس جستجوی سریع متنی) -->
                        <div class="row">
                            <!-- ۱. کیس (دسته‌بندی‌ها) -->
                            <div class="large-3 col" style="margin-bottom: 15px;">
                                <label
                                    style="font-weight: bold; display: block; margin-bottom: 6px; color: #334155; font-size: 13px;">📁
                                    کیس:</label>
                                <input type="text" id="cat-search" placeholder="🔍 جستجوی سریع کیس‌ها..."
                                    style="width: 100%; padding: 5px 10px; font-size: 12px; margin-bottom: 6px; border-radius: 6px; border: 1px solid #cbd5e1; height: 34px;">
                                <div class="sahab-cat-box"
                                    style="max-height: 140px; overflow-y: auto; background: #fff; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                                    <?php
                                    $categories = get_categories(array('hide_empty' => 0));
                                    $selected_cats = isset($_GET['as_cats']) ? (array) $_GET['as_cats'] : array();
                                    foreach ($categories as $cat) {
                                        echo '<label style="display:block; font-weight:normal; margin-bottom:4px; font-size:12px;"><input type="checkbox" name="as_cats[]" value="' . $cat->term_id . '" ' . checked(in_array($cat->term_id, $selected_cats), true, false) . '> ' . esc_html($cat->name) . '</label>';
                                    }
                                    ?>
                                </div>
                            </div>

                            <!-- ۲. برچسب (هشتگ) -->
                            <div class="large-3 col" style="margin-bottom: 15px;">
                                <label
                                    style="font-weight: bold; display: block; margin-bottom: 6px; color: #334155; font-size: 13px;">🏷️
                                    برچسب (هشتگ):</label>
                                <input type="text" id="tag-search" placeholder="🔍 جستجوی سریع برچسب‌ها..."
                                    style="width: 100%; padding: 5px 10px; font-size: 12px; margin-bottom: 6px; border-radius: 6px; border: 1px solid #cbd5e1; height: 34px;">
                                <div class="sahab-tag-box"
                                    style="max-height: 140px; overflow-y: auto; background: #fff; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                                    <?php
                                    $tags = get_tags(array('hide_empty' => 0));
                                    $selected_tags = isset($_GET['as_tags']) ? (array) $_GET['as_tags'] : array();
                                    foreach ($tags as $tag) {
                                        echo '<label style="display:block; font-weight:normal; margin-bottom:4px; font-size:12px;"><input type="checkbox" name="as_tags[]" value="' . $tag->term_id . '" ' . checked(in_array($tag->term_id, $selected_tags), true, false) . '> ' . esc_html($tag->name) . '</label>';
                                    }
                                    ?>
                                </div>
                            </div>

                            <!-- ۳. کارشناس -->
                            <div class="large-3 col" style="margin-bottom: 15px;">
                                <label
                                    style="font-weight: bold; display: block; margin-bottom: 6px; color: #334155; font-size: 13px;">👤
                                    کارشناس:</label>
                                <input type="text" id="expert-search" placeholder="🔍 جستجوی سریع کارشناسان..."
                                    style="width: 100%; padding: 5px 10px; font-size: 12px; margin-bottom: 6px; border-radius: 6px; border: 1px solid #cbd5e1; height: 34px;">
                                <div class="sahab-expert-box"
                                    style="max-height: 140px; overflow-y: auto; background: #fff; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                                    <?php
                                    $users = get_users(array('fields' => array('ID', 'display_name')));
                                    $selected_experts = isset($_GET['as_experts']) ? (array) $_GET['as_experts'] : array();
                                    foreach ($users as $user) {
                                        echo '<label style="display:block; font-weight:normal; margin-bottom:4px; font-size:12px;"><input type="checkbox" name="as_experts[]" value="' . $user->ID . '" ' . checked(in_array($user->ID, $selected_experts), true, false) . '> ' . esc_html($user->display_name) . '</label>';
                                    }
                                    ?>
                                </div>
                            </div>

                            <!-- ۴. ثبت کننده -->
                            <div class="large-3 col" style="margin-bottom: 15px;">
                                <label
                                    style="font-weight: bold; display: block; margin-bottom: 6px; color: #334155; font-size: 13px;">✍️
                                    ثبت کننده:</label>
                                <input type="text" id="author-search" placeholder="🔍 جستجوی سریع ثبت کنندگان..."
                                    style="width: 100%; padding: 5px 10px; font-size: 12px; margin-bottom: 6px; border-radius: 6px; border: 1px solid #cbd5e1; height: 34px;">
                                <div class="sahab-author-box"
                                    style="max-height: 140px; overflow-y: auto; background: #fff; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                                    <?php
                                    $selected_authors = isset($_GET['as_authors']) ? (array) $_GET['as_authors'] : array();
                                    foreach ($users as $user) {
                                        echo '<label style="display:block; font-weight:normal; margin-bottom:4px; font-size:12px;"><input type="checkbox" name="as_authors[]" value="' . $user->ID . '" ' . checked(in_array($user->ID, $selected_authors), true, false) . '> ' . esc_html($user->display_name) . '</label>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>

                        <!-- دکمه‌های فرم عملیات -->
                        <div style="text-align: left; margin-top: 15px;">
                            <button type="submit" class="button primary"
                                style="font-weight: bold; padding: 10px 30px; border-radius: 6px; background-color: #1e3a8a; border: none; margin: 0; height: 42px;">🎯
                                شروع فیلتر و جستجو</button>
                            <a href="<?php echo home_url('/advanced-search/'); ?>" class="button secondary text-dark"
                                style="padding: 10px 20px; border-radius: 6px; margin-right: 10px; margin: 0; height: 42px; line-height: 22px; background-color: #e2e8f0; border: none;">🔄
                                پاکسازی فرم</a>
                        </div>
                    </div>

                    <?php
                    // فعال‌سازی متغیر سراسری وردپرس برای برطرف کردن کلیه اخطارها و خطاهای قرمز رنگ ادیتور
                    global $post;

                    $should_load_all = (isset($_GET['load_all']) && $_GET['load_all'] === '1');

                    if ($should_load_all || !empty($_GET['as_s']) || !empty($_GET['as_f_id']) || !empty($_GET['as_f_subject']) || !empty($_GET['as_f_type']) || !empty($_GET['as_f_case']) || !empty($_GET['start_reg']) || !empty($_GET['end_reg']) || !empty($_GET['start_event']) || !empty($_GET['end_event']) || !empty($_GET['as_cats']) || !empty($_GET['as_tags']) || !empty($_GET['as_experts']) || !empty($_GET['as_authors'])) {

                        $posts_per_page = isset($_GET['as_per_page']) ? intval($_GET['as_per_page']) : 12;
                        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
                        $args = array(
                            'post_type' => 'post',
                            'posts_per_page' => -1,
                            'post_status' => 'publish'
                        );

                        if (!$should_load_all) {
                            // مدیریت فیلترهای آرایه‌ای ثبت‌کننده و کارشناس
                            if (!empty($_GET['as_authors'])) {
                                $args['author__in'] = array_map('intval', $_GET['as_authors']);
                            }
                            if (!empty($_GET['as_cats']))
                                $args['category__in'] = array_map('intval', $_GET['as_cats']);
                            if (!empty($_GET['as_tags']))
                                $args['tag__in'] = array_map('intval', $_GET['as_tags']);

                            $args['meta_query'] = array('relation' => 'AND');

                            // فیلتر کارشناسان (از طریق متادیتا)
                            if (!empty($_GET['as_experts'])) {
                                $args['meta_query'][] = array(
                                    'key' => 'expert',
                                    'value' => array_map('intval', $_GET['as_experts']),
                                    'compare' => 'IN'
                                );
                            }

                            // فیلتر فیلدهای استخراج شده داشبورد
                            if (!empty($_GET['as_f_id'])) {
                                $args['meta_query'][] = array('key' => 'automation_id', 'value' => sanitize_text_field($_GET['as_f_id']), 'compare' => '=');
                            }
                            if (!empty($_GET['as_f_subject'])) {
                                $args['meta_query'][] = array('key' => 'subject', 'value' => sanitize_text_field($_GET['as_f_subject']), 'compare' => 'LIKE');
                            }
                            if (!empty($_GET['as_f_type'])) {
                                $args['meta_query'][] = array('key' => 'news_type', 'value' => sanitize_text_field($_GET['as_f_type']), 'compare' => 'LIKE');
                            }
                            if (!empty($_GET['as_f_case'])) {
                                $args['meta_query'][] = array('key' => 'case', 'value' => sanitize_text_field($_GET['as_f_case']), 'compare' => 'LIKE');
                            }

                            // فیلترهای زمانی
                            if (!empty($_GET['start_reg'])) {
                                $args['meta_query'][] = array('key' => 'sahab_reg_date_shamsi', 'value' => sanitize_text_field($_GET['start_reg']), 'compare' => '>=');
                            }
                            if (!empty($_GET['end_reg'])) {
                                $args['meta_query'][] = array('key' => 'sahab_reg_date_shamsi', 'value' => sanitize_text_field($_GET['end_reg']), 'compare' => '<=');
                            }
                            if (!empty($_GET['start_event'])) {
                                $args['meta_query'][] = array('key' => 'event_date', 'value' => sanitize_text_field($_GET['start_event']), 'compare' => '>=');
                            }
                            if (!empty($_GET['end_event'])) {
                                $args['meta_query'][] = array('key' => 'event_date', 'value' => sanitize_text_field($_GET['end_event']), 'compare' => '<=');
                            }
                        }

                        $order_param = isset($_GET['as_order']) ? $_GET['as_order'] : 'date_desc';
                        switch ($order_param) {
                            case 'date_asc':
                                $args['orderby'] = 'date';
                                $args['order'] = 'ASC';
                                break;
                            case 'title_asc':
                                $args['orderby'] = 'title';
                                $args['order'] = 'ASC';
                                break;
                            case 'title_desc':
                                $args['orderby'] = 'title';
                                $args['order'] = 'DESC';
                                break;
                            case 'event_desc':
                                $args['meta_key'] = 'event_date';
                                $args['orderby'] = 'meta_value';
                                $args['order'] = 'DESC';
                                break;
                            case 'event_asc':
                                $args['meta_key'] = 'event_date';
                                $args['orderby'] = 'meta_value';
                                $args['order'] = 'ASC';
                                break;
                            case 'date_desc':
                            default:
                                $args['orderby'] = 'date';
                                $args['order'] = 'DESC';
                                break;
                        }

                        $raw_query = new WP_Query($args);
                        $final_posts = array();
                        $search_keyword = !empty($_GET['as_s']) ? sanitize_text_field($_GET['as_s']) : '';

                        if ($raw_query->have_posts()) {
                            while ($raw_query->have_posts()) {
                                $raw_query->the_post();
                                if (!empty($search_keyword)) {
                                    if (stripos(get_the_title(), $search_keyword) !== false || stripos(get_the_content(), $search_keyword) !== false || stripos(get_the_excerpt(), $search_keyword) !== false) {
                                        $final_posts[] = $raw_query->post;
                                    }
                                } else {
                                    $final_posts[] = $raw_query->post;
                                }
                            }
                            wp_reset_postdata();
                        }

                        $total_found = count($final_posts);

                        if ($total_found > 0) {
                            if ($posts_per_page > 0) {
                                $chunks = array_chunk($final_posts, $posts_per_page);
                                $current_chunk_index = $paged - 1;
                                $display_posts = isset($chunks[$current_chunk_index]) ? $chunks[$current_chunk_index] : array();
                                $max_num_pages = count($chunks);
                            } else {
                                $display_posts = $final_posts;
                                $max_num_pages = 1;
                            }

                            $current_url = $_SERVER['REQUEST_URI'];
                            $export_url = add_query_arg('export_xml', '1', $current_url);

                            echo '<div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 30px; margin-bottom: 15px; border-bottom: 2px solid #e2e8f0; padding-bottom: 15px; flex-wrap: wrap; gap: 15px; direction: rtl;">';
                            echo '<div style="flex: 1; min-width: 200px; text-align: right;">';
                            echo '<h3 style="margin: 0; color: #1e293b; font-size: 17px; font-weight: bold;">📚 نتایج فیلتر پیشرفته (' . $total_found . ' گزارش):</h3>';
                            echo '</div>';

                            echo '<div style="display: flex; gap: 10px; align-items: flex-end; justify-content: center;">';
                            echo '<div>';
                            echo '<label style="font-size: 11px; font-weight: bold; color: #475569; display: block; margin-bottom: 3px;">🔀 مرتب‌سازی:</label>';
                            echo '<select name="as_order" onchange="this.form.submit();" style="padding: 5px 10px; border-radius: 4px; border: 1px solid #cbd5e1; height: 34px; font-size: 12px; background: #fff; margin:0; width:150px;">';
                            echo '<option value="date_desc"' . selected($order_param, 'date_desc', false) . '>📅 جدیدترین ثبت</option>';
                            echo '<option value="date_asc"' . selected($order_param, 'date_asc', false) . '>📅 قدیمی‌ترین ثبت</option>';
                            echo '<option value="event_desc"' . selected($order_param, 'event_desc', false) . '>⏱️ جدیدترین وقوع</option>';
                            echo '<option value="event_asc"' . selected($order_param, 'event_asc', false) . '>⏱️ قدیمی‌ترین وقوع</option>';
                            echo '<option value="title_asc"' . selected($order_param, 'title_asc', false) . '>📝 عنوان (الف-ی)</option>';
                            echo '<option value="title_desc"' . selected($order_param, 'title_desc', false) . '>📝 عنوان (ی-الف)</option>';
                            echo '</select>';
                            echo '</div>';

                            echo '<div>';
                            echo '<label style="font-size: 11px; font-weight: bold; color: #475569; display: block; margin-bottom: 3px;">📄 تعداد نمایش:</label>';
                            echo '<select name="as_per_page" onchange="this.form.submit();" style="padding: 5px 10px; border-radius: 4px; border: 1px solid #cbd5e1; height: 34px; font-size: 12px; background: #fff; margin:0; width:100px;">';
                            echo '<option value="12"' . selected($posts_per_page, 12, false) . '>۱۲ مورد</option>';
                            echo '<option value="25"' . selected($posts_per_page, 25, false) . '>۲۵ مورد</option>';
                            echo '<option value="50"' . selected($posts_per_page, 50, false) . '>۵0 مورد</option>';
                            echo '<option value="-1"' . selected($posts_per_page, -1, false) . '>🚀 نمایش همه</option>';
                            echo '</select>';
                            echo '</div>';
                            echo '</div>';

                            echo '<div style="text-align: left;">';
                            echo '<a href="' . esc_url($export_url) . '" class="button success" style="background-color: #10b981; color: #fff; font-weight: bold; border-radius: 4px; padding: 7px 15px; margin: 0; font-size: 12px; height: 34px; line-height: 20px; display: inline-block; border: none;">خروجی XML نتایج</a>';
                            echo '</div>';
                            echo '</div>';

                            if (!function_exists('sahab_highlight_keyword')) {
                                function sahab_highlight_keyword($text, $keyword)
                                {
                                    if (empty($keyword))
                                        return $text;
                                    return preg_replace('/(' . preg_quote($keyword, '/') . ')/iu', '<strong style="background-color: #fef08a; color: #b91c1c; padding: 0 2px; border-radius: 2px;">$1</strong>', $text);
                                }
                            }

                            echo '<div class="sahab-table-wrapper" style="overflow-x: auto; direction: rtl; text-align: right;">';
                            echo '<table id="sahabResultTable" style="width: 100%; border-collapse: collapse; background: #fff; border: 1px solid #e2e8f0; font-size: 13px;">';
                            echo '<thead style="background: #f8fafc; border-bottom: 2px solid #cbd5e1;">';
                            echo '<tr>';
                            echo '<th style="padding: 12px; text-align: center; width: 40px;"><input type="checkbox" id="selectAllPosts" style="margin:0;"></th>';
                            echo '<th style="padding: 12px;">📝 عنوان گزارش</th>';
                            echo '<th style="padding: 12px; text-align: center; width: 120px;">✍️ ثبت‌کننده</th>';
                            echo '<th style="padding: 12px; text-align: center; width: 130px;">📁 کیس</th>';
                            echo '<th style="padding: 12px; text-align: center; width: 120px;">🏷️ برچسب‌ها</th>';
                            echo '<th style="padding: 12px; text-align: center; width: 110px;">📅 ثبت</th>';
                            echo '<th style="padding: 12px; text-align: center; width: 110px;">⏱️ وقوع</th>';
                            echo '<th style="padding: 12px; text-align: center; width: 70px;">عملیات</th>';
                            echo '</tr>';
                            echo '</thead>';
                            echo '<tbody>';

                            foreach ($display_posts as $post) {
                                setup_postdata($post);
                                $post_id = get_the_ID();
                                $event_date = get_post_meta($post_id, 'event_date', true);
                                $author_name = get_the_author();

                                $cats_list = wp_get_post_categories($post_id, array('fields' => 'names'));
                                $cats_str = !empty($cats_list) ? implode('، ', $cats_list) : '---';

                                $tags_list = wp_get_post_tags($post_id, array('fields' => 'names'));
                                $tags_str = !empty($tags_list) ? implode('، ', $tags_list) : '---';

                                $highlighted_title = sahab_highlight_keyword(get_the_title(), $search_keyword);
                                $highlighted_excerpt = sahab_highlight_keyword(wp_trim_words(get_the_excerpt(), 15, '...'), $search_keyword);

                                echo '<tr style="border-bottom: 1px solid #e2e8f0; transition: background 0.15s;" onmouseover="this.style.backgroundColor=\'#f8fafc\'" onmouseout="this.style.backgroundColor=\'#fff\'">';
                                echo '<td style="padding: 12px; text-align: center;"><input type="checkbox" class="post-bulletin-select" value="' . $post_id . '" style="margin:0;"></td>';
                                echo '<td style="padding: 12px;">';
                                echo '<a href="' . get_permalink() . '" style="font-weight: bold; color: #1e293b; text-decoration: none; display: block; margin-bottom: 4px;">' . $highlighted_title . '</a>';
                                echo '<div style="font-size: 11px; color: #64748b; line-height: 1.4;">' . $highlighted_excerpt . '</div>';
                                echo '</td>';
                                echo '<td style="padding: 12px; text-align: center; color: #334155;">' . esc_html($author_name) . '</td>';
                                echo '<td style="padding: 12px; text-align: center; color: #2563eb; font-size:12px;">' . esc_html($cats_str) . '</td>';
                                echo '<td style="padding: 12px; text-align: center; color: #475569; font-size:12px;">' . esc_html($tags_str) . '</td>';

                                $display_reg_shamsi = get_post_meta($post_id, 'sahab_reg_date_shamsi', true);
                                if (empty($display_reg_shamsi)) {
                                    $display_reg_shamsi = get_the_date('Y/m/d');
                                }

                                echo '<td style="padding: 12px; text-align: center; color: #334155;">' . esc_html($display_reg_shamsi) . '</td>';
                                echo '<td style="padding: 12px; text-align: center; color: #dc2626; font-weight: bold;">' . (!empty($event_date) ? esc_html($event_date) : '---') . '</td>';
                                echo '<td style="padding: 12px; text-align: center;"><a href="' . get_permalink() . '" target="_blank" style="font-size: 12px; color: #1d4ed8; font-weight: bold;">مشاهده ←</a></td>';
                                echo '</tr>';
                            }
                            wp_reset_postdata();

                            echo '</tbody>';
                            echo '</table>';
                            echo '</div>';

                            // پجینیشن
                            $paginated_links = paginate_links(array(
                                'base' => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
                                'format' => '?paged=%#%',
                                'current' => max(1, $paged),
                                'total' => $max_num_pages,
                                'type' => 'array',
                                'prev_text' => '« قبلی',
                                'next_text' => 'بعدی »',
                            ));

                            if (is_array($paginated_links)) {
                                echo '<div class="text-center" style="margin-top:30px;">';
                                echo '<ul class="page-numbers nav-pagination links" style="display:inline-flex; list-style:none; gap:5px; padding:0;">';
                                foreach ($paginated_links as $link) {
                                    echo '<li>' . $link . '</li>';
                                }
                                echo '</ul>';
                                echo '</div>';
                            }
                        } else {
                            echo '<div style="text-align:center; padding:20px; background:#fef3c7; border-radius:6px; color:#92400e; margin-top:20px; font-weight:bold;">⚠️ هیچ گزارشی متناسب با فیلترهای انتخابی شما یافت نشد.</div>';
                        }
                    }
                    ?>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- اسکریپت‌های مدیریت جستجوی آنی در فیلدها -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        function initQuickSearch(inputId, boxClass) {
            var searchInput = document.getElementById(inputId);
            if (!searchInput) return;
            searchInput.addEventListener('keyup', function () {
                var filter = this.value.toLowerCase().trim();
                var labels = document.querySelectorAll('.' + boxClass + ' label');
                labels.forEach(function (label) {
                    var text = label.textContent || label.innerText;
                    if (text.toLowerCase().indexOf(filter) > -1) {
                        label.style.display = "block";
                    } else {
                        label.style.display = "none";
                    }
                });
            });
        }
        // راه‌اندازی فیلترهای آنی متنی با آدرس‌دهی دقیق المان‌ها
        initQuickSearch('cat-search', 'sahab-cat-box');
        initQuickSearch('tag-search', 'sahab-tag-box');
        initQuickSearch('expert-search', 'sahab-expert-box');
        initQuickSearch('author-search', 'sahab-author-box');
    });
</script>

<?php
wp_enqueue_script('sahab-advanced-search-js', get_stylesheet_directory_uri() . '/assets/admin/js/sahab-advanced-search.js', array(), '1.1.0', true);
get_footer(); ?>