    <fieldset>
        <p>
            <label for="active">Фильтры</label>
            <div class="area hi">
                <div id="filters">
                    {foreach from=$filters item=f}
                        <ul>
                            <li><strong>{$f->name}</strong></li>
                            {foreach from=$f->values->order_by('sort', 'ASC')->order_by('name', 'DESC')->find_all() item=v}
                                <li>
                                    {*$checked = FALSE*}
                                    {capture assign=checked}{if !empty($values[$v->id])}1{else}0{/if}{/capture}
                                    <label {if $checked}class="checked"{/if} title="{$f->id}:{$v->id}"><input type="checkbox" name="misc[filters][{$f->id}][]" value="{$v->id}"
                                                                                    {if $checked}checked="checked"{/if}/> {$v->name}</label>
                                </li>
                            {/foreach}
                        </ul>
                    {foreachelse}
                        <p>Фильтры отсутствуют.</p>
                    {/foreach}
                </div>
            </div>
        </p>
    </fieldset>