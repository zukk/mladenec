<form action="" class="forms forms-inline">
    <fieldset>
        <legend>Поиск отзывов</legend>
        <div class="units-row">
            <div class="unit-40">
                <label>ID Пользователя: <input type="text" name="user_id" value="{$user_id|default:''}" /></label>
            </div>
            <div id="search_flags" class="unit-40">
                <label><i class="tr{$smarty.get.fixed|default:''}"></i><span>Обработана</span><input type="hidden" name="fixed" value="{$smarty.get.fixed|default:''}" /></label>
                <label><i class="tr{$smarty.get.answered|default:''}"></i><span>Ответ</span><input type="hidden" name="answered" value="{$smarty.get.answered|default:''}" /></label>
            </div>
            <div class="unit-20"><input type="submit" class="btn" name="search" value="Показать" />
            </div>
        </div>        
    </fieldset>
</form>
<form action="">
    <table id="list">
    <tr>
        <th>#</th>
        <th>дата</th>
        <th>номер заказа</th>
        <th>название</th>
        <th>заявка</th>
        <th>обработана</th>
        <th>письмо</th>
    </tr>

    {foreach from=$list item=i}
    <tr {cycle values='class="odd",'}>
        <td><small>{$i->id}</small></td>
        <td>{$i->created}</td>
        <td>{if $i->order_num|intval neq 0}<a href="/od-men/order/{$i->order_num|intval}">{/if}{$i->order_num}{if $i->order_num|intval neq 0}</a>{/if}</td>
        <td><a href="/od-men/{$m}/{$i->id}">{$i->name}</a></td>
        <td>{$i->text|truncate:100}</td>
        <td>
			{if $i->fixed}
				<span class="label label-green">да</span>
			{else}
				<span class="label label-red">нет</span>
			{/if}
		</td>
        <td>
			{if $i->answer_sent}
				<span class="label label-green">да</span>
			{else}
				<span class="label label-orange">нет</span>
			{/if}
		</td>
    </tr>
    {/foreach}
    </table>

</form>

{$pager->html('Претензии')}
