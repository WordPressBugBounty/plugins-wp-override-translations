document.addEventListener('DOMContentLoaded', function () {
    var tbody = document.getElementById('rowsTranslations');
    var counter = document.getElementById('wot-count');
    var optionName = wpOverrideTranslations.optionName;

    // Update the counter badge
    function updateCounter() {
        if (!counter) return;
        var rows = tbody.querySelectorAll('tr');
        // Count rows that have a value in the original input
        var count = 0;
        rows.forEach(function (row) {
            var input = row.querySelector('input[name="' + optionName + '[original][]"]');
            if (input && input.value.trim() !== '') {
                count++;
            }
        });
        counter.textContent = count;
    }

    // Handle delete translation action (event delegation)
    tbody.addEventListener('click', function (e) {
        var target = e.target;
        if (target.classList.contains('deleteTranslateAction')) {
            var row = target.closest('tr');
            if (row) {
                row.remove();
                updateCounter();
            }
        }
    });

    // Handle add new translation button
    var addBtn = document.getElementById('add-new-translation');
    if (addBtn) {
        addBtn.addEventListener('click', function (e) {
            e.preventDefault();
            addRowTranslate();
        });
    }

    function addRowTranslate() {
        var row = document.createElement('tr');

        row.innerHTML =
            '<td>' +
            '    <input type="text" name="' + optionName + '[original][]" />' +
            '</td>' +
            '<td>' +
            '    <input type="text" name="' + optionName + '[overwrite][]" />' +
            '</td>' +
            '<td class="column-js">' +
            '    <input type="checkbox" name="' + optionName + '[js_enabled][]" value="1" />' +
            '</td>' +
            '<td>' +
            '    <input type="text" name="' + optionName + '[css_selector][]" placeholder="e.g. #booking_date_from, .my-class" />' +
            '</td>' +
            '<td class="column-actions">' +
            '    <span class="dashicons dashicons-no wot-delete-btn deleteTranslateAction"></span>' +
            '</td>';

        tbody.appendChild(row);
    }
});
