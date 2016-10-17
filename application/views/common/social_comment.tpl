<div class="tabs mt cb">
    <div>
        <a class="active t">Facebook</a>
        <a class="t">Вконтакте</a>
    </div>
    <div class="tab-content active">
        <div class="txt">
            <div id="fb-root"></div>
            <script>
                (function(d, s, id) {
                    var js, fjs = d.getElementsByTagName(s)[0];
                    if (d.getElementById(id)) return;
                    js = d.createElement(s); js.id = id;
                    js.src = "//connect.facebook.net/ru_RU/sdk.js#xfbml=1&version=v2.4";
                    fjs.parentNode.insertBefore(js, fjs);
                }(document, 'script', 'facebook-jssdk'));
            </script>
            <div class="fb-comments" data-href="{$site}{$link}" data-width="712" data-num-posts="15"></div>
        </div>
    </div>
    <div class="tab-content">
        <div class="txt">
            <div id="vk_comments"></div>
            <script src="//vk.com/js/api/openapi.js?83"></script>
            <script>
                VK.init({ apiId: 3505639, onlyWidgets: true});
            </script>
            <script>
                VK.Widgets.Comments("vk_comments", { limit: 15, width: "712", attach: false});
            </script>
        </div>
    </div>
</div>
