<?php
/**
 * Template Name: داشبورد گزارشات سحاب
 * مسیر فایل: ریشه قالب / page-report.php
 * آدرس دسترسی: http://sahab.test/report/
 */

if (!defined('ABSPATH')) {
    exit;
}

// ۱. فراخوانی هدر بومی و اصلی پلتفرم سحاب
get_header();

// دریافت تاریخ‌ها یا تولید داینامیک بازه یک ماهه اخیر به کمک موتور پارسی‌دیت سحاب
if (function_exists('parsidate')) {
    $default_end = parsidate('Y/m/d', current_time('mysql'), 'eng');
    $thirty_days_ago_mysql = date('Y-m-d H:i:s', current_time('timestamp') - (30 * DAY_IN_SECONDS));
    $default_start = parsidate('Y/m/d', $thirty_days_ago_mysql, 'eng');
} else {
    $default_end = '1405/04/31';
    $default_start = '1405/03/01';
}

$start_date = isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : $default_start;
$end_date = isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : $default_end;
$filter_dept = isset($_GET['filter_dept']) ? sanitize_text_field($_GET['filter_dept']) : '';
$filter_analyst = isset($_GET['filter_analyst']) ? sanitize_text_field($_GET['filter_analyst']) : '';
$filter_case = isset($_GET['filter_case']) ? sanitize_text_field($_GET['filter_case']) : '';
$filter_subject = isset($_GET['filter_subject']) ? sanitize_text_field($_GET['filter_subject']) : '';

$report_data = sahab_get_advanced_bi_report($start_date, $end_date, $filter_dept, $filter_analyst, $filter_case, $filter_subject);
$departments = $report_data['departments'];
$analysts = $report_data['analysts'];
$total_news = $report_data['total_processed'];
$global_stats = $report_data['global_stats'];
$choices = $report_data['choices'];

$total_staff = count($analysts);
$total_topics = count(array_filter($global_stats['subjects']));
if ($total_topics === 0) {
    $total_topics = count($choices['subject']);
}
$total_cases = isset($report_data['total_active_cases']) ? $report_data['total_active_cases'] : count($choices['case']);

// محاسبه جمع کل اخبار مانیتور شده ادارات برای بج اختصاصی بخش ادارات
$total_dept_news = 0;
if (!empty($departments)) {
    foreach ($departments as $dept) {
        $total_dept_news += intval($dept['total_news']);
    }
}
?>

<!-- استایل‌های عایق‌بندی شده اداری کاملاً هم‌تراز با لایوت بومی سحاب -->
<style>
    .sahab-bi-container {
        background-color: #f1f5f9;
        color: #1e293b;
        padding: 24px;
        font-size: 13px;
        line-height: 1.5;
        text-align: right;
    }

    body.dark .sahab-bi-container {
        background-color: #0f172a;
        color: #f8fafc;
    }

    /* باکس‌های تخت مینی‌مال اداری */
    .sahab-report-box {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        width: 100%;
        display: block;
        clear: both;
    }

    body.dark .sahab-report-box {
        background-color: #1e293b;
        border-color: #334155;
    }

    .sahab-report-box.box-green {
        border: 1px solid var(--sahab-green);
    }

    /* ردیف فیلترها */
    .sahab-filter-row {
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        width: 100%;
        flex-wrap: nowrap;
        padding: 0 !important;
    }

    .sahab-filter-inputs {
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 12px;
        flex-wrap: nowrap;
    }

    .sahab-report-box.no-print {
        padding: 10px 20px !important;
        min-height: auto !important;
        margin-bottom: 15px !important;
    }

    .sahab-btn-reset {
        background-color: #e11d48 !important;
        color: #ffffff !important;
        border: none !important;
        border-radius: 6px !important;
        padding: 6px 14px !important;
        font-size: 13px !important;
        text-decoration: none !important;
        font-weight: bold !important;
        cursor: pointer;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        height: 34px !important;
        margin: 0 !important;
        line-height: 1 !important;
    }

    .sahab-input-date {
        background-color: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 6px !important;
        padding: 6px 8px !important;
        font-size: 12px !important;
        color: #1e293b !important;
        width: 85px !important;
        height: 34px !important;
        display: inline-block !important;
        text-align: center;
    }

    .sahab-select {
        background-color: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 6px !important;
        padding: 6px 8px !important;
        font-size: 12px !important;
        color: #1e293b !important;
        height: 34px !important;
        display: inline-block !important;
    }

    select[name="filter_dept"] {
        width: 105px !important;
    }

    select[name="filter_analyst"] {
        width: 95px !important;
    }

    select[name="filter_case"] {
        width: 95px !important;
    }

    select[name="filter_subject"] {
        width: 105px !important;
    }

    .sahab-btn-print {
        height: 34px !important;
        display: inline-flex !important;
        align-items: center !important;
        margin: 0 !important;
    }

    /* کارت‌ها به صورت ردیف‌های عریض تک کارت در یک ردیف */
    .sahab-stat-cards {
        display: grid;
        grid-template-columns: 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }

    .sahab-stat-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 24px;
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
        gap: 40px;
        box-shadow: 0 1px 4px rgba(0, 0, 0, .03);
    }

    body.dark .sahab-stat-card {
        background: #1e293b;
        border-color: #334155;
    }

    .sahab-stat-card__info {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .sahab-stat-card__label {
        font-size: 14px;
        color: #475569;
        font-weight: 700;
        margin-bottom: 8px;
    }

    /* بج‌های عددی بزرگ و رنگی */
    .sahab-stat-card__badge-container {
        display: inline-flex;
        align-items: center;
        margin-bottom: 15px;
    }

    .sahab-stat-card__badge {
        font-size: 32px;
        font-weight: 900;
        font-family: monospace, Tahoma;
        line-height: 1;
        padding: 6px 18px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .badge-blue {
        background-color: #eff6ff;
        color: #1d4ed8;
    }

    .badge-emerald {
        background-color: #ecfdf5;
        color: #047857;
    }

    .badge-amber {
        background-color: #fffbeb;
        color: #b45309;
    }

    .badge-rose {
        background-color: #fff1f2;
        color: #be123c;
    }

    .badge-indigo {
        background-color: #e0e7ff;
        color: #4338ca;
    }

    body.dark .badge-blue {
        background-color: rgba(29, 78, 216, 0.2);
        color: #60a5fa;
    }

    body.dark .badge-emerald {
        background-color: rgba(4, 120, 87, 0.2);
        color: #34d399;
    }

    body.dark .badge-amber {
        background-color: rgba(180, 83, 9, 0.2);
        color: #fbbf24;
    }

    body.dark .badge-rose {
        background-color: rgba(190, 18, 60, 0.2);
        color: #f43f5e;
    }

    body.dark .badge-indigo {
        background-color: rgba(67, 56, 202, 0.2);
        color: #818cf8;
    }

    .sahab-stat-card__list {
        list-style: none;
        margin: 0;
        padding: 0;
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 8px 24px;
    }

    .sahab-stat-card__row {
        font-size: 12px;
        color: #475569;
        display: flex;
        justify-content: space-between;
        border-bottom: 1px dashed #e2e8f0;
        padding-bottom: 4px;
    }

    body.dark .sahab-stat-card__row {
        color: #cbd5e1;
        border-bottom-color: #334155;
    }

    .sahab-stat-card__row strong {
        color: #0f172a;
        font-weight: 700;
    }

    body.dark .sahab-stat-card__row strong {
        color: #f8fafc;
    }

    .sahab-stat-card__chart {
        width: 45%;
        min-width: 340px;
        flex-shrink: 0;
    }

    .sahab-grid-charts {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 20px;
    }

    .sahab-chart-main {
        flex: 3;
        min-width: 600px;
    }

    /* جدول کارنامه کارشناسان */
    .sahab-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    .sahab-table th {
        font-size: 12px;
        color: #64748b;
        font-weight: 700;
        text-align: right;
        padding: 12px;
        border-bottom: 2px solid #e2e8f0;
    }

    body.dark .sahab-table th {
        border-bottom-color: #334155;
        color: #94a3b8;
    }

    .sahab-table td {
        font-size: 13px;
        padding: 12px;
        border-bottom: 1px solid #e2e8f0;
        vertical-align: middle;
        color: inherit;
    }

    body.dark .sahab-table td {
        border-bottom-color: #334155;
    }

    .sahab-table tr:hover {
        background-color: #f8fafc;
    }

    body.dark .sahab-table tr:hover {
        background-color: #1e293b;
    }

    .sahab-tabs-nav {
        display: flex;
        gap: 4px;
        border-bottom: 2px solid #e2e8f0;
        margin-bottom: 20px;
        padding-bottom: 1px;
    }

    body.dark .sahab-tabs-nav {
        border-bottom-color: #334155;
    }

    .sahab-tab-btn {
        background: none;
        border: none;
        padding: 12px 24px;
        font-size: 14px;
        font-weight: 700;
        color: #64748b;
        cursor: pointer;
        border-bottom: 3px solid transparent;
        margin-bottom: -2px;
    }

    .sahab-tab-btn.active {
        color: #0284c7;
        border-bottom-color: #0284c7;
    }

    .sahab-tab-content {
        display: none;
    }

    .sahab-tab-content.active {
        display: block;
    }

    .flat-badge {
        font-size: 11px;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 4px;
        background-color: #f1f5f9;
        border: 1px solid #e2e8f0;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #334155;
    }

    body.dark .flat-badge {
        background-color: #0f172a;
        border-color: #334155;
        color: #cbd5e1;
    }
</style>

<script src="<?php echo get_stylesheet_directory_uri(); ?>/assets/admin/js/apexcharts.min.js"></script>
<script src="<?php echo get_stylesheet_directory_uri(); ?>/assets/admin/js/jalali-datepicker.min.js"></script>
<link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/assets/admin/css/jalali-datepicker.min.css">

<div class="sahab-bi-container">
    <div class="max-w-[1400px] mx-auto">

        <!-- ===================== CONTROLS FILTER ===================== -->
        <div class="sahab-report-box no-print">
            <form method="GET" id="sahab-bi-filter-form" action="" class="sahab-filter-row">
                <div class="sahab-filter-inputs">
                    <span class="text-xs font-semibold">از تاریخ:</span>
                    <input type="text" data-jdp name="start_date" class="sahab-input-date"
                        value="<?php echo esc_attr($start_date); ?>" autocomplete="off"
                        onchange="document.getElementById('sahab-bi-filter-form').submit();">

                    <span class="text-xs font-semibold">تا تاریخ:</span>
                    <input type="text" data-jdp name="end_date" class="sahab-input-date"
                        value="<?php echo esc_attr($end_date); ?>" autocomplete="off"
                        onchange="document.getElementById('sahab-bi-filter-form').submit();">

                    <select name="filter_dept" class="sahab-select"
                        onchange="document.getElementById('sahab-bi-filter-form').submit();">
                        <option value="">همه ادارات</option>
                        <?php foreach ($departments as $m_id => $dept): ?>
                            <option value="<?php echo esc_attr($m_id); ?>" <?php selected($filter_dept, $m_id); ?>>
                                <?php echo esc_html($dept['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <select name="filter_analyst" class="sahab-select"
                        onchange="document.getElementById('sahab-bi-filter-form').submit();">
                        <option value="">کارشناس</option>
                        <?php
                        $all_base_users = get_users(array('fields' => array('ID', 'display_name')));
                        foreach ($all_base_users as $u_item):
                            if ($u_item->ID === 1 || strtolower($u_item->display_name) === 'administrator')
                                continue;
                            if (!empty($filter_dept)) {
                                $u_manager = get_field('reports_to', 'user_' . $u_item->ID);
                                if (intval($u_manager) !== intval($filter_dept))
                                    continue;
                            }
                            ?>
                            <option value="<?php echo esc_attr($u_item->ID); ?>" <?php selected($filter_analyst, $u_item->ID); ?>>
                                <?php echo esc_html($u_item->display_name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <select name="filter_case" class="sahab-select"
                        onchange="document.getElementById('sahab-bi-filter-form').submit();">
                        <option value="">همه کیس‌ها</option>
                        <?php if (!empty($choices['case'])):
                            foreach ($choices['case'] as $c_key => $c_label): ?>
                                <option value="<?php echo esc_attr($c_key); ?>" <?php selected($filter_case, $c_key); ?>>
                                    <?php echo esc_html($c_label); ?>
                                </option>
                            <?php endforeach; endif; ?>
                    </select>

                    <select name="filter_subject" class="sahab-select"
                        onchange="document.getElementById('sahab-bi-filter-form').submit();">
                        <option value="">همه موضوعات</option>
                        <?php if (!empty($choices['subject'])):
                            foreach ($choices['subject'] as $s_key => $s_label): ?>
                                <option value="<?php echo esc_attr($s_key); ?>" <?php selected($filter_subject, $s_key); ?>>
                                    <?php echo esc_html($s_label); ?>
                                </option>
                            <?php endforeach; endif; ?>
                    </select>

                    <a href="<?php echo strtok($_SERVER["REQUEST_URI"], '?'); ?>" class="sahab-btn-reset">حذف
                        فیلترها</a>
                </div>
                <div>
                    <button type="button" onclick="window.print()" class="sahab-btn-print">چاپ گزارش</button>
                </div>
            </form>
        </div>

        <!-- ===================== TAB NAVIGATION ===================== -->
        <div class="sahab-tabs-nav no-print">
            <button class="sahab-tab-btn active" onclick="switchTab(event, 'bi-system')">کارنامه سامانه و
                ادارات</button>
            <button class="sahab-tab-btn" onclick="switchTab(event, 'bi-analysts')">کارنامه کارشناسان</button>
        </div>

        <!-- ===================== TAB 1: SYSTEM ===================== -->
        <div id="bi-system" class="sahab-tab-content active">

            <!-- تصویر شماره ۳: ارتقای روند زمانی به بالاترین قسمت کل تب سامانه -->
            <div class="sahab-grid-charts">
                <div class="sahab-report-box sahab-chart-main box-green">
                    <h4 class="text-sm font-bold text-slate-600 mb-4">روند زمانی انتشار اخبار</h4>
                    <div id="trendChart"></div>
                </div>
            </div>

            <div class="sahab-stat-cards">

                <!-- کارت ۱: اخبار -->
                <div class="sahab-stat-card">
                    <div class="sahab-stat-card__info">
                        <div class="sahab-stat-card__label">کل اخبار فیلتر شده</div>
                        <div class="sahab-stat-card__badge-container">
                            <span class="sahab-stat-card__badge badge-blue">
                                <?php echo number_format_i18n($total_news); ?>
                            </span>
                        </div>
                        <ul class="sahab-stat-card__list">
                            <?php foreach ($choices['news_type'] as $k => $v):
                                if (empty($global_stats['news_types'][$k]))
                                    $global_stats['news_types'][$k] = 0; ?>
                                <li class="sahab-stat-card__row"><span>
                                        <?php echo esc_html($v); ?>
                                    </span><strong>
                                        <?php echo esc_html($global_stats['news_types'][$k]); ?>
                                    </strong></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="sahab-stat-card__chart">
                        <div id="newsTypeDonut"></div>
                    </div>
                </div>

                <!-- کارت ۲: کیس‌ها -->
                <div class="sahab-stat-card">
                    <div class="sahab-stat-card__info">
                        <div class="sahab-stat-card__label">کیس‌های عملیاتی (تعداد خبرهای ثبت شده در هر کیس)</div>
                        <div class="sahab-stat-card__badge-container">
                            <span class="sahab-stat-card__badge badge-emerald">
                                <?php echo number_format_i18n($total_cases); ?>
                            </span>
                        </div>
                        <ul class="sahab-stat-card__list">
                            <?php if (!empty($global_stats['cases'])):
                                foreach ($global_stats['cases'] as $c): ?>
                                    <li class="sahab-stat-card__row"><span>
                                            <?php echo esc_html($c['name']); ?>
                                        </span><strong>
                                            <?php echo esc_html($c['count']); ?>
                                        </strong></li>
                                <?php endforeach; else: ?>
                                <li class="sahab-stat-card__row"><span class="text-slate-400">بدون مورد
                                        فعال</span><strong>۰</strong></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <div class="sahab-stat-card__chart">
                        <div id="casesDonut"></div>
                    </div>
                </div>

                <!-- کارت ۳: موضوعات رصدی (تغییر عنوان طبق تصویر شماره ۱) -->
                <div class="sahab-stat-card">
                    <div class="sahab-stat-card__info">
                        <div class="sahab-stat-card__label">موضوعات رصدی (تعداد اخبار ثبت شده در هر موضوع)</div>
                        <div class="sahab-stat-card__badge-container">
                            <span class="sahab-stat-card__badge badge-amber">
                                <?php echo number_format_i18n($total_topics); ?>
                            </span>
                        </div>
                        <ul class="sahab-stat-card__list">
                            <?php foreach ($choices['subject'] as $k => $v):
                                if (empty($global_stats['subjects'][$k]))
                                    $global_stats['subjects'][$k] = 0; ?>
                                <li class="sahab-stat-card__row">
                                    <span>
                                        <?php echo esc_html($v); ?>
                                    </span>
                                    <strong>
                                        <?php echo esc_html($global_stats['subjects'][$k]); ?>
                                    </strong>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="sahab-stat-card__chart">
                        <div id="topicDonut"></div>
                    </div>
                </div>

            </div>

            <!-- سنجش عملکرد ادارات (تغییر ساختار باکس‌ها به لیست متنی استاندارد دو ستونه کاملاً شبیه به موضوعات رصدی) -->
            <div class="sahab-report-box">
                <div class="sahab-stat-card" style="border:none; padding:0; box-shadow:none;">
                    <div class="sahab-stat-card__info" style="flex:2;">
                        <div class="sahab-stat-card__label">سنجش عملکرد ادارات (تعداد اخبار کارشناسان آن اداره)</div>
                        <div class="sahab-stat-card__badge-container">
                            <span class="sahab-stat-card__badge badge-indigo">
                                <?php echo number_format_i18n($total_dept_news); ?>
                            </span>
                        </div>

                        <!-- تبدیل باکس‌های ضخیم قبلی به لیست خط‌کشی‌شده مینی‌مال دقیقاً مشابه کامپوننت بالایی -->
                        <ul class="sahab-stat-card__list">
                            <?php if (!empty($departments)):
                                foreach ($departments as $m_id => $dept): ?>
                                    <li class="sahab-stat-card__row">
                                        <span>
                                            <?php echo esc_html($dept['name']); ?>
                                        </span>
                                        <strong>
                                            <?php echo esc_html($dept['total_news']); ?>
                                        </strong>
                                    </li>
                                <?php endforeach;
                            endif; ?>
                        </ul>
                    </div>
                    <div class="sahab-stat-card__chart" style="flex:3;">
                        <div id="deptDonutChart"></div>
                    </div>
                </div>
            </div>

        </div> <!-- بستن تگ پایانی تب اول (bi-system) که در جایگذاری قبلی جا افتاده بود -->

        <!-- ===================== TAB 2: ANALYSTS ===================== -->
        <div id="bi-analysts" class="sahab-tab-content">

            <!-- کارت پرسنل مینی‌مال مجهز به نمودار میله‌ای رنگارنگ تمام‌عرض -->
            <div class="sahab-report-box box-green" style="margin-bottom: 25px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h4 class="text-sm font-bold text-slate-600" style="margin: 0;">پرسنل حاضر در گزارش</h4>
                    <span class="sahab-stat-card__badge badge-rose" style="font-size: 24px; padding: 4px 14px;">
                        <?php echo number_format_i18n($total_staff); ?> نفر
                    </span>
                </div>
                <!-- باکس نمودار میله‌ای به صورت تمام‌عرض -->
                <div id="staffBarChart" style="width: 100%; min-height: 250px;"></div>
            </div>

            <!-- جدول کارنامه مقایسه‌ای کارشناسان -->
            <div class="sahab-report-box">
                <h3 class="text-sm font-black text-slate-500 mb-4">جدول کارنامه مقایسه‌ای کارشناسان</h3>
                <div class="overflow-x-auto">
                    <table class="sahab-table">
                        <thead>
                            <tr>
                                <th class="w-12 text-center">ردیف</th>
                                <th>نام کارشناس</th>
                                <th class="text-center">تعداد اخبار</th>
                                <th>توزیع موضوعی کارشناس</th>
                                <th>ارزیابی</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($analysts)):
                                $i = 1;
                                foreach ($analysts as $a_id => $analyst): ?>
                                    <tr>
                                        <td class="font-mono text-slate-400 text-center">
                                            <?php echo esc_html($i++); ?>
                                        </td>
                                        <td class="font-bold">
                                            <?php
                                            // ۱. آدرس پایه برگه پروفایل تحلیلی کارشناس در وردپرس
                                            $profile_url = home_url('/analyst-profile/');

                                            // ۲. جمع‌آوری پارامترهای فیلتر فعلی برای ارسال به صفحه جدید
                                            $query_args = array(
                                                'id' => esc_attr($a_id), // شناسه منحصربه‌فرد کارشناس
                                                'from_date' => isset($_GET['from_date']) ? sanitize_text_field($_GET['from_date']) : '',
                                                'to_date' => isset($_GET['to_date']) ? sanitize_text_field($_GET['to_date']) : ''
                                            );

                                            // ۳. ترکیب آدرس با پارامترها
                                            $final_url = add_query_arg($query_args, $profile_url);
                                            ?>

                                            <!-- ۴. رندر تگ لینک برای باز شدن در تب جدید بدون آسیب به فیلترهای جاری -->
                                            <a href="<?php echo esc_url($final_url); ?>" target="_blank"
                                                class="sahab-analyst-link"
                                                style="color: #0284c7; text-decoration: none; border-bottom: 1px dashed #0284c7; padding-bottom: 2px; transition: color 0.2s;"
                                                onmouseover="this.style.color='#0369a1'"
                                                onmouseout="this.style.color='#0284c7'">
                                                <?php echo esc_html($analyst['name']); ?>
                                            </a>
                                        </td>
                                        <?php echo esc_html($analyst['news_count']); ?>
                                        </td>
                                        <td>
                                            <div class="flex flex-wrap gap-1">
                                                <?php foreach ($choices['subject'] as $sub_key => $sub_label): ?>
                                                    <?php if (isset($analyst['subjects'][$sub_key]) && $analyst['subjects'][$sub_key] > 0): ?>
                                                        <span class="flat-badge text-[11px]">
                                                            <?php echo esc_html($sub_label); ?>: <strong>
                                                                <?php echo esc_html($analyst['subjects'][$sub_key]); ?>
                                                            </strong>
                                                        </span>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="flex flex-wrap gap-1">
                                                <?php foreach ($choices['evaluation'] as $eval_key => $eval_label): ?>
                                                    <?php if (isset($analyst['evaluations'][$eval_key]) && $analyst['evaluations'][$eval_key] > 0): ?>
                                                        <span class="flat-badge">
                                                            <span
                                                                class="w-1.5 h-1.5 rounded-full <?php echo $eval_key === 'valid' ? 'bg-emerald-500' : ($eval_key === 'probably_valid' ? 'bg-sky-500' : 'bg-amber-500'); ?>"></span>
                                                            <?php echo esc_html($eval_label); ?>: <strong>
                                                                <?php echo esc_html($analyst['evaluations'][$eval_key]); ?>
                                                            </strong>
                                                        </span>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof jalaliDatepicker !== 'undefined') {
            jalaliDatepicker.startWatch({
                minDate: "attr", maxDate: "attr", time: false, separatorChar: "/", changeMonthRotateYear: true
            });
        }

        var isDark = document.body.classList.contains('dark');
        var tc = isDark ? '#94a3b8' : '#64748b';

        // تابع مشترک رندر نمودارهای دونات
        function donutCfg(series, labels, colors) {
            return {
                chart: {
                    type: 'donut',
                    height: 200,
                    sparkline: { enabled: false },
                    animations: { enabled: true }
                },
                series: series,
                labels: labels,
                colors: colors,
                stroke: { show: true, width: 2, colors: [isDark ? '#1e293b' : '#fff'] },
                dataLabels: { enabled: true, style: { fontSize: '11px', fontFamily: 'monospace' } },
                plotOptions: { pie: { donut: { size: '65%' } } },
                legend: {
                    show: true,
                    position: 'right',
                    fontSize: '11px',
                    fontFamily: 'Tahoma',
                    labels: { colors: tc },
                    markers: { width: 8, height: 8 }
                },
                tooltip: { enabled: true }
            };
        }

        // ۱. نمودار نوع خبر 
        var ntLabels = <?php echo json_encode(array_values($choices['news_type'])); ?>;
        var ntData = <?php echo json_encode(array_values($global_stats['news_types'])); ?>;
        if (ntData.reduce((a, b) => a + b, 0) > 0) {
            new ApexCharts(document.querySelector('#newsTypeDonut'), donutCfg(ntData, ntLabels, ['#0284c7', '#3b82f6', '#6366f1', '#a855f7', '#ec4899'])).render();
        }

        // ۲. نمودار کیس‌های عملیاتی
        var cLabels = <?php echo json_encode(array_column(array_values($global_stats['cases']), 'name')); ?>;
        var cData = <?php echo json_encode(array_column(array_values($global_stats['cases']), 'count')); ?>;
        if (cData.reduce((a, b) => a + b, 0) > 0) {
            new ApexCharts(document.querySelector('#casesDonut'), donutCfg(cData, cLabels, ['#10b981', '#f59e0b', '#3b82f6', '#ef4444', '#8b5cf6', '#ec4899'])).render();
        }

        // ۳. نمودار موضوعات رصدی (اصلاح پالت چندرنگ به درخواست تصویر شماره ۱)
        var tLabels = <?php echo json_encode(array_values($choices['subject'])); ?>;
        var tData = <?php echo json_encode(array_values($global_stats['subjects'])); ?>;
        if (tData.reduce((a, b) => a + b, 0) > 0) {
            new ApexCharts(document.querySelector('#topicDonut'), donutCfg(tData, tLabels, ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#ec4899'])).render();
        }

        // ۴. نمودار ستونی/میله‌ای رنگارنگ توزیع عملکرد پرسنل حاضر در گزارش
        var sLabels = <?php echo json_encode(array_values(wp_list_pluck($analysts, 'name'))); ?>;
        var sData = <?php echo json_encode(array_values(wp_list_pluck($analysts, 'news_count'))); ?>;

        if (sData.reduce((a, b) => a + b, 0) > 0) {
            new ApexCharts(document.querySelector('#staffBarChart'), {
                chart: {
                    type: 'bar',
                    height: 280,
                    toolbar: { show: false }
                },
                series: [{
                    name: 'تعداد اخبار',
                    data: sData
                }],
                plotOptions: {
                    bar: {
                        columnWidth: '45%',
                        distributed: true, // فعال‌سازی قابلیت رنگ مجزا و رنگارنگ برای هر ستون
                        borderRadius: 4,
                        dataLabels: { position: 'top' }
                    }
                },
                colors: ['#0284c7', '#f59e0b', '#10b981', '#6366f1', '#ec4899', '#e11d48', '#06b6d4', '#8b5cf6'], // پالت رنگی داینامیک و جذاب برای ستون‌ها
                dataLabels: {
                    enabled: true,
                    formatter: function (val) { return val; },
                    offsetY: -20,
                    style: { fontSize: '12px', colors: [tc], fontFamily: 'monospace' }
                },
                legend: { show: false }, // حذف لژندهای تکراری به دلیل وجود جدول زیرین
                xaxis: {
                    categories: sLabels,
                    labels: { style: { colors: tc, fontSize: '11px', fontFamily: 'Tahoma' } }
                },
                yaxis: {
                    labels: { style: { colors: tc, fontFamily: 'monospace' } }
                },
                tooltip: { theme: isDark ? 'dark' : 'light' }
            }).render();
        }

        // ۵. نمودار سنجش عملکرد ادارات (تصویر شماره ۴)
        var deptLabels = <?php echo json_encode(array_values(wp_list_pluck($departments, 'name'))); ?>;
        var deptData = <?php echo json_encode(array_values(wp_list_pluck($departments, 'total_news'))); ?>;
        if (!deptLabels.length) { deptLabels = ['بدون داده']; deptData = [0]; }
        new ApexCharts(document.querySelector('#deptDonutChart'), {
            chart: { type: 'donut', height: 260, foreColor: tc },
            series: deptData, labels: deptLabels,
            stroke: { show: true, width: 2, colors: [isDark ? '#1e293b' : '#fff'] },
            dataLabels: { enabled: true },
            colors: ['#0284c7', '#f59e0b', '#10b981', '#6366f1', '#ec4899'],
            legend: { position: 'bottom', fontFamily: 'Tahoma' },
            plotOptions: { pie: { donut: { size: '65%' } } }
        }).render();

        // ۶. روند زمانی هوشمند اسناد (تغییر یافته به حالت منحنی یکنواخت / Smooth)
        var tlLabels = <?php echo json_encode(array_keys($global_stats['timeline'])); ?>;
        var tlData = <?php echo json_encode(array_values($global_stats['timeline'])); ?>;
        if (!tlLabels.length) { tlLabels = ['بدون داده']; tlData = [0]; }
        new ApexCharts(document.querySelector('#trendChart'), {
            chart: { type: 'area', height: 280, toolbar: { show: false }, foreColor: tc },
            series: [{ name: 'اخبار منتشر شده', data: tlData }],
            xaxis: { categories: tlLabels },
            colors: ['#10b981'],
            stroke: { curve: 'smooth', width: 3 } // تبدیل وضعیت شکست خطوط به حالت روان و نرم نرم افزار
        }).render();
    });

    function switchTab(evt, tabId) {
        var i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName("sahab-tab-content");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].classList.remove("active");
        }
        tablinks = document.getElementsByClassName("sahab-tab-btn");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].classList.remove("active");
        }
        document.getElementById(tabId).classList.add("active");
        evt.currentTarget.classList.add("active");
    }
</script>

<?php
// ۴. فراخوانی فوتر بومی و اصلی پلتفرم سحاب
get_footer();
?>