function dbforms2_liveselect() {
    this.transport = new dbforms2_transport();
    this.onChooseAction = null;
    this.onDeleteAction = null;
    this.disabled = false;
    this.currentEntry = null;
    
    // set this to the data uri for this live select (should not be empty)
    this.dataURI = '';

    // the following members are optional configuration settings
    this.autoExpandResultsOnFocus = true;
    this.autoCollapseResultsOnBlur = true;
    this.showResultsIfEmpty = false;
    this.autoQueryTimeout = 200;
    this.readOnly = false;
    this.showSelectedEntry = false;
    
    this.init = function(queryfieldDOMNode, resultsDOMNode, dropDownImgNode) {
        this.queryField = new dbforms2_liveselect_queryfield(queryfieldDOMNode, this);
        this.queryField.init();
        
        if(this.readOnly == true) {
            queryfieldDOMNode.readOnly = true;
        }

        if(typeof dropDownImgNode != 'undefined') {
            var wev_onMouseUp = new ContextFixer(this.queryField.e_onMouseUpImage, this.queryField);
            bx_helpers.addEventListener(dropDownImgNode, 'mouseup', wev_onMouseUp.execute);
        }
        
        this.results = new dbforms2_liveselect_results(resultsDOMNode, this);

        _registerObj(queryfieldDOMNode.id, this.queryField);
        this.query('');
    }
    
    this.moveResultFocus = function(direction) {
        if(direction == 'next') {
            this.results.focusNext();
        } else if(direction == 'previous') {
            this.results.focusPrevious();
        }
    }
    
    this.reloadCurrentQuery = function() {
        this.query(this.queryField.currentQuery);
    }
    
    this.query = function(query) {
    	var get = '';
        if (window.location.search != "") {
  			get = '&' + window.location.search.substring(1,8192);   
  		} 
        var uri =  this.dataURI + '?q=' + escape(query) + get;
        this.data = Sarissa.getDomDocument();
    
        var wrappedCallback = new ContextFixer(this._sarissaOnLoadCallback, this);
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
            
            title = '#Broken Entry#';
            id = 0;

            if(titleNS.length > 0 && titleNS.item(0).childNodes[0]) {
                title = titleNS.item(0).childNodes[0].data
            } 

            if(idNS.length > 0 && idNS.item(0).childNodes[0]) {
                id = idNS.item(0).childNodes[0].data;
            }
            
            this.results.addEntry(id, title);
            
            entry = entry.nextSibling;
        }
        if(this.queryField.hasFocus && this.results.entries.length > 0) {
            this.results.focusEntryByID(0);
            this.results.show();
        } else {
            this.results.hide();
        }
    }
    
    this._sarissaOnLoadCallback = function() {
        if(this.data.readyState == 4 && !this.dataLoaded && this.data.documentElement) {
            this.dataLoaded = true;
            this.loadEntriesFromXML();
        }
    }
    
    this.onChoose = function(entry) {
        this.results.hide();
        this.currentEntry = entry;
        this.onChooseAction(entry);
    }
    
    this.onDelete = function(entry) {
        if(this.onDeleteAction != null) {
            this.onDeleteAction(entry);
        }
    }
    
    this.disable = function() {
        this.disabled = true;
        this.queryField.DOMNode.disabled = true;
    }

    this.enable = function() {
        this.disabled = false;
        this.queryField.DOMNode.disabled = false;
    }
    
    this.focus = function() {
        this.queryField.DOMNode.focus();
    }
    
}

function dbforms2_liveselect_queryfield(DOMNode, chooser) {
    this.currentQuery = '';
    this.savedValue = null;
    this.onKeyUpTimeout = null;
    this.hideTimeout = null;

    this.DOMNode = DOMNode;
    this.chooser = chooser;
    this.hasFocus = false;
    
    this.init = function() {
        var wev_keyPress = new ContextFixer(this.e_keyPress, this);
        if(_BX_HELPERS_IS_IE) {
            bx_helpers.addEventListener(this.DOMNode, 'keydown', wev_keyPress.execute);
        } else if(_BX_HELPERS_IS_MOZ){
            bx_helpers.addEventListener(this.DOMNode, 'keypress', wev_keyPress.execute);
        }

        var wev_keyUp = new ContextFixer(this.e_keyUp, this);
        bx_helpers.addEventListener(this.DOMNode, 'keyup', wev_keyUp.execute);
        
        var wev_focus = new ContextFixer(this.e_focus, this);
        bx_helpers.addEventListener(this.DOMNode, 'focus', wev_focus.execute);

        var wev_blur = new ContextFixer(this.e_blur, this);
        bx_helpers.addEventListener(this.DOMNode, 'blur', wev_blur.execute);

        var wev_onMouseDown = new ContextFixer(this.e_onMouseDown, this);
        bx_helpers.addEventListener(this.DOMNode, 'mousedown', wev_onMouseDown.execute);

        if(_BX_HELPERS_IS_MOZ) {
            this.DOMNode.setAttribute('autocomplete', 'off');
        }

    }
    
    this.clearCurrentEntry = function() {
        if(this.chooser.showSelectedEntry) {
            this.chooser.currentEntry = null;
            this.DOMNode.value = '';
            this.showCurrentEntry();
        }
    }
    
    this.showCurrentEntry = function() {
        if(this.chooser.showSelectedEntry) {
            if(this.chooser.currentEntry != null) {
                this.DOMNode.value = this.chooser.currentEntry.title;
            } else  {
                this.DOMNode.value = '';
            }
        }
    }
    
    this.hideCurrentEntry = function() {
        if(this.chooser.showSelectedEntry && this.savedValue != null) {
            this.DOMNode.value = this.savedValue;
        }
    }
    
    this.e_keyUp = function(event) {
        if(this.onKeyUpTimeout) {
            window.clearTimeout(this.onKeyUpTimeout);
        }
        var wrappedCallback = new ContextFixer(this._onKeyUpTimeout, this);
        this.onKeyUpTimeout = window.setTimeout(wrappedCallback.execute, this.chooser.autoQueryTimeout);
    }
    
    this.e_focus = function() {
        this.hasFocus = true;
        this.hideCurrentEntry();
        if(this.chooser.autoExpandResultsOnFocus) {
            this.chooser.results.show();
        }
    }
    
    this.e_blur = function() {
        this.hasFocus = false;
        if(this.chooser.autoCollapseResultsOnBlur) {
            this.chooser.results.hide();
        }
        this.savedValue = this.DOMNode.value;
        this.showCurrentEntry();
        
    }
    
    this.e_keyPress = function(event) {
        if (event.keyCode == 40 ) {
            // KEY DOWN
            if(this.chooser.results.hidden) {
                this.chooser.results.show();
            } else {
                this.chooser.results.focusNextEntry();
            }
            
        } else if (event.keyCode == 38 ) {
            // KEY UP
            if(this.chooser.results.hidden) {
                this.chooser.results.show();
            } else {
                this.chooser.results.focusPreviousEntry();
            }
            
        } else if (event.keyCode == 27) {
            // ESC
            this.chooser.results.hide();
            
        } else if(event.keyCode == 33) {
            event.stopPropagation();
            
        } else if (event.keyCode == 13 || event.keyCode == 14) {
            // ENTER & RETURN
            if(this.chooser.results.hidden) {
                this.chooser.results.show();
            } else {
                this.chooser.onChoose(this.chooser.results.entries[this.chooser.results.entryFocus]);
                this.chooser.results.hide();
            }
        } else if(event.keyCode == 46) {
            // DELETE
            this.chooser.onDelete(this.chooser.results.entries[this.chooser.results.entryFocus]);
        }
    }
    
    this.e_onMouseDown = function() {
        if(this.chooser.results.hidden) {
            this.chooser.results.show();
        } else {
            this.chooser.results.hide();
        }
    }
    
    this.e_onMouseUpImage = function(event) {
        this.DOMNode.focus();
    }
    
    this._onKeyUpTimeout = function() {
        if(this.currentQuery != this.DOMNode.value) {
            this.currentQuery = this.DOMNode.value
            this.chooser.query(this.currentQuery);
        }
    }
    
    this._hideResultsTimeout = function() {
        this.chooser.results.hide();
        this.hasFocus = false;
    }
    
}

function dbforms2_liveselect_results(DOMNode, chooser) {
    this.entries = new Array();

    this.DOMNode = DOMNode;
    this.chooser = chooser;
    
    var ulNS = DOMNode.getElementsByTagName('ul');
    this.ULDOMNode = ulNS.item(0);
    
    this.entryFocus = 0;
    this.hidden = true;
    
    this.show = function() {
        if(this.entries.length > 0 || this.chooser.showResultsIfEmpty) {
            this.DOMNode.style.display = 'block';
            this.hidden = false;
        }
    }
    
    this.hide = function() {
        this.DOMNode.style.display = 'none';
        this.hidden = true;
    }
    
    this.addEntry = function(id, title) {
        var entry = new dbforms2_liveselect_entry();
        var newLI = document.createElement('li');
        
        entry.id = id;
        entry.title = title;
        entry.results = this;
        
        newLI.innerHTML = title;
        newLI.id = dbforms2_getID();
        
        var wev_onMouseOver = new ContextFixer(entry.e_onMouseOver, entry);
        bx_helpers.addEventListener(newLI, 'mouseover', wev_onMouseOver.execute);

        var wev_onMouseDown = new ContextFixer(entry.e_onMouseDown, entry);
        bx_helpers.addEventListener(newLI, 'mousedown', wev_onMouseDown.execute);
        
        entry.DOMNode = newLI;
        this.ULDOMNode.appendChild(newLI);
        this.entries.push(entry);
        _registerObj(newLI.id, entry);
    }
    
    this.removeAllEntries = function() {
        while(this.ULDOMNode.hasChildNodes()) {
            this.ULDOMNode.removeChild(this.ULDOMNode.firstChild);
        }
        this.entries = new Array();
    }
    
    this.focusEntryByID = function(entry) {
        if(this.entries[entry] != null) {
	    if (this.entries[this.entryFocus]) {
               this.entries[this.entryFocus].unsetFocus();
	    }
            this.entryFocus = entry;
            this.entries[entry].setFocus();
        }
    }
    
    this.focusEntryByEntryObj = function(entry) {
        for(var i=0; i<= this.entries.length; i++) {
            if(entry == this.entries[i]) {
                this.focusEntryByID(i);
            }
        }
    }
    
    this.focusNextEntry = function() {
        if(this.entries[this.entryFocus] != null) 
            this.entries[this.entryFocus].unsetFocus();

        if(++this.entryFocus > (this.entries.length - 1))
            this.entryFocus = 0;

        if(this.entries[this.entryFocus] != null) 
            this.entries[this.entryFocus].setFocus();
    }

    this.focusPreviousEntry = function() {
        if(this.entries[this.entryFocus] != null) 
            this.entries[this.entryFocus].unsetFocus();

        if(--this.entryFocus < 0)
            this.entryFocus = this.entries.length - 1;

        if(this.entries[this.entryFocus] != null) 
            this.entries[this.entryFocus].setFocus();
    }
    
}

function dbforms2_liveselect_entry() {
    var DOMNode = null;
    var id = 0;
    var title = '';
    var results = null;
    
    this.e_onMouseDown = function() {
        this.results.chooser.onChoose(this);
    }
    
    this.e_onMouseOver = function() {
        this.results.focusEntryByEntryObj(this);
    }
    
    this.setFocus = function() {
        this.DOMNode.className = 'hasfocus';
    }

    this.unsetFocus = function() {
        this.DOMNode.className = '';
    }
    
}

