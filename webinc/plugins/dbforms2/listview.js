function dbforms2_listview() {
    
    this.transport = new dbforms2_transport();
    this.onChooseAction = null;
    this.onDeleteAction = null;
    this.disabled = false;
    
    // set this to the data uri
    this.dataURI = '';

    this.init = function(resultsDOMNode) {
        this.results = new dbforms2_listview_results(resultsDOMNode, this);
    }
    
    this.loadEntries = function(arguments) {
        
        var uri =  this.dataURI;
        if(arguments !== undefined) {
            if(arguments['thatid'] != undefined && arguments['thatid'] != null && arguments['thatid'] != 0) {
                uri = uri+'?thatid=' + escape(arguments['thatid']);
            }
        }
        
        this.data = Sarissa.getDomDocument();
    
        var wrappedCallback = new bx_helpers_contextfixer(this._sarissaOnLoadCallback, this);
        this.data.onreadystatechange = wrappedCallback.execute;
        
        dbforms2_log.log('loading ' + uri + '...')
        this.dataLoaded = false;
         //IE has some problems with YUI.io (no idea why), so we
        // use the old sarissa if IE.
        if (typeof this.data.onreadystatechange == "unknown") {
            var wrappedCallback = new ContextFixer(this._sarissaOnLoadCallback, this);
            this.data.onreadystatechange = wrappedCallback.execute;
            this.data.load(uri);
        } else {
        
            var thisObject = this;
            
            // Create a YUI instance using io-base module.
            YUI().use("io-base", function(Y) {
                Y.on('io:complete', thisObject._YUIOnLoadCallback , thisObject, []);
                var request = Y.io(uri);
                }
            );
        }
    }

    this.loadEntriesFromXML = function(isYui) {
       if (!isYui) {
           this.data = Sarissa.fixFirefox3Permissions(this.data);
       }
       var entry = this.data.documentElement.firstChild.firstChild;
        
        this.results.removeAllEntries();
        while(entry) {
            idNS = entry.getElementsByTagName('_id');
            titleNS = entry.getElementsByTagName('_title');
            
            id = 0;

            
            if(idNS.length > 0 && idNS.item(0).childNodes[0]) {
                id = idNS.item(0).childNodes[0].data;
            }

            title = '#Empty Field# ('+ id +')';
            
			if(titleNS.length > 0 && titleNS.item(0).childNodes[0]) {
                title = titleNS.item(0).childNodes[0].data
            } 

            this.results.addEntry(id, title);
            
            entry = entry.nextSibling;
        }
        
        if(this.enablePager) {
            var numPagesNS = this.data.getElementsByTagName('numpages');
            var numPages = 0;
            if(numPagesNS.length > 0) {
                numPages = numPagesNS.item(0).childNodes[0].data;
            }
            
            if(numPages > 1) {
                if(numPages != this.numPages) {
                    this.resetPager(numPages);
                } else {
                    this.updatePagerDisplay();
                }
            } else {
                this.hidePagerDisplay();
            }
               
        }
        
    }
    
    this._sarissaOnLoadCallback = function() {
        if(this.data.readyState == 4 && !this.dataLoaded && this.data.documentElement) {
            this.dataLoaded = true;
            this.loadEntriesFromXML();
        }
    }
    
    this._YUIOnLoadCallback = function(a,xhr,options) {
        if(xhr.readyState == 4 && !this.dataLoaded && xhr.responseXML && xhr.responseXML.documentElement) {
            this.dataLoaded = true;
            this.data = xhr.responseXML;
            this.loadEntriesFromXML(true);
        }
    }

    this.onChoose = function(entry) {
        this.onChooseAction(entry);
    }
    
    this.onDelete = function(entry) {
        this.onDeleteAction(entry);
    }
    
}

function dbforms2_listview_results(DOMNode, listview) {
    this.entries = new Array();

    this.DOMNode = DOMNode;
    this.listview = listview;
    
    this.hidden = true;
    
    this.show = function() {
    }
    
    this.hide = function() {
    }
    
    this.addEntry = function(id, title) {
        var entry = new dbforms2_listview_entry();
        var newTR = document.createElement('tr');
        newTR.id = dbforms2_getID();
        
        entry.id = id;
        entry.title = title;
        entry.results = this;
        
        // left td
        var td = document.createElement('td');
        td.innerHTML = '<a>'+title+'</a>';
        newTR.appendChild(td);

        var wev_choose_onMouseDown = new bx_helpers_contextfixer(entry.e_choose_onMouseDown, entry);
        bx_helpers.addEventListener(td.firstChild, 'mousedown', wev_choose_onMouseDown.execute);
        
        // right td
        var td = document.createElement('td');
        td.innerHTML = '<img border="0" src="'+bx_webroot+'admin/webinc/img/icons/delete.gif"/>';
        newTR.appendChild(td);

        var wev_delete_onMouseDown = new bx_helpers_contextfixer(entry.e_delete_onMouseDown, entry);
        bx_helpers.addEventListener(td.firstChild, 'mousedown', wev_delete_onMouseDown.execute);

        entry.DOMNode = newTR;
        this.DOMNode.appendChild(newTR);
        this.entries.push(entry);
        _registerObj(newTR.id, entry);
        
    }
    
    this.removeEntry = function(entry) {
        this.DOMNode.removeChild(entry.DOMNode);
    }
    
    this.removeAllEntries = function() {
        while(this.DOMNode.hasChildNodes()) {
            this.DOMNode.removeChild(this.DOMNode.firstChild);
        }
        this.entries = new Array();
    }
    
    this.length = function() {
        return this.entries.length;
    }
    
}

function dbforms2_listview_entry() {
    var DOMNode = null;
    var id = 0;
    var title = '';
    var results = null;
    
    this.e_choose_onMouseDown = function() {
        this.results.listview.onChoose(this);
    }
    
    this.e_delete_onMouseDown = function() {
        this.results.listview.onDelete(this);
    }
    
}

