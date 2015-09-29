<h1>Личный кабинет</h1>

<div class="tabs mt">

    {include file='user/personal.tpl' active='user_deferred'}

    <div class="tab-content active">

        {if $deferreds}
            <table id="orders" class="tt">
                <thead>
                <tr>
                    <th>Товар</th>
                    <th>Когда отложен</th>
                </tr>
                </thead>
                <tbody>

                {foreach from=$deferreds item=deferred}

                    <tr {cycle values='class="odd",'}>
                        <td><a href="/product/{$deferred->good->translit}/{$deferred->good->group_id}.{$deferred->good->id}.html" >{$deferred->good->group_name}</a></td>
                        <td>{$deferred->created}</td>
                    </tr>

                {/foreach}
                </tbody>
            </table>
            {$pager->html('Отложенные товары')}

        {else}

            <p>Вы ещё не&nbsp;отложили ни&nbsp;одного товара</p>

        {/if}

    </div>
</div>
