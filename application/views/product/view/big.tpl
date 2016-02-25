<div itemscope itemtype="http://schema.org/Product">

    {include file='product/view/zoombox.tpl'}

    <div id="good-view">

        <div id="view">
            <a name="{$cgood->id}"></a>
            <input type="hidden" id="good_id" value="{$cgood->id}" />

            {include file='product/view/review.tpl'}

            <h1 itemprop="name">{$group->name} {if not $group->good}<span>{$cgood->name}</span>{/if}</h1>
            <meta itemprop="brand" content="{$cgood->brand->name}" />

            {include file='product/view/price.tpl'}

        </div>

        {* выбор цвета *}
        {if not empty($goods) and count($goods) gt 1}
            {capture assign=colorz}
                <div class="fl">
                    <p style="color: #65949e">Ещё цвета:</p>
                    {if count($goods) gt 3}
                        <a class="choose-color-wrapper">
                            <div class="choose-color choose-color-left"></div>
                        </a>
                    {/if}
                    <div class="choose-color-content">
                        {foreach from=$goods item=g}
                            <a href="{$g->get_link(0)}" {if $g->qty == 0}style="opacity:.5"{/if} title="{$g->name|escape:html}"><img alt="{$g->name|escape:html}" class="choose-color-image{if $cgood->id eq $g->id}-current{/if}" width="70" height="70" src="{$g->prop->image70->get_img(0)}" /></a>
                        {/foreach}
                    </div>
                    {if count($goods) gt 3}
                        <a class="choose-color-wrapper">
                            <div class="choose-color choose-color-right"></div>
                        </a>
                    {/if}
                    <script>
                        $(function(){
                            $('.choose-color-content').animate({
                                scrollLeft: $('.choose-color-image-current').position().left-75
                            }, 400);

                            $('.choose-color-wrapper').click(function() {
                                var delta = $(this).find('.choose-color-left').length ? '-=76' : '+=76';

                                $('.choose-color-content').animate({
                                    scrollLeft: delta
                                }, 400);
                            });
                        });
                    </script>
                </div>
            {/capture}
        {/if}

        {if $cgood->show}
            {if $cgood->sborkable()}
                <a href="/delivery#sborka_tovara" class="fr ok">
                    <strong>Бесплатная сборка!</strong>
                </a>
            {/if}
            <div id="good-buy-qty">{$cgood|qty}</div>

            <div id="good-buy" class="big">

                <div>
                    <dl>
                        <dt>Код товара</dt> <dd>{$cgood->id1c} <span class="hide">{$cgood->code}, {$cgood->code1c}</span></dd>
                        {foreach from=$filters key=fname item=vals}
                            {assign var=under value=$fname|strpos:'_'}
                            {if $under}{assign var=fname value=$fname|mb_substr:$under}{/if}

                            {if ($cgood->section_id == 29982 and in_array($fname, ['Возраст', 'Вес', 'Размер шасси', 'Вид']))
                            or $cgood->section_id != 29982
                            } {* коляски = 29982 *}

                                <dt>{$fname}</dt> <dd>{', '|implode:$vals}</dd>
                            {/if}


                        {/foreach}
                    </dl>
                    <div class="advantage">
                        {$cgood->prop->advantage}
                        <a href="#tabs" class="a-italic fr">Подробнее</a>
                    </div>
                </div>

                {if $cgood->qty != 0}{* товар есть в наличии*}
                    {assign var=active value=1}

                    <div class="delivery_price">
                        <strong>Доставка</strong>
                        <div style="margin: 10px 0">
                            <em>По Москве</em> доставка от {Model_Zone::min_price({Model_Zone::DEFAULT_ZONE}, $current_price)|price}<br />
                            <em>По Московской области</em> стоимость доставки + {Model_Order::PRICE_KM} р/км<br />
                        </div>
                        <em>По России</em> доставка осуществляется транспортной компанией<br />
                        <br />
                    </div>
                    <hr style='margin: 0;' />
                    <div style="color: #15aad4; padding: 15px;"><strong style="font-size: 1.2em;">Бесплатный возврат и обмен в течение 14 дней</strong>&nbsp;&nbsp;<a class="a-italic" href="/about/agreement.php" target="_blank">Условия возврата</a></div>
                    <hr style='margin: 0 0 15px 0;' />

                    <form action="{Route::url('product_add')}" method="post">
                        {if empty($no_cart)}<a class="c" rel="{$cgood->id}"></a>{/if}
                    </form>

                    {$colorz}

                    <a rel="{$cgood->id}" class="butt bbutt small i i_cart c" style="float: right;" id="good-buy-butt">Добавить в&nbsp;корзину</a>

                    {*if isset($is_def) AND ($user)}
                        <a defdata="{$cgood->id}" class="butt small i" id="good-deferred-butt" do="{if $is_def}delete{else}add{/if}">{if not $is_def}Отложить товар{else}Удалить из отложенных{/if}</a>
                    {/if*}

                    <div id="good-buy-oco">
                        {if Model_User::can_one_click($cgood)}{include file='common/one_click.tpl' good_id=$cgood->id in_good=true}{/if}
                    </div>

                {else}

                    <hr style='margin: 0 0 15px 0;' />
                    <div class="alert alert-warning fl">
                        Нет в&nbsp;наличии {if $notInSale > 0}c&nbsp;{Txt::ru_date($notInSale)}{/if}
                    </div>

                    {if $cgood->can_appear()}
                        <div class="fr">
                            <a href="{Route::url('warn', ['id' => $cgood->id])}" class="butt fl"  rel="ajax">Уведомить о&nbsp;поставке</a>
                            <p id="wewarn">Мы сообщим Вам по&nbsp;почте,<br />когда товар появится на&nbsp;складе</p>
                        </div>
                    {else}
                        <div class="fr">
                            <p id="wewarn">Наличие товара не ожидается</p>
                        </div>
                    {/if}

                    {$colorz}

                {/if}
            </div>
            {include file="google/detail.tpl" good=$cgood}
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
