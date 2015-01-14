<div id="breadcrumb">
    <a href="/">Главная</a> &rarr;
    <a href="{$new->get_list_link()}" title="Новости">Новости</a>
</div>

<h1>{$new->name}</h1>

<div id="onew">
    <small>{$new->date|date_ru}</small>
    <div class="cb">
        {$new->text}
    </div>

    <div class="tabs mt cb">
        <div>
            <a class="active t">Вконтакте</a>
            <a class="t">Facebook</a>
        </div>
        <div class="tab-content active">
            <div class="txt">
                <div id="vk_comments"></div>
                {literal}
                    <script type="text/javascript" src="//vk.com/js/api/openapi.js?83"></script>
                    <script type="text/javascript">
                        VK.init({apiId: 3505639, onlyWidgets: true});
                    </script>
                    <script type="text/javascript">
                        VK.Widgets.Comments("vk_comments", {limit: 15, width: "712", attach: false});
                    </script>
                {/literal}
            </div>
        </div>
        <div class="tab-content">
            <div class="txt">
            {literal}
                <div id="fb-root"></div>
                <script type="text/javascript">(function(d, s, id) {
                    var js, fjs = d.getElementsByTagName(s)[0];
                    if (d.getElementById(id)) return;
                    js = d.createElement(s); js.id = id;
                    js.src = "//connect.facebook.net/ru_RU/all.js#xfbml=1";
                    fjs.parentNode.insertBefore(js, fjs);
                }(document, 'script', 'facebook-jssdk'));</script>
            {/literal}
                <div class="fb-comments" data-href="{$site}{$new->get_link(0)}" data-width="712" data-num-posts="15"></div>
            </div>
        </div>
    </div>

    <a href="{$new->get_list_link()}" class="back cb">Вернуться к списку новостей</a>
</div>