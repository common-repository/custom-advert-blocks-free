<?PHP
wp_enqueue_script("jquery-ui-timepicker", plugins_url('/js/jquery.ui.timepicker.js', __FILE__), array('jquery', 'jquery-ui-core'));
wp_enqueue_style('jquery-ui-timepicker-css', plugins_url('/css/jquery.ui.timepicker.css', __FILE__));
global $wpdb;
$name = '';
if (!$new) {
    $name = $wpdb->get_var('SELECT name from ' . $wpdb->prefix . 'custom_block_template WHERE id=' . $id);
}
?>

<div class="wrap">
    <h2><?= __('Targeting Templates', 'custom-blocks-free'); ?></h2>
    <form method="post">
        <input placeholder="<?= __('Targeting Template Title', 'custom-blocks-free'); ?>" name="title" type="text" value="<?= $name; ?>" required="" style="width: 100%; height: 35px; font-size: 23px; margin-bottom: 20px; margin-top: 10px;">
        <div class="post_category">
            <table class="post_options_geo js_geo">
                <tbody>
                    <tr>
                        <td class="table_options_geo_name"><input type="checkbox" name="geo" value="1" <?= ($info['geo']) ? 'checked' : ''; ?>>  <?= __('Geo Targeting', 'custom-blocks-free'); ?> </td>
                        <td class="table_options_geo_center_text"><?= __('Country', 'custom-blocks-free'); ?></td>
                        <td class="table_options_geo_center_text"><?= __('City', 'custom-blocks-free'); ?></td>
                    </tr>
                    <tr>
                        <td style="border-bottom: 1px solid #000000;"><?= __('Allow:', 'custom-blocks-free'); ?></td>
                        <td class="table_border_add block_chos block_geos">
                            <select data-placeholder="<?= __('Choose a country...', 'custom-blocks-free'); ?>" name="geo_country_access[]"  id="country_access<?= $id; ?>" class="chosen-select js_country_access" multiple="" style="width: 100%;">
                                <?PHP $this->list_template_selected($id, 'geo_country_access'); ?>
                            </select>
                        </td>
                        <td class="table_border_add block_chos block_geos">
                            <select data-placeholder="<?= __('Choose a city...', 'custom-blocks-free'); ?>" name="geo_city_access[]"  class="chosen-select js_city_access" id="city_access<?= $id; ?>" multiple="" style="width: 100%;">
                                <?PHP $this->list_template_selected($id, 'geo_city_access'); ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Запретить:</td>
                        <td class="table_border_add block_chos block_geos">
                            <select data-placeholder="<?= __('Choose a country...', 'custom-blocks-free'); ?>" name="geo_country_ban[]"  class="chosen-select js_country_block" id="country_block<?= $id; ?>" multiple="" style="width: 100%;">
                                <?PHP $this->list_template_selected($id, 'geo_country_ban'); ?>
                            </select>
                        </td>
                        <td class="table_border_add block_chos block_geos">
                            <select data-placeholder="<?= __('Choose a city...', 'custom-blocks-free'); ?>" name="geo_city_ban[]"  class="chosen-select js_city_block" id="city_block<?= $id; ?>" multiple="" style="width: 100%;">
                                <?PHP $this->list_template_selected($id, 'geo_city_ban'); ?>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>
            <table class="post_options_geo js_cont">
                <tbody>
                    <tr>
                        <td class="table_options_geo_name"><input type="checkbox" name="content" value="1" <?= ($info['content']) ? 'checked' : ''; ?>><?= __('Targeting by Content', 'custom-blocks-free'); ?></td>
                        <td class="table_options_geo_center_text"><?= __('Category', 'custom-blocks-free'); ?></td>
                        <td class="table_options_geo_center_text"><?= __('Post', 'custom-blocks-free'); ?></td>
                    </tr>
                    <tr>
                        <td style="border-bottom: 1px solid #000000;"><?= __('Allow:', 'custom-blocks-free'); ?></td>
                        <td class="table_border_add block_chos block_post">
                            <select data-placeholder="<?= __('Choose a category...', 'custom-blocks-free'); ?>" name="content_by_category[]" id="cat_access<?= $id; ?>" class="chosen-select" multiple="" style="width: 100%;">
                                <?PHP $this->list_template_selected($id, 'content_by_category'); ?>
                            </select>
                            <a class="special_add_by_url spec_cat button-secondary" href="#"><?= __('URL', 'custom-blocks-free'); ?></a>
                        </td>
                        <td class="table_border_add block_chos block_post">
                            <select data-placeholder="<?= __('Choose a post...', 'custom-blocks-free'); ?>" name="content_by_post[]" id="post_access<?= $id; ?>" class="chosen-select" multiple="" style="width: 100%;">
                                <?PHP $this->list_template_selected($id, 'content_by_post'); ?>
                            </select>
                            <a class="special_add_by_url spec_record button-secondary" href="#"><?= __('URL', 'custom-blocks-free'); ?></a>
                        </td>
                    </tr>
                    <tr>
                        <td>Запретить:</td>
                        <td class="table_border_add block_chos block_post">
                            <select data-placeholder="<?= __('Choose a category...', 'custom-blocks-free'); ?>" name="content_by_category_ban[]" id="cat_block<?= $id; ?>" class="chosen-select" multiple="" style="width: 100%;">   
                                <?PHP $this->list_template_selected($id, 'content_by_category_ban'); ?>
                            </select>
                            <a class="special_add_by_url spec_cat button-secondary" href="#">URL</a>
                        </td>
                        <td class="table_border_add block_chos block_post">
                            <select data-placeholder="<?= __('Choose a post...', 'custom-blocks-free'); ?>" name="content_by_post_ban[]" id="post_block<?= $id; ?>" class="chosen-select" multiple="" style="width: 100%;">
                                <?PHP $this->list_template_selected($id, 'content_by_post_ban'); ?>
                            </select>
                            <a class="special_add_by_url spec_record button-secondary" href="#"><?= __('URL', 'custom-blocks-free'); ?></a>
                        </td>
                    </tr>
                </tbody>
            </table>
            <table class="time_targeting_settings">
                <tbody>
                    <tr>
                        <td class="table_options_geo_name"><input type="checkbox" name="time" value="1" <?= ($info['time']) ? 'checked' : ''; ?>><?= __('Targeting by Time', 'custom-blocks-free'); ?></td>
                    </tr>
                    <tr>
                        <td style="width:200px;"><i><?= __('Time', 'custom-blocks-free'); ?></i></td>
                        <td style="width:250px;"><i><?= __('Dates', 'custom-blocks-free'); ?></i></td>
                    </tr>
                    <tr>
                        <td><input class="time_start_timetargeting" data-type="time_start_timetargeting"  type="text" name="time_start_timetargeting" value="<?= ($info['time_start_timetargeting']) ? $info['time_start_timetargeting'] : ''; ?>" > - <input class="time_end_timetargeting" type="text"  name="time_end_timetargeting" value="<?= ($info['time_end_timetargeting']) ? $info['time_end_timetargeting'] : ''; ?>"></td>
                        <td><input class="start_date_timetargeting" data-type="start_date_timetargeting"  type="text" name="start_date_timetargeting" value="<?= ($info['start_date_timetargeting']) ? $info['start_date_timetargeting'] : ''; ?>" > - <input class="end_date_timetargeting" type="text"  name="end_date_timetargeting" value="<?= ($info['end_date_timetargeting']) ? $info['end_date_timetargeting'] : ''; ?>"></td>
                        <td><input class="only_work_time"  data-type="only_work_time" type="checkbox" name="only_work_time" value="1" <?= ($info['only_work_time']) ? 'checked' : ''; ?>><?= __('Weekdays only', 'custom-blocks-free'); ?></td>
                    </tr>
                    <tr>
                        <td><input class="all_hours"  data-type="all_hours" type="checkbox" name="all_hours" value="1" <?= ($info['all_hours']) ? 'checked' : ''; ?>><?= __('24/7', 'custom-blocks-free'); ?></td>
                        <td></td>
                        <td><input class="only_holiday_time"  data-type="only_holiday_time" type="checkbox" name="only_holiday_time" value="1" <?= ($info['only_holiday_time']) ? 'checked' : ''; ?>><?= __('Weekend only', 'custom-blocks-free'); ?></td>
                    </tr>
                </tbody>
            </table>
            <input type="submit" class="button-primary" name="spec_filter_template" value="<?= ($new) ? __('Create', 'custom-blocks-free') : __('Save', 'custom-blocks-free'); ?>">
        </div>
    </form>
</div>

<script>
    jQuery(document).ready(function ($) {
        $('.chosen-select').chosen();
        var check_res = $('#type_sync').prop('checked');
        if (check_res) {
            $('#tabs ul li a input').each(function () {
                $(this).removeAttr("checked");
                $("#tabs").tabs("load", 1);
                $("#tabs").tabs("disable");

            });
        }

        $('.time_start_timetargeting').timepicker({
            showPeriodLabels: false,
            hourText: '<?= __('Hours', 'custom-blocks-free'); ?>',
            minuteText: '<?= __('Minutes', 'custom-blocks-free'); ?>',
            timeSeparator: ':',
            nowButtonText: '<?= __('Now', 'custom-blocks-free'); ?>',
            showNowButton: true,
            closeButtonText: '<?= __('Close', 'custom-blocks-free'); ?>',
            showCloseButton: true,
            deselectButtonText: '<?= __('Cancel', 'custom-blocks-free'); ?>',
            showDeselectButton: true
        });
        $('.time_end_timetargeting').timepicker({
            showPeriodLabels: false,
            hourText: '<?= __('Hours', 'custom-blocks-free'); ?>',
            minuteText: '<?= __('Minutes', 'custom-blocks-free'); ?>',
            timeSeparator: ':',
            nowButtonText: '<?= __('Now', 'custom-blocks-free'); ?>',
            showNowButton: true,
            closeButtonText: '<?= __('Close', 'custom-blocks-free'); ?>',
            showCloseButton: true,
            deselectButtonText: '<?= __('Cancel', 'custom-blocks-free'); ?>',
            showDeselectButton: true
        });
        $('.end_date_timetargeting').datepicker({
            changeMonth: true,
            changeYear: true
        });
        $('.start_date_timetargeting').datepicker({
            changeMonth: true,
            changeYear: true
        });
        $.datepicker.regional['ru'] = {
            closeText: '<?= __('Close', 'custom-blocks-free'); ?>',
            prevText: '&#x3c;<?= __('Prev', 'custom-blocks-free'); ?>',
            nextText: '<?= __('Next', 'custom-blocks-free'); ?>&#x3e;',
            currentText: '<?= __('Today', 'custom-blocks-free'); ?>',
            monthNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
                'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
            monthNamesShort: ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн',
                'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'],
            dayNames: ['воскресенье', 'понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота'],
            dayNamesShort: ['вск', 'пнд', 'втр', 'срд', 'чтв', 'птн', 'сбт'],
            dayNamesMin: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
            dateFormat: 'dd.mm.yy',
            firstDay: 1,
            isRTL: false
        };
        $.datepicker.setDefaults($.datepicker.regional['ru']);


        $('.time_start_timetargeting').timepicker({
            showPeriodLabels: false,
            hourText: '<?= __('Hours', 'custom-blocks-free'); ?>',
            minuteText: '<?= __('Minutes', 'custom-blocks-free'); ?>',
            timeSeparator: ':',
            nowButtonText: '<?= __('Now', 'custom-blocks-free'); ?>',
            showNowButton: true,
            closeButtonText: '<?= __('Close', 'custom-blocks-free'); ?>',
            showCloseButton: true,
            deselectButtonText: '<?= __('Cancel', 'custom-blocks-free'); ?>',
            showDeselectButton: true
        });
        $('.time_end_timetargeting').timepicker({
            showPeriodLabels: false,
            hourText: '<?= __('Hours', 'custom-blocks-free'); ?>',
            minuteText: '<?= __('Minutes', 'custom-blocks-free'); ?>',
            timeSeparator: ':',
            nowButtonText: '<?= __('Now', 'custom-blocks-free'); ?>',
            showNowButton: true,
            closeButtonText: '<?= __('Close', 'custom-blocks-free'); ?>',
            showCloseButton: true,
            deselectButtonText: '<?= __('Cancel', 'custom-blocks-free'); ?>',
            showDeselectButton: true
        });
        $('.end_date_timetargeting').datepicker({
            changeMonth: true,
            changeYear: true
        });
        $('.start_date_timetargeting').datepicker({
            changeMonth: true,
            changeYear: true
        });


        $('.only_holiday_time').click(function () {
            if ($(this).is(":checked"))
            {
                $('.only_work_time').removeAttr("checked");
            }
        });
        $('.only_work_time').click(function () {
            if ($(this).is(":checked"))
            {
                $('.only_holiday_time').removeAttr("checked");
            }
        });
    });
</script>