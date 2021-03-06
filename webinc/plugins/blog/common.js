var check = 0;

function checkCategories() {
    var cats = document.getElementById('categories');
    for (var i = 0; i < cats.childNodes.length; i++) {
        var child = cats.childNodes[i];
        
        if (child.nodeType == 1 &&  child.nodeName.toLowerCase() == "input" && child.checked == true) {
            return true;
        }
    }
}

function setDraft() {
    var form = document.getElementById('status');
    form.value = '4';
}

function checkTitle() {
    
    var form = document.getElementById('title');
    if (form.value == '') {
        return false;
    }
    return true;
}

function checkNewCategory() {
    
    var form = document.getElementById('newcategory');
    if (form.value == '') {
        return false;
    }
    return true;
}

function reallyNew() {
    if (confirm("Do you really want to make a new post?\n (Any unsaved changes will get lost)")) {
        window.location.href="./newpost.xml";
    }
}

function reallyDelete() {
    if (confirm("Do you really want to delete this post (and all its comments)?\n ")) {
        document.getElementById('delete').value = 1;
        return true;
    }
    return false;
}


function formCheck(form, preview) {
    if (!checkTitle()) {
        alert("You did not provide a title, but you have to.");
        return false;
    }
    
    fixEntities();
    if (! checkValidXML(form)){
        return false;
    }
    
    if(form == 'draft') {
        setDraft();
    }
    
    if(typeof preview != "undefined") {
        if(preview = 1) {
            return true;
        }
    } else {
        var form = document.getElementById('newcategory');
        if (form.value == '') {
            return blogPost(form);
        } else {
            return true;
        }
        
    }
    
}

function blogPost(draft) {
    var saveButton = document.getElementById("Save");
    var saveButtonBottom = document.getElementById("SaveBottom");
    
    saveButton.value = parent.i18n.translate("Saving...");
    saveButton.style.backgroundColor = "green";
    
    saveButtonBottom.value = parent.i18n.translate("Saving...");
    saveButtonBottom.style.backgroundColor = "green";
    
    var form = document.getElementById('entry');
    
    try {
        var postString = '';
        
        updateTextAreasOnly();
        
        for (var i = 0; i < form.elements.length; i++) {
            var xml = form.elements[i].value;
            if (form.elements[i].type == "checkbox") {
                if (form.elements[i].checked) {
                    postString += form.elements[i].name;
                    postString += "=" + encodeURIComponent(form.elements[i].value) +"&";
                } 
            } else {
                if(form.elements[i].name == 'bx[plugins][admin_edit][uri]') {
                    var uri = form.elements[i].value;
                }
                
                postString += form.elements[i].name;
                postString += "=" + encodeURIComponent(form.elements[i].value) +"&";
            }
        }
        postString += 'ajax=1';
    }
    catch(e)
    {
        return true;
    }
    new ajax (uri, {
    postBody: postString,
    method: 'post',
    onComplete: ajaxPostComplete
    });
    
    return false;
}

function ajaxPostComplete(req)  {
    var saveButton = document.getElementById("Save");
    var saveButtonBottom = document.getElementById("SaveBottom");
    var id = document.getElementById('id');
    var uri = document.getElementById('uri');
    var values = eval('('+req.responseXML.documentElement.firstChild.nodeValue+')');
    
    id.value = values.id;
    uri.value = values.uri;
    
    saveButton.value = parent.i18n.translate("Saved");
    saveButtonBottom.value = parent.i18n.translate("Saved");
    
    setTimeout("savedToSave()",3000);
}

function savedToSave() {
    var saveButton = document.getElementById("Save");
    var saveButtonBottom = document.getElementById("SaveBottom");
    
    saveButton.value = parent.i18n.translate("Save");
    saveButton.style.backgroundColor = "";
    saveButtonBottom.value = parent.i18n.translate("Save");
    saveButtonBottom.style.backgroundColor = "";
    
    return false;
}


function fixEntities() {
    
    var form = document.getElementById('title');
    
    form.value =  form.value.replace(/&/g,"&amp;");
    form.value =  form.value.replace(/\>/g,"&gt;");
    form.value =  form.value.replace(/\</g,"&lt;");
}

function toggleCheckboxes(checked,id) {
    var parent = document.getElementById(id);
    var inputs = parent.getElementsByTagName("input");
    for (var i = 0; i < inputs.length; i++) {
        inputs[i].checked = checked;
    }
}

var isIE = false;
// on !IE we only have to initialize it once
if (window.XMLHttpRequest) {
    liveSearchReq = new XMLHttpRequest();
}

function startPreview(form, focusField) {
   if (window.XMLHttpRequest) {
    // branch for IE/Windows ActiveX version
    } else if (window.ActiveXObject) {
        liveSearchReq = new ActiveXObject("Microsoft.XMLHTTP");
    }
    
    if (focusField) {
        try {
            focusField.focus();
        } catch (e) {
        }
    }
    
    var postRequest = "";
        
    for (var i = 0; i < form.elements.length; i++) {
        if (form.elements[i].type == "checkbox") {
            if (form.elements[i].checked) {
                postRequest += form.elements[i].name;
                postRequest += "=" + encodeURIComponent(form.elements[i].value) +"&";
            } 
        } else {
            postRequest += form.elements[i].name;
            postRequest += "=" + encodeURIComponent(form.elements[i].value) +"&";
        }
    }
    postRequest += "bx[plugins][admin_edit][id]=-1&";
    postRequest += "bx[plugins][admin_edit][uri]=preview__&";
    postRequest += "bx[plugins][admin_edit][status]=4&";	
    postRequest += "bx[plugins][admin_edit][preview]=1&";
    postRequest += "bx[plugins][admin_edit][trackback]=&";
    postRequest += "bx[plugins][admin_edit][autodiscovery]=&";
    postRequest += "bx[plugins][admin_edit][created]=now()";

    //liveSaveSetStatus("Saving Document ...");
    liveSearchReq.onreadystatechange= previewProcessReqChange;
    liveSearchReq.open("POST", form.action);
    liveSearchReq.setRequestHeader("Content-Type","application/x-www-form-urlencoded");

    liveSearchReq.send(postRequest);
    
    return false;
}

function previewProcessReqChange() {
    
    if (liveSearchReq.readyState == 4) {
        var win = window.open(liveSearchRoot + liveSearchRootSubDir.replace(/\/admin\/edit\/*/,'').replace(/[^\/]+$/,'') + "/archive/preview__.html","preview");
        //win.focus();
    }
}

var isMSIEWin = ((parseInt(navigator.appVersion) >= 4) && (navigator.appName == "Microsoft Internet Explorer") && navigator.platform != "MacPPC");
var isMSIEMac = ((parseInt(navigator.appVersion) >= 4) && (navigator.appName == "Microsoft Internet Explorer") && navigator.platform == "MacPPC");

function insertImage(uri) {
    if (typeof insertImageForKupu != "undefined") {
        
        insertImageForKupu(uri);
    } else {
        insertImageForTextArea(uri);
    }
}

function insertImageForTextArea(uri) {
    var inputObj = document.getElementById("bx[plugins][admin_edit][content]");
    theSelection = getTextareaSelection(inputObj);

    var imgcode = '<img alt="" src="'+uri+'"/>';
    if (theSelection || (inputObj.selectionStart && inputObj.selectionStart != inputObj.value.length)) {
        // Add tags around selection
        //
        replaceTextareaSelection(inputObj, imgcode, theSelection, "");
        inputObj.focus();
        theSelection = '';
        return;
    }

    // Find last occurance of an open tag the same as the one just clicked
    
    inputObj.value += imgcode;
    inputObj.focus();
    return;
 
}

function getTextareaSelection(inputObj) {
    var theSelection = false;
    if (isMSIEWin) {
        theSelection = document.selection.createRange().text; // Get text selection
    } else if (inputObj.selectionStart > -1) {
        theSelection = inputObj.value.slice(inputObj.selectionStart,inputObj.selectionEnd);
    } 
    return theSelection;
}

function replaceTextareaSelection(inputObj, before, middle, after) {
    if (isMSIEWin ) {
        if(!document.selection.createRange().text ) {
            inputObj.value += before + middle + after;
        } else  {
            document.selection.createRange().text = before + middle + after;
        }
    } else if (inputObj.selectionStart > -1) {
        var start = inputObj.selectionStart;
        var end = inputObj.selectionEnd;
        var oldLength = inputObj.value.length;
        inputObj.value = inputObj.value.substring(0,start) + before + middle + after + inputObj.value.substring(end);
        // if at the end, put cursor at the. No selected text
        if (start == end && end == oldLength) {
            inputObj.setSelectionRange(start + before.length +  middle.length + after.length , start + before.length +  middle.length + after.length);
        }
        // if something was selected, select everything
        else if (middle.length > 0) {
            inputObj.setSelectionRange(start , start + before.length +  middle.length + after.length);
            // if nothing selected, just put the cursor in the middle of the new tags
        } else {
            inputObj.setSelectionRange(start + before.length, end +  before.length);
        }
    } else {
        inputObj.value += before + middle + after;
    }
}
function toggleCheckboxes(checked,id) {
    var parent = document.getElementById(id);
    var inputs = parent.getElementsByTagName("input");
    for (var i = 0; i < inputs.length; i++) {
        inputs[i].checked = checked;
    }
}
function toggleAdvanced() {
    var tr = document.getElementById("advanced1");
    if (tr.style.display == "none") {
        showAdvanced();   
    } else {
        closeAdvanced();
    }
}

function showAdvanced() {
    var img = document.getElementById("advanced_triangle");
    img.src = img.src.replace(/\/[^\/]*_klein.gif/,"/open_klein.gif");
    for(var x = 1;x<=7;x++){
        var tr = document.getElementById("advanced"+x);
        tr.style.display = "";
    }
   
    
    var ExpireDate = new Date ();
    var cook = "blogAdvancedView=true";
    ExpireDate.setTime(ExpireDate.getTime() + (30 * 24 * 3600 * 1000));
    document.cookie = cook + "; expires=" + ExpireDate.toGMTString();
}

function closeAdvanced() {
    var img = document.getElementById("advanced_triangle");
    img.src = img.src.replace(/\/[^\/]*_klein.gif/,"/closed_klein.gif");
    for(var x = 1;x<=6;x++){
        var tr = document.getElementById("advanced"+x);
       
        tr.style.display = "none";
    }

    var ExpireDate = new Date ();
    var cook = "blogAdvancedView=none";
    ExpireDate.setTime(ExpireDate.getTime() + (30 * 24 * 3600 * 1000));
    document.cookie = cook + "; expires=" + ExpireDate.toGMTString();
}

function toggleExtendedPost() {
    var tr = document.getElementById("postExtended");
    if (tr.style.display == "none") {
        showExtendedPost();
    } else {
        closeExtendedPost();
    }
}

function showExtendedPost() {
    /*var img = document.getElementById("advanced_triangle");
    img.src = img.src.replace(/\/[^\/]*_klein.gif/,"/open_klein.gif");*/
    document.getElementById("toggleExtended").style.display="none";
    var tr = document.getElementById("postExtended");
    tr.style.display = "";
        if (FCKeditorAPI) {
            var oEditor = FCKeditorAPI.GetInstance("bx[plugins][admin_edit][content]") ;
            
            initFckExtended();
        }
    
}

function closeExtendedPost() {
    var img = document.getElementById("advanced_triangle");
    img.src = img.src.replace(/\/[^\/]*_klein.gif/,"/closed_klein.gif");
    var tr = document.getElementById("postExtended");
    tr.style.display = "none";
}

//extended useroptions

function toggleUserAdvanced() {
    var div = document.getElementById("user");
    if (div.style.display == "none") {
        showUserAdvanced();   
    } else {
        closeUserAdvanced();
    }
}

function showUserAdvanced() {
    var img = document.getElementById("advanced_triangle");
    img.src = img.src.replace(/\/[^\/]*_klein.gif/,"/open_klein.gif");
    var div = document.getElementById("user");
    div.style.display = "";

    
    var ExpireDate = new Date ();
    var cook = "userAdvancedView=true";
    ExpireDate.setTime(ExpireDate.getTime() + (30 * 24 * 3600 * 1000));
    document.cookie = cook + "; expires=" + ExpireDate.toGMTString();
}

function closeUserAdvanced() {
    var img = document.getElementById("advanced_triangle");
    img.src = img.src.replace(/\/[^\/]*_klein.gif/,"/closed_klein.gif");
    for(var x = 1;x<=6;x++){
        var div = document.getElementById("user");
       
        div.style.display = "none";
    }

    var ExpireDate = new Date ();
    var cook = "userAdvancedView=none";
    ExpireDate.setTime(ExpireDate.getTime() + (30 * 24 * 3600 * 1000));
    document.cookie = cook + "; expires=" + ExpireDate.toGMTString();
}

function FCKeditor_OnComplete(instance) {
    if(instance.Name == 'bx[plugins][admin_edit][content]') {
        var aktiv = window.setInterval("storeContent();", 20000);
    } else {
    }
}

function storeContent() {
    var form = document.getElementById('entry');
    
    if(form.elements['bx[plugins][admin_edit][title]'].value != '') {
    
        try {
            var postString = '';
            
            updateTextAreasOnly();
            
            for (var i = 0; i < form.elements.length; i++) {
                var xml = form.elements[i].value;
                if(form.elements[i].name != "bx[plugins][admin_edit][saveBotton]" && form.elements[i].name != "bx[plugins][admin_edit][save]") {
                    if (form.elements[i].type == "checkbox") {
                        if (form.elements[i].checked) {
                            postString += form.elements[i].name;
                            postString += "=" + encodeURIComponent(form.elements[i].value) +"&";
                        } 
                    } else {
                        if(form.elements[i].name == 'bx[plugins][admin_edit][uri]') {
                            var uri = form.elements[i].value;
                        }
                        postString += form.elements[i].name;
                        postString += "=" + encodeURIComponent(form.elements[i].value) +"&";
                    }
                }
            }
            postString += 'store=1';
        }
        catch(e)
        {
            return true;
        }
        
        if(check != postString && postString) {
            new ajax (uri, {
            postBody: postString,
            method: 'post'
            });
            check = postString;
        }
    }
    
    return false;
}
