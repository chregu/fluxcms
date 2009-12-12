
function multipleUpdate(name) {
    dst = document.getElementById('multiple_'+name+'_dst');
    elem = document.getElementById(name);
    val = '';
    if (dst && elem) {
        for(i=0;i<dst.options.length;i++) {
            val=val+dst.options[i].value+","; 
        } 
        
        val=val.substring(0,(val.length -1));
        elem.value = val;
    } 
}

function multipleAddOption(name, value, text) {
    dst = document.getElementById('multiple_'+name+'_dst');    
    optNode = document.createElement('option');
    optNode.value = value; 
    //optNode.text = text;
    optNode.appendChild(document.createTextNode(text)); 
    dst.appendChild(optNode);
}

function multipleAdd(name) {
    src = document.getElementById('multiple_'+name+'_src');
   
    for(i=0; i<src.options.length; i++) {
        if (src.options[i].selected) {
            multipleAddOption(name, src.options[i].value, src.options[i].text)
        }
    }

    multipleUpdate(name);
}

function multipleRemove(name) {
    dst = document.getElementById('multiple_'+name+'_dst');    
    for(i=0; i<dst.options.length; i++) {
        if (dst.options[i].selected) {
            dst.removeChild(dst.options[i]);
        }
    }

    multipleUpdate(name);
}
