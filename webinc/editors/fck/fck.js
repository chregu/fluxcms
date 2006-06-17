FCKeditor_OnComplete = function(inst) {
    loadContent();
}

startFCK = function() {
    var oFCKeditor = new FCKeditor("fluxfck");
    oFCKeditor.BasePath	= fckBasePath;
    oFCKeditor.Config['CustomConfigurationsPath'] = bx_webroot + 'admin/fck/fckconfig.js';
    oFCKeditor.Config['FullPage'] = false;
    oFCKeditor.ToolbarSet = 'fluxfck';
    oFCKeditor.Height = 500;
    oFCKeditor.Create() ;
}

loadContent = function() {
    var request = new XMLHttpRequest();
    
    // callback for async content loading
    function loadContent_callback() {
        if(request.readyState == 4) {
            
            if(request.responseText != null) {
                var contentDOM = Sarissa.getDomDocument();
                
                xml = request.responseText;
                
                // looks like IE doesn't like xml declarations and doctypes
                xml = xml.replace(/<\?xml[^>]+>/, "");
                // <!DOCTYPE ... >
                xml = xml.replace(/<\![^>]+>/, "");
                
                contentDOM = (new DOMParser()).parseFromString(xml, "text/xml");
                //alert(contentDOM.parseError);
                
                bodyNode = contentDOM.documentElement.firstChild;
                while(bodyNode.tagName != 'body' && bodyNode != null) {
                    bodyNode = bodyNode.nextSibling;
                }
                
                if(bodyNode) {
                    var bodyDOM = Sarissa.getDomDocument("", "bxrootnode");
                    
                    if(bodyNode.hasChildNodes()) {
                        node = bodyNode.firstChild;
                        while(node) {
                            // IE removes the node from the source on appendChild
                            nextSibling = node.nextSibling;                            

                            if(typeof bodyDOM.importNode == 'function') {
                                newNode = bodyDOM.importNode(node, true);
                                bodyDOM.documentElement.appendChild(newNode);
                            } else {
                                bodyDOM.documentElement.appendChild(node);
                            }
                            node = nextSibling;
                        }
                    }
                    
                    xml = Sarissa.serialize(bodyDOM);

                    // removes our root node
                    xml = xml.replace(/<[\/]*bxrootnode>/g, "");
                    
                    // removes all namespace prefixes matching "a[0..9]"
                    xml = xml.replace(/<a\d+:/g, "<");
                    xml = xml.replace(/<\/a\d+:/g, "</");
                    
                    // removes all namespace declarations
                    //xml = xml.replace(/xmlns(:.+)*="[^"]*"/g, "");

                    var oEditor = FCKeditorAPI.GetInstance("fluxfck");
                    oEditor.SetHTML(xml);
                    oEditor._bxOriginalDocument = contentDOM;
					if (_SARISSA_IS_MOZ) { 
						var res = oEditor.EditorDocument.evaluate("/html/body//*[@_moz-userdefined]",oEditor.EditorDocument,null, 0, null);
						if (res.iterateNext()) {
							alert("This document contains non-HTML elements, please consider using the oneform or BXE editor.\nSaving here will remove those elements and lead to unexpected results.\n\n PLEASE DO NOT SAVE, unless you know what you are doing.");
						}
					}
					
                    window.status = "Document loaded.";
                    
                } else {
                    alert('Error parsing the document. (No body tag found)');
                }

                
            } else {
                alert('Error loading document');
            }
        }
    }
    
    window.status = "Loading document...";
    request.open('GET', contentURI, true);
    request.onreadystatechange = loadContent_callback;
    request.send(null);
}

saveContent = function() {
    liveSaveSetStatus("Parsing the document...");
    var oEditor = FCKeditorAPI.GetInstance("fluxfck") ;
    var xml = oEditor.GetXHTML(true);
    var request = new XMLHttpRequest();

    // we don't like named entities
    xml = xml.replace(/&nbsp;/g, "&#160;");
    xml = "<body xmlns=\"http://www.w3.org/1999/xhtml\">" + xml + "</body>";
    
    contentDOM = oEditor._bxOriginalDocument;
    
    bodyNode = contentDOM.documentElement.firstChild;
    while(bodyNode.tagName != 'body' && bodyNode != null) {
        bodyNode = bodyNode.nextSibling;
    }
    
    bodyParent = bodyNode.parentNode;
    bodyNode.parentNode.removeChild(bodyNode);

    fckDOM = (new DOMParser()).parseFromString(xml, "text/xml");
    //alert(Sarissa.serialize(fckDOM));
    fckBodyNode = fckDOM.documentElement;
    
    //contentDOM.replaceChild(fckBodyNode, bodyNode);
    if(typeof contentDOM.importNode == 'function') {
        newNode = contentDOM.importNode(fckBodyNode, true);
        bodyParent.appendChild(newNode);
    } else {
        bodyParent.appendChild(fckBodyNode);
    }
    

    function saveContent_callback() {
        if(request.readyState == 4) {
            if (request.status != '200' && request.status != '204'  && request.status != '1223'  && request.status != '201'){
                alert('Error saving your data.\nResponse status: ' + request.status + '.\nCheck your server log for more information.');
               liveSaveSetStatus( "Error saving the document.");
            } else {
               liveSaveSetStatus("Document saved");
            }
			var  res = document.getElementById("LSResult");
			window.setTimeout(function() {res.style.display = 'none';}, 3000);
        }
    }

    request.open('PUT', contentURI, true);
    request.onreadystatechange = saveContent_callback;
    liveSaveSetStatus("Saving the document...");

    request.send(Sarissa.serialize(contentDOM));
}

function liveSaveSetStatus (text) {
	var  res = document.getElementById("LSResult");
	res.style.display = "inline";
	res.firstChild.nodeValue = text;
}

