<div id="breadcrumb">
    <a href="/">Главная</a> | Результаты поиска
</div>
<h1 class="yell">По запросу 
    {if Kohana::$server_name eq 'mladenec'}
        &laquo;{$smarty.get['q']}&raquo; на Младенец.ру
    {else}
        &laquo;{$smarty.get['q']}&raquo; на Eatmart.ru
    {/if}
    найдено:
</h1>
{if ! empty($banner_pampers)}
<p>
    <a href="{Route::url('pampers')}">перейти в Магазин Pampers на нашем сайте</a>
</p>
{/if}

{if ! empty($search_error)}
	<div class="error">{$search_error}</div>
{/if}

{$search_result}




