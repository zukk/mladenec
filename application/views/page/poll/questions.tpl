<input name="poll_id" value="{$p->id}" type="hidden" />
{foreach from=$questions item=q}
    <fieldset>
        <legend>{$q->name}</legend>
        {$variants = $q->variants->order_by('sort')->find_all()}
        {if  $q->type eq Model_Poll_Question::TYPE_TEXT}
            <p class="poll"><textarea class="txt" name="q_text[{$q->id}]" ></textarea></p>
        {/if}
        {foreach from=$variants item=v}
            {if $q->type eq Model_Poll_Question::TYPE_RADIO}
                <label class="label"><i class="radio"></i><input type="radio" class="poll_radio" name="poll_var" value="{$v->id}" /> <span class="var_name">{$v->name}</span></label>
                {if $v->free}
                    <p class="poll"><input type="text" class="txt poll_free" placeholder="Введите свой вариант" rel="{$v->id}" name="free[{$v->id}]" value="" disabled="disabled" /></p>
                {/if}
            {elseif  $q->type eq Model_Poll_Question::TYPE_MULTI}
                <label class="label"><i class="check"></i><input type="checkbox" class="poll_multi" name="poll_var_{$v->id}" value="{$v->id}" /> <span class="var_name">{$v->name}</span></label>
                {if $v->free}
                    <p class="poll"><input type="text" class="txt poll_free" placeholder="Введите свой вариант" rel="{$v->id}" name="free[{$v->id}]" value="" disabled="disabled" /></p>
                {/if}
            {elseif $q->type eq Model_Poll_Question::TYPE_PRIORITY}
                {$cnt = count($variants)}
                <label class="priority"><input id="var_priority_{$v->id}" type="hidden" name="poll_var_{$v->id}" value="" />
                    {for $x=1 to 5}<span rel="var_priority_{$v->id}" title="{$x}"></span>{/for} <em class="var_name">{$v->name}</em>
                    {if $v->free}
                        <input type="text" class="txt poll_free" placeholder="Введите свой вариант" rel="{$v->id}" name="free[{$v->id}]" size="2" value="" disabled="disabled" />
                    {/if}
                </label>
                <p class="poll"></p>
            {/if}
        {/foreach}
    </fieldset>
{/foreach}
 <script type="text/javascript">
    {literal}$('.user-registration .regpoll input:radio').radio();{/literal}
</script>