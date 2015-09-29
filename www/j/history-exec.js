var documentStack = { };
$(function(){
	var location = window.history.location || window.location;

	$(document).on('click', 'a.ajaxlink', function() {
		history.pushState(null, null, this.href);
		return false;
	});		

	$(window).on('popstate', function(e) {

		console.log(documentStack);
		if( typeof( documentStack[location.href] ) != 'undefined' ){
			$.map(documentStack[location.href], function(v,k){
				$(k).empty().append(v);
			});
		}

		if (typeof(zoombox_clickable) == 'function') zoombox_clickable();
		updateLinks();

		return false;
	});		
});
