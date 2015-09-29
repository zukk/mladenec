{foreach from=$actions item=action}
    <li>
        <div class="action_short">

            <a href="{$action->get_link(0)}" class="fr">
                {if $action->is_gift_type()}
                    <img src="/i/averburg/gift.png" alt="Акция с подарками" width="50" />
                {else}
                    <img src="/i/sale.png" alt="Акция со скидкой" width="50" />
                {/if}
            </a>

            <b>{$action->name}</b>

            <p>
            {*if $action->type == Model_Action::TYPE_PRICE}
                Просто скидка
            {elseif $action->type == Model_Action::TYPE_PRICE_QTY}
                Скидка на&nbsp;<b>{$action->sum}%</b> от&nbsp;{$action->quantity}шт
            {elseif $action->type == Model_Action::TYPE_PRICE_QTY_AB}
                Скидка на&nbsp;<b>{$action->sum}%</b> от&nbsp;{$action->quantity}шт
            {elseif $action->type == Model_Action::TYPE_PRICE_SUM}
                Скидка на {$action->sum}% от&nbsp;{$action->quantity}р.
            {elseif $action->type == Model_Action::TYPE_PRICE_SUM_AB}
                Скидка на {$action->sum}% от&nbsp;{$action->quantity}р.
            {elseif $action->type == Model_Action::TYPE_GIFT_SUM}
                Подарок от&nbsp;суммы
            {elseif $action->type == Model_Action::TYPE_GIFT_QTY}
                Подарок от&nbsp;количества
            {/if*}
            </p>

            <a class="cb" href="{Route::url('action',['id' => $action->id])}">{'товар'|plural:$action->visible_goods}</a>

            {if $action->visible_goods gt 0}<input class="butt bbutt small mt" onclick="load_action_goods({$action->id})" type="button" value="Условия | Купить" />{/if}
        </div>

        <div class="action_info">

            {if not empty($action->banner)}
                <a href="{$action->get_link(0)}" class="banner"><img alt="{$action->name}" title="{$action->name}" src="{$action->banner}" /></a>
                <div>{$action->text}</div>
            {else}
                <div>{$action->text}</div>
            {/if}
        </div>
        <div id="action_goods_{$action->id}" class="goods hide cb"></div>
    </li>
{/foreach}
