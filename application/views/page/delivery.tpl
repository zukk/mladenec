<script src="//api-maps.yandex.ru/2.1/?lang=ru-RU"></script>

<div class="half fl" style="width:300px;">
    <label class="l" for="city">Город</label><input name="city" id="city" value="{$city|default:'Москва'}" class="txt" />
    <label class="l" for="street">Улица</label><input name="street" id="street" value="{$street|default:''}" class="txt" />
    <label class="l" for="house">Дом <small>(например, 5к1)</small></label><input name="house" id="house" value="{$house|default:''}" class="txt" />
    <input type="button" value="Определить по адресу" class="butt small fl" id="check_addr" />

    <div class="cb mt">
        <label class="l"><strong>Зона доставки:&nbsp;</strong></label><input name="ship_zone" id="ship_zone" type="hidden" value="0" /><input name="mkad" type="hidden" value="0" /><abbr id="zone" abbr="Зона доставки определяется по координатам точки доставки.">Не определена</abbr>
        <br /><br />
        <div id="addr_status">Кликните на&nbsp;карту, чтобы увидеть условия доставки</div>
    </div>

</div>

<div class="half fl" style="width:425px; margin-left:10px;">
    <div id="map" style="width:425px; height:400px;" ></div>
</div>

{foreach from=$zones item=z name=z}
<div class="mt{if $z->id != $active_zone} hide{/if} zone cb" id="zone{$z->id}">
    <h2>{$z->name} {if $z->id == Model_Zone::ZAMKAD}<nobr>(+{Model_Order::PRICE_KM}р. за каждый км пути за МКаД)</nobr>{/if}<i style="background:#{$z->color};"></i></h2>
    <div class="shortzone">{$z->short}</div>
	<table class="table1">
	<thead>
	<tr>
		<th class="td2">
            <div>
                <span>Время</span>
                <span>Сумма</span>
            </div>
        </th>
		{foreach from=$ztp[$z->id] item=i key=t}
			<th scope="col">{$times[$t]->name|replace:':00':''} {if $times[$t]->week_day != 127}<br />{$times[$t]->week_day|week_day:1}{/if}</th>
		{/foreach}
	</tr>
	</thead>
	<tbody>
	{foreach from=$ztp[$z->id]|current item=i key=t name=t}
	<tr>
		{if $smarty.foreach.t.first}
		<td>менее {$prices[$t]->min_sum}р.</td>
		{foreach from=$ztp[$z->id] item=i key=tt}
			<td>{$times[$tt]->price}</td>
		{/foreach}
	</tr>
	</tr>
		{/if}
		<td>от {$prices[$t]->min_sum} р.</td>
		{foreach from=$ztp[$z->id] item=tp}
			{foreach from=$tp item=p}
				{if $p->min_sum eq $prices[$t]->min_sum}<td>{$p->price}</td>{/if}
			{/foreach}
		{/foreach}
	</tr>
	{/foreach}
	</tbody>
	</table>

	{$z->text}
</div>
{/foreach}

<script type="text/javascript">
var myMap = false, sep = ', ', bounds = [[55.490667, 37.182743], [56.01074, 37.964969]], mybounds = bounds, for_route = false;
jQuery.support.cors = true;

function addr_reset() {
    for_route = false;
    $('#house, #street, #city').removeClass('ok').removeClass('error').prop('readonly', false);
    $('#zone').html('Не определена');
    $('input[name=mkad]').val(0);
    $('input[name=latlong]').val('');
    $('input[name=correct_addr]').val('');
    $('html, body').animate({ scrollTop: $("#city").offset().top - 40}, 'fast');
    $('#check_addr, #check_addr + abbr').show();
    $('#addr_status').html('');
}

function no_addr() {
    $('#addr_status').html('Адрес не найден. Кликните на&nbsp;карту, чтобы увидеть условия доставки');
}

function fall_to_transport() {
    alert('Вероятно, регион доставки Вашего заказа находится за пределами Московской области, поэтому заказ может быть доставлен только транспортной компанией');
    addr_reset();
}

function addr_ok(names, coords) {
    if (names) {
        $('#house').val(names.pop());
        $('#street').val(names.pop());
        $('#city').val(names.join(sep));
        $('input[name=correct_addr]').val(1);
    } else {
        $('input[name=correct_addr]').val('');
    }
    $('#house, #street, #city').addClass('ok').prop('readonly', true);
    $('#check_addr, #check_addr + abbr').hide();
    $('input[name=latlong]').val(coords[1] + ',' + coords[0]);

    $('html, body').animate({ scrollTop: $("#city").offset().top - 20}, 'fast');

    $('#addr_status').html('<h3>Дом найден на карте</h3>' +
            '<p><strong>Координаты:</strong> '+ coords.join(sep) +
            '</p><p><strong>Адрес:</strong> '+ $('#city').val() + sep + $('#street').val() + sep + $('#house').val() +
            '</p><a class="do" onclick="addr_reset()">Изменить</a>');

    get_zone(coords);
    if (myMap) myMap.balloon.close();
}

function get_zone(coords) {

    $('#ship_zone').val(0); // зона не определена
    $('input[name=mkad]').val(0);
    $('#zone').html('Определяем зону доставки <i class="load"></i>');
    $('.zone').hide();

    $.get('/delivery/zone', { latlong:coords.reverse().join(' ') }, function (data) {
        $('#ship_zone').val(data.zone_id);
        if ( data.closest ) {
            $('#zone').html('За МКаД. Расчёт расстояния <i class="load"></i>');

            for_route = [data.closest.reverse(), coords.reverse()];
            map_init(); // строим до мкад дорогу

        } else {

            for_route = false;

            $('#zone').html(data.zone);
        }
        $('#zone' + data.zone_id).show();

    }, 'json');
}

$(document).ready( function() {

    $('#check_addr').click(function() {

        var e = false;
        if ( ! $('#city').val()) {
            $('#city').removeClass('ok').addClass('error').attr('error', 'Не указан город');
            e = true;
        }
        if ( ! $('#street').val()) {
            $('#street').removeClass('ok').addClass('error').attr('error', 'Не указана улица');
            e = true;
        }
        if ( ! $('#house').val()) {
            $('#house').removeClass('ok').addClass('error').attr('error', 'Не указан дом');
            e = true;
        }
        if (e) return false;

        $('#addr_status').html('<i class="load"></i>');

        var dx = bounds[1][1] - bounds[0][1], dy = bounds[1][0] - bounds[0][0];

        falled = $('#city').val(); // initialized to fall to transport if needed

        $.get('//geocode-maps.yandex.ru/1.x/', {
            geocode: $('#city').val() + sep + $('#street').val() + sep + $('#house').val(),
            format: 'json',
            spn: dx + ',' + dy ,
            ll: (bounds[1][1] - dx / 2) + ',' + (bounds[1][0] - dy / 2),
            rspn: 0,
            callback: 'jsonp_callback'
        }, function(data) {
            $('#addr_status i.load').remove();

            var found = false, md = false, houses = false, a = '', names = [], coords = '';
            for_route = false;

            if (data.response.GeoObjectCollection.featureMember) {
                found = data.response.GeoObjectCollection.featureMember;
                for(f in found) {
                    md = found[f].GeoObject.metaDataProperty.GeocoderMetaData;

                    names = md.text.split(sep).slice(1), // без страны
                            coords = found[f].GeoObject.Point.pos.split(' ').reverse();

                    if (md.precision == 'exact') { // найден точный адрес

                        addr_ok(names, coords);
                        return true;

                    } else { // адрес на карте не найден - показываем список найденных домов

                        if (md.kind == 'house') {
                            houses = true;
                            a = $('<a class="do">Это мой дом!</a>')
                                    .attr('rel', names.join(sep))
                                    .attr('rev', coords.join(sep))
                                    .click(function() {
                                        addr_ok($(this).attr('rel').split(sep), $(this).attr('rev').split(sep))
                                    });
                            $('#addr_status').append(names.join(sep) + ' ').append(a).append('<br /><br />');
                        }

                        mybounds[0] = found[f].GeoObject.boundedBy.Envelope.lowerCorner.split(' ').reverse();
                        mybounds[1] = found[f].GeoObject.boundedBy.Envelope.upperCorner.split(' ').reverse();
                    }
                }

                if (houses) {
                    $('#addr_status').prepend('<h3>Найдены адреса:</h3>');
                    a = $('<a class="do">Здесь нет моего дома</a>').click(function() {
                        no_addr();
                    });
                    $('#addr_status').append(a);
                } else {
                    no_addr();
                }

            } else {
                mybounds = bounds;
                no_addr();
            }
        }, 'jsonp');
    });

});

ymaps.ready(function() {
    if (!for_route) {
        var zones = [], min = [180, 180], max = [0, 0], colors = [];

        {foreach from=$zones item=z name=z}
        zones[{$z->id}] = [[{$z->poly|for_map}]];
        colors[{$z->id}] = '#{$z->color}';
        {if $z->id eq Model_Zone::ZAMKAD}{* для зоны замкад внутренний контур = контуры всех других зон *}
        {foreach from=$zones item=zz name=zz}
        {if $zz->id neq Model_Zone::ZAMKAD}zones[{$z->id}].push([{$zz->poly|for_map}]);
        {/if}
        {/foreach}
        {/if}
        {/foreach}

        for (var i in zones) {
            for (var j in zones[i][0]) {
                if (i != {Model_Zone::ZAMKAD}) {
                    min[0] = Math.min(min[0], zones[i][0][j][0]);
                    min[1] = Math.min(min[1], zones[i][0][j][1]);
                    max[0] = Math.max(max[0], zones[i][0][j][0]);
                    max[1] = Math.max(max[1], zones[i][0][j][1]);
                }
            }
        }

        var params = ymaps.util.bounds.getCenterAndZoom([min, max], [$('#map').width(), 400]);
        params.controls = [];
        var myMap = new ymaps.Map("map", params), polygons = [];

        for (var i in zones) {

            polygons[i] = new ymaps.GeoObject({
                geometry: {
                    type: "Polygon",
                    coordinates: zones[i]
                },
                properties: {
                    balloonContent: $('#zone' + i).children('div.shortzone').html(),
                    hintContent: $('#zone' + i + ' h2').eq(0).text(),
                    myZoneId: i
                }
            }, {
                fillColor: colors[i],
                strokeColor: '#000000',
                opacity: 0.5,
                strokeWidth: 1
            });
            polygons[i].events.add('click', function (e) {
                $('.zone').addClass('hide');
                $('#zone' + e.get('target').properties.get('myZoneId')).removeClass('hide');
            });
            myMap.geoObjects.add(polygons[i]);
        }

        myMap.controls.add('zoomControl');
        myMap.controls.add('searchControl');
        myMap.controls.add('geolocationControl');
        myMap.controls.add('fullscreenControl');
        myMap.options.set('minZoom', 5);

    } else {

        ymaps.route(for_route, { mapStateAutoApply: true})
                .then(function (rr) {

                    var points = rr.getPaths().get(0).getSegments(), length = 0, mkad_name = '', mkad = points[0].getCoordinates()[0];

                    for (var x in points.reverse()) { // с конца ищем мкад
                        mkad_name = points[x].getStreet();
                        var c = points[x].getCoordinates();
                        mkad = c[c.length - 1];
                        if (mkad_name == 'МКАД') break;
                        length += points[x].getLength();
                    }

                    var km = Math.ceil(length / 1000);
                    if (km > 100) fall_to_transport();
                    $('input[name=mkad]').val(km);
                    $('#zone').html(km + 'км от МКАД');

                    ymaps.route([mkad, for_route[1]], { mapStateAutoApply: true})
                            .then(function (r) {
                                myMap.geoObjects.add(r);
                                var points = r.getWayPoints();
                                points.options.set('preset', 'twirl#darkorangeStretchyIcon');
                                points.get(0).properties.set('iconContent', mkad_name);
                                points.get(1).properties.set('iconContent', $('#city').val() + sep + $('#street').val() + sep + $('#house').val());

                            });

                },
                function (error) {
                    alert("Возникла ошибка: " + error.message);
                    if (error.status == 500 && error.message == "can't construct a route") {
                        fall_to_transport();
                    }
                });
    }
});
</script>


