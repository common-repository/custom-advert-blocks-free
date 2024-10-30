var postids;
chest = new Object;
var containers = new Array();
var reveal_close=false;
var showing_modal_window=null;
var show_modal=false;

var custom_block = function (id, container_id, post)
{
    if (typeof (postids) == 'undefined')
    {
        postids = post;
    }
    if (typeof (chest[id]) == 'undefined')
    {
        chest[id] = new Object;
    }
    chest[id][container_id] = container_id;
    containers.push(container_id);
}

jQuery(document).ready(function ($) {
    var wordpress_poping = parseInt($.cookie('wordpress_poping'));
    if (wordpress_poping)
    {
        $.cookie('wordpress_poping', wordpress_poping + 1, {expires: 1, path: '/'});
    } else {
        $.cookie('wordpress_poping', '1', {expires: 1, path: '/'});
    }

    function blocked_modal() {
        if ($('.reveal-modal>p').length > 0) {
            $.each($('.reveal-modal'), function (ind, ind_val) {
                if ($(this).find('p').html().length<1)
                {
                    $('.reveal-modal').plainModal('close');
                    reveal_close=true;
                    showing_modal_window=false;
                }
            });
        }
        if (showing_modal_window===null)
        {
            showing_modal_window=true;
            
        }
    }

    if (containers.length > 0)
    {
        if (!Date.now) {
            Date.now = function () {
                return new Date().getTime();
            }
        }
        var timestamp = Math.floor(Date.now() / 1000);
        var date = new Date();
        var now = date.getHours() + ':' + date.getMinutes();
        $.ajax({
            type: 'POST',
            url: '/wp-admin/admin-ajax.php',
            data: {
                action: 'get_block',
                chest: chest,
                post_id: postids,
                width: $(window).width(),
                height: $(window).height(),
                block: typeof n_o_a_d_b_l_o_c_k == 'undefined',
                time: now
            },
            success: function (data) {
                if (containers.length > 0)
                {
                    containers.forEach(function (items, i, arr) {
                        var div = data[items]['text'];
                        $('#custom-block-' + items).removeAttr("id").replaceWith(div);
//                        $('#custom-block-' + items).click(function () {
//                            var item = $(this);
//                            $.post('/wp-admin/admin-ajax.php',
//                                    {
//                                        action: 'click_block',
//                                        id: $(this).attr('rel'),
//                                    }
//                            );
//
//
//                        });
                    });
                    set_new_cookie(data, chest);
                    blocked_modal();
                }

            },
            error: function (response) {
            },
            dataType: 'json'
        });
    } else {
        blocked_modal();
    }
    $(document).on('click', '.' + ajax_object.class_block, function () {
        $.post('/wp-admin/admin-ajax.php',
                {
                    action: 'click_block',
                    id: $(this).attr('rel')
                }
        );
    });


    $(".image_onhover").each(function () {
        var height = 0;
        var width = 0;
        $(this).find('img').each(function () {
            if ($(this).attr('height') > height) {
                height = $(this).attr('height');
            }
            if ($(this).width() > width) {
                width = $(this).width();
            }
        });
        var now_img_play = $(this).find('img.hovered_play_icon');
        now_img_play.removeAttr("width")
                .removeAttr("height")
                .css({width: "", height: ""});
        if (height > 0)
        {
            $(this).height(height);
            $(this).find('.text-content-on-image').height(height);

            var height_play = now_img_play.height();
            if (height_play > 0)
            {
                var tmp = parseInt(height) / 2 - (parseInt(height_play) / 2);
                now_img_play.css({top: tmp + "px"});
            }

        }
        if (width > 0)
        {
            $(this).width(width);
            $(this).find('.text-content-on-image').width(width);

            var width_play = now_img_play.width();
            if (width_play > 0)
            {
                var tmp = parseInt(width) / 2 - (parseInt(width_play) / 2);
                now_img_play.css({left: tmp + "px"});
            }
        }
    });

    $("body").on("mouseenter", '.image_onhover', function () {
        $(this).find('.hovered_play_icon').hide();
    });
    $("body").on("mouseout", '.image_onhover', function () {
        $(this).find('.hovered_play_icon').show();
    });

    function set_new_cookie(data, chests) {
        var count_add_to_wcs = new Object();
        count_add_to_wcs.last = {};
        $.each(chests, function (index_elem, elem) {
            $.each(elem, function (index_code) {
                if (data[index_code]['id'] !== '') {
                    if (typeof (count_add_to_wcs[index_elem]) === 'undefined')
                    {
                        count_add_to_wcs[index_elem] = {};
                    }
                    count_add_to_wcs[index_elem][data[index_code]['id']] = 1;
                    count_add_to_wcs.last[index_elem] = data[index_code]['id'];
                }
            });
        });
        if ($.cookie('wordpress_custom_setting'))
        {
            var cookies_getting = JSON.parse(window.atob($.cookie('wordpress_custom_setting')));
            if (cookies_getting.length > 0)
            {
                var both_object = $.extend(true, cookies_getting, count_add_to_wcs);
                $.each(both_object, function (index_elem) {
                    $.each(both_object[index_elem], function (index_code) {
                        if (index_elem !== 'last')
                        {
                            var summary = 0;
                            if (typeof (count_add_to_wcs[index_elem]) !== 'undefined' && typeof (count_add_to_wcs[index_elem][index_code]) !== 'undefined')
                            {
                                summary++;
                            }
                            if (typeof (cookies_getting[index_elem]) !== 'undefined' && typeof (cookies_getting[index_elem][index_code]) !== 'undefined')
                            {
                                summary = summary + cookies_getting[index_elem][index_code];
                            }
                            if (typeof (count_add_to_wcs[index_elem]) === 'undefined')
                            {
                                count_add_to_wcs[index_elem] = {};
                            }
                            count_add_to_wcs[index_elem][index_code] = summary;
                        }
                    });
                });
            }
        }
        var save_to_cookie = JSON.stringify(count_add_to_wcs);
        $.cookie('wordpress_custom_setting', window.btoa(save_to_cookie), {expires: 1, path: '/'});
    }
});