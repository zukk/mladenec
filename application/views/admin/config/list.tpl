<h2>Текущие настройки</h2>

<a href="/od-men/config/1">Изменить настройки</a>

<form action="">
    {foreach from=$list item=i}
        <table id="list">
            <tr>
	            <th>Оплата картами</th><td>{if $i->accept_cards}<a class="ok">{$i->accept_cards}</a>{else}<a class="no">откл</a>{/if}</td>
            </tr>
            <tr>
                <th>Поиск по сайту</th><td>{if $i->instant_search eq 'in site'}Встроенный{else}Внешний Findologic{/if}</td>
            </tr>
            <tr>
                <th>Рекомендательный сервис RetailRocket</th><td>{if $i->rr_enabled eq 1}Включен{else}Выключен{/if}</td>
            </tr>
            <tr>
                <th>Телефон</th><td>{$i->phone}</td>
            </tr>
            <tr>
                <th>Верхнее меню</th><td>{$i->menu}</td>
            </tr>
            <tr>
                <th>Логотип (270х90)</th><td>{if $i->logo_id}{$i->logo->get_img()}{else}<img src="/i/logo.png" alt="" />{/if}</td>
            </tr>
            <tr>
                <th>Оповещалки</th>
                <td>
                    <dl>
                        <dt>О заказе (копии всех писем о заказе)</dt>
                        <dd>{$i->mail_order|default:'нет'}</dd>

                        <dt>О партнёрстве (копии заявок на партнёрство)</dt>
                        <dd>{$i->mail_partner|default:'нет'}</dd>

                        <dt>Претензии (копии новых претензий)</dt>
                        <dd>{$i->mail_return|default:'нет'}</dd>

                        <dt>Обратная связь из контактов</dt>
                        <dd>{$i->mail_feedback|default:'нет'}</dd>

                        <dt>Комментарии (копии новых комментариев о сайте)</dt>
                        <dd>{$i->mail_comment|default:'нет'}</dd>

                        <dt>О товарах (список отзывов о товарах, неактивных более 14 дней)</dt>
                        <dd>{$i->mail_review|default:'нет'}</dd>

                        <dt>Об изменениях в товарах (цена, новые позиции)</dt>
                        <dd>{$i->mail_good|default:'нет'}</dd>

                        <dt>Об акциях</dt>
                        <dd>{$i->mail_action|default:'нет'}</dd>

                        <dt>Об окончании подарков</dt>
                        <dd>{$i->mail_present|default:'нет'}</dd>

                        <dt>СМС об окончании подарков</dt>
                        <dd>{$i->sms_present|default:'нет'}</dd>

                        <dt>Об ошибках</dt>
                        <dd>{$i->mail_error|default:'нет'}</dd>

                        <dt>Об ошибках при оплате картой</dt>
                        <dd>{$i->mail_payment|default:'нет'}</dd>

                        <dt>Об ошибках в СМС</dt>
                        <dd>{$i->mail_sms_warning|default:'нет'}</dd>

                        <dt>О пустых разделах</dt>
                        <dd>{$i->mail_empty_section|default:'нет'}</dd>

                        <dt>О пустых теговых</dt>
                        <dd>{$i->mail_emptytag|default:'нет'}</dd>

                        <dt>Об изменениях закупочных цен, для франшизы</dt>
                        <dd>{$i->mail_fransh|default:'нет'}</dd>

                        <dt>Подарочные сертификаты</dt>
                        <dd>{$i->emails}</dd>
                    </dl>
                </td>
            </tr>
            <tr>
                <th>Акции</th><td><b>{$i->actions_header}</b><br>{$i->actions_subheader|nl2br}</td>
            </tr>
            <tr>
                <th>Доставка от Озона</th><td>{if $i->use_ozon_delivery eq 1}Включена{else}Выключена{/if}</td>
            </tr>
            <tr>
                <th>Левая ссылка на картинке</th><td>{$i->link_left}</td>
            </tr>
            <tr>
                <th>Правая ссылка на картинке</th><td>{$i->link_right}</td>
            </tr>
            <tr>
                <th>Картинка для бэкграунда</th><td>{$i->image_id}</td>
            </tr>
        </table>
    {/foreach}
</form>

