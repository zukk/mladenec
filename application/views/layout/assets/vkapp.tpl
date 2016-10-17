<link href="/c/{$vitrina}.css" rel="stylesheet" type="text/css" />
<script src="https://vk.com/js/api/xd_connection.js?2" type="text/javascript"></script>
{literal}
<script type="text/javascript">
/* <![CDATA[ */
(function() {
  if ('undefined' !== typeof(VK)) { VK.init({ apiId: {/literal}{Kohana::$hostnames[Kohana::$server_name]['app_id']}{literal} }); }
  
  function autoResizeAppFrame(width) {
    if ('undefined' == typeof(window.top) || window == window.top) return false;
    if ('undefined' == typeof (VK.callMethod)) return false;    
    var w = window, d = document, e = d.documentElement, g = d.getElementsByTagName('body'), wheight = g.item(0).clientHeight||e.clientHeight||w.innerHeight;
    VK.callMethod('resizeWindow', width, parseInt(wheight + 60));
  }

  var clickTimeout = null;
  jQuery(document).ready(function(){ autoResizeAppFrame(1000); setTimeout(function(){ autoResizeAppFrame(1000); }, 2000); });
  jQuery(document).on('click', function() { autoResizeAppFrame(1000); if (null === clickTimeout) { clickTimeout = setTimeout(function(){ clickTimeout = null; autoResizeAppFrame(1000); }, 2000); } });
})();
/* ]]> */
</script>
{/literal}