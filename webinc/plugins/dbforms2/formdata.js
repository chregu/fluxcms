function dbforms2_formData() {

    this.values = new Array();
    this.xml = null;
    this.tablePrefix = '';
    this.formName = '';

    this.getXML = function() {
        xml = Sarissa.getDomDocument('', 'data');
        
        tableNodeName = this.tablePrefix + this.formName;
        tableNode = xml.createElement(tableNodeName);
        entryNode = xml.createElement(tableNodeName);
        
        for(valueName in this.values) {
            value = this.values[valueName];
			
			if (typeof value == "object" ) {
				if (value.constructor == Array) {
					var valueTN = xml.createElement("values");
					for (var i in value) {
						v = xml.createElement("value");
						v.appendChild( xml.createTextNode(value[i]));
						v.setAttribute("id",i);
						valueTN.appendChild(v);
					}
				} else {
					// TODO... append XML Element...
				}
			} else {
				valueTN = xml.createTextNode(value);
			}
			valueNode = xml.createElement(valueName);
			valueNode.appendChild(valueTN);

            entryNode.appendChild(valueNode);
        }
        
        tableNode.appendChild(entryNode);
        xml.documentElement.appendChild(tableNode);
        
        return xml;
    }

    this.setXML = function(xml) {
        this.xml = Sarissa.fixFirefox3Permissions(xml);
    }
    
    this.getValueByFieldID = function(fieldID) {
        tagNS = this.xml.getElementsByTagName(fieldID);
		if (tagNS[0] && tagNS[0].childNodes.length == 1) {
			var childNode = tagNS[0].firstChild;
			if (childNode.nodeType == 1) {
				if (childNode.nodeName == "values") {
					var values= new Array();
					var child = childNode.firstChild;
					while (child) {
						if(typeof child.childNodes[0] != 'undefined') {
                            values[child.getAttribute("id")] = child.childNodes[0].data;
                        }
						child = child.nextSibling;
					}
					return values;
				} else {
					return childNode;
				}
			} 
		}
		if (tagNS[0] && tagNS[0].childNodes[0]) {
            if (tagNS[0].childNodes[0].nextSibling) {
                str = '';
                nd = tagNS[0].childNodes[0];
                while(true) {
                    str+= nd.nodeValue;
                    nd = nd.nextSibling;
                    if (!nd) {
                        break;
                    }
                }
                
                return str;
            }
            
            return tagNS[0].childNodes[0].data;
		
        
        } else {
			return null;
		}
    }
	
	this.getValueNodeByFieldID = function(fieldID) {
		if(this.xml) {
			tagNS = this.xml.getElementsByTagName(fieldID);
			return tagNS[0];
		} else {
			return null;
		}
    }

    this.setValueByFieldID = function(fieldID, value) {
        this.values[fieldID] = value;
    }

}

