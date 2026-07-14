<?php
/**
 * Template Name: داشبورد گزارشات سحاب
 * مسیر فایل: ریشه قالب / page-report.php
 * آدرس دسترسی: http://sahab.test/report/
 */

if (!defined('ABSPATH')) {
    exit;
}

// ۱. دریافت فیلترهای تاریخ (اگر کاربر فیلتر کرده باشد، وگرنه پیش‌فرض ۳۰ روز اخیر)
$start_date = isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : date('Y-m-d', strtotime('-30 days'));
$end_date   = isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : date('Y-m-d');

// ۲. فراخوانی ماژول گزارش‌گیری داینامیک سحاب که قبلاً ساختیم
$report_data = sahab_get_dynamic_bi_report($start_date, $end_date);

$departments = $report_data['departments'];
$analysts    = $report_data['analysts'];
$total_news  = $report_data['total_processed'];

// محاسبه فرضی بقیه ویجت‌ها بر اساس دیتای دیتابیس
$total_cases = 18; 
$total_topics = 24;
$total_staff = count($departments) + count($analysts);
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
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <style>
        :root{
            --bg1:#eaf1fb; --bg2:#f3ecfb; --bg3:#e3f6f6;
            --glass-bg: rgba(255,255,255,0.55); --glass-bg-strong: rgba(255,255,255,0.72);
            --glass-border: rgba(255,255,255,0.65); --glass-shadow: 0 8px 32px rgba(31,58,110,0.10);
            --ink-1:#101a33; --ink-2:#3d4a68; --ink-3:#7180a0;
            --accent:#0891a1; --accent-2:#0ea5b7; --accent-soft: rgba(14,165,183,0.14);
            --violet:#6366f1; --violet-soft: rgba(99,102,241,0.14);
            --amber:#d97706; --amber-soft: rgba(217,119,6,0.14);
            --rose:#e11d48; --rose-soft: rgba(225,29,72,0.12);
            --grid-line: rgba(16,26,51,0.08); --scrollbar: rgba(16,26,51,0.18);
        }
        body.dark{
            --bg1:#0a0e23; --bg2:#161033; --bg3:#0c1b2e;
            --glass-bg: rgba(18,23,46,0.55); --glass-bg-strong: rgba(18,23,46,0.74);
            --glass-border: rgba(255,255,255,0.09); --glass-shadow: 0 8px 34px rgba(0,0,0,0.45);
            --ink-1:#eef1fb; --ink-2:#c2c9e4; --ink-3:#8790b3;
            --accent:#22d3ee; --accent-2:#5eead4; --accent-soft: rgba(34,211,238,0.14);
            --violet:#a78bfa; --violet-soft: rgba(167,139,250,0.16);
            --amber:#fbbf24; --amber-soft: rgba(251,191,36,0.14);
            --rose:#fb7185; --rose-soft: rgba(251,113,133,0.14);
            --grid-line: rgba(255,255,255,0.08); --scrollbar: rgba(255,255,255,0.16);
        }

        *{font-family:'Vazirmatn', sans-serif;}
        html,body{ min-height:100%; color: var(--ink-1); transition: background 0.5s ease, color 0.4s ease; }
        body{
            background: radial-gradient(60% 50% at 12% 8%, rgba(14,165,183,0.16), transparent 60%),
                        radial-gradient(50% 45% at 90% 12%, rgba(99,102,241,0.16), transparent 60%),
                        linear-gradient(160deg, var(--bg1), var(--bg2) 55%, var(--bg3));
            background-attachment: fixed; position:relative; overflow-x:hidden;
        }

        .cloud-blob{ position:fixed; border-radius:50%; filter: blur(70px); opacity:0.55; pointer-events:none; z-index:0; animation: drift 26s ease-in-out infinite alternate; }
        .blob-a{ width:480px; height:480px; background: var(--accent); top:-140px; right:-120px; }
        .blob-b{ width:420px; height:420px; background: var(--violet); bottom:-160px; left:-100px; }
        @keyframes drift{ 0%{ transform: translate(0,0) scale(1); } 50%{ transform: translate(30px,-40px) scale(1.08); } 100%{ transform: translate(-25px,25px) scale(0.96); } }

        .glass{ background: var(--glass-bg); border: 1px solid var(--glass-border); backdrop-filter: blur(16px); box-shadow: var(--glass-shadow); border-radius: 22px; }
        .glass-strong{ background: var(--glass-bg-strong); border: 1px solid var(--glass-border); backdrop-filter: blur(20px); box-shadow: var(--glass-shadow); border-radius: 22px; }
        
        .btn-primary{ background: linear-gradient(135deg, var(--accent), var(--accent-2)); color:#04222a; font-weight:700; border-radius:14px; padding:10px 18px; box-shadow: 0 6px 18px rgba(14,165,183,0.30); border:none; font-size:13px; cursor:pointer; }
        .btn-ghost{ background: var(--glass-bg-strong); border:1px solid var(--glass-border); color: var(--ink-1); font-weight:600; border-radius:14px; padding:10px 16px; font-size:13px; cursor:pointer; }
        input[type=date]{ background: var(--glass-bg-strong); border:1px solid var(--glass-border); border-radius:12px; padding:8px 12px; color:var(--ink-1); font-size:13px; }
        
        table{ border-collapse:separate; border-spacing:0; width:100%; }
        thead th{ font-size:12px; color: var(--ink-3); font-weight:700; text-align:right; padding:12px 14px; border-bottom:1px solid var(--grid-line); }
        tbody td{ font-size:13px; color: var(--ink-1); padding:13px 14px; border-bottom:1px solid var(--grid-line); }
        tbody tr:hover{ background: var(--accent-soft); }
        
        .badge{ font-size:11px; font-weight:700; padding:3px 9px; border-radius:999px; display:inline-flex; align-items:center; }
        .badge-molaheze{ background: var(--amber-soft); color: var(--amber); }
        .badge-nazarie{ background: var(--accent-soft); color: var(--accent); }
        .badge-baznevisi{ background: var(--rose-soft); color: var(--rose); }

        .switch{ position:relative; width:52px; height:28px; border-radius:999px; cursor:pointer; background: linear-gradient(90deg, var(--accent), var(--violet)); padding:3px; display:flex; align-items:center; }
        .switch .knob{ width:22px; height:22px; border-radius:50%; background:#fff; transition: transform .3s; }
        body.dark .switch .knob{ transform: translateX(-24px); }

        @media print{
            .no-print{ display:none !important; }
            body{ background:#fff !important; color:#000 !important; }
            .glass, .glass-strong{ background:#fff !important; box-shadow:none !important; border:1px solid #ccc !important; backdrop-filter:none !important; }
        }
    </style>
</head>
<body class="dark">

<div class="cloud-blob blob-a"></div>
<div class="cloud-blob blob-b"></div>

<div class="relative z-10 max-w-[1400px] mx-auto px-4 md:px-8 py-6">

    <!-- ===================== HEADER & FILTERS ===================== -->
    <header class="glass-strong px-5 md:px-7 py-5 mb-6 no-print">
        <form method="GET" action="" class="flex flex-col xl:flex-row xl:items-center justify-between gap-5">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-2xl" style="background:linear-gradient(135deg, var(--accent), var(--violet)); color:#fff;">☁</div>
                <div>
                    <h1 class="text-lg md:text-xl font-extrabold">سامانه هوشمند سحاب</h1>
                    <p class="text-xs text-slate-400">میز کار تخصصی گزارش‌گیری و هوشمندی رقابتی (BI)</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold">از تاریخ:</span>
                    <input type="date" name="start_date" value="<?php echo esc_attr($start_date); ?>">
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold">تا تاریخ:</span>
                    <input type="date" name="end_date" value="<?php echo esc_attr($end_date); ?>">
                </div>
                <button type="submit" class="btn-primary">اعمال فیلتر داینامیک</button>
                <a href="<?php echo strtok($_SERVER["REQUEST_URI"], '?'); ?>" class="btn-ghost text-center">حذف فیلتر</a>

                <div class="w-px h-8 bg-slate-700 mx-1"></div>

                <span class="text-xs font-bold">تم اقیانوسی:</span>
                <div class="switch" onclick="toggleTheme()"><div class="knob"></div></div>
                
                <button type="button" onclick="window.print()" class="btn-ghost">🖨 چاپ گزارش</button>
            </div>
        </form>
    </header>

    <!-- ===================== SYSTEM STATS ===================== -->
    <section class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="glass p-5">
            <span class="text-xs text-slate-400 block mb-1">کل اخبار در این بازه</span>
            <div class="text-3xl font-black text-cyan-400"><?php echo esc_html(number_format_i18n($total_news)); ?></div>
        </div>
        <div class="glass p-5">
            <span class="text-xs text-slate-400 block mb-1">کیس‌های مانیتورینگ</span>
            <div class="text-3xl font-black text-indigo-400"><?php echo esc_html($total_cases); ?></div>
        </div>
        <div class="glass p-5">
            <span class="text-xs text-slate-400 block mb-1">موضوعات فعال</span>
            <div class="text-3xl font-black text-amber-400"><?php echo esc_html($total_topics); ?></div>
        </div>
        <div class="glass p-5">
            <span class="text-xs text-slate-400 block mb-1">کل پرسنل فعال</span>
            <div class="text-3xl font-black text-rose-400"><?php echo esc_html($total_staff); ?></div>
        </div>
    </section>

    <!-- ===================== TOP ANALYTICS CHARTS ===================== -->
    <section class="grid grid-cols-1 lg:grid-cols-5 gap-5 mb-6">
        <div class="glass p-5 lg:col-span-3">
            <h3 class="text-sm font-bold mb-3">تحلیل روند ثبت اسناد خبری سیستم</h3>
            <div id="trendChart"></div>
        </div>
        <div class="glass p-5 lg:col-span-2">
            <h3 class="text-sm font-bold mb-3">توزیع کلان موضوعات</h3>
            <div id="topicDonut"></div>
        </div>
    </section>

    <!-- ===================== DEPARTMENTS PERFORMANCE ===================== -->
    <section class="mb-6">
        <h3 class="text-base font-extrabold mb-4">سنجش عملکرد و بازدهی ادارات تابعه سحاب</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            <?php if (!empty($departments)): foreach ($departments as $m_id => $dept): ?>
                <div class="glass p-4 border-t-4 border-t-cyan-500">
                    <div class="text-sm font-black mb-1"><?php echo esc_html($dept['name']); ?></div>
                    <div class="text-xs text-slate-400 mb-3">سرپرست اداره</div>
                    <div class="flex justify-between text-xs mt-2">
                        <span>کل خروجی اداره:</span>
                        <span class="font-bold text-cyan-400"><?php echo esc_html($dept['total_news']); ?> خبر</span>
                    </div>
                </div>
            <?php endforeach; else: ?>
                <div class="glass p-4 col-span-4 text-center text-slate-400">هیچ اداره‌ای یافت نشد. برای کارشناسان، مدیر مستقیم تعیین کنید.</div>
            <?php endif; ?>
        </div>
    </section>

    <!-- ===================== ANALYSTS COMPARATIVE TABLE ===================== -->
    <section class="glass-strong p-5 md:p-6 mb-6">
        <h3 class="text-base font-extrabold mb-4">جدول کارنامه مقایسه‌ای و پایش فعالیت کارشناسان</h3>
        <div class="overflow-x-auto">
            <table>
                <thead>
                    <tr>
                        <th>ردیف</th>
                        <th>نام کارشناس</th>
                        <th>تعداد اخبار</th>
                        <th>ساختار پی‌نوشت‌ها</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($analysts)): $i = 1; foreach ($analysts as $a_id => $analyst): ?>
                        <tr>
                            <td class="font-mono text-slate-400"><?php echo esc_html($i++); ?></td>
                            <td class="font-bold"><?php echo esc_html($analyst['name']); ?></td>
                            <td class="font-mono font-bold text-cyan-400"><?php echo esc_html($analyst['news_count']); ?></td>
                            <td>
                                <div class="flex gap-2 text-xs">
                                    <span class="badge badge-molaheze"><?php echo esc_html($analyst['footnotes']['molaheze']); ?> ملاحظه</span>
                                    <span class="badge badge-nazarie"><?php echo esc_html($analyst['footnotes']['nazarie']); ?> نظریه</span>
                                    <span class="badge badge-baznevisi"><?php echo esc_html($analyst['footnotes']['baznevisi']); ?> بازنویسی</span>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td colSpan="4" class="text-center py-6 text-slate-400">هیچ کارشناسی یافت نشد. کاربران با نقش نویسنده بسازید.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

</div>

<script>
    function toggleTheme() {
        document.body.classList.toggle('dark');
    }

    document.addEventListener('DOMContentLoaded', function() {
        var trendOptions = {
            chart: { type: 'area', height: 280, toolbar: {show:false}, foreColor: '#94a3b8' },
            series: [{ name: 'اسناد ثبت شده', data: [31, 40, 28, 51, 42, 109, 100] }],
            xaxis: { categories: ['۱ تیر','۵ تیر','۱۰ تیر','۱۵ تیر','۲۰ تیر','۲۵ تیر','۳۰ تیر'] },
            colors: ['#06b6d4'],
            stroke: { curve: 'smooth' }
        };
        new ApexCharts(document.querySelector("#trendChart"), trendOptions).render();

        var topicOptions = {
            chart: { type: 'donut', height: 280, foreColor: '#94a3b8' },
            series: [44, 55, 41, 17],
            labels: ['سیاسی', 'سایبری', 'بین‌الملل', 'موسسات'],
            colors: ['#6366f1', '#06b6d4', '#10b981', '#f59e0b']
        };
        new ApexCharts(document.querySelector("#topicDonut"), topicOptions).render();
    });
</script>
</body>
</html>