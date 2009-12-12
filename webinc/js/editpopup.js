// +----------------------------------------------------------------------+
// | popoon                                                               |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001,2002,2003,2004 Bitflux GmbH                       |
// +----------------------------------------------------------------------+
// | Licensed under the Apache License, Version 2.0 (the "License");      |
// | you may not use this file except in compliance with the License.     |
// | You may obtain a copy of the License at                              |
// | http://www.apache.org/licenses/LICENSE-2.0                           |
// | Unless required by applicable law or agreed to in writing, software  |
// | distributed under the License is distributed on an "AS IS" BASIS,    |
// | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
// | implied. See the License for the specific language governing         |
// | permissions and limitations under the License.                       |
// +----------------------------------------------------------------------+
// | Author: Christian Stocker <chregu@bitflux.ch>                        |
// +----------------------------------------------------------------------+
//
// $Id$


editPopup = function() {};

editPopup.init = function(webRoot, requestURI) {
    editPopup.popupLayer = editPopup.getPopupLayer();
    editPopup.visible = false;
    editPopup.menuDocumentURI = webRoot + 'admin/editpopup' + requestURI;
    editPopup.menuDocument = false;
    editPopup.menuDocumenLoaded = false;
    editPopup.timeout = false;
}

editPopup.show = function() {
    editPopup.visible = true;
    editPopup.popupLayer.style.visibility = 'visible';
}

editPopup.hide = function() {
    editPopup.visible = false;
    editPopup.popupLayer.style.visibility = 'hidden';
}

editPopup.mouseOut = function() {
    editPopup.clearTimeout();
    editPopup.timeout = window.setTimeout("editPopup.timeOut()", 1000);
}

editPopup.mouseOver = function() {
      editPopup.show();
}

editPopup.timeOut = function() {
    editPopup.hide();
}

editPopup.clearTimeout = function() {
    window.clearTimeout(editPopup.timeout);
}

editPopup.sarissaCallback = function() {
    if(editPopup.menuDocument.readyState == 4 && !editPopup.menuDocumentLoaded && editPopup.menuDocument.documentElement) {
        var popupNode = document.getElementById('editpopupchild');
        var newNode = document.importNode(editPopup.menuDocument.documentElement, true);
        editPopup.popupLayer.replaceChild(newNode, popupNode);
        editPopup.menuDocumentLoaded = true;
    }
}

editPopup.toggle = function() {
    editPopup.clearTimeout();

    if (editPopup.visible) {
        editPopup.hide();
    } else {
        editPopup.show();
    }

    // load menu document
    if(!editPopup.menuDocument) {
        editPopup.loadMenuDocument();
    }
}

editPopup.loadMenuDocument = function(url) {
    editPopup.menuDocument = Sarissa.getDomDocument();
    editPopup.menuDocument.onreadystatechange = editPopup.sarissaCallback;
    editPopup.menuDocument.load(editPopup.menuDocumentURI);
}

editPopup.getPopupLayer = function() {
    return document.getElementById('editpopup');
}
