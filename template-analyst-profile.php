<?php
/**
 * Template Name: Profile Analyst Report
 * Description: صفحه پروفایل تحلیلی و کارنامه اختصاصی هر کارشناس در تب جدید
 */

// ۱. دریافت پارامترها از آدرس URL
$analyst_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$from_date = isset($_GET['from_date']) ? sanitize_text_field($_GET['from_date']) : '';
$to_date = isset($_GET['to_date']) ? sanitize_text_field($_GET['to_date']) : '';

if (!$analyst_id) {
    wp_die('خطا: شناسه کارشناس معتبر نمی‌باشد.');
}

// لود کردن هدر وردپرس برای دسترسی به توابع و استایل‌ها
get_header();

// ۲. شبیه‌سازی منطق استخراج داده داشبورد اصلی
// در صورتی که منطق ساخت $report_data در یک فایل جداگانه include می‌شود یا تابع است، آن را فراخوانی کنید.
// در اینجا فرض می‌کنیم متغیرهای محیطی با فراخوانی منطق اصلی مقداردهی می‌شوند.
// برای راه‌اندازی اولیه، داده‌ها را مستقیماً از دیتابیس یا آرایه مادر واکشی می‌کنیم:

global $wpdb;

// به دست آوردن نام واقعی کارشناس از روی شناسه (با فرض نام کارشناسان از متای کاربر یا جدول متناظر)
// اصلاح تابع وردپرس برای دریافت اطلاعات کاربر
$user_info = get_user_by('id', $analyst_id);
$analyst_name = $user_info ? $user_info->display_name : 'کارشناس شماره ' . $analyst_id;

// نمونه‌سازی داده‌های موضوعی و ارزیابی برای رندر نمودارها (این مقادیر در گام بعد با کوئری دیتابیس دقیق شما همگام می‌شوند)
$subject_labels = array('روحانیون شاخص', 'بین الملل', 'تحجر', 'سیاسی');
$subject_counts = array(2, 1, 3, 0); // مقادیر تستی اولیه برای صحت‌سنجی نمودارها

$eval_labels = array('صحت دارد', 'احتمالاً صحت دارد', 'عدم صحت');
$eval_counts = array(1, 2, 0);
?>

<!-- تزریق کتابخانه‌های مورد نیاز -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script src="https://cdn.tailwindcss.com"></script>

<div class="sahab-profile-wrapper bg-slate-100 min-h-screen py-8 px-4" style="direction: rtl; text-align: right;">
    <div class="max-w-7xl mx-auto">

        <!-- هدر کارنامه اختصاصی -->
        <div
            class="bg-white rounded-2xl p-6 shadow-sm mb-6 flex justify-between items-center border border-slate-200/60">
            <div>
                <h1 class="text-2xl font-black text-slate-800">کارنامه تحلیلی:
                    <?php echo esc_html($analyst_name); ?>
                </h1>
                <p class="text-sm text-slate-500 mt-1">گزارش عملکرد، توزیع موضوعی و روند فعالیت در بازه زمانی درخواستی
                </p>
            </div>

            <div class="text-left font-mono text-xs text-slate-600 bg-slate-50 p-3 rounded-xl border border-slate-200">
                <div>از تاریخ:
                    <?php echo $from_date ? esc_html($from_date) : 'شروع سیستم'; ?>
                </div>
                <div class="mt-1">تا تاریخ:
                    <?php echo $to_date ? esc_html($to_date) : 'امروز'; ?>
                </div>
            </div>
        </div>

        <!-- بخش نمودارها (چیدمان کارت‌ها) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

            <!-- کارت نمودار ۱: توزیع موضوعی اخبار -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/60">
                <h3 class="text-sm font-bold text-slate-700 mb-4 border-r-4 border-sky-500 pr-2">توزیع موضوعی اخبار
                    کارشناس</h3>
                <div id="subjectChart"></div>
            </div>

            <!-- کارت نمودار ۲: وضعیت ارزیابی گزارش‌ها -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/60">
                <h3 class="text-sm font-bold text-slate-700 mb-4 border-r-4 border-emerald-500 pr-2">وضعیت ارزیابی
                    گزارش‌ها</h3>
                <div id="evalChart"></div>
            </div>

        </div>

    </div>
</div>

<script>
    // تنظیمات و رندر نمودار توزیع موضوعی (نمودار ستونی ApexCharts)
    var subjectOptions = {
        series: [{
            name: 'تعداد اخبار',
            data: <?php echo json_encode($subject_counts); ?>
        }],
        chart: {
            type: 'bar',
            height: 300,
            fontFamily: 'Vazirmatn, Tahoma, sans-serif'
        },
        plotOptions: {
            bar: {
                borderRadius: 6,
                horizontal: false,
                columnWidth: '45%',
            }
        },
        dataLabels: { enabled: false },
        xaxis: {
            categories: <?php echo json_encode($subject_labels); ?>,
        },
        colors: ['#0284c7'],
        grid: { borderColor: '#f1f5f9' }
    };

    var subjectChart = new ApexCharts(document.querySelector("#subjectChart"), subjectOptions);
    subjectChart.render();

    // تنظیمات و رندر نمودار وضعیت ارزیابی (نمودار دونات / پای)
    var evalOptions = {
        series: <?php echo json_encode($eval_counts); ?>,
        chart: {
            type: 'donut',
            height: 300,
            fontFamily: 'Vazirmatn, Tahoma, sans-serif'
        },
        labels: <?php echo json_encode($eval_labels); ?>,
        colors: ['#10b981', '#0ea5e9', '#f59e0b'],
        legend: { position: 'bottom' },
        dataLabels: { enabled: true }
    };

    var evalChart = new ApexCharts(document.querySelector("#evalChart"), evalOptions);
    evalChart.render();
</script>

<?php
get_footer();
?>