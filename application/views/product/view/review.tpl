<div id="to-write-review" >
    {if $cgood->rating neq 0}
    <span itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
        <span class="stars"><span style="width:{$cgood->rating*20}%"></span></span> <small itemprop="reviewCount">{$cgood->review_qty}</small>
        <meta itemprop="ratingValue" content="{$cgood->rating}" />
    </span>
    {else}
        <span class="stars"></span>
    {/if}

    {if $cgood->review_qty gt 0}
        <a href="#tabs" class="">Посмотреть или написать отзыв</a>
        <script>
            $(function(){
                $('#to-write-review').click(function(){
                    $('#reviews').click();
                    $('html, body').animate({
                        scrollTop: $("a[name=tabs]").offset().top
                    }, 400);
                    return false;
                });
            });
        </script>
    {else}
        <a data-url="/review/add/" href="#{$cgood->id}" class="small i i_pen appendhash" rel="ajax" data-fancybox-type="ajax">Написать отзыв</a>
    {/if}
</div>
