<form action="">
    Раздел:&nbsp;<select name="section_id">
        <option value="0">Все</option>
        {foreach from=$sections item=s}
            <option value="{$s->id}" disabled="disabled">{$s->name}</option>
            {if ! empty($s->children)}
                {foreach from=$s->children item=sub}
                    <option {if $smarty.get.section_id|default:'' eq $sub->id}selected="selected"{/if} value="{$sub->id}">{$s->name}::{$sub->name}</option>
                {/foreach}
            {/if}
        {/foreach}
    </select>
    <input type="submit" name="submit" value="найти" />
</form>
<table id="list">
    <tr>
        <th>#</th>
        <th>название</th>
        <th>картинка</th>
        <th>активность</th>
    </tr>
    {foreach from=$list item=l}
        <tr {cycle values='class="odd",'}>
            <td><small><a href="/od-men/group/{$l->id}">{$l->id}</a></small></td>
            <td><a href="/od-men/group/{$l->id}">{$l->name}</a><br /><small><a href="{$l->get_link( FALSE )}" target="blank">открыть на сайте</a></small></td>
            <td width="128">{$l->get_image()}</td>
            <td><input name="active[{$l->id}]" type="checkbox" value="1" {if $l->active}checked="checked"{/if} disabled="disabled" /></td>
        </tr>
    {/foreach}
</table>
{$pager->html('Группы')}