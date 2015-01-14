{literal}
<script type="text/javascript">
$(document).ready(function() {
    $(document).on('click', '#reset_password', function() {
        $('#reset_form').submit();
    });
});
</script>
{/literal}

<form action="" method="post" class="forms forms-columnar" enctype="multipart/form-data">
    <div class="units-row">
        <h1 class="unit-80">#{$i->id} {$i->name}</h1>
        <div class="unit-20"><input type="button" class="btn btn-red" id="reset_password" value="Сбросить пароль" name="reset_password" /></div>
    </div>
    <p>
        <label for="name">Имя</label>
        <input type="text" id="name" name="name" value="{$i->name|default:''}" class="width-50" />
    </p>
    <p>
        <label for="name">Отчество</label>
        <input type="text" id="second_name" name="second_name" value="{$i->second_name|default:''}" class="width-50" />
    </p>
    <p>
        <label for="name">Фамилия</label>
        <input type="text" id="last_name" name="last_name" value="{$i->last_name|default:''}" class="width-50" />
    </p>
    <p>
                <label for="email">
                    Email<br />
                    <a class="changee">изменить</a>
                </label>
                <input type="text" id="email" name="email" value="{$i->email|default:''}" size="50" readonly="readonly" />
            </p>
    <p>
                <label for="email">Телефон</label>
                <input type="text" id="phone" name="phone" value="{$i->phone|default:''}" class="width-50" />
            </p>
    <p>
                <label for="email">Доп.телефон</label>
                <input type="text" id="phone2" name="phone2" value="{$i->phone2|default:''}" class="width-50" />
            </p>
    <p>
                <label for="sum">Сумма заказов</label>
                <input type="text" id="sum" name="sum" value="{$i->sum|default:''}" size="50" readonly="readonly" />
            </p>
    <p>
                <label for="status_id">Любимый клиент</label>
                <input type="checkbox" id="status_id" name="status_id" value="1" {if $i->status_id}checked="checked"{/if}/>
            </p>
    <p>
                <label for="sub">Подписка на рассылку</label>
                <input type="checkbox" id="sub" name="sub" value="1" {if $i->sub}checked="checked"{/if}/>
            </p>
        {if $user->allow('admin')}

        <fieldset>
            <legend>Доступ:</legend>
            
                {if $i->allow('admin')}
                    <p class="cb">
                        <label for="misc[access][admin]">Полный доступ:</label>
                        <input type="checkbox" id="access_admin" name="misc[access][admin]" value="1" checked="checked">
                    </p>
                {else}
                    {foreach from=Kohana::message('admin') key=mod_code item=mod_name}
                        <p class="fl width-33">
                            <label class="cb" for="misc[access][{$mod_code}]">{$mod_name}</label>
                            <input type="checkbox" id="access_{$mod_code}" name="misc[access][{$mod_code}]" value="1" {if $i->allow($mod_code)}checked="checked"{/if}>
                        </p>
                    {/foreach}
                {/if}
        
        </fieldset>
        {/if}
        <div class="units-row">
            <div class="unit-80">
                <input name="edit" value="Сохранить" type="submit" class="btn btn-green" />
                {if $i->id}<input name="edit" value="Сохранить и вернуться к списку" type="submit" class="btn  btn-green ok" alt="list" />{/if}
            </div>
        </div>
</form>

<h3>Накопленные по акциям баллы</h3>
{$actions = ORM::factory('action')
                ->where('count_from', 'IS NOT', NULL)
                ->where('active', '=', 1)
                ->order_by('name','ASC')
                ->find_all()->as_array()}
{if not empty($actions)}
{$credits = DB::select('action_id','sum','qty')
                ->from('z_action_user')
                ->where('user_id',   '=',  $i->pk())
                ->where('action_id', 'IN', $actions)
                ->execute()->as_array('action_id')}
{/if}
{if not empty($credits)}
    <table class="tt">
        <tr>
            <th class="l">Название акции</th>
            <th class="l">Накопление баллов до</th>
            <th class="l">Баллы</th>
        </tr>
        {foreach $actions as $a}
            <tr>
                <td>{$a->get_link()}</td>
                <td>{$a->count_to|date_ru|default:'&mdash;'}</td>
                <td>
                    {$credits[$a->pk()]['sum']|price|default:'&mdash;'}
                </td>
            </tr>
        {/foreach}
    </table>
{/if}
<h3 for="orders">Заказы</h3>

{if $orders}
    <table class="tt">
        <thead>
            <tr>
                <th>Номер</th>
                <th>Создан</th>
                <th>Сумма</th>
                <th>Доставка</th>
                <th>Итого</th>
                <th>Состояние</th>
            </tr>
        </thead>
        <tbody>
        {foreach from=$orders item=o}
            <tr {cycle values='class="odd",'}>
                <td><a href="{Route::url('admin_edit',['model'=>'order','id'=>$o->id])}">{$o->id}</a></td>
                <td>{$o->created}</td>
                <td class="r">{$o->price|price}</td>
                <td class="r">{$o->price_ship|price}</td>
                <td class="r">{$o->get_total()|price}</td>
                <td class="c">{$o->status()}</td>
            </tr>
        {/foreach}
        </tbody>
    </table>
        <p><a href="{Route::url('admin_list',['model'=>'order'])}?user_id={$i->id}">Все заказы пользователя</a></p>
{else}
    <p>У пользователя нет ещё ни одного заказа</p>
{/if}

<h3>Отзывы</h3>  
{if $comments}
    <table class="tt">
        <thead>
            <tr>
                <th>Номер<br />Создан</th>
                <th>Имя / Текст / Ответ</th>
                <th>Опубликован</th>
            </tr>
        </thead>
        <tbody>
        {foreach from=$comments['themes'] key=k item=theme}
            <tr {cycle values='class="odd",'}>
                <td><a href="{Route::url('admin_edit',['model'=>'comment_theme','id'=>$theme->id])}" target="_blank">{$theme->id}</a><br />{$comments['questions'][$k][0]->date}</td>
                <td>
                    <p>{$theme->name}</p>
					{if !empty( $comments['questions'][$k] )}
                    <p style='font-style: italic'>{$comments['questions'][$k][0]->text|truncate:150}</p>
					{/if}
					{if !empty( $comments['answers'][$k] )}
                    <p>{$comments['answers'][$k][0]->answer|truncate:150}</p>
					{/if}
                </td>
                <td class="r">{if $theme->active}<a href="{$theme->get_link(FALSE)}" target="_blank">да</a>{else}нет{/if}</td>
            </tr>
        {/foreach}
        </tbody>
    </table>
        <p><a href="{Route::url('admin_list',['model'=>'comment_theme'])}?user_id={$i->id}">Все отзывы</a></p>
{else}
    <p>Пользователь ещё не оставил ни одного отзыва</p>
{/if}
                
            
<h3>Отзывы о товарах</h3>
                
{if $reviews}
    <table class="tt">
        <thead>
            <tr>
                <th>Номер<br />Создан</th>
                <th>ID товара</th>
                <th>Имя / текст</th>
                <th>Состояние</th>
            </tr>
        </thead>
        <tbody>
        {foreach from=$reviews item=review}
            <tr {cycle values='class="odd",'}>
                <td><a href="{Route::url('admin_edit',['model'=>'good_review','id'=>$review->id])}" target="_blank">{$review->id}</a><br />{$review->time}</td>
                <td>{if $review->good_id}
                    <a href="{Route::url('admin_edit',['model'=>'good','id'=>$review->good_id])}" target="_blank">{$review->good_id}</a>
                    {/if}
                    </td>
                <td>
                    <p>{$review->name}</p>
                    <p>{$review->text|truncate:150}</p>    
                </td>
                <td class="r">{if $review->active}опубликован</a>{else}скрыт{/if}</td>
            </tr>
        {/foreach}
        </tbody>
    </table>
        <p><a href="{Route::url('admin_list',['model'=>'good_review'])}?user_id={$i->id}">Все отзывы о товарах</a></p>
{else}
    <p>Пользователь ещё не оставил ни одного отзыва о товарах</p>
{/if}

<h3>Претензии</h3>
                
{if $returns}
    <table class="tt">
        <thead>
            <tr>
                <th>Номер<br />Создан</th>
                <th>Имя / текст</th>
                <th>Состояние</th>
            </tr>
        </thead>
        <tbody>
        {foreach from=$returns item=return}
            <tr {cycle values='class="odd",'}>
                <td><a href="{Route::url('admin_edit',['model'=>'return','id'=>$return->id])}" target="_blank">{$return->id}</a><br />{$return->created}</td>
                <td>
                    <p>{$return->name}</p>
                    <p>{$return->text|truncate:150}</p>    
                </td>
                <td class="r">{if $return->fixed}обработана</a>{else}не обработана{/if}</td>
            </tr>
        {/foreach}
        </tbody>
    </table>
        <p><a href="{Route::url('admin_list',['model'=>'return'])}?user_id={$i->id}">Все претензии</a></p>
{else}
    <p>Претензий нет</p>
{/if}

<form action="/od-men/user" method="post" id="reset_form">
    <input type="hidden" name="password" value="{$i->id}" />
    <input type="hidden" name="reset_password" value="{$i->id}" />
</form>

