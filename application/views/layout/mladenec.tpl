<div id="all">
    <div id="head">
        {if empty($is_kiosk)}
            {$ad->html('banner_top')}

            <div id="promotop">
                <a href="http://www.facebook.com/mladenec.ru" class="fb" title="Мы в facebook" target="_blank">Мы в facebook</a>
                <a href="http://vk.com/mladenecshop" class="vk" title="Мы в контакте" target="_blank">Мы в контакте</a>
                <a href="http://twitter.com/mladenecshop" class="tw" title="Наш твиттер" target="_blank">Наш твиттер</a>
                <a href="http://www.odnoklassniki.ru/group/55719798046774" class="ok" title="Мы в одноклассниках" target="_blank">Мы в одноклассниках</a>
            </div>
        {/if}

<a href="/" id="logo"><img src="/i/mladenec/logo.png" alt="" /></a>

        <div id="simple_menu">
            <a href="/about">О магазине<i class="i_shop"></i></a>
            <a href="/delivery">Доставка и оплата<i class="i_ship"></i></a>
            <a href="/contacts">Контакты<i class="i_addr"></i></a>
        </div>

        {include file='common/top_menu.tpl'}

        <form action="/search{Txt::view_params('')}" method="get" id="search">
            <input type="text" name="q" value="{$smarty.get.q|default:''|escape:html}" class="q txt" placeholder="Поиск по каталогу" />
            <button type="submit" value=" " class="search_submit" style="display:inline;"><img src="/i/lupa-white.png" alt="искать" /></button>
        </form>
    </div>

{if empty($is_kiosk) and empty($main)}
    {$ad->html('banner_950X60_1')}
{/if}

    <div id="body"{if not empty($main)} class="index"{/if}>

        {if not empty($menu)}
            <div id="side">
                {$menu|default:''}
                <a href="/catalog" id="ctg">Карта товарного каталога</a>

                {if not empty($main)}
                    <div id="last_comment">
                        <a class="big" href="{Model_Comment::get_list_link()}">Отзывы</a>
                        {if not $is_kiosk}
                            {foreach from=Model_Comment::last(2) item=c}
                                <p class="comment">
                                    <a href="{$c->get_link(0)}">{$c->user_name}</a><i></i>
                                    <strong>{$c->name}</strong>
                                    {$c->text|truncate:100}
                                    <a href="{$c->get_link(0)}" class="l">Ответ магазина<i></i></a>
                                </p>
                                <small>{$c->get_answer_by($c->answer_by)}</small>
                            {/foreach}
                        {/if}
                    </div>
                {/if}

            </div>
        {/if}

        <div id="content">
            {$body|default:''}
        </div>

    </div>

{if empty($is_kiosk)}
    <div id="bo">
        {$ad->html('banner_300X210_4')}
        {$ad->html('banner_300X210_5')}
        {$ad->html('banner_300X210_6')}
    </div>
{/if}

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
            <a href="/"></a>
            &copy;&nbsp;&laquo;Младенец.РУ&raquo;. Все права защищены. <a href="/user/error" id="error_button" rel="ajax" data-fancybox-type="ajax">Сообщить об ошибке</a><br />
            Использование материалов сайта разрешено только при наличии активной ссылки на&nbsp;источник.

{if Kohana::$environment == Kohana::PRODUCTION}
{literal}
<div id="li">
<!--LiveInternet counter--><script type="text/javascript"><!--
document.write("<a href='http://www.liveinternet.ru/click' target=_blank><img src='//counter.yadro.ru/hit?t44.3;r"+
escape(document.referrer)+((typeof(screen)=="undefined")?"":";s"+screen.width+"*"+screen.height+"*"+(screen.colorDepth?screen.colorDepth:screen.pixelDepth))
+";u"+escape(document.URL)+";h"+escape(document.title.substring(0,80))+";"+Math.random()+ "' alt='' title='LiveInternet' border='0' width='31' height='31'><\/a>")
//--></script><!--/LiveInternet-->
</div>
{/literal}
{/if}
            <a href="/site_map/list.php" id="sitemap">Карта сайта</a>

            <a href="http://clck.yandex.ru/redir/dtype=stred/pid=47/cid=2508/*http://market.yandex.ru/shop/3812/reviews" id="ya_rating"><img src="http://clck.yandex.ru/redir/dtype=stred/pid=47/cid=2505/*http://grade.market.yandex.ru/?id=3812&action=image&size=0" border="0" width="88" height="31" alt="Читайте отзывы покупателей и оценивайте качество магазина на Яндекс.Маркете" /></a>

        </div>
    </div>
</div>

{*$ad->html('banner_bg')*}

{if empty($is_kiosk) and Kohana::$environment eq Kohana::PRODUCTION}
{literal}
<!-- Yandex.Metrika counter -->
<div style="display:none;"><script type="text/javascript">
(function(w, c) {
    (w[c] = w[c] || []).push(function() {
        try {
            w.yaCounter11895307 = new Ya.Metrika({id:11895307, enableAll: true, ut:"noindex", webvisor:true});
        }
        catch(e) { }
    });
})(window, "yandex_metrika_callbacks");
</script></div>
<script src="//mc.yandex.ru/metrika/watch.js" type="text/javascript" defer="defer"></script>
<noscript><div><img src="//mc.yandex.ru/watch/11895307?ut=noindex" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->
{/literal}
{/if}
