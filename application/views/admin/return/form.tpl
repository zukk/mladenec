{literal}
<script type="text/javascript">
$(document).ready(function() {
    if($('#answer_sent').attr('checked')) {
        $('#warn').show();
    } else {
        $('#warn').hide();
    }
    $('#answer_sent').on('change', function() {
        $('#warn').toggle();
    });
});
</script>
{/literal}
<form action="" method="post" class="forms forms-columnar" enctype="multipart/form-data">
    <h1>Претензия: #{$i->id}</h1>
    <p>
        <label for="user_id">user_id</label>
        <input type="text" id="user_id" name="user_id" value="{$i->user_id|default:''}" size="5" />
        {if $i->user_id}<a href="/od-men/user/{$i->user->id}">{$i->user->name}</a>{/if}
    </p>
    <p>
        <label for="name">Имя</label>
        <input type="text" id="name" name="name" value="{$i->name|default:''}" class="width-50"  />
    </p>
    <p>
        <label for="email">Email</label>
        <input type="text" id="email" name="email" value="{$i->email|default:''}" size="50" readonly="readonly"/>
    </p>
    <p>
        <label>Создана</label><span>{$i->created|date_format:'Y-m-d'}</span>
    </p>
    <p><label for="text">Текст</label><p>{$i->text|default:''}</p>
    <p><label for="text">Картинка</label>{if $i->img}{$i->image->get_img()}{else}нет{/if}</p>
    <p>
        <label for="answer">Ответ</label>
        {if not $i->answer_sent}
            <textarea id="answer" name="answer" cols="60" rows="10" class="html">{$i->answer|default:''}</textarea>
        {else}
            <p>{$i->answer|default:''}</p>
        {/if}
    </p>
    <p>
        <label for="fixed">Обработана</label>
        <input type="checkbox" id="fixed" name="fixed" value="1" {if $i->fixed}checked="checked"{/if} />
    </p>
    <p>
        <label for="answer_sent">Отправить письмо</label>
        <input type="checkbox" id="answer_sent" name="answer_sent" value="1" {if $i->answer_sent}disabled="disabled"{/if} {if !$i->answer_sent}checked="checked"{/if} />
        {if $i->answer_sent}
            <div>Ответ отправлен клиенту на электронную почту {date('d m Y, H:m',$i->answer_sent)}.</div>
        {else}
            <div id="warn" class="no">при&nbsp;нажатии кнопки "Сохранить" пользователю будет выслано письмо c&nbsp;ответом на&nbsp;претензию!</div>
        {/if}
    </p>
    <div class="units-row">
        <div class="unit-80">
            <input name="edit" value="Сохранить" type="submit" class="btn btn-green" />
            {if $i->id}<input name="edit" value="Сохранить и вернуться к списку" type="submit" class="btn  btn-green ok" alt="list" />{/if}
        </div>
    </div>
</form>
