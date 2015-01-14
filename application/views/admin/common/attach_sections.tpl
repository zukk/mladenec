{*
To attach sections, model must have "sections" rel

@param $name
@param $attached_sections
@param $sections 
@param $model - name of model, for example, 'good'
@param $id

*}
<fieldset>
    <legend>{$name|default:'прикрепленные разделы'}</legend>
    <p>
        <label>Прикреплено:</label>
        <div class="area hi"><table>
        {foreach from=$attached_sections item=ia}
            {$parent = $ia->get_parent()}
            <tr><td>#{$ia->id}</td><td>{if $parent}{$parent->name} - {/if}{$ia->name}</td><td><a class="btn btn-round" href="{Route::url('admin_unbind', ['model'=>$model, 'id'=>$id, 'alias'=>'sections', 'far_key'=>$ia->id])}">удалить</a></td></tr>
        {foreachelse}
            <p>Разделы не прикреплены.</p>
        {/foreach}
        </table></div>
    </p>
    <p>
        <label>Добавить:</label>
        <select name="misc[bind][sections][]">
            <option value="0">Не привязывать</option>
            {foreach from=$sections item=s}
                <option value="{$s->id}" disabled="disabled">{$s->name}</option>
                {if ! empty($s->children)}
                    {foreach from=$s->children item=sub}
                        <option value="{$sub->id}">{$s->name}::{$sub->name}</option>
                    {/foreach}
                {/if}
            {/foreach}
        </select>
    </p>
</fieldset>