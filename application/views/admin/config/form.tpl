<form action="" method="post" class="forms forms-columnar" enctype="multipart/form-data">
<h1>Настройки сайта</h1>
    <p>
        <label for="accept_cards">Оплата картами</label>
        <select id="accept_cards" name="accept_cards">
	        <option value="">отключить оплату картами</option>
			{html_options output=Model_Payment::gates() values=Model_Payment::gates() selected=$i->accept_cards}
        </select>
    </p>
    <p>
        <label for="sms_method">Метод отправки SMS</label>
        <select id="sms_method" name="sms_method">
			<option value='{Model_Config::SMS_ACQUIROPAY}'>ACQUIROPAY</option>
			<option{if $i->sms_method == Model_Config::SMS_MTS} selected{/if} value='{Model_Config::SMS_MTS}'>MTS</option>
        </select>
    </p>
    <p>
        <label for="instant_search">Поиск по сайту</label>
        <select id="instant_search" name="instant_search">
            <option value="in site" {if $i->instant_search eq 'in site'} selected{/if}>Встроенный</option>
            <option value="findologic" {if $i->instant_search eq 'findologic'} selected{/if}>Внешний Findologic</option>
        </select>
    </p>
    <p>
        <label for="rr">Рекомендательный сервис RetailRocket:</label>
        <div class="indent">  
            <label><input type="radio" name="rr_enabled" value="0" {if $i->rr_enabled eq 0} checked{/if}> Выключен</label>
            <label><input type="radio" name="rr_enabled" value="1" {if $i->rr_enabled eq 1} checked{/if}> Включен</label>
        </div>
    </p>
    <p>
        <label for="phone">Телефон</label>
        <textarea id="phone" name="phone" class="width-50">{$i->phone}</textarea>
    </p>
    {*<p>
        <label for="menu">Верхнее меню</label>
        <textarea id="menu" name="menu" cols="80" rows="5" class="text">{$i->menu}</textarea>
    </p>
    <p>
        <label for="seo_index">SEO текст</label>
        <textarea id="seo_index" name="seo_index" cols="80" rows="10" class="text">{$i->seo_index}</textarea>
    </p>*}
    <p>
        <label for="mail_order">Настройки оповещений</label>
        <div class="area hi">
            <dl>
                <dt>О заказе (копии всех писем о заказе)</dt>
                <dd><input type="text" id="mail_order" name="mail_order" value="{$i->mail_order}" class="width-50" /></dd>

                <dt>О партнёрстве (копии заявок на партнёрство)</dt>
                <dd><input type="text" id="mail_partner" name="mail_partner" value="{$i->mail_partner}" class="width-50" /></dd>

                <dt>Претензии (копии новых претензий)</dt>
                <dd><input type="text" id="mail_return" name="mail_return" value="{$i->mail_return}" class="width-50" /></dd>
                
                <dt>Обратная связь из контактов</dt>
                <dd><input type="text" id="mail_feedback" name="mail_feedback" value="{$i->mail_feedback}" class="width-50" /></dd>

                <dt>Комментарии (копии новых комментариев о сайте)</dt>
                <dd><input type="text" id="mail_comment" name="mail_comment" value="{$i->mail_comment}" class="width-50" /></dd>

                <dt>О товарах (список отзывов о товарах, неактивных более 14 дней)</dt>
                <dd><input type="text" id="mail_review" name="mail_review" value="{$i->mail_review}" class="width-50" /></dd>
                
                <dt>Об изменениях в товарах (цена, новые позиции)</dt>
                <dd><input type="text" id="mail_good" name="mail_good" value="{$i->mail_good}" class="width-50" /></dd>
                
                <dt>Об акциях</dt>
                <dd><input type="text" id="mail_action" name="mail_action" value="{$i->mail_action}" class="width-50" /></dd>
                
                <dt>Об окончании подарков</dt>
                <dd><input type="text" id="mail_present" name="mail_present" value="{$i->mail_present}" class="width-50" /></dd>
                
                <dt>СМС об окончании подарков</dt>
                <dd><input type="text" id="sms_present" name="sms_present" value="{$i->sms_present}" class="width-50" />
                    <br />Через запятую без пробелов, начиная с +7. Например: +79998887766,+79891112233</dd>

                <dt>Об ошибках</dt>
                <dd><input type="text" id="mail_error" name="mail_error" value="{$i->mail_error}" class="width-50" /></dd>

                <dt>Об ошибках при оплате картой</dt>
                <dd><input type="text" id="mail_payment" name="mail_payment" value="{$i->mail_payment}" class="width-50" /></dd>

                <dt>Об ошибках в СМС</dt>
                <dd><input type="text" id="mail_sms_warning" name="mail_sms_warning" value="{$i->mail_sms_warning}" class="width-50" /></dd>
                
                <dt>О пустых категориях</dt>
                <dd><input type="text" id="mail_empty_section" name="mail_empty_section" value="{$i->mail_empty_section}" class="width-50" /></dd>

                <dt>О пустых теговых</dt>
                <dd><input type="text" id="mail_emptytag" name="mail_emptytag" value="{$i->mail_emptytag}" class="width-50" /></dd>

                <dt>Об изменениях закупочных цен, для франшизы</dt>
                <dd><input type="text" id="mail_fransh" name="mail_fransh" value="{$i->mail_fransh}" class="width-50" /></dd>
                
                <dt>Работы на конкурсы</dt>
                <dd><input type="text" id="mail_contest" name="mail_contest" value="{$i->mail_contest}" class="width-50" /></dd>

                <dt>Подарочные сертификаты</dt>
                <dd><input type="text" value="{$i->emails}" class="width-50" name="emails" id="emails" placeholder="Заполнять через ,"/>
                    <br />Через запятую без пробелов. Например: test@test.ru,admin@admin.com
                </dd>
            </dl>
        </div>
    </p>
    {*<p>
        <label for="logo_id">Логотип</label>
        <input type="file" id="logo_id" name="logo_id" />
        {if $i->logo_id}<figure>{$i->logo->get_img()}</figure>{/if}
    </p>*}
    <p>
        <label for="logo_id">Заголовок акций месяца</label>
        <input type="text" value="{$i->actions_header}" class="width-50" name="actions_header" />
    </p>
    <p>
        <label for="logo_id">Подзаголовок акций месяца</label>
        <textarea name="actions_subheader" class="width-50">{$i->actions_subheader}</textarea>
    </p>
    
    
    <p>
        <label for="ozon_delivery">Доставка от Озона</label>
        <label><input type="radio" name="use_ozon_delivery" value="0" {if $i->use_ozon_delivery eq 0} checked{/if}> Выключена</label>
        <label><input type="radio" name="use_ozon_delivery" value="1" {if $i->use_ozon_delivery eq 1} checked{/if}> Включена</label>
    </p>

    <p>
        <label>Левая ссылка на картинке</label>
        <input type="text" value="{$i->link_left}" class="width-50" name="link_left" />
    </p>
    <p>
        <label>Правая ссылка на картинке</label>
        <input type="text" value="{$i->link_right}" class="width-50" name="link_right" />
    </p>
    <p>
        <label>Картинка для бэкграунда</label>
        <input type="file" name="image_id" />
    </p>

    <p class="do">
        <input name="edit" value="Сохранить" type="submit" class="btn ok"/>
        <input name="edit" value="Сохранить и вернуться к списку" type="submit" class="btn ok" alt="list" />
    </p>
</form>