{if Kohana::ENVIRONMENT eq Kohana::PRODUCTION}
{if $code eq 'banner_bg'}
{literal}
<script type="javascript">
(function(AdFox_BGB_s, window, document){

	var AdFox_BGB_i = document.createElement('IMG'), AdFox_BGB_b = document.body;
	AdFox_BGB_i.style.position = 'absolute'; AdFox_BGB_i.style.width = AdFox_BGB_i.style.height = '0px';
	AdFox_BGB_i.onload = AdFox_BGB_i.onerror = function(){AdFox_BGB_b.removeChild(AdFox_BGB_i); AdFox_BGB_i = AdFox_BGB_b = null};
	AdFox_BGB_i.src = AdFox_BGB_s;
	AdFox_BGB_b.insertBefore(AdFox_BGB_i, AdFox_BGB_b.firstChild);

})('http://www.eatmart.ru', parent, parent.document);

setTimeout(function(){ document.close();}, 10);

(function(window, document){

	/**ADFOX parameters**/
	var _randomizer = '123456', //do not remove;
			_backgroundSettings =
			{
				backgroundAttachment: 'fixed',
				backgroundColor: '#fff',
				backgroundPosition: '50% 0%',
				backgroundPositionX: '50%',
				backgroundPositionY: '0%',
				backgroundImage: '%user2%',
				backgroundRepeat: 'repeat-y'
			},

			_goURL = 'http://www.eatmart.ru',
			_reference = '%reference%',
			_websiteShift = '%user6%',
			_resetTimer = '%user7%',
			_backgroundElementId = '%user8%', //Body will be used by default;
			_userExceptionsClickable = '%user9%',
			_userExceptionsNonClickable = '%user10%',
			_backgroundElement = null,
			_contentShifterId = 'contentShifter',
			CLICKABLE_ID = 'adfoxClickable';

	/**ARRAY utils**/
	/**
	 * @function indexOf
	 * @description Checks whether element is present in array and returns it's index or -1;
	 * @param {Array} array
	 * @param {*} element
	 * @param @optional  {Number} from
	 */
	function indexOf(array, element /*, from*/)
	{
		if(array.indexOf) {

			return array.indexOf(element);

		} else {
			var index = -1,
					len = array.length,
					from = Number(arguments[1]) || 0;

			from = (from < 0) ? Math.ceil(from): Math.floor(from);
			if (from < 0) { from += len;}
			for (; from < len; from ++)
			{
				if (from in array && array[from] === element)
				{
					index = from;
					break;
				}
			}
			return index;
		}
	}

	/**STYLE utils**/
	/**
	 * @function getCss
	 * @description returns computed value for styleProperty;
	 * @param {String} styleProperty (camelCase notation!)
	 * @param {ElementNode} element
	 */
	function getCss(styleProperty, element)
	{
		if (element.currentStyle) {
			return element.currentStyle[styleProperty] || '';
		} else if (window.getComputedStyle) {
			return window.getComputedStyle(element, null)[styleProperty] || '';
		}
	}

	function addClass(className, element)
	{
		if(!hasClass(className, element)) {
			element.className += ' ' + className;
		}
	}

	function removeClass(className, element)
	{
		var re = new RegExp('(\\s|^)('+className+')(\\s|$)', 'g');
		element.className = element.className.replace(re, function(x, y, z, t){ return (y && t)?' ':'';});
	}

	function hasClass(className, element)
	{
		var re = new RegExp('(\\s|^)('+className+')(\\s|$)', 'g');
		return (element.className && element.className.match(re));
	}

	function setDefaultCursor(element)
	{
//In IE and Opera it seems 'auto' works like 'inherit';
		if(navigator.userAgent.indexOf('MSIE') != -1 || navigator.userAgent.indexOf('Opera') != -1) {
			element.style.cursor = 'default';
		} else {
			element.style.cursor = 'auto';
		}
	}

	/**EVENT utils**/
	/**
	 * @function addEvent, removeEvent
	 * @description add/remove event listeners - crossbrowser;
	 * @param {ElementNode} elem
	 * @param {String} eventType
	 * @param {Function} eventHandler
	 */
	function addEvent(elem, eventType, eventHandler)
	{
		if(elem.addEventListener) {
			elem.addEventListener(eventType, eventHandler, false);
		} else if(elem.attachEvent) {
			elem.attachEvent('on'+eventType, eventHandler);
		}
	}

	function removeEvent(elem, eventType, eventHandler)
	{
		if(elem.removeEventListener) {
			elem.removeEventListener(eventType, eventHandler, false);
		} else if(elem.detachEvent) {
			elem.detachEvent('on'+eventType, eventHandler);
		}
	}

	/**ANIMATION utils**/
	/**
	 * @function delta
	 * @description defines the type of animation: linear, easeIn, easeOut - using mathematical expressions;
	 * @param {Number} progress - fraction of time;
	 */

	function delta(progress)
	{
		return progress; //linear
	}

	/**
	 * @function animateChange
	 * @description animates change for a certain element property;
	 * @param {ElementNode} element
	 * @param {String} parameter
	 * @param {String} paramMeasure
	 * @param {Number} startValue
	 * @param {Number} endValue
	 * @param {Number} duration - miliseconds;
	 * @param {Function} onComplete - action to perform after animation stops;
	 */
	function animateChange(element, parameter, paramMeasure, startValue, endValue, duration, onComplete)
	{
		var startTime = new Date().getTime();
		setTimeout(function(){
			var
					timeElapsed = (new Date().getTime()) - startTime,
					progress = timeElapsed / duration,
					result = (endValue - startValue) * delta(progress) + startValue;

			element.style[parameter] = result + paramMeasure;

			if (progress < 1) {
				setTimeout(arguments.callee, 10);
			} else {
				element.style[parameter] = endValue + paramMeasure; //To conpensate for round operations
				onComplete();
			}
		}, 10);
	}

	/**Background methods**/
	/**
	 * @function setBackgroundProperties
	 * @description set background for _backgroundElement;
	 * @param {Number} propertyObject - style properties' object;
	 */
	function setBackgroundProperties(propertyObject)
	{
		_backgroundElement.style.backgroundColor = propertyObject.backgroundColor;
		_backgroundElement.style.backgroundAttachment = propertyObject.backgroundAttachment;
		_backgroundElement.style.backgroundRepeat = propertyObject.backgroundRepeat;
		_backgroundElement.style.backgroundPosition = propertyObject.backgroundPosition;
		_backgroundElement.style.backgroundImage = 'url(\''+propertyObject.backgroundImage +'\')';
	}

	/**
	 * @function getCurrentBackground
	 * @description save current background of _backgroundElement;
	 * @param {String} getOption - 'get', 'save';
	 */
	function getCurrentBackground()
	{
		getCurrentBackground.bgSettings = {
			backgroundAttachment: getCss('backgroundAttachment', _backgroundElement),
			backgroundColor: getCss('backgroundColor', _backgroundElement),
			backgroundRepeat: getCss('backgroundRepeat', _backgroundElement),
			backgroundImage: getCss('backgroundImage', _backgroundElement),
			backgroundPosition: getCss('backgroundPosition', _backgroundElement),
			backgroundPositionX: getCss('backgroundPositionX', _backgroundElement),
			backgroundPositionY: getCss('backgroundPositionY', _backgroundElement),
		}
	}

	/**
	 * @function resetBackgroundProperties
	 * @description set initial saved background for _backgroundElement;
	 */
	function resetBackgroundProperties()
	{
//TODO: test correct IE behaviour for backgroundPosition;
		var propertyObject = getCurrentBackground.bgSettings;
		if(propertyObject)
		{
			setBackgroundProperties({
				backgroundImage : propertyObject.backgroundImage,
				backgroundColor : propertyObject.backgroundColor,
				backgroundAttachment : propertyObject.backgroundAttachment,
				backgroundRepeat : propertyObject.backgroundRepeat,
				backgroundPosition : (propertyObject.backgroundPosition)?
						(propertyObject.backgroundPosition):
						(propertyObject.backgroundPositionX + ' ' + propertyObject.backgroundPositionY)
			});
		}
	}

	/**
	 * @function createBlock
	 * @description create div with id and styles;
	 * @param {String} id
	 * @param {String} cssStyle
	 */
	function createBlock (id, cssStyle)
	{
		var bannerBlock = document.createElement('div');
		bannerBlock.style.cssText = cssStyle;
		bannerBlock.id = id;
		return bannerBlock;
	}

	/**
	 * @function shiftWebsiteDown
	 * @description -
	 */
	function shiftWebsiteDown()
	{
		var shifter = createBlock(_contentShifterId,
				'width:100%;'+
						'height:' +_websiteShift+ 'px;'+
						'clear:both;'+
						'margin:0;');

		_backgroundElement.insertBefore(shifter, _backgroundElement.firstChild);
	}

	/**
	 * @function shiftWebsiteUp
	 * @description - return website content as it were; Animated;
	 */
	function shiftWebsiteUp()
	{
		var shifter = document.getElementById(_contentShifterId);
		animateChange(shifter, 'height', 'px', parseInt(_websiteShift), 0, 600 , function(){
			shifter.parentNode.removeChild(shifter);
		});
	}

	/**
	 * @function goToAdvertiser
	 * @description
	 * @param {MouseEvent} evt
	 */
	function goToAdvertiser(evt)
	{
		var target = evt.target || evt.srcElement;
		if(hasClass(CLICKABLE_ID, target))
		{
			window.open(_reference + '@' + _goURL ,'_blank');
		}
	}

	/**
	 * Checks whether given element id belongs to list of exceptions;
	 * Can be extended for other elements like table, form etc.
	 * @param {String} elementId
	 */
	function isClickableException(elementId)
	{
		return (indexOf(_userExceptionsClickable, elementId) != - 1)
	}

	/**
	 * Checks whether given element id belongs to list of exceptions;
	 * Can be extended for other elements like table, form etc.
	 * @param {String} elementId
	 */
	function isNonClickableException(elementId)
	{
		return (indexOf(_userExceptionsNonClickable, elementId) != - 1)
	}

	/**
	 * Checks if the element is DIV;
	 * Can be extended for other elements like table, form etc.
	 * @param {ElementNode} element
	 */
	function isAppropriateType(element)
	{
		var nodeName = element.nodeName;
		return (nodeName == 'DIV');
	}

	/**
	 * Checks if the element is visible;
	 * @param {ElementNode} element
	 */
	function isVisible(element)
	{
		var height = getCss('height', element),
				width = getCss('width', element),
				display = getCss('display', element),
				visibility = getCss('visibility', element);

		return !((height == '0' || height == '0px' || height == '0%') ||
				(width == '0' || width == '0px' || width == '0%') ||
				(height == '1px' && width == '1px') ||
				(display == 'none') ||
				(visibility == 'hidden'));
	}

	/**
	 * Checks element for transparency;
	 * @param {ElementNode} element
	 */
	function isTransparent(element)
	{
		var color = getCss('backgroundColor', element),
				image = getCss('backgroundImage', element);

		return (color == 'transparent' || color == 'rgba(0, 0, 0, 0)' || color == '' ) && (image == 'none' || image == '');
	}

	/**
	 * Analyses DOM tree to find transparent blocks overlapping background;
	 * @param {DomElement} element - current tree root;
	 * @static @param {array} blockArray - an array of matching blocks;
	 * @static @param {array} exceptions - an array of matching blocks specified by user input;
	 */
	function findClickableElements(element)
	{
		var elementChildren = null,
				currentChild = null,
				ELEMENT_NODE = 1;

		if(element == _backgroundElement) {
			element.style.cursor = 'pointer'; //display as clickable; inhereted;
		}

		addClass(CLICKABLE_ID, element); //mark as clickable;
		elementChildren = element.children;
		for(var i = 0, len = elementChildren.length; i < len; i ++) {
			currentChild = elementChildren[i];
			if (currentChild.nodeType == ELEMENT_NODE && isAppropriateType(currentChild) || isClickableException(currentChild.id)) {
				if (isVisible(currentChild) && (isTransparent(currentChild) || isClickableException(currentChild.id)) && !isNonClickableException(currentChild.id)) {
					findClickableElements(currentChild);
				} else {
					setDefaultCursor(currentChild);
				}
			}
		}
	}

//execute
	var getBackgroundInterval = setInterval(getBackgroundElement, 100);
	function getBackgroundElement()
	{
		_backgroundElement = (_backgroundElementId)?
				document.getElementById(_backgroundElementId):
				document.getElementsByTagName('BODY')[0];

		if(_backgroundElement)
		{
			clearInterval(getBackgroundInterval);
			initBackground();
		}
	}

	function initBackground()
	{
		getCurrentBackground();
		if(_websiteShift != '0' && _websiteShift != '' )
		{
			shiftWebsiteDown();
		}
		setBackgroundProperties(_backgroundSettings);

		if(document.readyState == 'complete') {
			startBackgroundScript();
		} else {
			addEvent(window, 'load', startBackgroundScript);
		}
	}

	function startBackgroundScript(evt)
	{
		if(_goURL) //may be non-clickable;
		{
			_userExceptionsClickable = (_userExceptionsClickable != '') ? _userExceptionsClickable.split('|'):[];
			_userExceptionsNonClickable = (_userExceptionsNonClickable != '') ? _userExceptionsNonClickable.split('|'):[];
			findClickableElements(_backgroundElement);
			addEvent(_backgroundElement, 'click', goToAdvertiser);
		}

//if timer is not 0, return website to initial state;
		if( _resetTimer != '0' ) {
			setTimeout(function() {
				if(_websiteShift != '0' && _websiteShift != '' ) {
					shiftWebsiteUp();
				}
				resetBackgroundProperties();
				if(_goURL) {
					setDefaultCursor(_backgroundElement);
					removeEvent(_backgroundElement, 'click', goToAdvertiser);
				}
			},  _resetTimer*1000);
		}
	}
})(parent, parent.document);
{/literal}
</script>

{else}


<div class="{$code}">

{if $code eq 'banner_950X60_1'}

    <!--Тип баннера: №1-->
    <script type="text/javascript">
        <!--
        if ('undefined' !== typeof(AdFox_Write)) var pr1 = AdFox_Write();
        if ('undefined' !== typeof(AdFox_Scroll)) AdFox_Scroll(pr1,'http://ads.adfox.ru/213263/prepareCode?pp=g&amp;ps=bkmw&amp;p2=esyl&amp;pct=a&amp;plp=a&amp;pli=a&amp;pop=a&amp;pr=' + prr + pr1);
        // -->
    </script>

{elseif $code eq 'banner_360X256_2'}

    <!--Тип баннера: №2-->
    <script type="text/javascript">
        <!--
        if ('undefined' !== typeof(AdFox_Write)) var pr1 = AdFox_Write();
        if ('undefined' !== typeof(AdFox_Scroll)) AdFox_Scroll(pr1,'http://ads.adfox.ru/213263/prepareCode?pp=h&amp;ps=bkmw&amp;p2=esyo&amp;pct=a&amp;plp=a&amp;pli=a&amp;pop=a&amp;pr=' + prr + pr1);
        // -->
    </script>

{elseif $code eq 'banner_360X256_3'}

    <!--Тип баннера: №3-->
    <script type="text/javascript">
        <!--
        if ('undefined' !== typeof(AdFox_Write)) var pr1 = AdFox_Write();
        if ('undefined' !== typeof(AdFox_Scroll)) AdFox_Scroll(pr1,'http://ads.adfox.ru/213263/prepareCode?pp=h&amp;ps=bkmw&amp;p2=esyp&amp;pct=a&amp;plp=a&amp;pli=a&amp;pop=a&amp;pr=' + prr + pr1);
        // -->
    </script>

{elseif $code eq 'banner_300X210_4'}

    <!--Тип баннера: №4-->
    <script type="text/javascript">
        <!--
        if ('undefined' !== typeof(AdFox_Write)) var pr1 = AdFox_Write();
        if ('undefined' !== typeof(AdFox_Scroll)) AdFox_Scroll(pr1,'http://ads.adfox.ru/213263/prepareCode?pp=i&amp;ps=bkmw&amp;p2=esyq&amp;pct=a&amp;plp=a&amp;pli=a&amp;pop=a&amp;pr=' + prr + pr1);
        // -->
    </script>

{elseif $code eq 'banner_300X210_5'}

    <!--Тип баннера: №5-->
    <script type="text/javascript">
        <!--
        if ('undefined' !== typeof(AdFox_Write)) var pr1 = AdFox_Write();
        if ('undefined' !== typeof(AdFox_Scroll)) AdFox_Scroll(pr1,'http://ads.adfox.ru/213263/prepareCode?pp=i&amp;ps=bkmw&amp;p2=esyr&amp;pct=a&amp;plp=a&amp;pli=a&amp;pop=a&amp;pr=' + prr + pr1);
        // -->
    </script>

{elseif $code eq 'banner_300X210_6'}

    <!--Тип баннера: №6-->
    <script type="text/javascript">
        <!--
        if ('undefined' !== typeof(AdFox_Write)) var pr1 = AdFox_Write();
        if ('undefined' !== typeof(AdFox_Scroll)) AdFox_Scroll(pr1,'http://ads.adfox.ru/213263/prepareCode?pp=i&amp;ps=bkmw&amp;p2=esys&amp;pct=a&amp;plp=a&amp;pli=a&amp;pop=a&amp;pr=' + prr + pr1);
        // -->
    </script>

{elseif $code eq 'banner_top'}

    <!--Тип баннера: №7-->
    <script type="text/javascript">
        <!--
        if ('undefined' !== typeof(AdFox_Write)) var pr1 = AdFox_Write();
        if ('undefined' !== typeof(AdFox_Scroll)) AdFox_Scroll(pr1,'http://ads.adfox.ru/213263/prepareCode?pp=g&amp;ps=bkmw&amp;p2=eueb&amp;pct=a&amp;plp=a&amp;pli=a&amp;pop=a&amp;pr=' + prr + pr1);
        // -->
    </script>

{elseif $code eq 'banner_eatmart'}

    <!--Тип баннера: №8-->
    <script type="text/javascript">
        <!--
        if ('undefined' !== typeof(AdFox_Write)) var pr1 = AdFox_Write();
        if ('undefined' !== typeof(AdFox_Scroll)) AdFox_Scroll(pr1,'http://ads.adfox.ru/213263/prepareCode?pp=g&amp;ps=bkmw&amp;p2=eued&amp;pct=a&amp;plp=a&amp;pli=a&amp;pop=a&amp;pr=' + prr + pr1);
        // -->
    </script>


{elseif $code eq 'eatmart2'}

    <!--Тип баннера: №2 Eatmart-->
    <script type="text/javascript">
        <!--
        if ('undefined' !== typeof(AdFox_Write)) var pr1 = AdFox_Write();
        if ('undefined' !== typeof(AdFox_Scroll)) AdFox_Scroll(pr1,'http://ads.adfox.ru/213263/prepareCode?pp=i&amp;ps=bkmw&amp;p2=euby&amp;pct=a&amp;plp=a&amp;pli=a&amp;pop=a&amp;pr=' + prr + pr1);
        // -->
    </script>

{elseif $code eq 'eatmart3'}

    <!--Тип баннера: №3 Eatmart-->
    <script type="text/javascript">
        <!--
        if ('undefined' !== typeof(AdFox_Write)) var pr1 = AdFox_Write();
        if ('undefined' !== typeof(AdFox_Scroll)) AdFox_Scroll(pr1,'http://ads.adfox.ru/213263/prepareCode?pp=i&amp;ps=bkmw&amp;p2=eubz&amp;pct=a&amp;plp=a&amp;pli=a&amp;pop=a&amp;pr=' + prr + pr1);
        // -->
    </script>

{elseif $code eq 'eatmart4'}

    <!--Тип баннера: №4 Eatmart-->
    <script type="text/javascript">
        <!--
        if ('undefined' !== typeof(AdFox_Write)) var pr1 = AdFox_Write();
        if ('undefined' !== typeof(AdFox_Scroll)) AdFox_Scroll(pr1,'http://ads.adfox.ru/213263/prepareCode?pp=i&amp;ps=bkmw&amp;p2=euca&amp;pct=a&amp;plp=a&amp;pli=a&amp;pop=a&amp;pr=' + prr + pr1);
        // -->
    </script>

{/if}
</div>

{/if}
{/if}