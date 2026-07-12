jQuery(document).ready(function($) {
    var selectedDeleteId = null;

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

    $('#sahab-main-dashboard').on('click', '.sahab-btn-delete', function() {
        selectedDeleteId = $(this).data('id');
        $('#sahab-delete-confirm-input').val('').trigger('input');
        $('#sahab-delete-modal').fadeIn(200);
    });

    $('#sahab-modal-cancel-btn').on('click', function() {
        $('#sahab-delete-modal').fadeOut(200);
    });

    $('#sahab-delete-confirm-input').on('input', function() {
        var value = $(this).val();
        $('#sahab-modal-confirm-btn').prop('disabled', value !== 'delete');
    });

    $('#sahab-modal-confirm-btn').on('click', function() {
        if (!selectedDeleteId) {
            return;
        }

        $.ajax({
            url: sahab_dashboard_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'sahab_delete_dashboard_post',
                post_id: selectedDeleteId
            },
            success: function(response) {
                if (response.success) {
                    $('#sahab-delete-modal').fadeOut(200);
                    $('#sahab-main-dashboard').DataTable().ajax.reload(null, false);
                } else {
                    alert(response.data.message);
                }
            },
            error: function() {
                alert('خطا در ارتباط با سرور محلی.');
            }
        });
    });
});