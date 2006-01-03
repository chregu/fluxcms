/**  
 *  These globals are used to register objects which are called by HTML 
 *  elements later on.
 *
 *  globalObj is an array containing objects and their ids. The id is used 
 *  as the key to access the object.
 *
 *  To attach an action to a HTML element the following syntax may be used:
 *  <a href="#" onclick="dbforms2_globalObj[this.id].onClickHandler();"/>
 */    

dbforms2_globalObj = new Array();
dbforms2_objID = 1;

/**
 *  Get the next available id to register a global object.
 *
 */
function dbforms2_getID() {
    return 'id'+dbforms2_objID++;
}

/**
 *  Use this method to register your object within dbforms2_globalObj.
 *
 */
function _registerObj(id, obj) {
    dbforms2_globalObj[id] = obj;
}

dbforms2 = function() {}

dbforms2.init = function(formConfig) {
    // set up the log
    dbforms2_log.init();
    dbforms2_log.log('--');
    dbforms2_log.log('dbforms2 initializing...');

    // initialize some globals
    this.currentID = 0;
    this.dataDocument = null;
    this.dataDocumentLoaded = null;
    this.liveSelectRootURI = formConfig['liveSelectRootURI'];
    
    // set data URI and get the form and status line div
    this.dataURI = formConfig['dataURI'];
    this.formDiv = document.getElementById('form');
    this.statusTextDiv = document.getElementById('statustext');
    dbforms2.statusText('Initializing...');

    // set up the global toolbar
    this.toolbar = new dbforms2_toolbar();
    this.toolbar.setButton('save', document.getElementsByName('button_save')[0]);
    this.toolbar.setButton('new', document.getElementsByName('button_new')[0]);
    this.toolbar.setButton('saveasnew', document.getElementsByName('button_saveasnew')[0]);
    this.toolbar.setButton('delete', document.getElementsByName('button_delete')[0]);
    this.toolbar.setButton('reload', document.getElementsByName('button_reload')[0]);
    this.toolbar.lockAllButtons();
    
    // create the global form
    this.form = new dbforms2_form();
    this.form.dataURI = formConfig['dataURI'];
    this.form.formDiv = this.formDiv;
    this.form.tablePrefix = formConfig['tablePrefix'];
    this.form.name = formConfig['name'];
    this.form.init(formConfig['fields']);
    
    // set client-side event handlers
    if(formConfig['onSaveJS']) {
        this.form.eventHandlers['onSaveJS'] = formConfig['onSaveJS'];
    }
    if(formConfig['onLoadJS']) {
        this.form.eventHandlers['onLoadJS'] = formConfig['onLoadJS'];
    }

	// check if an id has been passed via url and load the corresponding entry if so
    this.parseUrlParams();
	if (this.urlParams['id']) {
		this.form.loadFormDataByID(this.urlParams['id']);
	}
	
    // set up a live select for the main entry chooser
    var cf_onLiveChoose = new ContextFixer(this.loadEntryByID, this);
    this.chooser = new dbforms2_liveselect();
    this.chooser.onChooseAction = cf_onLiveChoose.execute;
    this.chooser.dataURI = formConfig['chooserDataURI'];
    this.chooser.init(document.getElementById('chooserQueryField'), document.getElementById('chooserResults'), document.getElementById('chooserImg'));

    // we're ready to go now.
    this.toolbar.unlockButton('save');
    this.toolbar.unlockButton('new');
    dbforms2.statusText('Ready.');
    dbforms2_log.log('dbforms2 initialized');

}

dbforms2.statusText = function(msg) {
    this.statusTextDiv.childNodes[0].data = msg;
}

dbforms2.parseUrlParams = function () {
	this.urlParams = new Array ();
	var params = window.location.search.substring(1,window.location.search.length).split("&");
	var i = 0;
	for (var param in params) {
		var p = params[param].split("=");
		if (typeof p[1] != "undefined") {
			this.urlParams[p[0]] = p[1];
		} 
	}
}

dbforms2.loadEntryByID = function(entry) {
    this.form.loadFormDataByID(entry.id);
}

