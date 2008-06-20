

function bx_i18n (xml) {
    this.xml = xml;
    this.messages = new Array();
    
    this.init = function() {
        this.xml = Sarissa.fixFirefox3Permissions(this.xml);
        var msg = this.xml.documentElement.getElementsByTagName('message')[0];
        while(msg != null) {
            // skip text nodes (1 == ELEMENT_NODE)
            if(msg.nodeType == 1) {
                key = msg.getAttribute('key');
                if(msg.childNodes[0]) {
                    this.messages[key] = msg.childNodes[0].data
                }
            }
            msg = msg.nextSibling;
        }
    }

    this.getText = function(key) {
        if(this.messages[key]) {
            return this.messages[key];
        }
        return false;
    }
    
    this.translate = function(key, replacements) {
        if((string = this.getText(key)) == false) 
            string = key;

        return this.substitute(string, replacements); 
    }
    
    this.translate2 = function(key, replacements) {
        var t = this.getText(this.substitute(key, replacements));
        if(t == false || t == '')
            return this.translate(key, replacements);
        
        return t;
    }
    
    this.substitute = function(string, replacements) {
        string = eval("'"+string.replace(/\{([0-9]+)\}/g,"'+replacements[$1]+'") + "'");
        return string;
    }
    
}
