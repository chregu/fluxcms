
function setTable(txt)
{
	document.getElementById( 'insCode_area' ).value = txt ;
}

function getTable()
{
	return document.getElementById( 'insCode_area' ).value ;
}


xpOpenWindowRef = false;

function xpOpenWindow(url, w, h, l, t) {

	prms = 'width=' + (w | 320);
	prms += ',height=' + (h | 320);
	prms += ',left=' + (l | 20);
	prms += ',top=' + (t | 20);
	
	prms += ',toolbar=0';
	prms += ',location=0';
	prms += ',directories=0';
	prms += ',menuBar=0';
	prms += ',scrollbars=1';
	prms += ',resizable=0';
	
	xpOpenWindowRef = window.open(url, 'xpOpenWindowRef', prms);
	
	if (xpOpenWindowRef) {
		xpOpenWindowRef.focus();
	}
	
}
