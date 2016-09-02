{if not empty($sync)}{* удаление куки корзины на других доменах при чистке корзины *}
	{foreach from=Kohana::$hostnames item=conf key=h}
		<iframe src="//{$conf.host}/sync?clear=cart" height="0" width="0" frameborder="0" class="sync"></iframe>
	{/foreach}
{/if}

<script>
var recountTimeout = 0, jqXHR;

function cart_presents(val, inp) { {* выбор подарка в корзине *}
    var data = { };
    data[$(inp).attr('name')] = val;
    $.post('{Route::url('cart_presents')}', data);
}

function cart_recount() { {* функция пересчёта корзины *}
    if (recountTimeout) window.clearTimeout(recountTimeout);
    recountTimeout = window.setTimeout(function () {

        var o = $(".cart-recount-link");
        o.addClass("changed").html("Пересчитываем");
        o.append('<i class="load"></i>');

        $.post('{Route::url('cart_recount')}',
            $('#cart-form input, #sborka').serialize(),
            function(data) {
                if (data.cart) {
                    $('#cart-form').replaceWith(data.cart);
                    if (data.price_changed) {
                        addr.calcShip( $('#real-latlong').val() ); // ? пересчёт доставки
                    }
                    $('.cart-gift-radio').mladenecradio({ onClick: cart_presents });
                }
                setTimeout(function() {
                    $(".cart-recount-link").removeClass("changed").html("Пересчитать");
                }, 2000);
            }, 'json');
        return false;
    }, 333);// // запросы на пересчёт корзины - собираем в один за третьсекунды
}

var cit = [], k = [], r = [], suggestions = [];
{foreach from=ORM::factory('dpd_city')->order_by('name')->find_all()->as_array('id') key=k item=c}
cit['{$k}'] = "{$c->name|escape:html}";
suggestions.push({
    value: cit['{$k}'], data: {$k}
});
k['{$k}'] = {$c->region_id};
{/foreach}
{foreach from=ORM::factory('dpd_region')->order_by('id')->find_all()->as_array() item=r}
r[{$r->id}] = "{$r->name|escape:html}";
{/foreach}

var form, realAddr, oldAddr;

function fillReg(city) { // Находим код региона по выбранному городу, в конце вызываем геокодер по адресу
    city = $.trim(city);
    var reg = -1;
    for (var i in cit) {
        if (cit[i] == city) {
            $('#dpd_city_id').val(i);
            reg = k[i];
            break;
        }
    }

    $('#real-zone-name').text('не определена');
    $('#ship_price').text('сообщит менеджер');
    $('#ship_date').text('Стоимость и сроки доставки сообщит менеджер после приёма заказа');
    $('#ship_time').text('');

    if (reg == 77) { // точно курьерская

        delivery.modeCourier();

    } else if (reg != 50) { // доставка транспортной компанией

        delivery.modeRegion(reg, i);

    }
    addr.geocoder();
}
        
var delivery = {
    init: function() {
        form = $('#addr');
        realAddr = $('#real-address');
        oldAddr = $('#address');

        $('input[type=radio]', oldAddr).mladenecradio({ // выбор из старых адресов
            onClick: function(value) {
                if (value > 0) {
                    var latlong = addr.cached[value].latlong;
                    $('input[name=address_id]', form).prop('checked', false); // не новый адрес
                    $('#chooser').remove();

                    for(var n in addr.cached[value])
                    {
                        switch (n) {
                            case 'lift':
                                $('input', '#real-' + n).prop('checked', addr.cached[value][n]);
                                $('input', '#real-' + n).closest('label').toggleClass('checked', addr.cached[value][n]);
                                break;

                            case 'latlong':
                            case 'zone':
                            case 'comment':
                            case 'correct_addr':
                                $('#real-' + n).val(addr.cached[value][n]);
                                break;

                            default:
                                $('input,textarea', '#real-' + n).val(addr.cached[value][n]);
                                break;
                        }
                    }

                    $('#last_geocode').val('');
                    if ( latlong == '0' || latlong == '' || latlong == 'TRANSPORT' || ! (addr.cached[value].correct_addr || addr.cached[value].approved)) { // ищем координаты по адресу

                        $('#addr-map img').attr('src', '/i/load.gif');
                        fillReg($('#city').val());

                    } else { // есть координаты и корректный адрес - ставим координаты адреса на карте, по зоне в адресе считаем доставку
                        addr.calcShip(addr.cached[value].latlong);
                    }

                } else if(value == "-1" ) { // определение местоположения

                    tableStatus.slideUp(function() {
                        addr.startForm();
                        var timeout = setTimeout(function() {
                            $('#alert').html('Пожалуйста, разрешите доступ к вашему местоположению').slideDown();
                        }, 1000);
                        navigator.geolocation.getCurrentPosition(
                            function (position) {
                                clearTimeout(timeout);

                                if( $('#alert').height() ) {
                                    $('#alert').slideUp();
                                }

                                $.get('//geocode-maps.yandex.ru/1.x/', {
                                    geocode: position.coords.longitude+','+position.coords.latitude,
                                    format: 'json',
                                    callback: 'jsonp_callback'
                                }, function(data) {
                                    addr.parseGeocoder(data, true);
                                }, 'jsonp');
                            },
                            function displayError(error) {
                                var errors = {
                                    1: 'Доступ к местоположению не разрешен',
                                    2: 'Местоположение недоступно',
                                    3: 'Превышен интервал ожидания'
                                };

                                $('#alert').html(errors[error.code] + '. Введите адрес').slideDown(function() {
                                    addr.startForm();
                                    setTimeout(function() {
                                        $('#alert').slideUp();
                                    },5000);
                                });
                            },
                            { enableHighAccuracy: true, timeout: 1000, maximumAge: 0 }
                        );
                    });
                }
            }
        });

        $('input[type=radio]', oldAddr).first().parent('label').click();
                
        var cd = $("#cart-delivery");
        $("input[type=checkbox]", cd).mladenecbox({ size: 21});
        $("input[type=radio]", cd).mladenecradio();
        $('input[type=tel]', cd).attr('placeholder', 'Телефон');
        
        $("#lift, #order_sms input, input[name=no_ring], input[name=no_call]").mladenecbox();     

        $.getScript('/j/jquery.autocomplete.js', function() {
            $("#city").autocomplete({
                lookup: suggestions,
                deferRequestBy:3,
                minChars:1,
                maxHeight:300,
                onSelect: function(value, data) {
                    fillReg(value);
                }
            });
        });
        
        function city_region_init() {
            var c = $('#city'), city = c.val();
            if ( ! city) {
                c.removeClass('ok').addClass('error').attr('error', 'Не указан город');
                return false;
            }
            fillReg(city);
        }

        city_region_init();

        $(".cart-gift-radio").mladenecradio({ onClick: cart_presents });
    },
    modeCourier: function () { // режим курьерской доставки
        $('input[name=delivery_type]').val({Model_Order::SHIP_COURIER});
        $('#addr-courier').removeClass('hide');
        $('#addr-region').addClass('hide');
    },
    modeRegion: function (reg) { // режим доставки в регион
        $('input[name=delivery_type]').val({Model_Order::SHIP_SERVICE});
        $('#real-zone-name').text('{Model_Zone::NAME_REGION}');

        $('#addr-courier').addClass('hide');
        $('#addr-region').removeClass('hide');
        if (r[reg]) $("#zone-real-name").text(r[reg]);
    }
};

var addr, loader, bounds, dx, dy;

String.prototype.noSpaces = function() { // функция чистки строк для сравнения городов и улиц
    return this.replace(/\s+/g, '').toLowerCase().replace('улица', '');
}

$(document).ready( function() {
    // скрипты для обслуживания корзины все здесь складывать - не загружать c кусочками корзины
    loader = $('<i class="load"></i>');
    bounds = [[55.490667, 37.182743], [56.01074, 37.964969]];
    dx = bounds[1][1] - bounds[0][1];
    dy = bounds[1][0] - bounds[0][0];

    addr = {
        cached: { },

        chooser: function(data, noloader) {
            var gui = $('<div id="chooser"></div>').append(data).append(noloader ? '' : loader);
            $('#addr-map').empty().append(gui);
        },

        calcMkad: function (mkad, dest, free_delivery) { // расчет расстояния от мкад до точки

            if (typeof(ymaps) == 'undefined') {

                addr.chooser('Определяем расстояние до МКаД');
                $('#mkad').val('?');
                $.getScript('http://api-maps.yandex.ru/2.1/?lang=ru_RU&coordorder=longlat', function() { // грузим Я-карты / долгота,широта
                    //addr.calcMkad(mkad, dest, free_delivery); // как погрузим - вызовем ещё раз
                });

            } else {

                ymaps.ready(function () { // покажем карту с путём до МКАД

                    $('#addr-map').empty();

                    var myMap = new ymaps.Map('addr-map', {
                        center: dest,
                        zoom: 10,
                        controls: ['zoomControl']
                    }, { minZoom: 8 });

                    ymaps.route([mkad, dest], { mapStateAutoApply: true })
                    .then(function (rr) {
                        myMap.geoObjects.removeAll();

                        var points = rr.getPaths().get(0).getSegments(), length = 0, polyline, last_point = 0, coords;

                        for (var x in points.reverse()) { // с конца ищем мкад
                            coords = points[x].getCoordinates().reverse();

                            if (x == 0) {
                                myMap.geoObjects.add(new ymaps.Placemark(coords[0], { iconContent: 'Доставка' }, { preset: 'islands#blackStretchyIcon' }));
                            }

                            if (last_point && points[x].getStreet() == 'МКАД') {
                                myMap.geoObjects.add(new ymaps.Placemark(last_point, { iconContent: 'МКАД' }, { preset: 'islands#blackStretchyIcon' }));
                                break;
                            }

                            length += points[x].getLength();
                            if (last_point) {
                                polyline = new ymaps.Polyline([last_point, coords[0]], { }, { strokeColor: '#205f7e', strokeWidth: 5 });
                                myMap.geoObjects.add(polyline);
                            }
                            polyline = new ymaps.Polyline(coords, { }, { strokeColor: '#205f7e', strokeWidth: 5 });
                            last_point = coords[coords.length - 1];
                            myMap.geoObjects.add(polyline);
                        }
                        myMap.setBounds(myMap.geoObjects.getBounds());

                        var km = Math.ceil(length / 1000), $action = $('#mkad_action');
                        $('#mkad_real').val(km);

                        $action.val(0);
                        if (free_delivery) { // акция на бесплатную доставку
                            $action.val(1);
                            km = max(0, km - free_delivery);
                        }
                        $('#mkad').val(km);
                        $('#real-zone-name').append(' (' + km + 'км от МКАД)');
                        addr.showShipPrice();
                    });
                });
            }
        },

        showShipPrice: function() {

            var price = false, tariff = 0, km = 0, dt = $('#delivery_type').val(), checked;

            if (dt == {Model_Order::SHIP_COURIER}) {
                checked = $('input[name=ship_time]:checked');
                if (checked.length) {
                    tariff = parseInt(checked.attr('rel'), 10);
                    km = parseInt($('#mkad').val(), 10);

                    if ( ! isNaN(km) && ! isNaN(tariff)) {
                        price = km * {Model_Order::PRICE_KM} + tariff;
                    }
                }
            }

            if (dt == {Model_Order::SHIP_SERVICE}) {

                checked = $('input[name=comment]:checked');
                if (checked.length) {
                    price = parseInt(checked.val().split(':').pop(), 10);
                    if (isNaN(price)) price = false;
                }
            }

            $('#ship_price').html(price === false ? 'сообщит менеджер' : format_price(price));
        },

        calcShip: function(latlong) { // установить координаты доставки, рассчитать цену
            $('#ship_price').text('сообщит менеджер');
            
            if($('.cart-tabs').length != 0) {
                var active = $('.cart-tabs li.active').data('show-tab');
                if(active == 'ozon-terminals-tab') { 
                    getOzonPrice();
                    return true;
                }
            }
            
            $('#real-latlong').val(latlong);
            $('#addr-map').empty().append('<img src="http://static-maps.yandex.ru/1.x/?size=450,450&l=map&z=16&ll=' + latlong + '&pt=' + latlong + ',flag" />');

            // по координатам определяем зону
            var st = $('#ship_time'), sd = $('#ship_date');
            st.empty();
            st.append('<i class="load"></i>');
            sd.empty();
            sd.append('Определяем варианты доставки');

            var pattern = /[0-9]+/g;
            var total_prod = new Object();
            var main = [];
            $("table#cart_goods tbody tr").each(function(){
                var id_val = $(this).attr("id");
                if(id_val) {
                    var attr_id = id_val.match(pattern);

                    var val_qty = $('#qty_' + attr_id).val();
                    var val_price = $('#qty_' + attr_id).attr('price');
                    var total_price = val_qty * val_price;

                    if (attr_id != null && !isNaN(total_price)) {
                        total_prod = new Object();
                        total_prod.id = attr_id[0];
                        total_prod.price = total_price;
                        main.push(total_prod);
                    }
                }
            });

            $.get('{Route::url('delivery_zone')}',
                { latlong: latlong, dpd_city_id: $('#dpd_city_id').val(), city: $('#city').val(), mkad: $('#mkad').val(), free_delivery: main},
                function (data) {

                    $('#real-zone').val(data.zone_id);
                    $('#real-zone-name').text(data.zone);

                    sd.empty().append(data.ship_date);
                    st.empty().append(data.ship_time);

                    if (data.zone_id) { // доставка курьером - выбор времени

                        delivery.modeCourier();

                        if (data.closest) { /* это доставка заМкаД - расчёт МКаД*/
                            $('#mkad').val('?');
                            addr.calcMkad(data.closest, latlong.split(','), data.free_delivery);
                        } else {
                            $('#mkad_action').val(0);
                            $('#mkad').val(0);
                        }
                        $('input', st).mladenecradio({
                            onClick: function (val, item) {
                                $('#watch').css({ backgroundImage: item.attr('data-grad') });
                                addr.showShipPrice();
                            }
                        });

                    } else { // доставка DPD - выбор тарифа

                        delivery.modeRegion();

                        $('input', sd).mladenecradio( { onClick: addr.showShipPrice });
                        $('input', st).mladenecradio( { onClick: addr.showShipPrice });

                    }

                    addr.showShipPrice()

                }, 'json');
        },

        findOnMap: function(coords) {

            if (typeof(ymaps) == 'undefined') {

                addr.chooser('Загрузка карт');
                $('#mkad').val(0);
                $.getScript('http://api-maps.yandex.ru/2.1/?lang=ru_RU&coordorder=longlat', function() { // грузим Я-карты / долгота,широта
                    addr.findOnMap(coords); // как погрузим - вызовем ещё раз
                });

            } else {

                $('#addr-map').empty();
                ymaps.ready(function () { // покажем карту с путём до МКАД

                    var myMap = new ymaps.Map('addr-map', {
                        center: coords.split(','),
                        zoom: 12,
                        controls: ['zoomControl']
                    }, { minZoom: 8 });

                    if ( ! myMap.clickEvent) {

                        myMap.events.add('click', function (e) {

                            $('#real-latlong').val('Укажите на карте точку доставки');

                            if (myMap.getZoom() < 14) {
                                alert('Приблизьте карту. Затем, кликните на Вашем доме');
                                return false; // only 14 or more for house!
                            }

                            var _coords = e.get('coords');

                            if (confirm('Вы подтвержаете, что адрес доставки ' + "\n"
                                + $('#city').val() + ', ' + $('#street').val() + ', ' + $('#house').val() + "\n"
                                + 'находится здесь?')) {


                                $('#real-correct_addr').val(0); // адрес считаем некорректным!
                                addr.calcShip(_coords.join(','));
                            }

                            return false;
                        });
                    }
                });
            }
        },

        setText: function(text) { // проставить поля адреса (город, улица, дом)

            var a = text.split(', ');
            $('#city').val($.trim(a.slice(0, a.length - 2).join(', ')));
            $('#street').val($.trim(a[a.length - 2]));
            $('#house').val($.trim(a[a.length - 1]));
        },

        check: function() {
            var checked = true;
            realAddr.find('input[error]').each(function() {
                if ($(this).val() == '') {
                    $(this).addClass('error');
                    checked = false;
                }
            }).one('keyup', function() {
                $(this).parent().parent().removeClass('error');
            });
            return checked;
        },

        choose: function(addresses, coords) { /* найдено несколько похожих адресов - выбор */

            $('#real-correct_addr').val(0); // сбрасываем корректность
            $('#real-latlong').val(coords); // но ставим координаты

            var chooser = $('<div>'
                + '<p>Адрес не&nbsp;найден на&nbsp;карте.</p>'
                + (addresses.length ? '<h3>Исправить адрес:</h3>' : '')
                + '</div>'
            );

            if (addresses.length) {
                var ul = $('<ul></ul>'), addr_data, a;
                for (var i in addresses) {
                    addr_data = addresses[i].text.split(', ').reverse(); // город, улица, дом
                    a = $('<a class="do" rel="' + addresses[i].coords.replace(' ', ',') + '">' + addr_data.join(', ') + '</a>').click(function () {

                        $('input', realAddr).val('').prop('checked', false);
                        $('input[name=address_id]', form).prop('checked', true); // новый адрес
                        $('#mkad').val(0);
                        $('label.checked', oldAddr).removeClass('checked');

                        $('#real-correct_addr').val(1);

                        addr.setText($(this).text());
                        addr.calcShip($(this).attr('rel'));

                    });

                    ul.append($('<li></li>').append(a));
                }
                chooser.append(ul);
                chooser.append('<h3 class="mt fl">или</h3>');
            }

            a = $('<a class="butt small fr mt" rel="' + coords + '">Указать адрес вручную</a>').click(function() {
                addr.findOnMap($(this).attr('rel')); // переход в режим "укажи точку"
            });
            chooser.append(a);
            addr.chooser(chooser, true);
        },

        parseGeocoder: function(data) { /* прочитать ответ геокодера, если найден - показать на карте */
            var md, coords, first_point = false;
            var isReverse = arguments[1] ? arguments[1]: false;

            if ( ! isReverse) $('#real-correct_addr').val(0);

            if (data.response.GeoObjectCollection.featureMember) {
                var found = data.response.GeoObjectCollection.featureMember, toSend = [], parts, city = $.trim($('#city').val()), street = $.trim($('#street').val());
                for(var f in found) {
                    md = found[f].GeoObject.metaDataProperty.GeocoderMetaData;
                    parts = md.text.split(', ').reverse(); // дом, улица, [район, ] [область, ] город, страна
                    md.text = parts.slice(0, parts.length - 1).join(', ');
                    coords = found[f].GeoObject.Point.pos;
                    first_point = first_point || coords; // первая найденная точка будет стартовым положением карты при указании дома вручную

                    if (md.precision == 'exact') { // найден точный адрес

                        // если написание города и улицы совпадает с тем что в адресе с точностью до пробелов или адрес только один - автозамена
                        if (found.length == 1 || (street.noSpaces() == parts[1].noSpaces()
                            && city.noSpaces() == parts.slice(2, parts.length - 1).reverse().join(', ').noSpaces())) {

                            $('#real-correct_addr').val(1);

                            addr.setText(md.text.split(', ').reverse().join(', ')); // ставим точный адрес, порядок наоборот
                            addr.calcShip(coords.replace(' ', ','));
                            return true;

                        } else {

                            toSend.push({
                                text: md.text,
                                coords: coords
                            });

                        }

                    } else if (md.kind == 'house') { // найдены дома рядом - предложим выбор

                        toSend.push({
                            text: md.text,
                            coords: coords
                        });

                    }
                }
                first_point = first_point || '37.61556,55.75222'; // москва, центр
            }

            addr.choose(toSend, first_point.replace(' ', ',')); // предложим выбор дома или указание точки вручную

        },

        geocoder: function() { // определить координаты по введённому адресу
            var
                city = $.trim($('#city').val()),
                street = $.trim($('#street').val()),
                house = $.trim($('#house').val()),
                last = $('#last_geocode'),
                to_find = 'Россия, ' + city + ', ' + (street.length < 5 ? 'улица ' : '')  + street + ', дом ' + house;

            if (to_find.length < 9) return;
            if (last.val() == to_find) return;

            last.val(to_find);
            addr.chooser('Ищем ' + to_find);
            $('#real-latlong').val('');
            $('#mkad').val(0);
            $('#delivery_type').val(0);

            $.get('//geocode-maps.yandex.ru/1.x/', {
                geocode: to_find,
                format: 'json',
                spn: dx + ',' + dy ,
                ll: (bounds[1][1] - dx / 2) + ',' + (bounds[1][0] - dy / 2),
                rspn: 0,
                callback: 'jsonp_callback'
            }, function(data) {
                addr.parseGeocoder(data);
            }, 'jsonp');
        }
    };

    {if $user}
    {foreach from=$user->address()|default:'' item=a name=a}
    addr.cached[{$a->id}] = {
        city: "{$a->city|escape:javascript|trim}",
        street: "{$a->street|escape:javascript|trim}",
        house: "{$a->house|escape:javascript|trim}",
        enter: "{$a->enter|escape:javascript|trim}",
        domofon: "{$a->domofon|escape:javascript|trim}",
        floor: "{$a->floor|escape:javascript|trim}",
        lift: "{$a->lift|escape:javascript|trim}",
        kv: "{$a->kv|escape:javascript|trim}",
        zone: "{$a->zone_id|trim}",
        mkad: "{$a->mkad|trim}",
        comment: "{$a->comment|escape:javascript|trim}",
        correct_addr: {$a->correct_addr|default:0},
        approved: {$a->approved|default:0},
        latlong: "{$a->latlong|trim}"
    };
    {/foreach}
    {/if}


    delivery.init();

    $(document).on('click', '#make_order', function() { // корзину по enter не отсылать! -по клику
        $(this).closest('form').submit();
    });

    $(document).on('change', '#sborka', function(){
        cart_recount();
    });

    $(document).on('click', '#addr_add', function() { // добавить новый адрес - чистим поля
        $('input', realAddr).val('').prop('checked', false);
        $('input[name=address_id]', form).prop('checked', true); // новый адрес
        $('#mkad').val(0);
        $('label.checked', oldAddr).removeClass('checked');
        addr.chooser('Пожалуйста, заполните адрес доставки', true);
    });

    $(document).on('click', '.dec', function() { // -
        var price = $(this).siblings('input').attr('price');
        var attr_id = $(this).siblings('input').attr('id');
        var pattern = /[0-9]+/g;
        var id_val = attr_id.match(pattern);

        window.flocktory = window.flocktory || [];
        window.flocktory.push(['removeFromCart', {
            item: {
                id: id_val, // product id
                count: 1 // quantity of this product removed
            }
        }]);
    });

    $(document).on('click', '.inc', function() { // +
        var price = $(this).siblings('input').attr('price');
        var attr_id = $(this).siblings('input').attr('id');
        var pattern = /[0-9]+/g;
        var id_val = attr_id.match(pattern);

        window.flocktory = window.flocktory || [];
        window.flocktory.push(['addToCart', {
            item: {
                "id": id_val, // product id
                "price": price, // product price
                "count": 1 // quantity of this product  added
            }
        }]);
    });

    if (navigator.geolocation) {
        // $('#address').append('<label><input type="radio" name="address_id" value="-1" /> Текущее местоположение</label>');
    }

    $(document).on('blur', '#city, #street, #house', function() {
        var aid = $('input[name=address_id]').val();
        if (aid && addr.cached != undefined && addr.cached[aid]) { // снять выбор старого адреса
            if (addr.cached[aid].city != city || addr.cached[aid].street != street || addr.cached[aid].house != house) {
                $('label', oldAddr).removeClass('checked');
                $('input[name=address_id]', realAddr).prop('checked', true);
            }
            $('#real-zone').val('');
            $('#real-latlong').val('');
            $('#mkad').val('');
        }

        fillReg($('#city').val());
    });

    $(document).on('click', '.cart-recount-link',  function() { // пересчёт корзины по кнопке

        cart_recount();

        return false;
    });

    $(document).on('click', '.pencil button', function(event) { // комменты к товарам
        if ( 0 === $("#pencilator").size()) { // Диалог НЕ открыт
            $(event.target).pencilator();
        } else {
            $("#pencilator").show();
        }

        return false;
    });

    $(document).on('click', '.cart-remove-link', function() { // удаление товара из корзины

        var q = $(this), id = q.attr("data-id");
        var quntity = $('#qty_'+id).val();

        if (confirm("Удалить товар из корзины?")){

            var timeout = setTimeout(function(){
                q.replaceWith(loader);
            }, 400);

            var productObj = window["googleGood_"+id];

            if (productObj) {
                dataLayer.push({
                    userId: uid,
                    event: "removeFromCart",
                    ecommerce: {
                        remove: {
                            products: [productObj]
                        }
                    }
                });
            }

            window.flocktory = window.flocktory || [];
            window.flocktory.push(['removeFromCart', {
                item: {
                    id: id, // product id
                    count: quntity // quantity of this product removed
                }
            }]);

            $.post(
                '{Route::url('cart_remove_good')}',
                { id: id},
                function(data){
                    clearTimeout(timeout);

                    if (data.cart) {
                        $('#cart-form').replaceWith(data.cart);
                        if (data.price_changed) addr.calcShip( $('#real-latlong').val() ); // ? пересчёт доставки
                    }
                }, 'json'
            );
        }
    });

    $(document).on('click', '.cart-clear-link', function(){ // очистка корзины

        if (confirm("Вы уверены, что хотите полностью очистить корзину?")) {

            $.post("{Route::url('cart_clear')}", function() {
                location.reload();
            });
        }
        return false;
    });

    $(document).on('click', '#button-send', function() { // кнопка и блок отправки заказа

        if(googleCheckoutStep) googleCheckoutStep('2');

        $.post("{Route::url('delivery_open')}", function(data) {
            var cd = $("#cart-delivery");
            if (data) {
                cd.html(data);
                delivery.init();
                if(googleCheckoutStep) googleCheckoutStep('3');
            }
        });
        return false;
    });

    $(document).on('click', '#ship_date .calendar label', function() { // клик на календаре - показать интервалы даты
        $(this).closest('tbody').find('label').removeClass('a');
        $(this).addClass('a');
        $(this).closest('table').find('thead th').text($(this).attr('title'));

        $.get('{Route::url('delivery_time')}', { // загружаем интервалы на дату
            latlong: $('#real-latlong').val(),
            zone: $('#real-zone').val(),
            date: $('input', this).val(),
            mkad: $('#mkad').val()
        }, function(data) {
            $('#ship_time').empty().append(data);
            $('#ship_time input').mladenecradio({
                onClick: function (val, item) {
                    $('#watch').css({ backgroundImage: item.attr('data-grad') });
                    addr.showShipPrice();
                }
            });
            addr.showShipPrice();
        });
    });

    $(document).on('click', '#ship_time label', function() { // клик на времени доставки - [меняем цену]
        addr.showShipPrice();
    });

    $(document).on('click', '#cart-coupon-remove', function() { // удаление купона из корзины
        $("#coupon").val('');
        cart_recount();
        return false;
    });

    $(document).on('click', '#cart-coupon', function() { // использование купона
        cart_recount();
        return false;
    });

    $(document).on('keyup', '#coupon', function(e) { // купон по enter
        if (e.which == 13) {
            $("#cart-coupon").click();
            return false;
        }
    });
});
</script>


<div id="cart-wrap">
    <h1>Корзина</h1>

{if empty($goods)}

    <p>Вы&nbsp;пока не&nbsp;положили в&nbsp;корзину ни&nbsp;одного товара. Оформление заказа без товаров не&nbsp;допускается.</p>

{else}

    {* #cart-form *}
    {include file="cart/goods.tpl" cart=$cart goods=$goods promo=$promo presents=$presents present_goods=$present_goods include=1}

    {foreach from=$goods item=good}
		{include file="google/click.tpl" good=$good}
	{/foreach}

	<script>
		window.ad_products = [
			{foreach from=$goods item=g name=n}
			{
			    id: "{$g->id}",   // required
			    number: "{$g->quantity}"
			}
            {if ! $smarty.foreach.n.last},{/if}
			{/foreach}
		];
    </script>

    {include file='common/retag.tpl' level=3}

    <script>
		function googleCheckoutStep() {
			var productObjs = [], productObj;
			var step = arguments[0] || "1";

			{foreach from=$goods item=good}
				productObj = window["googleGood_"+{$good->id}];
				if (productObj) productObjs.push(productObj);
			{/foreach}

            if(productObj) {
				window.dataLayer = window.dataLayer || [];
				dataLayer.push({
                    userId: uid,
                    ecommerce: {
					    checkout: {
                            actionField: { step: step },
                            products: productObjs
                        }
                    },
				    event: "checkout"
				});									
			}
		}
		googleCheckoutStep("1");
	</script>
</div>

{* #cart-delivery *}
    <div id="cart-delivery">
        {$delivery}
    </div>

{/if}

<div class="cart-slider">
{if $config->rr_enabled eq 1}
    {if $goods}
        <div class="cl rr_slider" title="С этим товаром покупают:" data-func="CrossSellItemToItems" data-param="{$goods|array_keys|implode:'_'}"></div>
    {else}
        <div class="cl rr_slider" title="Рекомендуем Вам:" data-func="PersonalRecommendation" data-param="{$smarty.cookies.rrpusid|default:''}"></div>
    {/if}
{*elseif isset($slider) && not empty($slider.goods)}

        <h2>{$slider.name}</h2>
        <div class="slider" rel="{Route::url("slide_set",["type" => $slider.method, "set_id" => $slider.id])}?page={$slider.page}">
            {if $slider.count|default:0 gt 5}
                <i></i>
            {/if}

            {include file="common/goods.tpl" goods=$slider.goods images=$slider.images price=$slider.price short=1 ga_list="cart"}

            {if $slider.count|default:0 gt 5}
                <i></i>
            {/if}
        </div>
*}
{/if}
</div>
