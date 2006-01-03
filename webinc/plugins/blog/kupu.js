   var kupu = null;
   var kupuui = null;
   
   /* function BxContentFilter() {
	   this.initialize = function(editor) {
		   this.editor = editor;
	   };
	   
	   this.filter = function(ownerdoc, htmlnode) {
		   htmlnode = deInitBxContent(ownerdoc,htmlnode);
		   return htmlnode;
	   }
   }*/
   
   function insertImageForKupu(url) {
        
       kupu.getTool('imagetool').createImage(url);
   }
   
   function kupuGetContent() {
	   doc=kupu.getInnerDocument();
	   xmlserializer=new XMLSerializer();
	   alert(xmlserializer.serializeToString(doc));
	   
   }
   
   function startKupu() {
	   
	   
	   ifr = document.getElementById("kupu-editor")
	   function init() {
		   
		   var frame = document.getElementById('kupu-editor');
		   kupu = initKupu(frame,name);
//		   kupuui = kupu.getTool('ui');
		   
		   kupu.initialize();
		   updateIframe(frame);
	   }
	   //ifr.onload = init;
	   setTimeout(init, 100);
   }
   
   function updateTextAreas() {
	   var kuputextarea = document.getElementById('kupu-editor-textarea');
	   if (kuputextarea.style.display != "none") {
		   var sourceedittool = new SourceEditTool('kupu-source-button',
		   'kupu-editor-textarea');
		     sourceedittool.sourcemode = true;
		   sourceedittool.initialize(kupu);
		   sourceedittool.switchSourceEdit()
		   //updateIframe(frame);
	   }
	   
       kupu.prepareForm(document.forms['entry'], 'content', true);
       return formCheck();
   }
   
 
   
   function updateIframe(frame) {
	   var textarea = document.getElementById('content');
	   var bodyele = frame.contentWindow.document.getElementsByTagName("body")[0];
	   var doc = frame.contentWindow.document;
	   bodyele.innerHTML = textarea.value ;
	   
   }

   function initBxContent() {
	   
	   var css = document.getElementById('mastercss');
	   var frame = document.getElementById('kupu-editor');
	   var fdoc = frame.contentWindow.document;
	   var frameHead = fdoc.getElementsByTagName("head")[0];
	   
	   hasCss=0;
	   headLinks = frameHead.getElementsByTagName('link');
	   
	   if (headLinks.length > 0 ) {
		   for (var n in headLinks) {
			   if (headLinks.item(n).getAttribute('type') == 'text/css') {
				   hasCss=1;
				   break;
			   }
		   }
	   } 
	   
	   if (hasCss==0) {
		   
		   headCss= fdoc.createElement('link');
		   headCss.setAttribute('rel', 'stylesheet');
		   headCss.setAttribute('type','text/css');
		   headCss.setAttribute('href','/themes/'+theme+'/css/'+themeCss);
		   frameHead.appendChild(headCss);
		   
		   headCss= fdoc.createElement('link');
		   headCss.setAttribute('rel', 'stylesheet');
		   headCss.setAttribute('type','text/css');
		   headCss.setAttribute('href','/themes/'+theme+'/css/kupu-additions.css');
		   frameHead.appendChild(headCss);
	   }
	   
   }
   
  
   