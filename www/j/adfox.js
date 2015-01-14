function AdFox_SetLayerVis(spritename, state) {
    document.getElementById(spritename).style.visibility = state;
}
function AdFox_Open(AF_id) {
    AdFox_SetLayerVis('AdFox_DivBaseFlash_' + AF_id, "hidden");
    AdFox_SetLayerVis('AdFox_DivOverFlash_' + AF_id, "visible");
}
function AdFox_Close(AF_id) {
    AdFox_SetLayerVis('AdFox_DivOverFlash_' + AF_id, "hidden");
    AdFox_SetLayerVis('AdFox_DivBaseFlash_' + AF_id, "visible");
}
function AdFox_getCodeScript(AF_n, AF_id, AF_src) {
    var AF_doc;
    if (AF_n < 10) {
        try {
            if (document.all && !window.opera) {
                AF_doc = window.frames['AdFox_iframe_' + AF_id].document;
            } else if (document.getElementById) {
                AF_doc = document.getElementById('AdFox_iframe_' + AF_id).contentDocument;
            }
        } catch (e) {
        }
        if (AF_doc) {
            AF_doc.write('<scr' + 'ipt type="text/javascript" src="' + AF_src + '"><\/scr' + 'ipt>');
        } else {
            setTimeout('AdFox_getCodeScript(' + (++AF_n) + ',' + AF_id + ',"' + AF_src + '");', 100);
        }
    }
}
function adfoxSdvigContent(banID, flashWidth, flashHeight) {
    var obj = document.getElementById('adfoxBanner' + banID).style;
    if (flashWidth == '100%') obj.width = flashWidth;
    else obj.width = flashWidth + "px";
    if (flashHeight == '100%') obj.height = flashHeight;
    else obj.height = flashHeight + "px";
}
function adfoxVisibilityFlash(banName, flashWidth, flashHeight) {
    var obj = document.getElementById(banName).style;
    if (flashWidth == '100%') obj.width = flashWidth;
    else obj.width = flashWidth + "px";
    if (flashHeight == '100%') obj.height = flashHeight;
    else obj.height = flashHeight + "px";
}
function adfoxStart(banID, FirShowFlNum, constVisFlashFir, sdvigContent, flash1Width, flash1Height, flash2Width, flash2Height) {
    if (FirShowFlNum == 1) adfoxVisibilityFlash('adfoxFlash1' + banID, flash1Width, flash1Height);
    else if (FirShowFlNum == 2) {
        adfoxVisibilityFlash('adfoxFlash2' + banID, flash2Width, flash2Height);
        if (constVisFlashFir == 'yes') adfoxVisibilityFlash('adfoxFlash1' + banID, flash1Width, flash1Height);
        if (sdvigContent == 'yes') adfoxSdvigContent(banID, flash2Width, flash2Height);
        else adfoxSdvigContent(banID, flash1Width, flash1Height);
    }
}
function adfoxOpen(banID, constVisFlashFir, sdvigContent, flash2Width, flash2Height) {
    var aEventOpenClose = new Image();
    var obj = document.getElementById("aEventOpen" + banID);
    if (obj) aEventOpenClose.src = obj.title + '&rand=' + Math.random() * 1000000 + '&prb=' + Math.random() * 1000000;
    adfoxVisibilityFlash('adfoxFlash2' + banID, flash2Width, flash2Height);
    if (constVisFlashFir != 'yes') adfoxVisibilityFlash('adfoxFlash1' + banID, 1, 1);
    if (sdvigContent == 'yes') adfoxSdvigContent(banID, flash2Width, flash2Height);
}
function adfoxClose(banID, constVisFlashFir, sdvigContent, flash1Width, flash1Height) {
    var aEventOpenClose = new Image();
    var obj = document.getElementById("aEventClose" + banID);
    if (obj) aEventOpenClose.src = obj.title + '&rand=' + Math.random() * 1000000 + '&prb=' + Math.random() * 1000000;
    adfoxVisibilityFlash('adfoxFlash2' + banID, 1, 1);
    if (constVisFlashFir != 'yes') adfoxVisibilityFlash('adfoxFlash1' + banID, flash1Width, flash1Height);
    if (sdvigContent == 'yes') adfoxSdvigContent(banID, flash1Width, flash1Height);
}
function AdFox_getWindowSize() {
    var winWidth, winHeight;
    if (typeof( window.innerWidth ) == 'number') {
        //Non-IE
        winWidth = window.innerWidth;
        winHeight = window.innerHeight;
    } else if (document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight )) {
        //IE 6+ in 'standards compliant mode'
        winWidth = document.documentElement.clientWidth;
        winHeight = document.documentElement.clientHeight;
    } else if (document.body && ( document.body.clientWidth || document.body.clientHeight )) {
        //IE 4 compatible
        winWidth = document.body.clientWidth;
        winHeight = document.body.clientHeight;
    }
    return {"width": winWidth, "height": winHeight};
}//END function AdFox_getWindowSize

function AdFox_getElementPosition(elemId) {
    var elem;

    if (document.getElementById) {
        elem = document.getElementById(elemId);
    }
    else if (document.layers) {
        elem = document.elemId;
    }
    else if (document.all) {
        elem = document.all.elemId;
    }
    var w = elem.offsetWidth;
    var h = elem.offsetHeight;
    var l = 0;
    var t = 0;

    while (elem) {
        l += elem.offsetLeft;
        t += elem.offsetTop;
        elem = elem.offsetParent;
    }

    return {"left": l, "top": t, "width": w, "height": h};
} //END function AdFox_getElementPosition

function AdFox_getBodyScrollTop() {
    return self.pageYOffset || (document.documentElement && document.documentElement.scrollTop) || (document.body && document.body.scrollTop);
} //END function AdFox_getBodyScrollTop

function AdFox_getBodyScrollLeft() {
    return self.pageXOffset || (document.documentElement && document.documentElement.scrollLeft) || (document.body && document.body.scrollLeft);
}//END function AdFox_getBodyScrollLeft

function AdFox_Scroll(elemId, elemSrc) {
    var winPos = AdFox_getWindowSize();
    var winWidth = winPos.width;
    var winHeight = winPos.height;
    var scrollY = AdFox_getBodyScrollTop();
    var scrollX = AdFox_getBodyScrollLeft();
    var divId = 'AdFox_banner_' + elemId;
    var ltwhPos = AdFox_getElementPosition(divId);
    var lPos = ltwhPos.left;
    var tPos = ltwhPos.top;

    if (scrollY + winHeight + 5 >= tPos && scrollX + winWidth + 5 >= lPos) {
        AdFox_getCodeScript(1, elemId, elemSrc);
    } else {
        setTimeout('AdFox_Scroll(' + elemId + ',"' + elemSrc + '");', 100);
    }
}//End function AdFox_Scroll

function AdFox_Write() {
    var pr1 = Math.floor(Math.random() * 1000000);

    document.write('<div id="AdFox_banner_' + pr1 + '"><\/div>');
    document.write('<div style="visibility:hidden; position:absolute;"><iframe id="AdFox_iframe_' + pr1 + '" width=1 height=1 marginwidth=0 marginheight=0 scrolling=no frameborder=0><\/iframe><\/div>');
    return pr1;
}

// adfox init
var pr = Math.floor(Math.random() * 1000000);
if (typeof(document.referrer) != 'undefined') {
    if (typeof(afReferrer) == 'undefined') {
        afReferrer = escape(document.referrer);
    }
} else {
    afReferrer = '';
}
var addate = new Date();
var dl = escape(document.location);
var prr = pr + '&amp;pt=b&amp;pd=' + addate.getDate() + '&amp;pw=' + addate.getDay() + '&amp;pv=' + addate.getHours() + '&amp;prr=' + afReferrer + '&amp;dl=' + dl + '&amp;pr1=';

