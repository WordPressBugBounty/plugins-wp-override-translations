jQuery(document).ready(function ($) {
    $('.deleteTranslateAction').on('click', function (e) {
        var rowID = $(this).attr('id') + '_translate';
        $('#' + rowID).remove();
    });
});

function addRowTranslate() {

    var row = '';

    row += '<tr valign="top">';
    row += '    <td>';
    row += '        <input type="text" style="width:100%;" name="wp_override_translations_options_lines[original][]" />';
    row += '    </td>';
    row += '    <td>';
    row += '        <input type="text" style="width:100%;" name="wp_override_translations_options_lines[overwrite][]" />';
    row += '    </td>';
    row += '    <td style="text-align: center;">';
    row += '        <input type="checkbox" name="wp_override_translations_options_lines[js_enabled][]" value="1" />';
    row += '    </td>';
    row += '    <td>';
    row += '        <input type="text" style="width:100%;" name="wp_override_translations_options_lines[css_selector][]" placeholder="e.g. #booking_date_from, .my-class" />';
    row += '    </td>';
    row += '</tr>';

    jQuery('#rowsTranslations').append(row);
}