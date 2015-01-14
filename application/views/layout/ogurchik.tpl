<div id="all" {if not empty($allbg)}style="background-image:url(/i/ogurchik/section/{$allbg}.jpg);"{/if}>
    <div id="head">

        {if not empty($main) and empty($is_kiosk)}{$ad->html('banner_top')}{/if}

        {if empty($is_kiosk)}
            <div id="promotop">
                <a href="http://www.facebook.com/mladenec.ru" class="fb" title="Мы в facebook" target="_blank">Мы в facebook</a>
                <a href="http://vk.com/mladenecshop" class="vk" title="Мы в контакте" target="_blank">Мы в контакте</a>
                <a href="http://twitter.com/mladenecshop" class="tw" title="Наш твиттер" target="_blank">Наш твиттер</a>
                <a href="http://www.odnoklassniki.ru/group/55719798046774" class="ok" title="Мы в одноклассниках" target="_blank">Мы в одноклассниках</a>
            </div>
        {/if}
        <a href="/" id="logo"><img src="/i/ogurchik/logo.png" /></a>

        <div id="simple_menu">
            <a href="/about">О магазине<i class="i_shop"></i></a>
            <a href="/delivery">Доставка и оплата<i class="i_ship"></i></a>
            <a href="/contacts">Контакты<i class="i_addr"></i></a>
        </div>

        {include file='common/top_menu.tpl'}

        <form action="/search" method="get" id="search">
            <input type="text" name="q" value="{$search_query|default:''|escape:html}" class="q txt" placeholder="Поиск по каталогу" />
            <button type="submit" value=" " class="search_submit" style="display:inline;"><img src="/i/lupa-white.png" /></button>
        </form>
    </div>

    <div id="body">

        {assign var=bread value=''}
        {assign var=h1 value=''}

        {if preg_match('~.*(<div id="bread.*?/div>).*~isu', $body, $matches)}
            {assign var=bread value=$matches[1]}
            {assign var=body value=$body|replace:$bread:''}
        {/if}

        {if preg_match_all('~.*(<h1.*/h1>).*~isuU', $body, $matches)}
            {assign var=h1 value=$matches[1][0]}
            {assign var=body value=$body|replace:$h1:''}
        {/if}

        {if empty($main) and empty($is_kiosk)}{$ad->html('banner_eatmart')}{/if}

        {$bread}
        {$h1}

        {if empty($main)}
        <div id="good_good">
        {/if}
            {if not empty($menu)}
                <div id="side">{$menu|default:''}</div>
            {/if}

            <div id="content">
                {$body|default:'Eatmart.ru'}
            </div>
            <div class="cl"></div>

        {if empty($main)}
        </div>
        {/if}
    </div>

    {if empty($is_kiosk)}
    <div id="bo">
        {$ad->html('eatmart2')}
        {$ad->html('eatmart3')}
        {$ad->html('eatmart4')}
    </div>
    {/if}

    <div id="xxfoot">
        {$foot_menu|default:''}

        {if not empty($main)}
            <div id="seofoot">
                <p>Мы&nbsp;осуществляем доставку продуктов питания в&nbsp;удобное для вас время. В&nbsp;нашем интернет магазине вы&nbsp;сможете заказать и&nbsp;купить онлайн свежие продукты питания. Выгодные условия доставки по&nbsp;Москве и&nbsp;Московской области. Экономьте свое драгоценное время вместе с&nbsp;нами!<br />
                    Жизнь в&nbsp;мегаполисе ставит жесткие временные рамки перед всеми. И&nbsp;тратить драгоценные минуты в&nbsp;супермаркетах, очередях к&nbsp;кассам, пробках&nbsp;&mdash; очень жаль. Можно найти этому времени более приятное применение: поиграть с&nbsp;детьми, пообщаться с&nbsp;друзьями. А&nbsp;приобрести продукты питания на&nbsp;дом поможет <nobr>интернет-магазин</nobr> Eatmart.ru.<br />
                Ресурсы интернета дарят возможность во&nbsp;время разгара рабочего дня заказать продукты на&nbsp;дом. И&nbsp;уже к&nbsp;ужину курьерская доставка продуктов привезет их&nbsp;без опозданий. Если требуется срочная доставка продуктов на&nbsp;дом, то&nbsp;наш <nobr>интернет-магазин</nobr> не&nbsp;откажет своим покупателям и&nbsp;в&nbsp;этом.<br />
                Ассортимент продуктов питания на&nbsp;сайте Eatmart.ru очень большой и&nbsp;выгодно отличается от&nbsp;ассортимента многих современных супермаркетов. На&nbsp;&laquo;полках&raquo; нашего <nobr>интернет-магазина</nobr> можно найти продукцию только самого высокого качества по&nbsp;доступной цене.<br />
                Eatmart.ru предлагает купить продукты на&nbsp;дом на&nbsp;просторах своего сайта и&nbsp;воспользоваться услугой бесплатной доставки. В&nbsp;нашем <nobr>интернет-магазине</nobr> можно приобрести воды, соки, напитки от&nbsp;всех известных производителей. У&nbsp;нас очень большой ассортимент разнообразных круп (с&nbsp;ними каши станут любимым блюдом семьи), макаронных изделий из&nbsp;твердых сортов пшеницы, овощной и&nbsp;фруктовой консервации, сухих завтраков, которые так любят дети, кондитерских изделий на&nbsp;любой вкус, орехов и&nbsp;сухофруктов. У&nbsp;нас можно купить самые свежие молочные продукты с&nbsp;доставкой. А&nbsp;также приобрести незаменимые в&nbsp;кулинарном обиходе морскую и&nbsp;поваренную соль, тростниковый и&nbsp;свекольный сахар, разнообразные специи, муку разного сорта и&nbsp;вида, масло растительное и&nbsp;высококачественное масло оливковое и, конечно, чай, кофе и&nbsp;какао.<br />
                Заказ продуктов питания на&nbsp;дом&nbsp;&mdash; сервис, который экономит драгоценное время. А&nbsp;доставка продуктов на&nbsp;дом дает возможность осуществить мечту: приехать домой пораньше.<br />
                Если у&nbsp;Вас нет времени закупить продукты к&nbsp;ужину, нет возможности сходить в&nbsp;магазин&nbsp;&mdash; приобрести дешевые продукты на&nbsp;дом поможет <nobr>интернет-магазин</nobr> Eatmart.ru. Он&nbsp;станет помощником мамам, находящимся в&nbsp;декретном отпуске, заказать продукты можно будет одним нажатием клавиши, а&nbsp;в&nbsp;<nobr>интернет-магазине</nobr> Младенец.ru они без труда приобретут все товары для своих малышей.<br />
                Наша курьерская служба осуществит срочную доставку продуктов питания на&nbsp;дом по&nbsp;Москве и&nbsp;Московской области.</p>
            </div>
        {/if}

        <div id="foot2">
            <a href="/"></a>
            &copy;&nbsp;&laquo;Младенец.РУ&raquo;. Все права защищены. <a href="/user/error" id="error_button" rel="ajax" data-fancybox-type="ajax">Сообщить об ошибке</a><br />
            Использование материалов сайта разрешено только при наличии активной ссылки на&nbsp;источник.
        </div>
    </div>
</div>

{if empty($is_kiosk) and Kohana::$environment eq Kohana::PRODUCTION}
{literal}
    <!-- Yandex.Metrika counter -->
    <div style="display:none;"><script type="text/javascript">
    (function(w, c) {
        (w[c] = w[c] || []).push(function() {
            try {
                w.yaCounter21930301 = new Ya.Metrika({id:21930301, enableAll: true, ut:"noindex", webvisor:true});
            }
            catch(e) { }
        });
    })(window, "yandex_metrika_callbacks");
    </script></div>
    <script src="//mc.yandex.ru/metrika/watch.js" type="text/javascript" defer="defer"></script>
    <noscript><div><img src="//mc.yandex.ru/watch/21930301?ut=noindex" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
    <!-- /Yandex.Metrika counter -->
{/literal}
{/if}
