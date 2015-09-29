{if Kohana::$environment eq Kohana::PRODUCTION}

    {assign var=metrica_id value=0}
    {if Kohana::$server_name eq 'mladenec'}{assign var=metrica_id value=11895307}{/if}
    {if Kohana::$server_name eq 'ogurchik'}{assign var=metrica_id value=11895307}{/if}

    {if $metrica_id}
        <!-- Yandex.Metrika counter -->
        <div style="display:none;"><script>
            if (typeof(yaParams) != 'undefined') {  {* метрика для спасибо-страницы, передаёт данные о заказе *}

                (function (d, w, c) {
                    (w[c] = w[c] || []).push(function() {
                        try {
                            w.yaCounter{$metrica_id} = new Ya.Metrika({ id:{$metrica_id},
                                webvisor:true,
                                clickmap:true,
                                accurateTrackBounce:true,
                                ut:"noindex",
                                params:yaParams||{ }});
                        } catch(e) { }
                    });

                    var n = d.getElementsByTagName("script")[0],
                            s = d.createElement("script"),
                            f = function () { n.parentNode.insertBefore(s, n); };
                    s.type = "text/javascript";
                    s.async = true;
                    s.src = (d.location.protocol == "https:" ? "https:" : "http:") + "//mc.yandex.ru/metrika/watch.js";

                    if (w.opera == "[object Opera]") {
                        d.addEventListener("DOMContentLoaded", f, false);
                    } else { f(); }
                })(document, window, "yandex_metrika_callbacks");

            } else {    {* метрика для всех других страниц*}

                (function(w, c) {
                    (w[c] = w[c] || []).push(function() {
                        try {
                            w.yaCounter{$metrica_id} = new Ya.Metrika({ id:{$metrica_id}, enableAll: true, ut:"noindex", webvisor:true });
                        }
                        catch(e) { }
                    });
                })(window, "yandex_metrika_callbacks");
            }
        </script></div>
        <script src="//mc.yandex.ru/metrika/watch.js" defer="defer"></script>
        <noscript><div><img src="//mc.yandex.ru/watch/{$metrica_id}?ut=noindex" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
        <!-- /Yandex.Metrika counter -->
    {/if}
{/if}
