<h1>Итоги теста</h1>

<p>
    Пожалуйста, ответьте на&nbsp;несколько вопросов по&nbsp;работе сайта. Это поможет нам сделать сайт лучше.<br />
    Все поля обязательны для заполнения.
</p>

<form action="/test" method="post" class="ajax">
    <label class="lb" for="q1">Как прошла регистрация&nbsp;&mdash; возникли проблемы, и&nbsp;если возникли, то&nbsp;какие?</label>
    <textarea id="q1" name="q1" class="txt" rows="5"></textarea>

    <label class="lb" for="q2">Набор товаров в&nbsp;корзину&nbsp;&mdash; возникли&nbsp;ли проблемы, неясности?</label>
    <textarea id="q2" name="q2" class="txt" rows="5"></textarea>

    <label class="lb" for="q3">Оформление заказа (просьба особенное внимание обратить на&nbsp;заведение адреса
        и&nbsp;полностью расписать суть проблем если они возникли)</label>
    <textarea id="q3" name="q3" class="txt" rows="5"></textarea>

    <label class="lb" for="q4">Устраивает&nbsp;ли вас скорость загрузки страниц (да, нет, почему нет?)</label>
    <textarea id="q4" name="q4" class="txt" rows="5"></textarea>

    <label class="lb" for="q5">Прочее (удобство кнопок управления, внешний вид сайта, другие,
        возникшие в&nbsp;ходе теста, неудобные моменты использования сайта)</label>
    <textarea id="q5" name="q5" class="txt" rows="5"></textarea>

    <input type="hidden" name="order_id" value="{$smarty.request.order_id}" />

    <input type="submit" value="Отправить" class="butt" />
</form>

<br />
<a href="http://mladenec-shop.ru">Перейти на действующий сайт</a>
<br />
<br />
<br />
<a href="http://test.mladenecshop.ru">Посмотреть тестовый сайт</a>
<br /><br />
