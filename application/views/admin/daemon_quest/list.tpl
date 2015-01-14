<div class="units-row">
    <div class="unit-25">
        <h1>Задачи демона</h1>
    </div>
    <div class="unit-25">
        {if $alive|default:0}
            <b><span class="green">Демон работает</span></b>
        {else}
            <b><span class="red">Демон остановлен</span></b>
        {/if}
        <a class="btn" href="{Route::url('admin_list',['model'=>'daemon_quest'])}">Проверить</a>
    </div>
    <div class="unit-25">
        Пауза: 
            {if $pause|default:0}
                <b><span class="red">да</span></b>
                <a class="btn red" href="{Route::url('admin_list',['model'=>'daemon_quest'])}?pause=wakeup">ОТКЛ паузу</a>
            {else}
                <b><span class="green">нет</span></b>
                <a class="btn red" href="{Route::url('admin_list',['model'=>'daemon_quest'])}?pause=sleep">Пауза</a>
            {/if}
    </div>
    <div class="unit-25">
        Остановка: 
        {if $stop|default:0}
            <b><span class="red">да</span></b>
            <a class="btn red" href="{Route::url('admin_list',['model'=>'daemon_quest'])}?stop=allow">Разрешить запуск</a>
        {else}
            <b><span class="green">нет</span></b>
            <a class="btn red" onclick="return confirm('Вы уверены?')" href="{Route::url('admin_list',['model'=>'daemon_quest'])}?stop=stop">Остановить</a>
        {/if}
        <br /><span class="red">Внимание! Восстановить работу демона после остановки может только программист!</span>
    </div>
</div>
<table>
    <tr>
        <th>ID</th>
        <th>Действие</th>
        <th>Создана</th>
        <th>Статус</th>
        <th>Период</th>
    </tr>
    {foreach $list as $quest}
    <tr>
        <td>{$quest->id}</td>
        <td>{$quest->action}</td>
        <td>{$quest->created}</td>
        <td>{$quest->status_name()} {if $quest->done_ts}{'%Y-%m-%d %X'|strftime:$quest->done_ts}{/if}</td>
        <td>{$quest->delay}&nbsp;сек.</td>
    </tr>
        
    {/foreach}
</table>