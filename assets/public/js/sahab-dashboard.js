jQuery(document).ready(function($) {
	var $table = $('#sahab-main-dashboard');

	if (!$table.length) {
		return;
	}

	$table.DataTable({
		processing: true,
		serverSide: false,
		ajax: {
			url: sahab_dashboard_vars.ajax_url,
			type: 'POST',
			data: {
				action: 'sahab_get_dashboard_data'
			}
		},
		columns: [
			{ data: 'automation_id' },
			{ data: 'title' },
			{ data: 'case' },
			{ data: 'subject' },
			{ data: 'evaluation' },
			{ data: 'expert' },
			{ data: 'creator' },
			{ data: 'event_date' },
			{ data: 'publish_date' },
			{ data: 'comments_count_summary' },
			{ data: 'actions' }
		],
		language: {
			url: '',
			processing: 'در حال پردازش...',
			lengthMenu: 'نمایش _MENU_ خبر در هر صفحه',
			zeroRecords: 'هیچ خبری با این مشخصات یافت نشد',
			info: 'نمایش _START_ تا _END_ از مجموع _TOTAL_ خبر',
			infoEmpty: 'نمایش 0 تا 0 از 0 خبر',
			infoFiltered: '(فیلتر شده از مجموع _MAX_ خبر)',
			search: 'جستجوی زنده:',
			paginate: {
				first: 'ابتدا',
				previous: 'قبلی',
				next: 'بعدی',
				last: 'انتها'
			}
		},
		destroy: true,
		dom: '<"top"lf>rt<"bottom"ip><"clear">',
		reorder: true,
		responsive: true
	});
});
