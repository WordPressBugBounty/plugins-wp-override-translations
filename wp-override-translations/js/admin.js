document.addEventListener('DOMContentLoaded', function () {
    var tbody = document.getElementById('wot-rows');
    var template = document.getElementById('wot-row-template');
    var counter = document.getElementById('wot-count');
    var addButton = document.getElementById('wot-add-row');

    if (!tbody || !template) {
        return;
    }

    // Row indexes only have to be unique, so a counter that never goes back is enough
    var nextIndex = tbody.rows.length;

    // Count the rows that have an original text
    function updateCounter() {
        var count = 0;
        tbody.querySelectorAll('.wot-original').forEach(function (input) {
            if (input.value.trim() !== '') {
                count++;
            }
        });
        counter.textContent = count;
    }

    tbody.addEventListener('click', function (e) {
        var button = e.target.closest('.wot-delete-btn');
        if (button) {
            button.closest('tr').remove();
            updateCounter();
        }
    });

    tbody.addEventListener('input', updateCounter);

    addButton.addEventListener('click', function () {
        tbody.insertAdjacentHTML('beforeend', template.innerHTML.replace(/__INDEX__/g, nextIndex++));
        tbody.lastElementChild.querySelector('input').focus();
    });
});
