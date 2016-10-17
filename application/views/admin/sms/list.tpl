{$pager->html('Смс')}
<form action="">
    <table id="list">
    <tr>
        <th>id</th>
        <th>пользователь</th>
        <th>№ заказа</th>
        <th>телефон</th>
        <th>текст</th>
        <th>время создания</th>
        <th>время отправки</th>
        <th>ответ шлюза</th>
    </tr>

    {foreach from=$list item=i}
        {$user_url = Route::url('admin_edit',['model'=>'user','id'=>$i->user_id])}
        <tr {cycle values='class="odd",'}>
            <td><small>{$i->id}</small></td>
            <td><a href="{$user_url}">#{$i->user_id} {$i->user->name}</a></td>
            <td>{$i->order_id}</td>
            <td class="nowrap">{Txt::phone_format($i->phone)}</td>
            <td>{$i->text}</td>
            <td>{$i->created_ts|date_format:'%Y-%m-%d %H:%M:%S'}</td>
            <td>{if $i->sent_ts > 0}{$i->sent_ts|date_format:'%Y-%m-%d %H:%M:%S'}{else}&mdash;{/if}</td>
            <td>{$i->gateway_answer|default:'&mdash;'}</td>
        </tr>
    {/foreach}
    </table>

    <!--input type="submit" name="save" value="Сохранить изменения" /-->

</form>

{$pager->html('Смс')}
