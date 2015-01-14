<div itemscope itemtype="http://schema.org/Product" {if $cgood->is_cloth()}class="cloth"{/if}>

{if empty($images)}
    {assign var=images value=$cgood->get_images()}
{/if}

{capture assign=good_name}{$group->name} {if not $group->good}{$goods[{$prop->id}]->name}{/if}{/capture}

{if $cgood->is_cloth()}
    {assign var=imgsize value='380x560'}
{else}
    {assign var=imgsize value='380'}
{/if}

{if empty($infancybox)}
    {if $cgood->show}
        <div class="zoombox {if $prop->img380}_380{/if}">
            <div class="zoombox_thumb">
                <div class="zoombox_magnifier"></div>
                <div class="zoombox_roll">
                    {foreach from=$images key=k item=i}
                        <img itemprop="image" src="{if ! empty($i[$imgsize])}{$i[$imgsize]->get_img(0)}{else}{$i.255->get_img(0)}{/if}" {if $k gt 0}class="hide"{/if} {if $i.1600}rel="{$i.1600->get_img(0)}"{/if} alt="{$good_name} {$k}" />
                        {foreachelse}
                        <img src="http://www.mladenec-shop.ru/images/no_pic70.png" alt="{$good_name}" />
                    {/foreach}
                </div>
                <img class="zoombox_magnifier_icon" src="/i/lupa.png" />
                {if $cgood->new}<div class="product_new_marker"><img src="/i/new.png" /></div>{/if}
            </div>
            {if count($images) gt 1 and empty( $infancybox )}
                <div class="zoombox_st">{foreach from=$images key=k item=i}{if $i.255}<img {if (k%3)}class="cl"{/if} src="{$i.255->get_img(0)}" alt="{$good_name}" />{/if}{/foreach}</div>
            {/if}
            <div class="share42init" data-path="/i/"></div>
            <script src="/j/share42.js"></script>
        </div>
    {else}
        {include file='product/view/notshowimage.tpl'}
    {/if}
{else}
    <div class="zoombox">
        <div class="zoombox_thumb">
            <div class="zoombox_magnifier"></div>
            <div class="zoombox_roll">
                {foreach from=$images key=k item=i}
                    {if $i.255}<img src="{$i.255->get_img(0)}" {if $k > 0}class="hide"{/if} {if $i.1600}rel="{$i.1600->get_img(0)}"{/if} alt="{$good_name}" />{/if}
                {/foreach}
            </div>
        </div>
    </div>
{/if}
<div class="zoombox_large"></div>

<div id="good-view">
<div id="view">
    <a name="{$cgood->id}"></a>
    <input type="hidden" id="good_id" value="{$cgood->id}" />
    <h1 itemprop="name">{$group->name} {if not $group->good}<span>{$cgood->name}</span>{/if}</h1>

    {if $cgood->show}
        <div id="to-write-review" >
            <span class="stars"><span style="width:{$cgood->rating*20}%"></span></span> ({$cgood->review_qty})

            {if $cgood->review_qty gt 0}
                <a href="#tabs">Посмотреть или написать отзыв</a>
                <script>
                    $(function(){
                        $('#to-write-review').click(function(){
                            $('#reviews').click();

                            $('html, body').animate({
                                scrollTop: $("a[name=tabs]").offset().top
                            }, 400);
                            return false;
                        });
                    });
                </script>
            {else}
                <a data-url="/review/add/" href="#{$prop->id}" class="small i i_pen appendhash" rel="ajax" data-fancybox-type="ajax">Написать отзыв</a>
            {/if}
        </div>

        {assign var=lovely value=Cart::instance()->status_id()}
        {assign var=lovely_price value=$price[$cgood->id]}
        {assign var=default_price value=$cgood->price}
        {if ! empty($lovely)}
            {assign var=current_price value=$lovely_price}
        {else}
            {assign var=current_price value=$default_price}
        {/if}

        <div id="good-price">
            <strong {if $cgood->old_price gt 0}class="no"{/if}>
                {$current_price|price}
            </strong>
            {if $cgood->old_price gt 0}<del>{$cgood->old_price|price}</del>{/if}
            {if not empty( $good_action[$cgood->id] )}
                {foreach from=$good_action[$cgood->id] item=icon key=action_id}
                    <abbr class="q" abbr="<b>{$icon.name}</b><br />{$icon.preview}"><a href="{Route::url('action', ['id' => $action_id])}"><img src="/i/gift-small.png" alt="Акция" /></a></abbr>
                {/foreach}
            {/if}
            <br />
            {if ($default_price neq $lovely_price)}
                {if $lovely}
                    <abbr abbr="Ваш статус - Любимый клиент">{$default_price|price} обычная цена</abbr>
                {else}
                    <abbr>{$lovely_price} для любимого клиента</abbr>
                {/if}
            {/if}
        </div>
    {/if}
</div>

{if $cgood->show}
    <div id="good-buy">


        {if $cgood->qty != 0 and ! $cgood->can_appear()}
            {assign var=active value=1}

            {if $cgood->is_cloth()}{* форма для одежды - с цветами и размерами *}

                <div id="cloth-choice">
                    <div id="good-colors">
                        <strong>Цвет</strong>
                        {foreach from=$colors key=color item=good_ids}
                            {assign var=good_id value=$good_ids[0]}
                            {if empty($goods[$good_id])}
                                <!-- {$good_id} lost-->
                            {else}
                                <a href="{$goods[$good_id]->get_link(0)}" class="good-item good-color
                                {if in_array($cgood->id, $good_ids)}{assign var=curcolor value=$color}a{/if}" title="{$goods[$good_id]->name}">
                                    <img src="{$colorimage[$color]}" alt="{$goods[$good_id]->name}" />
                                </a>
                            {/if}
                        {/foreach}
                    </div>
                    <div id="good-sizes">
                        {if not empty($allsizes)}
                            <strong>
                                {if $size_filter eq Controller_Product::FILTER_SIZE}
                                    Размер
                                {elseif $size_filter eq Controller_Product::FILTER_GROWTH}
                                    Размер, см
                                {elseif $size_filter eq Controller_Product::FILTER_AGE}
                                    Возраст
                                {/if}
                            </strong>
                            {foreach from=$allsizes key=vid item=name}
                                {assign var=good_ids value=$colorsize[$curcolor][$vid]|default:0}
                                {if empty($good_ids)}
                                    <a class="good-item good-growth notgrowth" title="нет">{$name}</a>
                                {else}
                                    {assign var=good_id value=$good_ids|current}
                                    {assign var=curgood value=$goods[$good_id]}
                                    {if not empty($curgood)}
                                        {assign var=glink value=$curgood->get_link(0)}
                                        {assign var=cur_size value=in_array($cgood->id, $good_ids)}

                                        {if not $curgood->can_appear() and $curgood->qty neq 0}
                                            <a {if empty($asize)}href="{$glink}"{/if} class="good-item good-growth {if ! empty($cur_size)}a{/if}" title="{$name}">{$name}</a>
                                        {else}
                                            <a href="{$glink}" class="good-item good-growth notgrowth" title="нет в наличии">{$name}</a>
                                        {/if}
                                    {/if}

                                {/if}

                            {/foreach}
                        {/if}
                    </div>
                </div>

                <form action="/product/add" method="post" style="margin-top:25px; float:left;">

                    <input type="hidden" name="mode" value="1" />
                    <input type="hidden" id="qty_{$cgood->id}" name="qty[{$cgood->id}]" value="1" oldval="{$q|default:0}" price="{$current_price}"/>

                    <a rel="{$cgood->id}" class="butt small i i_cart c" id="good-buy-butt">Добавить в&nbsp;корзину</a>


                    <div id="good-buy-oco">{$cgood|qty}</div
                </form>

                <script>
                    $(function(){
                        var t = '#ajax-product-card';
                        if ($(t).length == 0) t = '.fancybox-outer';

                        var loader = $('<div></div>').appendTo('body').hide();

                        $('a.good-item').click(function() {
                            {if empty($infancybox)}
                            history.pushState(null, null, this.href);
                            {/if}
                            var timeout = setTimeout(function() {
                                var offset = $(t).offset();
                                loader.css({
                                    backgroundColor: 'rgba(100,100,100,0.15)',
                                    position: 'absolute',
                                    top: offset.top+'px',
                                    left: offset.left+'px',
                                    width: $(t).width()+'px',
                                    height: $(t).height()+'px',
                                    zIndex: 9030 /* больше чем у fancybox */
                                }).show().fadeOut(0).fadeIn(500);
                            }, 300);

                            var url = $(this).attr('href');
                            $.post(url, { 'isajax': true {if ! empty( $infancybox )}, infancybox: true {/if} }, function(data) {

                                clearTimeout(timeout);
                                loader.stop(true, false).fadeOut(300, function() {
                                    $(this).hide();
                                });

                                if ( ! documentStack[location.href]) documentStack[location.href] = { };
                                documentStack[location.href][t] = $(t).html();

                                $(t).empty().append(data);
                                $('.buy > input:text').incdec();
                                if( typeof(zoombox_clickable) == 'function') zoombox_clickable();

                                updateLinks();

                                if ( ! documentStack[url]) documentStack[url] = { };

                                documentStack['http://' + '{$host}' + url]['#ajax-product-card'] = data;
                            });

                            return false;
                        });
                    });
                </script>


            {else}

                <form action="/product/add" method="post">

                    {if not empty($good->quantity)}{assign var=q value=$good->quantity}{/if}
                    {if not empty($can_buy)}{assign var=id value=$can_buy}{else}{assign var=id value=$good->id}{/if}

                    <div class="buy{if ! empty($can_buy)} wide{/if}">
                        <input type="hidden" name="mode" value="1" />
                        <input id="qty_{$cgood->id}" name="qty[{$id}]" value="1" oldval="{$q|default:0}" price="{$current_price}"/>
                        <span>шт</span>
                    </div>
                    {if $cgood->pack gt 1}
                        <label class="label">
                            <i class="check"></i>
                            <input type="checkbox" id="adding-pack-button" value="{$cgood->pack}" />добавлять упаковками по&nbsp;{'штуке'|plural:$cgood->pack}
                        </label>
                        <script>
                            $(document).ready(function(){
                                $('#adding-pack-button').click(function(){
                                    var v = $(this).val();
                                    if( $('#adding-pack-button:checked').length ){
                                        $('[name=mode]').val(v);

                                        if( $('#qty_{$cgood->id}').val() == 1 ){
                                            $('#qty_{$cgood->id}').val(v);
                                        }

                                        retotal($('#qty_{$cgood->id}'));
                                    }
                                    else{
                                        $('[name=mode]').val(1);
                                    }
                                });
                            });
                        </script>
                    {/if}
                    {if not empty($can_buy) and empty($no_cart)}
                        <a class="c" rel="{$id}"></a>
                    {/if}

                    {assign var=buyable value=$current_price}
                    {if $active}{assign var=total value=$current_price}{/if}
                </form>

                <div id="good-buy-itogo">
                    <div>Итого</div><span id="pricetotal">{$total|default:0|price}</span>
                </div>

                <a rel="{$cgood->id}" class="butt small i i_cart c" id="good-buy-butt">Добавить в&nbsp;корзину</a>

                <div id="good-buy-oco" {if $cgood->pack gt 1 and $can_one_click|default:0}style="top:-20px"{/if}>
                    {$cgood|qty}

                    {if $can_one_click|default:0}{include file='common/one_click.tpl' good_id=$cgood->id in_good=true}{/if}
                </div>
            {/if}

        {else}

            <div id="wewarn">
                Мы сообщим Вам по&nbsp;почте, когда товар появится на&nbsp;складе
            </div>
            <div>
                {if $cgood->can_appear()}
                    Нет в&nbsp;наличии {if $notInSale > 0}c <br />{Txt::ru_date($notInSale)}{/if}
                {else}
                    <a rel="ajax" data-url="/product/warn/" href="#{$cgood->id}" rel="{$cgood->id}" class="appendhash butt small" style="padding: 5px 50px; font-size: 1.3em; float: left;">Уведомить о&nbsp;поставке</a>
                {/if}
            </div>

        {/if}
    </div>
{/if}
</div>

<div id="good-code" class="cb">
    {assign var=sostav value=$prop->spoiler2|strip_tags|trim}
    <b>Артикул</b>: {$cgood->code}<br />
    <b>Код товара</b>: {$cgood->id1c}
    {if not empty($sostav) and mb_strlen($sostav) lt 100}
        <br /><br /><b>Состав</b>: {$sostav}
    {/if}
</div>

{if $cgood->show}


    {if not empty($infancybox)}
		<br clear="all" />
        <div style="width: 735px; background: #fff; margin: 10px;">
            {include file='product/view/info.tpl'}
            <a href="{$cgood->get_link(FALSE)}">Подробнее</a>
        </div>

    {else}

        {if not empty($notInSale)}
            <div class="alert alert-warning" style="margin-top: 15px; clear:left;">
                Этого товара нет в наличии {if $notInSale > 0}c {Txt::ru_date($notInSale)}{/if}<br />
                {if ! empty($tags)}
                    <strong>С этим товаром искали:</strong> {Model_Tag::links($tags, $prop->tags)}
                {/if}
            </div>
        {/if}

        <a name="tabs"></a>
        <div class="tabs mt">
            <div>
                <a class="active t">Описание</a>
                {if ! empty($prop->spoiler_title)}<a class="t">{$prop->spoiler_title}</a>{/if}
                {if ! empty($prop->spoiler2_title)}<a class="t">{$prop->spoiler2_title}</a>{/if}
                {if ! empty($prop->spoiler3_title)}<a class="t">{$prop->spoiler3_title}</a>{/if}
                {if ! empty($serts)}<a class="t">Сертификаты соответствия</a>{/if}
                <a id="reviews" class="t">Отзывы</a>
            </div>
            <div class="tab-content active">
                <div itemprop="description" class="txt">
                    {$prop->desc()}
                    {if not empty($filters)}
                        {foreach from=$filters key=fname item=vals}
                            <p>
                                <strong>{$fname}:</strong> {', '|implode:$vals}
                            </p>
                        {/foreach}
                        </p>
                    {/if}

                    {if ! empty($tags)}
                        <p><strong>С этим товаром искали:</strong> {Model_Tag::links($tags, $prop->tags)}</p>
                    {/if}
                </div>
            </div>
            {if ! empty($prop->spoiler_title)}
                <div class="tab-content">
                    <div class="txt spoiler oh">
                        {$prop->spoiler}
                    </div>
                </div>
            {/if}
            {if ! empty($prop->spoiler2_title)}
                <div class="tab-content">
                    <div class="txt spoiler oh">
                        {$prop->spoiler2}
                    </div>
                </div>
            {/if}
            {if ! empty($prop->spoiler3_title)}
                <div class="tab-content">
                    <div class="txt spoiler oh">
                        {$prop->spoiler3}
                    </div>
                </div>
            {/if}
            {if ! empty($serts)}
                <div class="tab-content">
                    <div class="txt sert oh">
                        {foreach from=$serts item=s}
                            <a href="{$s->big->get_img(0)}" title="{$s->name}" rel="sert">{$s->small->get_img()}</a>
                        {/foreach}
                    </div>
                </div>
            {/if}

            <div class="tab-content">
                <div class="review">
                    {if empty( $infancybox )}
                        <a data-url='/review/add/' href="#{$prop->id}" id="review_butt" class="small i i_pen appendhash" rel="ajax" data-fancybox-type="ajax" style="margin-left: 0; padding: 20px 0 20px 10px; font-size: 1.2em; color: #3fb3d5;"><b>&#12297;</b> <span style="text-decoration: underline;">Написать отзыв</span></a>
                    {/if}
                    <a name="reviews"></a>
                    <input name="group" id="reviews_for" type="hidden" value="0" />

                    <div><i class="load"></i></div>
                </div>
                <br />
            </div>
        </div>
    {/if}
{/if}
</div>
