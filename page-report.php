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

$start_date = isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : '1405/03/01';
$end_date = isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : '1405/04/31';
$filter_dept = isset($_GET['filter_dept']) ? sanitize_text_field($_GET['filter_dept']) : '';
$filter_analyst = isset($_GET['filter_analyst']) ? sanitize_text_field($_GET['filter_analyst']) : '';

$report_data = sahab_get_advanced_bi_report($start_date, $end_date, $filter_dept, $filter_analyst);

$departments = $report_data['departments'];
$analysts = $report_data['analysts'];
$total_news = $report_data['total_processed'];
$global_stats = $report_data['global_stats'];
$choices = $report_data['choices'];

$total_staff = count($analysts);
$total_topics = count(array_filter($global_stats['subjects'])); // فقط موضوعاتی که در این بازه خبر داشته‌اند
if ($total_topics === 0) {
    $total_topics = count($choices['subject']);
}
$total_cases = isset($report_data['total_active_cases']) ? $report_data['total_active_cases'] : count($choices['case']);
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

    /* ردیف فیلترهای یکدست و تک‌خطی */
    .sahab-filter-row {
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        width: 100%;
        flex-wrap: nowrap;
    }

    .sahab-filter-inputs {
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 12px;
    }

    /* ردیف فیلترهای یکدست و تک‌خطی فیکس شده */
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

    /* اصلاح ارتفاع باکس و هم‌ترازی کامل دکمه قرمز */
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

    .sahab-input-date,
    .sahab-select {
        height: 34px !important;
        margin: 0 !important;
    }

    .sahab-btn-print {
        height: 34px !important;
        display: inline-flex !important;
        align-items: center !important;
        margin: 0 !important;
    }

    /* گریدبندی کارت‌ها و نمودارها */
    .sahab-grid-4 {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 20px;
    }

    .sahab-grid-4 .grid-col {
        flex: 1;
        min-width: 220px;
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px;
    }

    body.dark .sahab-grid-4 .grid-col {
        background-color: #1e293b;
        border-color: #334155;
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

    .sahab-chart-side {
        flex: 2;
        min-width: 350px;
    }

    .sahab-input-date,
    .sahab-select {
        background-color: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 6px !important;
        padding: 6px 12px !important;
        font-size: 13px !important;
        color: #1e293b !important;
        height: auto !important;
        display: inline-block !important;
        width: auto !important;
    }

    body.dark .sahab-input-date,
    body.dark .sahab-select {
        background-color: #1e293b !important;
        border-color: #475569 !important;
        color: #f8fafc !important;
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

        <!-- ===================== CONTROLS FILTER (تک‌ردیفه و اتوماتیک) ===================== -->
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
                        <option value="">همه کارشناسان</option>
                        <?php
                        // لود لیست کامل تحلیلگران برای فیلتر مستقیم
                        $all_base_users = get_users(array('fields' => array('ID', 'display_name')));
                        foreach ($all_base_users as $u_item):
                            if ($u_item->ID === 1 || strtolower($u_item->display_name) === 'administrator')
                                continue;
                            // اگر فیلتر اداره فعال بود، فقط کارشناسان آن اداره را نشان بده
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

                    <a href="<?php echo strtok($_SERVER["REQUEST_URI"], '?'); ?>" class="sahab-btn-reset">حذف
                        فیلترها</a>
                </div>
                <div>
                    <button type="button" onclick="window.print()" class="sahab-btn-print">🖨 چاپ</button>
                </div>
            </form>
        </div>

        <!-- ===================== TAB NAVIGATION ===================== -->
        <div class="sahab-tabs-nav no-print">
            <button class="sahab-tab-btn active" onclick="switchTab(event, 'bi-system')">تحلیل سیستم و ادارات</button>
            <button class="sahab-tab-btn" onclick="switchTab(event, 'bi-analysts')">کارنامه کارشناسان</button>
        </div>

        <!-- ===================== TAB 1: SYSTEM ===================== -->
        <div id="bi-system" class="sahab-tab-content active">
            <div class="sahab-grid-4">
                <div class="grid-col">
                    <span class="text-xs text-slate-400 block mb-1 font-semibold">کل اخبار فیلتر شده</span>
                    <div class="text-2xl font-black text-sky-600 dark:text-sky-400 font-mono">
                        <?php echo esc_html(number_format_i18n($total_news)); ?>
                    </div>
                </div>
                <div class="grid-col">
                    <span class="text-xs text-slate-400 block mb-1 font-semibold">کیس‌های عملیاتی</span>
                    <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 font-mono">
                        <?php echo esc_html($total_cases); ?>
                    </div>
                </div>
                <div class="grid-col">
                    <span class="text-xs text-slate-400 block mb-1 font-semibold">موضوعات فعال رصد</span>
                    <div class="text-2xl font-black text-amber-600 dark:text-amber-400 font-mono">
                        <?php echo esc_html($total_topics); ?>
                    </div>
                </div>
                <div class="grid-col">
                    <span class="text-xs text-slate-400 block mb-1 font-semibold">پرسنل حاضر در گزارش</span>
                    <div class="text-2xl font-black text-rose-600 dark:text-rose-400 font-mono">
                        <?php echo esc_html($total_staff); ?>
                    </div>
                </div>
            </div>

            <div class="sahab-grid-charts">
                <div class="sahab-report-box sahab-chart-main box-green">
                    <h4 class="text-xs font-bold text-slate-500 mb-4">تحلیل روند زمانی توزیع اسناد سحاب</h4>
                    <div id="trendChart"></div>
                </div>
                <div class="sahab-report-box sahab-chart-side">
                    <h4 class="text-xs font-bold text-slate-500 mb-4">سهم موضوعات (داینامیک ACF)</h4>
                    <div id="topicDonut"></div>
                </div>
            </div>

            <!-- SECTOR: DEPARTMENTS COMPARATIVE BAR CHART -->
            <div class="sahab-report-box">
                <h3 class="text-sm font-black text-slate-500 mb-4">سنجش عملکرد ادارات (در بازه انتخابی)</h3>
                <div class="sahab-grid-charts" style="align-items: center;">
                    <div style="flex: 3; min-width: 500px;">
                        <!-- کانتینر فیکس شده نمودار ادارات -->
                        <div id="deptDonutChart"></div>
                    </div>
                    <div style="flex: 2; min-width: 250px;" class="space-y-2">
                        <?php if (!empty($departments)):
                            foreach ($departments as $m_id => $dept): ?>
                                <div class="sahab-report-box !p-3 !mb-2" style="background-color: var(--badge-bg);">
                                    <div class="text-xs font-black">
                                        <?php echo esc_html($dept['name']); ?>
                                    </div>
                                    <div class="text-xs font-bold text-sky-600 mt-1">
                                        <?php echo esc_html($dept['total_news']); ?> خبر مانیتور شده
                                    </div>
                                </div>
                            <?php endforeach; endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===================== TAB 2: ANALYSTS ===================== -->
        <div id="bi-analysts" class="sahab-tab-content">
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
                                <th>وضعیت فیلدهای ارزیابی (ACF)</th>
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
                                            <?php echo esc_html($analyst['name']); ?>
                                        </td>
                                        <td class="font-mono font-bold text-emerald-600 text-center">
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
    // فعال‌سازی و شنود خودکار کلیک تقویم جلالی بدون نیاز به دکمه اعمال
    if (typeof jalaliDatepicker !== 'undefined') {
        jalaliDatepicker.startWatch({
            minDate: "attr",
            maxDate: "attr",
            time: false,
            separatorChar: "/",
            changeMonthRotateYear: true
        });

        // هوک اختصاصی برای ثبت خودکار فرم بلافاصله پس از انتخاب روز در تقویم
        document.addEventListener("jdp:change", function (e) {
            document.getElementById('sahab-bi-filter-form').submit();
        });
    }

    function toggleTheme() { document.body.classList.toggle('dark'); }

    function switchTab(evt, tabId) {
        var i, tc, tl;
        tc = document.getElementsByClassName("sahab-tab-content");
        for (i = 0; i < tc.length; i++) { tc[i].classList.remove("active"); }
        tl = document.getElementsByClassName("sahab-tab-btn");
        for (i = 0; i < tl.length; i++) { tl[i].classList.remove("active"); }
        document.getElementById(tabId).classList.add("active");
        evt.currentTarget.classList.add("active");
    }

    document.addEventListener('DOMContentLoaded', function () {
        var isDark = document.body.classList.contains('dark');
        var textColors = isDark ? '#94a3b8' : '#64748b';

        var timelineLabels = <?php echo json_encode(array_keys($global_stats['timeline'])); ?>;
        var timelineData = <?php echo json_encode(array_values($global_stats['timeline'])); ?>;
        if (timelineLabels.length === 0) { timelineLabels = ['بدون داده']; timelineData = [0]; }

        // ۱. چارت روند زمان
        new ApexCharts(document.querySelector("#trendChart"), {
            chart: { type: 'area', height: 260, toolbar: { show: false }, foreColor: textColors, animations: { enabled: false } },
            series: [{ name: 'اسناد ثبت شده', data: timelineData }],
            xaxis: { categories: timelineLabels },
            colors: ['#10b981'],
            stroke: { curve: 'straight', width: 2 }
        }).render();

        // ۲. چارت دایره ای موضوعات
        var topicLabels = <?php echo json_encode(array_values($choices['subject'])); ?>;
        var topicData = <?php echo json_encode(array_values($global_stats['subjects'])); ?>;
        new ApexCharts(document.querySelector("#topicDonut"), {
            chart: { type: 'pie', height: 260, foreColor: textColors, animations: { enabled: false } },
            series: topicData,
            labels: topicLabels,
            stroke: { show: false }
        }).render();

        // ۳. جایگزینی با نمودار حلقوی توپر و شیک برای سهم ادارات
        var deptLabels = <?php echo json_encode(array_values(wp_list_pluck($departments, 'name'))); ?>;
        var deptData = <?php echo json_encode(array_values(wp_list_pluck($departments, 'total_news'))); ?>;

        if (deptLabels.length === 0) { deptLabels = ['بدون داده']; deptData = [0]; }

        new ApexCharts(document.querySelector("#deptDonutChart"), {
            chart: { type: 'donut', height: 240, foreColor: textColors, animations: { enabled: false } },
            series: deptData,
            labels: deptLabels,
            stroke: { show: false },
            colors: ['#0284c7', '#f59e0b', '#10b981', '#6366f1', '#ec4899'],
            plotOptions: {
                pie: {
                    donut: {
                        size: '50%' // ضخامت کلف و منسجم حلقه
                    }
                }
            }
        }).render();
    });
</script>

<?php
// ۴. فراخوانی فوتر بومی و اصلی پلتفرم سحاب
get_footer();
?>