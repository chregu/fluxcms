function xmlcheck () {
	try {
		
		var masterlen = document.forms.Master.length;
		var formM = document.forms.Master;
		var xml = "<?xml version='1.0' ?>\n<root>";
		for (var i = 0; i < masterlen; i++)
		{
			var value = formM[i].value.replace(/&([^;]{4})/g,"&amp;$1");
			if ( formM[i].type == "textarea" || formM[i].type == "text")
			{
				xml += "\n<"+formM[i].name+">\n" +  value + "\n</" + formM[i].name + ">";
				
			}
		}
		xml += "\n</root>";
		var oDomDoc = Sarissa.getDomDocument();
		oDomDoc.async = false;
		oDomDoc.loadXML(xml);
		if(oDomDoc.parseError != 0) {
			alert(Sarissa.getParseErrorText(oDomDoc));
			return false;
		}
		return true;
	}
	catch(e)
	{
		return true;
	}
	
}

function ShowHideFromTill (id ) {
	var value = document.forms.Master[id].value;
	var fieldsid = id + "fields";
	
	if (value == 2)  {
		document.getElementById(fieldsid).style.display = 'block';
	}
	else {
		document.getElementById(fieldsid).style.display = 'none';
	}
}

function bx_toggleSource(id, toSource) {
	
	var textareaId = id;
	var contentEditableId = "mozile_"  + id;
	
	var ta = document.getElementById(textareaId);
	var editableArea = document.getElementById(contentEditableId);
	
	if (ta.style.display == "block" && !toSource ) {
		editableArea.innerHTML = ta.value;
		ta.style.display= "none";
		editableArea.style.display = "block";
		document.getElementById('mozile_source_'+id).className = "buttonStyle";
			document.getElementById('mozile_wysiwyg_'+id).className = "buttonStyleActive";
	} else {
		var dataToSaveRange = document.createRange();
		dataToSaveRange.selectNodeContents(editableArea);
		var dataToSave = dataToSaveRange.cloneContents();
			
		contentToSave = documentSaveXML(dataToSave);
		//ugly code to make tags lowercase...
		// if anyone has a better idea.. go ahead
		var Ergebnis = contentToSave.match(/<\/*[A-Z]+/g);
		if(Ergebnis) {
			for(i=0;i<Ergebnis.length;++i) {
				contentToSave = contentToSave.replace(eval("/"+Ergebnis[i].replace(/\//g,"\\\/")+"/"),Ergebnis[i].toLowerCase());
			}
		}
		ta.value = contentToSave;
		if (!toSource) {
		
			editableArea.style.display = "none";
		
			ta.style.display= "block";
			document.getElementById('mozile_source_'+id).className = "buttonStyleActive";
		document.getElementById('mozile_wysiwyg_'+id).className = "buttonStyle";
			
		}
	} 
	
}

function bx_onSave(isSensitive) {
	try {
		var res = document.evaluate("/html/body//*[@contentEditable = 'true']", document.documentElement, null,null, null);
		if(res) {
		var ceNode = null;
		var toToggle = new Array();
		while (ceNode = res.iterateNext()) {
		
			if (ceNode.style.display ==  "" || ceNode.style.display == "block") {
				var id = ceNode.id.replace(/^mozile_/,"");
				toToggle.push(id);
		}	
	}
	for (var i = 0; i < toToggle.length; i++) {
		bx_toggleSource(toToggle[i],true);
	}
}
}
        catch (e) {
           }
	
	if (!xmlcheck()) {
		return false;
	}
	if (isSensitive) {
		
		if (document.forms.Master['_issensitive_password'].value == "") {
			document.forms.Master['_issensitive_password'].style.backgroundColor = "#ff9999";
			document.forms.Master['_issensitive_password'].focus();
			alert("Please provide your *personal* password, otherwise changes can't be made");
			
			return false;
		}
		
	}
		
	return true;
	
	
}




