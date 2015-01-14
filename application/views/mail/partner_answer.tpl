<tr align="center">
	<td>
		<table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="712">
		<tr>
            <td width="30"></td>
			<td>

<table cellpadding="0" cellspacing="0" width="100%">
<tr>
    <td align="left">
        <br><h3>Здравствуйте, {$r->name}!</h3>
        <p>Вы оставляли заявку на&nbsp;партнёрство на&nbsp;сайте интернет-магазина <a href="{$site}">Младенец.РУ</a></p>
        <p>Ответ на&nbsp;заявку:<br><br>
            <strong>&laquo;{$r->answer}&raquo;</strong>
        </p>
        <p>Благодарим вас за&nbsp;обращение!</p>

    </td>
</tr>
<tr>
    <td align="left" style="border-top:1px solid #0099cc;">
        <h4>Ваша заявка:</h4>
        
        <p><b>Компания:</b> {$r->name}</p>

        <p><b>Адрес компании:</b><br />{$r->address|nl2br|default:'Не заполнено'}</p>
        <p><b>Контакты:</b><br />{$r->contacts|nl2br|default:'Не заполнено'}</p>
        <p><b>Информация о&nbsp;присутствии товаров и&nbsp;представленность их в&nbsp;основных
                сетях и&nbsp;интернет-магазинах:</b><br />{$r->dealers|nl2br|default:'Не заполнено'}</p>
        <p><b>Информация о&nbsp;положении компании на&nbsp;рынке с&nbsp;указанием
                ближайших конкурентов:</b><br />{$r->positioning|nl2br|default:'Не заполнено'}</p>
        <p><b>Информация о&nbsp;логистических особенностях поставок Вашего товара (доставка по&nbsp;магазинам,
                самовывоз, минимальная партия и&nbsp;т.д.):</b><br />{$r->logistics|nl2br|default:'Не заполнено'}</p>
        <p><b>Информация о&nbsp;ценах на&nbsp;ваш товар в&nbsp;магазинах (мониторинг):</b><br />{$r->price_monitoring|nl2br|default:'Не заполнено'}</p>
        <p><b>Расчетная величина продаж Ваших товаров (шт, кг. в&nbsp;мес. на&nbsp;1&nbsp;магазин) - производится поставщиком
                на&nbsp;основании данных о&nbsp;продажах в&nbsp;среднем на&nbsp;1&nbsp;магазин:</b><br />{$r->month_sales|nl2br|default:'Не заполнено'}</p>
        <p><b>Условия оплаты товара:</b><br />{$r->payment|nl2br|default:'Не заполнено'}</p>
        <p><b>Размер вознаграждения в&nbsp;зависимости от&nbsp;закупленного количества товара:</b><br />{$r->qty_remuneration|nl2br|default:'Не заполнено'}</p>
        <p><b>Условия по&nbsp;возвратам товара:</b><br />{$r->return|nl2br|default:'Не заполнено'}</p>
        <p><b>Дополнительная информация: Планы по&nbsp;продвижению товара, Маркетинговый бюджет и&nbsp;т.п.:</b><br />{$r->text|nl2br|default:'Не заполнено'}</p>
    </td>
</tr>
</table>

			</td>
            <td width="30"></td>
		</tr>
		</table>
	</td>
</tr>