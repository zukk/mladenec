{if ! empty($user->id)}
    {foreach from=$polls item=p}
        {$questions = $p->questions->order_by('sort')->find_all()}
        {$questions_count = count($questions)}
        {if $questions_count gt 1}
            <h2>{$p->name}</h2>
        {/if}
        <form action="" method="post" class="ajax">
            {if $p->closed}
                <p class="mt"><strong>Опрос завершён.</strong></p>
            {else}
                {include file='page/poll/questions.tpl' p=$p questions=$questions}
                {if ! empty($votes[$p->id])}
                    <p class="mt">{include file='page/poll/ok.tpl'}</p>
                {else}

                    {assign var=can_poll value=1}

                    {if ! empty($p->new_user)}
                        {assign var=orders value=ORM::factory('order')->where('user_id', '=', $user->id)->count_all()}

                        {if $orders gt 1}
                            <p>Принять участие в&nbsp;этом опросе могут только новые пользователи</a></p>
                            {assign var=can_poll value=0}
                        {/if}

                    {/if}

                    {if $can_poll}<p class="mt"><input type="submit" class="butt" value="Отправить ответ" /></p>{/if}

                {/if}
            {/if}

        </form>
    {foreachelse}
    <p>В&nbsp;данный момент нет активных опросов</p>
    {/foreach}
{else}
    <p><a class="no toreg" href="#reg_form">Принимать участие в&nbsp;опросах могут только зарегистрированные пользователи</a></p>
{/if}