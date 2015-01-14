<h2>Статистика сайта</h2>
<div id="index">
    <fieldset class="fl"><legend><a href="{Route::url('admin_list', ['model' => 'order'])}">Заказы</a></legend>
        <table>
        <thead>
        <tr>
            <td>Дата</td>
            <td>Сделано</td>
            <td>Отменено</td>
            <td>Доставлено</td>
        </tr>
        </thead>
        <tbody>
        {assign var=total_sum value=0}
        {assign var=total_new value=0}
        {assign var=total_sum_card value=0}
        {assign var=total_new_card value=0}

        {assign var=total_cancel_sum value=0}
        {assign var=total_cancel value=0}
        {assign var=total_cancel_sum_card value=0}
        {assign var=total_cancel_card value=0}

        {assign var=total_complete_sum value=0}
        {assign var=total_complete value=0}
        {assign var=total_complete_sum_card value=0}
        {assign var=total_complete_card value=0}
        
        {foreach from=$orders item=o}
            {assign var=ts value=$o.date|strtotime}
        <tr>
            <td>{'%d/%m, %a'|strftime:$ts}</td>
            <td class="r"><strong title="{$o.sum|price:1}">{$o.new}</strong> / <strong title="{$o.sum_card|price:1}">{$o.new_card}</strong></td>
            <td class="r"><strong class="no"  title="{$o.cancel_sum|price:1}">{$o.cancel}</strong> / <strong title="{$o.cancel_sum_card|price:1}">{$o.cancel_card}</strong></td>
	        <td class="r"><strong class="ok"  title="{$o.complete_sum|price:1}">{$o.complete}</strong> / <strong title="{$o.complete_sum_card|price:1}">{$o.complete_card}</strong></td>
        </tr>

            {assign var=total_sum value=$total_sum+$o.sum}
            {assign var=total_new value=$total_new+$o.new}
            {assign var=total_sum_card value=$total_sum_card+$o.sum_card}
            {assign var=total_new_card value=$total_new_card+$o.new_card}

            {assign var=total_cancel_sum value=$total_cancel_sum+$o.cancel_sum}
            {assign var=total_cancel value=$total_cancel+$o.cancel}
            {assign var=total_cancel_sum_card value=$total_cancel_sum_card+$o.cancel_sum_card}
            {assign var=total_cancel_card value=$total_cancel_card+$o.cancel_card}

            {assign var=total_complete_sum value=$total_complete_sum+$o.complete_sum}
            {assign var=total_complete value=$total_complete+$o.complete}
            {assign var=total_complete_sum_card value=$total_complete_sum_card+$o.complete_sum_card}
            {assign var=total_complete_card value=$total_complete_card+$o.complete_card}

        {/foreach}
        </tbody>
        <tfoot><tr>
            <td>Итого</td>
            <td class="r"><strong title="{$total_sum|price:1}">{$total_new}</strong> / <strong title="{$total_sum_card|price:1}">{$total_new_card}</strong></td>
            <td class="r"><strong class="no"  title="{$total_cancel_sum|price:1}">{$total_cancel}</strong> / <strong title="{$total_cancel_sum_card|price:1}">{$total_cancel_card}</strong></td>
            <td class="r"><strong class="ok"  title="{$total_complete_sum|price:1}">{$total_complete}</strong> / <strong title="{$total_complete_sum_card|price:1}">{$total_complete_card}</strong></td>
        </tr></tfoot>
        </table>
    </fieldset>

    <fieldset class="fl"><legend><a href="user">Регистрации</a></legend>
        <table>
        <thead>
        <tr>
            <td>Дата</td>
            <td>Всего</td>
        </tr>
        </thead>
        <tbody>
        {assign var=total value=0}
        {foreach from=$regs item=o}
        <tr>
            <td>{$o.date|date_format:'d/m'}</td>
            <td class="r">{$o.regs}</td>
            {assign var=total value=$total+$o.regs}
        </tr>
        {/foreach}
        </tbody>
        <tfoot><tr>
            <td>Итого</td>
            <td class="r">{$total}</td>
        </tr></tfoot>
        </table>
    </fieldset>

    <fieldset class="fl"><legend><a href="{Route::url('admin_list', ['model' => 'brand'])}">Бренды</a></legend>
        <span class="ok">{$brands.total}</span>
    </fieldset>
	
    <fieldset class="fl"><legend><a href="{Route::url('admin_list', ['model' => 'good'])}">Товары</a></legend>
        <span class="ok">{$goods.total}</span> / <span class="no">{$goods.new}</span>
    </fieldset>

    <fieldset class="fl"><legend><a href="{Route::url('admin_list', ['model' => 'good_review'])}">Отзывы о&nbsp;товарах</a></legend>
        <span class="ok">{$reviews.total}</span> / <span class="no">{$reviews.new}</span>
    </fieldset>

    <fieldset class="fl"><legend><a href="{Route::url('admin_list', ['model' => 'comment_theme'])}">Отзывы о&nbsp;сайте</a></legend>
        <span class="ok">{$comments.total}</span> / <span class="no">{$comments.new}</span>
    </fieldset>

    <fieldset class="fl"><legend><a href="{Route::url('admin_list', ['model' => 'return'])}">Претензии</a></legend>
        <span>{$return.total}</span> / <span class="no">{$return.new}</span>
    </fieldset>
	
    <fieldset class="fl"><legend><a href="{Route::url('admin_list', ['model' => 'searchwords'])}">История поисков</a></legend>
        <span></span>
    </fieldset>
</div>