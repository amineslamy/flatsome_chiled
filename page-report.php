<?php
/**
 * Template Name: داشبورد گزارشات سحاب
 * مسیر فایل: ریشه قالب / page-report.php
 * آدرس دسترسی: http://sahab.test/report/
 */

if (!defined('ABSPATH')) {
    exit;
}

$start_date     = isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : date('Y/m/d', strtotime('-30 days'));
$end_date       = isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : date('Y/m/d');
$filter_dept    = isset($_GET['filter_dept']) ? sanitize_text_field($_GET['filter_dept']) : '';
$filter_analyst = isset($_GET['filter_analyst']) ? sanitize_text_field($_GET['filter_analyst']) : '';

$report_data = sahab_get_advanced_bi_report($start_date, $end_date, $filter_dept, $filter_analyst);

$departments     = $report_data['departments'];
$analysts        = $report_data['analysts'];
$total_news      = $report_data['total_processed'];
$global_stats    = $report_data['global_stats'];
$choices         = $report_data['choices'];

$total_staff = count($departments) + count($analysts);
$total_topics = count($choices['subject']); 
$total_cases = 18; 
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سامانه هوشمند سحاب | گزارش‌گیری و تحلیل هوشمند</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- اصلاح آدرس‌دهی دقیق و محلی به فولدر assets/admin مطابق ساختار لاراگون شما -->
    <script src="<?php echo get_stylesheet_directory_uri(); ?>/assets/admin/js/apexcharts.min.js"></script>
    <script src="<?php echo get_stylesheet_directory_uri(); ?>/assets/admin/js/jalali-datepicker.min.js"></script>
    <link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/assets/admin/css/jalali-datepicker.min.css">
    
    <style>
        :root{
            --body-bg: #f1f5f9; --panel-bg: #ffffff; --border-color: #e2e8f0;
            --text-main: #1e293b; --text-muted: #64748b;
            --sahab-blue: #0284c7; --sahab-green: #10b981; --badge-bg: #f8fafc;
        }
        body.dark{
            --body-bg: #0f172a; --panel-bg: #1e293b; --border-color: #334155;
            --text-main: #f8fafc; --text-muted: #94a3b8; --badge-bg: #0f172a;
        }
        *{ font-family:'Vazirmatn', sans-serif; box-sizing: border-box; }
        html, body { min-height: 100%; background-color: var(--body-bg); color: var(--text-main); transition: background-color 0.2s; margin: 0; padding: 0; }
        .sahab-header { background-color: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 10px 24px; display: flex; align-items: center; justify-content: space-between; }
        body.dark .sahab-header { background-color: #1e293b; border-bottom: 1px solid #334155; }
        .sahab-nav { display: flex; align-items: center; gap: 24px; }
        .sahab-nav a { font-size: 14px; font-weight: 700; color: var(--text-main); text-decoration: none; padding: 4px 0; }
        .sahab-nav a:hover, .sahab-nav a.active { color: #0284c7; border-bottom: 2px solid #0284c7; }
        .flat-card { background-color: var(--panel-bg); border: 1px solid var(--border-color); border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); padding: 20px; }
        .flat-card.card-green { border: 2px solid var(--sahab-green); box-shadow: 0 4px 12px rgba(16,185,129,0.05); }
        .flat-btn-primary { background-color: #ffffff; border: 1px solid #0284c7; color: #0284c7; font-weight: 700; border-radius: 8px; padding: 8px 16px; font-size: 13px; cursor: pointer; }
        .flat-btn-primary:hover { background-color: #0284c7; color: #ffffff; }
        .flat-btn-ghost { background-color: transparent; border: 1px solid var(--border-color); color: var(--text-main); font-weight: 600; border-radius: 8px; padding: 8px 16px; font-size: 13px; cursor: pointer; }
        
        input[data-jdp], select { background-color: var(--panel-bg); border: 1px solid var(--border-color); border-radius: 8px; padding: 6px 12px; color: var(--text-main); font-size: 13px; text-align: left; direction: ltr; cursor: pointer; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        thead th { font-size: 12px; color: var(--text-muted); font-weight: 700; text-align: right; padding: 14px; border-bottom: 2px solid var(--border-color); }
        tbody td { font-size: 13px; padding: 14px; border-bottom: 1px solid var(--border-color); vertical-align: middle; }
        tbody tr:hover { background-color: var(--badge-bg); }
        .flat-badge { font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 6px; background-color: var(--badge-bg); border: 1px solid var(--border-color); display: inline-flex; align-items: center; gap: 6px; }
        
        .tabs-nav { display: flex; gap: 8px; border-bottom: 1px solid var(--border-color); margin-bottom: 20px; }
        .tab-btn { background: none; border: none; padding: 12px 24px; font-size: 14px; font-weight: 700; color: var(--text-muted); cursor: pointer; border-bottom: 3px solid transparent; transition: all 0.2s; }
        .tab-btn:hover { color: var(--text-main); }
        .tab-btn.active { color: #0284c7; border-bottom-color: #0284c7; }
        .tab-content { display: none; animation: fadeIn 0.25s ease; }
        .tab-content.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(3px); } to { opacity: 1; transform: translateY(0); } }
        .theme-toggle-btn { background: none; border: 1px solid var(--border-color); border-radius: 8px; padding: 6px 12px; cursor: pointer; font-size: 12px; font-weight: 700; color: var(--text-main); }
    </style>
</head>
<body>

<!-- ===================== هدر سحاب ===================== -->
<header class="sahab-header no-print">
    <div class="flex items-center gap-6">
        <div class="text-xl font-black text-cyan-600 dark:text-cyan-400">☁ پلتفرم سحاب</div>
        <nav class="sahab-nav">
            <a href="<?php echo home_url('/dashboard/'); ?>">میز کار سحاب</a>
            <a href="<?php echo home_url('/create-news/'); ?>">ساخت خبر</a>
            <a href="<?php echo home_url('/report/'); ?>" class="active">گزارش</a>
            <a href="<?php echo home_url('/advanced-search/'); ?>">جستجوی پیشرفته</a>
        </nav>
    </div>
</header>

<div class="max-w-[1400px] mx-auto px-4 md:px-8 py-6">

    <!-- ===================== FILTERS CONTROLS ===================== -->
    <div class="flat-card mb-6 no-print">
        <form method="GET" action="" class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-1">
                    <span class="text-xs font-semibold">از:</span>
                    <input type="text" data-jdp name="start_date" value="<?php echo esc_attr($start_date); ?>" placeholder="انتخاب تاریخ شروع" autocomplete="off">
                </div>
                <div class="flex items-center gap-1">
                    <span class="text-xs font-semibold">تا:</span>
                    <input type="text" data-jdp name="end_date" value="<?php echo esc_attr($end_date); ?>" placeholder="انتخاب تاریخ پایان" autocomplete="off">
                </div>

                <select name="filter_dept">
                    <option value="">همه ادارات</option>
                    <?php foreach ($departments as $m_id => $dept): ?>
                        <option value="<?php echo esc_attr($m_id); ?>" <?php selected($filter_dept, $m_id); ?>><?php echo esc_html($dept['name']); ?></option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="flat-btn-primary">اعمال فیلتر داینامیک</button>
                <a href="<?php echo strtok($_SERVER["REQUEST_URI"], '?'); ?>" class="flat-btn-ghost text-xs">حذف فیلتر</a>
            </div>

            <div class="flex items-center gap-3">
                <button type="button" onclick="toggleTheme()" class="theme-toggle-btn">🌓 تغییر پوسته</button>
                <button type="button" onclick="window.print()" class="flat-btn-ghost text-xs">🖨 چاپ</button>
            </div>
        </form>
    </div>

    <!-- ===================== TAB NAVIGATION ===================== -->
    <div class="tabs-nav no-print">
        <button class="tab-btn active" onclick="switchTab(event, 'tab-system')">تحلیل سیستم و ادارات</button>
        <button class="tab-btn" onclick="switchTab(event, 'tab-analysts')">کارنامه کارشناسان</button>
    </div>

    <!-- ===================== TAB 1: SYSTEM & DEPARTMENTS ===================== -->
    <div id="tab-system" class="tab-content active">
        <!-- SYSTEM COUNTERS -->
        <section class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="flat-card">
                <span class="text-xs text-slate-500 block mb-1">کل اخبار فیلتر شده</span>
                <div class="text-2xl font-black text-sky-600 dark:text-sky-400 font-mono"><?php echo esc_html(number_format_i18n($total_news)); ?></div>
            </div>
            <div class="flat-card">
                <span class="text-xs text-slate-500 block mb-1">کیس‌های عملیاتی</span>
                <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 font-mono"><?php echo esc_html($total_cases); ?></div>
            </div>
            <div class="flat-card">
                <span class="text-xs text-slate-500 block mb-1">موضوعات فعال رصد</span>
                <div class="text-2xl font-black text-amber-600 dark:text-amber-400 font-mono"><?php echo esc_html($total_topics); ?></div>
            </div>
            <div class="flat-card">
                <span class="text-xs text-slate-500 block mb-1">پرسنل حاضر در گزارش</span>
                <div class="text-2xl font-black text-rose-600 dark:text-rose-400 font-mono"><?php echo esc_html($total_staff); ?></div>
            </div>
        </section>

        <!-- CHARTS SECTION -->
        <section class="grid grid-cols-1 lg:grid-cols-5 gap-5 mb-6">
            <div class="flat-card card-green lg:col-span-3">
                <h4 class="text-xs font-bold text-slate-500 mb-4">تحلیل روند زمانی توزیع اسناد</h4>
                <div id="trendChart"></div>
            </div>
            <div class="flat-card lg:col-span-2">
                <h4 class="text-xs font-bold text-slate-500 mb-4">سهم موضوعات (داینامیک ACF)</h4>
                <div id="topicDonut"></div>
            </div>
        </section>

        <!-- DEPARTMENTS REPORT -->
        <section class="mb-6">
            <h3 class="text-sm font-black text-slate-500 mb-3">سنجش عملکرد و بازدهی ادارات تابعه سحاب</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                <?php if (!empty($departments)): foreach ($departments as $m_id => $dept): ?>
                    <div class="flat-card">
                        <div class="text-sm font-black text-slate-800 dark:text-slate-100 mb-2"><?php echo esc_html($dept['name']); ?></div>
                        <div class="w-full h-px bg-slate-200 dark:bg-slate-700 my-2"></div>
                        <div class="flex justify-between text-xs mt-2">
                            <span class="text-slate-500">مجموع اسناد اداره:</span>
                            <span class="font-bold text-sky-600 dark:text-sky-400"><?php echo esc_html($dept['total_news']); ?> خبر</span>
                        </div>
                    </div>
                <?php endforeach; else: ?>
                    <div class="flat-card col-span-4 text-center text-slate-400 text-xs">هیچ اداره‌ای یافت نشد.</div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <!-- ===================== TAB 2: ANALYSTS TABLE ===================== -->
    <div id="tab-analysts" class="tab-content">
        <section class="flat-card mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-black text-slate-500">جدول کارنامه مقایسه‌ای کارشناسان</h3>
            </div>
            <div class="overflow-x-auto">
                <table>
                    <thead>
                        <tr>
                            <th class="w-12 text-center">ردیف</th>
                            <th>نام کارشناس</th>
                            <th class="text-center">تعداد اخبار ثبت شده</th>
                            <th>توزیع موضوعی کارشناس</th>
                            <th>وضعیت فیلدهای ارزیابی (ACF)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($analysts)): $i = 1; foreach ($analysts as $a_id => $analyst): ?>
                            <tr>
                                <td class="font-mono text-slate-400 text-center"><?php echo esc_html($i++); ?></td>
                                <td class="font-bold"><?php echo esc_html($analyst['name']); ?></td>
                                <td class="font-mono font-bold text-emerald-600 dark:text-emerald-400 text-center"><?php echo esc_html($analyst['news_count']); ?></td>
                                <td>
                                    <div class="flex flex-wrap gap-1.5">
                                        <?php foreach ($choices['subject'] as $sub_key => $sub_label): ?>
                                            <?php if (isset($analyst['subjects'][$sub_key]) && $analyst['subjects'][$sub_key] > 0): ?>
                                                <span class="flat-badge border-slate-300 dark:border-slate-600 text-[10px] text-slate-500 bg-white dark:bg-slate-800"><?php echo esc_html($sub_label); ?>: <span class="font-mono font-bold text-slate-700 dark:text-slate-300"><?php echo esc_html($analyst['subjects'][$sub_key]); ?></span></span>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                <td>
                                    <!-- رندر فیکس شده و کاملاً داینامیک فیلدهای ارزیابی ACF بر اساس تصویر ارسالی شما -->
                                    <div class="flex flex-wrap gap-1.5">
                                        <?php foreach ($choices['evaluation'] as $eval_key => $eval_label): ?>
                                            <?php if (isset($analyst['evaluations'][$eval_key]) && $analyst['evaluations'][$eval_key] > 0): ?>
                                                <span class="flat-badge">
                                                    <span class="w-1.5 h-1.5 rounded-full <?php echo $eval_key === 'valid' ? 'bg-emerald-500' : ($eval_key === 'probably_valid' ? 'bg-sky-500' : 'bg-amber-500'); ?>"></span>
                                                    <?php echo esc_html($eval_label); ?>: <span class="font-bold"><?php echo esc_html($analyst['evaluations'][$eval_key]); ?></span>
                                                </span>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colSpan="5" class="text-center py-6 text-slate-400 text-xs">هیچ کارشناسی در این بازه یافت نشد.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

</div>

<script>
    // لود شکیل و بدون خطای تقویم شمسی محلی
    if(typeof jalaliDatepicker !== 'undefined') {
        jalaliDatepicker.startWatch({
            minDate: "attr",
            maxDate: "attr",
            time: false,
            separatorChar: "/"
        });
    }

    function toggleTheme() {
        document.body.classList.toggle('dark');
    }

    function switchTab(evt, tabId) {
        var i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName("tab-content");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].classList.remove("active");
        }
        tablinks = document.getElementsByClassName("tab-btn");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].classList.remove("active");
        }
        document.getElementById(tabId).classList.add("active");
        evt.currentTarget.classList.add("active");
    }

    document.addEventListener('DOMContentLoaded', function() {
        var isDark = document.body.classList.contains('dark');
        var textColors = isDark ? '#94a3b8' : '#64748b';
        
        var timelineLabels = <?php echo json_encode(array_keys($global_stats['timeline'])); ?>;
        var timelineData = <?php echo json_encode(array_values($global_stats['timeline'])); ?>;
        
        if (timelineLabels.length === 0) {
            timelineLabels = ['بدون داده'];
            timelineData = [0];
        }

        var trendOptions = {
            chart: { type: 'area', height: 260, toolbar: {show:false}, foreColor: textColors, animations: { enabled: false } },
            series: [{ name: 'اسناد ثبت شده', data: timelineData }],
            xaxis: { categories: timelineLabels },
            colors: ['#10b981'],
            stroke: { curve: 'straight', width: 2 }
        };
        new ApexCharts(document.querySelector("#trendChart"), trendOptions).render();

        var topicLabels = <?php echo json_encode(array_values($choices['subject'])); ?>;
        var topicData = <?php echo json_encode(array_values($global_stats['subjects'])); ?>;

        var topicOptions = {
            chart: { type: 'donut', height: 260, foreColor: textColors, animations: { enabled: false } },
            series: topicData,
            labels: topicLabels,
            stroke: { show: false }
        };
        new ApexCharts(document.querySelector("#topicDonut"), topicOptions).render();
    });
</script>
</body>
</html>