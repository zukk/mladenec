{if $i->id}
    <h1 class="unit-80">Купон {$i->id}</h1>
{else}
    <h1>Создание купона</h1>
{/if}

<form action="" method="post" class="forms forms-columnar" enctype="multipart/form-data">
    <p style="clear:both;">
        <label for="type">Тип купона</label>
        <select name="type" id="type">
            {html_options options=Model_Coupon::type() selected=$i->type}
        </select>
    </p>

    <p>
        <label for="name">Код купона</label>
        <input type="text" id="name" name="name" value="{$i->name}" class="width-25" maxlength="16" />
    </p>

    <div id="type0" {if not $i->id OR $i->type != Model_Coupon::TYPE_SUM}class="hide"{/if}>
        <p>
            <label for="sum">Сумма скидки</label>
            <input type="text" id="sum" name="sum" value="{$i->sum}" class="width-25" />
        </p>
    </div>

    <div id="type1" {if not $i->id OR $i->type != Model_Coupon::TYPE_PERCENT}class="hide"{/if}>
        {if $i->id}
            {foreach from=$i->get_goods() key=discount item=goods}
                <label>
                    Товары со скидкой {$discount}%<br />
                    <a onclick="$('#goods{$discount} .trdel').click()" class="no">удалить все</a>
                </label>

                <div class="area" id="goods{$discount}">
                    {include file='admin/good/chosen.tpl' goods=$goods discount=$discount}
                </div>
            {/foreach}

            <label>Скидка (%)<input type="text" value="1" name="misc[discount]"/></label>
            <div class="area" id="goods{$discount}">
                {include file='admin/good/chosen.tpl' goods=[] discount=0}
            </div>
        {/if}
    </div>

    {if $i->type eq Model_Coupon::TYPE_PERCENT}
    <p>
        <label for="max_sku">Число товаров одного наименования на которые дается скидка (0 - без ограничения)</label>
        <input type="text" id="max_sku" name="max_sku" value="{$i->max_sku}" class="width-25" />
    </p>
    {/if}

    <p>
        <label for="min_sum">Минимальная сумма заказа</label>
        <input type="text" id="min_sum" name="min_sum" value="{$i->min_sum}" class="width-25" />
    </p>
    <p>
        <label for="active">Активность</label>
        <input type="checkbox" id="active" name="active"{if $i->active} checked="checked"{/if} value="1" />
    </p>
    <p>
        <label for="per_user">Использований на человека</label>
        <input type="text" id="per_user" name="per_user" value="{$i->per_user}" class="width-25" />
    </p>
    <p>
        <label for="uses">Использований</label>
        <input type="text" id="uses" name="uses" value="{$i->uses}" class="width-25" /> {if $i->used}(уже использовано {$i->used} раз){/if}
    </p>

    <p class="forms-inline">
        <label class="unit-40">Начало</label>
        {html_select_date time=$i->from field_array=from field_order=DMY all_empty='' start_year="2014" end_year="+1"}
        {html_select_time minute_interval=30 display_seconds=0 time=$i->from field_array=from all_empty=''}
    </p>
    <p class="forms-inline">
        <label class="unit-40">Окончание</label>
        {html_select_date time=$i->to field_array=to field_order=DMY all_empty='' end_year="+1"}
        {html_select_time minute_interval=30 display_seconds=0 time=$i->to field_array=to all_empty=''}
    </p>

    <div class="units-row">
        <div class="unit-80">
            <input name="edit" value="Сохранить" type="submit" class="btn btn-green" />
            {if $i->id}<input name="edit" value="Сохранить и вернуться к списку" type="submit" class="btn  btn-green ok" alt="list" />{/if}
        </div>
    </div>

</form>