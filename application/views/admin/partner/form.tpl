<form action="" method="post" class="forms forms-columnar" enctype="multipart/form-data">
    <h1>Заявка поставщика &laquo;{$i->name|default:''}&raquo; #{$i->id}</h1>
    <p>
        <label for="email">Email</label>
        {if $i->user_id}
            <a href="/od-men/user/{$i->user_id}">{$i->user->name}</a> {$i->user->email} [{$i->user->phone}]
        {else}
            {$i->email|default:'нет данных'}
        {/if}
    </p>
    <p>
        <label for="name">Название</label>
        <input type="text" id="name" name="name" value="{$i->name|default:'Не заполнено'}" class="width-50" readonly="readonly" />
    </p>
    <p>
        <label for="address">Адрес компании:</label>
        <input id="address" name="address" class="width-50" readonly="readonly" value="{$i->address|default:'Не заполнено'}" />
    </p>
    <p>
        <label for="contacts">Контакты:</label>
        <textarea id="contacts" name="contacts" class="raw_text" cols="40" rows="10" readonly="readonly">{$i->contacts|default:'Не заполнено'}</textarea>
    </p>
    <p>
        <label for="dealers">Присутствие на рынке:</label>
        <textarea id="dealers" name="dealers" class="raw_text" cols="40" rows="10" readonly="readonly">{$i->dealers|default:'Не заполнено'}</textarea>
        <span class="forms-desc">Информация о присутствии товаров и представленность их в основных сетях и интернет-магазинах.</span>
    </p>
    <p>
        <label for="positioning">Положение и конкуренты:</label>
        <textarea id="positioning" name="positioning" class="raw_text" cols="40" rows="10" readonly="readonly">{$i->positioning|default:'Не заполнено'}</textarea>
        <span class="forms-desc">Информация о положении компании на рынке с указанием ближайших конкурентов.</span>
    </p>
    <p>
        <label for="logistics">Логистика:</label>
        <textarea id="logistics" name="logistics" class="raw_text" cols="40" rows="10" readonly="readonly">{$i->logistics|default:'Не заполнено'}</textarea>
        <span class="forms-desc">Информация о логистических особенностях поставок Вашего товара (доставка по магазинам, самовывоз, минимальная партия и т.д.).</span>
    </p>
    <p>
        <label for="price_monitoring">Мониторинг цен:</label>
        <textarea id="price_monitoring" name="price_monitoring" class="raw_text" cols="40" rows="10" readonly="readonly">{$i->price_monitoring|default:'Не заполнено'}</textarea>
        <span class="forms-desc">Информация о ценах на ваш товар  в магазинах (мониторинг).</span>
    </p>
    <p>
        <label for="month_sales">Продажи:</label>
        <textarea id="month_sales" name="month_sales" class="raw_text" cols="40" rows="10" readonly="readonly">{$i->month_sales|default:'Не заполнено'}</textarea>
        <span class="forms-desc">Расчетная величина продаж Ваших товаров (шт, кг. в мес. на 1 магазин)&nbsp;&mdash; 
            производится поставщиком на основании данных о продажах в среднем на 1 магазин.</span>
    </p>
    <p>
        <label for="payment">Оплата:</label>
        <textarea id="payment" name="payment" class="raw_text" cols="40" rows="10" readonly="readonly">{$i->payment|default:'Не заполнено'}</textarea>
        <span class="forms-desc">Условия оплаты товара.</span>
    </p>
    <p>
        <label for="qty_remuneration">Бонус за количество:</label>
        <textarea id="qty_remuneration" name="dealers" class="raw_text" cols="40" rows="10" readonly="readonly">{$i->qty_remuneration|default:'Не заполнено'}</textarea>
        <span class="forms-desc">Размер вознаграждения в зависимости от закупленного количества товара.</span>
    </p>
    <p>
        <label for="return">Возврат:</label>
        <textarea id="return" name="return" class="raw_text" cols="40" rows="10" readonly="readonly">{$i->return|default:'Не заполнено'}</textarea>
        <span class="forms-desc">Условия по возвратам товара.</span>
    </p>
    <p>
        <label for="text">Текст</label>
        <textarea class="raw_text" id="text" name="text" cols="40" rows="10" readonly="readonly">{$i->text|default:'Не заполнено'}</textarea>
        <span class="forms-desc">Дополнительная информация: Планы по продвижению товара, Маркетинговый бюджет и т.п.</span>
    </p>
    <p>
        <label for="date">Создана</label> {$i->created|date_format:'Y-m-d'}
    </p>
    {if $i->email}
        <p>
            <label for="answer">Текст ответа</label>
            {if not $i->answer_sent}
                <textarea id="answer" name="answer" cols="40" rows="10" class="text">{$i->answer}</textarea>
                <div id="warn" class="no">при нажатии кнопки "Сохранить" поставщику будет выслано письмо!</div>
            {else}
                {$i->answer}
            {/if}
        </p>
        <p>
        {if $i->answer_sent}
            <div>Ответ отправлен партнёру на электронную почту {date('d m Y, H:m', $i->answer_sent)}.</div>
        {/if}
        </p>
    {/if}
    <div class="units-row">
        <div class="unit-80">
            <input name="edit" value="Сохранить" type="submit" class="btn btn-green" />
            {if $i->id}<input name="edit" value="Сохранить и вернуться к списку" type="submit" class="btn  btn-green ok" alt="list" />{/if}
        </div>
    </div>

{if $i->id}
    <p><a href="{$i->id}/del" onclick="return confirm('Удалить насовсем?')" class="red">Удалить</a></p>
{/if}
</form>