jQuery(document).ready(function($) {
    if ($('#sahab-main-dashboard').length) {
        $('#sahab-main-dashboard').DataTable({
            "ajax": {
                "url": sahab_dashboard_vars.ajax_url,
                "type": "POST",
                "data": {
                    "action": "sahab_get_dashboard_data"
                }
            },
            "columns": [
                { "data": "automation_id" },
                { 
                    "data": "title",
                    "render": function(data, type, row) {
                        return '<a href="' + row.permalink + '" target="_blank" style="font-weight:bold; color:#0f172a; text-decoration:none;">' + data + '</a>';
                    }
                },
                { "data": "case" },
                { "data": "subject" },
                { "data": "evaluation" },
                { "data": "expert" },
                { "data": "creator" },
                { "data": "event_date" },
                { "data": "publish_date" },
                { 
                    "data": "comments_count_summary",
                    "render": function(data, type, row) {
                        if (!data) return '---';
                        
                        // حل مشکل لینک undefined با استفاده از ساختار دقیق دیتای برگشتی
                        var commentUrl = row.permalink + '#comments';
                        
                        var noteHtml = '<a href="' + commentUrl + '" target="_blank" class="sahab-comment-link-badge"><span class="sahab-comment-badge note" title="ملاحظات: ' + data.note + ' عدد">' + data.note + '</span></a>';
                        var theoryHtml = '<a href="' + commentUrl + '" target="_blank" class="sahab-comment-link-badge"><span class="sahab-comment-badge theory" title="نظریه‌ها: ' + data.theory + ' عدد">' + data.theory + '</span></a>';
                        var rewriteHtml = '<a href="' + commentUrl + '" target="_blank" class="sahab-comment-link-badge"><span class="sahab-comment-badge rewrite" title="بازنویسی‌ها: ' + data.rewrite + ' عدد">' + data.rewrite + '</span></a>';
                        var miscHtml = '<a href="' + commentUrl + '" target="_blank" class="sahab-comment-link-badge"><span class="sahab-comment-badge misc" title="سایر پی‌نوشت‌ها: ' + data.misc + ' عدد">' + data.misc + '</span></a>';
                        
                        return '<div class="sahab-comment-grid-wrapper">' + noteHtml + theoryHtml + rewriteHtml + miscHtml + '</div>';
                    }
                },
                { "data": "actions", "orderable": false }
            ],
            "order": [[0, "desc"]],
            "pageLength": 10,
            "language": {
                "processing": "در حال پردازش...",
                "lengthMenu": "نمایش _MENU_ خبر",
                "zeroRecords": "هیچ خبری یافت نشد",
                "info": "نمایش _START_ تا _END_ از مجموع _TOTAL_ خبر",
                "infoEmpty": "نمایش 0 تا 0 از 0 خبر",
                "infoFiltered": "(فیلتر شده از _MAX_ خبر)",
                "search": "جستجوی زنده:",
                "paginate": {
                    "first": "ابتدا",
                    "previous": "قبلی",
                    "next": "بعدی",
                    "last": "انتها"
                }
            }
        });
    }
});