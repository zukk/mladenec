{foreach from=$comments['themes'] item=c}
	{if !empty( $comments['questions'][$c->id][0] )}
    <div class="livecomment_box">
        <div class="livecomment">
            <a href="/about/review/{$c->id}">{$c->user_name}</a>
            <small>{$comments['questions'][$c->id][0]->date|date_format:'%d-%m-%y %H:%M'}</small>
            <strong>{$c->name}</strong>
            {$comments['questions'][$c->id][0]->text|nl2br}
            <i></i>
        </div>
		{if !empty($comments['answers'][$comments['questions'][$c->id][0]->id])}
        <div class="liveanswer">
            <p class="liveanswer_btn">Ответ магазина</p>
            <div class="liveanswer_body cb">{$comments['answers'][$comments['questions'][$c->id][0]->id][0]->answer}</div>
            <i></i>
        </div>
        <small>{$comments['answers'][$comments['questions'][$c->id][0]->id][0]->get_answer_by()}</small>
		{/if}
        <div class="cb"></div>
    </div>
	{/if}
{/foreach}
