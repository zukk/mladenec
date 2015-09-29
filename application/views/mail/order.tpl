<tr align="center">
    <td>
        <table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="712">
        <tr>
            <td width="30"></td>
            <td><br><h3>Здравствуйте, {$od->name}!<br></h3>

                <p>

                {if $canPay|default:0}
                    Ваш заказ номер <font color="#0099cc"><b>{$o->id}</b></font> готов к&nbsp;оплате картой.
                    Для&nbsp;оплаты пройдите по&nbsp;ссылке <a href="{$site}{Route::url('pay', ['id' => $o->id])}">http://mladenec.ru{Route::url('pay', ['id' => $o->id])}</a>.
                    Вы можете также передать эту ссылку мужу или&nbsp;подруге, и&nbsp;они смогут оплатить Ваш заказ.

                {elseif $o->status == 'N'}

                    Ваш заказ номер <font color="#0099cc"><b>{$o->id}</b></font> принят.

                {elseif $o->status == 'T'}

                    Ваш заказ номер <font color="#0099cc"><b>{$o->id}</b></font> сформирован и&nbsp;принят в&nbsp;курьерскую службу

                {elseif $o->status == 'F'}
                    {if $o->delivery_type eq Model_Order::SHIP_SELF}

                        Ваш заказ номер <font color="#0099cc"><b>{$o->id}</b></font> отправлен на&nbsp;пункт самовывоза, время доставки в&nbsp;магазин {$od->address}&nbsp;&mdash; уточнит менеджер.<br>
                        Пожалуйста, ожидайте звонок, либо позвоните в&nbsp;магазин <nobr>по телефону: {Txt::phone_format($shop_phone|default:'')}.</nobr>

                    {elseif $o->delivery_type eq Model_Order::SHIP_SERVICE}
                        Ваш заказ номер <font color="#0099cc"><b>{$o->id}</b></font> передан в транспортную компанию
                    {else}
                        Ваш заказ номер <font color="#0099cc"><b>{$o->id}</b></font> доставлен
                    {/if}
                {elseif $o->status == 'X'}

                    Ваш заказ номер <font color="#0099cc"><b>{$o->id}</b></font> отменён

                {/if}
                </p>

                <p>
                {if $o->status == 'N'}

                    {assign var=ship_date value=$od->ship_date|date_ru:1}
                    Подтверждение заказа и&nbsp;его комплектации происходит в{if substr($ship_date, 0, 2) eq 'вт'}о{/if}&nbsp;<b>{$ship_date|regex_replace:'~а$~':'у'}</b>

                    {if ! empty($to_wait)}
                    Мы ожидаем поступления на&nbsp;склад в&nbsp;течение 1-2 дней следующих товаров из&nbsp;Вашего заказа*:<br><br>
                    {foreach from=$to_wait item=g}
                        <a href="{$site}{$g->get_link(FALSE)}">{$g->group_name} {$g->name}</a><br>
                    {/foreach}
                    {/if}<br>

                    После комплектации Ваш&nbsp;заказ будет передан в&nbsp;{if $o->delivery_type neq Model_Order::SHIP_SERVICE}курьерскую службу{else}транспортную компанию{/if}. <br><br>
                    Пожалуйста, при&nbsp;обращении  по&nbsp;телефонам <nobr>+7 (495) 662-999-4</nobr>, <nobr>8 (800) 555-699-4</nobr> в&nbsp;службу по&nbsp;работе с&nbsp;клиентами Младенец.РУ
                    <font color="#ff9000">ОБЯЗАТЕЛЬНО указывайте номер Вашего заказа&nbsp;&mdash; {$o->id}, <small>или Ваш номер телефона&nbsp;&mdash; {$od->phone}</small></font><br>

                {elseif $o->status == 'F'}

                    <h4>Общая сумма Ваших заказов на&nbsp;сайте составила {$o->user->sum|price}<br></h4>

                    {if ! empty($got_status)}
                        <strong>Вам присвоен статус &laquo;Любимый клиент&raquo;</strong><br>
                    {/if}

                    <br>

                    <nobr>Интернет-магазин</nobr> Младенец.РУ благодарит Вас за&nbsp;покупку и&nbsp;просит, потратив совсем немного времени, помочь другим покупателям в&nbsp;выборе товаров, оставив о&nbsp;них отзыв.<br><br>
                    В&nbsp;случае, если у&nbsp;вас осталось негативное впечатление от&nbsp;работы с&nbsp;нашим магазином, есть <nobr>какая-то</nobr> претензия или недопонимание, пожалуйста,
                    всегда пишите номер заказа в&nbsp;вашем отзыве, и&nbsp;мы&nbsp;приложим все силы к&nbsp;тому, чтобы оперативно отреагировать на&nbsp;него.<br><br>
                {/if}
                </p>

            </td>
            <td width="30"></td>
        </tr>
        </table>
    </td>
</tr>

{if $o->status eq 'F'}
<tr align="center"><td><table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="712"><tr><td><img width="712"
    src="/i/mail/zzz.jpg" style="display:block" border="0" alt=""></td></tr></table></td></tr>
<tr align="center">
    <td>
        <table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="712">
            <tr>
                <td><a href="http://market.yandex.ru/shop/3812/reviews/add"
                       title="Яндекс.маркет не забудьте оставить отзыв"><img width="712" src="/i/mail/i51.jpg"
                                                                             style="display:block"
                                                                             border="0"
                                                                             alt="Яндекс.маркет не забудьте оставить отзыв"
                                                                             title="Яндекс.маркет не забудьте оставить отзыв"></a>
                </td>
            </tr>
        </table>
    </td>
</tr>
<tr align="center">
    <td>
        <table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="712">
            <tr>
                <td width="30"></td>
                <td>
                    <p>Мы&nbsp;также просим Вас оставить отзыв о&nbsp;нашей работе на
                        <a href="http://market.yandex.ru/shop/3812/reviews/add"><font color="red">Я</font><font color="black">ндекс Маркете</font></a>.<br>
                        Нам&nbsp;бы очень хотелось, чтобы эта оценка объективно отражала
                        наши усилия по&nbsp;работе с&nbsp;покупателями. Давайте вместе преодолеем тенденцию &laquo;ругает каждый, хвалит один из&nbsp;100&raquo;.<br>
                        Более подробную информацию о&nbsp;написании отзывов можно прочитать <a href="http://help.yandex.ru/market/?id=1127815">здесь</a>.<br>
                        Если по&nbsp;<nobr>какой-то</nobr> причине, Вы&nbsp;получили сообщение, что Ваш отзыв не&nbsp;прошел систему
                        автоматической фильтрации то, пожалуйста, сообщите нам об&nbsp;этом, и&nbsp;мы&nbsp;поможем разобраться.</p>

                    <p> Благодарим за&nbsp;сделанный Вами заказ и&nbsp;уделённое нам&nbsp;время.<br> Спасибо!!!</p>
                </td>
                <td width="30"></td>
            </tr>
        </table>
    </td>
</tr>
{/if}

<tr align="center">
    <td>
        <table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="712">
            <tr>
                <td><img width="712" src="/i/mail/zzz.jpg" style="display:block" border="0" alt=""></td>
            </tr>
        </table>
    </td>
</tr>

<tr align="center">
    <td>
        <table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="712">
            <tr>
                <td align="left"><h3>Параметры Вашего заказа:<br></h3></td>
            </tr>
        </table>
    </td>
</tr>

<tr align="center">
    <td>
        <table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="712">
        <tr>
            <td width="30"></td>
            <td><p>
                    <b>Получатель:</b> {$od->last_name} {$od->name} {$od->second_name} <br>
                    <b>Телефон:</b> {$od->phone}<br>
                    <b>Комментарий:</b> {$o->description|default:'нет'}<br>
                    <b>Способ оплаты:</b> {if $o->pay_type == Model_Order::PAY_CARD}Безналичный расчёт{else}Наличный расчёт{/if}<br>
                    <b>Способ доставки:</b> {if $o->delivery_type == Model_Order::SHIP_COURIER}курьерская{elseif $o->delivery_type == Model_Order::SHIP_SERVICE}доставка в&nbsp;регион выбранной транспортной компанией{elseif $o->delivery_type == Model_Order::SHIP_SELF || Model_Order::SHIP_OZON}самовывоз{/if}<br>
                    {if $o->delivery_type == Model_Order::SHIP_COURIER}
                        <b>Адрес:</b> {$od->city}, {$od->street}, {$od->house}, {$od->kv}<br>
                        <b>Дата доставки:</b> {$od->ship_date|date_ru:1}<br>
                        <b>Время доставки:</b> {Model_Zone_Time::name($od->ship_time)}<br>
                        <b>{if $od->comment}Комментарий к&nbsp;доставке:</b> {$od->comment}{/if}

                    {elseif $o->delivery_type == Model_Order::SHIP_SERVICE}
                        <b>Адрес:</b> {$od->city}, {$od->street}, {$od->house}, {$od->kv}<br>
                        <b>Cтоимость доставки:</b> {if $o->price_ship}{$o->price_ship|price}{else}?{/if}<br>
                        <b>Код доставки:</b> {$od->comment}<br>

                    {elseif $o->delivery_type == Model_Order::SHIP_SELF || 
                            $o->delivery_type == Model_Order::SHIP_OZON}
                        <b>Адрес пункта выдачи:</b> {$od->address}<br>
                    {/if}
                </p></td>
            <td></td>
        </tr>
        </table>
    </td>
</tr>

<tr align="center">
    <td>
        <table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="712">
            <tr>
                <td><img width="712" src="/i/mail/zzz2.jpg" style="display:block" border="0" alt=""></td>
            </tr>
            <tr>
                <td align="left"><h4>Состав заказа:</h4></td>
            </tr>
        </table>
    </td>
</tr>

<tr align="center">
    <td>
        <table bgcolor="#ffffff" border="0" cellpadding="5" cellspacing="0" width="712">
            <tr bgcolor="#ffcc66">
                <td width="80" height="30">Изображение</td>
                <td width="330">Наименование</td>
                <td width="84" align="right">Цена</td>
                <td width="84" align="center">Кол-во</td>
                <td width="84" align="right">Сумма</td>
            </tr>

            {assign var=q value=0}

            {foreach from=$o->get_goods() item=g}

            {if $g->id neq Cart::BLAG_ID}{assign var=q value=$q+$g->quantity}{/if} {* число товаров *}
            {assign var=rowtotal value=$g->price*$g->quantity}

            <tr bgcolor="#{cycle values='ffffff,f3f3f3'}">
                <td width="80" height="80" align="center">
                    {if ($g->price gt 0) && ($g->id neq Cart::BLAG_ID)}{* товар *}
                        <a href="{$site}{$g->get_link(0)}"><img width="70" src="{$g->prop->get_img(70)}" style="display:block" border="0" alt=""></a>

                    {elseif $g->id eq Cart::BLAG_ID}{* благо *}
                        <a href="{$site}/charity/cooperation.php"><img width="70" src="{$site}/i/otkazniki.jpg" style="display:block" border="0" alt=""></a>

                    {else} {* подарок *}
                        <img width="70" src="{$site}/i/gift70.png" style="display:block" border="0" alt="">
                    {/if}
                </td>
                <td width="300">
                    {if $g->price eq 0}{* подарок *}
                        {$g->group_name} {$g->name}

                    {elseif $g->id eq Cart::BLAG_ID}{* благо *}
                        <a href="{$site}/charity/cooperation.php">Благотворительность</a>

                    {else}
                        <a href="{$site}{$g->get_link(0)}">{$g->group_name} {$g->name}</a>
                    {/if}
                </td>
                <td width="84" align="right">{$g->price|price}</td>
                <td width="84" align="center"> x {$g->quantity}шт. =</td>
                <td width="84" align="right">{$rowtotal|price}</td>
            </tr>
            {/foreach}
			{if ! empty($coupon)}
            <tr bgcolor="#{cycle values='ffffff,f3f3f3'}">
                <td width="80" height="80" align="center">
					<img width="70" src="{$site}/i/sale.png" style="display:block" border="0" alt="">
                </td>
                <td width="300">
                    Промо-акция {$coupon->name}
                </td>

                {if $coupon->type eq Model_Coupon::TYPE_SUM}

                    <td width="84" align="right">-{$coupon->sum|price}</td>
                    <td width="84" align="center">x 1 =</td>
                    <td width="84" align="right">-{$coupon->sum|price}</td>

                {elseif $coupon->type eq Model_Coupon::TYPE_PERCENT}

                    <td width="84" colspan="3">Вы получили скидку {$o->discount|price}</td>

                {/if}
            </tr>
			{/if}
        </table>
    </td>
</tr>

<tr align="center">
    <td>
        <table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="712">
            <tr>
                <td><h6>Общее количество товара: <b>{$q}</b></h6></td>
                <td align="right">Сумма заказа: {$o->price|price}<br>Стоимость доставки: {$o->price_ship|price}</td>
            </tr>
            <tr>
                <td></td>
                <td align="right"><h3>Общая сумма заказа: {$o->get_total()|price}</h3></td>
            </tr>
        </table>
    </td>
</tr>

{if ! empty($to_wait)}
<tr align="center">
    <td>
        <table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="712">
            <tr>
                <td width="30"></td>
                <td><p>
                    *&nbsp;Срок поступления товара на&nbsp;склад может быть увеличен в&nbsp;связи с&nbsp;непредвиденными обстоятельствами.
                    Мы ценим своих клиентов и&nbsp;делаем все возможное, чтобы товар был доставлен Вам в&nbsp;срок!<br>
                    </p></td>
                <td width="30"></td>
            </tr>
        </table>
    </td>
</tr>
{/if}

<tr align="center">
    <td>
        <table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="712">
            <tr>
                <td width="30"></td>
                <td><p>Вы можете самостоятельно следить за этапами выполнения <br>Вашего заказа в разделе <a href="{$site}/account">«Личный кабинет».</a><br>
                        <!--small>Для входа в этот раздел необходимо ввести Ваш логин и пароль пользователя Младенец.РУ.</small-->
                        <br>Благодарим за сделанный Вами заказ.<br> Спасибо!</p></td>
                <td width="30"></td>
            </tr>
        </table>
    </td>
</tr>

{if $o->status eq 'F'}
<tr align="center">
    <td>
        <table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="712">
            <tr>
                <td><a href="http://torg.mail.ru/review/shops/mladenec-ru-cid17154/" title="отзыв в mail.ru"><img
                                width="357" src="/i/mail/i71.jpg" style="display:block" border="0" alt="отзыв в mail.ru"
                                title="отзыв в mail.ru"></a></td>
                <td><a href="http://price.ru/firm-reviews/?id=7345:1" title="отзыв в price.ru"><img width="355"
                                                                                                    src="/i/mail/i72.jpg"
                                                                                                    style="display:block"
                                                                                                    border="0"
                                                                                                    alt="отзыв в price.ru"
                                                                                                    title="отзыв в price.ru"></a>
                </td>
            </tr>
        </table>
    </td>
</tr>
{/if}
