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
                                $selected_cats = isset($_GET['as_cats']) ? (array) $_GET['as_cats'] : array();
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
                                $selected_tags = isset($_GET['as_tags']) ? (array) $_GET['as_tags'] : array();
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
                if (isset($_GET['export_xml']) && $_GET['export_xml'] === '1') {
                    $args = array('post_type' => 'post', 'posts_per_page' => -1, 'post_status' => 'publish');
                    if (!empty($_GET['as_s'])) $args['s'] = sanitize_text_field($_GET['as_s']);
                    if (!empty($_GET['as_author'])) $args['author'] = intval($_GET['as_author']);
                    if (!empty($_GET['as_cats'])) $args['category__in'] = array_map('intval', $_GET['as_cats']);
                    if (!empty($_GET['as_tags'])) $args['tag__in'] = array_map('intval', $_GET['as_tags']);
                    if (!empty($_GET['start_event']) || !empty($_GET['end_event'])) {
                        $args['meta_query'] = array('relation' => 'AND');
                        if (!empty($_GET['start_event'])) {
                            $args['meta_query'][] = array('key' => 'event_date', 'value' => sanitize_text_field($_GET['start_event']), 'compare' => '>=', 'type' => 'CHAR');
                        }
                        if (!empty($_GET['end_event'])) {
                            $args['meta_query'][] = array('key' => 'event_date', 'value' => sanitize_text_field($_GET['end_event']), 'compare' => '<=', 'type' => 'CHAR');
                        }
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

                if (!empty($_GET['as_s']) || !empty($_GET['start_reg']) || !empty($_GET['start_event']) || !empty($_GET['as_cats']) || !empty($_GET['as_tags']) || !empty($_GET['as_author'])) {
                    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
                    $args = array(
                        'post_type'      => 'post',
                        'posts_per_page' => 12,
                        'paged'          => $paged,
                    );

                    $order = isset($_GET['as_order']) ? $_GET['as_order'] : 'date_desc';
                    if ($order == 'date_asc') {
                        $args['orderby'] = 'date';
                        $args['order'] = 'ASC';
                    } elseif ($order == 'relevance') {
                        $args['orderby'] = 'relevance';
                        $args['order'] = 'DESC';
                    } else {
                        $args['orderby'] = 'date';
                        $args['order'] = 'DESC';
                    }

                    if (!empty($_GET['as_s']))       $args['s'] = sanitize_text_field($_GET['as_s']);
                    if (!empty($_GET['as_author']))  $args['author'] = intval($_GET['as_author']);
                    if (!empty($_GET['as_cats']))    $args['category__in'] = array_map('intval', $_GET['as_cats']);
                    if (!empty($_GET['as_tags']))    $args['tag__in'] = array_map('intval', $_GET['as_tags']);

                    if (!empty($_GET['start_event']) || !empty($_GET['end_event'])) {
                        $args['meta_query'] = array('relation' => 'AND');
                        if (!empty($_GET['start_event'])) {
                            $args['meta_query'][] = array('key' => 'event_date', 'value' => sanitize_text_field($_GET['start_event']), 'compare' => '>=', 'type' => 'CHAR');
                        }
                        if (!empty($_GET['end_event'])) {
                            $args['meta_query'][] = array('key' => 'event_date', 'value' => sanitize_text_field($_GET['end_event']), 'compare' => '<=', 'type' => 'CHAR');
                        }
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
                        echo '<a href="' . esc_url($export_url) . '" class="button success" style="background-color:#28a745; color:#fff; font-weight:bold; border-radius:4px; padding:8px 16px; margin:0; white-space:nowrap;">📥 خروجی XML این نتایج</a>';
                        echo '</div>';

                        $search_keyword = !empty($_GET['as_s']) ? sanitize_text_field($_GET['as_s']) : '';
                        function sahab_highlight_keyword($text, $keyword) {
                            if (empty($keyword)) return $text;
                            return preg_replace('/(' . preg_quote($keyword, '/') . ')/iu', '<strong style="background-color: #fff3cd; color: #d9534f; padding: 0 2px; border-radius: 2px;">$1</strong>', $text);
                        }

                        echo '<div class="sahab-table-wrapper" style="overflow-x: auto; margin-top: 20px; direction: rtl; text-align: right;">';
                        echo '<table id="sahabResultTable" style="width: 100%; border-collapse: collapse; background: #fff; border: 1px solid #e5e5e5; font-size: 14px;">';
                        echo '<thead style="background: #f1f1f1; border-bottom: 2px solid #ccc;">';
                        echo '<tr>';
                        echo '<th style="padding: 12px; text-align: center; width: 40px;"><input type="checkbox" id="selectAllPosts" style="margin:0;"></th>';
                        echo '<th onclick="sortTable(1)" style="padding: 12px; cursor: pointer; user-select: none;">📝 عنوان گزارش ↕️</th>';
                        echo '<th onclick="sortTable(2)" style="padding: 12px; text-align: center; width: 120px; cursor: pointer; user-select: none;">👤 ثبت‌کننده ↕️</th>';
                        echo '<th onclick="sortTable(3)" style="padding: 12px; text-align: center; width: 130px; cursor: pointer; user-select: none;">📁 دسته‌بندی ↕️</th>';
                        echo '<th onclick="sortTable(4)" style="padding: 12px; text-align: center; width: 130px; cursor: pointer; user-select: none;">🏷️ برچسب‌ها ↕️</th>';
                        echo '<th onclick="sortTable(5)" style="padding: 12px; text-align: center; width: 110px; cursor: pointer; user-select: none;">📅 ثبت ↕️</th>';
                        echo '<th onclick="sortTable(6)" style="padding: 12px; text-align: center; width: 110px; cursor: pointer; user-select: none;">⏱️ وقوع ↕️</th>';
                        echo '<th style="padding: 12px; text-align: center; width: 70px;">عملیات</th>';
                        echo '</tr>';
                        echo '</thead>';
                        echo '<tbody>';

                        while ($query->have_posts()) {
                            $query->the_post();
                            $post_id = get_the_ID();
                            $event_date = get_post_meta($post_id, 'event_date', true);
                            $author_name = get_the_author();
                            $cats_list = wp_get_post_categories($post_id, array('fields' => 'names'));
                            $cats_str = !empty($cats_list) ? implode('، ', $cats_list) : '---';
                            $tags_list = wp_get_post_tags($post_id, array('fields' => 'names'));
                            $tags_str = !empty($tags_list) ? implode('، ', $tags_list) : '---';
                            $raw_title = get_the_title();
                            $raw_excerpt = wp_trim_words(get_the_excerpt(), 15, '...');
                            $highlighted_title = sahab_highlight_keyword($raw_title, $search_keyword);
                            $highlighted_excerpt = sahab_highlight_keyword($raw_excerpt, $search_keyword);

                            echo '<tr style="border-bottom: 1px solid #e5e5e5; transition: background 0.2s;" onmouseover="this.style.backgroundColor=\'#fdfdfd\'" onmouseout="this.style.backgroundColor=\'#fff\'">';
                            echo '<td style="padding: 12px; text-align: center;"><input type="checkbox" class="post-bulletin-select" value="' . $post_id . '" style="margin:0;"></td>';
                            echo '<td style="padding: 12px;">';
                            echo '<a href="' . get_permalink() . '" style="font-weight: bold; color: #333; text-decoration: none; display: block; margin-bottom: 4px;">' . $highlighted_title . '</a>';
                            echo '<div style="font-size: 12px; color: #666; line-height: 1.5;">' . $highlighted_excerpt . '</div>';
                            echo '</td>';
                            echo '<td style="padding: 12px; text-align: center; color: #444;">' . esc_html($author_name) . '</td>';
                            echo '<td style="padding: 12px; text-align: center; color: #0066cc; font-size:12px;">' . esc_html($cats_str) . '</td>';
                            echo '<td style="padding: 12px; text-align: center; color: #666; font-size:12px;">' . esc_html($tags_str) . '</td>';
                            echo '<td style="padding: 12px; text-align: center; color: #555;">' . get_the_date('Y/m/d') . '</td>';
                            echo '<td style="padding: 12px; text-align: center; color: #d9534f; font-weight: bold;">' . (!empty($event_date) ? esc_html($event_date) : '---') . '</td>';
                            echo '<td style="padding: 12px; text-align: center;"><a href="' . get_permalink() . '" target="_blank" style="font-size: 12px; color: #0056b3; font-weight: bold; text-decoration: underline;">مشاهده ←</a></td>';
                            echo '</tr>';
                        }

                        echo '</tbody>';
                        echo '</table>';
                        echo '</div>';

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

    var catSearch = document.getElementById('cat-search');
    if (catSearch) {
        catSearch.addEventListener('input', function(e) {
            var filter = e.target.value.trim().toLowerCase();
            var labels = document.querySelectorAll('.sahab-cat-box label');
            labels.forEach(function(label) {
                var text = label.textContent || label.innerText;
                label.style.display = text.toLowerCase().indexOf(filter) > -1 ? 'block' : 'none';
            });
        });
    }

    var tagSearch = document.getElementById('tag-search');
    if (tagSearch) {
        tagSearch.addEventListener('input', function(e) {
            var filter = e.target.value.trim().toLowerCase();
            var labels = document.querySelectorAll('.sahab-tag-box label');
            labels.forEach(function(label) {
                var text = label.textContent || label.innerText;
                label.style.display = text.toLowerCase().indexOf(filter) > -1 ? 'block' : 'none';
            });
        });
    }

    var selectAllPosts = document.getElementById('selectAllPosts');
    if (selectAllPosts) {
        selectAllPosts.addEventListener('change', function(e) {
            var checked = e.target.checked;
            var checkboxes = document.querySelectorAll('.post-bulletin-select');
            checkboxes.forEach(function(checkbox) {
                checkbox.checked = checked;
            });
        });
    }
});

function sortTable(columnIndex) {
    var table = document.getElementById('sahabResultTable');
    if (!table) return;
    var tbody = table.tBodies[0];
    var rowsArray = Array.prototype.slice.call(tbody.querySelectorAll('tr'));
    var currentOrder = table.getAttribute('data-sort-order') || 'asc';
    var newOrder = currentOrder === 'asc' ? 'desc' : 'asc';

    rowsArray.sort(function(a, b) {
        var aText = a.children[columnIndex].textContent.trim();
        var bText = b.children[columnIndex].textContent.trim();
        var aNumber = parseFloat(aText.replace(/[^0-9\-\.]/g, ''));
        var bNumber = parseFloat(bText.replace(/[^0-9\-\.]/g, ''));

        if (!isNaN(aNumber) && !isNaN(bNumber) && aText.match(/[0-9]/) && bText.match(/[0-9]/)) {
            return newOrder === 'asc' ? aNumber - bNumber : bNumber - aNumber;
        }

        var aStr = aText.toLowerCase();
        var bStr = bText.toLowerCase();
        if (aStr < bStr) return newOrder === 'asc' ? -1 : 1;
        if (aStr > bStr) return newOrder === 'asc' ? 1 : -1;
        return 0;
    });

    rowsArray.forEach(function(row) {
        tbody.appendChild(row);
    });
    table.setAttribute('data-sort-order', newOrder);
}
</script>

<?php get_footer(); ?>
