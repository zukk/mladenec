{if ! empty($user->id)}
    {foreach from=$polls item=p}
        {$questions = $p->questions->order_by('sort')->find_all()}
        {$questions_count = count($questions)}
        {if $questions_count gt 1}
            <h2>{$p->name}</h2>
            {if $p->text}<div>{$p->text}</div>{/if}
        {/if}
        <form action="" method="post" class="ajax" id="poll_{$p->id}">
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

                    {if $can_poll}

                        <p class="mt">
                        {if $p->type == Model_Poll::TYPE_COUPON}
                            <script>
                                $(document).ready(function () {
                                    $('#poll_{$p->id}').submit( function() {
                                        var answered = true;
                                        $('input', $(this)).each(function() {
                                            var iname = $(this).attr('name');
                                            if (iname) {
                                                if (iname.search('poll_var') == 0) {
                                                    if ($(this).attr('type') == 'checkbox') {
                                                        if ($(this).closest('fieldset').find(':checked').length == 0)  answered = false;
                                                    } else {
                                                        if ($(this).val() == '') answered = false;
                                                    }
                                                } else if (iname.search('free') == 0) {
                                                    if ( ! $(this).prop('disabled') && $(this).val() == '') answered = false;
                                                }
                                            }
                                        });
                                        if (answered) return true;
                                        alert('Пожалуйста, ответьте на все вопросы');
                                        return false;
                                    });
                                });
                            </script>

                        {/if}
                            <input type="submit" class="butt fl" value="Отправить ответ" />
                            {if  $p->type == Model_Poll::TYPE_COUPON}
                            <span id="poll_coupon">
                                и&nbsp;получить купон <span style="color:#ed1c24; white-space:nowrap;">на {$p->coupon} р.</span>
                            </span>
                            {/if}
                        </p>
                    {/if}

                {/if}
            {/if}

        </form>
    {foreachelse}
    <p>В&nbsp;данный момент нет активных опросов, на&nbsp;которые вы&nbsp;ещё не&nbsp;отвечали</p>
    {/foreach}
{else}
    <p><a class="no toreg" href="#">Принимать участие в&nbsp;опросах могут только зарегистрированные пользователи</a></p>
{/if}