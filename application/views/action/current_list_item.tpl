    {foreach from=$actions item=action}
        <li>
            {if ! empty($action->banner)}
                <div class="action_header">
                    <p class="banner"><a href="{$action->get_link( FALSE )}"><img alt="{$action->name}" title="{$action->name}" src="{$action->banner}" /></a>
                        {if $action->visible_goods gt 0}<input onClick="load_action_goods('#action_goods_{$action->id}',{$action->id})" type="button">{/if}</p>
                    <div class="description">{$action->text}</div>
                </div>
                <div id="action_goods_{$action->id}" class="goods"></div>
                {if $action->visible_goods gt 8}
                    <div class="goods_all"><a class="cb" href="{Route::url('action',['id'=>$action->id])}">Показать все товары</a></div>
                {/if}
            {else}
                {$action->text}
            {/if}
        </li>
    {/foreach}
