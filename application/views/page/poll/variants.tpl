<input type="hidden" name="poll_id" value="{$poll_id}" />
{foreach from=$variants item=v}
    <label class="label"><i class="radio"></i><input type="radio" name="var_id" value="{$v->id}" /> {$v->name}</label>
    {if $v->free}
    <p class="poll"><input type="text" class="txt poll_free" rel="{$v->id}" name="free[{$v->id}]" value="" disabled="disabled" maxlength="250" /></p>
    {/if}
{/foreach}
 <script type="text/javascript">
    {literal}
        $('#reg_form .regpoll input:radio').radio();
        $('input.poll_radio,input.poll_multi,').change(function() {
            var id = $(this).val(), f = $(this).closest('form');
            $('input[rel='+id+']', f).prop('disabled', false);
            $('input.poll_free', f).not('input[rel='+id+']').prop('disabled', true).val('');
        })
    {/literal}
</script>