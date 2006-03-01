/**
 * <p>
 * dbforms2_field
 * </p>
 * 
 */
function dbforms2_field(DOMNode) {
    var defaultValue = '';
    var id = '';
    var form = null;
    
    this.initField = function(DOMNode) {
        this.hasFocus = false;
        this.value = null;
        this.DOMNode = DOMNode;
    }
    
    this.init = function(DOMNode) {
        this.initField(DOMNode);
    }

    this.setValue = function(value) {
        this.value = value;
        this.updateDOMNodeValue();
    }
    
    this.updateDOMNodeValue = function() {
        this.DOMNode.value = this.value;
    }

    
    this.getValue = function() {
        return this.DOMNode.value;
    }
    
    this.resetValue = function() {
        this.setValue(this.defaultValue);
    }
    
    this.isValid = function() {
        return true;
    }
    
    this.enable = function() {
        this.DOMNode.disabled = false;
    }
    
    this.disable = function() {
        this.DOMNode.disabled = true;
    }
    
    this.show = function() {
        this.DOMNode.style.visibility = 'visible';
    }
    
    this.hide = function() {
        this.DOMNode.style.visibility = 'hidden';
    }
    
    this.focus = function() {
        this.DOMNode.focus();
    }
    
    this.onChange = function() {
    }
    
    /**
     * e_XXX methods are called from onXXX handlers from a HTML element
     *
     */
    this.e_onFocus = function() {
        this.DOMNode.className = 'hasfocus';
        this.hasFocus = true;
        this.form.updateFocus(this);
    }

    this.e_onBlur = function() {
        this.DOMNode.className = '';
        this.hasFocus = false;
    }
    
    this.e_onMouseOver = function() {
        this.DOMNode.className = 'hasfocus';
    }

    this.e_onMouseOut = function() {
        if(!this.hasFocus)
            this.DOMNode.className = '';
    }
    
    this.e_onChange = function() {
        this.onChange();
    }
    
}


/**
 * <p>
 * dbforms2_field_text
 * </p>
 * 
 */
function dbforms2_field_text(DOMNode) {
    this.init(DOMNode);
}

// dbforms2_field_text inherits from dbforms2_field
dbforms2_field_text.prototype = new dbforms2_field();

/**
 * <p>
 * dbforms2_field_password
 * </p>
 * 
 */
function dbforms2_field_password(DOMNode) {
    this.init(DOMNode);
}

dbforms2_field_password.prototype = new dbforms2_field();


function dbforms2_field_password_md5(DOMNode) {
    this.init(DOMNode);
}

dbforms2_field_password_md5.prototype = new dbforms2_field();


/**
 * <p>
 * dbforms2_field_checkbox
 * </p>
 * 
 */
function dbforms2_field_checkbox(DOMNode) {
    
    this.setValue = function(value) {
        if(value == 1) {
            this.DOMNode.checked = true;
        } else {
            this.DOMNode.checked = false;
        }
    }
    
    this.getValue = function() {
        if(this.DOMNode.checked == true) 
            return 1;
        
        return 0;
    }
    
    
}

// dbforms2_field_text inherits from dbforms2_field
dbforms2_field_checkbox.prototype = new dbforms2_field();

/**
 * <p>
 * dbforms2_field_color
 * </p>
 * 
 */
function dbforms2_field_color(DOMNode) {
    
    this.setValue = function(value) {
 		this.DOMNode.value = value;
		var bt = document.getElementById( "anchor_" + this.DOMNode.id );
		bt.style.backgroundColor = value;
    }
    
}

// dbforms2_field_text inherits from dbforms2_field
dbforms2_field_color.prototype = new dbforms2_field();


/**
 * <p>
 * dbforms2_field_text_area
 * </p>
 * 
 */
function dbforms2_field_text_area(DOMNode) {
}
// dbforms2_field_text inherits from dbforms2_field
dbforms2_field_text_area.prototype = new dbforms2_field();

function dbforms2_field_text_wysiwyg(DOMNode) {
    this.init = function(DOMNode) {
		this.initField(DOMNode);
	
        var oFCKeditor = new FCKeditor(this.id ) ;
        oFCKeditor.BasePath	= fckBasePath;
        
        oFCKeditor.Config['CustomConfigurationsPath'] = bx_webroot + 'webinc/plugins/dbforms2/fckconfig.js';
        oFCKeditor.ToolbarSet = 'BxCMS';
        oFCKeditor.ReplaceTextarea() ;
		
    }
	
	this.setValue = function(value) {
		// Get the editor instance that we want to interact with.
		
		if (typeof FCKeditorAPI  != "undefined") {
			var oEditor = FCKeditorAPI.GetInstance(this.id) ;
            // Set the editor contents (replace the actual one).
			
			if (oEditor.SetHTML) {
                if(value == null)
                    value = '';
				oEditor.SetHTML(value);
			} else {
				this.DOMNode.value = value;
			}
		} else {
			this.DOMNode.value = value;
		}
	}
	
	this.getValue = function(value) {
		
		// Get the editor instance that we want to interact with.
		var oEditor = FCKeditorAPI.GetInstance(this.id) ;
		
		return oEditor.GetXHTML( true );
		// Set the editor contents (replace the actual one).
	}
		
}


dbforms2_field_text_wysiwyg.prototype = new dbforms2_field();

// dbforms2_field_text inherits from dbforms2_field

/**
 * <p>
 * dbforms2_field_text_area_small
 * </p>
 * 
 */
function dbforms2_field_text_area_small(DOMNode) {
}

// dbforms2_field_text inherits from dbforms2_field
dbforms2_field_text_area_small.prototype = new dbforms2_field();

/**
 * <p>
 * dbforms2_field_select
 * </p>
 * 
 */
function dbforms2_field_select(DOMNode) {
}

// dbforms2_field_text inherits from dbforms2_field
dbforms2_field_select.prototype = new dbforms2_field();


function dbforms2_field_date(DOMNode) {
}
// dbforms2_field_text inherits from dbforms2_field
dbforms2_field_date.prototype = new dbforms2_field();

function dbforms2_field_fixed(DOMNode) {
	this.setValue = function(value) {
		var sp = document.getElementById( this.DOMNode.id + "_fixed");
		sp.innerHTML = value;
		this.value = value;
        this.updateDOMNodeValue();
	}
}

// dbforms2_field_text inherits from dbforms2_field
dbforms2_field_fixed.prototype = new dbforms2_field();

function dbforms2_field_relation_n2m(DOMNode) {

    this.init = function(DOMNode) {
        this.initField(DOMNode);

        this.divElement = document.getElementById(this.DOMNode.id+"_values");
        
        var cf_onLiveChoose = new ContextFixer(this.onLiveChoose, this);
        this.liveSelect = new dbforms2_liveselect();
        
        // init live select
        this.liveSelect.onChooseAction = cf_onLiveChoose.execute;
        this.liveSelect.dataURI = dbforms2.liveSelectRootURI + '/' + this.id;
        this.liveSelect.autoExpandResultsOnFocus = false;
        this.liveSelect.init(document.getElementById(this.id + '_lsqueryfield'), document.getElementById(this.id + '_lsresults'));
        
    }
	
	this.setValue = function(values) {
		this.resetFieldValues();
		for (var i in values) {
			this.addFieldValue(i,values[i]);
		}
	}
    
    this.onLiveChoose = function(entry) {
        this.addFieldValue(entry.id, entry.title);
    }
	
	this.addFieldValue = function(id, title) {
		if (id != 0)  {
			var div= document.createElement("div");
			var del = document.createElement("a");
            div.className = 'n2mvalue';
			del.appendChild(document.createTextNode("x"));
			del.setAttribute("style","cursor: pointer;");

            //var wev_onClick = new bx_helpers_contextfixer(this.e_onMouseDown, this);
            clickHandler = function() {
                this.parentNode.parentNode.removeChild(this.parentNode);
            }
            bx_helpers.addEventListener(del, 'click', clickHandler);
			
            //del.setAttribute("onclick"," return false");
			
			div.appendChild(del);
			div.appendChild(document.createTextNode(" "));
			
			div.appendChild(document.createTextNode(title));
			div.setAttribute("_value_id", id);
			div.setAttribute("id", this.DOMNode.id+"_value_id_"+id);
			this.divElement.appendChild(div);
		}
	}
	
	
	this.getValue = function() {
		var values = new Array();
		var child = this.divElement.firstChild;
		while (child) {
			if (child.nodeType == 1) {
				values[child.getAttribute("_value_id")] = child.childNodes[0].data;
			}
			child = child.nextSibling;
		}
		
		return values;
	}
	
	this.resetFieldValues = function() {
		this.divElement.innerHTML = "";
	}
    
    this.enable = function() {
        this.liveSelect.enable();
    }
    
    this.disable = function() {
        this.liveSelect.disable();    
    }
    
    this.focus = function() {
        this.liveSelect.focus();
    }
    
    
}
// dbforms2_field_text inherits from dbforms2_field
dbforms2_field_relation_n2m.prototype = new dbforms2_field();

function dbforms2_field_fixed_datetime(DOMNode) {
    this.init(DOMNode);
	
	this.setValue = function(value) {
		var sp = document.getElementById( this.DOMNode.id + "_fixed");
		if (sp) {
		value = value.replace(/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/,"$2/$3/$1 $4:$5:$6");
		
		var date = new Date( Date.parse(value));
		
		sp.innerHTML = date.toLocaleString();;
		}
        this.updateDOMNodeValue();
	}
}
// dbforms2_field_text inherits from dbforms2_field
dbforms2_field_fixed_datetime.prototype = new dbforms2_field_fixed();

function dbforms2_field_file(DOMNode) {
    
    var uploadDir = '';

	this.init = function(DOMNode) {
        this.initField(DOMNode);
        this.uploadDir = this.DOMNode.getAttribute('uploaddir');
        
        this.previewSmallDOMNode = document.getElementById(this.DOMNode.id + "_previewSmall");
        this.previewLargeDOMNode = document.getElementById(this.DOMNode.id + "_previewLarge");
    }

    this.setValue = function(value) {
        this.value = value;
        this.updateDOMNodeValue();
        this.updatePreviewAndTooltip();
    }

    this.updatePreviewAndTooltip = function() {
        if(dbforms2_helpers.isImage(this.value)) {
            this.previewSmallDOMNode.src = DBFORMS2_IMG_PREVIEW_SMALL_DIR + this.uploadDir + this.value;
            bx_tooltip.prepare(this.previewLargeDOMNode, DBFORMS2_IMG_PREVIEW_LARGE_DIR + this.uploadDir + this.value);
        } else {
            this.previewSmallDOMNode.src = DBFORMS2_IMG_NULLIMG;
            bx_tooltip.remove(this.previewLargeDOMNode);
        }
    }

    this.onChange = function() {
        this.value = this.DOMNode.value;
        this.updatePreviewAndTooltip();
    }
    
	this.setIframe = function() {
		
		var iframe = document.getElementById(this.DOMNode.id + "_iframe");
		if (iframe.style.display == "block") {
			this.closeIframe();
		} else {
			iframe.style.display = "block";
			iframe.DOMNode = this.DOMNode;
			iframe.onload = function() { 
				var doc = this.contentDocument;
				doc.forms.upload.action = document.location + "/upload/";
				var fu = doc.getElementById("fieldname");
				fu.value = this.DOMNode.name;
				this.onload = null;
			}
			
			iframe.contentWindow.location = bx_webroot + "webinc/plugins/dbforms2/emptyupload.html";
		}
	}
	
	this.closeIframe = function() {
		var iframe = document.getElementById(this.DOMNode.id + "_iframe")
		iframe.style.display = "none";
	}
	
}
	
dbforms2_field_file.prototype = new dbforms2_field();
 

function dbforms2_field_file_browser(DOMNode) {
    var isImage;

    this.init = function(DOMNode) {
        this.initField(DOMNode);
        this.isImage = false;
        
        if(DOMNode.getAttribute('isImage') == '1') {
            this.isImage = true;
            this.previewSmallDOMNode = document.getElementById(this.DOMNode.id + "_previewSmall");
            this.previewLargeDOMNode = document.getElementById(this.DOMNode.id + "_previewLarge");
        }
        
    }
    
    this.setValue = function(value) {
        this.value = value;
        this.updateDOMNodeValue();
        this.updatePreviewAndTooltip();
    }
    
    this.updatePreviewAndTooltip = function() {
        if(this.isImage) {
            if(dbforms2_helpers.isImage(this.value)) {
                this.previewSmallDOMNode.src = DBFORMS2_IMG_PREVIEW_SMALL_DIR + this.value;
                bx_tooltip.prepare(this.previewLargeDOMNode, DBFORMS2_IMG_PREVIEW_LARGE_DIR + this.value);
            } else {
                this.previewSmallDOMNode.src = DBFORMS2_IMG_NULLIMG;
                bx_tooltip.remove(this.previewLargeDOMNode);
            }
        }
    }
    
    this.onChange = function() {
        this.value = this.DOMNode.value;
        this.updatePreviewAndTooltip();
    }
    
}

// dbforms2_field_filebrowser inherits from dbforms2_field
dbforms2_field_file_browser.prototype = new dbforms2_field();
 


