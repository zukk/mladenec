<script>
$(function() {
    $( "#datepicker" ).datepicker({
        showOn: "button",
        buttonImage: "/images/calendar.png",
        buttonImageOnly: true,
        buttonText: "Выберите дату"
    });

    $("#target").change(function() {
        $('#count_row').submit();
    });
});
</script>
<h2>SEO статистика</h2>
{if $error}
    <div class="error">{$error}</div>
{/if}

<form method="get" action="">
    <input type="text" id="datepicker" name="date" value="" />
    <input type="submit" value="Экспортировать в CSV" />
</form>

<form method="get" action="" id="count_row">
    <label>Выводить</label>
    <select name="count_row" id="target">
        {for $foo=20 to 100 step=20}
            {if $count_row eq $foo}
                {assign value='selected="selected"' var=selected}
            {else}
                {assign value='' var=selected}
            {/if}
            <option value="{$foo}" {$selected}>{$foo}</option>
        {/for}
    </select>
    <label>на странице</label>
</form>

<br />

<form action="" class="cb">
    {$pager->html('СЕО статистика')}
    <table id="list">
        <tr>
            <th>#</th>
            <th>Дата</th>
            <th>Количество продуктов</th>
            <th>Пустой title</th>
            <th>Пустой description</th>
            <th>Пустой keywords</th>
            <th>Количество категорий</th>
            <th>Пустой title</th>
            <th>Пустой description</th>
            <th>Пустой keywords</th>
            <th>Количество тегов</th>
            <th>Пустой title</th>
            <th>Пустой description</th>
            <th>Пустой keywords</th>
        </tr>

        {foreach from=$list item=l}
            <tr {cycle values='class="odd",'}>
                <td><small>{$l->id}</small></td>
                <td>{date('d-m-Y H:i:s', strtotime($l->date))}</td>
                <td>{$l->products_count}</td>
                <td>{$l->prod_missing_title}</td>
                <td>{$l->prod_missing_desc}</td>
                <td>{$l->prod_missing_keywords}</td>
                <td>{$l->categories_count}</td>
                <td>{$l->categories_missing_title}</td>
                <td>{$l->categories_missing_desc}</td>
                <td>{$l->categories_missing_keywords}</td>
                <td>{$l->tags_count}</td>
                <td>{$l->tags_missing_title}</td>
                <td>{$l->tags_missing_desc}</td>
                <td>{$l->tags_missing_keywords}</td>
            </tr>
        {/foreach}
    </table>
</form>