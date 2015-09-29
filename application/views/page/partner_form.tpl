{if ! empty($p)}
    <div class="cl">
        <h2>Спасибо</h2>
        <p>Ваша заявка принята. Номер заявки: №{$p->id}</p>
    </div>
{else}
<form action="" method="post" class="cols" id="partner_form" enctype="multipart/form-data">
    <label class="l" for="name">Наименование компании:&nbsp;<sup>*</sup></label>
        <input id="name" name="name" class="txt {if ! empty($errors.name)}error" error="{$errors.name}{/if}" value="{$smarty.post.name|default:''}" />
        
    <label class="l" for="address">Адрес местонахождения компании:&nbsp;<sup>*</sup></label>
        <input id="address" name="address" class="txt {if ! empty($errors.address)}error" error="{$errors.address}{/if}" value="{$smarty.post.address|default:''}" />
        
    <label class="l" for="contacts">Контакты для связи:&nbsp;<sup>*</sup></label>
        <textarea id="contacts" name="contacts" class="txt {if ! empty($errors.contacts)}error" error="{$errors.contacts}{/if}" rows="5">{$smarty.post.contacts|default:''}</textarea>
        
    <label class="wl" for="dealers">Информация о&nbsp;присутствии Ваших товаров и&nbsp;представленность их&nbsp;в&nbsp;основных сетях и&nbsp;интернет-магазинах:&nbsp;<sup>*</sup></label>
        <textarea id="dealers" name="dealers" class="wtxt {if ! empty($errors.dealers)}error" error="{$errors.dealers}{/if}" rows="5" >{$smarty.post.dealers|default:''}</textarea>
        
    <label class="wl" for="positioning">Информация о&nbsp;положении компании на&nbsp;рынке с&nbsp;указанием ближайших конкурентов:&nbsp;<sup>*</sup></label>
        <textarea id="positioning" name="positioning" class="wtxt {if ! empty($errors.positioning)}error" error="{$errors.positioning}{/if}" rows="10">{$smarty.post.positioning|default:''}</textarea>
        
    <label class="wl" for="logistics">Информация о&nbsp;логистических особенностях поставок Вашего товара (доставка по&nbsp;магазинам, самовывоз, минимальная партия и&nbsp;т.д.):&nbsp;<sup>*</sup></label>
        <textarea id="logistics" name="logistics" class="wtxt {if ! empty($errors.logistics)}error" error="{$errors.logistics}{/if}" rows="10">{$smarty.post.logistics|default:''}</textarea>
        
    <label class="wl" for="price_monitoring">Информация о&nbsp;ценах на&nbsp;ваш товар в&nbsp;магазинах (мониторинг):&nbsp;<sup>*</sup></label>
        <textarea id="price_monitoring" name="price_monitoring" class="wtxt {if ! empty($errors.price_monitoring)}error" error="{$errors.price_monitoring}{/if}" rows="10">{$smarty.post.price_monitoring|default:''}</textarea>
        
    <label class="wl" for="month_sales">Расчетная величина продаж Ваших товаров (шт, кг. в&nbsp;мес. на&nbsp;1&nbsp;магазин)&nbsp;&mdash; производится поставщиком на&nbsp;основании данных о&nbsp;продажах в&nbsp;среднем на&nbsp;1&nbsp;магазин:&nbsp;<sup>*</sup></label>
        <textarea id="month_sales" name="month_sales" class="wtxt {if ! empty($errors.month_sales)}error" error="{$errors.month_sales}{/if}" rows="10">{$smarty.post.month_sales|default:''}</textarea>
        
    <label class="wl" for="payment">Условия оплаты товара:&nbsp;<sup>*</sup></label>
        <textarea id="payment" name="payment" class="wtxt {if ! empty($errors.payment)}error" error="{$errors.payment}{/if}" rows="10">{$smarty.post.payment|default:''}</textarea>
        
    <label class="wl" for="qty_remuneration">Размер вознаграждения в&nbsp;зависимости от&nbsp;закупленного количества товара:&nbsp;<sup>*</sup></label>
        <textarea id="qty_remuneration" name="qty_remuneration" class="wtxt {if ! empty($errors.qty_remuneration)}error" error="{$errors.qty_remuneration}{/if}" rows="10">{$smarty.post.qty_remuneration|default:''}</textarea>
        
    <label class="wl" for="return">Условия по&nbsp;возвратам товара:&nbsp;<sup>*</sup></label>
        <textarea id="return" name="return" class="wtxt {if ! empty($errors.return)}error" error="{$errors.return}{/if}" rows="10">{$smarty.post.return|default:''}</textarea>
        
    <label class="wl" for="text">Дополнительная информация: планы по&nbsp;продвижению товара, маркетинговый бюджет&nbsp;и&nbsp;т.п.:&nbsp;<sup>*</sup></label>
        <textarea id="text" name="text" class="wtxt {if ! empty($errors.text)}error" error="{$errors.text}{/if}" rows="10">{$smarty.post.text|default:''}</textarea>

    <label class="wl" for="price">Ценовое предложение (прайс-лист).<sup>*</sup></label>
        <div class="fl"><input type="file" name="price" /><br />
            {if ! empty($errors.price)}<b style="color:red">{$errors.price}</b>{/if}
            Mаксимальный размер файла &mdash;&nbsp;2.5&nbsp;мб.
        </div>

    {if not $user}
        <label class="l" for="email">E-mail:&nbsp;<sup>*</sup></label>
            <input id="email" name="email" class="txt {if ! empty($errors.email)}error" error="{$errors.email}{/if}" value="{$smarty.post.email|default:''}" />

        <label for="captcha" class="l"><img src="/captcha" alt="" /></label>
        
        <div class="fl">
            <label>Введите цифры с&nbsp;картинки:&nbsp;<sup>*</sup></label><br />
            <input id="captcha" type="text" name="captcha" value="" maxlength="6" class="txt"/>
        </div>
    {/if}

    <!--<label class="l" for="img">Прикрепить файл<br /> <small>Только .doc или .docx до 10Мб</small></label>
    <div class="fl"><input type="file" name="doc" /></div>
    -->

    <p class="cb"><br /><input type="submit" value="Отправить заявку" class="butt" name="partner"/></p>
</form>
{/if}