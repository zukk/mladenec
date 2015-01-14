<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{$title|default:'Mladenec.ru - Администрирование'}</title>

    <link href="/c/admin/kube.css" rel="stylesheet" type="text/css" />
    <link href="/c/admin/admin.css?hash={time()}" rel="stylesheet" type="text/css" />
    <link href="/c/admin/filemanager.css?hash={time()}" rel="stylesheet" type="text/css" />
    <link href="/c/admin/jquery-ui-redmond/jquery-ui-1.10.4.custom.min.css" rel="stylesheet" type="text/css" />
    <link href="/j/redactor/redactor.css" rel="stylesheet" type="text/css" />

    <script type="text/javascript" src="/j/jquery.min.js?v=120313"></script>
    <script type="text/javascript" src="/j/admin/jquery-ui-1.10.4.custom.min.js"></script>
    <script type="text/javascript" src="/j/admin/jquery.ui.datepicker-ru.js"></script>

    <script type="text/javascript" src="/j/fancybox/jquery.fancybox.pack.js?v=120313"></script>
    <link href="/j/fancybox/jquery.fancybox.css" rel="stylesheet" type="text/css" />

	<script type="text/javascript" src="/j/redactor/redactor.js?v=120313"></script>
	<script src="/j/redactor/fontcolor.js"></script>
	<script src="/j/redactor/goods.js"></script>
    <script type="text/javascript" src="/j/redactor/ru.js"></script>

    <script type="text/javascript" src="/j/admin/admin.js?v=300514"></script>
    <script type="text/javascript" src="/j/admin/filemanager.js?v=300514"></script>

    <link rel="shortcut icon" href="/i/favicon.ico" type="image/x-icon" />
    <link rel="icon" href="/i/favicon.ico" type="image/x-icon" />

</head>
<body>
    <div class="wrapper">
        <nav id="menu">
            <div class="units-row">
                {if $user}
                    <div class="unit-20">
                        <button id="menu-toggle" class="btn btn-orange">Меню</button>
                        {if $user->allow('action')}<a href="/od-men/ajax/set" data-fancybox-type="ajax" class="btn btn-small">Наборы товаров</a>{/if}
                    </div>

                    <nav class="unit-30 breadcrumbs">
                        <ul>
                            <li><a href="/od-men/">Администрирование</a></li>
                            {if empty($breadcrumbs)}
                                {if ! empty($m)}<li><a href="/od-men/{$m}{if ! empty($search_query)}?{$search_query}{/if}">{$model_name}</a></li>{/if}
                            {else}
                                {if is_array($breadcrumbs)}
                                    {foreach from=$breadcrumbs key=brc_key item=brc_val}
                                        <li><a href="{$brc_key}">{$brc_val}</a></li>
                                    {/foreach}
                                {else}
                                    <li>{$breadcrumbs}</li>
                                {/if}
                            {/if}
                            <li>
                                {if $action eq 'edit'}<span>Редактирование</span>{/if}
                                {if $action eq 'add'}<span>Добавление</span>{/if}
                                {if $action eq 'list'}<span>Список</span>{/if}
                            </li>
                        </ul>
                    </nav>
                    <div class="unit-20 nowrap">
                        {if $user->allow('admin') AND ( NOT Model_Sms::sending_ok())}
                            <span class="red">Проверь СМС!!!</span>
                        {/if}
                    </div>
                    <div class="unit-30 nowrap">
                        <a href="/account" class="btn">{$user->login} [{$user->name}]</a>
                        <a href="/" class="btn btn-red">Выйти</a>
                        <a href="/" class="btn btn-green">Сайт</a>
                    </div>
                    <input id="search_query" value="/od-men/{$m}{if ! empty($search_query)}?{$search_query}{/if}" name="search_query" type="hidden" />

                {/if}
            </div>
            {$menu|default:''}
        </nav>
        <div class="row">
            {if not empty($messages)}
            <div class="admin_messages">
            {foreach from=$messages key=message_type item=msg_list}
                {if not empty($msg_list)}
                <ul class="{$message_type}">
                {foreach from=$msg_list key=message_n item=msg}
                    <li>{$msg}</li>
                {/foreach}
                </ul>
                {/if}
            {/foreach}
            </div>
            {/if}

            {if not empty($errors)}
            <ul class="errors">
            {foreach from=$errors key=name item=e}
                <li class="msg error">{$e}</li>
            {/foreach}
            </ul>
            {/if}

            {$body|default:'content goes here'}

            {if ! empty($history)}
                <fieldset><legend>История изменений</legend>

                    <table>
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Дата</th>
                            <th>Кто</th>
                            <th>Действие</th>
                            <th>IP</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach from=$history item=h}
                            <tr>
                                <td>{$h->id}</td>
                                <td>{$h->timestamp}</td>
                                <td><a href="{Route::url('admin_edit', ['model' => 'user', 'id' => $h->user_id])}" target="_blank">{$h->user->name}</a></td>
                                <td>{$h->action}</td>
                                <td>{$h->ip|long2ip}</td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </fieldset>
            {/if}

        </div>

        <footer>
		{$profile|default:''}
        </footer>
    </div>

</body>
</html>
