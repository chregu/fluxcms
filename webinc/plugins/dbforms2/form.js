function dbforms2_form() {
    
    this.fields = new Array();
    this.name = '';
    this.currentID = 0;
    this.idField = 'id';
    this.dataURI = '';
    this.formData = new dbforms2_formData();
    this.transport = new dbforms2_transport();
    this.formDiv = null;
    this.tablePrefix = '';
    this.transportTimeout = null;
    this.lastFocus = null;
    
    this.eventHandlers = new Array();

    this.init = function(fields) {
        var fieldID;
        for(fieldID in fields) {
            if(fields[fieldID]['isGroup']) {

                // field is a group
                this.fields[fieldID] = this.initGroup(fieldID, fields[fieldID]);
            } else {

                // field is a regular field
                this.fields[fieldID] = this.initField(fieldID, fields[fieldID]);
            }
        }
        
        this.formData.tablePrefix = this.tablePrefix;
        this.resetValues();
        this.focusFirstField();
    }
    
    this.initField = function(fieldID, fieldConfig) {

        fieldType = fieldConfig['type'];
        fieldNode = document.getElementById('field_' + fieldID);
        fieldClass = 'dbforms2_field_' + fieldType;

        try {
            eval("field = new "+fieldClass+"();");
        } catch (e) {
            field = new dbforms2_field(fieldNode); 
        }

        field.defaultValue = fieldConfig['default'];
        field.id = fieldID;
        field.form = this;
        field.init(fieldNode);
        _registerObj(fieldNode.id, field);
        return field;
    }
    
    this.initGroup = function(groupID, groupConfig) {
        groupType = groupConfig['type'];
        groupClass = 'dbforms2_group_' + groupType;

        eval("group = new "+groupClass+"();");
        group.id = groupID;
        group.init();
        
        for(fieldID in groupConfig['fields']) {
            field = this.initField(fieldID, groupConfig['fields'][fieldID]);
            group.fields[fieldID] = field;
            //this.fields[fieldID] = field;
        }
        return group;
        
    }
    
    this.loadFormDataByID = function(id) {
        var uri =  this.dataURI + '?id=' + id;
        this.disable();
        dbforms2.statusText('Loading Data ...');
        
        var wrappedCallback = new ContextFixer(this._dataLoadedCallback, this);
        this.transport.onLoadCallback = wrappedCallback.execute;
        
        this.startTransportTimeout();
        this.transport.loadXML(uri);
    }
    
    this.saveFormData = function() {
        var uri =  this.dataURI;
        this.saveFocus();
        this.disable();
        dbforms2.statusText('Saving Data ...');
        
        this.formData.formName = this.name;
        // set current id

        for (fieldID in this.fields) {
            field = this.getFieldByID(fieldID);
            value = field.getValue();
            this.formData.setValueByFieldID(fieldID, value);
        }
        this.formData.setValueByFieldID(this.idField, this.currentID);

        var wrappedCallback = new ContextFixer(this._dataSavedCallback, this);
        this.transport.onSaveCallback = wrappedCallback.execute;
        this.startTransportTimeout();
        this.transport.saveXML(uri, this.formData.getXML());
    }
	
	this.deleteEntry = function() {
		
		if (this.currentID == 0) {
			alert("No entry to be deleted...");
			return;
		}

		if (!confirm("Are you sure you want to delete this entry?")) {
			return;
		}
		
		var uri =  this.dataURI;
		
		dbforms2.toolbar.lockAllButtons();
        this.disable();
		dbforms2.statusText('Deleting Data ...');
		
		this.formData.formName = this.name;
		
		for (fieldID in this.fields) {
			field = this.getFieldByID(fieldID);
			value = field.getValue();
			this.formData.setValueByFieldID(fieldID, value);
		}
		this.formData.setValueByFieldID(this.idField, this.currentID);
		
		var wrappedCallback = new ContextFixer(this._dataDeletedCallback, this);
		this.transport.onSaveCallback = wrappedCallback.execute;
		var xml = this.formData.getXML();
		xml.documentElement.setAttribute("delete","true");
        this.startTransportTimeout();
		this.transport.saveXML(uri, xml);
	}
	

    this.saveFormDataAsNew = function() {
        // reset current id and then save => will create a new record
        this.currentID = 0;
        this.saveFormData();
    }
    
    this.createNewEntry = function() {
        this.currentID = 0;
        this.resetValues();
        dbforms2.toolbar.lockAllButtons();
        dbforms2.toolbar.unlockButtons(['save', 'new']);
        window.scrollTo(0,0);
        this.focusFirstField();
    }
    
    this.saveFocus = function() {
        if(this.lastFocus)
            this.savedFocus = this.lastFocus;
    }
    
    this.restoreFocus = function() {
        if(this.savedFocus) 
            this.savedFocus.focus();
    }
    
    this.focusFirstField = function() {
        for(fieldID in this.fields) {
            this.fields[fieldID].focus();
            break;
        }
    }
    
    this.updateFocus = function(field) {
        this.lastFocus = field;
    }
    
    this.enable = function() {
        for (fieldID in this.fields) {
            this.getFieldByID(fieldID).enable();
        }
    }
    
    this.disable = function() {
        dbforms2.toolbar.lockAllButtons();
        for (fieldID in this.fields) {
            field = this.getFieldByID(fieldID);
            field.disable();
        }
    }
    
    this.resetValues = function() {
        for (fieldID in this.fields) {
            field = this.getFieldByID(fieldID);
            field.resetValue();
        }
    }
    
    this.getFieldByID = function(id) {
        return this.fields[id];
    }
    
    this.loadFieldValuesByXML = function(xml) {
        this.formData.tablePrefix = this.tablePrefix;
        this.formData.setXML(xml);
        for (fieldID in this.fields) {
            field = this.getFieldByID(fieldID);
            value = this.formData.getValueByFieldID(fieldID);
            field.setValue(value);
        }
        this.currentID = this.formData.getValueByFieldID(this.idField);
    }
    
    this._dataLoadedCallback = function() {
        this.stopTransportTimeout();
        this.loadFieldValuesByXML(this.transport.data);
        
        // call correspondig event handler on successsfull save
        if(this.eventHandlers['onLoadJS']) {
            eval(this.eventHandlers['onLoadJS']);
        }
    
        window.scrollTo(0, 0);
        this.enable();
        dbforms2.toolbar.unlockAllButtons();
        this.focusFirstField();
        
        dbforms2.statusText('Data loaded. (id = ' + this.currentID + ')');
    }

    this._dataSavedCallback = function(response) {
        this.stopTransportTimeout();
        if(response.isError()) {
            alert("error saving data!\n---\nReason: "+response.getResponseText()+"\nCode: "+response.getResponseCode());
            
            dbforms2.statusText('Error saving data: ' + response.getResponseText());
            dbforms2.toolbar.unlockButtons(['save', 'new']);

        } else {
            dbforms2.statusText('Data saved: ' + response.getResponseText());
            
            // reload the returned data
            this.loadFieldValuesByXML(response.responseData);
            
            // reload chooser results
            dbforms2.chooser.reloadCurrentQuery();
            
            // call correspondig event handler on successsfull save
            if(this.eventHandlers['onSaveJS']) {
				eval(this.eventHandlers['onSaveJS']);
            }

            dbforms2.toolbar.unlockAllButtons();
        }
        this.enable();
        this.restoreFocus();
    }
	
	this._dataDeletedCallback = function(response) {
        this.stopTransportTimeout();
		
		if(response.isError()) {
			alert("error saving data!\n---\nReason: "+response.getResponseText()+"\nCode: "+response.getResponseCode());
			dbforms2.statusText('Error deleting data: ' + response.getResponseText());

        } else {
			dbforms2.statusText('Data deleted: ' + response.getResponseText());
			this.currentID = response.savedID;
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
	
    this.startTransportTimeout = function() {
        if(this.transportTimeout) {
            dbforms2_log.log('killed an old transport timeout!');
            this.stopTransportTimeout();
        }
        
        var wrappedCallback = new ContextFixer(this._transportTimeoutCallback, this);
        this.transportTimeout = window.setTimeout(wrappedCallback.execute, 10000);
        
    }
    
    this.stopTransportTimeout = function() {
        window.clearTimeout(this.transportTimeout);
    }
    
    this._transportTimeoutCallback = function(action) {
        alert('Timeout while trying to communicate with the server.');
    }

}

