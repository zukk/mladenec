<div class="tabs">
    <div>
        <a class="active t" rel="/superprice">Суперцена</a>
        <a class="t" rel="/about/sale.php">Распродажа</a>
        <a class="r" href="/superprice">Показать все</a>
    </div>
    <div class="tab-content active wide">
        <div class="slider" rel="slide/superprice">
            <i></i>
            {include file='common/goods.tpl' goods=$superprice short=1}
            <i></i>
        </div>
    </div>
    <div class="tab-content wide">
        <div class="slider" rel="slide/sale">
            <i></i>
            {include file='common/goods.tpl' goods=$sale short=1}
            <i></i>
        </div>
    </div>
</div>

<div class="na">
    <a class="big" href="{Model_New::get_list_link()}">Новости</a>
    <ul>
        {foreach from=$news item=n}
            <li>
                <small>{$n->date|date_ru}</small>
                {$n->get_link()}
                <a href="{$n->get_link(0)}">{$n->image->get_img()}</a>
                <p>{$n->preview}</p>
            </li>
        {/foreach}
    </ul>
</div>

<div class="na">
    <a class="big" href="{Route::url('action_list')}">Акции</a>
    <ul class="act">
        <li>
            <img class="fr" src="/i/action_star25.png" alt="Акция с подарками" width="25" />
            <a href="{Route::url('action_current_list')}">{$config->actions_header|default:'Акции месяца'}</a>
            {$config->actions_subheader|nl2br}
        </li>
        {foreach from=$actions item=n}
            <li>
                {if $n->type == 1}<img class="fr" src="/i/podarok.png" alt="Акция с подарками" width="25" />{/if}
                {if $n->type == 2}<img class="fr" src="/i/sale.png" alt="Акция со скидкой" width="25" />{/if}
                {$n->get_link()}
                {$n->preview|nl2br}
            </li>
        {/foreach}
    </ul>
</div>
