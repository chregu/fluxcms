function fckAddLoadEvent(func) {
	 var oldonload = window.onload;
	 
	 if (typeof window.onload != 'function') {
		 if (typeof func == 'string') {
			 window.onload = function() { eval(func); }
		 } else {
			 window.onload = func;
		 }
	 } else {
		 window.onload = function() {
			 oldonload();
			 
			 if (typeof func == 'string') {
				 eval(func);
			 } else {
				 func();
			 }
		 }
	 }
}


function initFck() {
	var ta = document.getElementsByTagName("textarea");
	
	for (var i = 0; i < ta.length; i++) {
		if (ta[i].id.substr(0,8) == 'wysiwyg_') {
			
			
    		
			 var oFCKeditor = new FCKeditor( ta[i].id ) ;
			 oFCKeditor.BasePath = fckBasePath ;
			 oFCKeditor.BasePath	= fckBasePath;
			 oFCKeditor.Config['CustomConfigurationsPath'] = bx_webroot + 'admin/fck/fckconfig.js';
			 oFCKeditor.Config['FullPage'] = false;
			 oFCKeditor.ToolbarSet = 'patforms';
			 oFCKeditor.Config.ImageBrowser = false;
			 oFCKeditor.Config.LinkBrowser = false;
			 oFCKeditor.Config.LinkUpload = false;
        		 oFCKeditor.ReplaceTextarea() ;
		}
	}
}

fckAddLoadEvent(initFck);

