function dbforms2_liveselect() {
    this.transport = new dbforms2_transport();
    this.onChooseAction = null;
    this.onDeleteAction = null;
    this.disabled = false;
    this.currentEntry = null;
    
    this.currentPage = 0;
    this.numPages = 0;
    this.pagerDOMNode = null;
    
    // set this to the data uri for this live select (should not be empty)
    this.dataURI = '';

    // the following members are optional configuration settings
    this.autoExpandResultsOnFocus = true;
    this.autoCollapseResultsOnBlur = true;
    this.showResultsIfEmpty = false;
    this.autoQueryTimeout = 200;
    this.readOnly = false;
    this.showSelectedEntry = false;
    this.enablePager = false;
    this.pagerDisplayTemplate = 'page CURPAGE of NUMPAGES';
    
    this.init = function(queryfieldDOMNode, resultsDOMNode, dropDownImgNode, pagerDOMNode) {
        this.queryField = new dbforms2_liveselect_queryfield(queryfieldDOMNode, this);
        this.queryField.init();
        
        if(this.readOnly == true) {
            queryfieldDOMNode.readOnly = true;
        }

        if(typeof dropDownImgNode != 'undefined' && dropDownImgNode != null) {
            var wev_onMouseUp = new ContextFixer(this.queryField.e_onMouseUpImage, this.queryField);
            bx_helpers.addEventListener(dropDownImgNode, 'mouseup', wev_onMouseUp.execute);
        }

        if(typeof pagerDOMNode != 'undefined' && pagerDOMNode != null && this.enablePager) {
            this.pagerDOMNode = pagerDOMNode;
            this.pagerDOMNode.style.display = 'block';
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
        var uri =  this.dataURI + '?q=' + escape(query);

        if(this.enablePager) {
            uri = uri + '&p=' + this.currentPage;
        }
        uri = uri + get;
        
        this.data = Sarissa.getDomDocument();
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
        
        if(this.queryField.hasFocus && this.results.entries.length > 0) {
            this.results.focusEntryByIndex(0);
            this.results.show();
        } else {
            this.results.hide();
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
    
    this.showNextPage = function() {
        if(this.currentPage + 1 < this.numPages) {
            this.currentPage++;
        } else {
            this.currentPage = 0;
        }
        this.reloadCurrentQuery();
    }
    
    this.showPreviousPage = function() {
        if(this.currentPage - 1 >= 0) {
            this.currentPage--;
        } else {
            this.currentPage = this.numPages - 1;   
        }
        this.reloadCurrentQuery();
    }
    
    this.showFirstPage = function() {
        this.currentPage = 0;
        this.reloadCurrentQuery();
    }
    
    this.showLastPage = function() {
        this.currentPage = this.numPages - 1;
        this.reloadCurrentQuery();
    }
     
    this.updatePagerDisplay = function() {
        var display = this.pagerDisplayTemplate;
        display = display.replace(/CURPAGE/, this.currentPage + 1);
        display = display.replace(/NUMPAGES/, this.numPages);
        this.pagerDOMNode.innerHTML = display;
        this.pagerDOMNode.style.display = 'block';
    }
    
    this.resetPager = function(numPages) {
        this.currentPage = 0;
        this.numPages = numPages;
        this.updatePagerDisplay();
    }
    
    this.hidePagerDisplay = function() {
        this.pagerDOMNode.style.display = 'none';
    }
    
    this.setCurrentEntryById = function(id) {
        entry = this.results.getEntryByID(id);
        if(entry !== false) {
            this.currentEntry = entry;
        } else {
            this.currentEntry = new dbforms2_liveselect_entry();
            this.currentEntry.title = 'n/a';
        }
        this.queryField.savedValue = '';
        this.queryField.showCurrentEntry();
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
                this.DOMNode.value = bx_string.stripHtmlTags(this.chooser.currentEntry.title);
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
    
    this.e_blur = function(e) {
        this.hasFocus = false;
        if(this.chooser.autoCollapseResultsOnBlur) {
            this.chooser.results.hide();
        }
        this.savedValue = this.DOMNode.value;
        this.showCurrentEntry();
        
    }
    
    this.e_keyPress = function(event) {
        if (event.keyCode == 40 ) { // KEY DOWN
            if(this.chooser.results.hidden) {
                this.chooser.results.show();
            } else {
                this.chooser.results.focusNextEntry();
            }
            
        } else if (event.keyCode == 38 ) {  // KEY UP
            if(this.chooser.results.hidden) {
                this.chooser.results.show();
            } else {
                this.chooser.results.focusPreviousEntry();
            }
            
        } else if (event.keyCode == 27) {   // ESC
            this.chooser.results.hide();
            
        } else if(event.keyCode == 33 && this.chooser.enablePager) {    // PAGE UP
            // don't do the default action on the textfield
            event.preventDefault();
            this.chooser.showPreviousPage();
            
        } else if(event.keyCode == 34 && this.chooser.enablePager) {    // PAGE DOWN
            // don't do the default action on the textfield
            event.preventDefault();
            this.chooser.showNextPage();
            
        } else if(event.keyCode == 36 && this.chooser.enablePager) {    // HOME
            event.preventDefault();
            this.chooser.showFirstPage();
            
        } else if(event.keyCode == 35 && this.chooser.enablePager) {    // END
            event.preventDefault();
            this.chooser.showLastPage();

        } else if (event.keyCode == 13 || event.keyCode == 14) {    // ENTER & RETURN
            if(this.chooser.results.hidden) {
                this.chooser.results.show();
            } else {
                this.chooser.onChoose(this.chooser.results.entries[this.chooser.results.entryFocus]);
                this.chooser.results.hide();
            }

        } else if(event.keyCode == 46) {    // DELETE
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
        //this.DOMNode.focus();
        if(this.chooser.results.hidden) {
            this.chooser.results.show();
        } else {
            this.chooser.results.hide();
        }
        event.preventDefault();
    }
    
    this._onKeyUpTimeout = function() {
        if(this.currentQuery != this.DOMNode.value) {
            this.currentQuery = this.DOMNode.value
            this.chooser.currentPage = 0;
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
    
    this.getEntryByID = function(id) {
        try {
            for(var i=0; i<= this.entries.length; i++) {
                if(this.entries[i].id == id) {
                    return this.entries[i];
                }
            }
        } catch(e) {
            return false;
        }
    }
    
    this.focusEntryByIndex = function(entry) {
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
                this.focusEntryByIndex(i);
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
    
    this.e_onMouseDown = function(e) {
        if (e.preventDefault) {
            e.preventDefault();
        }
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

