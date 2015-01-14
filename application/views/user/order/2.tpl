{* Доставка курьером младенца *}
<h2 class="mt">Адрес доставки</h2>
<div>
	{foreach from=$address item=a name=a}
		{capture assign=addr}{$a->city}, {$a->street}, {$a->house}, {$a->kv}{if $a->comment}, {$a->comment}{/if}{/capture}
		<label class="label" title="{$addr}"><i class="radio"></i><input type="radio" rel="{$a->latlong}" name="address_id" value="{$a->id}" {if $smarty.foreach.a.first} checked="checked"{/if} />{$addr}</label>
	{/foreach}
	<label class="label"><i class="radio"></i><input type="radio" name="address_id" value="0" {if empty($address)}checked="checked"{/if}/> Новый адрес</label>
</div>

<div {if ! empty($address)}class="hide"{/if} id="new_addr">
	<div class="half mt">
		<label class="l" for="city">Город</label><input name="city" id="city" value="{$o->city|default:'Москва'}" class="txt" />
		<label class="l" for="street">Улица</label><input name="street" id="street" value="{$o->street}" class="txt" />
		<label class="l" for="house">Дом<br /><small>(например, 5к1)</small></label><input name="house" id="house" value="{$o->house}" class="txt" />
		<input type="button" value="проверить адрес" class="butt small fl" id="check_addr" />
		<abbr class="fl" abbr="Для улучшения логистики доставок наша курьерская служба использует координаты адреса.<br />
            Если Вашего дома нет&nbsp;в&nbsp;нашей базе данных,&nbsp;то Вам будет предложено
            указать курсором на&nbsp;карте место доставки.<br />
            ">Зачем?</abbr>
	</div>

	<div id="searcher" class="half"><div id="addr_status"></div><div id="map" class="hide"></div></div>

	<div id="house_details" class="mt hide cl">
		<div class="cl oh">
			<div class="half">
				<label class="l" for="enter">Подъезд</label><input name="enter" id="enter" value="{$o->enter}" class="txt short" />
				<label class="l" for="floor">Этаж</label><input name="floor" id="floor" value="{$o->floor}" class="txt short" />
				<label class="label fl ml11"><i class="check"></i><input name="lift" type="checkbox" value="1" {if $o->lift}checked="checked"{/if} /> Есть лифт</label><abbr class="fl ml11" abbr="Для заказов тяжелее 10кг при&nbsp;отсутствии лифта подъем на&nbsp;этаж платный.">?</abbr>
				<label class="l" for="kv">Номер квартиры/офиса</label><input name="kv" id="kv" value="{$o->kv}" class="txt short" />
				<label class="l" for="domofon">Домофон</label><input name="domofon" id="domofon" value="{$o->domofon}" class="txt" />
			</div>
			<div class="half">
				<br/><br />
				<label class="l" for="comment">Комментарий к&nbsp;доставке <abbr abbr="Опишите особенности проезда на&nbsp;автомобиле">?</abbr></label>
				<textarea id="comment" name="comment" class="txt" style="height:110px;">{$o->comment|default:''}</textarea>
			</div>
			<input name="latlong" value="{$o->latlong}" type="hidden" />
			<input name="correct_addr" value="{$o->correct_addr}" type="hidden" value="0"/>
		</div>
	</div>
</div>

<div class="cb mt">
	<label class="l">Зона доставки</label><input name="ship_zone" id="ship_zone" type="hidden" value="0" /><input name="mkad" type="hidden" value="0" /><abbr id="zone" abbr="Зона доставки определяется по координатам точки доставки.">Не определена</abbr>
</div>

<div class="cl hide" id="time_details">

	<h2 class="mt">Время доставки</h2>

	<div class="cl">
		<label class="l" for="date">День доставки</label>
        <span id="date"><i class="load"></i>Дождитесь определения зоны доставки&hellip;</span>
	</div>

	{if not empty($big)}
	<div class="cl">
            <p>Мы ожидаем поступления на&nbsp;склад в&nbsp;течение <nobr>1-2 дней</nobr> следующих товаров из&nbsp;Вашего заказа*:</p>
            <ul>
            {foreach from=$big item=g}
                <li><a href="{$g->get_link(FALSE)}">{$g->group_name} {$g->name}</a></li>
            {/foreach}
            </ul>
	</div>
	{/if}

	<div class="cl">
		<label class="l" for="time">Часы доставки</label>
		<span id="time"><i class="load"></i>Определяются возможные интервалы доставки&hellip;</span>
        <span>Просим Вас не&nbsp;ограничивать время доставки без&nbsp;крайней необходимости!</span>
	</div>

	<div class="half cl">
		<label class="l" for="call">Звонок курьера</label>
		<select name="call" id="call">
			{html_options options=Model_Order::delivery_call() selected=$o->call}
		</select>
	</div>

	<div class="half cl">
		{*if $user->sum}
		<label class="label"><i class="check"></i><input type="checkbox" name="call_ok" id="call_ok" />Менеджеру не&nbsp;звонить если все в&nbsp;порядке.</label>
		{/if*}
	</div>

    <div class="half cl mt">
		<input type="submit" class="butt" value="Подтверждение заказа" />
	</div>

    <div class="cb pt">
        На&nbsp; странице подтверждения заказа Вы&nbsp;увидите окончательную сумму к&nbsp;оплате и&nbsp;сможете проверить данные заказа перед его отправкой
    </div>
    {if not empty($big)}
        <div class="cb pt">
            * Срок поступления товара на&nbsp;склад может быть увеличен в&nbsp;связи с&nbsp;непредвиденными обстоятельствами. Мы&nbsp;ценим своих клиентов и&nbsp;делаем все возможное, чтобы товар был доставлен Вам в&nbsp;срок!
        </div>
    {/if}
</div>


<script type="text/javascript">
var myMap = false, sep = ', ', bounds = [[55.490667, 37.182743], [56.01074, 37.964969]], mybounds = bounds, for_route = false;
jQuery.support.cors = true;

function show_map() {
	$('#map').show();
	if ( ! myMap) {
		$('#map').html('<i class="load"></i>').show();
		$.getScript("//api-maps.yandex.ru/2.1/?lang=ru-RU&onload=map_init");
	} else {
		map_init();
	}
}

function addr_reset() {
	for_route = false;
	$('#house, #street, #city').removeClass('ok').removeClass('error').prop('readonly', false);
	$('#zone').html('Не определена');
	$('input[name=mkad]').val(0);
	$('input[name=latlong]').val('');
	$('input[name=correct_addr]').val('');
	$('html, body').animate({ scrollTop: $("#city").offset().top - 40}, 'fast');
	$('#check_addr, #check_addr + abbr').show();
	$('#map').hide();
	$('#addr_status').html('');
	$('#time_details input[type=text]').val('').removeClass('ok').removeClass('error');
	$('#house_details').hide();
	$('#time_details').hide();
}

function fall_to_transport() {
	alert('Вероятно, регион доставки Вашего заказа находится за пределами Московской области, поэтому заказ может быть доставлен только транспортной компанией');
	addr_reset();
	$('#dt{Model_Order::SHIP_SERVICE}').click();
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

	$('#house_details').show();

	get_zone(coords);
	if (myMap) myMap.balloon.close();
}

function get_zone(coords) {

	$('#ship_zone').val(0); // зона не определена
	$('input[name=mkad]').val(0);
	$('#zone').html('Определяем зону доставки <i class="load"></i>');

    $('#date').replaceWith('<span id="date">Дождитесь определения зоны доставки&hellip;</span>');
	$('#time').replaceWith('<span id="time">Дождитесь определения зоны доставки&hellip;</span>');

	$.get('/delivery/zone', { latlong:coords.reverse().join(' ') }, function (data) {
        $('#ship_zone').val(data.zone_id);
        $('#date').replaceWith(data.date);
        $('#date').on('change', function() { show_times()});

		if ( data.closest ) {
			$('#zone').html('За МКаД. Расчёт расстояния <i class="load"></i>');

			for_route = [data.closest.reverse(), coords.reverse()];

			show_map();

		} else {

			for_route = false;

			$('#zone').html(data.zone);
			$('#map').hide();
		}

        show_times();
		$('#house_details').show('fast');
		$('#time_details').show('fast');

	}, 'json');
}

function show_times() {
	$('#time').replaceWith('<span id="time"><i class="load></i>Получение доступных интервалов доставки&hellip;</span>');

    $.post('/delivery/time', { zone : $('#ship_zone').val(), date: $('#date').val()}, function(data) {
        $('#time').replaceWith(data.time);
    }, 'json');
}

$(document).ready( function() {

    $('input[name="address_id"]').change(function() {
		var addr_id = $(this).val();
		$('#new_addr').toggle(addr_id == 0);
		addr_reset();
		if (addr_id > 0) { // show time and date options (according to zone!)
			get_zone($(this).attr('rel').split(',').reverse());
		}
	});

	if ($('input[name="address_id"]').val() != '0') get_zone($('input[name="address_id"]:checked').attr('rel').split(',').reverse());

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
					$('#map').hide();
					$('#addr_status').prepend('<h3>Найдены адреса:</h3>');
					a = $('<a class="do">Здесь нет моего дома</a>').click(function() {
						show_map();
					});
					$('#addr_status').append(a);
				} else {
					show_map();
				}

			} else {
				mybounds = bounds;
				show_map();
			}
		}, 'jsonp');
	});

});

function map_init() {
	$('#map').show();
	if ( ! myMap) {
		var w = $('#map').width(), params = ymaps.util.bounds.getCenterAndZoom(mybounds, [w, w]);
        params.controls = ['zoomControl'];
		myMap = new ymaps.Map('map', params);
	} else {
		myMap.geoObjects.each(function(o) { myMap.geoObjects.remove(o);}); // clear map
		myMap.setBounds(mybounds);
	}
	$('#map i.load').remove();
	myMap.options.set('minZoom', 5);

	if (for_route == false) {

		myMap.events.add('click', function(e) {
			var z = myMap.getZoom();
			if (z < 14) {
				$('#addr_status').html('Приблизьте карту. Затем, кликните на&nbsp;Вашем доме');
				return false; // only 14 or more for house!
			}
			var coords = e.get('coords');

            myMap.balloon.open(coords, {
				contentHeader: 'Подтвердите точку доставки!',
				contentBody: 'Координаты: ' + [coords[0].toPrecision(8),coords[1].toPrecision(8)].join(sep) + '<br />' +
                    '<a class="do">Я подтверждаю что здесь находится дом, адрес:<br />' + $('#city').val() + sep + $('#street').val() + sep + $('#house').val() + '</a>',
				contentFooter: 'Если Вы ошиблись и&nbsp;это не&nbsp;Ваш дом, щёлкните ещё раз на&nbsp;Вашем доме.'
			});
            $('#maps a.do').on('click', function() { // not working!!!
                addr_ok(false, coords);
            })
		});
		$('#addr_status').html('Пожалуйста, кликните на&nbsp;Вашем доме на&nbsp;карте');

	} else {

		myMap.events.add('click', function() { return false});

		ymaps.route(for_route, { mapStateAutoApply: true})
				.then(function (rr) {

					var points = rr.getPaths().get(0).getSegments(), length = 0, mkad_name = '',
							mkad = points[0].getCoordinates()[0];

					for(var x in points.reverse()) { // с конца ищем мкад
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
							.then(function(r) {
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
				}
		);
	}
}
</script>

