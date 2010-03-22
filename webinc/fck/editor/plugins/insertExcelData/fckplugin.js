

// Register the related command.
FCKCommands.RegisterCommand( 'insertExcelData', new FCKDialogCommand( 'insertExcelData', 'Excel To HTML', FCKPlugins.Items['insertExcelData'].Path + 'fck_insertExcelData.html', 415, 120 ) ) ;

// Create the "insertExcelData" toolbar button.
var oinsertExcelDataItem = new FCKToolbarButton( 'insertExcelData', 'Excel To HTML', 'Excel To HTML', null, null, false, true) ;
oinsertExcelDataItem.IconPath = FCKPlugins.Items['insertExcelData'].Path + 'insertExcelData.gif' ;

FCKToolbarItems.RegisterItem( 'insertExcelData', oinsertExcelDataItem ) ;

