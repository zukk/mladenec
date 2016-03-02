<form action="" class="forms forms-inline" method="post">
    <fieldset>
        <legend>Редактирование ссылки</legend>
        <div class="units-row">
            <div class="unit-50">
                <b>Название</b><br />
                <div>
                    <input type="text" name="link" class="width-100" value="{$res.0->link}">
                    <input type="hidden" name="id" class="width-100" value="{$res.0->id}">
                </div>

                <br /><b>Добавление текста и ссылки</b><br />
                <div id="links">
                    {foreach from=$res item=i}
                        <div style="width: 40%; display: inline-block;">
                            Текст
                        </div>
                        <div style="width: 40%; display: inline-block;">
                            Ссылка
                        </div>

                        <input type="text" name="title[]" required="required" value="{$i->blocklinksanchor->title}" class="width-40">
                        <input type="text" name="url[]" required="required" value="{$i->blocklinksanchor->url}" class="width-40">

                        <a href="javascript:void(0)" class="edit_action_tag" onclick="addFields(); return false;">
                            &plus;
                        </a>
                    {/foreach}

                </div>

                <div style='float: right; margin-top: 10px;'>
                    <a href="/od-men/blocklinks" class="btn">Назад</a>
                    <input class='btn' type='submit' value='Изменить' />
                </div>
            </div>
        </div>
    </fieldset>
</form>

<script>
    function addFields(){
        var str = '<div style="width: 40%; display: inline-block;">Текст</div>&nbsp;' +
                '<div style="width: 40%; display: inline-block;">Ссылка</div>' +
                '<input type="text" name="title[]" value="" class="width-40">&nbsp;' +
                '<input type="text" name="url[]" value="" class="width-40">&nbsp;' +
                '<a href="javascript:void(0)" class="edit_action_tag" onclick="addFields(); return false;">&plus;</a>';
        $("#links").append(str);
     }
</script>