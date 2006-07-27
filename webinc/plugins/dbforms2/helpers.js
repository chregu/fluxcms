function updateUriField(field, urifield) {
    // BROKEN: this helper doesn't work in subforms, because dbforms2.mainform
    // refers to the main form only. Should be fixed using a different way of 
    // calling the helper.
    
	//only update uri field, if it's a new entry (uri should be a permalink after all)
	
	if (urifield.value.length == 0) {
		urifield.wasEmpty = true;
		urifield.edited = false;
	}
	if ((urifield.wasEmpty  &&  urifield.edited == false ) || (!(dbforms2.mainform.currentID > 0) && (typeof urifield.edited == "undefined" ))) {
		var splitted = field.split(",");
		var uri = "";
		for (var i = 0; i < splitted.length; i++) {
			uri = uri + document.getElementById(splitted[i]).value + "-";
		}
		var newValue= makeUri(uri);
		urifield.value = newValue ;
	}
}

function makeUri(value) {
	   var newValue = value.toLowerCase();
	   newValue= newValue.replace(/@/g,"-at-");
		
		newValue= newValue.replace(/güe/g,"gue"); //spanish dieresis
		newValue= newValue.replace(/güi/g,"gui"); //spanish dieresis
		newValue= newValue.replace(/[öÖ]/g,"oe");
		newValue= newValue.replace(/[üÜ]/g,"ue");
		newValue= newValue.replace(/[äÄ]/g,"ae");
		newValue= newValue.replace(/[à]/g,"a");
		newValue= newValue.replace(/[éè]/g,"e");
		newValue= newValue.replace(/[ïíì]/g,"i");
		newValue= newValue.replace(/[ñ]/g,"n");
		newValue= newValue.replace(/[òó]/g,"o");
		newValue= newValue.replace(/[ùú]/g,"u");
		newValue= newValue.replace(/[ß]/g,"ss");
		newValue= newValue.replace(/[\n\r]*/g,"");
		newValue= newValue.replace(/[^a-z0-9\.\-\_]/g,"-");
		
		newValue= newValue.replace(/-{2,}/g,"-");
		newValue= newValue.replace(/^-/g,"");
		newValue= newValue.replace(/-+$/g,"");
		
		newValue= newValue.replace(/_([0-9]+)$/g,"-$1");
		
		
		
		return newValue;
}

function openUploadIframe (id) {
	var field = dbforms2.mainform.getFieldByID(id);
	field.setIframe();
}

function updateN2M(select,id) {
	field = dbforms2.form.getFieldByID(id);
	field.setFieldValue(select.options[select.selectedIndex].value,select.options[select.selectedIndex].text);
	select.selectedIndex = 0;
}

dbforms2_helpers = function() {
}


dbforms2_helpers.isImage = function(src) {
    if(src && typeof src == 'string') {
        var matches = src.match(/.*\.(jpeg|jpg|png|tif|gif)$/g);
        if(matches)
            return true;
    }
    
    return false;
}

dbforms2_helpers.toggleSubForm = function(linkn, id) {
    var dnode = document.getElementById(id);
    if(dnode.style.display == 'block') {
        dnode.style.display = 'none';
        linkn.innerHTML = '+';
    } else {
        dnode.style.display = 'block';        
        linkn.innerHTML = '-';
    }
}

function var_dump(obj) {
   if(typeof obj == "object") {
      alert("Type: "+typeof(obj)+((obj.constructor) ? "\nConstructor: "+obj.constructor : "")+"\nValue: " + obj);
   } else {
      alert("Type: "+typeof(obj)+"\nValue: "+obj);
   }
}

function var_serialize(obj) {
   if(typeof obj == "object") {
      return "Type: "+typeof(obj)+((obj.constructor) ? "\nConstructor: "+obj.constructor : "")+"\nValue: " + obj;
   } else {
      return "Type: "+typeof(obj)+"\nValue: "+obj;
   }
}
