<div itemscope itemtype="http://schema.org/Product" {if $cgood->is_cloth()}class="cloth"{/if}>

{assign var=good_pics value=$cgood->get_images()}

{capture assign=good_name}{$group->name|escape:html} {if not $group->good}{$goods[{$prop->id}]->name|escape:html}{/if}{/capture}

{if $cgood->is_cloth()}
    {assign var=imgsize value='380x560'}
{else}
    {assign var=imgsize value='380'}
{/if}

    {include file='product/view/zoombox.tpl'}

<div id="good-view">

    <div id="view">
        <a name="{$cgood->id}"></a>
        <input type="hidden" id="good_id" value="{$cgood->id}" />

        {include file='product/view/review.tpl'}

        <h1 itemprop="name">{$group->name} {if not $group->good}<span>{$cgood->name}</span>{/if}</h1>
        <meta itemprop="brand" content="{$cgood->brand->name}" />

        {assign var=lovely value=Cart::instance()->status_id()}
        {assign var=lovely_price value=$price[$cgood->id]}
        {assign var=default_price value=$cgood->price}
        {if ! empty($lovely)}
            {assign var=current_price value=$lovely_price}
        {else}
            {assign var=current_price value=$default_price}
        {/if}

        {include file='product/view/price.tpl'}

    </div>

{if $cgood->show}
    <div id="good-buy">

        {if $cgood->qty != 0}{* товар есть в наличии*}
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
                                    {if isset($goods[$good_id])}
                                        {assign var=curgood value=$goods[$good_id]}
                                        {if not empty($curgood)}
                                            {assign var=glink value=$curgood->get_link(0)}
                                            {assign var=cur_size value=in_array($cgood->id, $good_ids)}
                                            
                                            {if $curgood->qty neq 0}
                                                <a {if empty($asize)}href="{$glink}"{/if} class="good-item good-growth {if ! empty($cur_size)}a{/if}" title="{$name}">{$name}</a>
                                            {elseif $curgood->can_appear()}
                                                <a href="{$glink}" class="good-item good-growth notgrowth" title="нет в наличии">{$name}</a>
                                            {/if}
                                        {/if}
                                    {/if}

                                {/if}

                            {/foreach}
                        {/if}
                    </div>
                </div>

                <form action="{Route::url('product_add')}" method="post" style="margin-top:25px; float:left;">

                    <input type="hidden" name="mode" value="1" />
                    <input type="hidden" id="qty_{$cgood->id}" name="qty[{$cgood->id}]" value="1" oldval="{$q|default:0}" price="{$current_price}"/>

                    <a rel="{$cgood->id}" class="butt small i i_cart c" id="good-buy-butt">Добавить в&nbsp;корзину</a>

{if  isset($is_def) AND ($user)}
        <a defdata="{$cgood->id}" class="butt small i" id="good-deferred-butt" do="{if $is_def}delete{else}add{/if}">{if ! $is_def}Отложить товар{else}Удалить из отложенных{/if}</a>
{/if}
                    <div id="good-buy-qty">{$cgood|qty}</div
                </form>

                <script>
                    $(function(){
                        var t = '#ajax-product-card';
                        if ($(t).length == 0) t = '.fancybox-outer';

                        var loader = $('#loader');

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

                                $.when($(t).empty().append(data)).then(
                                    //обновление отзывов
                                    $('#reviews_for').change()
                                );
                                if( typeof(zoombox_clickable) == 'function') zoombox_clickable();

                                updateLinks();

                                if ( ! documentStack[url]) documentStack[url] = { };

                                documentStack['http://' + '{$host}' + url]['#ajax-product-card'] = data;
                            });

                            return false;
                        });
                    });
                </script>


            {else}{* обычная карточка товара *}

                <form action="{Route::url('product_add')}" method="post">

                    {if not empty($good->quantity)}{assign var=q value=$good->quantity}{/if}
                    {if not empty($can_buy)}{assign var=id value=$can_buy}{else}{assign var=id value=$good->id}{/if}

                    <div class="buy incdeced{if ! empty($can_buy)} wide{/if}">
                        <input type="hidden" name="mode" value="1" />
                        <a class="dec">-</a>
                        <input id="qty_{$cgood->id}" name="qty[{$id}]" value="1" max="{if $cgood->qty eq -1}500{else}{$cgood->qty}{/if} "oldval="{$q|default:0}" price="{$current_price}"/>
                        <a class="inc">+</a>
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
                                    if ($('#adding-pack-button:checked').length) {
                                        $('[name=mode]').val(v);
                                        var qty = $('#qty_{$cgood->id}');
                                        if( qty.val() == 1) qty.val(v);
                                        retotal(qty);
                                    } else {
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

                    {$cgood|qty:1}

                </form>

                <div id="good-buy-itogo">
                    <div>Итого</div><span id="pricetotal">{$total|default:0|price}</span>
                </div>

                <a rel="{$cgood->id}" class="butt small i i_cart c" id="good-buy-butt">Добавить в&nbsp;корзину</a>

{if isset($is_def) AND ($user)}
        <a defdata="{$cgood->id}" class="butt small i " id="good-deferred-butt" do={if ($is_def)}"delete"{else}"add"{/if}
        style="width: 179px;padding: 5px;float: left;text-align: center;margin-left: 205px;"
        >{if (!$is_def) }Отложить товар{else}Удалить из отложенных{/if}</a>
{/if}
                <div id="good-buy-oco">
                    {if Model_User::can_one_click($cgood)}{include file='common/one_click.tpl' good_id=$cgood->id in_good=true}{/if}
                </div>
            {/if}

        {else}

            <div class="alert alert-warning fl">
                Нет в наличии {if $notInSale > 0}c {Txt::ru_date($notInSale)}{/if}
            </div>

            {if $cgood->can_appear()}
            <div class="fr">
                <a rel="ajax" data-url="{Route::url('warn', ['id' => $cgood->id])}" href="#" class="appendhash butt fl">Уведомить о&nbsp;поставке</a>
                <p id="wewarn">Мы сообщим Вам по&nbsp;почте,<br />когда товар появится на&nbsp;складе</p>
            </div>                
            {else}
                <div class="fr">
                    <p id="wewarn">Наличие товара не ожидается</p>
                </div>  
            {/if}
        {/if}
    </div>
	{include file="google/detail.tpl" good=$cgood}
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

{if not empty($infancybox)} {* быстрый просмотр - один таб*}
    <br clear="all" />
    <div style="width: 735px; background: #fff; margin: 10px;">
        {include file='product/view/info.tpl'}
        <a href="{$cgood->get_link(FALSE)}">Подробнее</a>
    </div>
{else}
    {include file='product/view/tabs.tpl'}
{/if}
