function switchTab(id, all, direct ) {
	var tab = document.getElementById("li_"+id);
	var type = tab.parentNode.parentNode.getAttribute('name');
	
	var ExpireDate = new Date ();
	 //7days
	 ExpireDate.setTime(ExpireDate.getTime() + (7 * 24 * 3600 * 1000));
	 if (!direct) {
		 document.cookie = "openTabs["+type + "]=" + escape(tab.firstChild.firstChild.nodeValue) + "; path=/; expires=" + ExpireDate.toGMTString();
	 } else {
		 document.cookie = "openTabs["+type + "]=" + escape(id) + "; path=/; expires=" + ExpireDate.toGMTString();
	 }
	 
	 child = tab.parentNode.firstChild;
	while (child ) {
		if (all) {
			child.firstChild.className = "";
			
		}
		if (child.nodeType == 1 && child != tab && (child.firstChild.className == "selected") )
		{
			child.firstChild.className = "";
			
		}
		child = child.nextSibling;
	}
	
	tab.firstChild.className = "selected";
	
	tab = document.getElementById("tab_"+id);
	child = tab.parentNode.firstChild
	while (child ) {
		if (child == tab || (all && child.nodeName == "DIV" && child.id.match(/^tab/) )) {
			child.className = "tabcontent";
		}
		else if (child.nodeType == 1 && child.className == "tabcontent") {
			child.className = "tabcontentHidden";
		}
		child = child.nextSibling;
	}
}


