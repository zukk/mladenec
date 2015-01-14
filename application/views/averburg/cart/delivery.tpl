{assign var=rand value=rand(1,100000)}
<table class='cart-delivery' style='width: 100%;'>
	<col width='48%' />
	<col width='4%' />
	<col width='48%' />
	<tr>
		<td style='vertical-align: top'>
			<form class='cart-user-form' style='display: none'>
				<input type='hidden' name='phone' value='{$user->phone}' />
				<input type='hidden' name='phone2' value='{$user->phone2}' />
				<input type='hidden' name='email' value='{$user->email}' />
				<input type='hidden' name='last_name' value='{$user->last_name}' />
				<input type='hidden' name='name' value='{$user->name}' />
			</form>
			<h3>Доставка</h3>
			<input type='hidden' name='delivery_type' value='{$session_params.delivery_type}' />
			<div class='addr-accordeon'>
				{*<div {if $session_params.delivery_type eq 4}class='active' {/if}data-value='4'>
					<span class='sys'>Самовывоз</span>
					<div>
						<form class='cart-delivery-form'>
							{include file='averburg/cart/method/4.tpl'}
						</form>
					</div>
				</div>*}
				<div class='active' data-value='{Model_Order::SHIP_COURIER}'>
					<span class='sys'>
						<abbr style="font: inherit; color: inherit; cursor: inherit; border-width: 0; font-weight: normal;" abbr="только Москва и МО" class='sys'>
							Курьерская доставка (только Москва и МО)
						</abbr>
					</span>
					<div>
						<form class='cart-delivery-form'>
							{include file='averburg/cart/method/2.tpl'}
						</form>
					</div>
				</div>
				<div {if $session_params.delivery_type eq Model_Order::SHIP_SERVICE and $cart->can_ship()}class='active' {/if}data-value='{Model_Order::SHIP_SERVICE}'>
					<span class="sys">
						<abbr style="font: inherit; color: inherit; cursor: inherit; border-width: 0; font-weight: normal;" abbr="кроме Москвы и МО" class='sys'>
							Доставка по России (кроме Москвы и МО)
						</abbr>
					</span>
					<div>
						{if not $cart->can_ship()}
						<p>
							Обращаем Ваше внимание, что по&nbsp;техническим ограничениям мы не&nbsp;осуществляем
							доставку по&nbsp;Российской Федерации товаров из&nbsp;следующих категорий:<br />
							{ORM::factory('section', 28934)->get_link()}<br />
							{ORM::factory('section', 116957)->get_link()}<br />
							{ORM::factory('section', 30025)->get_link()}<br />
						</p>
						{else}
						<form class='cart-delivery-form'>
							{include file='averburg/cart/method/3.tpl'}
						</form>
						{/if}
					</div>
				</div>
			</div>
			<script>
				var delivery;
				$(function(){
					var loader = new Image();
					loader.src="/i/load.gif";
					var working = false;
					delivery = {
						jq: $('.cart-delivery'),
						getParams: function(){
							
							var j = this.jq, v = j.find('[name=delivery_type]').val(), f = j.find('[data-value='+v+'] form');
							var params = f.serialize();
							params += '&address_id=' + $("[name=address_id_"+v+"][checked]").val();
							params += '&ship_date=' + $("[name=delivery-date]").val();
							params += '&description=' + j.find("[name=description]").val();
							params += '&delivery_type='+v;
							params += '&ajax=1';
							params += '&' + j.find('.cart-user-form').serialize();
							
							if( v != 4 ){
								
								params += '&ship_time='+j.find('[name=ship_time]').val();
							}
							
							params += '&agree='+j.find('[name=agree]:checked').length;
							params += '&pay_type='+j.find('[name=pay_type][checked]').val();
							$('[name*=select_present][checked=checked]').each(function(){
								params += "&" + $(this).attr('name') +'='+$(this).val();
							});
							return params;
						},
						sync: function(){
							$.post('/personal/cart_sync.php', this.getParams());
						},
						showPrice: function(){

							var text = '';
							if( $('[name=delivery_type]').val() == 2 ){
								var interval = setInterval(function(){
									if( typeof( $('.cart-addr-zone-2').attr('data-km') ) != 'undefined' ){
										clearInterval(interval);
										var tax = +$('[name=ship_time] option[value='+$('[name=ship_time]').val()+']').html().replace(/^.*\[([0-9]+) руб\.\]/,"$1");
										tax = $('.cart-addr-zone-2').attr('data-km') * {Model_Order::PRICE_KM} + tax;
										$('.cart-delivery-price').html('Стоимость доставки: ' + tax + ' руб.');
										$('.cart-delivery-send-link').removeAttr('disabled');
									}
								}, 400 );
							}
							else if( $('[name=delivery_type]').val() == 3 ){
								$('.cart-delivery-send-link').removeAttr('disabled');
								text = ''; //'Стоимость доставки зависит от транспортной компании';
							}
							
							$('.cart-delivery-price').html(text);
							// $('#time').replaceWith(data.time);
						},
						init: function(){
							var j = this.jq;
							j.find('.addr-accordeon').mladenecaccordeon({
								onClick: function(p){
									
									var v = p.attr('data-value');
									$('.cart-delivery-price').html('');
									
									if( v ){
										
										if( v == "2" ){
											$('.cart-delivery-send-link').attr("disabled", "disabled");
										}
										
										/*
										if( v == "3" && {$cart->get_total()} < 2500 ){
											alert('доставка транспортной компанией доступна для заказов от 2500 рублей');
											return false;
										}
										*/

										if( v == "4" || v == "3" ){
											$('.cart-delivery-send-link').removeAttr('disabled');
											delivery.jq.find('.delivery-date').slideUp();
										}
										else{
											delivery.jq.find('.delivery-date').slideDown();
										}
										j.find('[name=delivery_type]').val(v);
										delivery.sync();
										$('.addr-accordeon.active.mladenecradio.checked').click();
										
										return true;
									}
								}
							});
							j.find('[name=description]').change(function(){
								delivery.sync();
							});
							
							$('[name=agree]').mladenecbox({
								size: 21,
								onClick: function(){
									delivery.setClickableButton();
								}
							});
							
							this.jq.find('.cart-delivery-send-link').click(function(){
								
								if( $(this).attr('disabled') )
									return false;
								
								if( $('[name=delivery_type]').val() == '2' && $('.addr-table-form').css('display') != 'none' ){
									$('.addr-check').click();
								}
								else{
									delivery.send();
								}
								
								return false;
							});
							
							this._initOplata();
							
							$('#delivery-date-{$rand}').mladenecdateslider({
								target: '[name=delivery-date]',
								onClick: function(date){
									
									var dates = $('#delivery-date-{$rand}').data('dates');
									if( dates ){
										var label = dates[date.getFullYear() + '-' + ('0' + (date.getMonth() + 1)).slice(-2) + '-' + ('0' + date.getDate()).slice(-2)];
										$('#date-label-{$rand}').html(label);
									}

									var zoneId = $('#addr-ship-zone-{$rand}').val();
									
									if( zoneId > 0 ){
										$.post('/delivery/time', { 
											zone : zoneId, 
											date: [date.getFullYear(),date.getMonth()+1, date.getDate()].join('-')
										}, function(data) {
											delivery.jq.find('.date-time').empty().append($(data.time)).fadeIn();
											delivery.jq.find('.delivery-date').css({
												opacity: 1
											}).find('[name=ship_time]').change(delivery.showPrice);
											delivery.showPrice();
										}, 'json');
									}
								}
							});
						},
						setClickableButton: function(){
							var b = $('.cart-delivery-send-link');
							if( $('[name=agree]').val() == '1' ){
								b.removeAttr('disabled');
							}
							else{
								b.attr('disabled', 'disabled');
							}
						},
						_initOplata: function(){
							
							if( $('[name=pay_type][value={Model_Order::PAY_DEFAULT}]:checked').length )
								$('.cart-visa').hide();
		
							$('.cart-oplata').mladenecradiotabs({
								onClick: function(value){
									delivery.sync();
									if( value == {Model_Order::PAY_CARD} ){
										$('.cart-visa').slideDown();
									}
									else{
										$('.cart-visa').slideUp();
									}
								}
							});
						},
						send: function(){
						
							if( working )
								return false;
							
							working = true;
							
							var b = $('.cart-delivery-send-link'), c = b.html();
							
							var timeout = setTimeout(function(){
								b.empty().append('Загрузка...').attr('disabled', 'disabled');
							}, 400);
							
							$.ajax({
								url: '/personal/basket.php',
								data: this.getParams(),
								dataType: 'JSON',
								method: 'POST',
								success: function(data){
									
									clearTimeout(timeout);
									b.html(c).removeAttr('disabled');
									working = false;
									if( data.thank_you ){
										if( data.redirect ){
											history.pushState(null, null, data.redirect);
										}
										$('html, body').animate({
											scrollTop: '0px'
										}, 400);
										$('#content').empty().append(data.thank_you);
									}
									
									// для платежного шлюза
									else if( data.redirect ){
										location.href = data.redirect;
									}
									
									else if( data.error ){
										if( data.error.cartempty ){
											alert(data.error.cartempty);
										}
										if( data.error.address ){
											var sel = '.addr-table-form';
											
											if( $('[name=delivery_type]').val() == "3" )
												sel = '#new_addr';
											
											$(sel).slideDown(function(){
												var o = $(this);
												$.map(data.error.address, function(v,k){
													o.find('[name='+k+']')/*.attr('error', v)*/.addClass('error');
												});
											});
										}
										else{
											var j = delivery.jq, v = j.find('[name=delivery_type]').val(), f = j.find('[data-value='+v+'] form');
											$.map(data.error, function(v, name){
												f.find('[name='+name+']').addClass('error').attr('error', v);
											});
										}
									}
								}
							});
						}
					};
					
					delivery.init();
				});
			</script>
		</td>
		<td></td>
		<td style='vertical-align: top'>
			
			<h3>Оплата</h3>
			{include file='averburg/cart/delivery/oplata.tpl' session_params=$session_params}
			<br />
			
			<div class='delivery-date' style='opacity: 0; {if !empty( $session_params['delivery_type'] ) and ( $session_params['delivery_type'] eq 4 || $session_params['delivery_type'] eq 3 )}display: none{/if}'>
				<h3>Дата доставки</h3>
				<div id="delivery-date-{$rand}">
				</div>
				<input type='hidden' name='delivery-date' value='sdfdfs' />
				<br />
				<div class='date-label fl-lft' id='date-label-{$rand}'></div>
				<div class='date-time fl-rght' style='display: none;'></div>
				{*include file='averburg/cart/delivery/date.tpl' session_params=$session_params*}
				<br clear='all' />
			</div>
			
			<h3>Комментарий к заказу</h3>
			<textarea class='txtarea' name="description" style='width: 100%; height: 150px;'>{$session_params.description|default:''}</textarea>
			<br />
			<br />
			<br />
			<div class='fl-rght' style='max-width: 300px;'>
				<label class='sys'>
					<input type='checkbox' name='agree' value='1' checked="checked" /> С пользовательским соглашением на предоставление услуг интернет-магазином Младенец.ру согласен.
				</label>
				<p><a href="/about/agreement.php" target="_blank">читать пользовательское соглашение</a></p>
				<p class="cart-delivery-price"></p>
				<a class='cart-delivery-send-link butt' style='text-align: center;'>Отправить заказ</a>
				<script>
					$(function(){
						if( $('[name=delivery_type]').val() == "2" ){
							$('.cart-delivery-send-link').attr("disabled", "disabled");
						}
					});
				</script>
			</div>
		</td>
	</tr>
</table>
</form>