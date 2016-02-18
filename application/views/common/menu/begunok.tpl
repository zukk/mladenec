{if $fid eq Model_Filter::STROLLER_WEIGHT}
    {assign var=options value=$stroller_weight}
    {* бегунок по весу для колясок *}
{/if}

{if $fid eq Model_Filter::STROLLER_SHASSI}
    {assign var=options value=$stroller_shassi}
    {* бегунок по размеру шасси для колясок *}
{/if}

{if $fid eq Model_Filter::VOLUME_KG}
    {assign var=options value=$volume_kg}
    {* бегунок по кг в бытхимии *}
{/if}

{if $fid eq Model_Filter::VOLUME_LITR}
    {assign var=options value=$volume_litr}
    {* бегунок по литрам в бытхимии *}
{/if}

<ul>
    {foreach from=$begunok.settings key=id item=data}
    <li>
        {if not empty($params.f[$fid]) and is_array($params.f[$fid]) and in_array($id, $params.f[$fid])}
            {assign var=checked value=1}
        {else}
            {assign var=checked value=0}
        {/if}

        {if $checked}
            {assign var=change value=-$id}
        {else}
            {assign var=change value=$id}
        {/if}

        {if $is_checked == 1}
            <a onclick="reload_section('{$sphinx->href(['f' => [{$fid} => [{$change}]]])}')" href="#"
               class="checkbox {if empty($options[$id])
            }empty{/if} {if $checked}checked{/if}" title="{$data.name}"><i></i>{$data.name}<small>{$options[$id]|default:0}</small></a>
        {else}
            <a href="{$sphinx->href(['f' => [{$fid} => [{$change}]]])}" class="checkbox {if empty($options[$id])}empty{/if} {if $checked}checked{/if}" title="{$data.name}"><i></i>{$data.name}<small>{$options[$id]|default:0}</small></a>
        {/if}
    </li>
    {foreachelse}
    {/foreach}
    <li style="padding-top:10px;">
        {assign var=limits value=0}
        {if not empty($params.f[$fid]) and is_string($params.f[$fid]) and strpos($params.f[$fid], '-')}
            {assign var=limits value='-'|explode:$params.f[$fid]}
        {/if}
        <div class="range" rev="f{$fid}" {if empty($begunok.settings)}data-digits="1"{/if}>
            <span class="range-ui">
                <span class="line" rel="f{$fid}" rev="f{$fid}"><i class="min"></i><i class="max"></i></span>
            </span>
            от<input class="min" rel="{$options.min}" value="{$limits.0|default:''}" placeholder="{$options.min}" />до<input class="max" rel="{$options.max}" value="{$limits.1|default:''}" placeholder="{$options.max}" />{$begunok.unit}
        </div>

        <input id="f{$fid}" name="f{$fid}" type="hidden" value="{if not empty($limits)}{$params.f[$fid]}{else}{$options.min}-{$options.max}{/if}" data-url="{$sphinx->href(['f' => [{$fid} => [0]]])}"/>
    </li>
</ul>
