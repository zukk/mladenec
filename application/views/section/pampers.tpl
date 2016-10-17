<div id="breadcrumb">
               
    <span itemscope itemtype="http://data-vocabulary.org/Breadcrumb" itemref="breadcrumb-1">
        <a href="/" itemprop="url"><span itemprop="title">Главная</span></a>
    </span>
    &rarr;
    <span itemprop="child" itemscope itemtype="http://data-vocabulary.org/Breadcrumb" id="breadcrumb-1">
        <a href="{Route::url('pampers')}" itemprop="url"><span itemprop="title">Магазин Памперс</span></a>
    </span>
    <i></i>
</div>

{if ! empty($front)}
<div id="product_list">
    <img src="/i/pampers/v2/header-banner.jpg" class="main-banner" alt="Магазин Памперс" width="730" height="95" />

    <div class="pamper-content-block">
        <div id="header_pampers-ru-sis" class="packshots_pampers-ru-sis">
            <h2 class="br-6_pampers-ru-sis">По размеру</h2>
            <div  id="header_pampers-ru-sis" class="packshots_pampers-ru-sis clearfix">
                {foreach from=$size item=s key=k name=s}
                {if $k neq 0}
                <div class="col7g_pampers-ru-sis col7g-1_pampers-ru-sis">
                    <div class="header-nav-box_pampers-ru-sis br-6_pampers-ru-sis">
                        <a href="?size={$k}&ba={$smarty.foreach.s.iteration+2}">                        
                            {assign var="size_name" value=' ('|explode:($s|key)}
                            <span class="name_pampers-ru-sis">{$size_name[0]}</span><br /><span class="hl_pampers-ru-sis after-arrow-right_pampers-ru-sis">({$size_name[1]}</span>
                        </a>
                    </div>
                </div>
                {/if}
                {/foreach}
            </div>
        </div>

        <div id="middle_pampers-ru-sis" class="section_pampers-ru-sis clearfix">
            <div class="col_pampers-ru-sis col-6_pampers-ru-sis">
                <div class="big-box_pampers-ru-sis bundle-offer_pampers-ru-sis br-10_pampers-ru-sis">
                    <div class="big-box-picture_pampers-ru-sis">
                        <img src="/i/pampers/v2/bundle-offer.png" alt="Наборы Pampers - Покупай больше с большей экономией!">
                    </div>
                    <div class="details_pampers-ru-sis">
                        <div class="name_pampers-ru-sis">Наборы<br />Pampers</div>
                        <div class="frame_pampers-ru-sis br-6_pampers-ru-sis">
                            Покупай больше<br />с большей экономией!
                        </div>
                        <div class="purchase-block_pampers-ru-sis">
                            <a href="?f2199=18888&ba=21" class="purchase_pampers-ru-sis br-4_pampers-ru-sis">Показать</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col_pampers-ru-sis col-6_pampers-ru-sis">
                <div class="big-box_pampers-ru-sis hero-offer_pampers-ru-sis br-10_pampers-ru-sis">
                    <div class="big-box-picture_pampers-ru-sis">
                        <img src="/i/pampers/v2/hero-offer.png" alt="Подгузники-трусики Pampers Premium Care разм. 4, 44 шт.">
                    </div>
                    <div class="details_pampers-ru-sis">
                        <div class="name_pampers-ru-sis">Новые<br />подгузники-трусики<br />Pampers Premium Care</div>
                        <div class="price_pampers-ru-sis">
                            от {$price_premium_care|price}
                        </div>
                        <a href="?f2199=18696&f2200=18743&ba=15" class="purchase_pampers-ru-sis br-4_pampers-ru-sis">Показать</a>
                    </div>
                </div>
            </div>
        </div>

        <div id="comparision_pampers-ru-sis" class="section_pampers-ru-sis v2_pampers-ru-sis">
            <h2 class="br-6_pampers-ru-sis">По линейке</h2>
            <div id="comparision-wrap_pampers-ru-sis" class="br-10_pampers-ru-sis">
                <div id="comparision-content_pampers-ru-sis">
                    <div class="comparision-aside-picture_pampers-ru-sis">&nbsp;</div>
                    <div class="comparision-table_pampers-ru-sis">
                        <table>
                            <thead>
                                <tr>
                                    <th>Размер:</th>
                                    <th>0<small>Micro</small></th>
                                    <th>1<small>New Born</small></th>
                                    <th>2<small>Mini</small></th>
                                    <th>3<small>Midi</small></th>
                                    <th>4<small>Maxi</small></th>
                                    <th>4<sup>+</sup><small>Maxi Plus</small></th>
                                    <th>5<small>Junior</small></th>
                                    <th><span>6</span><small class="spanned_pampers-ru-sis">Extra Large</small></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="c1_pampers-ru-sis">
                                    <td>
                                        <a class="comparision-table-row-box_pampers-ru-sis" href="?f2199=18695&f2200=18743&ba=14">
                                            <div class="comparision-table-row-picture_pampers-ru-sis">
                                                <img src="/i/pampers/v2/comparision-picture-premium-care.png" alt="Premium Care - 5 ЗВЕЗД защиты кожи">
                                            </div>
                                            <div class="comparision-table-row-details_pampers-ru-sis">
                                                <div class="name_pampers-ru-sis">
                                                    Premium Care
                                                </div>
                                                <div class="description_pampers-ru-sis">
                                                    "5 ЗВЕЗД" защиты кожи малыша
                                                </div>
                                            </div>
                                        </a>
                                    </td>
                                    <td><a href="?size=0&f2200=18743&f2199=18695">&lt;2.5<small>кг</small></a></td>
                                    <td><a href="?size=1&f2200=18743&f2199=18695">2-5<small>кг</small></a></td>
                                    <td><a href="?size=2&f2200=18743&f2199=18695">3-6<small>кг</small></a></td>
                                    <td><a href="?size=3&f2200=18743&f2199=18695">5-9<small>кг</small></a></td>
                                    <td><a href="?size=4&f2200=18743&f2199=18695">8-14<small>кг</small></a></td>
                                    <td></td>
                                    <td><a href="?size=5&f2200=18743&f2199=18695">11-18<small>кг</small></a></td>
                                    <td></td>
                                </tr>
                                <tr class="c1_pampers-ru-sis">
                                    <td>
                                        <a class="comparision-table-row-box_pampers-ru-sis" href="?f2199=18696&f2200=18743&ba=15">
                                            <div class="comparision-table-row-picture_pampers-ru-sis">
                                                <img src="/i/pampers/v2/comparision-picture-premium-care-2.png" alt="Трусики Premium Care - Трусики такие мягкие, что Ваш малыш их даже не заметит">
                                            </div>	
                                            <div class="comparision-table-row-details_pampers-ru-sis">
                                                <div class="name_pampers-ru-sis">
                                                    Трусики<br>Premium Care
                                                </div>
                                                <div class="description_pampers-ru-sis">
                                                    Трусики такие мягкие, <br />что Ваш малыш их<br />даже не заметит
                                                </div>
                                            </div>
                                        </a>
                                    </td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td><a href="?size=3&f2199=18696&f2200=18743">6-11<small>кг</small></a></td>
                                    <td><a href="?size=4&f2199=18696&f2200=18743">9-14<small>кг</small></a></td>
                                    <td></td>
                                    <td><a href="?size=5&f2199=18696&f2200=18743">12-18<small>кг</small></a></td>
                                    <td></td>
                                </tr>
                                <tr class="c2_pampers-ru-sis">
                                    <td>
                                        <a class="comparision-table-row-box_pampers-ru-sis" href="?f2200=18728&ba=16">
                                            <div class="comparision-table-row-picture_pampers-ru-sis"">
                                                <img src="/i/pampers/v2/comparision-picture-active-baby-dry.png" alt="Active Baby-Dry - До 12 часов надежной сухости">
                                            </div>
                                            <div class="comparision-table-row-details_pampers-ru-sis">
                                                <div class="name_pampers-ru-sis yellow">
                                                    New Baby-Dry
                                                </div>
                                                <div class="name_pampers-ru-sis">
                                                    Active Baby-Dry
                                                </div>
                                                <div class="description_pampers-ru-sis">
                                                    До 2x раз суше обычного подгузника
                                                </div>
                                            </div>
                                        </a>
                                    </td>
                                    <td></td>
                                    <td><a href="?size=1&f2200=18728" class="yellow">2-5<small>кг</small></a></td>
                                    <td><a href="?size=2&f2200=18728" class="yellow">3-6<small>кг</small></a></td>
                                    <td><a href="?size=3&f2200=18728">4-9<small>кг</small></a></td>
                                    <td><a href="?size=4&f2200=18728">7-14<small>кг</small></a></td>
                                    <td><a href="?size=4+&f2200=18728">9-16<small>кг</small></a></td>
                                    <td><a href="?size=5&f2200=18728">11-18<small>кг</small></a></td>
                                    <td><a href="?size=6&f2200=18728">&gt;15<small>кг</small></a></td>
                                </tr>
                                <tr class="c3_pampers-ru-sis">
                                    <td>
                                        <a class="comparision-table-row-box_pampers-ru-sis" href="?f2200=18738&ba=25">
                                            <div class="comparision-table-row-picture_pampers-ru-sis">
                                                <img src="/i/pampers/v2/comparision-picture-panties.png" alt="Трусики - Самые сухие трусики для мальчиков и девочек">
                                            </div>
                                            <div class="comparision-table-row-details_pampers-ru-sis">
                                                <div class="name_pampers-ru-sis">
                                                    Трусики Pampers Pants
                                                </div>
                                                <div class="description_pampers-ru-sis">
                                                    Непревзойденно сухие трусики для&nbsp;мальчиков и&nbsp;девочек
                                                </div>
                                            </div>
                                        </a>
                                    </td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td><a href="?size=3&f2200=18738">6-11<small>кг</small></a></td>
                                    <td><a href="?size=4&f2200=18738">9-14<small>кг</small></a></td>
                                    <td></td>
                                    <td><a href="?size=5&f2200=18738">12-18<small>кг</small></a></td>
                                    <td><a href="?size=6&f2200=18738">&gt;16<small>кг</small></a></td>
                                </tr>
                                <tr class="c4_pampers-ru-sis">
                                    <td>
                                        <a class="comparision-table-row-box_pampers-ru-sis" href="?f2200=18730&ba=18">
                                            <div class="comparision-table-row-picture_pampers-ru-sis">
                                                <img src="/i/pampers/v2/comparision-picture-sleep-and-play.png" alt="Sleep &amp; Play - Сухой по СУПЕРцене">
                                            </div>
                                            <div class="comparision-table-row-details_pampers-ru-sis">
                                                <div class="name_pampers-ru-sis">
                                                    Sleep &amp; Play
                                                </div>
                                                <div class="description_pampers-ru-sis">
                                                    Надежная сухость каждому по&nbsp;карману
                                                </div>
                                            </div>
                                        </a>
                                    </td>
                                    <td></td>
                                    <td></td>
                                    <td><a href="?size=2&f2200=18730">3-6<small>кг</small></a></td>
                                    <td><a href="?size=3&f2200=18730">4-9<small>кг</small></a></td>
                                    <td><a href="?size=4&f2200=18730">7-14<small>кг</small></a></td>
                                    <td></td>
                                    <td><a href="?size=5&f2200=18730">11-18<small>кг</small></a></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="comparision-footer_pampers-ru-sis">
                            <img src="/i/pampers/v2/comparision-footer-heart.png" class="comparision-footer-heart_pampers-ru-sis" alt="">
                            <a class="comparision-footer-wipes_pampers-ru-sis tooltip-handler_pampers-ru pointer-tooltip_pampers-ru-sis" href="?ba=14&c=28856">
                                <img src="/i/pampers/v2/comparision-footer-wipes.png" alt="">
                            </a>
                            <p class="comparision-footer-cleansing_pampers-ru-sis">
                                Деликатное очищение для нежной кожи малыша
                            </p>
                        </div>
                    </div>
                    <div class="comparision-logo_pampers-ru-sis"></div>
                </div>
                <div class="clear_pampers-ru-sis"></div>
            </div>
        </div>
        <h2 class="br-6_pampers-ru-sis">Лучшие предложения</h2>
    </div>

    <div id="best_pampers-ru-sis" class="section_pampers-ru-sis">

        <div class="tab-content active wide">
            <div class="slider" rel="slide/pampers">
                <i></i>
                {include file='common/goods.tpl' goods=$best short=1}
                <i></i>
            </div>
        </div>
    </div>
</div>	

{else}
    {if ! empty($smarty.get.ba)}
	<img width="735" height="95" class="inner-banner" src="/i/pampers/v2/banners/{$smarty.get.ba|intval}.jpg" alt="" />
    {else}
        <div class="yell">
            <h1>Магазин Памперс</h1>
        </div>
    {/if}

    {$search_result}

{/if}


