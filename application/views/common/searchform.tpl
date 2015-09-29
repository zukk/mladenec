<form action="/search{Txt::view_params('')}" method="get" id="search"{if $config->instant_search == 'findologic'} class="findologic"{/if}>
	<input type="text" name="q" value="{$smarty.get.q|default:''|escape:html}" class="q txt" placeholder="Поиск по каталогу" autocomplete="off" oldval="{$smarty.get.q|default:''|escape:html}" />
	<button type="submit" value=" " class="search_submit" style="display:inline;"><img src="/i/lupa-white.png" alt="искать" /></button>
</form>
<div class="search-suggestions"></div>
