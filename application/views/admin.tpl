<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{$title|default:'Mladenec.ru - Администрирование'}</title>

    <link href="/c/admin/kube.min.css" rel="stylesheet" type="text/css" />
    <link href="/c/admin/admin.css" rel="stylesheet" type="text/css" />
    <link href="/c/admin/filemanager.css" rel="stylesheet" type="text/css" />
    <link href="/c/admin/jquery-ui-redmond/jquery-ui-1.10.4.custom.min.css" rel="stylesheet" type="text/css" />
    <link href="/j/redactor/redactor.css" rel="stylesheet" type="text/css" />

    <script src="/j/jquery.min.js"></script>
    <script src="/j/admin/jquery-ui-1.10.4.custom.min.js"></script>
    <script src="/j/admin/jquery.ui.datepicker-ru.js"></script>

    <script src="/j/fancybox/jquery.fancybox.pack.js"></script>
    <link href="/j/fancybox/jquery.fancybox.css" rel="stylesheet" type="text/css" />

	<script src="/j/redactor/redactor.min.js"></script>
    <script src="/j/redactor/filemanager.js"></script>
    <script src="/j/redactor/table.js"></script>
    <script src="/j/redactor/imagemanager.js"></script>
    <script src="/j/redactor/fullscreen.js"></script>
    <script src="/j/redactor/video.js"></script>
    <script src="/j/redactor/fontcolor.js"></script>
	<script src="/j/redactor/goods.js"></script>
    <script src="/j/redactor/ru.js"></script>

    <script src="/j/admin/filemanager.js"></script>
    <script src="/j/admin/admin.js?{$smarty.now|date_format:'%y%m%d'}"></script>

    <link rel="shortcut icon" href="/i/favicon.ico" type="image/x-icon" />
    <link rel="icon" href="/i/favicon.ico" type="image/x-icon" />

</head>
<body>

<header class="group">
    <nav class="breadcrumbs block-first left unit-padding" >
        <ul>
            <li><a href="{Route::url('admin')}">Админ</a></li>
            {if empty($breadcrumbs)}
                {if ! empty($m)}<li><a href="{Route::url('admin_list', ['model' => $m])}{if ! empty($search_query)}?{$search_query}{/if}">{$model_name}</a></li>{/if}
            {else}
                {if is_array($breadcrumbs)}
                    {foreach from=$breadcrumbs key=brc_key item=brc_val}
                        <li><a href="{$brc_key}">{$brc_val}</a></li>
                    {/foreach}
                {else}
                    {$breadcrumbs}
                {/if}
            {/if}
            {if $action|default:0}
                {if $action eq 'edit'}<li><span>Редактирование</span></li>{/if}
                {if $action eq 'add'}<li><span>Добавление</span></li>{/if}
                {if $action eq 'list'}<li><span>Список</span></li>{/if}
            {/if}
        </ul>
    </nav>

    {if not empty($m)}
        <input id="search_query" value="{Route::url('admin_list', ['model' => $m])}{if ! empty($search_query)}?{$search_query}{/if}" name="search_query" type="hidden" />
    {/if}

    {if $user}
        <div class="right">
            {if $user && $user->allow('admin') && ( ! Model_Sms::sending_ok()) && Kohana::$environment != Kohana::DEVELOPMENT}
                <div class="tools-alert tools-alert-red left">Проверь СМС!!!</div>
            {/if}

            {if $user->allow('action')}
                <a href="{Route::url('admin_ajax_list', ['model' => 'set'])}" data-fancybox-type="ajax" class="btn btn-small">Наборы товаров</a>
            {/if}

            <a href="{Route::url('user')}" class="btn">{$user->login} [{$user->name}]</a>
            <a href="{Route::url('logout')}" class="btn btn-red">Выйти</a>
            <a href="{Route::url('index')}" class="btn btn-green">Сайт</a>

        </div>

    {/if}
</header>

<div class="units-row">

    <div class="unit-20">

        <nav class="nav">
            {$menu|default:''}
        </nav>

    </div>

    <div class="unit-80">

        {if not empty($messages)}
            {foreach from=$messages key=message_type item=msg_list}
            <div class="tools-alert tools-alert-{if $message_type eq 'errors'}red{else}green{/if}">
                {foreach from=$msg_list item=msg}
                    {$msg}<br />
                {/foreach}
            </div>
            {/foreach}
        {/if}

        {if not empty($errors)}
            <div class="tools-alert tools-alert-red">
                {foreach from=$errors key=name item=e}{$e}<br />{/foreach}
            </div>
        {/if}

        {$body|default:'content goes here'}

    </div>
</div>

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


<footer>
    {$profile|default:''}
</footer>


</body>
</html>
