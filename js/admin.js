// Сортирует SELECT по тексту
function sortSelect(selElem) {
    var tmpAry = new Array();
    for (var i = 1; i < selElem.options.length; i++) {
        tmpAry[i - 1] = new Array();
        tmpAry[i - 1][0] = selElem.options[i].text;
        tmpAry[i - 1][1] = selElem.options[i].value;
    }
    tmpAry.sort();
    while (selElem.options.length > 1) {
        selElem.options[1] = null;
    }

    for (var i = 0; i < tmpAry.length; i++) {
        var op = new Option(tmpAry[i][0], tmpAry[i][1]);
        selElem.options[i + 1] = op;
    }
    return;
}

function addCode(params) {
    var $ = jQuery;
    var ch_val = 0 + 1 + 1 + 0 + 6 - 6;
    if ($('.codes .code').length < ch_val)
    {
        var code = $("#code-template").tmpl({
            id: params['id'],
            type_id: params['type_id'],
            title: params['title'],
            published: params['published'],
            posts: params['posts'],
            posts_ban: params['posts_ban'],
            categories: params['categories'],
            categories_ban: params['categories_ban'],
            country_ban: params['country_ban'],
            country_access: params['country_access'],
            city_ban: params['city_ban'],
            city_access: params['city_access'],
            html: params['html'],
            geotargeting: false,
            content_filter: params['content_filter'],
            time_targeting: params['time_targeting'],
            decor: false,
            templates_element: false,
            templates: false,
            tax_allow: params['tax_allow'],
            tax_ban: params['tax_ban'],
            term_allow: params['term_allow'],
            term_ban: params['term_ban'],
            subhead: params['subhead'],
        });

        $(code).find('.remove-code').click(function () {
            if (confirm(local.deletecode)) {
                var id = $(this).attr('rel');
                removeCode(id);
            }
            return false;
        });
        $(code).find('.show-options').click(function () {

            var id = $(this).attr('rel');
            toggleOptions(id);
            return false;
        });
        var textarea = code.find('textarea');
        textarea.undoredo();
        textarea.on('undoredo', function () {
            $(code).find('.undo').css("opacity", $(this).data('undostack').length > 1 ? "1" : "0.2");
            $(code).find('.redo').css("opacity", $(this).data('redostack').length > 0 ? "1" : "0.2");
        });
        code.find('.undo').click(function () {
            textarea.undoredo('undo');
            return false;
        });
        code.find('.redo').click(function () {
            textarea.undoredo('redo');
            return false;
        });
        var tab = $("#tab" + params['type_id'] + " .codes");
        if (tab.find('.code').length == 0) {
            tab.html('');
        }

        tab.append(code);
        var num = parseInt($(".tab" + params['type_id'] + "-a span").html());
        $(".tab" + params['type_id'] + "-a span").html(num + 1);


        $('.chosen-select').chosen();
    } else {
        alert(local.two_block);
    }

}
function appendToChosen(id, value, target) {
    var $ = jQuery;
    var need_add = true;
    $(target + ' option').each(function () {
        if ($(this).val() == id)
        {
            need_add = false;
        }
    });
    if (need_add)
    {
        $(target)
                .append($('<option></option>')
                        .val(id)
                        .html(value));
    }

}

function deleteNotChose(target) {
    var $ = jQuery;
    $(target + ' option').each(function () {
        if ($(this).attr("selected") == "selected")
        {

        } else {
            $(target + " option[value='" + $(this).val() + "']").remove();
        }
    });
}

function removeCode(id) {
    var $ = jQuery;
    var code = $("#code" + id);
    var tab_number = code.parents('.tab-content').attr('rel');
    code.remove();
    var num = parseInt($(".tab" + tab_number + "-a span").html());
    $(".tab" + tab_number + "-a span").html(num - 1);
    var tab = $("#tab" + tab_number + " .codes");
    if (tab.find('.code').length == 0) {
        tab.html(local.notcode);
    }
}

toggleOptions = function (id) {
    return false;
}

codeFormSubmit = function () {
    var $ = jQuery;
    for (var i = 1; i != 2; ++i) {
        var tab = $('#tab' + i);
        // Проверяем есть ли вообще во вкладке коды
        var codes = tab.find('.code');
        for (var j = 0; j != codes.length; ++j) {
            var code = codes[j];
            var title = $(code).find('.input-title').val();
            var html = $(code).find('.input-html').val();
            if (title.length == 0 || html.length == 0) {
                alert(local.error_not_field);
                return false;
            }

        }
    }
    var count_error = 0;
    $('.place_decor .template_check').each(function () {
        if ($(this).attr('checked'))
        {
            var id_rel = $(this).data('id');
            var value_title = $('#template_title' + id_rel + ' .title_test').val();
            if (value_title == '')
            {
                count_error++;
            } else {
                $.ajax({
                    type: 'POST',
                    url: '/wp-admin/admin-ajax.php',
                    data: {
                        action: 'check_name_for_decor',
                        title: value_title
                    },
                    success: function (data) {
                        if (data.result == '0')
                        {
                            count_error++;
                        }
                    },
                    error: function (response) {
                        console.log(response);
                    },
                    dataType: 'json'
                });
            }
        }
    });
    if (count_error == 0)
    {
        return true;
    } else {
        return false;
    }
}

jQuery(function ($) {
    $('#toplevel_page_custom-blocks .wp-first-item a').html(local.name_plugin);
    $('.stat-item-state').change(function () {
        var state = $(this).is(":checked") ? 1 : 0;
        var id = $(this).val();
        $.post('/wp-admin/admin-ajax.php',
                {
                    action: 'item_state',
                    id: id,
                    published: state
                });
    });
    $('.block-collapse').click(function () {
        var id = $(this).attr('rel');
        if ($(this).text() == '[-]') {
            $('.block-' + id).hide();
            $(this).text('[+]');
        }
        else {
            $('.block-' + id).show();
            $(this).text('[-]');
        }
    });
    $('.cb-shortcoding').change(function () {
        var now_id = parseInt($(this).val());
        var block_texting = '';
        if (now_id)
        {
            block_texting = '[block id="' + now_id + '"]';
        } else {
            block_texting = local.block_insert;
        }
        $(this).parents('div.cb-shortcode-info').find('p>input').val(block_texting);
    });
    $(".cb-shortcode-info input").focus(function () {
        this.select();
    });
});

jQuery(document).ready(function ($) {
    $('body').on('click', '.remove-code', function () {
        if (confirm(local.deletecode)) {
            var id = $(this).attr('rel');
            removeCode(id);
        }
        return false;
    });
    $('.js_preview_decor').click(function () {
        $(this).parents('td').find('div.admin-block').toggle();
    });
    $('.place_decor').on('change', '.template_check', function () {
        var id_template = $(this).data('id');
        $('#template_title' + id_template).show();
    });
    $('td').on('change', '.js_rotate_radio', function () {
        if ($(this).attr('checked') == 'checked')
        {
            var id_code_data = $(this).data('codeId');
            $(".js_scale_radio[data-code-id='" + id_code_data + "'").attr('checked', false);
        }
    });
    $('td').on('change', '.js_scale_radio', function () {
        if ($(this).attr('checked') == 'checked')
        {
            var id_code_data = $(this).data('codeId');
            $(".js_rotate_radio[data-code-id='" + id_code_data + "'").attr('checked', false);
        }
    });
    $('.place_decor').on('focusout', '.title_test', function () {
        var title_send = $(this).val();
        var ob_title = $(this);
        if (title_send == '')
        {
        } else {
            $.ajax({
                type: 'POST',
                url: '/wp-admin/admin-ajax.php',
                data: {
                    action: 'check_name_for_decor',
                    title: title_send
                },
                success: function (data) {
                    if (data.result == '0')
                    {
                    }
                },
                error: function (response) {
                    console.log(response);
                },
                dataType: 'json'
            });
        }
    });

    $(document).on('click', 'a.js-add-code', function () {

        $.datepicker.regional['ru'] = {
            closeText: 'Close',
            prevText: '&#x3c;Prev',
            nextText: 'Next&#x3e;',
            currentText: 'Today',
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
            hourText: 'Hours',
            minuteText: 'Minutes',
            timeSeparator: ':',
            nowButtonText: 'Now',
            showNowButton: true,
            closeButtonText: 'Close',
            showCloseButton: true,
            deselectButtonText: 'Cancel',
            showDeselectButton: true
        });
        $('.time_end_timetargeting').timepicker({
            showPeriodLabels: false,
            hourText: 'Hours',
            minuteText: 'Minutes',
            timeSeparator: ':',
            nowButtonText: 'Now',
            showNowButton: true,
            closeButtonText: 'Close',
            showCloseButton: true,
            deselectButtonText: 'Cancel',
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
    });

    $('input[data-type="geotargeting"]').change(function () {
        if ($(this).attr('checked') != 'checked')
        {
            var need_id_delete = $(this).data('id');
            $('#country_block' + need_id_delete).val('');
            $('.chosen-select').chosen();
        }
    });

    $(document).on('change', 'input[type="checkbox"]', function (e) {
        var info = e.target.dataset;
        var status = $(this).attr("checked");
        if (info.type == 'geotargeting') {
            if (status == 'checked') {
                $('div#code' + info.id + ' .js_geo').show(200);
            } else {
                $('div#code' + info.id + ' .js_geo').hide(200);
            }
        }

        if (info.type == 'content_filter') {
            if (status == 'checked') {
                $('div#code' + info.id + ' .js_cont').show(200);
            } else {
                $('div#code' + info.id + ' .js_cont').hide(200);
            }
        }

        if (info.type == 'time_targeting') {
            if (status == 'checked') {
                $('div#code' + info.id + ' .time_targeting_settings').show(200);
            } else {
                $('div#code' + info.id + ' .time_targeting_settings').hide(200);
            }
        }
        if (info.type == 'decor') {
            if (status == 'checked') {
                $('div#code' + info.id + ' .decor_settings').show(200);
            } else {
                $('div#code' + info.id + ' .decor_settings').hide(200);
            }
        }
        if (info.type == 'templates') {
            if (status == 'checked') {
                $('div#code' + info.id + ' .templates_settings').show(200);
            } else {
                $('div#code' + info.id + ' .templates_settings').hide(200);
            }
        }
        if (info.type == 'all_hours')
        {
            if (status == 'checked')
            {
                $('div#code' + info.id + ' .time_start_timetargeting').prop('disabled', true).val('');
                $('div#code' + info.id + ' .time_end_timetargeting').prop('disabled', true).val('');
            } else {
                $('div#code' + info.id + ' .time_start_timetargeting').prop('disabled', false);
                $('div#code' + info.id + ' .time_end_timetargeting').prop('disabled', false);
            }
        }
        if (info.type == 'only_holiday_time')
        {
            if (status == 'checked')
            {
                $('div#code' + info.id + ' .only_work_time').prop('checked', false);
            }
        }
        if (info.type == 'only_work_time')
        {
            if (status == 'checked')
            {
                $('div#code' + info.id + ' .only_holiday_time').prop('checked', false);
            }
        }
    });
});

jQuery(document).ready(function ($) {
    $('.place_decor').each(function (index) {
        var now_rel_decor = $(this).attr("rel");
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            data: {
                action: 'get_decor_template_codes',
                id_rel: now_rel_decor
            },
            success: function (data) {
                if (data.length > 0)
                {
                    if (data == 'template')
                    {
                        $.ajax({
                            type: 'POST',
                            url: ajax_object.ajax_url,
                            data: {
                                action: 'get_decor_template',
                                ids: now_rel_decor,
                            },
                            success: function (data) {
                                $(".decor_settings_select[rel='" + now_rel_decor + "']").append(data);
                                $(".place_decor[rel='" + now_rel_decor + "']").html('');
                            },
                        });
                        $('input[name="codes[' + now_rel_decor + '][decor]').prop("checked", true);
                        $('div#code' + now_rel_decor + ' .decor_settings').show();
                    } else {
                        $('.place_decor[rel="' + now_rel_decor + '"]').html(data);
                        if (typeof (decoring_js) !== "undefined")
                        {
                            if (decoring_js[now_rel_decor] == '2')
                            {
                                $('input[name="codes[' + now_rel_decor + '][decor]').prop("checked", false);
                                $('div#code' + now_rel_decor + ' .decor_settings').hide();
                            }
                        } else {
                            $('input[name="codes[' + now_rel_decor + '][decor]').prop("checked", true);
                            $('div#code' + now_rel_decor + ' .decor_settings').show();
                        }
                    }
                }
            },
            dataType: 'html'
        });
    });

    $(document).on("click", ".inputing_decor_chose", function () {
        var id_item_for_decor = $(this).attr('rel');
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            data: {
                action: 'get_decor_template',
                ids: id_item_for_decor,
            },
            success: function (data) {
                $(".decor_settings_select[rel='" + id_item_for_decor + "']").html(data);
                $(".place_decor[rel='" + id_item_for_decor + "']").html('');
            },
        });

    });

    function forEach(data, callback) {
        for (var key in data) {
            if (data.hasOwnProperty(key)) {
                callback(key, data[key]);
            }
        }
    }

    $(document).on("keydown", ".block_geos input", function () {
        var id_block = $(this).parents('div').attr('id');
        var real_id = parseInt(id_block.replace(/\D+/g, ""));
        var type_block = 0;
        // 1 - country
        // 2 - city
        var allow = 0;
        var selecting = '#';
        var country_select = '#country';
        // 1 - yes
        // 2 - no
        if (id_block.indexOf('country') + 1) {
            type_block = 1;
            selecting = selecting + 'country';
        } else if (id_block.indexOf('city') + 1) {
            type_block = 2;
            selecting = selecting + 'city';
        }
        if (id_block.indexOf('access') + 1) {
            allow = 1;
            selecting = selecting + '_access';
            country_select = country_select + '_access';
        } else if (id_block.indexOf('block') + 1) {
            allow = 2;
            selecting = selecting + '_block';
            country_select = country_select + '_block';
        }
        var div_block_chosen = $(this).parents('div.chosen-container');
        var MySelect = $(this).parents('div.block_chos').find('select');
        var search_param = $(this).val();
        div_block_chosen.find('.chosen-choices input').autocomplete({
            source: function (request, response) {
                if (search_param.length >= 3) {
                    if (type_block == 1)
                    {
                        $.ajax({
                            type: 'POST',
                            url: ajax_object.ajax_url,
                            data: {
                                search: search_param,
                                action: 'get_geo',
                                type_block: type_block
                            },
                            success: function (data) {
                                if (typeof (data) == 'object') {
                                    deleteNotChose(selecting + real_id);
                                    forEach(data, function (key, value) {
                                        appendToChosen(key, value, selecting + real_id);
                                    });
                                    $(selecting + real_id).trigger("chosen:updated");
                                }
                                ;
                            },
                            error: function (response) {
                                console.log(response);
                            },
                            dataType: 'json'
                        });
                    } else if (type_block == 2) {
                        var country_ids = new Array();
                        $(country_select + real_id + ' option').each(function () {
                            country_ids.push($(this).val());
                        });
                        $.ajax({
                            type: 'POST',
                            url: ajax_object.ajax_url,
                            data: {
                                search: search_param,
                                action: 'get_geo',
                                type_block: type_block,
                                country: country_ids
                            },
                            success: function (data) {
                                if (typeof (data) == 'object') {
                                    deleteNotChose(selecting + real_id);
                                    forEach(data, function (key, value) {
                                        appendToChosen(key, value, selecting + real_id);
                                    });
                                    $(selecting + real_id).trigger("chosen:updated");
                                }
                                ;
                            },
                            error: function (response) {
                                console.log(response);
                            },
                            dataType: 'json'
                        });
                    }
                }
            }
        });
    });



    //for post

    $(document).on("keydown", ".block_post input", function () {
        var type_block = 0;
        // 1 - category
        // 2 - post
        var allow = 0;
        var selector_head = 'select#';
        var selecting = $(this).parents('.block_post').find('select');
        var select_name = selecting.attr('id');
        var real_id = parseInt(select_name.replace(/\D+/g, ""));
        if (select_name.indexOf('cat') + 1) {
            type_block = 1;
            selector_head = selector_head + 'cat';
        } else if (select_name.indexOf('post') + 1) {
            type_block = 2;
            selector_head = selector_head + 'post';
        }
        if (select_name.indexOf('access') + 1) {
            allow = 1;
            selector_head = selector_head + '_access';
        } else if (select_name.indexOf('block') + 1) {
            allow = 2;
            selector_head = selector_head + '_block';
        }

        var div_block_chosen = $(this).parents('div.chosen-container');
        var search_param = $(this).val();
        div_block_chosen.find('.chosen-choices input').autocomplete({
            source: function (request, response) {
                if (search_param.length >= 2) {
                    if (type_block == 1)
                    {
                        $.ajax({
                            type: 'POST',
                            url: ajax_object.ajax_url,
                            data: {
                                search: search_param,
                                action: 'get_posting',
                                type_block: type_block
                            },
                            success: function (data) {
                                if (typeof (data) == 'object') {
                                    deleteNotChose(selector_head + real_id);
                                    forEach(data, function (key, value) {
                                        appendToChosen(key, value, selector_head + real_id);
                                    });
                                    $(selector_head + real_id).trigger("chosen:updated");
                                }
                                ;
                            },
                            error: function (response) {
                                console.log(response);
                            },
                            dataType: 'json'
                        });
                    } else if (type_block == 2) {
                        $.ajax({
                            type: 'POST',
                            url: ajax_object.ajax_url,
                            data: {
                                search: search_param,
                                action: 'get_posting',
                                type_block: type_block
                            },
                            success: function (data) {
                                if (typeof (data) == 'object') {
                                    deleteNotChose(selector_head + real_id);
                                    forEach(data, function (key, value) {
                                        appendToChosen(key, value, selector_head + real_id);
                                    });
                                    $(selector_head + real_id).trigger("chosen:updated");
                                }
                                ;
                            },
                            error: function (response) {
                                console.log(response);
                            },
                            dataType: 'json'
                        });

                    }
                }
            }
        });
    });

    $(document).on("click", ".js_title_code", function () {
        var id_code = $(this).parent().attr('id');
        var class_code = "other_code";
        if ($("div#" + id_code + "." + class_code).css("display") == "none") {
            $("div#" + id_code + "." + class_code).show("500");
            $("div.head_code#" + id_code + " div.js_title_code div.rectangle_close").removeClass().addClass("rectangle_open");
        } else {
            $("div#" + id_code + "." + class_code).hide("500");
            $("div.head_code#" + id_code + " div.js_title_code div.rectangle_open").removeClass().addClass("rectangle_close");
        }
    });
    $(document).on("change", ".input-title", function () {
        $(this).parents(".code").find("div.head_code div.js_title_code a").html($(this).val());
    });
    $(document).on("click", ".js-add-code", function () {
        addCode({
            id: Math.round(Math.random() * 100000) + '' + Math.round(Math.random() * 100000),
            type_id: $(this).attr('rel'),
            title: '',
            html: '',
            posts: [],
            posts_ban: [],
            categories: [],
            categories_ban: [],
            country_ban: [],
            country_access: [],
            geotargeting: false,
            content_filter: false,
            time_targeting: '',
            decor: false,
            published: true
        });
        return false;
    });
    var el_input = "";
    var el_img = "";

    window.send_to_editor = function (html) {
        img_url = jQuery("img", html).attr("src");
        el_input.val(img_url);
        el_img.attr("src", img_url);
        tb_remove();
    };

    $(document).on('change', '.templates_special_select', function () {
        var message_place = $(this).parents('table').find('.templates_error_place');

        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            data: {
                templates: $(this).val(),
                action: 'checking_groups_templates',
            },
            success: function (data) {
                console.log(data);
                switch (data.status)
                {
                    case 'success':
                        message_place.html(data.success).attr('style', 'color:green; display: block;');
                        break;
                    case 'error':
                        message_place.html(data.error).attr('style', 'color:red; display: block;');
                        break;
                }
            },
            error: function (response) {
                console.log(response);
            },
            dataType: 'json'
        });
    });

    $(document).on('click', '.button-clear-stat-block', function () {
        var thising = $(this).parents('tr');
        var codeid = $(this).data('codeId');
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            data: {
                codeid: codeid,
                action: 'clear_statistic_block',
            },
            success: function (data) {
                thising.find('.clear-show').html('0');
                thising.find('.clear-click').html('0');
                thising.find('.clear-ctr').html('0.00%');
            },
            error: function (response) {
                console.log(response);
            },
            dataType: 'json'
        });
        return false;
    });

    $(document).on('change', '.tax_type', function () {
        var taxonomies = $(this).val();
        var template_term = $(this).parents('td').find('.term_type');
        template_term.empty();
        if (taxonomies !== null)
        {
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                data: {
                    tax: taxonomies,
                    action: 'get_terms_by_taxonomy',
                },
                success: function (data) {
                    var newOption = '';
                    $.each(data, function (index, value) {
                        newOption = newOption + '<option value="' + index + '">' + value + '</option>';
                    });
                    template_term.append(newOption).trigger("chosen:updated");
                },
                error: function (response) {
                    console.log(response);
                },
                dataType: 'json'
            });
        }
    });

    $(document).on('click', '.js-close-div-spec', function () {
        $(this).parent().hide();
    });
});

                                                    