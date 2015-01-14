<form action="" id="list_props" method="get">
        <input type="hidden" id="mode" name="m" value="{$m}" />

        {if empty( $section ) or $section->settings['view_type'] neq Model_Section::VIEW_CLOTHES}
        <div id="view_mode">
            <label for="sort"><strong>Вид:</strong></label>
            <a class="m0 {if $m eq 0}a{/if}" title="списком" rel="0"><i></i></a>
            <a class="m1 {if $m eq 1}a{/if}" title="группами" rel="1"><i></i></a>
        </div>
		{/if}
        <label for="page">Порядок:</label>
		{if not empty($section)}
            {assign var=per_page value=$section->settings['per_page']}

			{$o = $section->settings('sort')}
			{$data = $section->settings['orderByItems']}
		{elseif not empty($novetly)}
			{$o = ORM::factory('section')->settings('sort')}
			{$data = array("new", "rating", "name", "price")}
		{else}
			{$o = ORM::factory('section')->settings('sort')}
			{$data = array("rating", "name", "price", "new")}
		{/if}
		<select id="sort" name="s">
			{foreach from=$data item=field}
			<option value="{$field}"{if not empty($section) and $section->settings['orderByItems'][0] eq $field} selected='selected'{/if}>{$o[$field]}</option>
			{/foreach}
		</select>
        {if not isset($per_page)}{assign var=per_page value=[12,24,48]}{/if}

        <label for="page">На странице:</label>
        <select id="page" name="pp">
            {foreach from=$per_page item=p}
            <option{if not empty($choice) and $p eq $choice} selected="selected"{/if}>{$p}</option>
            {/foreach}
        </select>

        {if $params.x|default:0 eq 2}
            <input type="hidden" id="has" name="x" value="1" />
        {else}
            <label class="label"><i class="check"></i><input type="checkbox" id="has" name="x" value="1" class="big" {if $x eq 1}checked="checked"{/if} /> В наличии</label>
        {/if}

	    <div id="choice">
            {if not empty($params)}
                {if not empty($params.b)}
                {foreach from=$params.b item=i}
                    {if $b = $stats.brands[$i]}
                    <a rel="b{$b.id}" title="{$b.name}">{$b.name}</a>
                    {/if}
                {/foreach}
                {/if}

                {if ! in_array($mode, ['section', 'section_filter']) && not empty($params.c)}
                    {foreach from=$params.c item=i}
                        {if $c = $stats.sections[$i]}
                        <a rel="c{$c.id}" title="{$c.name}">{$c.name}</a>
                        {/if}
                    {/foreach}
                {/if}

                {if not empty($params.f)}
                    {foreach from=$params.f key=fid item=fvals}
                        {foreach from=$fvals item=i}
                            {if $v = $stats.vals[$fid][$i]}
                            <a rel="f{$fid}_{$i}" title="{$v}">{$v}</a>
                            {/if}
                        {/foreach}
                    {/foreach}
                {/if}
            {/if}
	    </div>

    </form>