<div id="all">
    <div id="head">
        <a href="/" id="logo"><img src="/images/vk-logo.png" alt="" /></a>
        <div id="simple_menu">
            <a href="/about">О магазине</a>⋅<a href="/delivery">Доставка и оплата</a>⋅<a href="/contacts">Контакты</a>
        </div>

        {include file='common/top_menu.tpl'}

        <form action="/search" method="get" id="search">
            <input type="text" autocomplete="off" spellcheck="true" autocapitalize="off" autocorrect="off" autofocus x-webkit-speech name="q" value="{$smarty.get.q|default:''|escape:html}" class="q txt" placeholder="Поиск по каталогу" id="search_query_string" />
            <button type="submit" value=" " class="search_submit" style="display:inline;"><img src="/i/lupa-white.png" alt="искать" /></button>
            <div id="search_suggestions"></div>
            <!--span id="search_suggest_sample">Например: <span id="search_suggest_sample_query"></span></span-->
        </form>
    </div>

    <div id="body">

        {if not empty($menu)}
            <div id="side">
                {$menu|default:''}
                <a href="/catalog" id="ctg">Карта товарного каталога</a>

                {if not empty($main)}
                    <div id="last_comment">
                        <a class="big" href="{Model_Comment::get_list_link()}">Отзывы</a>
                        {if not $is_kiosk}
                            {foreach from=Model_Comment::last(2) item=c}
                                <p class="comment">
                                    <a href="{$c->get_link(0)}">{$c->user_name}</a><i></i>
                                    <strong>{$c->name}</strong>
                                    {$c->text|truncate:100}
                                    <a href="{$c->get_link(0)}" class="l">Ответ магазина<i></i></a>
                                </p>
                                <small>{$c->get_answer_by($c->answer_by)}</small>
                            {/foreach}
                        {/if}
                    </div>
                {/if}

            </div>
        {/if}

        <div id="content">
            {$body|default:'Младенец.РУ'}
        </div>

    </div>

    <div id="xxfoot">

        <div id="foot2">
            <a href="/"></a>
            <br />&copy;&nbsp;&laquo;Младенец.РУ&raquo;. Все права защищены.<br />
            Использование материалов сайта разрешено только при наличии активной ссылки на&nbsp;источник.

            <a href="/site_map/list.php" id="sitemap">Карта сайта</a>

        </div>
    </div>
</div>
