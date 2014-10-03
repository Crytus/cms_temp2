function win_disp(id)
{
	var overlap = document.getElementById(id);
	overlap.style.position = 'absolute';
	overlap.style.display = "block";
	var height = parseInt(overlap.clientHeight);
	var width = parseInt(overlap.clientWidth);

	var PageSize = getPageSize();
	var PageScroll = getPageScroll();

	var overlapTop = PageScroll[1] + ((PageSize[3] - height) / 2);
	var overlapLeft = ((PageSize[0] - width) / 2);
	//
	overlap.style.top = (overlapTop < 0) ? "0px" : overlapTop + "px";
	overlap.style.left = (overlapLeft < 0) ? "0px" : overlapLeft + "px";
	overlap.style.zIndex = '100';	// 灰色オーバーレイより大きな値
	overlap.style.display = "block";
/*
	document.getElementById(id).style.display = 'block';
	var PageSize = getPageSize();
	var x = (PageSize[2] - document.getElementById(id).offsetWidth) / 2;
	var y = parseInt((PageSize[3] - document.getElementById(id).offsetHeight) / 2);// - document.getElementById(id).offsetTop
//	var y = PageSize[1] + parseInt((PageSize[3] - parseInt(document.getElementById(id).clientHeight)) / 2);
//alert(y);
	document.getElementById(id).style.left = x + "px";
	document.getElementById(id).style.top = y + "px";
*/
}
function win_hide(id)
{
	document.getElementById(id).style.display = 'none';
}
function getPageSize()
{
	var xScroll, yScroll;

	if (window.innerHeight && window.scrollMaxY) {
		xScroll = document.body.scrollWidth;
		yScroll = window.innerHeight + window.scrollMaxY;
	} else if (document.body.scrollHeight > document.body.offsetHeight) { // all but Explorer Mac
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
//assert(true, "pageWidth:" + pageWidth + " pageHeight:" +  pageHeight + " windowWidth:" +  windowWidth + " windowHeight:" +  windowHeight);
//assert(true, "scrollLeft:" + document.body.scrollLeft + " scrollTop:" +  document.body.scrollTop);

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
function dump(obj) {
	str = "";
	for (i in obj) {
		str += i + ":" + obj[i] + "<br>\r\n";
	}
	return str;
}
