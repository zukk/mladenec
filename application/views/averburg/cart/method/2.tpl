{* Доставка курьером младенца *}
<div id='kurier-{$rand}'>
	{if !empty( $addresses )}
	{foreach from=$addresses item=a name=a}
		{capture assign=addr}{$a->city}, {$a->street}, {$a->house}{*, {$a->kv}{if $a->comment}, {$a->comment}{/if}*}{/capture}
		<label title="{$addr}"><input type="radio" rel="{$a->latlong}" name="address_id_2" value="{$a->id}" {if $smarty.foreach.a.first} checked="checked"{/if} />{$addr}</label>
	{/foreach}
	{/if}
	<label>
		<input type="radio" name="address_id_2" value="0" {if empty($addresses)}checked="checked"{/if}/> Новый адрес
	</label>
</div>
<script>
	var onBalloonLinkClick{$rand};
	$(function(){
		var 
			form = $('#addr-{$rand}'),
			tableForm = form.find('.addr-table-form'),
			tableStatus = form.find('.addr-table-status'),
			loader = new Image,
			bounds = [[55.490667, 37.182743], [56.01074, 37.964969]],
			dx = bounds[1][1] - bounds[0][1], dy = bounds[1][0] - bounds[0][0];
			
		loader.src = '/i/load.gif';
		
		var addr = {
			cached: {},
			startForm: function(){
				tableForm.find('input').val('');
				tableStatus.slideUp(function(){
					tableForm.slideDown();
				});
			},
			map: {
				instance: null,
				targetPoint: null,
				bounds: bounds,
				clickEvent: false,
				point: function(coords){
					
					return new ymaps.GeoObject({
							geometry: {
								type: "Point",
								coordinates: coords
							},
							properties: {
								iconContent: "Доставка",
								// balloonContent: statusText.join(', ')
							}
						}, {
							preset: 'islands#blueStretchyIcon',
						});
				},
				init: function(){
					var callback = arguments[0];
					if( !this.instance ){
						var 
							o = form.find('.addr-map');
						o.animate({
							height: '400px'}, 'fast', function(){
							
							if( !addr.map.instance ){
								ymaps.ready(function(){
									addr.map.instance = new ymaps.Map(o.attr('id'), ymaps.util.bounds.getCenterAndZoom(addr.map.bounds, [o.width(), o.height()]));
									addr.map.instance.controls.add('zoomControl');
									addr.map.instance.events.add('click', addr.map.click);
									if( callback )
										callback(addr.map.instance);
								});
							}
						});
					}
					else{
						
						if( callback )
							callback(addr.map.instance);
					}
				}
			},
			reset: function(){
				tableStatus.slideUp(function(){
					tableForm.slideDown();
				});
				return false;
			},
			findOnMap: function(){
				var coords = arguments[0];
				addr.map.instance.geoObjects.removeAll();
				tableStatus.find('table').hide();
				tableForm.slideUp(function(){
					tableStatus.slideDown(function(){
						addr.map.init(function(i){

							var _alert = $('#alert-{$rand}').html('Кликните на карте для уточнения Вашего адреса').slideDown();
							
							if( addr.map.targetPoint ){
								i.geoObjects.remove(addr.map.targetPoint);
								addr.map.targetPoint = null;
							}
							
							if( coords ){
								i.setCenter(coords.split(' ').reverse());
							}
							
							if( !addr.map.clickEvent )
							i.events.add('click', function(e) {
								e.preventDefault();
								addr.map.clickEvent = true;
								
								var 
									addrArr = [$('[name=city]').val(),$('[name=street]').val(), $('[name=house]').val()],
									z = i.getZoom(),
									_coords = e.get('coords'),
									a = $('');
								;
								if (z < 14) {
									_alert.html('Приблизьте карту. Затем, кликните на&nbsp;Вашем доме');
									return false; // only 14 or more for house!
								}


								/* i.balloon.open(_coords, {
									contentHeader: 'Подтвердите точку доставки!',
									contentBody: '<p><a class="do" onclick="onBalloonLinkClick{$rand}()">Я подтверждаю, что здесь находится дом, адрес:<br />' +
										addrArr.join(', ') + '</a></p>',
									contentFooter: '<sup>Если Вы ошиблись и&nbsp;это не&nbsp;Ваш дом, щёлкните ещё раз на&nbsp;Вашем доме.</sup>'
								});
								
								onBalloonLinkClick{$rand} = function(){ */
									$('#alert-{$rand}').slideUp();
									
									// addr.map.init(function(i){
										// var coords = i.balloon.getPosition();

										// i.balloon.close();
										addr.ok(addrArr.reverse().join(', '), _coords.reverse().join(' '));
									// });
								/*
									return false;
								}; */
	
								return false;
							});
						});
					});
				});
			},
			ok: function(text, coords){

				var
					fields = ['house', 'street', 'city'],
					status = form.find('#addr_status'),
					statusText = [];

				// без страны
				text = text.split(', ');

				$.map( text, function(value, key){
					if( fields[key] ){
						form.find('[name='+fields[key]+']').val(value);
						statusText.push(value);
					}
				});

				if( addr.check() ){
					
					tableStatus.find('table').slideDown();

					$.ajax({
						url: '/delivery/zone',
						method: 'GET',
						data: {
							'latlong': coords
						},
						dataType: 'JSON',
						success: function(data){

							addr.geocoderState('stop');


							if( data.zone ){
								form.find('[name=ship_zone]').val(data.zone_id);
								form.find('.cart-addr-zone-2').html(data.zone);
							}

							if( data.dates ){

								// array_keys
								var _ = [];
								$.map(data.dates, function(v, k){
									_.push(k);
								});
								$('#delivery-date-{$rand}').data('dates', data.dates).mladenecdateslider('update', _);
							}

							coords = coords.split(' ').reverse();
							form.find('[name=latlong]').val(coords.join(','));

							tableForm.slideUp(function(){
								tableStatus.find('.addr-status-text').empty().append(statusText.reverse().join(', '));
								tableStatus.slideDown(function(){
									addr.map.init(function(m){

										if( data.zone_id == {Model_Zone::DEFAULT_ZONE} ){
											m.setCenter(coords);
											m.setZoom(14);
											
											addr.map.instance.geoObjects.removeAll();

											addr.map.targetPoint = addr.map.point(coords);
											m.geoObjects.add(addr.map.targetPoint);
											$('.cart-addr-zone-2').attr('data-km', '0');
										}
										else if ( data.closest ) {
											m.setCenter(coords);
											m.setZoom(14);
											
											var q = coords;
											ymaps.route([data.closest.reverse(), q], { mapStateAutoApply: true})
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
														$('input[name=mkad]').val(km);
														$('.cart-addr-zone-2').append( ' (' + km + 'км от МКАД)').attr('data-km', km);

														ymaps.route([mkad, q], { mapStateAutoApply: true})
																.then(function(r) {
																	addr.map.instance.geoObjects.removeAll();
																	addr.map.instance.geoObjects.add(r);
																	var points = r.getWayPoints();
																	points.options.set('preset', 'islands#darkorangeStretchyIcon');
																	points.get(0).properties.set('iconContent', mkad_name);
																	points.get(1).properties.set('iconContent', $('[name=city]').val() + ', ' + $('[name=street]').val() + ', ' + $('[name=house]').val());
																	if( addr.map.targetPoint ){
																		addr.map.instance.geoObjects.remove(addr.map.targetPoint);
																		addr.map.targetPoint = points.get(1);
																	}
																});

													},
													function (error) {
														alert("Возникла ошибка: " + error.message);
														if (error.status == 500 && error.message == "can't construct a route") {
															// fall_to_transport();
														}
													}
											);
										}										
										else{
											tableForm.find('input').each(function(){
												$('#new_addr input[name='+$(this).attr('name')+']').val($(this).val());
												$(this).val('');
											});
											$('.addr-accordeon [data-value=3] .cart-delivery-form:not(.prepended)').prepend('<div style="padding: 10px">Вероятно, регион доставки Вашего заказа находится за пределами Московской области, поэтому заказ может быть доставлен только транспортной компанией</div>').addClass('prepended');
											$('.addr-accordeon [data-value=3] span').click();
											return false;
										}
									});
								});
							});
						}
					});
				}
				else{
					addr.geocoderState('stop');
					tableForm.slideDown();
				}
			},
			check: function(){
				var checked = true;
				tableForm.find('input[error]').each(function(){
					if( $(this).val() == '' ){
						$(this).addClass('error');
						checked = false;
					}
				}).one('keyup', function(){
					$(this).parent().parent().removeClass('error');
				});
				
				return checked;
			},
			choose: function(choose){
				
				var 
					l = tableForm.find('.delivery-check-line'),
					c = tableForm.find('.delivery-choose-line');
				
				c.empty().append('<p>Найденные адреса:</p>');
				
				$.map(choose, function(v){

					var item = $('<div class="delivery-choose-item"><p>'+v.text.split(', ').reverse().join(', ')+'</p></div>').appendTo(c);
					
					$('<a class="butt small fl-lft">Это мой дом!</a><div class="clear"></div>')
							.appendTo(item)
							.click(function(){
								c.slideUp(function(){
									$(this).empty();
									l.slideDown();
								});
								addr.ok(v.text, v.coords);
								return false;
							});
				});
				
				var noAddrLink = $('<a class="do fl-rght">Здесь нет моего дома</a>')
					.click(addr.findOnMap)
					.appendTo(c);
				
				l.slideUp(function(){
					c.slideDown();
				});
			},
			parseGeocoder: function(data) {
				var md, found, coords;
				var isReverse = arguments[1] ? arguments[1]: false;
				
				if( !isReverse ){
					$('.addr [name=correct_addr]').val('0');
				}
				
				//console.log(arguments);
				if (data.response.GeoObjectCollection.featureMember) {
					found = data.response.GeoObjectCollection.featureMember;
					
					var toSend = [];
					for(var f in found) {
						md = found[f].GeoObject.metaDataProperty.GeocoderMetaData;
						md.text = md.text.split(', ').reverse().slice(0,3).join(', ');
						coords = found[f].GeoObject.Point.pos;
						
						if (md.precision == 'exact') { // найден точный адрес

							$('.addr [name=correct_addr]').val('1');
							addr.ok(md.text, coords);
							return true;

						} else if (md.kind == 'house') {

							addr.geocoderState('stop');
							toSend.push({
									text: md.text,
									coords: coords
								});
						}
					}

					if( toSend.length > 0 ){
						addr.choose(toSend);
					}
					else{
						if( !isReverse ){
							addr.findOnMap();
						}
						else{
							addr.geocoderState('stop');
							$('#alert-{$rand}').html('Мы не смогли определить адрес по координатам. Пожалуйста, введите адрес').slideDown();
							addr.startForm();
						}
					}
				}
			},
			geocoderState: function(){
				
				var act = arguments[0];
				
				if( !act || ( addr._timeout && act == 'start' ) ){
					return false;
				}
				
				var b = tableForm.find('.addr-check');
				
				if( act == 'start' ){
					addr._timeout = setTimeout(function(){
						$(loader).appendTo(b).css({
							position: 'absolute',
							right: '-30px',
							top: '5px',
							display: 'block'
						});
					},400);
				}
				else{
					clearTimeout(addr._timeout);
					addr._timeout = false;
					$(loader).remove();
				}
				
				return true;
			},
			geocoder: function(){
				
				if( addr.check() ){
					
					$('#tooltip').hide();
					if( !addr.geocoderState('start') )
						return false;

					$.get('//geocode-maps.yandex.ru/1.x/', {
						geocode: form.find('[name=city]').val() + ', ' + form.find('[name=street]').val() + ', ' + form.find('[name=house]').val(),
						format: 'json',
						spn: dx + ',' + dy ,
						ll: (bounds[1][1] - dx / 2) + ',' + (bounds[1][0] - dy / 2),
						rspn: 0,
						callback: 'jsonp_callback'
					}, function(data){
						addr.parseGeocoder(data);
					}, 'jsonp');
				}
				else{
					addr.geocoderState('stop');
					tableForm.slideDown();
				}

				return false;
			},
			_timeout: false
		};
		
		{foreach from=$addresses item=a name=a}
		addr.cached[{$a->id}] = {
			city: "{$a->city}",
			street: "{$a->street}",
			house: "{$a->house}",
			enter: "{$a->enter}",
			domofon: "{$a->domofon}",
			floor: "{$a->floor}",
			lift: "{$a->lift}",
			kv: "{$a->kv}",
			latlong: "{$a->latlong}"
		};
		{/foreach}
		

		if( $('#kurier-{$rand} [name=address_id_2]').val() > 0 ){
			tableForm.hide();
		}
		else{
			tableForm.show();
		}
		form.find('.addr-check').click(addr.geocoder);
		tableStatus.find('.addr-reset-link').click(addr.reset);
			
		if (navigator.geolocation) {
			$('#kurier-{$rand}').append('<label><input type="radio" name="address_id_2" value="-1" /> Текущее местоположение</label>');
		}			
		
		if ( $('#kurier-{$rand} [name=address_id_2]').val() > 0 ){
			var v = $('#kurier-{$rand} [name=address_id_2]').val();
			var _d = addr.cached[v];
			if( _d ){
				tableForm.find('input').each(function(){
					if( _d[$(this).attr('name') ] ){
						$(this).val(_d[$(this).attr('name') ]);
					}
				});
				addr.ok($('#kurier-{$rand} [name=address_id_2][value='+v+']').parent().text().split(', ').reverse().join(', '), _d.latlong.split(',').join(' '));
			}
		}
		$('#kurier-{$rand} input[type=radio]').mladenecradio({
			onClick: function(check, value ){
				
				$('.cart-delivery-send-link').attr("disabled", "disabled");
				$('.cart-delivery-price').html('');

				$('.cart-addr-zone-2').removeAttr('data-km');
				// $('.cart-delivery-price').html('');
				$('[name=address_id_2][oldval]').each(function(){
					$(this).val($(this).attr('oldval')).removeAttr('oldval');
				});
				$('#alert-{$rand}').slideUp();
				
				var
					l = tableForm.find('.delivery-check-line'),
					c = tableForm.find('.delivery-choose-line');
			
				c.slideUp(function(){
					$(this).empty();
					l.slideDown();
				});					
				
				tableForm.find('input.error').removeClass('error');
				
				if( check && value > "0" ){
					tableForm.slideUp(function(){
						var d = addr.cached[value];
						if( d ){
							tableForm.find('input').each(function(){
								if( d[$(this).attr('name') ] ){
									$(this).val(d[$(this).attr('name') ]);
								}
							}).filter(":not(.keyupped)").one('keyup', function(){
								if( $(this).hasClass('keyupped') ){
									$('[name=address_id_2][checked]').each(function(){
										$(this).attr('oldval', $(this).val());
									}).val('0');
									tableForm.find('input.keyupped').removeClass('keyupped');
								}
							}).addClass('keyupped');
							addr.ok($('#kurier-{$rand} [name=address_id_2][value='+value+']').parent().text().split(', ').reverse().join(', '), d.latlong.split(',').join(' '));
						}
					});
				}
				else if( check && value == "0" ){
					addr.startForm();
				}
				else if( check && value == "-1" ){
					tableStatus.slideUp(function(){
						addr.startForm();
						var timeout = setTimeout(function(){
							$('#alert-{$rand}').html('Пожалуйста, разрешите доступ к вашему местоположению').slideDown();
						}, 1000);
						navigator.geolocation.getCurrentPosition(
							function (position) {
								clearTimeout(timeout);
								
								if( $('#alert-{$rand}').height() ){
									$('#alert-{$rand}').slideUp();
								}
								
								$.get('//geocode-maps.yandex.ru/1.x/', {
									geocode: position.coords.longitude+','+position.coords.latitude,
									format: 'json',
									callback: 'jsonp_callback'
								}, function(data){
									addr.parseGeocoder(data, true);
								}, 'jsonp');
							}, 
							function displayError(error) {
								var errors = { 
								  1: 'Доступ к местоположению не разрешен',
								  2: 'Местоположение недоступно',
								  3: 'Превышен интервал ожидания'
								};

								$('#alert-{$rand}').html(errors[error.code] + '. Введите адрес').slideDown(function(){
									addr.startForm();
									setTimeout(function(){
										$('#alert-{$rand}').slideUp();
									},5000);
								});
							},
							{ enableHighAccuracy: true, timeout: 1 * 1000, maximumAge: 0 }
						);
					});
				}
			}
		});
		
	});
</script>
<div id="html5_addr" style="padding: 10px 0;">
	<div id="alert-{$rand}" class="alrt alrt-warning" style="display: none;">Пожалуйста, разрешите доступ к вашему местоположению</div>
</div>
{include file='averburg/cart/delivery/addr.tpl' rand=$rand}

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
			{html_options options=Model_Order::delivery_call() selected=$o->call|default:''}
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
