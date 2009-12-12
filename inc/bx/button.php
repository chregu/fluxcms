<?php


class button{
		
	public $text_w				= 0;
	public $text_h				= 0;

	public $text_offset_x		= false;
	public $text_offset_y		= false;
	public $text_underlenght	= 0;
		
	public $text_align			= 'center';
	public $text_valign			= 'middle';
	
	public $out 				= false;
	public $target_x			= 10;
	public $target_y			= 10;
	public $text_color			= '';
	public $background_color	= '';
	public $text				= '';
	public $options				= array();
	
	public $tmpdir				= './tmp/';
	public $fontdir				= './button/fonts/';
	public $templatedir			= './button/templates/';
	
	static public $instance 	= array();

	public $type 				= 'png';
	
	private $fontfile			= 'LucidaSansDemiBold.ttf';
	private $fontsize			= 12;
	static private $create		= '';
		
    public static function getInstance($type = '') {
		$classname = 'button';
		if(trim($type) != ''){
			$classname .= '_'.trim($type);
		}
        if (!isset(button::$instance[$type])) {
            button::$instance[$type] = new $classname();
        }
		button::$create = 'createButton_'.trim($type);
        return button::$instance[$type];
    }

	public static function http_date($time = false){
	    if(!$time){
	        $time = time();
	    }
	    $time = $time + (date("Z"));
	    return date("D, d M Y H:i:s ", $time)."GMT";
	}
	
	public function __construct(){
		$this->tmpdir		= BX_TEMP_DIR.'button/';
		if(!file_exists($this->tmpdir)){
			mkdir($this->tmpdir);
		}
		$this->fontdir		= BX_LIBS_DIR.'button/fonts/';
		$this->templatedir	= BX_LIBS_DIR.'button/templates/';	
	}
	
	public function createButton($x,$y, $background = '000000', $textcolor = 'ffffff', $text = 'generator', $options = false){

		$this->background_color	= $background;
		$this->text_color		= $textcolor;
		$this->text				= $text;
		$this->options			= $options;
		$this->target_x			= $x;
		$this->target_y			= $y;	
		
		$this->fontBox();	
		$function = button::$create;
		$this->$function();
		$this->placetext();
	}
	
	/**
	 * simple button creation
	 */
	function createButton_(){
		$this->newImage();
	}
	
	/**
	 * 
	 */	
	public function newImage($x = false, $y = false){
		$x 		= ($x)? $x : $this->target_x ;
		$y 		= ($y)? $y : $this->target_y ;
		$col 	= $this->background_color;
	
		$this->out 	= ImageCreatetruecolor($x,$y);
		$rgb 		= $this->web2rgb($col);
		$col 		= imagecolorallocate ( $this->out, $rgb['r'], $rgb['g'], $rgb['b']);
		imagefill ( $this->out, 0, 0, $col);			
	}

	public function show(){
		header("Last-Modified: ". $this->http_date() );
		switch($this->type){
			case 'png':
				header("Content-Type: image/png");
				ImagePng($this->out);
			break;
			case 'gif':
				header("Content-Type: image/gif");
				Imagegif($this->out);
			break;
			case 'jpeg':
				header("Content-Type: image/jpeg");
				Imagejpeg($this->out);
			break;
		}
	}

	public function writeTmp(){
		$file = tempnam($this->tmpdir, "btn_");
		ImagePng($this->out, $file);
		return $file;
	}
	
	public function write($filename){
		ImagePng($this->out, $filename);
	}
	
	//----------------------------------------------------------------------------------
	private function fontBox(){
		/*
		0	untere linke Ecke, X-Position
		1	untere linke Ecke, Y-Position
		2	untere rechte Ecke, X-Position
		3	untere rechte Ecke, Y-Position
		4	obere rechte Ecke, X-Position
		5	obere rechte Ecke, Y-Position
		6	obere linke Ecke, X-Position
		7	obere linke Ecke, Y-Position
		*/
		$box1 = imagettfbbox($this->fontsize, 0, $this->fontdir.$this->fontfile, $this->text);
		$this->text_w = $box1[2] - $box1[0];
		
		$box2 = imagettfbbox($this->fontsize, 0, $this->fontdir.$this->fontfile, 'Xg');
		$box4 = imagettfbbox($this->fontsize, 0, $this->fontdir.$this->fontfile, 'X');
		$tmp = $box2[1] - $box2[7];
		//für die höhe nehmen wir nur oberlänge
		$this->text_h = $box4[1] - $box4[7];
		//echo $this->text_h.'<br>';
		//unterlänge
		$this->text_underlenght = $tmp - $this->text_h;
		//echo $this->text_underlenght;
	}
	
	private function placeText(){
		//if the offset are not set from the driver
		//we do it now
		$x = ($this->text_offset_x === false)? $this->getXoffset() : $this->text_offset_x ;
		$y = ($this->text_offset_y === false)? $this->getYoffset() : $this->text_offset_y ;
		
		$rgb 	= $this->web2rgb($this->text_color);
		$text 	= imagecolorallocatealpha ( $this->out, $rgb['r'], $rgb['g'], $rgb['b'], 10);	
		$shadow = imagecolorallocatealpha ( $this->out, 0,0,0,64);	
		// ( int im, int size, int angle, int x, int y, int col, string fontfile, string text )
		//glow
		//ImageTTFText($this->out, $this->fontsize, 0, 10, 15, $col2, $this->fontdir.$this->fontfile, $this->text);
		//ImageTTFText($this->out, $this->fontsize, 0, 10, 16, $col3, $this->fontdir.$this->fontfile, $this->text);
		//shadow
		ImageTTFText($this->out, $this->fontsize, 0, $x+1, $y+1, $shadow, $this->fontdir.$this->fontfile, $this->text);
		//text
		ImageTTFText($this->out, $this->fontsize, 0, $x, $y, $text, $this->fontdir.$this->fontfile, $this->text);	
	}
	
	/**
	 * 
	 */
	private function getXoffset(){
		switch($this->text_align){
			case 'center':
				$x = round($this->target_x - $this->text_w)/2;
			break;
		}
		return $x;
	}
	
	/**
	 * 
	 */
	private function getYoffset(){
		switch($this->text_valign){
			case 'middle':
				$y = $this->target_y - (round($this->target_y - $this->text_h )/2);
			break;
		}
		return $y;
	}
		
	private function web2rgb($str){
		$str	= str_replace('#','',$str);
		$r 		= hexdec(substr($str,0,2));
		$g 		= hexdec(substr($str,2,2));
		$b 		= hexdec(substr($str,4,2));
		$rgb = array(
			'r' => $r,
			'g' => $g,
			'b' => $b
		);
		return $rgb;
	}

	private function rgb2web($r, $g = false, $b = false){
		if(is_array($r)){
			$g = $r['g'];
			$b = $r['b'];
			$r = $r['r'];
		}
		return dechex($r).dechex($g).dechex($b);
	}



}



?>