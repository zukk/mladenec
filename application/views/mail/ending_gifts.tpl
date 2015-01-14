<tr align="center">
    <td>
        <table bgcolor="#ffffff" border="0" cellpadding="3" cellspacing="2" width="712">
            <tr>
                <td width="80">ID</td>
                <td width="110">Арт</td>
                <td align="left">
                    Акция
                </td>
                <td align="left">
                    Подарок
                </td>
                <td width="70">Кол-во</td>
            </tr>
            {foreach from=$presents key=good_id item=action_id}
                <tr>
                    <td width="80">{$good_id}</td>
                    <td width="110">{$goods[$good_id]->code}</td>
                    <td align="left">
                        <a href="{Mail::site()}{Route::url('admin_edit',['model'=>'action','id'=>$action_id])}">#{$action_id} {$actions[$action_id]->name}</a>
                    </td>
                    <td align="left">
                        <a href="{Mail::site()}{Route::url('admin_edit',['model'=>'good','id'=>$good_id])}">#{$good_id} {$goods[$good_id]->name}</a>
                    </td>
                    <td width="70">{$goods[$good_id]->qty}</td>
                </tr>
            {/foreach}
        </table>
    </td>
</tr>