<div id="breadcrumb">    
    <span itemscope itemtype="http://data-vocabulary.org/Breadcrumb">
        <a href="/" itemprop="url" title="На главную"><span itemprop="title">Главная</span></a>
    </span>
    &rarr;
    <span>Отзывы</span>
</div>

<script type="text/javascript">
$(function() {
    $(document).on( "click",  ".liveanswer_btn", function( event ) {
        event.preventDefault();
        $(this).next().toggle();
        //$(this).parent().children('.liveanswer_body').toggle();
    });
});
</script>

<a href="/about/review/add" class="butt comment" rel="ajax" data-fancybox-type="ajax">Оставить отзыв</a>

<h1 class="w500">Отзывы</h1>

<div class="livecomments">
    {include file='comments/list_item.tpl' comments=$comments}
</div>

<div class="more"></div>

<script>
    $(document).ready(function() {
        var $loading = $("<div class='loading'><p>Загрузка&hellip;</p></div>"),
        $footer = $('.more'),
        opts = {
            offset: '100%'
        };
        var offset = 0;
        var perPage = {$perPage};
        var working = false;
        $(window).scroll(function(e){
            if( !$('.more').length )
                return false;

            var sc = window.scrollY ? window.scrollY: document.documentElement.scrollTop;

            if( $footer.offset().top <=( sc + $(window).height() + 500 ) ){

            if( working )
                return false;

            working = true;

            offset += perPage;

            if( offset >= {$count} )
                return false;
            $footer.after($loading);

            $.get('/about/review/list?offset=' + offset, function(data) {
                $loading.remove();
                working = false;
                var d = $(data);
                $('.livecomments').append(d);
                updateLinks();
                $('div.description', d).hide();
                $('div.goods', d).hide();
                $('div.goods_all', d).hide();
                $('p.banner input[type=button]', d).click(function() {
                    $(this).parent('p.banner').siblings('div.description').toggle();
                    $(this).parent('p.banner').parent('div.action_header').siblings('div.goods, div.goods_all').toggle();
                });
            });
            }
        });
    });
</script>