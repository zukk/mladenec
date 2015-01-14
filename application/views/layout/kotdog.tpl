<div id="all">
    <div id="head" {if not empty($main)} class="main"{/if}>
        <div id="topmenu">
            <a href="{Route::url('section', ['id' => 29690, 'translit' => 'sobaki'])}" title="Собаки"></a>
            <a href="#" title="Кошки" class="cat"></a>
            <a href="#" title="Грызуны" class="rat"></a>
            <a href="#" title="Рыбки" class="fish"></a>
            <a href="#" title="Птицы" class="bird"></a>
            <a href="#" title="Другие" class="more"></a>
        </div>

        <form action="/search" method="get" id="search">
            <input type="text" name="q" value="{$smarty.get.q|default:''|escape:html}" class="q txt" placeholder="Поиск по каталогу" />
            <input type="submit" value="Апорт" class="aport" />
        </form>
    </div>


    <div id="body">

        {if not empty($top_brands)}
	    <div id="top_brands">
            <i></i>{foreach from=$top_brands item=tb}{$tb->name}{/foreach}<i></i>
        </div>
	    {/if}

        {if not empty($menu)}
            <div id="side">{$menu|default:''}</div>
        {/if}

        <div id="content">
            {$body|default:'Kot-dog.ru'}
        </div>
        <div class="cl"></div>

    </div>

    <div id="xxfoot">
        {$foot_menu|default:''}

        <div id="foot2">
            <a href="/"></a>
            <br />&copy;&nbsp;&laquo;Младенец.РУ&raquo;. Все права защищены.<br />
            Использование материалов сайта разрешено только при наличии активной ссылки на&nbsp;источник.

        </div>
    </div>
</div>

{if empty($is_kiosk)}
    {literal}
        <!-- Put specific counters and chats here -->
    {/literal}
{/if}
