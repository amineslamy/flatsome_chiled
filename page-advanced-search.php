<?php
/**
 * Template Name: Advanced Search Page
 */

get_header(); ?>

<div id="content" class="content-area page-wrapper" style="padding: 40px 0;">
    <div class="row">
        <?php
        /**
         * Template Name: Advanced Search Page
         */

        get_header(); ?>

        <div id="content" class="content-area page-wrapper" style="padding: 40px 0;">
            <div class="row">
                <div class="large-12 col">
                    <header class="entry-header text-center" style="margin-bottom: 30px;">
                        <h1 class="entry-title" style="color: #444; font-weight: bold;">⚙️ موتور جامع جستجوی پیشرفته سحاب</h1>
                        <p style="color: #777;">فیلترهای پیشرفته برای استخراج دقیق اطلاعات و گزارش‌ها</p>
                    </header>
            
                    <div class="entry-content">
                        <form method="get" action="" class="sahab-adv-search-form" style="background: #f9f9f9; padding: 25px; border-radius: 8px; border: 1px solid #eee; margin-bottom: 40px; direction: rtl; text-align: right;">
                    
                            <div class="row">
                                <div class="large-8 col" style="margin-bottom: 15px;">
                                    <label style="font-weight: bold; display: block; margin-bottom: 5px;">🔎 کلمه کلیدی جستجو (شامل متن، عنوان و کامنت‌ها):</label>
                                    <input type="text" name="as_s" value="<?php echo isset($_GET['as_s']) ? esc_attr($_GET['as_s']) : ''; ?>" placeholder="عبارت مورد نظر را وارد کنید..." style="width: 100%; padding: 10px; border-radius: 4px; border: 1px solid #ccc;">
                                </div>
                                <div class="large-4 col" style="margin-bottom: 15px;">
                                    <label style="font-weight: bold; display: block; margin-bottom: 5px;">🔀 مرتب‌سازی بر اساس:</label>
                                    <select name="as_order" style="width: 100%; padding: 10px; border-radius: 4px; border: 1px solid #ccc; height:43px;">
                                        <option value="date_desc" <?php selected(isset($_GET['as_order']) && $_GET['as_order'] == 'date_desc'); ?>>جدیدترین تاریخ انتشار</option>
                                        <option value="date_asc" <?php selected(isset($_GET['as_order']) && $_GET['as_order'] == 'date_asc'); ?>>قدیمی‌ترین تاریخ انتشار</option>
                                        <option value="relevance" <?php selected(isset($_GET['as_order']) && $_GET['as_order'] == 'relevance'); ?>>میزان ارتباط متنی</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="large-6 col" style="margin-bottom: 15px;">
                                    <label style="font-weight: bold; display: block; margin-bottom: 5px;">📅 بازه تاریخ ثبت (انتشار در سایت):</label>
                                    <div style="display: flex; gap: 10px;">
                                        <input type="text" name="start_reg" class="sahab-pdate" data-jdp value="<?php echo isset($_GET['start_reg']) ? esc_attr($_GET['start_reg']) : ''; ?>" placeholder="از تاریخ" style="width: 50%; padding: 8px; border-radius: 4px; border: 1px solid #ccc;" autocomplete="off">
                                        <input type="text" name="end_reg" class="sahab-pdate" data-jdp value="<?php echo isset($_GET['end_reg']) ? esc_attr($_GET['end_reg']) : ''; ?>" placeholder="تا تاریخ" style="width: 50%; padding: 8px; border-radius: 4px; border: 1px solid #ccc;" autocomplete="off">
                                    </div>
                                </div>

                                <div class="large-6 col" style="margin-bottom: 15px;">
                                    <label style="font-weight: bold; display: block; margin-bottom: 5px;">⏱️ بازه تاریخ وقوع رویداد:</label>
                                    <div style="display: flex; gap: 10px;">
                                        <input type="text" name="start_event" class="sahab-pdate" data-jdp value="<?php echo isset($_GET['start_event']) ? esc_attr($_GET['start_event']) : ''; ?>" placeholder="از تاریخ" style="width: 50%; padding: 8px; border-radius: 4px; border: 1px solid #ccc;" autocomplete="off">
                                        <input type="text" name="end_event" class="sahab-pdate" data-jdp value="<?php echo isset($_GET['end_event']) ? esc_attr($_GET['end_event']) : ''; ?>" placeholder="تا تاریخ" style="width: 50%; padding: 8px; border-radius: 4px; border: 1px solid #ccc;" autocomplete="off">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="large-4 col" style="margin-bottom: 15px;">
                                    <label style="font-weight: bold; display: block; margin-bottom: 5px;">📁 انتخاب دسته‌بندی:</label>
                                    <input type="text" id="cat-search" placeholder="🔍 سرچ سریع دسته‌ها..." style="width: 100%; padding: 5px 10px; font-size: 12px; margin-bottom: 5px; border-radius: 4px; border: 1px solid #ccc; height: 30px;">
                                    <div class="sahab-cat-box" style="max-height: 120px; overflow-y: auto; background: #fff; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                                        <?php
                                        $categories = get_categories(array('hide_empty' => 0));
                                        $selected_cats = isset($_GET['as_cats']) ? (array)$_GET['as_cats'] : array();
                                        foreach ($categories as $cat) {
                                            echo '<label style="display:block; font-weight:normal; margin-bottom:3px;"><input type="checkbox" name="as_cats[]" value="' . $cat->term_id . '" ' . checked(in_array($cat->term_id, $selected_cats), true, false) . '> ' . esc_html($cat->name) . '</label>';
                                        }
                                        ?>
                                    </div>
                                </div>

                                <div class="large-4 col" style="margin-bottom: 15px;">
                                    <label style="font-weight: bold; display: block; margin-bottom: 5px;">🏷️ انتخاب برچسب (Tag):</label>
                                    <input type="text" id="tag-search" placeholder="🔍 سرچ سریع برچسب‌ها..." style="width: 100%; padding: 5px 10px; font-size: 12px; margin-bottom: 5px; border-radius: 4px; border: 1px solid #ccc; height: 30px;">
                                    <div class="sahab-tag-box" style="max-height: 120px; overflow-y: auto; background: #fff; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                                        <?php
                                        $tags = get_tags(array('hide_empty' => 0));
                                        $selected_tags = isset($_GET['as_tags']) ? (array)$_GET['as_tags'] : array();
                                        foreach ($tags as $tag) {
                                            echo '<label style="display:block; font-weight:normal; margin-bottom:3px;"><input type="checkbox" name="as_tags[]" value="' . $tag->term_id . '" ' . checked(in_array($tag->term_id, $selected_tags), true, false) . '> ' . esc_html($tag->name) . '</label>';
                                        }
                                        ?>
                                    </div>
                                </div>

                                <div class="large-4 col" style="margin-bottom: 15px;">
                                    <label style="font-weight: bold; display: block; margin-bottom: 5px;">👤 ثبت کننده گزارش (نویسنده):</label>
                                    <select name="as_author" style="width: 100%; padding: 10px; border-radius: 4px; border: 1px solid #ccc; height:43px; background:#fff;">
                                        <option value="">همه کاربران</option>
                                        <?php
                                        $users = get_users(array('fields' => array('ID', 'display_name')));
                                        foreach ($users as $user) {
                                            echo '<option value="' . $user->ID . '" ' . selected(isset($_GET['as_author']) && $_GET['as_author'] == $user->ID, true, false) . '>' . esc_html($user->display_name) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div style="text-align: left; margin-top: 15px;">
                                <button type="submit" class="button primary" style="font-weight: bold; padding: 10px 30px; border-radius: 4px; background-color: #d9534f;">🎯 شروع فیلتر و جستجو</button>
                                <a href="<?php echo home_url('/advanced-search/'); ?>" class="button secondary text-dark" style="padding: 10px 20px; border-radius: 4px; margin-right: 10px;">🔄 پاکسازی فرم</a>
                            </div>
                        </form>

                        <?php
// ۱. بخش پردازش و دانلود خروجی استاندارد XML وردپرس
if (isset($_GET['export_xml']) && $_GET['export_xml'] === '1') {
    $args = array('post_type' => 'post', 'posts_per_page' => -1, 'post_status' => 'publish');
    if (!empty($_GET['as_s'])) $args['s'] = sanitize_text_field($_GET['as_s']);
    if (!empty($_GET['as_author'])) $args['author'] = intval($_GET['as_author']);
    if (!empty($_GET['as_cats'])) $args['category__in'] = array_map('intval', $_GET['as_cats']);
    if (!empty($_GET['as_tags'])) $args['tag__in'] = array_map('intval', $_GET['as_tags']);
    if (!empty($_GET['start_event']) || !empty($_GET['end_event'])) {
        $args['meta_query'] = array('relation' => 'AND');
        if (!empty($_GET['start_event'])) { $args['meta_query'][] = array('key' => 'event_date', 'value' => sanitize_text_field($_GET['start_event']), 'compare' => '>=', 'type' => 'CHAR'); }
        if (!empty($_GET['end_event'])) { $args['meta_query'][] = array('key' => 'event_date', 'value' => sanitize_text_field($_GET['end_event']), 'compare' => '<=', 'type' => 'CHAR'); }
    }
    
    $export_query = new WP_Query($args);
    if (function_exists('relevanssi_do_query') && !empty($args['s'])) {
        relevanssi_do_query($export_query);
    }
    $post_ids = wp_list_pluck($export_query->posts, 'ID');
    
    if (!empty($post_ids)) {
        require_once ABSPATH . 'wp-admin/includes/export.php';
        if (ob_get_length()) { ob_end_clean(); }
        export_wp(array('content' => 'post', 'post__in' => $post_ids));
        exit;
    }
}

// ۲. بخش اجرای کوئری نمایش نتایج روی صفحه
if (!empty($_GET['as_s']) || !empty($_GET['start_reg']) || !empty($_GET['start_event']) || !empty($_GET['as_cats']) || !empty($_GET['as_tags']) || !empty($_GET['as_author'])) {
    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
    $args = array(
        'post_type'      => 'post',
        'posts_per_page' => 12,
        'paged'          => $paged,
    );

    // تعیین ترتیب نمایش
    $order = isset($_GET['as_order']) ? $_GET['as_order'] : 'date_desc';
    if ($order == 'date_asc') { $args['orderby'] = 'date'; $args['order'] = 'ASC'; } 
    else { $args['orderby'] = 'date'; $args['order'] = 'DESC'; }

    if (!empty($_GET['as_s']))       $args['s'] = sanitize_text_field($_GET['as_s']);
    if (!empty($_GET['as_author']))  $args['author'] = intval($_GET['as_author']);
    if (!empty($_GET['as_cats']))    $args['category__in'] = array_map('intval', $_GET['as_cats']);
    if (!empty($_GET['as_tags']))    $args['tag__in'] = array_map('intval', $_GET['as_tags']);

    if (!empty($_GET['start_event']) || !empty($_GET['end_event'])) {
        $args['meta_query'] = array('relation' => 'AND');
        if (!empty($_GET['start_event'])) { $args['meta_query'][] = array('key' => 'event_date', 'value' => sanitize_text_field($_GET['start_event']), 'compare' => '>=', 'type' => 'CHAR'); }
        if (!empty($_GET['end_event'])) { $args['meta_query'][] = array('key' => 'event_date', 'value' => sanitize_text_field($_GET['end_event']), 'compare' => '<=', 'type' => 'CHAR'); }
    }

    $query = new WP_Query($args);
    
    if (function_exists('relevanssi_do_query') && !empty($args['s'])) {
        relevanssi_do_query($query);
    }

    if ($query->have_posts()) {
        $current_url = $_SERVER['REQUEST_URI'];
        $export_url = add_query_arg('export_xml', '1', $current_url);
        
        echo '<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:2px solid #eee; padding-bottom:10px;">';
        echo '<h3 style="margin:0; color:#222;">📚 نتایج فیلتر پیشرفته (' . $query->found_posts . ' گزارش):</h3>';
        echo '<a href="' . esc_url($export_url) . '" class="button success" style="background-color:#28a745; color:#fff; font-weight:bold; border-radius:4px; padding:5px 15px; margin:0;">📥 خروجی XML این نتایج</a>';
        echo '</div>';

        // کارت‌های بومی و تمیز بدون تداخل با فرانت‌آند
        echo '<div class="row large-columns-3 medium-columns-2 small-columns-1 col-spacing-small">';
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();
            $event_date = get_post_meta($post_id, 'event_date', true);
            ?>
            <div class="col">
                <div class="col-inner" style="background:#fff; border:1px solid #e5e5e5; border-radius:6px; padding:15px; margin-bottom:20px; box-shadow:0 2px 4px rgba(0,0,0,0.02); display:flex; flex-direction:column; justify-content:space-between; min-height:180px;">
                    <div>
                        <h4 class="post-title" style="margin:0 0 10px 0; font-weight:bold; font-size:16px;">
                            <a href="<?php the_permalink(); ?>" style="color:#333; text-decoration:none;"><?php the_title(); ?></a>
                        </h4>
                        <div class="post-meta-data" style="font-size:12px; color:#888; margin-bottom:10px;">
                            <span>📅 ثبت: <?php echo get_the_date('Y/m/d'); ?></span>
                            <?php if(!empty($event_date)): ?>
                                <span style="color:#d9534f; margin-right:10px; font-weight:bold;">⏱️ وقوع: <?php echo esc_html($event_date); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="post-excerpt" style="font-size:13px; color:#666; line-height:1.6; max-height:60px; overflow:hidden;">
                            <?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?>
                        </div>
                    </div>
                    <div style="text-align:left; margin-top:15px;">
                        <a href="<?php the_permalink(); ?>" style="font-size:12px; font-weight:bold; color:#0056b3; text-decoration:underline;">مشاهده گزارش ←</a>
                    </div>
                </div>
            </div>
            <?php
        }
        echo '</div>';
        
        // پیاده‌سازی دقیق ساختار صفحه‌بندی فلتسام استخراج‌شده از DOM
        $paginated_links = paginate_links(array(
            'base'      => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
            'format'    => '?paged=%#%',
            'current'   => max(1, $paged),
            'total'     => $query->max_num_pages,
            'type'      => 'array',
            'prev_text' => '« قبلی',
            'next_text' => 'بعدی »',
        ));

        if (is_array($paginated_links)) {
            echo '<div class="ux-relay__control ux-relay__control--bottom container pb-half text-center" style="margin-top:30px;">';
            echo '<ul class="ux-relay__pagination page-numbers nav-pagination links">';
            foreach ($paginated_links as $link) {
                // هماهنگ‌سازی کلاس اکتیو وردپرس با ساختار فلتسام
                $link = str_replace("page-numbers current", "page-number current", $link);
                $link = str_replace("page-numbers", "page-number", $link);
                echo '<li>' . $link . '</li>';
            }
            echo '</ul>';
            echo '</div>';
        }
        
    } else {
        echo '<div class="notice-box style-warning" style="text-align:center; padding:20px; background:#fff3cd; border-radius:4px; color:#856404;">⚠️ هیچ گزارشی متناسب با فیلترهای انتخابی شما یافت نشد.</div>';
    }
    wp_reset_postdata();
}
?>
                    </div>
                </div>
            </div>
        </div>

        <script>
document.addEventListener("DOMContentLoaded", function() {
    if (typeof jalaliDatepicker !== 'undefined' && typeof jalaliDatepicker.startWatch === 'function') {
        jalaliDatepicker.startWatch({
            selector: ".sahab-pdate",
            dateFormat: "yyyy/mm/dd",
            autoShow: true,
            autoHide: true
        });
    }

    // فیلتر زنده دسته‌بندی‌ها
    var catSearch = document.getElementById('cat-search');
    if (catSearch) {
        catSearch.addEventListener('input', function(e) {
            var filter = e.target.value.trim().toLowerCase();
            var labels = document.querySelectorAll('.sahab-cat-box label');
            labels.forEach(function(label) {
                var text = label.textContent || label.innerText;
                if (text.toLowerCase().indexOf(filter) > -1) {
                    label.style.display = 'block';
                } else {
                    label.style.display = 'none';
                }
            });
        });
    }

    // فیلتر زنده برچسب‌ها
    var tagSearch = document.getElementById('tag-search');
    if (tagSearch) {
        tagSearch.addEventListener('input', function(e) {
            var filter = e.target.value.trim().toLowerCase();
            var labels = document.querySelectorAll('.sahab-tag-box label');
            labels.forEach(function(label) {
                var text = label.textContent || label.innerText;
                if (text.toLowerCase().indexOf(filter) > -1) {
                    label.style.display = 'block';
                } else {
                    label.style.display = 'none';
                }
            });
        });
    }
});
</script>

        <?php get_footer(); ?>
