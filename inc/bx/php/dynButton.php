<?php
///dynbuttons/300,25,ffffff,eeeeff,hue:24,shadow:1/aqua/

include_once("../../../inc/bx/init.php");
bx_init::start('conf/config.xml', "../../..");

$request = urldecode($_SERVER['REQUEST_URI']);

require_once '../autoload.php';

$foo = new dynbutton($request);
unset($foo);



class dynbutton{
	 
	private $id = '';
	 
	public function __construct($request){
		$this->id = md5($request);
		$this->tmpdir = BX_TEMP_DIR.'button/';
		if($this->buttonExists()){
			exit;		
		}
		else {
			$this->createButton($request);
		}
	}
	
	private function createButton($request){
		//prepare request
		$request = str_replace('dynbuttons/','',$request);
		$request = explode('/',$request);		
		//params
		$params = explode(',', $request[1]);
		$x = $params[0];
		$y = $params[1];
		//colors
		$bgcolor = (isset($params[2]) AND trim($params[2]) != '')?$params[2]:'ffffff';
		$textcolor = (isset($params[3]) AND trim($params[3]) != '')?$params[3]:'000000';
		$type = $request[2];
		$text = $request[3];		
		$options = array();		
		//options
		for($i = 4; $i < count($params); $i++){
			//echo $params[$i];
			$tmp = explode(':',$params[$i]);
			$options[trim($tmp[0])] = trim($tmp[1]);
		}		
		$btn = button::getInstance($type);
		bx_log::log('Button: '.$type);
		$btn->createButton($x,$y,$bgcolor,$textcolor,$text, $options);
		bx_log::log('Width: '.$x.', height: '.$y.', bg: '.$bgcolor.', color'.$textcolor.', text: '.$text.', opts: '.implode( $options ) );
		$btn->write($this->tmpdir.$this->id);
		$btn->show();		
	}
 
	private function buttonExists(){
		$file = $this->tmpdir.$this->id;
		$lastModified = filemtime($file);
		header("Last-Modified: ". button::http_date($lastModified) );
		$now = time();
		//304
		if (isset($_SERVER["HTTP_IF_MODIFIED_SINCE"]) ) {
			$lastMod304 = $_SERVER["HTTP_IF_MODIFIED_SINCE"];
			//echo $lastMod304;
			if ($lastMod304 >= $lastModified) {
				header("Not Modified",true,304);
				exit;
			}
        }
		//file schicken
		if(file_exists($file)){
			$contentType = popoon_helpers_mimetypes::getFromFileLocation($file);
			header("Content-Type: ".$contentType);
			echo file_get_contents($file);
			return true;
		}
		return false;
	}
 
	
	
	
	
	
	
	
 }


?>