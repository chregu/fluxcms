

// Register the related command.
FCKCommands.RegisterCommand( 'insertExcelData', new FCKDialogCommand( 'insertExcelData', FCKLang.insertExcelData, FCKPlugins.Items['insertExcelData'].Path + 'fck_insertExcelData.html', 415, 300 ) ) ;

// Create the "insertExcelData" toolbar button.
var oinsertExcelDataItem = new FCKToolbarButton( 'insertExcelData', FCKLang.insertExcelData, FCKLang.insertExcelData, null, null, false, true) ;
oinsertExcelDataItem.IconPath = FCKPlugins.Items['insertExcelData'].Path + 'insertExcelData.gif' ;

FCKToolbarItems.RegisterItem( 'insertExcelData', oinsertExcelDataItem ) ;

