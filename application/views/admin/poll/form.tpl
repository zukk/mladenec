{literal}
    <script type="text/javascript">
        $(document).ready(function() {
            $('.area').click(function(e) {
                if (e.target.className == 'no') {
                    $(e.target).closest('tr').remove();
                    $('#poll_changed').val(1);
                }
                if (e.target.className == 'ok') {
                    var name = $('#new_var').val();
                    if (name > '') {
                        $('#all_body').append(
                            '<tr>'
                            + '<td><input name="new_sort[]" value="' + $('#new_sort').val() + '" size="2" /></td>'
                            + '<td><input name="new_var[]" value="' + name + '" size="50 "/></td>'
                            + '<td><input name="new_free[]" type="checkbox" value="1" ' + ($('#new_free').prop('checked') ? 'checked="checked"' : '') + '" size="50 "/></td>'
                            + '<td><a class="no">del</a>'
                            + '</tr>');
                        $('tfoot input').val('').prop('checked', false);

                        $('#poll_changed').val(1);
                    } else {
                        alert('Текст варианта обязателен');
                    }
                }
            });
            $('#new_question').click(function(){
                var template = $('.new_question').eq(0).clone(false);
                $(template).append('<label for="new_q_delete" class="unit-20"><input type="button" class="new_question_delete btn" name="new_q_delete" value="Удалить" class="btn"/></label>');
                $(template).insertAfter('.new_question:last');
                $('.new_question:last input:text').val('');

                $('.new_question_delete').unbind('click');
                $('.new_question_delete').click(function(){$(this).parents('.new_question').detach();});
            });
        });
    </script>
{/literal}

<form action="" method="post" class="forms forms-inline" enctype="multipart/form-data">
    <fieldset class="units-row">
        <legend>#{$i->id}:</legend>
        <label class="unit-100" for="name">Название:
            <input type="text" id="name" name="name" value="{$i->name|default:''}" class="width-100" />
        </label>
        <label for="type" class="unit-40">Тип:
            <select name="type">
                <option {if $i->type eq 0}selected="selected"{/if} value="0">Обычный</option>
                <option {if $i->type eq 1}selected="selected"{/if} value="1">После оформления заказа</option>
                <option {if $i->type eq 2}selected="selected"{/if} value="2">При регистрации</option>
            </select>
        </label>
        <label class="unit-20" for="active">Активность:
            <input type="checkbox" id="active" name="active" value="1" {if $i->active}checked="checked"{/if}  />
        </label>
        <label for="closed" class="unit-20">Завершён:
            <input type="checkbox" id="closed" name="closed" value="1" {if $i->closed}checked="checked"{/if} />
        </label>
    </fieldset>
    {if ! empty($i->id)}
        <h3>Вопросы и варианты ответов:</h3>
        {foreach from=$i->questions->order_by('sort')->find_all() item=q name=qk}
            <fieldset class="units-row" style="background-color: #F0F0F0">
                <legend style="background-color: #F3F3F3; border-radius:1em;">Вопрос #{{$smarty.foreach.qk.index + 1}}:</legend>
                <label class="unit-20">Сортировка:
                    <input name="misc[questions][{$q->id}][sort]" type="number" value="{$q->sort}" size="3" />
                </label>
                <label class="unit-50">Текст вопроса:
                    <input name="misc[questions][{$q->id}][name]" value="{$q->name}"  class="width-50" />
                </label>
                <label class="unit-20">Тип вопроса:
                    <select name="misc[questions][{$q->id}][type]">
                        <option {if $q->type eq Model_Poll_Question::TYPE_RADIO}selected="selected"{/if} value="{Model_Poll_Question::TYPE_RADIO}">Выбор варианта</option>
                        <option {if $q->type eq Model_Poll_Question::TYPE_MULTI}selected="selected"{/if} value="{Model_Poll_Question::TYPE_MULTI}">Множественный выбор</option>
                        <option {if $q->type eq Model_Poll_Question::TYPE_TEXT}selected="selected"{/if} value="{Model_Poll_Question::TYPE_TEXT}">Текст</option>
                        <option {if $q->type eq Model_Poll_Question::TYPE_PRIORITY}selected="selected"{/if} value="{Model_Poll_Question::TYPE_PRIORITY}">Приоритет</option>
                    </select>
                </label>
                {if $q->type neq Model_Poll_Question::TYPE_TEXT}
                    <fieldset class="cb units-row" style="background-color: #FFF">
                        <legend style="background-color: #FFF; border-radius:1em;">Ответы на вопрос #{{$smarty.foreach.qk.index + 1}}:</legend>
                        {foreach from=$q->variants->order_by('sort')->find_all() item=v name=k}
                            <label class="cb unit-20">Сортировка:
                                <input name="misc[variants][{$v->id}][sort]" type="number" value="{$v->sort}" size="3" />
                            </label>
                            <label class="unit-50">Текст варианта ответа:
                                <input name="misc[variants][{$v->id}][name]" value="{$v->name}"  class="width-50" />
                            </label>
                            <label class="unit-20">Свободный ответ:
                                <input name="misc[variants][{$v->id}][free]" type="checkbox" value="1" {if $v->free}checked="checked"{/if} size="3" />
                            </label>

                        {/foreach}
                        <h5 class="cb">Новый вариант ответа:</h5>
                        <label class="unit-20">Сортировка:
                            <input name="misc[new_var][{$q->id}][sort][]" type="number" value="{$smarty.foreach.k.total}" />
                        </label>
                        <label class="unit-50">Текст варианта ответа:
                            <input name="misc[new_var][{$q->id}][name][]" value=""  class="width-50" />
                        </label>
                        <label class="unit-20">Свободный ответ:
                            <input name="misc[new_var][{$q->id}][free][]" type="checkbox" value="1" />
                        </label>
                    </fieldset>
                {/if}
            </fieldset>
        {/foreach}
        <fieldset class="units-row new_question" style="background-color: #F0F0F0">
            <legend style="background-color: #F3F3F3; border-radius:1em;">Новый вопрос:</legend>
            <label class="unit-20">Сортировка:
                <input name="misc[new_q_sort][]" type="number" value="{$smarty.foreach.qk.total}" size="3" />
            </label>
            <label class="unit-70">Текст вопроса:
                <input name="misc[new_q_name][]" value=""  class="width-50" />
            </label>
           <label class="unit-20">Тип вопроса:
                    <select name="misc[new_q_type][]">
                        <option value="{Model_Poll_Question::TYPE_RADIO}">Выбор варианта</option>
                        <option value="{Model_Poll_Question::TYPE_MULTI}">Множественный выбор</option>
                        <option value="{Model_Poll_Question::TYPE_TEXT}">Текст</option>
                        <option value="{Model_Poll_Question::TYPE_PRIORITY}">Приоритет</option>
                    </select>
                </label>
        </fieldset>
    {/if}
    <p><input type="button"  id="new_question" class="btn" value="Добавить еще вопрос" /></p>
    <p>
        <label for="new_user">Только для новых пользователей</label>
        <input type="checkbox" id="new_user" name="new_user" value="1" {if $i->new_user}checked="checked"{/if} />
    </p>
    <p>
        
    </p>
    <p class="do">
        <input name="edit" value="Сохранить" type="submit" class="btn ok"/>
        <input name="edit" value="Сохранить и вернуться к списку" type="submit" class="btn ok" alt="list" />
    </p>
    {if $i->id}
        <p class="cb"><a href="{$i->id}/del" onclick="return confirm('Удалить насовсем?')" class="red">Удалить</a></p>
    {/if}
</form>
    <p class="cb"></p>