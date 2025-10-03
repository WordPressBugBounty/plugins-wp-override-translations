jQuery(document).ready(function ($) {
    // Handle delete translation action
    $('.deleteTranslateAction').on('click', function (e) {
        var rowID = $(this).attr('id') + '_translate';
        $('#' + rowID).remove();
    });

    // Handle add new translation button
    $('#add-new-translation').on('click', function(e) {
        e.preventDefault();
        addRowTranslate();
    });
});

function addRowTranslate() {
    var optionName = wpOverrideTranslations.optionName;
    var row = '';

    row += '<tr valign="top">';
    row += '    <td>';
    row += '        <input type="text" style="width:100%;" name="' + optionName + '[original][]" />';
    row += '    </td>';
    row += '    <td>';
    row += '        <input type="text" style="width:100%;" name="' + optionName + '[overwrite][]" />';
    row += '    </td>';
    row += '    <td style="text-align: center;">';
    row += '        <input type="checkbox" name="' + optionName + '[js_enabled][]" value="1" />';
    row += '    </td>';
    row += '    <td>';
    row += '        <input type="text" style="width:100%;" name="' + optionName + '[css_selector][]" placeholder="e.g. #booking_date_from, .my-class" />';
    row += '    </td>';
    row += '</tr>';

    jQuery('#rowsTranslations').append(row);
}