{if empty($cart->delivery_open)}

    <div class="fr">
        <a class="butt fl" href="{Route::url('index')}">Продолжить покупки</a>
        <input type="submit" value="Оформить заказ" name="delivery_open" class="butt fl ml11" id="button-send" />
    </div>

{elseif Model_User::logged()}
    <form class="cart-delivery ajax" method="post" action="">
        {if isset($cart->use_ozon_delivery) && $cart->use_ozon_delivery}
            <h2>Выбор города доставки: <a href="{Route::url('user_city')}" rel="ajax" data-fancybox-type="ajax" id="current_city_cart">{Session::instance()->get('city')}{*<small>ваш город</small>*}</a></h2>                       
        {/if}
        {if isset($cart->use_ozon_delivery) && $cart->use_ozon_delivery 
            && isset($cart->ozon_terminals) && count($cart->ozon_terminals)>0}
            <ul class="cart-tabs">
                <li class="active" data-show-tab="courier-tab">Адрес доставки</li>
                <li data-show-tab="ozon-terminals-tab">Самовывоз</li>
            </ul>
            <div id="ozon-terminals-tab" class="tab">                
                <p class="info-warning{if !$cart->ozon_terminals->error} hide{/if}" id="ozon-error">Самовывоз доступен только для негабаритных товаров до 20 кг</p>
                <p class="info-warning{if !$cart->ozon_terminals->warning} hide{/if}" id="ozon-warning">Ваша корзина содержит скоропортящуюся молочную продукцию</p>
                
                <div id="terminal-data"{if $cart->ozon_terminals->error} class="hide"{/if}>
                    <h3>Выбирите терминал:</h3>
                    <div id="ozon-terminals-map"></div>
                    {if isset($cart->ozon_terminals->items)}                        
                        <div class="ozon-terminals-choose">                            
                            <input type="hidden" name="ozon_delivery_id" value="">
                        {foreach from=$cart->ozon_terminals->items item=terminal name=terminal_loop}
                            {if isset($terminal->lat)}
                            <label><input type="radio" name="ozon_terminal" value="{$terminal->address}" data-id="{$terminal->id}" data-index="{$smarty.foreach.terminal_loop.iteration}" data-lat="{$terminal->lat}" data-lng="{$terminal->lng}" {if $smarty.foreach.terminal_loop.first}checked{/if}>{$terminal->address}</label>
                            {/if}
                        {/foreach}          
                        </div>
                    {/if}   
                    <h3 class="mt">Уточните ваши данные:</h3> 
                    <div>
                        <em>заказ выдаётся по&nbsp;документу, удостоверяющему личность</em>
                        <dl class="cb">
                            <dt><label for="last_name">Фамилия<sup>*</sup>:</label></dt>
                            <dd><label><input id="last_name" name="last_name" value="{$user->last_name|escape:html}" class="txt"></label></dd>
                            <dt class="cl"><label for="name">Имя<sup>*</sup>:</label></dt>
                            <dd><label><input id="name" name="name" value="{$user->name|escape:html}" class="txt"></label></dd>
                            <dt class="cl"><label for="second_name">Отчество:</label></dt>
                            <dd><label><input id="second_name" name="second_name" value="{$user->second_name|escape:html}" class="txt"></label></dd>
                        </dl>
                    </div>
                </div>
            </div>
            <script type="text/javascript">                
                initCartTabs();
                {if isset($cart->ozon_terminals->center_lat) && isset($cart->ozon_terminals->center_lng)}
                var markers = [], terminalMap = null;
                $.getScript('http://api-maps.yandex.ru/2.1/?lang=ru_RU&coordorder=longlat', function() {                     
                    $('#ozon-terminals-map').empty();
                    ymaps.ready(function () {
                        terminalMap = new ymaps.Map('ozon-terminals-map', {
                            center: [{$cart->ozon_terminals->center_lat}, {$cart->ozon_terminals->center_lng}],
                            zoom: 10,
                            controls: ['zoomControl']
                        }, { minZoom: 8 });
                        $.each($('#ozon-terminals-tab input[name="ozon_terminal"]'), function(){
                            var lat = parseFloat($(this).data('lat'));
                            var lng = parseFloat($(this).data('lng'));
                            var index = $(this).data('index');
                            markers[index] = new ymaps.Placemark( [lat, lng], {
                                         balloonContentBody: $(this).val()
                                   }
                            );
                            if(index == 1) {                            
                                markers[index].options.set('preset', 'islands#redIcon');
                            }
                            markers[index].events.add('click', function (e) {
                               selectTerminal(index);
                            });
                            terminalMap.geoObjects.add(markers[index]);
                        });
                    });
                });
                function selectTerminal(index) {
                    var checked_elem = $('#ozon-terminals-tab input[data-index='+index+']');
                    checked_elem.click();
                    $('.ozon-terminals-choose').animate({
                        scrollTop: checked_elem.offset().top
                    }, 0);
                    for(var i in markers) {
                        if(i != index) markers[i].options.set('preset', 'islands#blueIcon');
                    }
                    markers[index].options.set('preset', 'islands#redIcon');
                    getOzonPrice();
                }
                getOzonPrice();
                function getOzonPrice() { 
                    var sd = $('#ship_date'), st = $('#ship_time');
                    st.empty();
                    st.append('<i class="load"></i>');
                    sd.empty();
                    sd.append('Считаем стоимость доставки...');
                    $('#ship_price').text('считаем...');
                    var delivery_id = $('input[name="ozon_terminal"]:checked').data('id');
                    $('input[name="ozon_delivery_id"]').val(delivery_id);
                    $.post('{Route::url('delivery_ozon')}',
                        { delivery_id : delivery_id },
                        function(data) {
                            if(data.settings.status_error) {
                                $.when($('#terminal-data').addClass('hide'), $('#ozon-warning').addClass('hide'))
                                 .then($('#ozon-error').removeClass('hide'));
                                 $('input[name="ozon_delivery_id"]').val('');
                            } else if(data.settings.status_warning) {
                                $.when($('#ozon-error').addClass('hide'), $('#ozon-warning').removeClass('hide'))
                                 .then($('#terminal-data').removeClass('hide'));
                            } else {
                                $.when($('#ozon-error,#ozon-warning').addClass('hide'))
                                 .then($('#terminal-data').removeClass('hide'));
                            }
                            if(typeof(data.settings.warning) != 'undefined') {
                                $('#ship_price').text('сообщит менеджер');
                            } else {
                                $('#ship_price').html(data.settings.price+' <small>р.</small>');
                            }
                            st.empty();
                            $.when( sd.html(data.html)).then(
                                function(){
                                    if(sd.find('input').length != 0) {
                                        $('input', sd).mladenecradio( { onClick: addr.showShipPrice });
                                    }
                                }
                            );
                        }, 'json');
                }
                {/if}
            </script>            
        {else}
            {foreach from=$cart->goods item=good}
                {assign var="def" value="0"}
                {if count($cart->goods) eq 1}
                    {if $cart->gift_sum eq 0}
                        {$def = 1}
                    {else}
                        <input type="hidden" name="syst_gift" value="1">
                        {$def = 0}
                    {/if}
                {else}
                    {$def = 1}
                {/if}
            {/foreach}
            {if $def == 1}
                <h2 id="delivery_address">Адрес доставки</h2>
            {/if}
        {/if}


        {if $def == 1}
            <div id="courier-tab" class="tab active">
            {if not empty($user) && $user->address()} {* адреса юзера *}
                <div id="address">
                    {foreach from=$user->address() item=a name=a}
                        {capture assign=addr}{$a->city}, {$a->street}, дом {$a->house}{if $a->kv}, кв./оф. {$a->kv}{/if}{/capture}
                        <label title="{$addr|trim}"><input type="radio" name="address_id" value="{$a->id}" {if $smarty.foreach.a.first}checked{/if}/>{$addr}
                            {if $a->correct_addr or $a->approved}.{/if}
                        </label>
                    {/foreach}
                </div>
            {/if}

            {*<script src="https://api-maps.yandex.ru/2.0/?load=package.standard,package.geoQuery&lang=ru-RU"></script>
            <script src="https://delivery.yandex.ru/widget/loader?resource_id=3127&sid=1540&key=2e3bad24bd1b2e1349c2f47293da27b2"></script>*}

            <div id="map-wrap">
                <input id="last_geocode" type="hidden" />
                <div id="addr-map">
                    {if not empty($latlong)}
                        <img src="http://static-maps.yandex.ru/1.x/?size=450,450&l=map&z=16&ll={$latlong}&pt={$latlong},flag" alt="Точка доставки на карте" />
                    {else}
                        <div id="chooser">Пожалуйста, заполните адрес доставки</div>
                    {/if}
                </div>
                <dl class="cb">
                    <dt>Координаты:</dt>
                    <dd><input id="real-latlong" class="plain" name="latlong" value="{$latlong|default:''}" readonly style="width:300px;" /></dd>
                    <dt class="cb">Зона доставки:</dt>
                    <dd>
                        <input name="ship_zone" id="real-zone" type="hidden" value="0" />
                        <input id="mkad" name="mkad" type="hidden" value="0" />
                        <input id="dpd_city_id" value="0" class="hide" />
                        <abbr id="real-zone-name" abbr="Зона доставки определяется по координатам точки доставки.">Не определена</abbr>
                    </dd>
                </dl>
            </div>

            <div class="addr" id="addr">

                <div id="real-address">

                    <input type="radio" name="address_id" value="0" class="hide" />

                    <input type="hidden" id="real-correct_addr" name="correct_addr" value="0" />
                    <input type="hidden" id="delivery_type" name="delivery_type" value="0" />

                    <a class="do" id="addr_add">Очистить адрес</a>

                    <dl id="addr-common" class="cb">
                        <dt><label for="city">Город<sup>*</sup>:</label></dt><dd id="real-city"><input required name="city" value="{$o->city|default:Session::instance()->get('city')|default:''}" class="txt" id="city" placeholder="?" style="width:300px"/></dd>
                        <dt class="cl"><label for="street">Улица<sup>*</sup>:</label></dt><dd id="real-street"><input required name="street" value="{$o->street|default:''}" class="txt" placeholder="?" id="street" style="width:300px" /></dd>
                        <dt class="cl"><label for="house">Дом<sup>*</sup>:</label></dt><dd id="real-house"><input required name="house" value="{$o->house|default:''}" class="txt short" id="house" placeholder="?"/></dd>
                        <dt><label for="kv">Квартира/офис:</label></dt><dd id="real-kv"><input name="kv" id="kv" value="{$o->kv|default:''}" class="txt short" /></dd>
                    </dl>
                    <em>Если стоимость доставки не определилась, Вы всё равно можете отправить заказ. Менеджер сообщит стоимость доставки дополнительно.</em>

                    <h3 class="mt">Информация для курьера:</h3>
                    <dl id="addr-courier" class="cb">
                        <dt><label for="enter">Подъезд:</label></dt><dd id="real-enter"><input name="enter" id="enter" value="{$o->enter|default:''}" class="txt short" /></dd>
                        <dt><label for="floor">Этаж:</label></dt><dd id="real-floor"><input name="floor" id="floor" value="{$o->floor|default:''}" class="txt short" /></dd>
                        <dd id="real-lift">
                            <label>
                                Лифт <abbr class="ml11" abbr="Поставьте галочку если есть лифт. Для&nbsp;заказов тяжелее 10кг при&nbsp;отсутствии лифта подъем на&nbsp;этаж платный.">?</abbr>
                                <input type="checkbox" name="lift" id="lift" value="1" {if $o->lift|default:0}checked{/if} />
                            </label>
                        </dd>
                        <dt class="cl"><label for="domofon">Домофон:</label></dt><dd id="real-domofon"><input name="domofon" id="domofon" value="{$o->domofon|default:''}" class="txt" /></dd>
                        <dt class="cl"><label for="real-comment">Особенности проезда:</label></dt>
                        <dd><textarea class="cl" id="real-comment" name="comment" placeholder="Укажите здесь особенности проезда на автомобиле"></textarea></dd>
                        <dt><label class="l" for="call">Звонок курьера</label></dt>
                        <dd><select name="call" id="call" class="fl">{html_options options=Model_Order::delivery_call() selected=$o->call|default:''}</select></dd>

                        <dd><label class="l cb"><input class="checkbox" type="checkbox" name="no_ring" /> Не звонить в&nbsp;дверь</label></dd>

                    </dl>

                    <div id="addr-region" class="hide">
                        <em>заказ выдаётся по&nbsp;документу, удостоверяющему личность</em>
                        <dl class="cb">
                            <dt><label for="last_name">Фамилия<sup>*</sup>:</label></dt>
                            <dd><label><input id="last_name" name="last_name" value="{$user->last_name|escape:html}" class="txt" /></label></dd>
                            <dt class="cl"><label for="name">Имя<sup>*</sup>:</label></dt>
                            <dd><label><input id="name" name="name" value="{$user->name|escape:html}" class="txt" /></label></dd>
                            <dt class="cl"><label for="second_name">Отчество:</label></dt>
                            <dd><label><input id="second_name" name="second_name" value="{$user->second_name|escape:html}" class="txt" /></label></dd>
                        </dl>
                    </div>

                </div>
            </div>
        </div>
        {/if}

        <h2>Звонки и SMS</h2>
        <div>
            <input type="hidden" name="email" value="{$user->email|default:''}" />
            <div class="half cl fl">
                <label class="l" for="phone">Телефон для связи</label>
                <input class="txt" type="tel" name="phone" id="phone" value="{$user->phone}" placeholder="+7(9__)___-__-__" required="required" />
                {if $user->sum}
                    <label class="l cb"><input class="checkbox" type="checkbox" name="no_call" /> Не звонить если всё в&nbsp;наличии</label>
                {/if}
            </div>

            <div class="half fl">
                {if $user->phone && Txt::phone_is_mobile($user->phone)}
                    {assign var=mobile_phone value=$user->phone}
                {elseif ($user->phone2 && Txt::phone_is_mobile($user->phone2))}
                    {assign var=mobile_phone value=$user->phone2}
                {/if}

                <label class="l" for="mobile_phone">Смс на номер</label>
                <input type="tel" class="txt" name="mobile_phone" id="mobile_phone" value="{$mobile_phone|default:''}" placeholder="+7(9__)___-__-__"/>

            </div>
        </div>

        {if $def == 1}
            <h2 id="delivery">Доставка</h2>
            <div id="ship_datetime">
                <div class="half fl" id="ship_date">
                    <p>Время и&nbsp;стоимость доставки уточнит менеджер поcле приёма Вашего заказа</p>
                </div>

                <div class="half fl" id="ship_time"></div>
            </div>
        {/if}

        <h2>Оплата</h2>
        <div id="cart-payment">
            {*{foreach from=$cart->goods item=good}
                {assign var="def" value="0"}
                {if strpos($good.code, "syst_gift") === false}
                    {$def = 1}
                {/if}
            {/foreach}*}

            {if $def == 1}
                <div class="half fl">
                    <label><input type="radio" name="pay_type" value="{Model_Order::PAY_DEFAULT}"
                                {if empty($session_params.pay_type) || $session_params.pay_type eq Model_Order::PAY_DEFAULT} checked="checked"{/if} />
                        Наличный расчет</label>

                    <em>Оплата заказа и&nbsp;доставки производится наличными при получении</em>
                </div>
            {/if}


            <div class="half fl">
                <label><input type="radio" name="pay_type" value="{Model_Order::PAY_CARD}"
                            {if $session_params.pay_type eq Model_Order::PAY_CARD} checked="checked"{/if} />
                    Оплата картой <img src="/i/cards.png" alt="Visa, Mastercard" class="fr" /></label>

                {*if ! empty($cart->to_wait)}

                    <em>В&nbsp;заказе есть товары, которых может не&nbsp;быть на&nbsp;складе. <br />
                        Вы не&nbsp;сможете оплатить заказ картой прямо сейчас.<br />
                        Менеджер сообщит, когда заказ будет готов к&nbsp;оплате
                        <!--Мы сообщим Вам по&nbsp;email и&nbsp;SMS когда заказ будет готов к&nbsp;оплате.-->
                    </em>

                {else}

                    <em>Вы сможете оплатить заказ картой сразу после отправки заказа.</em>
                {/if*}

                    <em>Оплата будет доступна в&nbsp;личном кабинете сразу после
                        проверки наличия товара и&nbsp;стоимости доставки. Мы&nbsp;сообщим Вам об&nbsp;этом
                        по&nbsp;email и/или&nbsp;SMS</em>
            </div>
        </div>

        <h3 class="mt">Комментарий для менеджера</h3>
        <textarea name="description" id="cart-desc" placeholder="Напишите здесь Ваши пожелания к заказу">{$session_params.description|default:''}</textarea>

        <p class="fr"><a class="do" href="/about/agreement.php" target="_blank">читать пользовательское соглашение</a></p>

        <label class="mt fl"><input type="checkbox" name="agree" value="1" checked />
            С&nbsp;пользовательским соглашением на&nbsp;предоставление услуг интернет-магазином Младенец.ру согласен.
        </label>

        <input type="button" class="cb butt txt-cntr" style="margin:auto; " id="make_order" value="Отправить заказ" />

    </form>

{else} {* 3 варианта *}

    {include file='cart/user.tpl'}

{/if}

{*if not $cart->can_ship()}
    Обращаем Ваше внимание, что по&nbsp;техническим ограничениям мы не&nbsp;осуществляем
    доставку по&nbsp;Российской Федерации товаров из&nbsp;следующих категорий:<br />
    {ORM::factory("section", 29051)->get_link()}<br />
    {ORM::factory("section", 32766)->get_link()}<br />
Продукты <a href="http://www.eatmart.ru">eatmart.ru</a>*}

