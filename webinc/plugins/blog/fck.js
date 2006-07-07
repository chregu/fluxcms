

saveContent = function() {
	window.status = "Parsing the document...";
	var oEditor = FCKeditorAPI.GetInstance("fluxfck") ;
	var xml = oEditor.GetXHTML(true);
	// we don't like named entities
	xml = xml.replace(/&nbsp;/g, "&#160;");
	
}



function initFck() {
		var oFCKeditor = new FCKeditor("bx[plugins][admin_edit][content]");
		oFCKeditor.BasePath	= fckBasePath;
		oFCKeditor.Config['CustomConfigurationsPath'] = bx_webroot + 'admin/fck/fckconfig.js';
		oFCKeditor.Config['FullPage'] = false;
		oFCKeditor.ToolbarSet = 'fluxfckblog';
		//alert("here");
		oFCKeditor.Height = 500;
		oFCKeditor.ReplaceTextarea() ;
		if (document.getElementById('postExtended').style.display != 'none') {
			
			initFckExtended();
		}

	
}

function initFckExtended() {
		
		if (typeof FCKeditorAPI == 'undefined' || ! FCKeditorAPI.GetInstance("bx[plugins][admin_edit][content_extended]")) {
			var oFCKeditor = new FCKeditor("bx[plugins][admin_edit][content_extended]");
			oFCKeditor.BasePath	= fckBasePath;
			oFCKeditor.Config['CustomConfigurationsPath'] = bx_webroot + 'admin/fck/fckconfig.js';
			oFCKeditor.Config['FullPage'] = false;
			oFCKeditor.ToolbarSet = 'fluxfckblog';
			//alert("here");
			oFCKeditor.Height = 500;
			oFCKeditor.ReplaceTextarea() ;
		} 

}


function updateTextAreas() {
	var oEditor = FCKeditorAPI.GetInstance("bx[plugins][admin_edit][content]") ;
	var xml = oEditor.GetXHTML(true);
	document.getElementById("bx[plugins][admin_edit][content]").value = xml;
	
	var oEditor = FCKeditorAPI.GetInstance("bx[plugins][admin_edit][content_extended]") ;
	var xml = oEditor.GetXHTML(true);
	document.getElementById("bx[plugins][admin_edit][content_extended]").value = xml;
	
	return formCheck();
}


function insertImageForKupu(url) {
	var oEditor = FCKeditorAPI.GetInstance("bx[plugins][admin_edit][content]") ;
	oImage = oEditor.CreateElement( 'IMG' ) ;
	oImage.src = url;
	
}

function checkValidXML() {
	return true;
}

