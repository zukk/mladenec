{if Kohana::$environment == Kohana::PRODUCTION}
<script>
    window._retag = window._retag || [];

    var levelcode = [], level = Math.min({$level|default:0}, 4);
    levelcode[0] = '9ce8886f78';
    levelcode[1] = '9ce8886f79';
    levelcode[2] = '9ce8886f7a';
    levelcode[3] = '9ce8886f7b';
    levelcode[4] = '9ce8886f7c';

    window._retag.push({ code: levelcode[level], level: level });
    (function () {
        var id = "admitad-retag";
        if (document.getElementById(id)) { return; }
        var s = document.createElement("script");
        s.async = true; s.id = id;
        var r = (new Date).getDate();
        s.src = (document.location.protocol == "https:" ? "https:" : "http:") + "//cdn.trmit.com/static/js/retag.min.js?r="+r;
        var a = document.getElementsByTagName("script")[0];
        a.parentNode.insertBefore(s, a);
    })()
</script>
{/if}
