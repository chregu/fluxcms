dbforms2_fBrowserLastLocation = null;

bx_openFileBrowser = function(field) {
    var fBrowserUrl = bx_webroot + 'webinc/fck/editor/filemanager/browser/default/browser.html?Type=files&Connector=connectors/php/connector.php';

    var currentFile = field.value;
    
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
    
    SetUrl = function(url) {
    	field.value = url;
    }
    
}