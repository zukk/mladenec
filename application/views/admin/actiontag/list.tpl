<h2>Теги для акций</h2>

<form action="" class="forms forms-inline" method="post">
    <fieldset>
        <legend>Добавление тегов</legend>

        <div class="units-row">
            <div class="unit-25">
                <b>Название</b><br />
                <div>
                    <input type="text" name="title" class="width-100" value="">
                </div>

                <b>УРЛ</b><br />
                <div>
                    <input type="text" name="url" class="width-100" value="">
                </div>
                <div style='float: right'>
                    <input class='btn' type='submit' value='Добавить' />
                </div>
            </div>
        </div>
    </fieldset>
</form>

<table id="list">
    <tr>
        <th>#</th>
        <th>Название</th>
        <th>URL</th>
    </tr>
    {foreach from=$actiontag item=i}
        <tr>
            <td>{$i->id}</td>
            <td>{$i->title}</td>
            <td>{$i->url}</td>
        </tr>
    {/foreach}
</table>
{$pager->html('Теги')}