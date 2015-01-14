var index_slider_animation_runs = 0, hitzTimeout = 0, updateLinks;

function generateUUID() {
    var d = new Date().getTime();
    var uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
        var r = (d + Math.random()*16)%16 | 0;
        d = Math.floor(d/16);
        return (c=='x' ? r : (r&0x3|0x8)).toString(16);
    });
    return uuid;
};

function is_touchable() {
    return ('ontouchstart' in window || navigator.msMaxTouchPoints);
}

var touchable = is_touchable();

function hitz_move(delta, repeat) {
    if (!hitzTimeout) return;
    if (delta == 0) return;
    $('#hitz ul li.g').html('<i class="load"></i>');
    $('#hitz td').removeClass('o').removeClass('o1').removeClass('o2');

    var section = $('#hitz table a').eq(2 + delta).attr('rel');
    $.get('ajax/hitz/' + section, function (data) {
        $('#hitz ul').replaceWith(data);
        $('#hitz ul .buy > input:text').incdec();
    });

    if (delta > 0) { // slide left
        $('#hitz table').animate({'marginLeft': -delta * 194}, function () {
            for (i = 1; i <= delta; i++) {
                $('#hitz tr').append($('#hitz td').first().detach());
                $('#hitz table').css('marginLeft', (delta - i) * 194);
            }
            $('#hitz td').eq(0).addClass('o2');
            $('#hitz td').eq(1).addClass('o1');
            $('#hitz td').eq(2).addClass('o');
            $('#hitz td').eq(3).addClass('o1');
            $('#hitz td').eq(4).addClass('o2');
        });
    } else { // slide right
        for (i = 1; i <= Math.abs(delta); i++) {
            $('#hitz tr').prepend($('#hitz td').last().detach());
            $('#hitz table').css('marginLeft', -i * 194);
        }
        $('#hitz table').animate({'marginLeft': 0}, function () {
            $('#hitz td').removeClass('o').removeClass('o1').removeClass('o2');
            $('#hitz td').eq(0).addClass('o2');
            $('#hitz td').eq(1).addClass('o1');
            $('#hitz td').eq(2).addClass('o');
            $('#hitz td').eq(3).addClass('o1');
            $('#hitz td').eq(4).addClass('o2');
        });
    }
    if (repeat) {
        hitzTimeout = window.setTimeout(function () {
            hitz_move(-1)
        }, 10000);
    }
}

function index_slider_move(offset) {
    if (index_slider_animation_runs) return;
    index_slider_animation_runs = 1;
    $('#index_slider div.is_runner').stop(true, true);
    var is_width = $('#index_slider div.is_runner').width(), left = parseInt($('#index_slider div.is_runner').css('left')), newLeft = left + offset, step = Math.abs(offset);
    if (newLeft > 0) {
        newLeft = step - is_width;
    } else if (newLeft < step - is_width) {
        newLeft = 0;
    }
    $('#index_slider b.point').removeClass('active');
    $('#index_slider div.is_runner').animate({'left': newLeft + 'px'}, 500, function () {
        index_slider_animation_runs = 0;
        $('#index_slider b.point:eq(' + Math.abs(newLeft / step) + ')').addClass('active');
    });
}

function addfancy(selector) {

    $(selector).each(function () {
        var element = this;
        $(element).fancybox({
            loop: false,
            arrows: false,
            type: 'ajax',
            autoCenter: false,
            keys: {
                next: false,
                prev: false,
                close: [27], // escape key
                toggle: [70]  // letter "f" - toggle fullscreen
            },
            onUpdate: function () {
                /* Fix popup if page is in an iframe */
                if ('undefined' != typeof(window.top) && window != window.top) {
                    var context = $(".fancybox-wrap.fancybox-opened"),
                        top = parseInt(jQuery(element).position().top),
                        bottom = top + context.height() - jQuery(document).height();
                    if (0 == top) {
                        top = (jQuery(document).height() * 0.03);
                    }
                    if (0 <= bottom) {
                        top -= (bottom + 50);
                    }
                    context.css({'top': top});
                }
            },
            beforeShow: function () {
                $('.fancybox-inner :radio').radio();
                $('.fancybox-inner :checkbox').checkbox();
                $('.fancybox-inner .buy > input:text').incdec();
            }
        });
    });
}

function ajax_sphinx() {
    var param_hash = '', param_array = [];
    if ($('#search_mode').val() == 'word') param_array['q'] = $('#search_query').val(); //q

    $('#ff input:checked').each(function (index, item) { // b, c, f
        var key = $(item).prop('name');
        if (key.charAt(0) == 'f') key = key.replace('[', '').replace(']', '');
        if (!param_array[key]) param_array[key] = [];
        param_array[key].push($(item).val());
    });
    $('#list_props select').each(function (index, item) { // s, pp
        param_array[$(item).prop('name')] = $(item).val();
    });

    if ($('#prpr').val()) param_array['pr'] = $('#prpr').val();

    param_array['x'] = ( $('#list_props #has:checked').prop('checked') || $('#list_props #has:hidden').val() ) ? 1 : 0; // x

    param_array['m'] = $('#mode').length ? $('#mode').val() : 1; // m

    for (var k in param_array) {
        if (param_array[k] && typeof(param_array[k].sort) === "function") {
            param_hash += k + '=' + param_array[k].sort().join('_') + '&';
        } else {
            param_hash += k + '=' + param_array[k] + '&';
        }
    }
    param_hash = param_hash.substr(0, param_hash.length - 1)

    if (typeof(sphinx_location) != 'undefined') {
        window.location.href = sphinx_location + '?' + param_hash;
    } else {
        window.location.search = '?' + param_hash
    }

    return param_hash;
}

function fill_hash(query) {
    var qarr = query.split(';');

    $('#ff :checkbox').prop('checked', false); // clear all filters
    $('#ff :checkbox').parent().removeClass('checked');

    $('#choice').empty();

    // clear price filter
    var mi = $('#ff span.min'), ma = $('#ff span.max'), ratio = parseFloat($('#ff div.range').attr('rel'));
    var min = parseInt(mi.attr('rel')); // limits
    var max = parseInt(ma.attr('rel'));
    $('#prpr').val('');

    $('#ff span.line').css({marginLeft: 0, marginRight: 0});
    mi.text(format_number(min));
    ma.text(format_number(max));

    // nalichie
    $('#list_props #has').prop('checked', false);
    $('#list_props #has').parent().removeClass('checked');

    for (var i in qarr) {
        var kv = qarr[i].split('=');
        switch (kv[0]) {
            case 'x':
                if (kv[1] == 1) {
                    $('#list_props #has').prop('checked', true);
                    $('#list_props #has').parent().addClass('checked');
                }
                break;
            case 'pp':
                $('#list_props #page').val(kv[1]);
                break;
            case 'm':
                $('#list_props #mode').val(kv[1]);
                $('#view_mode a').removeClass('a');
                $('#view_mode a.m' + kv[1]).addClass('a');
                break;
            case 's':
                $('#list_props #sort').val(kv[1]);
                break;
            case 'pr': // price
                var pr = kv[1].split('-'), pr0 = parseInt(pr[0], 10), pr1 = parseInt(pr[1], 10);
                if (isNaN(pr0) || pr0 < min || pr0 > max) pr0 = min;
                if (isNaN(pr1) || pr1 > max || pr1 < min) pr1 = max;

                $('#ff span.line').css({
                    marginLeft: (pr0 - min) / ratio,
                    marginRight: (max - pr1) / ratio
                });
                mi.text(format_number(pr0));
                ma.text(format_number(pr1));

                if (pr0 != min || pr1 != max) {
                    var id = 'prpr', cb = $('#' + id), title = mi.parent().text();
                    cb.parent().toggleClass('checked', true);
                    $('#choice').append($('<a>' + title + '</a>').attr({rel: id, title: title}));
                }
                $('#prpr').val($('#ff span.line').parent().parent().text().replace(/[^0-9-]/g, ''));

                break;
            case 'c': // category
            case 'b': // brand
                var vals = kv[1].split('_');
                for (var j in vals) {
                    var id = kv[0] + vals[j], cb = $('#' + id), title = cb.parent().attr('title');
                    if (cb.length) {
                        cb.prop('checked', true);
                        cb.parent().toggleClass('checked', true);
                        $('#choice').append($('<a>' + title + '</a>').attr({rel: id, title: title}));
                    }
                }
                break;
            default:
                if (kv[0].charAt(0) == 'f') { // filters
                    var vals = kv[1].split('_');
                    for (var j in vals) {
                        var id = kv[0] + '_' + vals[j], cb = $('#' + id), title = cb.parent().attr('title');
                        if (cb.length) {
                            cb.prop('checked', true);
                            cb.parent().toggleClass('checked', true);
                            $('#choice').append($('<a>' + title + '</a>').attr({rel: id, title: title}));
                        }
                    }
                }
        }
    }
}

function place_cart() {
    if ($('#cart').length) {
        var t = $(window).scrollTop(), offset = $('#head').offset();
        $('#cart').toggleClass('down', t > 15).css('left', t > 15 ? offset.left + 756 : 756);
    }
}

function format_number(n, html) {
    var rgx = /(\d+)(\d{3})/;
    var s = n.toFixed(2);
    if (Math.floor(n) == s) {
        s = n.toFixed(0);
    }
    while (rgx.test(s)) {
        s = s.replace(rgx, '$1' + ' ' + '$2');
    }

    return (html) ? s.replace('.', '<small>.') + html + '</small>' : s;
}

function format_price(n)
{
    var rgx = /(\d+)(\d{3})/;
    var s = n.toFixed(2);
    if (Math.floor(n) == s) {
        s = n.toFixed(0);
    }
    while (rgx.test(s)) {
        s = s.replace(rgx, '$1' + ' ' + '$2');
    }

    return s.replace('.', '<small>.') + ' р.</small>';
    
}

function get_inputs(form) {
    var inputs = new Object(), name, is_array, val;
    $('input[name], select, textarea', form).each(function () {
        name = $(this).prop('name');
        is_array = name.replace('[]', '') != name;
        val = false;

        if ($(this).prop('type') == 'checkbox') {
            val = $(this).prop('checked') ? ($(this).prop('value') ? $(this).prop('value') : 1 ) : 0;
        }
        if (($(this).prop('type') == 'radio' && $(this).prop('checked')) ||
            ($(this).prop('type') == 'text' || $(this).prop('type') == 'password' || $(this).prop('type') == 'hidden'
            || $(this).prop('type') == 'submit' || $(this).prop('type') == 'button') ||
            $(this).is('select') || $(this).is('textarea')
        ) {
            val = $(this).val();
        }

        if (!inputs[name] && is_array) inputs[name] = [];
        if (val !== false) {
            if (is_array) inputs[name].push(val);
            else inputs[name] = val;
        }

    });

    inputs.ajax = 1;

    return inputs;
}

var tooltip = false;
function tt(item, content) {
    if (!item) {
        if (tooltip) tooltip.css('visibility', 'hidden');
        return;
    }
    if (!tooltip) {
        tooltip = $('<div id="tooltip"></div>')
            .appendTo('body')
            .css('visibility', 'hidden');
    }
    var offset = $(item).offset();
    tooltip.html(content.replace('<i></i>', '') + '<i></i>');

    tooltip.removeClass('r');
    tooltip.removeClass('b');
    tooltip.removeClass('ff');

    if ($(item).is('input') || $(item).is('textarea')) { // inside form - show at right
        tooltip.addClass('r');
        var pos = {
            left: offset.left + $(item).outerWidth() + 10,
            top: offset.top
        };
    } else if ($(item).is('label') || $(item).hasClass('range')) { // filters click
        tooltip.addClass('ff');
        var pos = {
            left: offset.left + 178,
            top: offset.top - 8
        };
    } else if ($(item).parent().hasClass('status')) { // user status
        tooltip.addClass('b');
        var pos = {
            left: offset.left - tooltip.outerWidth() + 50,
            top: offset.top + 20
        };
    } else if ($(item).parent().hasClass('delable')) { // delete from cart checkbox
        var pos = {
            left: $(item).parent().offset().left + 27,
            top: offset.top - tooltip.outerHeight() - 5
        };
    } else {
        var pos = {
            left: offset.left - 10,
            top: offset.top - tooltip.outerHeight() - 5
        };
    }
    tooltip.offset(pos).css('visibility', 'visible');
}

var total = 0, pricetotal = 0; // переменные при покупке

function retotal(item) { // пересчёт тоталов при изменении кол-ва - передаём поле ввода с новым значением
    var oldval = parseInt($(item).attr('oldval'), 10);
    var val = parseInt($(item).val(), 10);
    var price = parseFloat($(item).attr('price'));

    if (isNaN(oldval)) oldval = 0;
    if (isNaN(val)) val = 0;

    var delta = val - oldval;
    if (delta != 0) {
        total += delta;
        pricetotal += price * delta;
    }
    $('#total').text(total);
    $('#pricetotal').html(format_number(pricetotal, ' р.'));

    $(item).attr('oldval', val);
    $('#totals tfoot').toggleClass('a', total > 0);
}

function pager(name, total, perPage, currentPage, callback) {
    //alert(1);

    var from = (currentPage - 1) * perPage;
    var to = currentPage * perPage;

    var pagesCount = Math.ceil(total / perPage);
    ;

    var pagerDOM = $('<div class="pager"></div>');
    $('<span>' + name + ' ' + from + '-' + to + ' из ' + total + ' </span>').appendTo(pagerDOM);

    var buttons = $('<div></div>')
    var input = '';
    for (var i = 0; i < pagesCount; i++) {
        if (i == currentPage) {
            $('<strong>' + i + '</strong>').appendTo(buttons);
        } else {
            input = $('<input type="button" value="' + i + '" />');
            input.click(function () {
                callback($(this).val());
            });
            input.appendTo(buttons);
        }
    }
    pagerDOM.append(buttons);
    return pagerDOM;
}

function load_action_goods(targetElSelector, actionId) {
    var targetEl = $(targetElSelector);
    if (!targetEl.hasClass('loaded')) {
        targetEl.html('<p align="center"><img src="/i/load.gif" /></p>');
        jQuery.get('/actions/' + actionId + '/goods', null, function (data) {
            targetEl.html(data);
            $(targetElSelector + ' .buy > input:text').incdec();
            if (!touchable) addfancy(targetElSelector + ' a.fastview');
        });
        targetEl.addClass('loaded');
    }
}

// checkbox plugin
(function ($) {
    $.fn.extend({
        checkbox: function () {
            if (IE7) return;

            return this.each(function () {
                $(this).parent().toggleClass('checked', $(this).prop('checked'))
                if (!$(this).prop('readonly')) {
                    $(this).change(function () {
                        $(this).parent().toggleClass('checked');
                    })
                }
            });
        }
    });
})(jQuery);

// radio plugin
(function ($) {
    $.fn.extend({
        radio: function () {
            if (IE7) return;

            return this.each(function () {
                $(this).parent().toggleClass('checked', $(this).prop('checked'));

                $(this).change(function () {
                    var name = $(this).attr('name');
                    name = name.replace('[', '\\[');
                    name = name.replace(']', '\\]');
                    $('input[name=' + name + ']').each(function (i, item) {
                        $(item).parent().toggleClass('checked', $(item).prop('checked'));
                    });
                });
            });
        }
    });
})(jQuery);

$(document).ready(function () {

    if ('undefined' == typeof(window.top) || window == window.top) { // clear external user
        $.ajax({
            url: "/user/external/clean", dataType: 'json', success: function (r) {
                if (r.clean) location.reload();
            }
        });
    }

    if ($('#index_slider').length) {
        var wrap = $('#index_slider div.is_runner'), step = wrap.innerWidth(), slides = $('#index_slider div.is_runner a'), picCount = slides.length;

        if (picCount > 1) {
            slides.each(function (i, el) {
                var point = $('<b class="point"></b>');
                if (i == 0) point.addClass('active');
                $(point).click(function () {
                    wrap.stop(false);
                    wrap.animate({left: '-' + i * step + 'px'}, 500);
                    $('#index_slider b.point').removeClass('active');
                    $(this).addClass('active');
                });
                $('#index_slider #nav').append(point);
            });
            $('#index_slider #nav').css('left', Math.round((step - picCount * 29) / 2) + 'px');

            wrap.css('width', picCount * step + 'px');
            $('#index_slider').append('<i class="l"></i><i class="r"></i>');

            var indexSliderIntervalID = setInterval(function () {
                index_slider_move(-step);
            }, 6000);
            $('#index_slider i.r').click(function () {
                index_slider_move(-step);
                clearInterval(indexSliderIntervalID);
            });
            $('#index_slider i.l').click(function () {
                index_slider_move(step);
                clearInterval(indexSliderIntervalID);
            });
            $('#index_slider b.point').click(function () {
                clearInterval(indexSliderIntervalID);
            });
        }
    }

    if ($('#hitz').length) {
        hitzTimeout = window.setTimeout(function () {
            hitz_move(-1, true)
        }, 10000);
        $(document).on('click', '#hitz a.arr, #hitz table td', function () { // клик на хиты продаж
            if ($(this).is('a')) { // стрелка
                var move = $(this).index('#hitz a') == 1 ? -1 : 1;
                hitz_move(move, false);
            } else {
                hitz_move($(this).index('#hitz table td') - 2, false);
            }
            return false;
        })
            .on('mouseenter', '#hitz ul,#hitz table', function () {
                window.clearTimeout(hitzTimeout);
            })
            .on('mouseleave', '#hitz ul,#hitz table', function () {
                hitzTimeout = window.setTimeout(function () {
                    hitz_move(-1, true)
                }, 10000);
            });

    }

    $(window).on('hashchange', function () { // редирект с ажакс-урлов на обычные
        if (window.location.hash.substr(0, 2) == '#!') {
            var query = window.location.hash.substr(2), pl = $('#product_list'), yell = $('h1.yell');
            var redir = window.location.pathname + (window.location.search ? window.location.search + '&' : '?') + query.replace(/;/g, '&').substr(0, query.length - 1);
            window.location.href = redir;

            //if (confirm(redir)) ;
            fill_hash(query);
            $('#tags').hide();
            pl.html('<i class="load"></i>');

            $('body').scrollTop(yell.length ? yell.offset().top : pl.offset().top);
            $.post('URL', {
                hash: query,
                mode: $('#search_mode').val(),
                query: $('#search_query').val()
            }, function (response) {
                updateLinks();
                pl.replaceWith(response);
                $('#tags').show();
                pl = $('#product_list');
                $('.buy > input:text', pl).incdec();
                addfancy("#product_list a[rel='ajax']");
                $('#menu').css('marginTop', yell.position().top - $('#action_stats').outerHeight());
            });
        }
    });

    if (product_load || window.location.hash.substr(0, 2) == '#!') $(window).trigger('hashchange'); // дёрнем товары

    if (is_kiosk) {
        tinyKbd.init('input:text,input:password,textarea'); // миниклава для киоска
    } else {
        $('.user-registration input[name=phone]').mask('+7(999)999-99-99');
        $('.child_birth').mask('2099-99-99');

        $('.user-registration .regpoll').hide();
        if (register_poll) {
            $('.user-registration p').click(function () {
                $(".user-registration .regpoll").load("/poll/variants/" + register_poll);
                $(".user-registration .regpoll").show();
                $('.user-registration p').unbind('click');
                $('.user-registration p').click(function () {
                    $(".user-registration .regpoll").toggle()
                });
            });
        }

        $('#search button').hide(); // кнопка поиска при фокусе в поле
        $('#search .q')
            .focus(function () {
                $('#search').addClass('active');
                $('#search button').show();
            })
            .focusout(function () {
                $('#search').removeClass('active');
            });
    }

    /* красивые чекбоксы */
    $('label.label > input:checkbox').checkbox();

    /* красивые радио */
    $('label.label > input:radio').radio();

    /* добавлялки - убавлялки */
    $('.buy > input:text').incdec();

    addfancy("a[rel='ajax']");

    $("a[rel='sert']").fancybox(); // сертификатики

    $('div.range').each(function () { // ranges init for prices
        var mi = $('span.min', this), ma = $('span.max', this);

        // current values
        var xmin = $('span.min').text().replace(/ /g, '');
        var xmax = $('span.max').text().replace(/ /g, '');

        var min = parseInt(mi.attr('rel')); // limits
        var max = parseInt(ma.attr('rel'));
        var ratio = (max - min) / ($(this).width() - 18);

        $(this).attr('rel', ratio); // ratio
        $('span.line', this).css({
            marginLeft: (xmin - min) / ratio,
            marginRight: (max - xmax) / ratio
        });
    });

    if (!touchable) {
        $(document)
            .on('mouseenter.catalog_submenu', '#catalog td:first', function () {
                $('#catalog').addClass('first')
            })
            .on('mouseleave.catalog_submenu', '#catalog td:first', function () {
                $('#catalog').removeClass('first')
            })
            .on('mouseenter.catalog_submenu', '#catalog td:last', function () {
                $('#catalog').addClass('last')
            })
            .on('mouseleave.catalog_submenu', '#catalog td:last', function () {
                $('#catalog').removeClass('last')
            });
    }


    $(document)

        .on('submit', 'form.ajax', function () { // формы обрабатываются ажаксом

            var f = $(this), fancy = false;
            if (f.hasClass('proceed')) return false; // do not work with form already proceeded
            f.addClass('proceed');

            if (f.parent().hasClass('fancybox-inner')) fancy = true;
            $('input[type=submit]', this).after('<i class="load"></i>');

            $.post($(this).attr('action'), get_inputs(this), function (data) {
                var redir = function () {
                };

                if (data.redirect) {
                    redir = function () {
                        location.href = data.redirect;
                    }

                    var co = 0;
                    $.each(data, function () {
                        co++
                    });
                    if (co == 1)
                        redir();
                }

                if (data.reload) location.reload();

                if (data.fancybox) {
                    $.fancybox.open([{
                        content: data.fancybox,
                        type: 'html',
                        beforeClose: redir
                    }]);
                }
                if (data.error) {

                    var scrolled = false;

                    $('input.txt, input.wtxt, textarea.txt, textarea.wtxt', f).each(function () { // сообщения об ошибках на инпутах

                        if (!$(this).hasClass('misc')) {
                            var n = $(this).attr('name');
                            if (data.error[n]) {
                                $(this)
                                    .removeClass('ok')
                                    .addClass('error')
                                    .attr('error', data.error[n]);

                                if (!scrolled) {

                                    scrolled = true;

                                    $('html, body').animate({
                                        scrollTop: $(this).parent().offset().top
                                    }, 500).animate({
                                        scrollLeft: $(this).parent().offset().left
                                    }, 500);
                                }

                            } else {
                                $(this)
                                    .addClass('ok')
                                    .removeClass('error')
                                    .removeAttr('error');
                            }
                        }
                    });
                    $('select', f).each(function () { // ошибки на селектах

                    })
                }
                if (data.ok) {
                    $('input.txt, textarea.txt, input.wtxt, textarea.wtxt', f).each(function (i, item) {
                        $(item)
                            .addClass('ok')
                            .removeClass('error')
                            .removeAttr('error');
                    });
                }
                if (data.html) {
                    if (fancy) {
                        f.parent().html(data.html);
                        $.fancybox.update();
                    } else {
                        $('input.txt, textarea.txt, input.wtxt, textarea.wtxt', f).each(function (i, item) {
                            $(item)
                                .prop('readonly', 'readonly')
                                .addClass('ok')
                                .removeClass('error')
                                .removeAttr('error');
                        });
                        $('input[type=submit]', f).replaceWith(data.html);
                    }
                }
                $('i.load', f).remove();
                f.removeClass('proceed');
            }, 'json')
                .error(function (xhr, status, errorThrown) {
                    return confirm('Произошла ошибка:' + errorThrown + '\n' + status + '\n' + xhr.statusText);
                    alert('Произошла ошибка:' + errorThrown + '\n' + status + '\n' + xhr.statusText);
                    f.removeClass('proceed');
                });

            return false;
        })

        .on('click', '#userpad.w > a', function () {  /* login form and register form toggler */
            var yourClick = true;
            $(document).bind('click.myEvent', function (e) {
                if (!yourClick && $(e.target).closest('#userpad').length == 0) {
                    $('#userpad > a').removeClass('open');
                    $('#userpad > form').hide();
                    $(document).unbind('click.myEvent');
                }
                yourClick = false;
                tt(false);
            });
            if ($(this).hasClass('open')) {
                $(this).removeClass('open');
                $('.' + $(this).attr('rel')).hide('fast');
            } else {
                $('#userpad > a').removeClass('open');
                $('#userpad > form').hide();
                $(this).addClass('open');
                $('.' + $(this).attr('rel')).show('fast');
            }
            tt(false);
        })

        .on('click', '.fancybox-inner a[rel="buy"]', function () { // покупка внутри всплывающего окна
            $('.fancybox-inner h1, .fancybox-inner #etalage li, .fancybox-inner .txt').prepend('<i class="load"></i>');

            $('.fancybox-inner tr').removeClass('a');
            $('.fancybox-inner .buy input').val(0);
            $(this).closest('tr').addClass('a').find('input').val('1');

            var selected_price = parseFloat($(this).closest('tr').find('td.price span').text());

            $.get($(this).prop('href'), function (data) {
                var j = $(data);
                $('.fancybox-inner h1').replaceWith($('h1', j));
                $('.fancybox-inner #etalage').replaceWith($('#etalage', j))
                $('.fancybox-inner .txt').replaceWith($('.txt', j));

                if (!isNaN(selected_price)) {
                    $('#pricetotal').html(selected_price + '<small>р.</small>');
                } else {
                    $('#pricetotal').html(0 + '<small>р.</small>');
                    $('#pricetotal').closest('tfoot').removeClass('a');
                }
            });
            return false;
        })

        .on('blur', 'input.txt, textarea.txt, input.wtxt, textarea.wtxt', function (ev) { // посказки у инпутов - скрыть
            if ($(this).val() > '') $(this).addClass('full');
            tt(false);
        })
		.on('mouseleave', '.user-registration', function(){
			tt(false);
		})
        .on('focus mouseover', 'input.txt, textarea.txt, input.wtxt, textarea.wtxt', function (ev) { // посказки у инпутов - показать
            $(this).removeClass('ok').removeClass('error');
            if ($(this).attr('error')) {
                tt(this, $(this).attr('error'));
            }
        })

        .on('click', 'table.totals a.c', function (e) { // положить в корзину всё
            $(this).closest('form').submit();
            e.stopPropagation();
        })
        .on('click', 'a.c', function () { // положить в корзину один товар
            var id = $(this).attr('rel'), inp = $('#qty_' + id), q = inp.val();
            if (q == 0) q = 1;
            $.post('/product/add', 'one=1&qty[' + id + ']=' + q, function (data) {
                $('#cart').replaceWith(data);
                place_cart();
                inp.val(0);
                retotal(inp);
                if ($('#cart_box').length > 0) {
                    $.get('/personal/basket_ajax', {}, function (html) {
                        var cartbox = $('#cart_box');
                        cartbox.html(html);
                        $('input:checkbox').checkbox();
                        $('input:text', cartbox).incdec();
                        //Листаем слайдер на 1 вперед
                        $('.slider i:last').click();
                    });
                }
            });

            return false;
        })

        .on('click', 'ul > li > a.toggler', function () { // togglers in lists (last li, works once)
            $(this).closest('ul').find('li.hide').show('fast');
            $(this).parent().hide();
        })

        .on('click', 'a.tognext', function () { // togglers in delivery zones
            $(this).next('div').toggleClass('hide');
            return false;
        })

        .on('click', 'a.toggler.abbr', function () { // togglers
            $('#' + $(this).attr('rel')).toggleClass('hide');
            return false;
        })

        .on('mouseenter', 'abbr', function () { // tooltip show
            var a = $(this).attr('abbr');
            tt(this, a ? a : $('#abbr').attr('abbr'));
        })
        .on('mouseleave', 'abbr', function () { // tooltip hide
            tt(false);
        })

        .on('click', '#ff > strong', function () { /* фильтры - открытие-закрытие */
            $(this).next().toggle('fast');
            $(this).toggleClass('off');
        })
        .on('click', '#ff label[data-url]', function () { /* меню на месте фильтров */
            location.href = $(this).data('url');
            return false;
        })
        .on('change', '#ff label > input:checkbox', function () { /* фильтры - накликивание */
            ajax_sphinx();
        })
        .on('click', '#choice a', function () { // откликивание фильтров

            if ($(this).attr('rel') == 'prpr') { // ценовой
                $('#prpr').val('');
                ajax_sphinx();
            } else {
                $('#' + $(this).attr('rel')).click(); // остальные фильтры
            }

        })

        .on('touchstart mousedown', 'div.range span.line i', function (event) {

            var cssProp, css1Prop, inp, mode;

            if ($(this).hasClass('min'))
                mode = 'left';
            else
                mode = 'right';

            if (mode == 'left') {

                cssProp = 'marginLeft';
                css1Prop = 'marginRight';
                inp = 'span.min';
            }
            else {

                cssProp = 'marginRight';
                css1Prop = 'marginLeft';
                inp = 'span.max';
            }

            var line = $(this).parent();
            var ratio = parseFloat($(this).closest('div.range').attr('rel'));
            if (event.type == "touchstart") {
                var touch = event.originalEvent.touches[0] || event.originalEvent.changedTouches[0];
                var initX = (touch.clientX);
            }
            else {
                var initX = event.pageX;
            }

            var initP = parseInt(line.css(cssProp));
            var maxP = line.parent().width() - parseInt(line.css(css1Prop)) - 18;
            var input = line.closest('div.range').find(inp);
            var ePageX = initX;

            var tm = function (epagex) {

                var p, v;
                $('body').css('cursor', 'pointer');

                if (mode == 'left') {

                    p = Math.max(0, Math.min(initP + epagex - initX, maxP));
                    v = parseInt(input.attr('rel')) + Math.round(ratio * p);
                }
                else {

                    p = Math.max(0, Math.min(initP + initX - epagex, maxP));
                    v = parseInt(input.attr('rel')) - Math.round(ratio * p);
                }

                line.css(cssProp, p + 'px');
                input.text(format_number(v));
            };
            $('body')
                .bind('touchmove', function (event) {
                    tm(event.originalEvent.touches[0].pageX);
                })
                .bind('mousemove', function (event) {
                    tm(event.pageX);
                })
                .bind('selectstart', function (event) {
                    return false;
                })
                .bind('touchend mouseup', function (event) {
                    $('body')
                        .css('cursor', 'default')
                        .unbind('mousemove')
                        .unbind('touchmove')
                        .unbind('selectstart');

                    $('#prpr').val(line.parent().parent().text().replace(/[^0-9-]/g, ''));
                    ajax_sphinx();
                });

            event.stopPropagation();
            event.preventDefault();
        })
        .on('click', 'a.toreg', function () {     // открыть форму регистрации
            $(window).scrollTop(0);
            $('#userpad a[rel=reg_form]').click();
            return false;
        })
        .on('click', '.tabs a.t', function () { // tabы
            var i = $(this).index();
            var href = $(this).attr('rel');
            $('a.t', $(this).parent()).removeClass('active');
            $('a.r', $(this).parent()).attr('href', href);
            $('div.tab-content', $(this).parent().parent()).removeClass('active');
            $(this).addClass('active');
            $('div.tab-content', $(this).parent().parent()).eq(i).addClass('active');
        })
        .on('change', 'input.poll_radio:radio', function () { // Голосования - тип радио
            var id = $(this).val(), f = $(this).closest('fieldset');
            $(f).find('.poll_free').prop('disabled', true).val('');
            $('input[rel=' + id + ']', f).prop('disabled', false);
        })
        .on('change', 'input.poll_multi:checkbox', function () { // Голосования - тип checkbox
            var id = $(this).val(), f = $(this).closest('fieldset');
            if ($(this).prop('checked')) {
                $('input[rel=' + id + ']', f).prop('disabled', false);
            }
            else {
                $('input[rel=' + id + ']', f).prop('disabled', true).val('');
            }
        })
        .on('click', 'label.priority span', function () { // Голосования - звездочки
            $(this).parent().children('.active').removeClass('active');
            $(this).parent().children('.highlighted').removeClass('highlighted');
            $(this).parent().find('.poll_free').prop('disabled', false);
            $(this).addClass('active');
            $(this).prevAll('span').addClass('active');
            var id = $(this).attr('rel');
            $('#' + id).val($(this).attr('title'));
        })
        .on('mouseover', 'label.priority span', function () { // Голосования - звездочки
            $(this).parent().children('.highlighted').removeClass('highlighted');
            $(this).addClass('highlighted');
            $(this).prevAll('span').not('.active').addClass('highlighted');
        })
        .on('mouseout', 'label.priority span', function () { // Голосования - звездочки
            $(this).parent().children('.highlighted').removeClass('highlighted');
        })


        .on('click', '#view_mode > a', function () { // параметры вывода списка товаров
            var old_m = $('#mode').val(), m = $(this).attr('rel');
            if (old_m != m) {
                $('#view_mode > a').toggleClass('a');
                $('#mode').val(m);
                ajax_sphinx();
            }
        })
        .on('change', '#list_props #sort', function () {
            ajax_sphinx();
        })
        .on('change', '#list_props #page', function () {
            ajax_sphinx();
        })
        .on('change', '#list_props #has', function () {
            ajax_sphinx();
        })

        // рейтинг товара-группы
        .on('change', '#stats_for', function () {
            $('#good_rate > div').html('<i class="load"></i>');
            var href = $(this).val() == 0 ? '/rate/product/' + $('#good_id').val() : '/rate/group/' + $('#group_id').val();
            $('#good_rate > div').load(href, {ajax: 1});
        })

        // отзывы о товаре-группе
        .on('change', '#reviews_for', function () {
            $('.review > div').html('<i class="load"></i>');
            var href = $(this).val() == 0 ? '/review/product/' + $('#good_id').val() : '/review/group/' + $('#group_id').val();
            $('.review > div').load(href, {ajax: 1});
        })

        .on('scroll', window, function () { // корзина - движение
            place_cart();
        })

        // отзыв полезен - не полезен
        .on('click', '.review .desc blockquote > a', function () {
            var bl = $(this).parent(), todo = $(this).hasClass('no') ? 'no' : 'ok';
            bl.append('<i class="load"></i>');
            $.post('/review/' + todo + '/' + $(this).attr('rel'), {ajax: 1}, function (data) {
                bl.replaceWith(data);
            });
            return false;
        })

        // подгрузка отзывов по кнопке
        .on('click', '#load_reviews', function () {
            $(this).html('<i class="load"></i>');
            var href = $('#reviews_for').val() == 0 ? '/review/product/' + $('#good_id').val() : '/review/group/' + $('#group_id').val() + '/' + $(this).attr('rel');
            $.post(href, {page: $(this).attr('rel'), ajax: 1}, function (content) {
                $('#load_reviews').replaceWith(content);
            });
            return false;
        });

    // слайдеры
    var slide = 1;
    $('.slider i').on('click', function () {
        var slider = $(this).parents('.slider');
        var url = $(slider).attr('rel');

        var match = url.match(/page=([^&]+)/);

        if (match) slide = parseInt(match[1]);
        else url += '?page=' + slide;

        if ($(this).index() == 0) slide--;
        else slide++;

        url = url.replace(/page=[-0-9]+/g, 'page=' + slide);
        $(slider).attr('rel', url);

        $.get(url, function (data) {
            $('ul', slider).replaceWith(data);
            $('input:text', slider).incdec();
        })
    });

    if (navigator.userAgent.toLowerCase().indexOf("chrome") >= 0 || navigator.userAgent.toLowerCase().indexOf("safari") >= 0) { // проверка полей на автозаполнение и очистка, если что
        window.setTimeout(function () {
            $('input:-webkit-autofill').each(function () {
                $(this).addClass('full');
                var clone = $(this).clone(true, true);
                $(this).after(clone).remove();
            });
        }, 100);
    }
    var abl = $('#action_banner_list');
    if (abl.length) {
        $('div.description', abl).hide();
        $('li div.goods', abl).hide();
        $('li div.goods_all', abl).hide();
        $('li p.banner input[type=button]', abl).click(function () {
            $(this).parent('p.banner').siblings('div.description').toggle();
            $(this).parent('p.banner').parent('div.action_header').siblings('div.goods, div.goods_all').toggle();
        });
    }

    updateLinks = function () {

        $('.appendhash').each(function () {

            if ($(this).hasClass('updated')) {
                return false;
            }
            $(this).addClass('updated');

            var prep = $(this).attr('data-url');
            var s = $(this).attr('href');
            var p = s.indexOf('#');
            var u;
            if (p != -1) {

                u = s.substring(0, p) + s.substring(p + 1);
            }
            else {
                u = s;
            }
            $(this).attr('href', prep + u);
        });
        addfancy("a[rel='ajax']");
    };

    updateLinks();

    $(document).on('click', 'table#goods td.pencil button,.pencilator-opener', function (event) {
        if (0 === $('#pencilator').size()) { // Диалог НЕ открыт
            $(event.target).pencilator();
        } else {
            $('#pencilator').show();
        }
        event.preventDefault();
        event.stopPropagation();
    });

    /* <a href="/callback" rel="ajax" data-fancybox-type="ajax" class="callback"><i></i>Заказать обратный звонок</a> */
    var callCode = 'PGEgaHJlZj0iL2NhbGxiYWNrIiByZWw9ImFqYXgiIGRhdGEtZmFuY3lib3gtdHlwZT0iYWpheCIgY2xhc3M9ImNhbGxiYWNrIj48aT48L2k+0JfQsNC60LDQt9Cw0YLRjCDQvtCx0YDQsNGC0L3Ri9C5INC30LLQvtC90L7QujwvYT4=';
    $('address#topcontacts').prepend(Base64.decode(callCode));

    if (touchable) {
        $('head').append('<link rel="stylesheet" type="text/css" href="/c/touch.css?v=20140902" />');
    } else {
        /* <a href="http://issa.mangotele.com/widget/MTA0MDAy" class="mangotele_btn" onclick="window.open(this.href,'mangotele_widget', 'width=238,height=215,resizable=no,toolbar=no,menubar=no,location=no,status=no'); return false;" id="mangocall"><i></i>Звонок онлайн</a> */
        $('address#topcontacts').append(Base64.decode('PGEgaHJlZj0iaHR0cDovL2lzc2EubWFuZ290ZWxlLmNvbS93aWRnZXQvTVRBME1EQXkiIGNsYXNzPSJtYW5nb3RlbGVfYnRuIiBvbmNsaWNrPSJ3aW5kb3cub3Blbih0aGlzLmhyZWYsJ21hbmdvdGVsZV93aWRnZXQnLCAnd2lkdGg9MjM4LGhlaWdodD0yMTUscmVzaXphYmxlPW5vLHRvb2xiYXI9bm8sbWVudWJhcj1ubyxsb2NhdGlvbj1ubyxzdGF0dXM9bm8nKTsgcmV0dXJuIGZhbHNlOyIgaWQ9Im1hbmdvY2FsbCI+PGk+PC9pPtCX0LLQvtC90L7QuiDQvtC90LvQsNC50L08L2E+'));
    }

    addfancy("address#topcontacts a[rel='ajax']");

	$('.ml-box').mladenecbox();
	$('.ml-radio').mladenecradio();
});
