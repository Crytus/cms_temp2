// 詳細画面の表示
var overlayItem = '';
function showDetail(item)
{
	overlayItem = item;
	// 詳細画面の表示位置などを設定（画面中央）
	var overlap = document.getElementById(overlayItem);
	overlap.style.position = 'absolute';
	overlap.style.display = "block";
	var height = parseInt(overlap.clientHeight);
	var width = parseInt(overlap.clientWidth);
	//
	var PageSize = getPageSize();
	var PageScroll = getPageScroll();
	//
	var overlapTop = PageScroll[1] + ((PageSize[3] - height) / 2);
	var overlapLeft = ((PageSize[0] - width) / 2);
	//
	overlap.style.top = (overlapTop < 0) ? "0px" : overlapTop + "px";
	overlap.style.left = (overlapLeft < 0) ? "0px" : overlapLeft + "px";
	overlap.style.zIndex = '100';	// 灰色オーバーレイより大きな値
	overlap.style.display = "block";
	// 影を作成
	var shadow = document.getElementById('overlayShadow');
	if (shadow) {
		shadow.style.position = 'absolute';
		shadow.style.height = height + 'px';
		shadow.style.width = width + 'px';
		shadow.style.top = (overlapTop < 0) ? "0px" : (overlapTop + 8) + "px";
		shadow.style.left = (overlapLeft < 0) ? "0px" : (overlapLeft + 8) + "px";
		shadow.style.display = "block";
	}
	// 背後をマスクする
	var objOverlay = document.getElementById('overlay');
	if (objOverlay) {
		objOverlay.style.height = (PageSize[1] + 'px');
		objOverlay.style.width = (PageSize[0] + 'px');
		objOverlay.style.display = 'block';
		//
/*
		selects = document.getElementsByTagName("select");
		for (i = 0; i != selects.length; i++) {
			selects[i].style.visibility = "hidden";
		}
*/
	}
}
// 詳細画面を非表示
function hideDetail()
{
	document.getElementById(overlayItem).style.display = "none";
	var objShadow = document.getElementById('overlayShadow');
	if (objShadow) {
		objShadow.style.display = "none";
	}
	var objOverlay = document.getElementById('overlay');
	if (objOverlay) {
		objOverlay.style.display = 'none';
	}
	//
/*
	selects = document.getElementsByTagName("select");
    for (i = 0; i != selects.length; i++) {
		selects[i].style.visibility = "visible";
	}
*/
}
// 現在のページサイズを取得する
function getPageSize()
{
	var xScroll, yScroll;

	if (window.innerHeight && window.scrollMaxY) {	
		xScroll = document.body.scrollWidth;
		yScroll = window.innerHeight + window.scrollMaxY;
	} else if (document.body.scrollHeight > document.body.offsetHeight){ // all but Explorer Mac
		xScroll = document.body.scrollWidth;
		yScroll = document.body.scrollHeight;
	} else { // Explorer Mac...would also work in Explorer 6 Strict, Mozilla and Safari
		xScroll = document.body.offsetWidth;
		yScroll = document.body.offsetHeight;
	}

	var windowWidth, windowHeight;
	if (self.innerHeight) {	// all except Explorer
		windowWidth = self.innerWidth;
		windowHeight = self.innerHeight;
	} else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode
		windowWidth = document.documentElement.clientWidth;
		windowHeight = document.documentElement.clientHeight;
	} else if (document.body) { // other Explorers
		windowWidth = document.body.clientWidth;
		windowHeight = document.body.clientHeight;
	}

	// for small pages with total height less then height of the viewport
	if (yScroll < windowHeight){
		pageHeight = windowHeight;
	} else { 
		pageHeight = yScroll;
	}

	// for small pages with total width less then width of the viewport
	if (xScroll < windowWidth) {
		pageWidth = windowWidth;
	} else {
		pageWidth = xScroll;
	}

	arrayPageSize = new Array(pageWidth, pageHeight, windowWidth, windowHeight) 
	return arrayPageSize;
}
function getPageScroll(){

	var yScroll;

	if (self.pageYOffset) {
		yScroll = self.pageYOffset;
	} else if (document.documentElement && document.documentElement.scrollTop){	 // Explorer 6 Strict
		yScroll = document.documentElement.scrollTop;
	} else if (document.body) {// all other Explorers
		yScroll = document.body.scrollTop;
	}

	arrayPageScroll = new Array('',yScroll) 
	return arrayPageScroll;
}

// 初期化（背景マスク用と影用のDIV要素作成、別途CSS指定が必要）
function initOverlap()
{
	if (!document.getElementsByTagName)　{
		// 非対応
		return;
	}
	var objBody = document.getElementsByTagName("body").item(0);
	// オーバーレイを作成
	var objOverlay = document.createElement("div");
	objOverlay.setAttribute('id', 'overlay');
	objOverlay.onclick = function () {
		hideDetail();
		return false;
	}
	objOverlay.style.display = 'none';
	objOverlay.style.position = 'absolute';
	objOverlay.style.top = '0';
	objOverlay.style.left = '0';
	objOverlay.style.zIndex = '90';
 	objOverlay.style.width = '100%';
	objBody.insertBefore(objOverlay, objBody.firstChild);
	// 影部分を作成
	var objShadow = document.createElement("div");
	objShadow.setAttribute('id','overlayShadow');
	objShadow.style.display = 'none';
	objShadow.style.position = 'absolute';
	objShadow.style.top = '0';
	objShadow.style.left = '0';
	objShadow.style.zIndex = '95';
 	objShadow.style.width = '100%';
	objBody.insertBefore(objShadow, objBody.firstChild);
}
// OnLoadイベント追加
function addLoadEvent(func)
{
	if (typeof window.onload != 'function') {
    	window.onload = func;
	} else {
		var oldonload = window.onload;
		window.onload = function() {
			oldonload();
			func();
		}
	}
}

// 初期化処理をOnLoadに追加
addLoadEvent(initOverlap);

