<div class="addr" id="addr-{$rand}">
	<input name="latlong" value="{$o->latlong|default:''}" type="hidden" />
	<input name="correct_addr" value="{$o->correct_addr|default:''}" type="hidden" />
	<div class="addr-table-form" style='display: none;'>
		<table>
			<col width='50%' />
			<col width='50%' />
			<tr>
				<td>
					Город
				</td>
				<td>
					<input error="Не указан город" name="city" value="{$o->city|default:'Москва'}" class="txt" />
				</td>
			</tr>
			<tr>
				<td>
					Улица
				</td>
				<td>
					<input error="Не указана улица" name="street" value="{$o->street|default:''}" class="txt" />
				</td>
			</tr>
			<tr>
				<td>
					Дом
				</td>
				<td>
					<input error="Не указан дом" name="house" value="{$o->house|default:''}" class="txt" />
				</td>
			</tr>
			<tr>
				<td>
					Подъезд
				</td>
				<td>
					<input name="enter" value="{$o->enter|default:''}" class="txt short" />
				</td>
			</tr>
			<tr>
				<td>
					Этаж
				</td>
				<td>
					<input error="Укажите номер этажа" name="floor" value="{$o->floor|default:''}" class="txt short fl-lft" style="margin-right: 10px;" />
					<label class="fl-rght" style="margin-bottom: 0;">
						<input id="delivery-lift-box-{$rand}" name="lift" type="checkbox" value="1" {if $o->lift|default:''}checked="checked"{/if} /> Есть лифт
						<script>
							$(function(){
								$('#delivery-lift-box-{$rand}').mladenecbox();
							});
						</script>
						<abbr class="ml11" abbr="Для заказов тяжелее 10кг при&nbsp;отсутствии лифта подъем на&nbsp;этаж платный.">?</abbr>
					</label>
				</td>
			</tr>
			<tr>
				<td>
					Номер квартиры/офиса
				</td>
				<td>
					<input error="Укажите номер квартиры" name="kv" id="kv" value="{$o->kv|default:''}" class="txt short" />			
				</td>
			</tr>
			<tr>
				<td>
					Домофон
				</td>
				<td>
					<input name="domofon" id="domofon" value="{$o->domofon|default:''}" class="txt short" />
				</td>
			</tr>
			<tr>
				<td colspan="2" class='txt-lft'>
					<div class='delivery-check-line'>
						<a class="butt small addr-check fl-lft" style="position: relative; left: 50%; margin-left: -90px; margin-bottom: 10px;">проверить адрес</a>
						<abbr style="display: block; margin: 6px;" class="fl-rght" abbr="Для улучшения логистики доставок наша курьерская служба использует координаты адреса.<br />
					Если Вашего дома нет&nbsp;в&nbsp;нашей базе данных,&nbsp;то Вам будет предложено
					указать курсором на&nbsp;карте место доставки.<br />">Зачем?</abbr>
					</div>
					<div class='delivery-choose-line' style='display: none'></div>
				</td>
			</tr>
		</table>
	</div>
	<div class="addr-table-status" style="display: none;">
		<div class="addr-map" id="map-{$rand}" style="width: 100%; height: 0px;"></div>
		<table style="width: 100%;">
			<col width='30%' />
			<col width='70%' />
			<tr>
				<td>Адрес</td>
				<td class="addr-status-text"></td>
			</tr>
			<tr>
				<td>
					Зона доставки
				</td>
				<td>
					<input name="ship_zone" id="addr-ship-zone-{$rand}" type="hidden" value="0" /><input name="mkad" type="hidden" value="0" /><abbr class="cart-addr-zone-2" abbr="Зона доставки определяется по координатам точки доставки.">Не определена</abbr>
				</td>
			</tr>
			<tr>
				<td></td>
				<td>
					<a class="addr-reset-link butt small fl-lft">Изменить</a>
				</td>
			</tr>
		</table>
	</div>
</div>
