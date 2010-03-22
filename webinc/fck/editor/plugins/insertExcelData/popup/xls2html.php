<?php



require_once('reader.php');

class xls2html extends Spreadsheet_Excel_Reader{



	private $dbTable 		= null;
	private $dbHandle 		= null;
	private $fieldNames		= false;
	private $tableRow		= array();
	private $filename       = '';
	
	function __construct(){
		parent::Spreadsheet_Excel_Reader();
	}

	function importForm($file){
		$this->read($file);
		$this->filename = $file;
		$this->formShow();
	}
	
	function import($file){
		$this->read($file);
		$this->dataImport();
	}
	
	/**
	 * -----------------------------------------------------------------------------------------------------
	 * reader functions
	 * -----------------------------------------------------------------------------------------------------
	 */
	
	 /**
	  * imports the data from excel
	  */
	function dataImport(){
		
		$startRow = 1;
		
		$usedCols = $_POST['xls2html_col'];
		
		//start at line 1 or 2? (heder-row or not)
		if(isset( $_POST['xls2html_ignore_first']) ){
			$startRow = 2;
		}
		
		$html = '<table border="0" cellpading="0" cellspacing="0>';

		$count = 0;
		for ($i = $startRow; $i <= $this->sheets[0]['numRows']; $i++) {
			$html .= '<tr>';
			for ($j = 1; $j <= $this->sheets[0]['numCols']; $j++) {
				//do we have to import this col?
				if( in_array($j,$usedCols) ){
					$html .= '<td>'.@utf8_encode( $this->sheets[0]['cells'][$i][$j] ).'</td>';
				}
			}
			$html .= '</tr>';
		}	
		$html .= '</table>';
		
		//echo $html;

		echo '<script language="JavaScript" type="text/javascript">'."\n";
        echo "        window.opener.document.getElementById( 'insCode_area' ).value = '$html'\n";
        echo "        window.opener.document.getElementById( 'insCode_Message' ).style.display = 'block'\n";
        echo "        window.opener.document.getElementById( 'insCode_Button' ).style.display = 'none'\n";
        echo "        self.close()\n";
        echo '</script>'."\n";
		
		// TODO put html
	}
	 

	
	/**
	 * -----------------------------------------------------------------------------------------------------
	 * form and table functions
	 * -----------------------------------------------------------------------------------------------------
	 */
	/**
	 * creates the whole import-form
	 */
	function formShow(){
		echo '<form method="post" action="excel.php">';

			$excelData = array();
			//get the first 5 lines of data
			for ($i = 1; $i <= 5; $i++) {
				$data = array();
				for ($j = 1; $j <= $this->sheets[0]['numCols']; $j++) {
					$tmp = array();
					$tmp['value'] = @utf8_encode( $this->sheets[0]['cells'][$i][$j] );
					$data[] = $tmp;
				}
				$excelData[] = $data;
			}
			
			$data = array();
			$header = $excelData[0];
			for ($j = 1; $j <= $this->sheets[0]['numCols']; $j++) {
				$tmp = array();
				$tmp['value'] = '<input name="xls2html_col[]" type="checkbox" value="'.$j.'">';
				$data[] = $tmp;
			}
			$this->tableRow[] = $data;
			
			
			foreach($excelData as $row){
				$this->tableRow[] = $row;
			}

			
			echo $this->tableGet();
			echo '<br /><input type="checkbox" name="xls2html_ignore_first" value="xls2html_ignore_first"  style="width: auto;" />Skip first line<br />';
			echo '<input type="hidden" name="xls2html_xslFilename" value="'.$this->filename.'"  />';
			echo '<br /><input type="submit" name="xls2html_import" value="Create HTML" />';
			
		echo '</form>';
	}
	
	/**
	 * creates a table from $this->tableRow[]
	 */
	function tableGet(){
		$count = 0;
		$html = '<table border="0" width="95%" background="/webinc/fck/bg.gif">';
		foreach($this->tableRow as $row){
			$class = ($count%2)? 'even' : 'odd' ; 
			if($count== 0){
				$html .= '<tr>';
			}
			else{
				$html .= '<tr class="'.$class.'">';
			}
			foreach($row as $cell){
				$bg = '';
				$html .= '<td'.$bg.'>'.$cell['value'].'</td>';
			}
			$html .= '</tr>';
			$count++;
		}
		$html .= '</table>';
		
		return $html;
	}
	

	
	/**
	 * -----------------------------------------------------------------------------------------------------
	 * generic functions
	 * -----------------------------------------------------------------------------------------------------
	 */
			 
	/**
	 * sends error-message to the browser
	 */
	function Error($text){
		echo '<span style="color: #ff0000; font-weight: bold;">'.$text.'</span><br>';
	}
	
	/**
	 * sends message to the browser
	 */
	function Msg($text){
		echo '<span style="color: #333333; font-weight: bold;">'.$text.'</span><br>';
	}

}





?>