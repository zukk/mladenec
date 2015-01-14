<h1>Личный кабинет</h1>

<div class="tabs mt">
    <div>
        <a class="t" href="{Route::url('user')}">Мои данные</a>
        <a class="t" href="{Route::url('order_list')}">Мои заказы</a>
        <a class="t" href="{Route::url('user_address')}">Мои адреса</a>
        <a class="t" href="{Route::url('user_child')}">Мои дети</a>
        <a class="t" href="{Route::url('user_action')}">Мои баллы по акции</a>
        <a class="t active" href="{Route::url('user_reviews')}">Мои отзывы</a>
    </div>
    <div class="tab-content active">
        <p>Оставленные отзывы</p>
		
{foreach from=$comments item=c name=c}
<div>
    <div class="data">
        {if ! empty($params[$c->id]['me'])} ({$params[$c->id]['me']}) {/if}

        <span class="stars"><span style="width:{$c->rating*20}%"></span></span>

        {if ! empty($params[$c->id][1])}
            <div class="good">
                {foreach from=$params[$c->id][1] item=p}<span>+1</span> {$p}<br />{/foreach}
            </div>
        {/if}
        {if ! empty($params[$c->id][-1])}
            <div class="bad">
                {foreach from=$params[$c->id][-1] item=p}<span>-1</span>  {$p}<br />{/foreach}
            </div>
        {/if}
        {if ! empty($params[$c->id][0])}
            <div class="neutral">
                <strong>Использовать с</strong><br />
                {foreach from=$params[$c->id][0] item=p} <span>+1</span> {$p}<br />{/foreach}
            </div>
        {/if}
    </div>

    <div class="desc">
        <small>{$c->time|date_format:'%d-%m-%Y'}</small>
        <h4>{$c->name}</h4>
        <p>{$c->text|nl2br}</p>
        <span>Комментарий к товару <a href="{$goods[$c->good_id]->get_link(false)}">{$goods[$c->good_id]->group_name} {$goods[$c->good_id]->name}</a></span>
    </div>
</div>
<br />
{/foreach}

    </div>
</div>
