var index_slider_animation_runs = 0, hitzTimeout = 0, updateLinks, allowGoodsTopBar = false;

var PHONE_PATTERN = /^\D*(7|8)?\D*(3|4|8|9)\D*\d\D*\d\D*\d\D*\d\D*\d\D*\d\D*\d\D*\d\D*\d\D*$/;

function is_touchable() {
    return ('ontouchstart' in window || navigator.msMaxTouchPoints);
}

var touchable = is_touchable();

function hitz_move(delta, repeat) {
    if ( ! hitzTimeout) return;
    if (delta == 0) return;
    $('#hitz ul li.g').html('<i class="load"></i>');
    $('#hitz td').removeClass('o').removeClass('o1').removeClass('o2');

    var section = $('#hitz table a').eq(2 + delta).attr('rel');
    $.get('ajax/hitz/' + section, function (data) {
        $.when($('#hitz ul').replaceWith(data.html)).then(addfancy('#hitz .fastview'));
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

function addfancy(selector, rr_slider) {

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
                var inner = $('.fancybox-inner');
                if (typeof(rr_slider) != 'undefined') inner.addClass('rr_slider').attr('data-func', rr_slider);
                $(':radio', inner).radio();
                $(':checkbox', inner).checkbox();                
                updateLinks();
                //при открытии нового окна быстрого просмотра - сбрасывать тоталы
                if($(element).hasClass('fastview')) {
                    var init_product = inner.find('input[id^="qty_"][value="1"]');
                    if(init_product.length>0){
                        total = 1;
                        pricetotal = parseFloat(init_product.attr('price'));
                    } else {
                        total = 0;
                        pricetotal = 0;
                    }
                }
//                addfancy(".fancybox-inner a[rel=ajax]");
//                addfancy(".fancybox-inner a.fastview");
            }
        });
    });
}

function reload_section(url) {
    var t = '#body', loader = $('#loader');
    if (url.charAt(0) == '/') {
        url = location.protocol + '//' + location.host + url;
    }

    history.pushState(null, null, url);

    var timeout = setTimeout(function () {
        var offset = $(t).offset();
        loader.css({
            backgroundColor: 'rgba(100,100,100,0.15)',
            position: 'absolute',
            top: offset.top + 'px',
            left: offset.left + 'px',
            width: $(t).width() + 'px',
            height: $(t).height() + 'px',
            zIndex: 9030 /* больше чем у fancybox */
        }).show().fadeOut(0).fadeIn(500);
    }, 300);

    $.post(url, {'goodajax': 1}, function (data) {

        clearTimeout(timeout);
        loader.stop(true, false).fadeOut(300, function () {
            $(this).hide();
        });

        if ( ! documentStack[location.href]) documentStack[location.href] = { };
        documentStack[location.href][t] = $(t).html();

        $(t).empty().append(data.data);

        updateLinks();

        if ( ! documentStack[url]) documentStack[url] = { };

        documentStack[url]['#body'] = data.data;
        document.title = data.title;
        document.keywords = data.keywords;
        document.description = data.description;
    }, 'json');
}

function ajax_sphinx(key, link_id) {
    var o = $('#' + link_id), href = o.attr('data-url');
    href += (href.search('\\?') !== -1 ? '&' : '?') + key + '=' + o.val();
    reload_section(href);
}

function place_cart() {
    if ($('#cart').length) {
        var t = $(window).scrollTop(), offset = $('#head').offset();
        $('#cart').toggleClass('down', t > 15).css('left', t > 15 ? offset.left + 756 : 756);
    }

    if ($('#totals').length) {
        var t = $(window).scrollTop(), offset = $('.cart-prm').offset();
        $('#totals').toggleClass('down', t > offset.top).css('left', t > offset.top ? offset.left + 756 : 'auto');
    }
}

function format_number(n, html) {
    var rgx = /(\d+)(\d{3})/;
    n = parseFloat(n, 10);
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
    n = parseFloat(n, 10);
    var s = n.toFixed(2);
    if (Math.floor(n) == s) {
        s = n.toFixed(0);
    }
    while (rgx.test(s)) {
        s = s.replace(rgx, '$1' + ' ' + '$2');
    }

    return (s.search('\\.') != -1 ? s.replace('.', '<small>.') : s + '<small>') + ' р.</small>';
    
}

var tooltip = false;
function tt(item, content) {
    if (content == undefined) content = '';
    if ( ! item) {
        if (tooltip) tooltip.css('visibility', 'hidden');
        return;
    }
    if ( ! tooltip) {
        tooltip = $('<div id="tooltip"></div>')
            .appendTo('body')
            .css('visibility', 'hidden');
    }
    var offset = $(item).offset();
    tooltip.html(content.replace('<i></i>', '') + '<i></i>');

    tooltip.removeClass('r');
    tooltip.removeClass('b');
    tooltip.removeClass('top');
    tooltip.removeClass('ff');
    
    if ($(item).is('input') || $(item).is('textarea')) { // inside form - show at right
        tooltip.addClass('r');
        var pos = {
            left: offset.left + $(item).outerWidth() + 10,
            top: offset.top
        };
    } else if ($(item).is('label') && $(item).hasClass('tooltip-top')) { // filters click
        tooltip.addClass('top');
        var pos = {
            left: offset.left - 6,
            top: offset.top - 63
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
    } else if ($(item).parent().hasClass('zoombox')) { // discount under zoombox
        var pos = {
            left: offset.left + 155,
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
    $('tfoot.totals').toggleClass('a', total > 0);
}

function pager(name, total, perPage, currentPage, callback) {
    //alert(1);

    var from = (currentPage - 1) * perPage;
    var to = currentPage * perPage;

    var pagesCount = Math.ceil(total / perPage);

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

function load_action_goods(actionId) {
    var targetEl = $('#action_goods_' + actionId);
    if ( ! targetEl.hasClass('loaded')) {
        targetEl.html('<i class="load"></i>');
        jQuery.get('/actions/' + actionId + '/goods', null, function (data) {
            targetEl.html(data);
            if ( ! touchable) addfancy($('a.fastview', targetEl));
        });
        targetEl.addClass('loaded');
    }
    targetEl.closest('li').find('.action_info div').toggle();
    targetEl.toggle()
}

function init_ranges() {
    $('div.range').not('[rel]').each(function () { // ranges init for prices
        var mi = $('input.min', this), ma = $('input.max', this);

        // current values
        var xmin = parseFloat(mi.val().replace(/ /g, ''));
        var xmax = parseFloat(ma.val().replace(/ /g, ''));

        var min = parseFloat(mi.attr('rel')); // limits
        var max = parseFloat(ma.attr('rel'));
        if (isNaN(xmin)) {
            xmin = min;
        }
        if (isNaN(xmax)) {
            xmax = max;
        }
        var ratio = (max - min) / ($(this).width() - 18);

        $(this).attr('rel', ratio); // ratio
        $('span.line', this).css({
            marginLeft: Math.round((xmin - min) / ratio),
            marginRight: Math.round((max - xmax) / ratio)
        });
    });

}

// checkbox plugin
(function ($) {
    $.fn.extend({
        checkbox: function () {
            if (IE7) return;

            return this.each(function () {
                $(this).parent().toggleClass('checked', $(this).prop('checked'))
                if ( ! $(this).prop('readonly')) {
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

    var suggest = $('.search-suggestions'), suggestTimeout = 0;

    if ( ! ('placeholder' in document.createElement('input'))) { // нет поддержки placeholder - эмуляция
        $('input,textarea').each(function () {

            var ph = $(this).attr('placeholder'), val = $(this).val();
            if ( ! val) {
                $(this).val(ph).addClass('placeholder');
            }
            $(this).on('focus', function () {
                if ($(this).val() == ph) {
                    $(this).val('').removeClass('placeholder');
                }
            });
            $(this).on('blur', function () {
                if ($(this).val() == '') {
                    $(this).val(ph).addClass('placeholder');
                }
            });
        });
    }

    $('.rr_slider').each(function() { // слайдеры - асинхронно загружаем
        var url = '/slide/rr-' + $(this).attr('data-func'), param = $(this).attr('data-param'), div = $(this);
        if (param) url += '/' + param;
        url += '?t=' + encodeURIComponent($(this).attr('title'));

        window.setTimeout(
            $.get(url, function(data) {
                div.empty().append(data);
                addfancy($('a.fastview', div), $(div).attr('data-func'));
            }), 250);
    });

    $(document)
    .on('click', '.rr_slider a', function() { // rr - события
        var method_name = $(this).closest('.rr_slider').attr('data-func');

        if ( ! $(this).hasClass('i_cart')) { // переход в карточку с RR (переход в корзину - в другом месте)
            try { rrApi.recomMouseDown($(this).attr('data-id'), method_name) } catch (e) { }
        }
    })
    .on('keyup mouseup touchend', '#search:not(.findologic) [name=q]', function(e) {

        var a = $('a.active', suggest); // выбранный товар в поиске

        if (e.type == 'keyup' && (e.which == 38 || e.which == 40 || e.which == 13)) { // навигация по результатам
            switch (e.which) {
                case 38: // up
                    if (a.length) {
                        a.removeClass('active');
                        var prev = a.prev('a');
                        if (prev.length) prev.addClass('active');
                    }
                    break;

                case 40: // down
                    if (a.length) {
                        a.removeClass('active');
                        var next = a.next('a');
                        if (next.length) next.addClass('active');
                    } else {
                        $('a', suggest).first().addClass('active');
                    }
                    break;

                default: // enter
                    if (a.length) {
                        location.href = a.attr('href');
                        e.stopPropagation();
                    }
            }

        } else {

            var oldval = $(this).attr('oldval'), val = $.trim($(this).val());
            if (oldval != val) {
                $(this).attr('oldval', val);
                if (val) {
                    if (suggestTimeout) clearTimeout(suggestTimeout);

                    suggestTimeout = setTimeout(function () { // ждём паузы 0.3 секунды
                        $.post('/suggest/search', {q: val}, function (data) {
                            suggest.empty().append(data).show();
                        });
                    }, 300)
                }
            }
        }
        return false;
    });

    $('#good-deferred-butt').click(function(){
        var goodId = $(this).attr('defdata');
        var doing = 'add';
        if ($(this).attr('do') === 'delete')
        {
            doing = 'delete';
        }
        $.ajax({
            url: '/add_deferred',
            data:{'id' : goodId,'doing':doing },
            method: 'POST',
            success: function(data){
                if ($('#good-deferred-butt').attr('do') === 'delete')
                {
                    $('#good-deferred-butt').attr('do','add');
                    $('#good-deferred-butt').html('Отложить товар');
                } else {
                    if ($('#good-deferred-butt').attr('do') === 'add')
                    {
                        $('#good-deferred-butt').attr('do','delete');
                        $('#good-deferred-butt').html('Удалить из отложенных');
                    }
                }
            }
        })
    });

    $(document).on('click', '#user_city p > a.abbr', function() { // города во всплывашке
        $(this).closest('form').find('[name=city]').val($(this).text());
    });

    $(document).on('change keyup focus blur mouseup touchend', 'input[type=tel]', function() { // проверка телефона
       if ( ! $(this).val().match(PHONE_PATTERN)) {
           $(this).removeClass('ok').addClass('error');
       } else {
           $(this).removeClass('error').addClass('ok');
       }
    });

    $('input[type=tel]').attr('placeholder', 'Телефон');

    var login = $('#user-login'); // форма логина
    if (login.length) {
        $(login, 'input').keyup(function(e){
            if(e.which == 13){
                $(login, '.login-submit').click();
                return false;
            }
        });
        $('.user-login input[type=checkbox]').mladenecbox({ size: 23 });
    }

    var tate = $('#tag_text'); // long text toggler
    if (tate.length && tate.prop('scrollHeight') > 200) {
        toggler = $('<a>Показать полностью &darr;</a>');
        toggler.on('click', function () {
            if (tate.hasClass('short')) {
                toggler.text('Свернуть');
            } else {
                toggler.text('Показать полностью');
                $('html, body').prop('scrollTop', tate.offset().top);
            }
            tate.toggleClass('short');
        });
        tate.addClass('short');
        tate.after(toggler);
    }

    //RR - view event
    if (typeof(RetailRocket) != 'undefined') { // включено RR - сообщаем им о клике на быстрый просмотр
        $(document)
        .on('click', 'a.fastview', function() {
            var id = $(this).attr('data-id');
            rrApiOnReady.push(function() {
                try { rrApi.view(id); } catch(e) { }
            });
        })
        .on('blur', 'input[type=email], input[name=login], input[name=email]', function() { // и сообщаем мыла
            var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/; // mail regex from RR
            var val = $(this).val();

            if (regex.test(val)) {
                try { rrApi.setEmail(val); } catch(e) { }
            }
        });

    }
    if ($('#index_slider').length) { // слайдер на главной
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
        // Подгоняем длину
        $('#hitz table').css('width', $('#hitz table td').length * 194);
    
        hitzTimeout = window.setTimeout(function () {
            hitz_move(-1, true)
        }, 10000);

        $(document)
            .on('click', '#hitz a.arr, #hitz table td', function () { // клик на хиты продаж
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

    $(window).on('hashchange', function () { // редирект с ажакс-урлов на обычные - нужен для старфый ссылок на каталог(?)
        if (window.location.hash.substr(0, 2) == '#!') {
            var query = window.location.hash.substr(2), pl = $('#product_list'), yell = $('.yell h1'),
                redir = window.location.pathname + (window.location.search ? window.location.search + '&' : '?') + query.replace(/;/g, '&').substr(0, query.length - 1);
            window.location.href = redir;

            $.post('URL', {
                hash: query,
                mode: $('#search_mode').val(),
                query: $('#search_query').val()
            }, function (response) {
                updateLinks();
                pl.replaceWith(response);
                $('#tags').show();
                pl = $('#product_list');
                addfancy("#product_list a[rel='ajax']");
                $('#menu').css('marginTop', yell.position().top - $('#action_stats').outerHeight());
            });
        }
    });

    if (product_load || window.location.hash.substr(0, 2) == '#!') $(window).trigger('hashchange'); // дёрнем товары

    $('.child_birth').mask('99-99-2099');

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

    /* красивые чекбоксы */
    $('label.label > input:checkbox').checkbox();

    /* красивые радио */
    $('label.label > input:radio').radio();

    addfancy("a[rel='ajax']");

    $("a[rel='sert']").fancybox(); // сертификатики

    init_ranges();

    if ( ! touchable) {
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

	var addToTheCart = function(inputs){
		
		var params = {
			id: []
		};
		
		for( key in inputs ){
			params.id.push(key);
		}
		
		$.ajax({
			url: '/get-goods',
			data: params,
			dataType: 'JSON',
			method: 'POST',
			success: function(data){
				
				var products = [];
				
				for( id in data ){
					var item = data[id];
					item['quantity'] = inputs[id];
					products.push(item);
				}

				window.dataLayer = window.dataLayer || [];

				dataLayer.push({
				  ecommerce: {
					currencyCode: 'RUR',
					add: {
					  products: products
					}
				  },
				  event: 'addToCart'
				});
			}
		})
	};
    $(document)

        .on('submit', 'form.ajax', function () { // формы обрабатываются ажаксом

            tt(false);

            var f = $(this), action = f.attr('action'), fancy = false, inputs = $('input, select, textarea', this).not('[disabled]').serialize() + '&ajax=1';
            var subm = $(this).find('[type=submit]');
            if (subm.length) inputs += '&' + subm.attr('name') + '=1';

            if( action == '/product/add' ){
				addToTheCart(inputs);
			}
			
            if (f.hasClass('proceed')) return false; // do not work with form already proceeded
            f.addClass('proceed');

            if (f.parent().hasClass('fancybox-inner')) fancy = true;
            f.append('<i class="load"></i>');

            $.post(action, inputs, function (data) {
                var redir = function () { };

				if(data.userId){
					window.dataLayer = window.dataLayer || [];
					dataLayer.push({
                        userId : data.userId,
					    event : 'authentication'
					});
					
					delete data.userId;
				}
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
                if (data.delivery && typeof(delivery) !== 'undefined') {
                    $.when($("#cart-delivery").html(data.delivery)).then( 
                        $.when(addr.cached=data.addresses).then(delivery.init())
                    );
                    delivery.init();
                    if(googleCheckoutStep) googleCheckoutStep("3");
                }

                if(data.userpad) {
                    $("#current_city").remove();
                    $.when($("#userpad").replaceWith(data.userpad)).then($("#current_city").fancybox());
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

                        if ( ! $(this).hasClass('misc')) {
                            var n = $(this).attr('name');
                            if (data.error[n]) {
                                $(this)
                                    .removeClass('ok')
                                    .addClass('error')
                                    .attr('error', data.error[n]);

                                if ( ! scrolled) {

                                    scrolled = true;

                                    $('html, body').animate({
                                        scrollTop: $(this).parent().offset().top
                                    }, 500).animate({
                                        // scrollLeft: $(this).parent().offset().left
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
                    
                    //обработка ошибки с непринятым соглашением пользователя
                    if(typeof(data.error['agree']) !== 'undefined') {
                        var agree_checkbox = $('input[name="agree"]').closest('.mladenecbox-label');
                        agree_checkbox.addClass('error tooltip-top');
                        $(agree_checkbox).click(function(){
                                tt(false);
                                $(this).closest('.mladenecbox-label').removeClass('error').removeClass('tooltip-top');
                            });
                        tt(agree_checkbox, data.error['agree']);
                    }
                    
                    //ошибка доставки озон
                    if(typeof(data.error['ozonfail']) !== 'undefined') {
                        var ozon_tab = $('#ozon-error');
                        ozon_tab.addClass('tooltip-top');
                        setTimeout(
                            function(){tt(false), ozon_tab.removeClass('tooltip-top')
                            }, 5000);
                        $(ozon_tab).click(function(){
                                tt(false);
                                ozon_tab.removeClass('tooltip-top');
                            });
                        tt(ozon_tab, data.error['ozonfail']);
                    }
                    
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

        .on('click', '#userpad.w > .top-forms-holder > a', function () {  /* login form and register form toggler */
            var yourClick = true;
            $(document).bind('click.myEvent', function (e) {
                if (!yourClick && $(e.target).closest('#userpad').length == 0) {
                    $('#userpad > .top-forms-holder > a').removeClass('open');
                    $('#userpad > .top-forms-holder > form').hide();
                    $(document).unbind('click.myEvent');
                }
                yourClick = false;
                tt(false);
            });
            if ($(this).hasClass('open')) {
                $(this).removeClass('open');
                $('#userpad .' + $(this).attr('rel')).hide('fast');
            } else {
                $('#userpad > .top-forms-holder > a').removeClass('open');
                $('#userpad > .top-forms-holder > form').hide();
                $(this).addClass('open');
                $('#userpad .' + $(this).attr('rel')).show('fast');
            }
            tt(false);
        })

        .on('click', '.fancybox-inner a[rel="buy"]', function () { // ссылка на товар внутри всплывающего окна
            $('.fancybox-inner h1, .fancybox-inner #etalage li, .fancybox-inner #good_desc').prepend('<i class="load"></i>');

            $('.fancybox-inner tr').removeClass('a');
            $('.fancybox-inner .buy input').val(0);
            var row = $(this).closest('tr');
            row.addClass('a').find('input').val('1');

            var text_price = row.find('td.price span').contents()[0].data;
            var selected_price = parseFloat(text_price.replace(/ /g,""));
            var qty_input = row.find('input[id^="qty_"]');
            var in_stock = qty_input.length;

            $.get($(this).prop('href'), function (data) {
                var j = $(data);
                $('.fancybox-inner h1').replaceWith($('h1', j));
                $('.fancybox-inner #etalage').replaceWith($('#etalage', j))
                $('.fancybox-inner #good_desc').replaceWith($('#good_desc', j));

                if (!isNaN(selected_price) && in_stock) {
                    total = 1;
                    pricetotal = selected_price;
                    row.find('input[id^="qty_"]').attr('value', 1);
                    $('#total').text(1);
                    $('#pricetotal').html( format_price(selected_price) );
                } else {
                    total = 0;
                    pricetotal = 0;
                    $('#total').text('0');
                    $('#pricetotal').html(0 + '<small>р.</small>');
                    $('#pricetotal').closest('tfoot').removeClass('a');
                }
                $.when(
                    $('.fancybox-inner input[id^="qty_"]').attr('oldval', 0).val(0),
                    $('.fancybox-inner .dec').addClass('min-zero')
                ).then(                    
                    qty_input.attr('oldval', 1).val(1), qty_input.parent().find('.dec').removeClass('min-zero')                    
                );
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
        .on('click', 'tfoot.totals a.c', function (e) { // положить в корзину всё
            $(this).closest('form').submit();
            e.stopPropagation();
            return false;
        })
        .on('click', 'a.c', function () { // положить в корзину один товар
            var id = $(this).attr('rel'), inp = $('#qty_' + id), q = inp.val();
            if (q == 0 || q == undefined) q = 1;
			
			var p = {};
			p[id] = q;
			addToTheCart(p);

            //RR
            if(typeof(RetailRocket) != 'undefined') { // включено RR - сообщаем им о том что положили в корзину
                try {
                    var slider = $(this).closest('.rr_slider');
                    if (slider.length) {
                        rrApi.recomAddToCart(id, slider.attr('data-func'))
                    } else {
                        rrApi.addToBasket(id);
                    }
                } catch(e) {}
            }

            $.post('/product/add', 'one=1&qty[' + id + ']=' + q, function (data) {
                if ($('#cart-wrap').length) {
                    location.reload();
                }
                $('#cart').replaceWith(data);
                place_cart();
                inp.val(0);
                retotal(inp);                
                
                //findologic
                if(typeof(_paq) != 'undefined') {
                   _paq.push(['addEcommerceItem',
                        id,
                        "",
                        [],
                        inp.attr('price'),
                        q   
                    ]);

                    _paq.push(['trackEcommerceCartUpdate', $('#cart-total').data('price')]);
                    _paq.push(['trackPageView']);
                }
                /* ? $('#cart-wrap').length > 0
                    $.get('/personal/basket_ajax', {}, function (html) {
                        var cartbox = $('#cart_wrap');
                        cartbox.html(html);
                        $('input:checkbox').checkbox();

                    });
                */
            });

            return false;
        })

        .on('click', 'ul > li > a.toggler', function () { // togglers in lists
            var lis = $(this).closest('ul').find('li'), q = $(this).attr('rev');
            if ( ! q) {
                q = lis.filter(':hidden').length;
                $(this).attr('rev', q);
            }

            lis.each(function(i) {
                if ( i >= (lis.length - q - 1) && i < lis.length - 1) {
                    $(this).toggle('fast');
                }
            });
            if ($(this).hasClass('up')) {
                $(this).removeClass('up').text('+ Показать ещё ' + q);
            } else {
                $(this).addClass('up').text('- Скрыть ' + q);
            }
            $.post('/toggle_state', { section: $('#search_section').val(), mode: $('#search_mode').val(), query: $('#search_query').val(), rel: $(this).attr('rel') });
        })

        .on('click', 'a.tognext', function () { // togglers in delivery zones
            $(this).next('div').toggleClass('hide');
            return false;
        })

        .on('click', 'a.toggler.abbr', function () { // togglers
            $('#' + $(this).attr('rel')).toggleClass('hide');
            return false;
        })

        .on('mouseenter', 'abbr, a.real-discount', function () { // tooltip show
            var a = $(this).attr('abbr');
            if (a || ! $(this).is('a')) tt(this, a ? a : $('#abbr').attr('abbr')); // do not show standart text in discount tooltips
        })
        .on('mouseleave', 'abbr, a.real-discount', function () { // tooltip hide
            tt(false);
        })

        .on('click', '#ff > strong', function () { /* фильтры - открытие-закрытие */
            if ( ! $(this).find('a').length) {
                $(this).next().toggle('fast');
                $(this).toggleClass('off');
            }
        })
        .on('click', '#ff label[data-url]', function () { /* меню на месте фильтров */
            location.href = $(this).data('url');
            return false;
        })
        .on('click', '#menu.ajax a, #list_props a', function() { /* меню фильтров  - переходы без перезагрузки всей страницы */

            if ($(this).hasClass('empty') && ! $(this).hasClass('checked')) { // запрет выбора заведомо пустых фильтров
                return false;
            }

            var url = $(this).attr('href');

            if (url && url != '#') {
                reload_section(url);
            }
            return false;
        })

        .on('touchstart mousedown', 'div.range span.line i', function (event) { // бегунок от-до

            var cssProp, css1Prop, inp,
                mode = $(this).hasClass('min') ? 'left' : 'right';

            if (mode == 'left') {

                cssProp = 'marginLeft';
                css1Prop = 'marginRight';
                inp = 'input.min';

            } else {

                cssProp = 'marginRight';
                css1Prop = 'marginLeft';
                inp = 'input.max';
            }

            var line = $(this).parent(), ratio = parseFloat($(this).closest('div.range').attr('rel')), initX,
                key = $(line).attr('rev'), link_id = $(line).attr('rel'), digits = parseInt($(this).closest('div.range').attr('data-digits'));
            if (event.type == "touchstart") {
                var touch = event.originalEvent.touches[0] || event.originalEvent.changedTouches[0];
                initX = (touch.clientX);
            } else {
                initX = event.pageX;
            }

            var initP = parseInt(line.css(cssProp));
            var maxP = line.parent().width() - parseInt(line.css(css1Prop)) - 18;
            var input = line.closest('div.range').find(inp);

            var tm = function (epagex) {

                var p, v;
                $('body').css('cursor', 'pointer');

                if (mode == 'left') {

                    p = Math.max(0, Math.min(initP + epagex - initX, maxP));
                    v = parseFloat(input.attr('rel')) + ratio * p;

                } else {

                    p = Math.max(0, Math.min(initP + initX - epagex, maxP));
                    v = parseFloat(input.attr('rel')) - ratio * p;
                }

                line.css(cssProp, p + 'px');
                input.val(v.toFixed(digits));
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
                .one('touchend mouseup', function (event) {
                    $('body')
                        .css('cursor', 'default')
                        .unbind('mousemove')
                        .unbind('touchmove')
                        .unbind('selectstart');

                    var inputs = line.parent().parent().find('input'), mi = inputs.eq(0), ma = inputs.eq(1);

                    var from = parseFloat(mi.val().replace(/[^0-9-\.]/g, ''));
                    if (isNaN(from)) {
                        from = parseFloat(mi.attr('rel'));
                    }
                    var to = parseFloat(ma.val().replace(/[^0-9-\.]/g, ''));
                    if (isNaN(to)) {
                        to = parseFloat(ma.attr('rel'));
                    }

                    $('#' + link_id).val(from + '-' + to);
                    ajax_sphinx(key, link_id);
                    return false;
                });

            return false;
            //event.stopPropagation();
            //event.preventDefault();
        })
        .on('keyup', '.range input', function(e) { // отправка ranges если вбить цифры вручную и нажать enter
            if (e.which == 13){
                var parent = $(this).parent(),
                    line = parent.find('.line'),
                    inputs  = parent.find('input'),
                    from = inputs.eq(0).val().replace(/[^0-9-]/g, ''),
                    to = inputs.eq(1).val().replace(/[^0-9-]/g, ''),
                    key = $(line).attr('rev'),
                    link_id = $(line).attr('rel');

                $('#' + link_id).val(from + '-' + to);
                ajax_sphinx(key, link_id);
                return false;
            }
        })
        .on('click', 'a.toreg', function () {     // открыть форму регистрации
            $(window).scrollTop(0);
            $('#userpad a[rel=user-registration]').click();
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

        .on('change', '#list_props #sort', function () { // переключение сортировок
            ajax_sphinx('s', 'sort');
        })
        .on('change', '#list_props #page', function () { // или числа на странице
            ajax_sphinx('pp', 'page');
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
            var href, review_container, quickview = 0;
            if ($(this).hasClass('quickview')) {
                href = '/review/product/' + $('#goods .a>.vt>a').attr('name');
                review_container = '.fancybox-inner .review #load_reviews';
                quickview = 1;
            } else {
                href = $('#reviews_for').val() == 0 ? '/review/product/' + $('#good_id').val() : '/review/group/' + $('#group_id').val() + '/' + $(this).attr('rel');
                review_container = '#load_reviews';
            }
            $(this).html('<i class="load"></i>');
            $.post(href, {page: $(this).attr('rel'), ajax: 1, is_quickview: quickview}, function (content) {
                $(review_container).replaceWith(content);
            });
            return false;
        })
        
        //подгрузка отзывов при быстром просмотре
        .on('click', '.fancybox-inner #reviews', function () {
            var href = '/review/product/' + $('#goods .a>.vt>a').attr('name');
            $.post(href, {page: $(this).attr('rel'), ajax: 1, is_quickview: 1}, function (content) {
                $('.fancybox-inner .review .load').replaceWith(content);
            });
            return false;
        })

        // слайдеры
        .on('click', '.slider i', function () {
            var slider = $(this).closest('.slider'),
                url = $(slider).attr('data-url'),
                page = $(slider).attr('data-page');

            if ($(this).index() == 0) page--;
            else page++;

            $.get(url + '?page=' + page, function (data) {
                $('ul', slider).replaceWith(data);
                $(slider).attr('data-page', page);
                addfancy($('a.fastview', slider));
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
            } else {
                u = s;
            }
            $(this).attr('href', prep + u);
        });
        addfancy("a[rel='ajax']");
        init_ranges();
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
	
	$('body').on('click', 'a.google-good', function() {
		
		var productObj = window['googleGood_' + $(this).attr('data-id')];
		var href = $(this).attr('href');
		
		if (productObj) {
			
			window.dataLayer = window.dataLayer || [];
			dataLayer.push({
                ecommerce: {
                    click: {
				        products: [productObj]
				    }
			    },
			    eventCallback: function() {
				    document.location = href
			    },
                event: 'productClick'
			});
			//return false; - убрал чтобы был переход по клику
		}
	});
        
    //на странице товара - активация вкладки с отзывами    
    initFeedbacksTab();
    initToTopButton();

    //виджеты соцсетей
    if ($('body').hasClass('main')) {
        $.ajax({
            type: "GET",
            url: '//vk.com/js/api/openapi.js?121',
            dataType: "script",
            cache: true,
            success: function() {
                VK.Widgets.Group("vk", { mode: 0, width: "300", height: "200"}, 39518389);
            }
        });

        $('#fb').append('<iframe src="//www.facebook.com/plugins/likebox.php?href=http%3A%2F%2Fwww.facebook.com%2Fmladenec.ru&amp;width=300&amp;height=210&amp;colorscheme=light&amp;show_faces=true&amp;border_color&amp;stream=false&amp;header=true" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:300px; height:210px;" allowTransparency="true"></iframe>');
    }
});

var executeGoodsTopBar = function() { // история просмотров товаров
	
	if( allowGoodsTopBar && $(window).width() > (994 + 120 * 2)) {
		var $uh = $('<div id="user_history"><i class="load"></i></div>');
		var w = Math.floor(($(window).width() - 994 - 20 * 2) / 2);
		w = Math.min(w, 200);

		$('#content').append($uh);
		$uh.css({
			width: w + 'px',
			right: -(w + 10) + 'px'
		});

        setTimeout(function() {
			$uh.animate({ opacity: 1 }, 500);
		}, 500);

		$.get('/user/goods_ajax', function(data){
			if (data == "") $uh.hide();
			else {
                $uh.empty().append(data);
			}
		});
	}
};

function initFeedbacksTab() {
    var hash = window.location.hash;
    if(hash == '' || $('#reviews').length == 0) return;
    if(hash == '#reviews') {
        $('#reviews').click();
    }    
}

function initToTopButton() {
    $(window).scroll(function(e){
        var top_pos = $(document).scrollTop();
        var client_height = $(window).height();    
        
        if($('.to-top-page').length>0){
            if(top_pos>client_height) {
                $('.to-top-page').fadeIn();
            } else {
                $('.to-top-page').fadeOut();
            }
        }
    });
     
    if($('.to-top-page').length>0){
        $('.to-top-page').click(function(e){
            e.preventDefault();
            $('body').scrollTo(0,{
                duration:'fast'
            });
        })
    }
}

function initCartTabs() {
    if($('.cart-tabs').length == 0) return false;
    
    $(document).on('click', '.cart-tabs li', function(e){
        e.preventDefault();
        var new_active = $(this);
        var active = $('.cart-tabs li.active').data('show-tab');
       
        if($(new_active).data('show-tab') == 'ozon-terminals-tab') {                 
            $('#ship_date').text('Самовывоз, сроки доставки сообщит менеджер после приёма заказа');
            $('#ship_time').text('');
            $('#ship_price').text('сообщит менеджер');
            $('#delivery_type').val(5);
            if( $('#ozon-terminals-map').length > 0 ) getOzonPrice();
            $('#addr-common input, #addr-courier input, #addr-region  input').prop('disabled',true);
            $('#terminal_user_data input').prop('disabled',false);
        } else if($(new_active).data('show-tab') == 'courier-tab') {
            cart_recount();
            $('#addr-common input, #addr-courier input, #addr-region  input').prop('disabled',false);
            $('#terminal_user_data input').prop('disabled',true);
        }
             
        $.when(
            $('.cart-tabs li.active').removeClass('active'),
            $('#'+active).removeClass('active')
        ).then(
            new_active.addClass('active'),
            $('#'+$(new_active).data('show-tab')).addClass('active')         
        );
    });
    
    $('input[type=radio]', $('.ozon-terminals-choose')).mladenecradio({
            onClick: function(el) {
                var elem = $('[rel="ozon_terminal"].checked input');
                if(terminalMap != null && typeof(elem.data('lat')) != 'undefined') {
                    terminalMap.setCenter([elem.data('lat'), elem.data('lng')], 14);                    
                    for(var i in markers) {
                        if(i != elem.data('index')) markers[i].options.set('preset', 'islands#blueIcon');
                    }
                    markers[elem.data('index')].options.set('preset', 'islands#redIcon');
                    
                    getOzonPrice();
                }
            }
    }) 
}
