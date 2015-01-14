<tr align="center">
	<td>
		<table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="712">
		<tr>
			<td>

<br /><br />
<strong>Поступила <a href="{$site}{Route::url('admin_edit', ['model' => 'partner', 'id' => $p->id])}">заявка</a> на&nbsp;сотрудничество.</strong>
<br /><br />
Компания: <strong>{$p->name}</strong>
{if $p->user_id}<a href="{$site}/od-men/user/{$p->user_id}">{$p->user->name}</a> {$p->user->email} [{$p->user->phone}]{/if}
<br />
<hr />
<p><b>Адрес компании:</b><br />{$p->address|default:'Не заполнено'}</p>
<br />
<hr /><p><b>Контакты:</b><br />{$p->contacts|nl2br|default:'Не заполнено'}</p>
<br />
<hr /><p><b>Информация о&nbsp;присутствии товаров и&nbsp;представленность их в&nbsp;основных
        сетях и&nbsp;интернет-магазинах:</b><br />{$p->dealers|nl2br|default:'Не заполнено'}</p>
<br />
<hr /><p><b>Информация о&nbsp;положении компании на&nbsp;рынке с&nbsp;указанием
        ближайших конкурентов:</b><br />{$p->positioning|nl2br|default:'Не заполнено'}</p>
<br />
<hr /><p><b>Информация о&nbsp;логистических особенностях поставок Вашего товара (доставка по&nbsp;магазинам,
        самовывоз, минимальная партия и&nbsp;т.д.):</b><br />{$p->logistics|nl2br|default:'Не заполнено'}</p>
<br />
<hr /><p><b>Информация о&nbsp;ценах на&nbsp;ваш товар в&nbsp;магазинах (мониторинг):</b><br />{$p->price_monitoring|nl2br|default:'Не заполнено'}</p>
<br />
<hr /><p><b>Расчетная величина продаж Ваших товаров (шт, кг. в&nbsp;мес. на&nbsp;1&nbsp;магазин) - производится поставщиком
        на&nbsp;основании данных о&nbsp;продажах в&nbsp;среднем на&nbsp;1&nbsp;магазин:</b><br />{$p->month_sales|nl2br|default:'Не заполнено'}</p>
<br />
<hr /><p><b>Условия оплаты товара:</b><br />{$p->payment|nl2br|default:'Не заполнено'}</p>
<br />
<hr /><p><b>Размер вознаграждения в&nbsp;зависимости от&nbsp;закупленного количества товара:</b><br />{$p->qty_remuneration|nl2br|default:'Не заполнено'}</p>
<br />
<hr /><p><b>Условия по&nbsp;возвратам товара:</b><br />{$p->return|nl2br|default:'Не заполнено'}</p>
<br />
<hr /><p><b>Дополнительная информация: Планы по&nbsp;продвижению товара, Маркетинговый бюджет и&nbsp;т.п.:</b><br />{$p->text}</p>

			</td>
		</tr>
		</table>
	</td>
</tr>