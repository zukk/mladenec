<form action="" method="post"  class="forms forms-inline">
    <fieldset class="units-row">
        <label class="unit-50">
            Выберите опрос:
            <select name="current_poll_id" class="width-70">
                {foreach from=$list item=i}
                    <option value="{$i->id}" {if $current_poll_id eq $i->id}selected="selected"{/if}>#{$i->id} {$i->name}</option>
                {/foreach}
            </select>
        </label>
        <label class="cb unit-20">
            <input type="submit" class="btn" value="Показать результаты">
        </label>
    </fieldset>
</form>
{if $current_poll}
    <h2>Результаты опроса «{$current_poll->name}»</h2>
    <table>
        <tr>
            <th>Вариант</th>
            {foreach from=$variant_months item=vm}
                <td>{$vm}</td>
            {/foreach}
        </tr>
        {foreach from=$questions item=qstn}
            {$variant_moth_cnt = count($variant_months)}
            <tr><td {if ($variant_moth_cnt + 1) gt 1}colspan="{$variant_moth_cnt + 1}"{/if}><b>{$qstn->name}</b></td></tr>
            {if Model_Poll_Question::TYPE_TEXT eq $qstn->type}
                <tr>
                    <td>Ответы пользователей:</td>
                    {foreach from=$qstn->get_texts_by_months() key=votes_month item=values}
                        <td>
                            <span class="expand-toggle-control">развернуть</span>
                            <ul class="expand-toggle hidden">
                                {foreach from=$values item=t}
                                    {if not empty($t)}<li>{$t}</li>{/if}
                                {/foreach}
                            </ul>
                        </td>
                    {/foreach}
                </tr>
            {else}
                {foreach from=$variants[$qstn->id] item=pv}
                    <tr>
                        <td>{$pv->name}</td>
                        {foreach from=$votes[$qstn->id][$pv->id] key=votes_month item=values}
                            <td>
                                <span class="expand-toggle-control">{$values['cnt']|default:''}</span>
                                {if ( not empty($values['texts']))}
                                    {*$values['texts']*}
                                    <ul class="expand-toggle hidden">
                                        {foreach from=explode('|||',$values['texts']) item=t}
                                            {if not empty($t)}<li>{$t}</li>{/if}
                                        {/foreach}
                                    </ul>
                                {/if}
                            </td>
                        {/foreach}
                    </tr>
                {/foreach}
            {/if}
        {/foreach}
    </table>
{/if}
<a href="/od-men/poll/add">+ Добавить опрос</a>

<form action="">
    <table id="list">
    <tr>
        <th>#</th>
        <th>название</th>
        <th>тип</th>
        <th>активность</th>
    </tr>

    {foreach from=$list item=i}
    <tr {cycle values='class="odd",'}>
        <td><small>{$i->id}</small></td>
        <td><a href="/od-men/{$m}/{$i->id}">{$i->name}</a></td>
        <td>{$i->get_type_name()}</td>
        <td><input name="active[{$i->id}]" type="checkbox" value="1" {if $i->active}checked="checked"{/if} disabled="disabled" /></td>
    </tr>
    {/foreach}
    </table>

    <!--input type="submit" name="save" value="Сохранить изменения" /-->

</form>

{$pager->html('Опросы')}
