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
        this.data.load(uri);
    }

    this.loadEntriesFromXML = function() {
        var entry = this.data.documentElement.firstChild.firstChild;
        this.results.removeAllEntries();
        while(entry) {
            idNS = entry.getElementsByTagName('_id');
            titleNS = entry.getElementsByTagName('_title');
            
            title = 'Parse Error';
            id = 0;

            if(titleNS.length > 0 && titleNS.item(0).childNodes[0])
                title = titleNS.item(0).childNodes[0].data

            if(idNS.length > 0 && idNS.item(0).childNodes[0])
                id = idNS.item(0).childNodes[0].data;
            
            this.results.addEntry(id, title);
            
            entry = entry.nextSibling;
        }
    }

    this.onChoose = function(entry) {
        this.onChooseAction(entry);
    }
    
    this.onDelete = function(entry) {
        this.onDeleteAction(entry);
    }
    
    this._sarissaOnLoadCallback = function() {
        if(this.data.readyState == 4 && !this.dataLoaded && this.data.documentElement) {
            this.dataLoaded = true;
            this.loadEntriesFromXML();
        }
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

