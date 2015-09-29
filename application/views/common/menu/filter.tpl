<ul>
    {foreach from=$vals item=val name=b key=vid}
        <li>
            {if ! empty($params.f[$fid]) AND in_array($vid, $params.f[$fid])}
                {assign var=checked value=1}
            {else}
                {assign var=checked value=0}
            {/if}

            {if $checked}
                {assign var=change value=-$vid}
            {else}
                {assign var=change value=$vid}
            {/if}
            <a href="{$sphinx->href(['f' => [$fid => [$change]]])}" class="checkbox {if empty($val.qty)}empty{/if} {if $checked}checked{/if}"  title="{$val.name} ({$val.qty})"><i></i> {$val.name|trim}{*nospace!*}<small>{$val.qty}</small></a>
        </li>
    {/foreach}
</ul>