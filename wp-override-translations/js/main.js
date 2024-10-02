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
    row += '    <td class="td_textarea">';
    row += '        <textarea style="width:100%;" rows="3" name="wp_override_translations_options_lines[descriptions][]"></textarea>';
    row += '    </td>';
    row += '</tr>';

    jQuery('#rowsTranslations').append(row);
}