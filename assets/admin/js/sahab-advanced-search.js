document.addEventListener("DOMContentLoaded", function() {
    if (typeof jalaliDatepicker !== 'undefined' && typeof jalaliDatepicker.startWatch === 'function') {
        jalaliDatepicker.startWatch({
            selector: ".sahab-pdate",
            dateFormat: "yyyy/mm/dd",
            autoShow: true,
            autoHide: true
        });
    }

    // فیلتر زنده دسته‌بندی‌ها
    var catSearch = document.getElementById('cat-search');
    if (catSearch) {
        catSearch.addEventListener('input', function(e) {
            var filter = e.target.value.trim().toLowerCase();
            var labels = document.querySelectorAll('.sahab-cat-box label');
            labels.forEach(function(label) {
                var text = label.textContent || label.innerText;
                if (text.toLowerCase().indexOf(filter) > -1) {
                    label.style.display = 'block';
                } else {
                    label.style.display = 'none';
                }
            });
        });
    }

    // فیلتر زنده برچسب‌ها
    var tagSearch = document.getElementById('tag-search');
    if (tagSearch) {
        tagSearch.addEventListener('input', function(e) {
            var filter = e.target.value.trim().toLowerCase();
            var labels = document.querySelectorAll('.sahab-tag-box label');
            labels.forEach(function(label) {
                var text = label.textContent || label.innerText;
                if (text.toLowerCase().indexOf(filter) > -1) {
                    label.style.display = 'block';
                } else {
                    label.style.display = 'none';
                }
            });
        });
    }

    // انتخاب همگانی چک‌باکس‌ها
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

    // منطق گیت امنیتی و تزریق سیگنال نمایش کل دیتابیس
    var form = document.querySelector('.sahab-adv-search-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            var keyword = form.querySelector('input[name="as_s"]').value.trim();
            var startReg = form.querySelector('input[name="start_reg"]').value.trim();
            var endReg = form.querySelector('input[name="end_reg"]').value.trim();
            var startEvent = form.querySelector('input[name="start_event"]').value.trim();
            var endEvent = form.querySelector('input[name="end_event"]').value.trim();
            var author = form.querySelector('select[name="as_author"]').value;
            
            var hasCats = form.querySelectorAll('input[name="as_cats[]"]:checked').length > 0;
            var hasTags = form.querySelectorAll('input[name="as_tags[]"]:checked').length > 0;

            if (!keyword && !startReg && !endReg && !startEvent && !endEvent && !author && !hasCats && !hasTags) {
                var confirmLoadAll = confirm("⚠️ هشدار امنیتی سحاب:\n\nشما هیچ فیلتری را برای جستجو تنظیم نکرده‌اید!\nدر صورت تایید، تمام محتویات پایگاه داده به صورت یک‌جا رندر خواهد شد که ممکن است فشار شدیدی به سرور وارد کند.\n\nآیا مایلید کل پایگاه داده را در قالب جدول مشاهده کنید؟");
                
                if (confirmLoadAll) {
                    document.getElementById('sahab-load-all').value = '1';
                } else {
                    e.preventDefault();
                }
            } else {
                document.getElementById('sahab-load-all').value = '0';
            }
        });
    }
});

// تابع مرتب‌سازی هوشمند فرانت‌آند سحاب
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
