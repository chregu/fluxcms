/*****************************************************************************
 *
 * Copyright (c) 2003-2004 Kupu Contributors. All rights reserved.
 *
 * This software is distributed under the terms of the Kupu
 * License. See LICENSE.txt for license text. For a list of Kupu
 * Contributors see CREDITS.txt.
 *
 *****************************************************************************/

// $Id: kupupostsupport.js 4105 2004-04-21 23:56:13Z guido $

function SimpleLogger() {
    this.log = function(message, severity) {
        /* log a message */
        if (severity > 1) {
            alert("Error: " + message);
        } else if (severity == 1) {
            alert("Warning: " + message);
        } else {
            window.status = "Log message: " + message;
        }
    };
};

function KupuMultiEditor(documents, config, logger) {
    this._documents = documents;
    this.config = config; // an object that holds the config values
    this.log = logger; // simple logger object
    this.tools = {}; // mapping id->tool
    this._currentDocument = documents[0];
    
    this._initialized = false;

    // some properties to save the selection, required for IE to remember where 
    // in the iframe the selection was
    this._previous_range = null;
    this._saved_selection = null;

    this.init = function() {
        for (var i=0; i < this._documents.length; i++) {
            this._addEventHandler(this._documents[i].getDocument(), "focus", this.setCurrentDocument, this);
        };
        this.initialize();
    };

    this.setCurrentDocument = function(event) {
        // find the current document
        for (var i=0; i < this._documents.length; i++) {
            if (this._documents[i].getDocument() === event.target) {
                this._currentDocument = this._documents[i];
            };
        };
        window.status = 'setting current document to ' + this._currentDocument.editable.name;
    };

    this.getDocument = function() {
        window.status = this._currentDocument.editable.name;
        return this._currentDocument;
    };

    this.prepareForm = function() {
        if (!this._initialized) {
            alert('Not initialized');
            return;
        };
        this._initialized = false;

        try {
            this.logMessage("Starting HTML cleanup");
            var xhtmldoc = Sarissa.getDomDocument();
            for (var i=0; i < this._documents.length; i++) {
                var doc = this._documents[i];
                var transform = this._convertContentToXHTML(xhtmldoc,
                                doc.getDocument().documentElement);
                var xml = null;
                if (this.config.bodies_only) {
                    xml = transform.getElementsByTagName('body')[0].xml;
                } else {
                    xml = '<html>' + 
                            transform.getElementsByTagName('head')[0].xml +
                            transform.getElementsByTagName('body')[0].xml +
                            '</html>';
                };
                doc.input.setAttribute('value', xml);
            };
            this.logMessage("Cleanup done");
        } catch(e) {
            this.logMessage("Exception in cleanup: " + e.toString());
            this._initialized = true;
        };
    };

    this._setDesignModeWhenReady = function() {
        for (var i=0; i < this._documents.length; i++) {
            this._setDesignModeLoop(this._documents[i]);
        };
    };

    this._setDesignModeLoop = function(document) {
        try {
            document.getDocument().designMode = "On";
            document.execCommand("undo");
            // XXX should be set from conf
            document.execCommand("useCSS", true);
            this._initialized = true;
        } catch(e) {
            timer_instance.registerFunction(this, this._setDesignModeLoop, 100, document);
        };
    };
};

KupuMultiEditor.prototype = new KupuEditor;

function KupuStarter(number_of_editors) {
    /* a registry for iframes, will be used when kupu is starting

        register an iframe to this registry (using this.registerEditorFrame)
        and kupu will turn it into an editor
    */
    this._documents = new Array();
    this._loaded = 0;
    this._kupu = null;
    this._numeditors = number_of_editors;
    
    this.registerEditorFrame = function(iframe) {
        /* register an iframe to the kupu system
        
            the iframe will become an editor and an input field will
            be generated so it will become part of the nearest parent
            form
        */
        this._documents.push(new KupuDocument(iframe));
        this._loaded++;
        if (this._loaded >= this._numeditors) {
            this._startInit();
        };
    };

    this.startKupu = function(logger, use_css, bodies_only) {
        /* this creates the kupu editor object

            should be called in the body's onload handler
        */
        var conf = {'use_css': use_css,
                    'bodies_only': bodies_only};

        // create the inputs
        for (var i=0; i < this._documents.length; i++) {
            var doc = this._documents[i];
            var iframe = doc.editable;
            var input = document.createElement('input');
            input.type = "hidden";
            input.name = doc.editable.getAttribute('name');
            // make it part of the form
            iframe.parentNode.insertBefore(input, iframe);
            // and set a reference on the document object
            doc.input = input;
        };
        
        this._kupu = new KupuMultiEditor(this._documents, conf, logger);

        var ui = new KupuUI('kupu-tb-styles');
        this._kupu.registerTool('ui', ui);
        
        return this._kupu;
    };

    this.getKupu = function() {
        return this._kupu;
    };
     
    this.getDocuments = function() {
        return this._documents;
    };
    
    this._startInit = function() {
        /* initialize the editor

            this has to be done polling since we're not 100% sure if the editor 
            is ready
        */
        if (!this._kupu) {
            timer_instance.registerFunction(this, this._startInit, 10);
        };
        try {
            this._kupu.init();
        } catch(e) {
            timer_instance.registerFunction(this, this._startInit, 10);
        };
    };
};

