admin = function () {
}

admin.addNewResource = function(path, type) {
    //var resName = prompt('File Name', 'new.de.' + type);
    // if(resName != null) {
        parent.edit.location.href = bx_webroot+'admin/addresource/' + path + "/?type="+type;
    //}
}

admin.addNewCollection = function(path) {
    parent.edit.location.href = bx_webroot+'admin/collection' + path;
}
admin.deleteResource = function(path) {
	
	if (confirm(parent.i18n.translate('Do you really want to delete {0} ?', [path]))) {
		parent.edit.location.href= bx_webroot+'admin/delete' + path + '?updateTree=' + getParentPath(path);
	}
}


admin.copyResource = function(path, move) {
	
    if(move) {
        var actionDescr = parent.i18n.translate('Move {0} to:', [path]);
    } else {
        var actionDescr = parent.i18n.translate('Copy {0} to:', [path]);
    }
        
    var newRes = prompt(actionDescr, path )
	if (newRes != null) {
		var copyLoc = bx_webroot+'admin/copy/' + path + '?to=' + newRes + '&updateTree=';
		if (move) {
			copyLoc += getParentPath(newRes);
			copyLoc  += ';'+getParentPath(path)+'&move=1'; 
		} else {
			copyLoc += newRes;
		}
		parent.edit.location.href= copyLoc;
	}
}

function getParentPath(src) {
	
	var paths  = src.substring(0,src.length -1).split("/");
	var path = "/";
	for (var i = 0; i < paths.length - 1; i ++) {
		
		if (paths[i]) {
			path = path + paths[i] + "/"; 
		}
		
	}
	return path;
}
