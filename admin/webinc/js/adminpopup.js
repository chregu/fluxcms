function adminPopUp() {}

adminPopUp.create = function(url) {
    this.popupurl = url;
    this.editorTagName = 'editor';
    
    return this;
}

adminPopUp.load = function(path) {
    
    if (this.popupurl != '') {
    
        try {
            var xmlHttp = XmlHttp.create();
        } catch(e) {
            alert(e);
            return false;
        }
            
        xmlHttp.open("GET", this.popupurl+path, true);
        xmlHttp.onreadystatechange = function () {
		
            if (xmlHttp.readyState == 4) {
                adminPopUp.parse(xmlHttp.responseXML);
            }
        
        };
        
        window.setTimeout(function () {
            xmlHttp.send(null);
        }, 10);
        
    }
    
}


adminPopUp.parse = function(xml) {
   popNode = xml.getElementsByTagName('adminpopup').item(0);
   if (popNode) {
       
       type = popNode.getAttribute('type');
	   var root = popNode.getAttribute('root');
	   
	   popdivId = 'PopUp' + type;
       uri = '';
       /* remove old*/
       this.remove(popdivId);
       
       /* create div */
       popdiv = document.createElement('div');
       popdiv.setAttribute('id', popdivId);
/*       popdiv.setAttribute('class', 'popup');*/
		popdiv.className = 'popup';
       popdiv.setAttribute('onmouseover', "MM_showHideLayers('"+popdivId+"','','show')");
       popdiv.setAttribute('onmouseout', "MM_showHideLayers('"+popdivId+"','','hide')");
       
       /* Tooldiv - to be monorom-ized ;) */
       tooldiv = document.createElement('div');
/*       tooldiv.setAttribute('class','popupHead');*/
       tooldiv.className = 'popupHead';
       /* spacer gif */
       
       /* Hide-icon */
       toollink = document.createElement('a');
       toollink.setAttribute('href', "javascript:MM_showHideLayers('PopUpSection','','hide');");
       toollink.setAttribute('style', "color: #ffffff");
/*       toollink.setAttribute('class', 'popupMenuEntry');*/
       toollink.className = 'popupMenuEntry';
       toollink_txt = document.createTextNode('x ' + type);
       toollink.appendChild(toollink_txt);
       tooldiv.appendChild(toollink);
       
       /* spacer gif*/
       
       /* Popup name*/
/*       toolname = document.createElement('i');
       toolname.appendChild(document.createTextNode(type));
       tooldiv.appendChild(toolname);
  */     
       
       popdiv.appendChild(tooldiv);
       /* Editor items*/
       editItems = popNode.getElementsByTagName('editor');
       if (editItems.length > 0) {
           for (i=0; i<editItems.length; i++) {
               name = parent.i18n.translate2("Edit in {0}", [editItems.item(i).getAttribute('name')]);
               
               nid  = 'webfx-tree-object-' + WebFXTreeOpenId;
               enode = this.createElement(name, editItems.item(i).getAttribute('href'), editItems.item(i).getAttribute('target'));
               
               if (enode) {
                enode.setAttribute("onclick", "webFXTreeHandler.activateItem('"+nid+"', 1)");
               }
               popdiv.appendChild(enode);
           }
               

           /*FIXME: ugly trailing line */
           tr = document.createElement('div');
           tr.setAttribute('style',"border-bottom:1px solid #fff; padding-bottom:2px; margin-bottom: 2px;");
           
           popdiv.appendChild(tr);
       }

       /* resource type items*/
       resourceTypeItems = popNode.getElementsByTagName('resourceType');
       if (resourceTypeItems.length > 0) {
           for (i=0; i<resourceTypeItems.length; i++) {
               name = parent.i18n.translate2("Create New {0}", [resourceTypeItems.item(i).getAttribute('name')]);
               rnode = this.createElement(name, resourceTypeItems.item(i).getAttribute('src'), null);
               /*
			   the above line, was before the three lines below.
			   Hopefully, this does the same, additionally it adds a "mouse-hand" when I hover
			   (by chregu)
			   rnode = this.createElement(name, null, null);
			   rnode.setAttribute('href', "#");
               rnode.setAttribute('onclick', resourceTypeItems.item(i).getAttribute('src'));
			   */
               popdiv.appendChild(rnode);
           }

            adminPopUp.addLine(popdiv);
               
       }
       

       
       actItems = popNode.getElementsByTagName('action');
       if (actItems) {
           for (i=0; i<actItems.length; i++) {
               an = this.createElement(actItems.item(i).getAttribute('name'), actItems.item(i).getAttribute('src'),   actItems.item(i).getAttribute('target'));
			   popdiv.appendChild(an);
               if (actItems.item(i).getAttribute("lineAfter")) {
				   adminPopUp.addLine(popdiv);
			   }

           }
       }
       
      adminPopUp.addLine(popdiv);
       
       /* Menu items */
       popItems = popNode.getElementsByTagName('item');
       if (popItems) {
           for (i=0; i<popItems.length; i++) {
               
               popdiv.appendChild(this.createElement(popItems.item(i).getAttribute('name'), popItems.item(i).getAttribute('src'), popItems.item(i).getAttribute('target')));
           }
       }
       
	   if (type =="collection") {
		   if (root) {
			   popdiv.appendChild(this.createElement(parent.i18n.translate("Reload"), "javascript:location.reload()"));
		   } else {
			   popdiv.appendChild(this.createElement(parent.i18n.translate("Reload"), "javascript:reload('"+popdivId+"')", null));
		   }
	   }
       
    }
    
    popdiv = document.getElementsByTagName('body').item(0).appendChild(popdiv);
	//quick and simple fix to make all the above eventhandlers and setAttribute work on MSIE...
	if (document.all) {
		popdiv.outerHTML = popdiv.outerHTML ;
	}
     
    this.show(type, uri, 'Produkte');
}

adminPopUp.addLine = function(node) {
	 var tr = document.createElement('div');
     tr.setAttribute('style',"border-bottom:1px solid #fff; padding-bottom:2px; margin-bottom: 2px;");
     node.appendChild(tr);
}

adminPopUp.remove = function(id) {
    
    node = document.getElementById(id);
    if (node) {
        document.getElementsByTagName('body').item(0).removeChild(node);
    }
    
    return true;
    
}

adminPopUp.show = function(type, id, name) {
    showPopUp(type, id, 0, id, name, 0, ' ');
}


adminPopUp.createBlock = function() {
}


adminPopUp.createElement = function(name, src, target) {
   
   elem = document.createElement('div');
   elem.className =  'popupMenuEntry';
   
   elema = document.createElement('a');
   
   if (src != "" && src != null) {
       elema.setAttribute('href', src);
   }
   
   if (target != "" && target != null) {
       elema.setAttribute('target', target);
   }
   
   elema.appendChild(document.createTextNode(name));
   
   elem.appendChild(elema);
   return elem;
   
}
