var isIE = false;
// on !IE we only have to initialize it once
if (window.XMLHttpRequest) {
	liveSearchReq = new XMLHttpRequest();
}


function liveSaveSetStatus (text) {
	var  res = document.getElementById("LSResult");
	res.style.display = "inline";
	res.firstChild.nodeValue = text;
}

function liveSave(form, focusField, mimetype) {
	if (mimetype == "text/html" || mimetype == "text/xml") {
		liveSaveSetStatus("Checking Document ...");
	
		if (!xmlcheck(form)) {
			liveSaveSetStatus("Document not wellformed. Not saved.");
			var  res = document.getElementById("LSResult");
			window.setTimeout(function() {res.style.display = 'none';}, 3000);

			return false;
		}
	}
	if (window.XMLHttpRequest) {
	// branch for IE/Windows ActiveX version
	} else if (window.ActiveXObject) {
		liveSearchReq = new ActiveXObject("Microsoft.XMLHTTP");
	}
	
	if (focusField) {
		try {
			focusField.focus();
		} catch (e) {
		}
	}
	
	var postRequest = "";
	for (var i = 0; i < form.elements.length; i++) {
		postRequest += form.elements[i].name;
		postRequest += "=" + encodeURIComponent(form.elements[i].value) +"&";
	}
	postRequest += "liveSave=1";
	
	liveSaveSetStatus("Saving Document ...");
	liveSearchReq.onreadystatechange= liveSaveProcessReqChange;
	liveSearchReq.open("POST", form.action);
	liveSearchReq.setRequestHeader("Content-Type","application/x-www-form-urlencoded");

	liveSearchReq.send(postRequest);
	
	return false;
}

function xmlcheck (form) {
	try {
		
		for (var i = 0; i < form.elements.length; i++) {
			var xml = form.elements[i].value.replace(/&([^;]{4})/g,"&amp;$1");
			xml = xml.replace(/<!DOCTYPE[^>]*>/,"");
			if ( (form.elements[i].type == "textarea" || form.elements[i].type == "text") &&  xml.replace(/\s*/g,"").length > 0 )
			{
				var oDomDoc = Sarissa.getDomDocument();
				oDomDoc.async = false;
				oDomDoc.loadXML(xml);
				if(oDomDoc.parseError != 0) {
					alert(Sarissa.getParseErrorText(oDomDoc));
					return false;
				}
			}
		}
		
		
		return true;
	}
	catch(e)
	{
		return true;
	}
	
}

function liveSaveProcessReqChange() {
	
	if (liveSearchReq.readyState == 4) {
		liveSaveSetStatus("Document saved.");
		var  res = document.getElementById("LSResult");
		window.setTimeout(function() {res.style.display = 'none';}, 3000);
		 
	}
}
