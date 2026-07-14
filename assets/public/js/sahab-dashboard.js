jQuery(document).ready(function($) {
    var selectedDeleteId = null;

    $.fn.dataTable.ext.errMode = 'none';

    if ($('#sahab-main-dashboard').length) {
        var dashboardTable = $('#sahab-main-dashboard').DataTable({
            "ajax": {
                "url": sahab_dashboard_vars.ajax_url,
                "type": "POST",
                "data": function(d) {
                    return $.extend({}, d, {
                        action: 'sahab_get_dashboard_data',
                        f_id: $('#filter_id').val(),
                        f_case: $('#filter_case').val(),
                        f_type: $('#filter_type').val(),
                        f_subject: $('#filter_subject').val(),
                        f_expert: $('#filter_expert').val(),
                        f_author: $('#filter_author').val(),
                        f_notes: $('#filter_notes').val()
                    });
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
                { 
                    "data": "news_type",
                    "render": function(data, type, row) {
                        // Allow rendering of the HTML link tags returned by the server
                        return data;
                    }
                },
                { "data": "evaluation" },
                { "data": "expert" },
                { "data": "creator" },
                { "data": "event_date" },
                { "data": "publish_date" },
                { 
                    "data": "comments_count_summary",
                    "render": function(data, type, row) {
                        if (!data) return '---';
                        
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
                "lengthMenu": "_MENU_",
                "zeroRecords": "هیچ خبری یافت نشد",
                "info": "نمایش _START_ تا _END_ از مجموع _TOTAL_ خبر",
                "infoEmpty": "نمایش 0 تا 0 از 0 خبر",
                "infoFiltered": "(فیلتر شده از _MAX_ خبر)",
                "search": "",
                "paginate": {
                    "first": "ابتدا",
                    "previous": "قبلی",
                    "next": "بعدی",
                    "last": "انتها"
                }
            },
            "initComplete": function(settings, json) {
                var api = this.api();
                var lengthSelect = $('#sahab-main-dashboard_length select').appendTo('#sahab_custom_length');
                var searchInput = $('#sahab-main-dashboard_filter input').appendTo('#sahab_custom_search');
                $('#sahab-main-dashboard_length, #sahab-main-dashboard_filter').remove();

                lengthSelect.css({
                    'margin': '0',
                    'height': '30px',
                    'font-size': '11px'
                });
                searchInput.attr('placeholder', 'جستجو...').css({
                    'margin': '0',
                    'height': '30px',
                    'font-size': '11px',
                    'padding': '4px'
                });

                // Enforce tight spacing and widths for a clean single-row filter bar
                $('#sahab-dashboard-filters').css({
                    'flex-wrap': 'nowrap',
                    'justify-content': 'space-between'
                });
                $('#sahab-dashboard-filters #sahab_custom_search').css('flex', '0 0 110px');
                $('#sahab-dashboard-filters #sahab_custom_search input').css('width', '100px');
                $('#sahab-dashboard-filters select, #sahab-dashboard-filters input').css('height', '30px');

                $('#clear_all_filters').on('click', function(e) {
                    e.preventDefault();
                    var cleanUrl = window.location.protocol + '//' + window.location.host + window.location.pathname;
                    window.location.href = cleanUrl;
                });
            }
        });
    }

    $('#sahab-main-dashboard').on('click', '.sahab-btn-delete', function() {
        selectedDeleteId = $(this).data('id');
        $('#sahab-delete-confirm-input').val('').trigger('input');
        $('#sahab-delete-modal-error').hide().text('');
        $('#sahab-delete-modal').fadeIn(200);
    });

    $('#sahab-modal-cancel-btn').on('click', function() {
        $('#sahab-delete-modal').fadeOut(200);
    });

    $('#sahab-delete-confirm-input').on('input', function() {
        var value = $(this).val();
        $('#sahab-delete-modal-error').hide().text('');
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
                    $('#sahab-delete-modal-error').text(response.data.message).fadeIn(200);
                }
            },
            error: function(xhr) {
                if (xhr.status === 403 && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                    $('#sahab-delete-modal-error').text(xhr.responseJSON.data.message).fadeIn(200);
                } else {
                    $('#sahab-delete-modal-error').text('خطا در ارتباط با سرور محلی.').fadeIn(200);
                }
            }
        });
    });
});
