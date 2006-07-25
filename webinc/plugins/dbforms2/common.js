dbforms2_fBrowserField = null;
dbforms2_fBrowserLastLocation = '';

function dbforms2_common() {
}

dbforms2_common.serializeToString = function(dom) {
    var serializer = new XMLSerializer();
    return serializer.serializeToString(dom);
}

dbforms2_common.openFileBrowser = function(field) {
    var fBrowserUrl = bx_webroot + 'webinc/fck/editor/filemanager/browser/default/browser.html?Type=files&Connector=connectors/php/connector.php';

    var currentFile = field.getValue();
    
    if (currentFile == '' && dbforms2_fBrowserLastLocation) {
        currentFile = dbforms2_fBrowserLastLocation;
    }
    var filesDir = '/files';
    sParentFolderPath = currentFile.substring(filesDir.length, currentFile.lastIndexOf('/', currentFile.length - 2) + 1);

    if(sParentFolderPath != '' && (sParentFolderPath.indexOf('/') != -1))
        fBrowserUrl += '&RootPath=' + escape(sParentFolderPath);
    
    if(typeof fBrowserWindow != 'undefined' && !fBrowserWindow.closed) {
        fBrowserWindow.location.href = fBrowserUrl;
    } else {
        fBrowserWindow = window.open(fBrowserUrl, 'fBrowser', 'width=800,height=600,location=no,menubar=no');
    }

    fBrowserWindow.focus();

    dbforms2_fBrowserField = field;
    
    SetUrl = function(url) {
        if(dbforms2_fBrowserField != null) {
            dbforms2_fBrowserField.setUrl(url);
            dbforms2_fBrowserLastLocation = url;
        }
        dbforms2_fBrowserField = null;
    }
    
}

