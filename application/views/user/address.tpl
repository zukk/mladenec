<h1>Личный кабинет</h1>

<div class="tabs mt">

    {include file='user/personal.tpl' active='user_address'}

    <div class="tab-content active">

        <p><br />Список адресов доставки для Ваших заказов. Завести новый адрес доставки Вы&nbsp;всегда можете при оформлении нового заказа</p>

        {if $address}
            <input type="hidden" name="address_id" id="address_id" value="0" />
            <table id="orders" class="tt">
                <thead>
                <tr>
                    <th>Город</th>
                    <th>Улица</th>
                    <th>Дом</th>
                    <th>Квартира</th>
                    <th>Подъезд</th>
                    <th>Этаж</th>
                    <th>Домофон</th>
                    <th>Лифт</th>
                    <th>Комментарий</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                {foreach from=$address item=a}
                    {capture assign=addr}{$a->city}, {$a->street}, {$a->house}, {$a->kv}{/capture}
                    <tr {cycle values='class="odd",'}>
                        <td>{$a->city}</td>
                        <td>{$a->street}</td>
                        <td class="c">{$a->house}</td>
                        <td class="c">{$a->kv}</td>
                        <td class="c">{if ! empty($a->enter)}нет{else}{$a->enter}{/if}</td>
                        <td class="c">{if ! empty($a->floor)}{$a->floor}{/if}</td>
                        <td class="c">{if ! empty($a->domofon)}{$a->domofon}{/if}</td>
                        <td class="c">{if ! empty($a->lift)}есть{else}нет{/if}</td>
                        <td>{$a->comment}</td>
                        <td><form action="" method="post" class="ajax">
                                <input type="hidden" name="address_id" value="{$a->id}" />
                                <input type="image" src="/i/x.png" alt="удалить" title="" onclick="return confirm('Удалить адрес доставки &laquo;{$addr|escape:'html'}&raquo;?')" />
                            </form>
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
            {$pager->html('Адреса')}

        {else}

            <p>Нет сохранённых адресов доставки</p>

        {/if}

    </div>
</div>
