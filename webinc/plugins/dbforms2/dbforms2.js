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

dbforms2_saveCount = 0;

DBFORMS2_EVENT_FORM_LOAD_PRE        = 1;
DBFORMS2_EVENT_FORM_LOAD_POST       = 2;
DBFORMS2_EVENT_FORM_DELETE_PRE      = 3;
DBFORMS2_EVENT_FORM_DELETE_POST     = 4;
DBFORMS2_EVENT_FORM_RELOAD_PRE      = 5;
DBFORMS2_EVENT_FORM_RELOAD_POST     = 6;
DBFORMS2_EVENT_FORM_SAVE_PRE        = 7;
DBFORMS2_EVENT_FORM_SAVE_POST       = 8;
DBFORMS2_EVENT_FORM_NEW_PRE         = 9;
DBFORMS2_EVENT_FORM_NEW_POST        = 10;
DBFORMS2_EVENT_FORM_SAVEASNEW_PRE   = 11;
DBFORMS2_EVENT_FORM_SAVEASNEW_POST  = 12;

DBFORMS2_EVENT_PARENTFORM_SAVE      = 20;
DBFORMS2_EVENT_PARENTFORM_NEW       = 21;

DBFORMS2_DEBUGLOG_ENABLED = true;

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

    // FIXME: this should be per form
    this.liveSelectRootURI = formConfig['liveSelectRootURI'];
    
    // set up the status text
    this.statusTextDiv = document.getElementById('statustext');
    dbforms2.statusText('Initializing...');

    // create the main form
    this.mainform = dbforms2.getFormByConfig(formConfig);

    this.mainform.toolbar = new dbforms2_toolbar();
    this.mainform.initToolbar();
    this.mainform.toolbar.lockAllButtons();

	// check if an id has been passed via url and load the corresponding entry if so
    this.parseUrlParams();
	if (this.urlParams['id']) {
		this.mainform.loadFormDataByID(this.urlParams['id']);
	}
	
    // set up a live select for the main chooser
    var cf_onLiveChoose = new ContextFixer(this.loadEntryByID, this);
    this.chooser = new dbforms2_liveselect();
    this.chooser.onChooseAction = cf_onLiveChoose.execute;
    
    /* FIXME: This makes use of the delete key, which is already used in the queryfield to
       edit the currenty query ... 
       
    var wev = new ContextFixer(this.deleteEntryByID, this);
    this.chooser.onDeleteAction = wev.execute;
    */
    
    this.chooser.showSelectedEntry = true;
    this.chooser.enablePager = true;

    this.chooser.dataURI = formConfig['chooserDataURI'];
    this.chooser.init(document.getElementById('chooserQueryField'), document.getElementById('chooserResults'), document.getElementById('chooserImg'), document.getElementById('chooserPagerDisplay'));

    // we're ready to go now.
    this.mainform.toolbar.unlockButton('save');
    this.mainform.toolbar.unlockButton('new');
    dbforms2.statusText('Ready.');
    if (window.console  && window.console.firebug ) {
        var mozillaRvMinorVersion = navigator.userAgent.match(/rv:1.([[0-9a-z\.]*)/)[1];
        // 9 = 1.9 = Firefox 3.0
        // Firefox 3.1 has Version 9.1 = 1.9.1
        // Firefox 2.0 has Version 8
        if (parseFloat(mozillaRvMinorVersion) == 9) {
            dbforms2.statusText('There are known issues with Firefox 3.0 and Firebug. Please disable it!',true);
        }
    }
    dbforms2_log.log('dbforms2 initialized');
}

dbforms2.statusText = function(msg, error) {
    this.statusTextDiv.childNodes[0].data = msg;
	if (error) {
		this.statusTextDiv.style.backgroundColor = "#ff7777";
	} else {
		this.statusTextDiv.style.backgroundColor = null;
	}
}

dbforms2.parseUrlParams = function () {
	this.urlParams = new Array ();
	var params = window.location.search.substring(1, window.location.search.length).split("&");
	var i = 0;
	for (var param in params) {
		var p = params[param].split("=");
		if (typeof p[1] != "undefined") {
			this.urlParams[p[0]] = p[1];
		} 
	}
}

dbforms2.loadEntryByID = function(entry) {
    this.mainform.loadFormDataByID(entry.id);
}

dbforms2.deleteEntryByID = function(entry) {
    this.mainform.deleteEntryByID(entry.id);
}

dbforms2.getFormByConfig = function(formConfig) {
    var form = new dbforms2_form();
    form.init(formConfig);
    return form;
}
