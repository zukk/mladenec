<form action="" method="post" class="forms forms-columnar" enctype="multipart/form-data">
    <div class="units-row">
        <div class="unit-70">
            <h1>Отзыв #{$i->id}</h1>
            К товару «<a href="/od-men/good/{$i->good->id}">{$i->good->group_name} {$i->good->name}</a>».
        </div>
        <div class="unit-30">
            <a target="_blank" class="btn" href="{$i->good->get_link(0)}">открыть на сайте</a>.
        </div>
    </div>
    <p>
        <label for="date">Время</label>{$i->time|date_format:'d-m-y h:s'}
    </p>
    <p>
        <label for="user">Пользователь</label>
        {if $i->user_id}
            <a href="/od-men/user/{$i->author->id}">{$i->author->name}</a>
        {else}
            Аноним
        {/if}
    </p>
    <p>
        <label for="user">Оценка</label>
        {if $i->rating}{$i->rating}{else}нет оценки{/if}
    </p>
    <p>
        <label for="name">Название</label>
        <input type="text" id="name" name="name" value="{$i->name}" class="width-50" />
    </p>
    <p>
        <label for="text">Текст</label>
        <textarea id="text" name="text" cols="50" rows="5" class="txt">{$i->text}</textarea>
    </p>
    <p>
        <label for="params">Параметры отзыва</label>
        <div class="area">
        {foreach from=$i->params->find_all() item=p}
            {if $p->type eq 1}
                {capture assign=good}
                    {$good|default:''}
                    <li>{$p->value} <a href="/od-men/section/{$p->section_id}">?</a></li>
                {/capture}
            {/if}
            {if $p->type eq 0}
                {if $p->section_id}
                    {capture assign=neutral}
                        {$neutral|default:''}
                        <li>{$p->value} <a href="/od-men/section/{$p->section_id}">?</a></li>
                    {/capture}
                {else}
                    {capture assign=me}
                        {$me|default:''}
                        <li>{$p->value} <a href="/od-men/section/{$p->section_id}">?</a></li>
                    {/capture}
                {/if}
            {/if}
            {if $p->type eq -1}
                {capture assign=bad}
                    {$bad|default:''}
                    <li>{$p->value} <a href="/od-men/section_param/del/{$p->id}" class="del">x</a></li>
                {/capture}
            {/if}
        {/foreach}

        {if !empty($good)}
            <ul class="good">
                <li><strong>Положительный отзыв</strong></li>
                {$good}
            </ul>
        {/if}
        {if !empty($neutral)}
            <ul class="neutral">
                <li><strong>Использовать с</strong></li>
                {$neutral}
            </ul>
        {/if}
        {if !empty($bad)}
            <ul class="bad">
                <li><strong>Отрицательный отзыв</strong></li>
                {$bad}
            </ul>
        {/if}
        {if !empty($me)}
            <ul class="bad">
                <li><strong>О себе</strong></li>
                {$me}
            </ul>
        {/if}
        </div>
    </p>
    <p>
        <label for="active">Активность</label>
        <input type="checkbox" id="active" name="active" value="1" {if $i->active}checked="checked"{/if} />
    </p>
    <p>
        <label for="active">Плохой отзыв (не публиковать)</label>
        <input type="checkbox" id="hide" name="hide" value="1" {if $i->hide}checked="checked"{/if} />
    </p>
    <div class="units-row">
        <div class="unit-80">
            <input name="edit" value="Сохранить" type="submit" class="btn btn-green" />
            {if $i->id}<input name="edit" value="Сохранить и вернуться к списку" type="submit" class="btn  btn-green ok" alt="list" />{/if}
        </div>
    </div>

</form>