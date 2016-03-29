{* google adwords remarketing params *}
<script>
    var google_tag_params = {
        ecomm_pagetype: 'other'
    };
</script>

{* findologic search *}
{if $config->instant_search == 'findologic'}
<script>
    (function() {
        var flDataMain = "https://cdn.findologic.com/autocomplete/009E9CF70F589A977CAC2A17D2A33351/autocomplete.js";
        var flAutocomplete = document.createElement('script'); 
        flAutocomplete.type = 'text/javascript'; 
        flAutocomplete.async = true;
        flAutocomplete.src = "https://cdn.findologic.com/autocomplete/require.js";
        var s = document.getElementsByTagName('script')[0];
        flAutocomplete.setAttribute('data-main', flDataMain);
        s.parentNode.insertBefore(flAutocomplete, s);
    })();
</script>    
<script>
    var _paq = _paq || [];
    (function(){ var u=(("https:" == document.location.protocol) ? "https://tracking.findologic.com/" : "http://tracking.findologic.com/");
    _paq.push(['setSiteId', '009E9CF70F589A977CAC2A17D2A33351']);
    _paq.push(['setTrackerUrl', u+'tracking.php']);
    _paq.push(['trackPageView']);
    _paq.push(['enableLinkTracking']);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0]; g.type='text/javascript'; g.defer=true; g.async=true; g.src=u+'tracking.js';
    s.parentNode.insertBefore(g,s); })();
</script>    
{/if}

<div id="all">
    <div id="head">

        {$ad->html('banner_top')}

        <a href="{Route::url('index')}" id="logo"><img src="/i/mladenec/logo.png" alt="" /></a>

        <div id="simple_menu">
            <a href="/about">О магазине<i class="i_shop"></i></a>
            <a href="/delivery">Доставка и оплата<i class="i_ship"></i></a>
            <a href="/contacts">Контакты<i class="i_addr"></i></a>
        </div>

        {include file='common/top_menu.tpl'}
		{include file='common/searchform.tpl'}
    </div>

{if empty($main) AND empty($is_pampers)}
    {$ad->html('banner_950X60_1')}
{/if}

    <div id="body"{if not empty($main)} class="index"{elseif not empty($is_pampers)} class="pampers-page"{/if}>
        {if not empty($is_pampers)}{$ad->html('banner_950X60_1')}{/if}
        {if not empty($menu)}
            <div id="side">
                {$menu|default:''}
                <a href="/catalog" id="ctg">Карта товарного каталога</a>

                {if not empty($main)}
                    <div id="last_comment">
                        <a class="big" href="{Model_Comment::get_list_link()}">Отзывы</a>
                        {foreach from=Model_Comment::last(2) item=c}
                            <p class="comment">
                                <a href="{$c->get_link(0)}">{$c->user_name}</a><i></i>
                                <strong>{$c->name}</strong>
                                {$c->text|truncate:100}
                                <a href="{$c->get_link(0)}" class="l">Ответ магазина<i></i></a>
                            </p>
                            <small>{$c->get_answer_by($c->answer_by)}</small>
                        {/foreach}
                    </div>
                {/if}
            </div>
        {/if}

        <div id="content">
            {$body|default:''}
        </div>

        {$after_body|default:''}

    </div>

    <div id="bo">
        {$ad->html('banner_300X210_4')}
        {$ad->html('banner_300X210_5')}
        {$ad->html('banner_300X210_6')}
    </div>

    <div id="xxfoot">
        {$foot_menu|default:''|strip}

        {if not empty($main)}
        <div id="seofoot">

	        <p>Магазин детских товаров Младенец.ру<br />
		        С&nbsp;появлением <nobr>интернет-магазинов</nobr> делать покупки для ребёнка стало гораздо проще&nbsp;&mdash; для этого даже не&nbsp;нужно выходить из&nbsp;дома. Утомительные поездки по&nbsp;городу, капризничающий в&nbsp;супермаркете малыш, суета и&nbsp;нервозность&hellip; Забудьте! Теперь достаточно пары кликов мышкой, чтобы приобрести подгузники, салфетки, питание и&nbsp;другие <strong>детские товары</strong>, без которых никак не&nbsp;обойтись заботливым родителям.</p>
	        <p>Наш <strong>детский интернет магазин</strong>&nbsp;&mdash; верный помощник для всех мам и&nbsp;пап. На&nbsp;его виртуальных полках представлены тысячи позиций, начиная от&nbsp;детского питания и&nbsp;средств гигиены и&nbsp;заканчивая мебелью и&nbsp;детским транспортом. Автокресла, коляски, игрушки, одежда, смеси, книги&nbsp;&mdash; какие&nbsp;бы <strong>товары и&nbsp;детские</strong> вещи Вам ни&nbsp;понадобились, Вы&nbsp;всегда сможете найти их&nbsp;в&nbsp;<nobr>mladenec-shop</nobr>.ru!</p>
	        <p>Широкий ассортимент нашего магазина постоянно обновляется: мы&nbsp;делаем всё возможное для того, чтобы Вы&nbsp;смогли приобрести новинки от&nbsp;ведущих производителей товаров для детей. Особое внимание уделяется японским подгузникам и&nbsp;косметике, пользующимся высоким спросом у&nbsp;наших клиентов. Родители, пока лишь ожидающие появления малыша, найдут в&nbsp;<nobr>mladenec-shop</nobr>.ru все необходимые <strong>детские товары для новорождённых</strong>.</p>
	        <p><em><strong><nobr>Интернет-магазин</nobr> детских товаров</strong> <nobr>mladenec-shop</nobr>.ru&nbsp;&mdash; экономия вашего времени и&nbsp;денег</em><br />
	        Вам не&nbsp;придётся заранее планировать свой день для совершения нужных покупок. Вы&nbsp;сможете ознакомиться с&nbsp;ассортиментом детских товаров в&nbsp;удобное для вас время, а&nbsp;также, благодаря качественным фотографиям, внимательно рассмотреть любую заинтересовавшую вас вещь.</p>
	        <p>При желании Вы&nbsp;сможете привлечь и&nbsp;ребёнка к&nbsp;процессу покупки&nbsp;&mdash; ему обязательно понравится выбирать одежду, игрушки и&nbsp;другие <strong>товары для детей</strong>. Ну, а&nbsp;Вам при этом не&nbsp;придётся беспокоиться о&nbsp;том, что дражайшее чадо разобьёт или испортит <nobr>что-нибудь</nobr> дорогостоящее.</p>
	        <p>Детский магазин <nobr>mladenec-shop</nobr>.ru работает днём и&nbsp;ночью, в&nbsp;том числе в&nbsp;моменты, пока ваш малыш увлечённо играет с&nbsp;игрушками или смотрит телевизор. Теперь Вы&nbsp;и&nbsp;только Вы&nbsp;решаете, когда и&nbsp;где посетить <strong>детский магазин&nbsp;&mdash; Интернет</strong> почти всегда под рукой.</p>
	        <p>Курьерская доставка осуществляется по&nbsp;Москве и&nbsp;Московской области. Также вы&nbsp;можете совершить покупки в&nbsp;наших стационарных магазинах в&nbsp;Москве, Мытищах, Щелково, Юбилейном, Ивантеевке, Красногорске, Подольске, Химках. Доставка заказов по&nbsp;России осуществляется силой транспортных компаний.</p>
	        <p><em><nobr>mladenec-shop</nobr>.ru&nbsp;&mdash; <strong><nobr>интернет-магазин</nobr> для детей</strong> и&nbsp;родителей</em><br />
	        Младенец.ру&nbsp;&mdash; это:
	        <ul>
		        <li>привлекательные цены на&nbsp;<strong>детские товары</strong>, а&nbsp;также регулярные акции и&nbsp;действующая система скидок;</li>
		        <li>широкий ассортимент: детское питание, игрушки, мебель, транспорт, <strong>детские товары для новорождённых</strong> и&nbsp;пр.;</li>
		        <li>гарантированное качество: все <strong>детские товары</strong>, представленные в&nbsp;нашем <nobr>интернет-магазине</nobr>, имеют соответствующие сертификаты;</li>
		        <li>минимальное время	выполнения заказа;</li>
		        <li>обслуживание на&nbsp;высоте: сотрудники сделают всё для	того, чтобы вы&nbsp;остались довольны и&nbsp;самой вещью, и&nbsp;процессом её приобретения.</li>
	        </ul>
			<p>Если вам нужны качественные и&nbsp;недорогие <strong>детские товары, <nobr>интернет-магазин</nobr></strong> <nobr>mladenec-shop</nobr>.ru всегда рядом!</p>

        </div>
        {/if}

        <div id="foot2">
            <div class="fr txt-rht">
                <a href="{Route::url('user_error')}" id="error_button" rel="ajax" data-fancybox-type="ajax">Сообщить об ошибке</a><br />
                <img title="Принимаем к оплате Visa и Mastercard" src="/i/cards.png" alt="Принимаем к оплате Visa и Mastercard" />
                <!--noindex-->
                <a href="http://clck.yandex.ru/redir/dtype=stred/pid=47/cid=2508/*http://market.yandex.ru/shop/3812/reviews" id="ya_rating" rel="nofollow"><img src="http://clck.yandex.ru/redir/dtype=stred/pid=47/cid=2505/*http://grade.market.yandex.ru/?id=3812&action=image&size=0" border="0" width="88" height="31" alt="Читайте отзывы покупателей и оценивайте качество магазина на Яндекс.Маркете" /></a>
                <!--/noindex-->
            </div>

            <div class="fl">
                &copy;&nbsp;&laquo;Младенец.РУ&raquo;. Все права защищены.
                <a href="{Route::url('map')}" id="sitemap">Карта сайта</a><br />
                Использование материалов сайта разрешено только при наличии активной ссылки на&nbsp;источник.<br />
                <div id="promotop">
                    {if empty($main)}<!--noindex-->{/if}
                    <a href="https://www.facebook.com/mladenec.ru" class="fb" title="Мы в facebook" target="_blank"{if empty($main)} rel="nofollow"{/if}>Мы в facebook</a>
                    <a href="https://vk.com/mladenecshop" class="vk" title="Мы в контакте" target="_blank"{if empty($main)} rel="nofollow"{/if}>Мы в контакте</a>
                    <a href="https://twitter.com/mladenecshop" class="tw" title="Наш твиттер" target="_blank"{if empty($main)} rel="nofollow"{/if}>Наш твиттер</a>
                    <a href="https://www.instagram.com/mladenecshop" class="im" title="Наш Инстаграм" target="_blank"{if empty($main)} rel="nofollow"{/if}>Наш Инстаграм</a>
                    {if empty($main)}<!--/noindex-->{/if}
                </div>
            </div>
        </div>
    </div>
</div>
<a href="#" class="to-top-page" title="К началу страницы">Вверх</a>
<div id="loader" style="display:none;"></div>

{if Kohana::$environment eq Kohana::PRODUCTION}

{if ! empty($main)}<!-- verify-admitad: "b37b432df3" -->{/if}

{include file="common/mc.yandex.ru.tpl"}

<div id="li">
<!--LiveInternet counter--><script><!--
document.write("<a href='http://www.liveinternet.ru/click' target=_blank><img src='//counter.yadro.ru/hit?t44.3;r"+
escape(document.referrer)+((typeof(screen)=="undefined")?"":";s"+screen.width+"*"+screen.height+"*"+(screen.colorDepth?screen.colorDepth:screen.pixelDepth))
+";u"+escape(document.URL)+";h"+escape(document.title.substring(0,80))+";"+Math.random()+ "' alt='' title='LiveInternet' border='0' width='31' height='31'><\/a>")
//--></script><!--/LiveInternet-->
</div>

{* google adwords remarketing *}
<script>
    /* <![CDATA[ */
    var google_conversion_id = 961630544;
    var google_custom_params = window.google_tag_params;
    var google_remarketing_only = true;
    /* ]]> */
</script>
<script src="//www.googleadservices.com/pagead/conversion.js"></script>
<noscript>
    <div style="display:inline;">
        <img height="1" width="1" style="border-style:none;" alt="" src="//googleads.g.doubleclick.net/pagead/viewthroughconversion/961630544/?value=0&amp;guid=ON&amp;script=0"/>
    </div>
</noscript>
{* /google remarketing *}

{* контрольный пиксель *}
<img src="//code.directadvert.ru/track/240598.gif" width="1" height="1" />
{/if}