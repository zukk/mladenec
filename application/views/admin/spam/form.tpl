<script>
    $(document).ready(function() {
       $('#clear_list_button').click(function() {
           $('#clear_list').val('do');
           $('#clear_list_button').replaceWith('<p>нажмите <input name="edit" type="submit" value="Сохранить" /> для очистки списка подписчиков</p>');
       })
    });
</script>
<form action="" method="post" class="forms forms-columnar" enctype="multipart/form-data">
    <h1>Рассылка #{$i->id} <span class="{if $i->status == Model_Spam::STATUS_PROCEED}red{elseif $i->status == Model_Spam::STATUS_READY}}green{/if}">[{$i->status()}]</span></h1>
    <p>
        <label for="name">Название<br />(тема письма)</label>
        <input type="text" id="name" name="name" value="{$i->name|escape:html|default:''}" class="width-50" />
    </p>
    <p class="forms-inline">
        <label class="unit-40">Рассылать после</label>
        {html_select_date time=$i->from field_array=from field_order=DMY all_empty='' end_year="+2"}
        {html_select_time minute_interval=30 display_seconds=0 time=$i->from field_array=from all_empty=''}
    </p>
    {if $i->id}
    <div class="area">
        <label class="unit-40">Получатели</label>
        {assign var=recipients value=$i->recipients()}
        {if not $recipients}
            Рассылку получат все подписчики
        {else}
            Рассылку получит {$recipients|count} чел
            <input name="clear_list" id="clear_list" value="0" type="hidden" />
            <button class="btn btn-red" id="clear_list_button">Очистить список</button>
        {/if}
    </div>
    <div class="area">
        <p>Загрузить excel c email получателей:
            {if $recipients}(<small>список добавляется к существующим</small>){/if}
            <input type="file" name="excel" />
        </p>
    </div>
    <div class="area">
        {if $i->status lt Model_Spam::STATUS_PROCEED}
            <p>Загрузите архив с файлами рассылки <br />
                (<small>Zip-архив со файлом index.html и картинками, строго без папок</small>)
                <input type="file" name="zip" />
            </p>
        {/if}

        {if $i->status gte Model_Spam::STATUS_NEW AND $i->status lt Model_Spam::STATUS_PROCEED}
        <p>
            <input type="text" id="mail" name="mail" value="" size="50" placeholder="user@mail.to"/>
            <input class="no" type="submit" value="Отослать тестовое письмо" name="edit" />
        </p>
        {/if}

        {if $i->status eq Model_Spam::STATUS_READY}
            <p>
                {ORM::factory('user')->where('sub', '=', 1)->count_all()} получателя
                <input type="hidden" id="spamit" name="spamit" value="0" />
                <input class="no" name="edit" type="submit" value="Начать рассылку" onclick="if (confirm('Вы уверены что хотите начать рассылку?')) {literal}{ document.getElementById('spamit').value = 1; return true;}{/literal}" />
            </p>
        {/if}

        {if $i->status eq Model_Spam::STATUS_PROCEED}
            <p>
                Отослано писем {DB::select(DB::expr('COUNT(*) as c'))->from('z_spam_user')->where('spam_id', '=', $i->id)->where('status', '>', 0)->execute()->get('c')}
                / осталось {DB::select(DB::expr('COUNT(*) as c'))->from('z_spam_user')->where('spam_id', '=', $i->id)->where('status', '=', 0)->execute()->get('c')}
            </p>
        {/if}
    </div>
    {else}
        Дайте название рассылке и&nbsp;нажмите &laquo;сохранить&raquo;&nbsp;&mdash; появятся новые опции.
    {/if}

    {if $i->id AND $i->status gte Model_Spam::STATUS_NEW}
    <div class="units-row">
        <label>Предпросмотр:<br /><a href="/upload/mail/{$i->id}/index.html" target="_blank">в&nbsp;новом окне</a></label>
        <div class="area hi"><iframe src="/upload/mail/{$i->id}/index.html" style="width:100%;"></iframe></div>
    </div>
    {/if}

    <div class="units-row">
        <div class="unit-80">
            <input name="edit" value="Сохранить" type="submit" class="btn btn-green" />
            {if $i->id}<input name="edit" value="Сохранить и вернуться к списку" type="submit" class="btn  btn-green ok" alt="list" />{/if}
        </div>
    </div>
</form>