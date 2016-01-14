<h2>Теги для акций</h2>

<form action="" class="forms forms-inline" method="post">
    <fieldset>
        <legend>Редактирование тега</legend>

        <div class="units-row">
            <div class="unit-25">
                <b>Название</b><br />
                <div>
                    <input type="text" name="title" class="width-100" value="{$res.title}">
                    <input type="hidden" name="id" class="width-100" value="{$res.id}">
                </div>

                <b>УРЛ</b><br />
                <div>
                    <input type="text" name="url" class="width-100" value="{$res.url}">
                </div>
                <div style='float: right'>
                    <input class='btn' type='submit' value='Изменить' />
                </div>
            </div>
        </div>
    </fieldset>
</form>
