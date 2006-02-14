/*
    Javascript for Bubble Tooltips by Alessandro Fulciniti
    http://pro.html.it - http://web-graphics.com 
*/

bx_tooltip = function() {
}

bx_tooltip.init = function(id) {
	var links,i,h;
	if(!document.getElementById || !document.getElementsByTagName) return;
	h=document.createElement("span");
	h.id="btc";
	h.setAttribute("id","btc");
	h.style.position="absolute";
	document.getElementsByTagName("body")[0].appendChild(h);

    /*
	if(id==null) {
		links=document.getElementsByTagName("span");
	} else {
		links=document.getElementById(id).getElementsByTagName("span");
	}

	for(i=0;i<links.length;i++){
		if (links[i].className== 'pic') {
			Prepare(links[i]);
		}
	}
    */
}

bx_tooltip.prepare = function(el, imgSrc) {
    var tooltip, t, b, s, l;

	t = el.getAttribute("title");

	if (t==null || t.length==0) 
		t = "link:";

	el.removeAttribute("title");
	
    var src = '';
    if(typeof imgSrc != 'undefined') {
        src = imgSrc;
    } else {
        src = t.replace(/.*src: (.*)$/, "$1");
    }
    
    /*if(src.indexOf('http://') == -1)
        src = 'http://' + src;
    */
	tooltip = bx_tooltip.createEl("span", "imageTooltip");
	
	img = document.createElement("img");
	img.setAttribute("src",src);
	
	tooltip.appendChild(img);
	
	//bx_tooltip.setOpacity(tooltip);
	el.tooltip = tooltip;
	el.onmouseover = bx_tooltip.showTooltip;
	el.onmouseout = bx_tooltip.hideTooltip;
	el.onmousemove = bx_tooltip.locate;
}

bx_tooltip.remove = function(element) {
    element.onmouseover = null;
	element.onmouseout = null;
	element.onmousemove = null;
}

bx_tooltip.showTooltip = function(e){
	document.getElementById("btc").appendChild(this.tooltip);
	bx_tooltip.locate(e);
}

bx_tooltip.hideTooltip = function(e){
	var d = document.getElementById("btc");
	if(d.childNodes.length>0) d.removeChild(d.firstChild);
}

bx_tooltip.setOpacity = function(el){
	el.style.filter = "alpha(opacity:95)";
	el.style.KHTMLOpacity = "0.95";
	el.style.MozOpacity = "0.95";
	el.style.opacity = "0.95";
}

bx_tooltip.createEl = function(t,c){
	var x = document.createElement(t);
	x.className = c;
	x.style.display = "block";
	return(x);
}



bx_tooltip.locate = function(e){
	var posx=0,posy=0;
	if(e==null) e=window.event;
	if(e.pageX || e.pageY){
		posx=e.pageX; posy=e.pageY;
	}
	else if(e.clientX || e.clientY){
		if(document.documentElement.scrollTop){
			posx=e.clientX+document.documentElement.scrollLeft;
			posy=e.clientY+document.documentElement.scrollTop;
		}
		else{
			posx=e.clientX+document.body.scrollLeft;
			posy=e.clientY+document.body.scrollTop;
		}
	}
	document.getElementById("btc").style.top=(posy+10)+"px";
	document.getElementById("btc").style.left=(posx-20)+"px";
}

