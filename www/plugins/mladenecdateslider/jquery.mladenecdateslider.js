(function( $ ){

  $.fn.mladenecdateslider = function( method ) {  

	var methods = {
		init : function( options ) { 
			
			var settings = $.extend( {
				dates: null,
				onClick: function(){},
				target: null,
				labels: {
					0: "Вс",
					1: "Пн",
					2: "Вт",
					3: "Ср",
					4: "Чт",
					5: "Пт",
					6: "Сб"
				}
			}, options);

			return this.each(function() {        

				var o = $(this);
				
				o.data('settings', settings);

				if( o.hasClass('mladenecdateslider') || ! settings.target ){
					return;
				}

				$(settings.target).hide();
				
				o.empty().addClass('mladenecdateslider');
				// var prev = $('<div class="mladenecdateslider-before"><div></div></div>').prependTo(o);
				// var next = $('<div class="mladenecdateslider-after"><div></div></div>').appendTo(o);

				var shiftLine = function(act){
					// TODO
				};

				/* prev.click(function(){
					shiftLine('prev');
				}).css({
					opacity: 0.5,
					cursor: 'default'
				});
				next.click(function(){
					shiftLine('next');
				}).css({
					opacity: 0.5,
					cursor: 'default'
				}); */
				
				if( settings.dates ){
					methods._go(o, settings.dates);
				}
				else{
					/* $(this).css({
						opacity: 0
					}); */
				}
			});
		  },
		_go: function(o, dates){
			
			o.animate({
				opacity: 1
			});
			
			var settings = o.data('settings');
			
			// Начинаем со вчера
			o.find('.mladenecdateslider-body').remove();
			var b = $('<div class="mladenecdateslider-body"></div>').appendTo(o);
			var current = new Date(), today = new Date(+current);

			if( !dates ){
				var dates = [];
				for( var i = 0; i < 7; i++ ){
					dates.push(new Date(current));
					current.setDate(current.getDate() + 1);
				}
			}
			else{
				$.map(dates, function(v, k){
					dates[k] = new Date(v.replace(/(\d+)-(\d+)-(\d+)/, '$2/$3/$1'));
				});
			}

			$.map(dates, function(date, k){
				var d = $('<div class="mladenecdateslider-date"></div>').appendTo(b);
				var label = $('<div class="mladenecdateslider-label">Вт</div>').appendTo(d);

				label.html(settings.labels[date.getDay()]);
				d.append("<span>"+date.getDate()+"</span>");

				if( k == 0 ){
					d.addClass('current');
					settings.onClick(date);
					$(settings.target).val(date.getFullYear()+'-'+( date.getMonth()+1 )+'-'+date.getDate());
				}
				/* else if( +today > +date ){
					d.addClass('earlier');
					return;
				} */

				d.click(function(){
					
					if( $(this).hasClass('current') ){
					
						return false;
					}
					
					settings.onClick(date);
					$(settings.target).val(date.getFullYear()+'-'+(date.getMonth()+1)+'-'+date.getDate());
					b.find('.mladenecdateslider-date').removeClass('current');
					d.addClass('current');
				});
			});
		},
		update : function( dates ) {
			return this.each(function(){
				methods._go($(this), dates);
			});
		}
	};
	
	// логика вызова метода
	if (methods[method]) {
		return methods[ method ].apply(this, Array.prototype.slice.call(arguments, 1));
	} else if (typeof method === 'object' || !method) {
		return methods.init.apply(this, arguments);
	} else {
		$.error('Метод с именем ' + method + ' не существует');
	}
	
  };
})( jQuery );
