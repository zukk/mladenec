<div class="cl">
    <h3>Спасибо!</h3>
    <p>Ваши ответы приняты.

    {if $p->type == Model_Poll::TYPE_COUPON}
        <br />Ваш купон <small>(на&nbsp;{$p->coupon}&nbsp;р.)</small>:
        <br /><strong id="poll_coupon">{$coupon->name}</strong>

        <script>
            $(document).ready(function() { $('span#poll_coupon').hide()});
        </script>
    {/if}
    </p>
</div>

