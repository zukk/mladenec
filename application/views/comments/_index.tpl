<script>
$(document).ready(function() {
    var comments_container = $('#comments-container');

    commentLink = function(el){
        var url = el.href, p = url.indexOf( '#'), hash = '', base = url;

        if( p == -1 ) {
            hash = '/list';
        } else{
            base = url.substring(0,p)+'/';
            hash = url.substring(p+1);
			
			if( location.href.indexOf('#') == -1 )
				location.href += '#'+hash;
        }
        url = base + hash;
        commentLoad(url);
        return false;
    }

    commentLoad = function(url){
        comments_container.empty().append('<i class="load"></i>');
        $('html, body').animate({ scrollTop: comments_container.offset().top - 20}, 'fast');

        $(comments_container).load(url, function(){
            updateLinks();
        });
    }
	$(window).on('hashchange', function() {
		commentLink(location);
	});
    commentLink(location);
});
</script>

<div id="comments-container"><i class="load"></i></div>