{assign var=column value=11}
<div id="breadcrumb">
    <a href="/">Главная</a> &rarr; <a href="{Route::url('pampers')}">Магазин Памперс</a><i></i>
</div>

{if ! empty($front)}
<div style="background:url(/i/pampers/pampers_fon.png) no-repeat -5px 135px;" id="product_list">
    <img src="/i/pampers/shop.jpg" alt="Магазин Памперс" style="margin-top:10px;" width="730" height="160"/>

    <h2 class="mt pam2">По возрасту малыша</h2>
    <div id="simple">
        <a href="?ba=19{$view_params}f1578=14643;" class="pampa">
            <img src="/i/pampers/packshot_1_500.png" alt="" />
            <span class="pam1">Premium Care</span>
            <small>5&nbsp;звёзд защиты кожи Вашего малыша <i></i></small>
        </a>
        <a href="?age=0-5&ba=1{$view_params}" class="pampa">
            <img src="/i/pampers/packshot_2_500.png" alt="" />
            <span class="pam1">0 - 5 месяцев</span>
            <small>Подгузники и&nbsp;салфетки для новорожденных <i></i></small>
        </a>
        <a href="?age=6-12&ba=3{$view_params}" class="pampa">
            <img src="/i/pampers/packshot_3_500.png" alt="">
            <span class="pam1">6 - 12 месяцев</span>
            <small>Непревзойдённая сухость и&nbsp;комфорт <i></i></small>
        </a>
        <a href="?age=1-2&ba=1{$view_params}" class="pampa">
            <img src="/i/pampers/packshot_4_500.png" alt="">
            <span class="pam1">1 - 2 годика</span>
            <small>Самое необходимое для маленьких исследований <i></i></small>
        </a>
    </div>

    <a href="?ba=19{$view_params}f1578=14643;" ><img src="/i/pampers/premium_ban.png" alt="Pampers Premium Care" width="730" height="160"/></a>

    <div class="pamp">
        <h2 class="mt pam2">По размеру</h2>
	    <ul>
		    {foreach from=$size item=s key=k name=s}
            <li><a href="?size={$k}&ba={$smarty.foreach.s.iteration+3}{$view_params|default:''}">{$s|key}</a></li>
		    {/foreach}
        </ul>
    </div>

    <div class="pamp">
        <h2 class="mt pam2">Сортировать по линейке</h2>

        <ul style="background:#f6f6f6 url(/i/pampers/box_2_right_r.png) 110% -15px no-repeat;">
            <li><a href="?ba=19{$view_params}f1578=14643;">Premium Care</a></li>
            <li><a href="?ba=17{$view_params}f1578=13372;">New Baby</a></li>
            <li><a href="?ba=18{$view_params}f1578=13373;">Active Baby</a></li>
            <li><a href="?ba=13{$view_params}f1578=13375;">Sleep&amp;Play</a></li>
            <li><a href="?ba=16{$view_params}f1578=13376;">Подгузники-трусики Active Boy</a></li>
            <li><a href="?ba=15{$view_params}f1578=13377;">Подгузники-трусики Active Girl</a></li>
            <li><a href="?ba=12{$view_params}c=28856;">Салфетки</a></li>
        </ul>
    </div>

    <h2 class="mt pam1">Бестселлеры</h2>

    <div class="tab-content active wide">
        <div class="slider" rel="slide/pampers">
            <i></i>
            {include file='common/goods.tpl' goods=$best short=1}
            <i></i>
        </div>
	    <div style="margin:5px 33px 0;">Срок действия акции с 01.11.13 по 31.01.14</div>
    </div>

	<div class="pampersvideo">
		<img src="/i/pampers/why.png" width="274" height="166" alt="Почему мамы выбирают Памперс"/>
		<iframe width="400" height="225" src="http://www.youtube.com/embed/NMT02xyQihw" frameborder="0" allowfullscreen=""></iframe>
	</div>
</div>	

{else}
    {if ! empty($smarty.get.ba)}
		<img src="/i/pampers/{$smarty.get.ba|intval}.png" alt="" />
    {else}
        <h1 class="yell">Магазин Памперс</h1>
    {/if}

    {$search_result}

{/if}


