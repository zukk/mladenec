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
                        {assign var=rand value=rand(1,100000)}
                        <div class="test_{$rand}">
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
                            <a href="javascript:void(0)" class="del_action_tag">
                                &minus;
                            </a>
                        </div>
                    {/foreach}

                </div>

                <div style='float: right; margin-top: 10px;'>
                    <a href="/od-men/blocklinks" class="btn">Назад</a>
                    <input class='btn' type='submit' value='Обновить' />
                </div>
            </div>
        </div>
    </fieldset>
</form>

<script>
    function randomInteger(min, max) {
        var rand = min + Math.random() * (max - min)
        rand = Math.round(rand);
        return rand;
    }

    function addFields(){
        var rand = randomInteger(1, 100000);

        var str = '<div class="test_'+ rand+'">' +
                '<div style="width: 40%; display: inline-block;">Текст</div>&nbsp;' +
                '<div style="width: 40%; display: inline-block;">Ссылка</div>' +
                '<input type="text" name="title[]" required="required" value="" class="width-40">&nbsp;' +
                '<input type="text" name="url[]" required="required" value="" class="width-40">&nbsp;' +
                '<a href="javascript:void(0)" class="edit_action_tag" onclick="addFields(); return false;">&plus;</a>&nbsp;' +
                '<a href="javascript:void(0)" class="del_action_tag">&minus;</a>';
        $("#links").append(str);
    }

    $(function(){
        $(document).on("click", ".del_action_tag", function(){
            $(this).parents('div').eq(0).remove();
        })
    })
</script>