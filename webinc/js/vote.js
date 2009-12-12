	/*
    // +----------------------------------------------------------------------+
    // | Copyright (c) 2004 Bitflux GmbH                                      |
    // +----------------------------------------------------------------------+
    // | Licensed under the Apache License, Version 2.0 (the "License");      |
    // | you may not use this file except in compliance with the License.     |
    // | You may obtain a copy of the License at                              |
    // | http://www.apache.org/licenses/LICENSE-2.0                           |
    // | Unless required by applicable law or agreed to in writing, software  |
    // | distributed under the License is distributed on an "AS IS" BASIS,    |
    // | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
    // | implied. See the License for the specific language governing         |
    // | permissions and limitations under the License.                       |
    // +----------------------------------------------------------------------+
    // | Author: Bitflux GmbH <devel@bitflux.ch>                              |
    // +----------------------------------------------------------------------+
    
    */
    var voteReq = false;
    var t = null;
    var voteLast = "";
	
    var isIE = false;
    // on !IE we only have to initialize it once
    if (window.XMLHttpRequest) {
        voteReq = new XMLHttpRequest();
    }
    
    function voteInit() {
        
        if (navigator.userAgent.indexOf("Safari") > 0) {
        } else if (navigator.product == "Gecko") {
        } else {
            isIE = true;
        }
        
    }
    
	
    
    function voteSubmit() {
        /*
        if (typeof voteRoot == "undefined") {
            voteRoot = "";
        }
        if (typeof voteRootSubDir == "undefined") {
            voteRootSubDir = "";
        }
        if (typeof voteParams == "undefined") {
            voteParams2 = "";
        } else {
            voteParams2 = "&" + voteParams;
        }*/
        value = null;
        for (var i = 0; i < document.forms.voteform.selection.length; i++) {
            if (document.forms.voteform.selection[i].checked) { 
                value = document.forms.voteform.selection[i].value;
                break;
            }
        }
        var postvalue='selection='+value+'&votesubmit=true';
        /*if (voteReq && voteReq.readyState < 4) {
            alert("request is on the way");
            return false;
        }*/
        /*if (! value) { 
            alert("please choose something");
            return false;
        }*/
        if (window.XMLHttpRequest) {
            // branch for IE/Windows ActiveX version
        } else if (window.ActiveXObject) {
            voteReq = new ActiveXObject("Microsoft.XMLHTTP");
        }
        voteReq.onreadystatechange= voteProcessReqChange;
        var url = document.forms.voteform.action.replace(/\.html$/,'.xml');
        
        voteReq.open("POST", url);
        voteReq.setRequestHeader("Content-Type","application/x-www-form-urlencoded");

        voteReq.send(postvalue);
        return false;
    }
    
    function voteProcessReqChange() {
        
        if (voteReq.readyState == 4) {
            var  res = document.getElementById("votediv");
            res.innerHTML = voteReq.responseText;
            //alert(voteReq.responseText);
        }
    }
    
    

