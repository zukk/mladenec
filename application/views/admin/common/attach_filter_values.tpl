<fieldset id="filters">
    <h3>Фильтры</h3>
    <ul class="blocks-5">
        {foreach from=$filters item=f name=ff}
        <li class="{if $smarty.foreach.ff.index mod 5 == 0}block-first{/if}">
            {$f->section->name} : <strong>{$f->name}</strong>
            <ul>
            {foreach from=$f->values->order_by('sort', 'ASC')->order_by('name', 'DESC')->find_all() item=v}
                <li>
                    <label title="{$f->id}:{$v->id}"><input type="checkbox" name="misc[filters][{$f->id}][]" value="{$v->id}"
                        {if ! empty($values[$v->id])}checked="checked"{/if}/> {$v->name}</label>
                </li>
            {/foreach}
            </ul>
        </li>
        {foreachelse}
            <li>Фильтры отсутствуют.</li>
        {/foreach}
    </ul>
</fieldset>