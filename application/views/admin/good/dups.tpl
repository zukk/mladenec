{assign var=sections value=Model_Section::get_catalog(FALSE, FALSE)}
{assign var=dups value=$i->get_dups()}

{foreach from=$dups item=dup}

    <p>{$dup.vitrina} : {$dup.name} <a class="no del_dup" data-id="{$dup.id}">удалить</a></p>

{/foreach}

<select id="new_dup">
    {foreach from=$sections item=s}

        <optgroup label="{$s->vitrina} : {$s->name}">
        {if ! empty($s->children)}
            {foreach from=$s->children item=sub}
                <option value="{$sub->id}" {if ! empty($dups[$sub->id]) || $sub->id == $i->section_id}disabled{/if}>{$sub->name}</option>
            {/foreach}
        {/if}
        </optgroup>
    {/foreach}
</select><a class="ok" id="add_dup">+ Добавить</a>

{if ! empty($changed)}
    <br /><small>Внесены изменения! Изменения вступят в силу после следующей переиндексации каталога (раз в 5 минут)</small>
{/if}