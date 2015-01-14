<p>Отчет <b>{$code|default:''}</b>:</p>
<table cellpadding="5" cellspacing="5" border="1" bordercolor="#ccc">
    <tr>
        <th>Дата<br />Время</th>
        <th>Модуль</th>
        <th>Item ID</th>
        <th>Действие</th>
        <th>Сообщение</th>
    </tr>
    {foreach from=$logs item=log}
        <tr>
            <td align="center">{$log->date}<br />{$log->time}</td>
            <td align="center">{$log->model|default:'-'}</td>
            <td align="center">{$log->item_id|default:'-'}</td>
            <td align="center">{$log->action|default:'-'}</td>
            <td align="center">{$log->get_data()}</td>
        </tr>
    {/foreach}
</table>