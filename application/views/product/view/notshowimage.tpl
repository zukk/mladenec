<script>
    $(document).ready(function(){
        var ns = $('#no_sale');
        ns.load(function() {
            var shadow = $('<div>Нет в продаже</div>').css({
                position: 'absolute',
                top: ns.position().top,
                left: 0,
                width: ns.width(),
                height: '80px',
                lineHeight: '80px',
                textAlign: 'center',
                padding: Math.ceil((ns.height() - 80) / 2) + 'px 0',
                background: 'rgba(200, 200, 200, .6)',
                color: '#000',
                fontSize: '30px'
            });
            ns.before(shadow);
        });
    });
</script>

{if not empty($images) and is_array($images)}
    {assign var=i value=$images|current}

    {if $i.255}<img id="no_sale" class="{$css}" src="{$i.255->get_img(0)}" alt="{$good_name}" />{/if}

{else}
    <img id="no_sale" class="{$css}" src="/i/no.png" alt="Нет в наличии" title="Нет в наличии">

{/if}

