<div id="breadcrumb">
    <a href="/">Главная</a> |
</div>

<div id="simple">
    <h1>Акции</h1>

    <ul id="action_list" class="na">
        <li>
            <a href="{Route::url('action_current_list')}" class="fl"><img src="/i/action_star50.png" alt="{$config->actions_header|default:'Акции месяца'}" width="50" /></a>
            <a href="{Route::url('action_current_list')}">{$config->actions_header|default:'Акции месяца'}</a>
            {$config->actions_subheader|nl2br}
        </li>
    {foreach from=$actions item=n}
    <li>
        {if $n->type == 1}<a href="{$n->get_link(0)}" class="fl"><img src="/i/podarok.png" alt="Акция с подарками" width="50" /></a>{/if}
        {if $n->type == 2}<a href="{$n->get_link(0)}" class="fl"><img src="/i/sale.png" alt="Акция со скидкой" width="50" /></a>{/if}
        {$n->get_link()}
        <p>{$n->preview|strip_tags|nl2br}</p>
    </li>
    {/foreach}
    </ul>
    {$pager->html('Акции')}

    {include file='action/star.tpl'}
</div>