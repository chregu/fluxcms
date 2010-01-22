function dbforms2_transport() {

    this.data = null;
    this.dataLoaded = false;
    this.dataSaved = false;
    this.onLoadCallback = null;
    this.onSaveCallback = null;
    
    this.loadXML = function(dataURI) {
        this.data = Sarissa.getDomDocument();
    
        var thisObject = this;

        
        
        dbforms2_log.log('loading ' + dataURI + '...')
        this.dataLoaded = false;
        // Create a YUI instance using io-base module.
        YUI().use("io-base", function(Y) {
            Y.on('io:complete', thisObject._YUIOnLoadCallback , thisObject, []);
            var request = Y.io(dataURI);
            }
        );
    }
    
    this.loadXMLSync = function(dataURI) {
        this.data = new XMLHttpRequest();
        this.data.open('GET', dataURI, false);
        
        response = new dbforms2_response();
    
        dbforms2_log.log('loading ' + dataURI + '...')
        
        try {
            this.data.send('');
            response.responseData = this.data.responseXML;
            response.responseCode = 0;
        } catch (e) { 
            response.responseText = 'Unable to establish a connection to the server.';
        }
        
        return response;
        
    }
    
    this.saveXML = function(dataURI, xml) {
        this.data = new XMLHttpRequest();

        var wrappedCallback = new ContextFixer(this._sarissaOnSaveCallback, this);
        this.data.onreadystatechange = wrappedCallback.execute;
        
        this.dataSaved = false;
        this.data.open('POST', dataURI);
        this.data.send(xml);
    }
    
    this.saveXMLSync = function(dataURI, xml) {
        this.data = new XMLHttpRequest();
        this.data.open('POST', dataURI, false);
        response = new dbforms2_response();
        
        try {
            this.data.send(xml);
            response.setXML(this.data.responseXML);
        } catch (e) {
            response.responseText = 'Unable to establish a connection to the server.';
        }
        
        return response;
        
    }
    
    this._YUIOnLoadCallback = function(a,xhr,options) {
        if(xhr.readyState == 4 && !this.dataLoaded && xhr.responseXML && xhr.responseXML.documentElement) {
            dbforms2_log.log('data loaded');
            this.dataLoaded = true;
            this.data = xhr.responseXML;
            // call document ready callback when the document has been loaded
            if (this.onLoadCallback != null && typeof this.onLoadCallback == "function")
                this.onLoadCallback();
        }
    }

    this._sarissaOnSaveCallback = function() {
        //dbforms2_log.log('dbforms2_loader::_sarissaOnSaveCallback');
        
        if(this.data.readyState == 4 && !this.dataSaved) {
            dbforms2_log.log('data saved');
            this.dataSaved = true;
 
            response = new dbforms2_response();
            response.setXML(this.data.responseXML);
            
            dbforms2_log.log('response = ' + dbforms2_common.serializeToString(this.data.responseXML));

            // call document ready callback when the document has been saved
            if (this.onSaveCallback != null && typeof this.onSaveCallback == "function")
                this.onSaveCallback(response);
        }
    }

}

function dbforms2_response() {

    this.xml = null;
    this.responseCode = 20;
    this.responseText = '';
    this.savedID = 0;
    this.responseData = null;
    
    this.setXML = function(xml) {
        this.xml = xml;
        this.parseXML();
    }
    
    this.parseXML = function() {
        // parse response code and message
        this.responseCode = this.xml.documentElement.getAttribute('code');
        this.savedID = this.xml.documentElement.getAttribute('id');
        this.responseText = this.xml.documentElement.getElementsByTagName('text')[0].childNodes[0].data;
        
        // check if the server has returned new data
        var dataNS = this.xml.documentElement.getElementsByTagName('data');
        if(dataNS[0]) {
            var xml = Sarissa.getDomDocument('', 'data');
            if(typeof xml.importNode == 'function') {
                newRoot = xml.importNode(dataNS[0], true);
                xml.documentElement.appendChild(newRoot);
            } else {
                xml.documentElement.appendChild(dataNS[0]);
            }
            
            this.responseData = xml;
        }
    }
    
    this.isError = function() {
        if(this.responseCode != 0) 
            return true;

        return false;
    }
    
    this.getResponseText = function() {
        return this.responseText; 
    }
    
    this.getResponseCode = function() {
        return this.responseCode; 
    }
    
}
