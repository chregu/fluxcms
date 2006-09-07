function dbforms2_form() {
    
    this.fields = new Array();
    this.forms = new Array();
    this.name = '';
    this.currentID = 0;
    this.insertID = 0;
    this.idField = 'id';
    this.dataURI = '';
    this.formData = new dbforms2_formData();
    this.transport = new dbforms2_transport();
    this.formDiv = null;
    this.tablePrefix = '';
    this.transportTimeout = null;
    this.lastFocus = null;
    this.parentForm = null;
    this.toolbar = null;

    this.thisidfield = null;
    this.thatidfield = null;
    
    this.eventHandlers = new Array();
    this.internalEventHandlers = new Array();
    
    this.changed = false;
    
    /**
     *  Initializes the form.
     *
     */
    this.init = function(formConfig) {
        
        this.dataURI = formConfig['dataURI'];
        this.liveSelectRootURI = formConfig['liveSelectRootURI'];
        this.listViewRootURI = formConfig['listViewRootURI'];
        this.tablePrefix = formConfig['tablePrefix'];
        this.name = formConfig['name'];

        this.relationtype = formConfig['relationtype'];
        this.thisidfield = formConfig['thisidfield'];
        this.thatidfield = formConfig['thatidfield'];
        
        var fieldID;
        var fields = formConfig['fields'];
        for(fieldID in fields) {
            
            if(fields[fieldID]['isGroup']) {
                // field is a group
                this.fields[fieldID] = this.initGroup(fieldID, fields[fieldID]);
            
            } else if(fields[fieldID]['isForm']) {
                //dbforms2_log.log(fieldID + ' is a form.');
                var form = this.initForm(fieldID, fields[fieldID]['config']);
                this.fields[fieldID] = form;
                this.forms[fieldID] = form;
            
            } else {
                //dbforms2_log.log(fieldID + '.init()...');
                // field is a regular field
                this.fields[fieldID] = this.initField(fieldID, fields[fieldID]);
            }
        }
        
        this.formData.tablePrefix = this.tablePrefix;
        this.resetValues();
        this.focusFirstField();
    }
    
    /**
     *  Initializes a newly created field.
     *
     */
    this.initField = function(fieldID, fieldConfig) {

        fieldType = fieldConfig['type'];
        fieldNode = document.getElementById(this.name + '_' + fieldID);
        fieldClass = 'dbforms2_field_' + fieldType;

        try {
            eval("field = new "+fieldClass+"();");
        } catch (e) {
            field = new dbforms2_field(fieldNode); 
        }

        field.defaultValue = fieldConfig['default'];
        field.id = fieldID;
        field.form = this;
        field.type = fieldType;
        field.init(fieldNode);
        _registerObj(fieldNode.id, field);
        return field;
    }
    
    /**
     *  Initializes a newly created group.
     *
     */
    this.initGroup = function(groupID, groupConfig) {
        groupType = groupConfig['type'];
        groupClass = 'dbforms2_group_' + groupType;

        eval("group = new "+groupClass+"();");
        group.id = groupID;
        group.init();
        
        for(fieldID in groupConfig['fields']) {
            field = this.initField(fieldID, groupConfig['fields'][fieldID]);
            group.fields[fieldID] = field;
        }
        return group;
        
    }
    
    /**
     *  Initializes a newly created subform.
     *
     */
    this.initForm = function(formID, formConfig) {
        form = dbforms2.getFormByConfig(formConfig);
        form.parentForm = this;
        
        form.toolbar = new dbforms2_toolbar();
        form.initToolbar();
        
        return form;
    }

    // field interface ...
    /**
     *  Sets the current value. (Field interface)
     *
     */
    this.setValue = function(value) {
    }
    
    /**
     *  Sets the current value.
     *
     */
    this.getValue = function() {
    }
    
    /**
     *  Resets the value to the default value.
     *
     */
    this.resetValue = function() {
        for(fieldID in this.fields) {
            this.fields[fieldID].resetValue();
        }
    }
    
    /**
     *  Tells whether this field is valid.
     *
     */
    this.isValid = function() {
        return true;
    }
    
    /**
     *  Visually enable this field.
     *
     */
    this.enable = function() {
    }
    
    /**
     *  Visually disable this field.
     *
     */
    this.disable = function() {
    }
    
    /**
     *  Visually show this field.
     *
     */
    this.show = function() {
    }
    
    /**
     *  Visually hide this field.
     *
     */
    this.hide = function() {
    }
    
    /**
     *  Tells whether the contents of this field has changed.
     *
     */
    this.hasChanged = function() {

        if(this.changed == true) {
            return true;
        } 

        // check all fields for changes
        for (fieldID in this.fields) {
            if(this.fields[fieldID].hasChanged() == true) {
                return true;
            }
        }
        
        // check all subforms for changes
        for (formID in this.forms) {
            if(this.forms[formID].hasChanged() == true) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     *  Resets the changed property of this field.
     *
     */
    this.resetChanged = function() {
    }
    
    // .. / field interface
    
    // used by subforms only
    /**
     *  Used by parent forms to set the current id of the parent form.
     *
     */
    this.setParentFormId = function(id) {
        this.resetValues();
        this.currentID = 0;
        for (fieldID in this.fields) {
            field = this.getFieldByID(fieldID);
            field.setParentFormId(id);
        }
    }
    
    /**
     *  Registers an internal handler for the given event.
     *
     */
    this.registerInternalEventHandler = function(event, ctx, handler) {
        if(this.internalEventHandlers[event] == undefined) {
            this.internalEventHandlers[event] = new Array();
        }
        var eh = new Array();
        eh['context'] = ctx;
        eh['handler'] = handler;
        this.internalEventHandlers[event].push(eh);
    }
    
    /**
     *  Calls all internal handlers for the given event.
     *
     */
    this.callInternalEventHandlers = function(event) {
        if(this.internalEventHandlers[event] != undefined) {
            for(e in this.internalEventHandlers[event]) {
                var handler = this.internalEventHandlers[event][e];
                handler['handler'].apply(handler['context']);
            }
        }
        
    }
    
    /**
     *  Initializes the toolbar of this form.
     *
     */
    this.initToolbar = function() {
        this.toolbar.setButton('save', document.getElementById('tb_'+this.name+'_save'));
        var wev = new bx_helpers_contextfixer(this.e_save_click, this);
        this.toolbar.addButtonEventHandler('save', wev.execute);

        this.toolbar.setButton('new', document.getElementById('tb_'+this.name+'_new'));
        var wev = new bx_helpers_contextfixer(this.e_new_click, this);
        this.toolbar.addButtonEventHandler('new', wev.execute);

        this.toolbar.setButton('saveasnew', document.getElementById('tb_'+this.name+'_saveasnew'));
        var wev = new bx_helpers_contextfixer(this.e_saveasnew_click, this);
        this.toolbar.addButtonEventHandler('saveasnew', wev.execute);

        //if(this.parentForm == null) {
            this.toolbar.setButton('delete', document.getElementById('tb_'+this.name+'_delete'));
            var wev = new bx_helpers_contextfixer(this.e_delete_click, this);
            this.toolbar.addButtonEventHandler('delete', wev.execute);
        //}

        this.toolbar.setButton('reload', document.getElementById('tb_'+this.name+'_reload'));
        var wev = new bx_helpers_contextfixer(this.e_reload_click, this);
        this.toolbar.addButtonEventHandler('reload', wev.execute);
        
    }
    
    /**
     *  Requests a new id from the server for this form.
     *
     */
    this.requestNewId = function() {
        if(this.currentID != 0) {
            return false;
        }
        
		this.toolbar.lockAllButtons();
        this.disable();
		
		this.formData.formName = this.name;
        
		var wev = new ContextFixer(this._dataGetNewIdCallback, this);
		this.transport.onSaveCallback = wev.execute;
        
		var xml = this.formData.getXML();
		xml.documentElement.setAttribute('getnewid', 'true');
		
        response = this.transport.saveXMLSync(this.dataURI, xml);
        var newID = 0;
        if(!response.isError()) {
            newID = response.savedID;
        }

        this.enable();
        // save the new ID for later but don't overwrite currentID
        this.insertID = newID;
        return newID;
        
    }
    
    /**
     *  Loads an entry specified by the given id.
     *
     */
    this.loadFormDataByID = function(id) {
        
        if(id == 0 || id == null) {
            return false;
        }
    
        this.callInternalEventHandlers(DBFORMS2_EVENT_FORM_LOAD_PRE);

        var uri =  this.dataURI + '?id=' + id;
        this.disable();
        dbforms2.statusText('Loading Data ...');
        
        var wrappedCallback = new ContextFixer(this._dataLoadedCallback, this);
        this.transport.onLoadCallback = wrappedCallback.execute;
        
        this.startTransportTimeout();
        this.callInternalEventHandlers(DBFORMS2_EVENT_FORM_LOAD_PRE);
        this.transport.loadXML(uri);
    }
    
    /**
     *  Saves the current form data.
     *
     */
    this.saveFormData = function(fromLastChild) {
        
        dbforms2_log.log( this.name + '.saveFormData()');

        if(this.parentForm == null && (typeof fromLastChild  == 'undefined')) {

            var hasSaved = false;
            // first save all child forms
            for (var formID in this.forms) { 	 
                this.forms[formID].saveFormData();
                hasSaved = true;
            }
        
            // return if this is the parent form, because the last child callback
            // will call this method again. Don't do this, if none of the child forms
            // have started a save.
            if(hasSaved !== false) {
                return;
            }
            
        }
        
        dbforms2_log.log('saving ' + this.name);
        if(!this.hasChanged()) {
            dbforms2_log.log(this.name + ' has no changes');
            return false;
        }
        
        if(this.parentForm != null) {
            dbforms2_saveCount++;
        }        
        
        var uri =  this.dataURI;
        
        this.callInternalEventHandlers(DBFORMS2_EVENT_FORM_SAVE_PRE);
        this.formData.formName = this.name;

        for (fieldID in this.fields) {
            field = this.getFieldByID(fieldID);
            value = field.getValue();
            this.formData.setValueByFieldID(fieldID, value);
        }
        // set current id
        this.formData.setValueByFieldID(this.idField, this.currentID);
        
        // if this is a subform, set the corresponding relation field ...
        if(this.parentForm != null) {
            
            if(this.thatidfield != '' && this.thisidfield == '') {

                // relation: 1 -> n
                /*
                    Gets the current ID from the parent form.
                    - if the parent form doesn't have one, a new is generated
                    - if the parent form has an insertID set, it is used
                */
                var parentID = 0;
                if(this.parentForm.currentID == 0 && this.parentForm.insertID == 0) {
                    parentID = this.parentForm.requestNewId();
                } else if(this.parentForm.currentID == 0 && this.parentForm.insertID != 0) {
                    parentID = this.parentForm.insertID;
                } else {
                    parentID = this.parentForm.currentID;
                }
                
                if(parentID != 0 && this.thatidfield != '') {
                    this.formData.setValueByFieldID(this.thatidfield, parentID);
                } else {
                    return false;
                }
                
            } else if(this.thatidfield == '' && this.thisidfield != '') {
                // relation: n -> 1
                
            } else if(this.thatidfield != '' && this.thisidfield != '') {
                // relation: n -> m
            }
            
            
        }
        
        this.saveFocus();
        this.disable();
        dbforms2.statusText('Saving Data ...');

        var wrappedCallback = new ContextFixer(this._dataSavedCallback, this);
        this.transport.onSaveCallback = wrappedCallback.execute;

		var xml = this.formData.getXML();
		
        if(this.currentID == 0 && this.insertID != 0) {
            xml.documentElement.setAttribute('insertid', this.insertID);
        }
        
        this.startTransportTimeout();
		this.transport.saveXML(uri, xml);
        
        // returning true means this form has started to save
        return true;
    }

    /**
     *  Resets the current id and then saves the form's data. This creates 
     *  a new entry.
     *
     */
    this.saveFormDataAsNew = function() {
        // reset current id and then save => will create a new record
        this.currentID = 0;
        this.changed = true;
        this.saveFormData();
    }
    
    /**
     *  Delete the entry specified by the given id.
     *
     */
    this.deleteEntryByID = function(id) {
		var uri =  this.dataURI;

        this.callInternalEventHandlers(DBFORMS2_EVENT_FORM_DELETE_PRE);
		
		this.toolbar.lockAllButtons();
        this.disable();
		dbforms2.statusText('Deleting Data ...');
		
		this.formData.formName = this.name;
		
		for (fieldID in this.fields) {
			field = this.getFieldByID(fieldID);
			value = field.getValue();
			this.formData.setValueByFieldID(fieldID, value);
		}
		this.formData.setValueByFieldID(this.idField, id);
		
		var wrappedCallback = new ContextFixer(this._dataDeletedCallback, this);
		this.transport.onSaveCallback = wrappedCallback.execute;
		var xml = this.formData.getXML();
		xml.documentElement.setAttribute("delete","true");
        this.startTransportTimeout();
		this.transport.saveXML(uri, xml);
    }
	
    /**
     *  Deletes the currently edited entry.
     *
     */
	this.deleteEntry = function() {
		if (this.currentID == 0) {
			return false;
		}
		if (!confirm("Do you really want to delete this entry?")) {
			return;
		}
        this.deleteEntryByID(this.currentID);
	}
	
    /**
     *  Creates a new entry.
     *
     */
    this.createNewEntry = function() {
        this.callInternalEventHandlers(DBFORMS2_EVENT_FORM_NEW_PRE);
        this.currentID = 0;
        this.resetValues();

        dbforms2.chooser.queryField.clearCurrentEntry();
        
        // notify all child forms' fields
        for (var fieldID in this.forms) {
            this.fields[fieldID].createNewEntry();
            this.fields[fieldID].callInternalEventHandlers(DBFORMS2_EVENT_PARENTFORM_NEW);
        }

        this.toolbar.lockAllButtons();
        this.toolbar.unlockButtons(['save', 'new']);
        this.callInternalEventHandlers(DBFORMS2_EVENT_FORM_NEW_POST);

        dbforms2.statusText('Created a new entry.');

        //window.scrollTo(0,0);
        this.focusFirstField();
    }
    
    /**
     *  Reloads the current entry.
     *
     */
    this.reloadEntry = function() {
        this.loadFormDataByID(this.currentID);
    }
    
    /**
     *  Saves the currently focused field.
     *
     */
    this.saveFocus = function() {
        if(this.lastFocus)
            this.savedFocus = this.lastFocus;
    }
    
    /**
     *  Restores the focus from a previously saved field.
     *
     */
    this.restoreFocus = function() {
        if(this.savedFocus) 
            this.savedFocus.focus();
    }
    
    /**
     *  Focuses the first field of the form.
     *
     */
    this.focusFirstField = function() {
        for(fieldID in this.fields) {
            this.fields[fieldID].focus();
            break;
        }
    }
    
    /**
     *  Saves the currently focused field. This method is called by form fields.
     *  Use saveFocus to save the currently focused field.
     *
     */
    this.updateFocus = function(field) {
        this.lastFocus = field;
    }
    
    /**
     *  Enables the form.
     *
     */
    this.enable = function() {
        for (fieldID in this.fields) {
            this.getFieldByID(fieldID).enable();
        }
    }
    
    /**
     *  Disables the form.
     *
     */
    this.disable = function() {
        this.toolbar.lockAllButtons();
        for (fieldID in this.fields) {
            field = this.getFieldByID(fieldID);
            field.disable();
        }
    }
    
    /**
     *  Resets all field values.
     *
     */
    this.resetValues = function() {
        for (fieldID in this.fields) {
            field = this.getFieldByID(fieldID);
            field.resetValue();
        }
    }
    
    /**
     *  Returns a field by its id.
     *
     */
    this.getFieldByID = function(id) {
        return this.fields[id];
    }
    
    /**
     *  Sets all field values using the given xml document.
     *
     */
    this.loadFieldValuesByXML = function(xml) {
        this.formData.tablePrefix = this.tablePrefix;
        this.formData.setXML(xml);
        for (fieldID in this.fields) {
            field = this.getFieldByID(fieldID);
            value = this.formData.getValueByFieldID(fieldID);
            field.setValue(value);
            field.resetChanged();
        }
        this.currentID = this.formData.getValueByFieldID(this.idField);
        this.insertID = 0;
    }
    
    /**
     *  Reloads all subforms.
     *
     */
    this.reloadSubForms = function() {
        for (fieldID in this.forms) {
            this.fields[fieldID].setParentFormId(this.currentID);
        }
    }
    
    /**
     *  Internal handler which is called when data is returned by the server.
     *
     */
    this._dataLoadedCallback = function() {
        this.stopTransportTimeout();
        this.loadFieldValuesByXML(this.transport.data);
        
        // call correspondig event handler on successsfull save
        if(this.eventHandlers['onLoadJS']) {
            eval(this.eventHandlers['onLoadJS']);
        }
        this.reloadSubForms();
        
        this.enable();
        this.toolbar.unlockAllButtons();
        this.focusFirstField();
        
        dbforms2.statusText('Data loaded. (id = ' + this.currentID + ')');
    }

    /**
     *  Internal handler which is called when data is returned by the server.
     *
     */
    this._dataSavedCallback = function(response) {
        //console.log(this.name + '._dataSavedCallback()');
        this.stopTransportTimeout();
        if(response.isError()) {
            alert("error saving data!\n---\nReason: "+response.getResponseText()+"\nCode: "+response.getResponseCode());
            
            dbforms2.statusText('Error saving data: ' + response.getResponseText());
            this.toolbar.unlockButtons(['save', 'new']);

        } else {
            dbforms2.statusText('Data saved. (' + response.getResponseText() + ')');
            
            // reload the returned data
            this.loadFieldValuesByXML(response.responseData);

            // reload chooser results
            dbforms2.chooser.reloadCurrentQuery();

            this.callInternalEventHandlers(DBFORMS2_EVENT_FORM_SAVE_POST);

            // call correspondig event handler on successsfull save
            if(this.eventHandlers['onSaveJS']) {
				eval(this.eventHandlers['onSaveJS']);
            }
            
            this.toolbar.unlockAllButtons();
            
            //console.log(dbforms2_saveCount);
            
            if(this.parentForm != null) {
                if(dbforms2_saveCount > 1) {
                    dbforms2_saveCount--;
                } else {
                    this.parentForm.saveFormData(true);
                }
            } else {
                dbforms2_saveCount = 0;
                this.reloadSubForms();
            }
            //console.log(dbforms2_saveCount);
            
        }
        this.enable();
        this.restoreFocus();
    }
	
    /**
     *  Internal handler which is called when data is returned by the server.
     *
     */
	this._dataDeletedCallback = function(response) {
        this.stopTransportTimeout();
		
		if(response.isError()) {
			alert("error saving data!\n---\nReason: "+response.getResponseText()+"\nCode: "+response.getResponseCode());
			dbforms2.statusText('Error deleting data: ' + response.getResponseText());

        } else {
			dbforms2.statusText('Data deleted: ' + response.getResponseText());
			this.currentID = response.savedID;
            this.callInternalEventHandlers(DBFORMS2_EVENT_FORM_DELETE_POST);

			// reload chooser results
			dbforms2.chooser.reloadCurrentQuery();
			
			// call correspondig event handler on successsfull save
			if(this.eventHandlers['onDeleteJS']) {
				eval(this.eventHandlers['onDeleteJS']);
			}
		}

		this.createNewEntry();
		this.enable();
	}
	
    /**
     *  Starts a transport timeout.
     *
     */
    this.startTransportTimeout = function() {
        if(this.transportTimeout) {
            //dbforms2_log.log('killed an old transport timeout!');
            this.stopTransportTimeout();
        }
        
        var wrappedCallback = new ContextFixer(this._transportTimeoutCallback, this);
        this.transportTimeout = window.setTimeout(wrappedCallback.execute, 10000);
        
    }
    
    /**
     *  Stops the currently running transport timeout.
     *
     */
    this.stopTransportTimeout = function() {
        window.clearTimeout(this.transportTimeout);
    }
    
    /**
     *  Callback for the transport timeout.
     *
     */
    this._transportTimeoutCallback = function(action) {
        alert('Timeout while trying to communicate with the server.');
    }
    
    /* toolbar events */
    this.e_save_click = function() {
        this.saveFormData();
    }
    
    this.e_new_click = function() {
        this.createNewEntry();
    }
    
    this.e_saveasnew_click = function() {
        this.saveFormDataAsNew();
    }
    
    this.e_delete_click = function() {
        this.deleteEntry();
    }
    
    this.e_reload_click = function() {
        this.reloadEntry();
    }


}

