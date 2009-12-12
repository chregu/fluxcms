function dbforms2_group() {
    var value = null;
    var defaultValue = null;

    this.initGroup = function() {
        this.fields = new Array();
    }
    
    this.init = function() {
        this.initGroup();
    }

    this.setValue = function(value) {
        this.value = value;
    }
    
    this.getValue = function() {
        return this.value;
    }
    
    this.resetValue = function() {
        for(fieldID in this.fields) {
            this.fields[fieldID].resetValue();
        }
    }
    
    this.isValid = function() {
        return true;
    }
    
    this.enable = function() {
    }
    
    this.disable = function() {
    }
    
    this.show = function() {
    }
    
    this.hide = function() {
    }
    
}


function dbforms2_group_xml() {
    
    this.init = function() {
        this.initGroup();
        this.groupData = new dbforms2_formData();
        this.groupData.tablePrefix = '';
        this.groupData.formName = 'group';
    }
    
    this.setValue = function(value) {
        dbforms2_log.log(value);
        var xml = Sarissa.getDomDocument();
        try {
            xml = (new DOMParser()).parseFromString(value, "text/xml");
            if(xml.parseError == 0) {            
                this.groupData.setXML(xml);
                
                // set value of each field
                for(fieldID in this.fields) {
                    var field = this.fields[fieldID];
                    var value = this.groupData.getValueByFieldID(fieldID);
                    if(!value)
                        value = '';
                    field.setValue(value);
                }
                
            }
        } catch(e) {
            dbforms2_log.log('error parsing xml string: '+value);
        }
    }
    
    this.getValue = function() {
        var fieldID;
        for(fieldID in this.fields) {
            this.groupData.setValueByFieldID(fieldID, this.fields[fieldID].getValue());
        }
        var xml = this.groupData.getXML();
        
        var fieldsNode = xml.documentElement.firstChild.firstChild;
        var valueXML = Sarissa.getDomDocument('', 'xml');
        var length = fieldsNode.childNodes.length;
        for(var i=0; i<length; i++) {
            //alert(i+"/"+length+":"+fieldsNode.childNodes[i].nodeName);
            
            if(typeof valueXML.importNode == 'function') {
                var valNode = valueXML.importNode(fieldsNode.childNodes[i], true);
            } else {
                var valNode = fieldsNode.childNodes[i].cloneNode(true);
            }
            valueXML.documentElement.appendChild(valNode);
        }
        
        return(Sarissa.serialize(valueXML));
    }
    
}

// dbforms2_field_text inherits from dbforms2_field
dbforms2_group_xml.prototype = new dbforms2_group();



