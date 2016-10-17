{if !empty($action->from)}
    {assign var=from value=" "|explode:$action->from}
{/if}
{if !empty($action->to)}
    {assign var=to value=" "|explode:$action->to}
{/if}
<p>
{if !empty($action->to) || !empty($action->from)}
    Акция действует
{/if}

{if !empty($action->from)}
    с {date("d.m.Y", strtotime($from.0))}
{/if}

{if !empty($action->to)}
    до {date("d.m.Y", strtotime($to.0))}
{/if}
</p>

<p class="cb" style="text-indent:-1em !important">
    * Акции действуют до&nbsp;окончания подарков.<br />
    Количество товаров ограничено.<br />
    Акции с&nbsp;подарками действуют только при&nbsp;заказе через сайт.<br />
    Акции действуют только при&nbsp;заказе через интернет-магазин на&nbsp;сайтах: <a href="http://www.mladenec-shop.ru">www.mladenec-shop.ru</a><br />
    и&nbsp;<a href="http://www.eatmart.ru">www.eatmart.ru</a>, если иное не&nbsp;указано в&nbsp;условиях акции.<br />
    Условия и&nbsp;сроки проведения акции могут быть изменены без&nbsp;предварительного уведомления.<br />
</p>
