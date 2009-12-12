<?php

//
// class.cropinterface.php
// version 1.0.0, 28th November, 2003
//
// Andrew Collington, 2003
// php@amnuts.com, http://php.amnuts.com/
//
// Description
//
// This class allows you to use all the power of the crop canvas
// class (class.cropcanvas.php) with a very easy to use and understand
// user interface.
//
// Using your browser you can drag and resize the cropping area and
// select if you want to resize in any direction or proportional to
// the image.
//
// If you wanted to provide users a cropping area without any resizing
// options, then this can easily be acheived.
//
// Requirements
//
// You will need the crop canvas class also from http://php.amnuts.com/
// The cropping area implements the 'Drag & Drop API' javascript by
// Walter Zorn (http://www.walterzorn.com/dragdrop/dragdrop_e.htm).
//
// Feedback
//
// There is message board at the following address:
//
//    http://php.amnuts.com/forums/index.php
//
// Please use that to post up any comments, questions, bug reports, etc.  
// You can also use the board to show off your use of the script.
//
// Support
//
// If you like this script, or any of my others, then please take a moment
// to consider giving a donation.  This will encourage me to make updates 
// and create new scripts which I would make available to you, and to give 
// support for my current scripts.  If you would like to donate anything, 
// then there is a link from my website to PayPal.
//
// Example of use
//
//  require_once 'class.cropinterface.php';
//  $ci = new cropInterface();
//  if ($_GET['file']) {
//    $ci->loadImage($_GET['file']);
//    $ci->cropToDimensions($_GET['sx'], $_GET['sy'], $_GET['ex'], $_GET['ey']);
//    header('Content-type: image/jpeg');
//    $ci->showImage('jpg', 100);
//    exit;
//  } else {
//    $ci->loadInterface('myfile.jpg');
//    $ci->loadJavaScript();
//  }
// 


	
class Image_CropInterface extends Image_CropCanvas
{
	public $file;
	public $img;
	public $crop;
	public $useFilter;


	/**
	* @return cropInterface
	* @param bool $debug
	* @desc Class initializer
	*/
	function __construct($debug = false)
	{
		parent::__construct($debug);
		
		$this->img  = array();
		$this->crop = array();
		$this->useFilter = false;

		$agent = trim($_SERVER['HTTP_USER_AGENT']);
		if ((stristr($agent, 'wind') || stristr($agent, 'winnt')) && (preg_match('|MSIE ([0-9.]+)|', $agent) || preg_match('|Internet Explorer/([0-9.]+)|', $agent)))
		{
			$this->useFilter = true;
		}
		else
		{
			$this->useFilter = false;
		}
		$this->setResizing();
		$this->setCropMinSize();
	}

	
	/**
	* @return void
	* @param unknown $do
	* @desc Sets whether you want resizing options for the cropping area.
	* This is handy to use in conjunction with the setCropSize function if you want a set cropping size.
	*/
	function setResizing($do = true)
	{
		$this->crop['resize'] = ($do) ? true : false;
	}
	
	
	/**
	* @return void
	* @param int $w
	* @param int $h
	* @desc Sets the initial size of the cropping area.
	* If this is not specifically set, then the cropping size will be a fifth of the image size.
	*/
	function setCropDefaultSize($w, $h)
	{
		$this->crop['width']  = ($w < 5) ? 5 : $w;
		$this->crop['height'] = ($h < 5) ? 5 : $h;
	}
	
	
	/**
	* @return void
	* @param int $w
	* @param int $h
	* @desc Sets the minimum size the crop area can be
	*/
	function setCropMinSize($w = 25, $h = 25)
	{
		$this->crop['min-width']  = ($w < 5) ? 5 : $w;
		$this->crop['min-height'] = ($h < 5) ? 5 : $h;
	}
	

	/**
	* @return void
	* @param string $filename
	* @desc Load the cropping interface
	*/
	function loadInterface($filename)
	{
		if (!file_exists($filename))
		{
			die("The file '$filename' cannot be found.");
		}
		else
		{
			$this->fileSrc = substr($filename,strlen(BX_OPEN_BASEDIR));
            $this->file = $filename;
			$this->img['sizes'] = getimagesize($this->file);
			if (!isset($this->crop['width']) || !isset($this->crop['height']))
			{
				$this->setCropDefaultSize(($this->img['sizes'][0] / 5), ($this->img['sizes'][1] / 5));
			}
		}
		echo '<script type="text/javascript" src="'.BX_WEBROOT.'webinc/editors/image/wz_dragdrop.js"></script>', "\n";
		echo '<div id="theCrop" style="position:absolute;background-color:transparent;border:1px solid yellow;width:', $this->crop['width'], 'px;height:', $this->crop['height'], 'px;';
		if ($this->useFilter)
		{
			echo 'filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\''.BX_WEBROOT.'webinc/editors/image/transbg.png\',sizingMethod=\'scale\');';
		}
		else
		{
			echo 'background-image:url('.BX_WEBROOT.'webinc/editors/image/transbg.png);';
		}
		echo "\"></div>\n";
		echo '<table border="1" style="border-collapse:collapse; border:1px solid black; width:', $this->img['sizes'][0], 'px;" align="center">';
		echo '<tr><td align="center" style="padding:5px;"><strong>', basename($this->file), ' (', $this->img['sizes'][0], ' x ', $this->img['sizes'][1], ')</strong>';
		if ($this->crop['resize'])
		{
			echo '<br><br>hold down either the \'shift\' or \'control\' button to resize the cropping area';
		}
		echo "</td></tr>\n";
		echo '<tr><td><img src="', BX_WEBROOT.$this->fileSrc, '?rand='.rand().'" ', $this->img['sizes'][3], ' alt="crop this image" name="theImage"></td></tr>', "\n";
		/*if ($this->crop['resize'])
		{
			echo '<tr><td align="center" style="font-size:11px;vertical-align:middle;padding:5px;"><input type="radio" id="resizeAny" name="resize" onClick="my_SetResizingType(0);" checked> <label for="resizeAny">Any Dimensions</label> &nbsp; <input type="radio" name="resize" id="resizeProp" onClick="my_SetResizingType(1);"> <label for="resizeProp">Proportional</label></td></tr>', "\n";
		}
		echo '<tr><td><input type="submit" value="crop the image" style="width:100%;" id="submit" onClick="my_Submit();"></td></tr>';
        */
		echo "\n</table>\n";
	}
	
	
	/**
	* @return void
	* @desc Load the javascript required for a functional interface.
	* This MUST be called at the very end of all your HTML, just before the closing body tag.
	*/
	function loadJavaScript($item  = "")
	{
		$params = '"theCrop"+MAXOFFLEFT+0+MAXOFFRIGHT+' . $this->img['sizes'][0] . '+MAXOFFTOP+0+MAXOFFBOTTOM+' . $this->img['sizes'][1] . ($this->crop['resize'] ? '+RESIZABLE' : '') . '+MAXWIDTH+' . $this->img['sizes'][0] . '+MAXHEIGHT+' . $this->img['sizes'][1] . '+MINHEIGHT+' . $this->crop['min-height'] . '+MINWIDTH+' . $this->crop['min-width'] . ',"theImage"+NO_DRAG';
		echo <<< EOT
<script type="text/javascript">
<!--

	SET_DHTML($params);

	dd.elements.theCrop.moveTo(dd.elements.theImage.x, dd.elements.theImage.y);
	dd.elements.theCrop.setZ(dd.elements.theImage.z+1);
	dd.elements.theImage.addChild("theCrop");
	dd.elements.theCrop.defx = dd.elements.theImage.x;

	function my_DragFunc()
	{
		dd.elements.theCrop.maxoffr = dd.elements.theImage.w - dd.elements.theCrop.w;
		dd.elements.theCrop.maxoffb = dd.elements.theImage.h - dd.elements.theCrop.h;
		dd.elements.theCrop.maxw    = {$this->img['sizes'][0]};
		dd.elements.theCrop.maxh    = {$this->img['sizes'][1]};
	}

	function my_ResizeFunc()
	{
		dd.elements.theCrop.maxw = (dd.elements.theImage.w + dd.elements.theImage.x) - dd.elements.theCrop.x;
		dd.elements.theCrop.maxh = (dd.elements.theImage.h + dd.elements.theImage.y) - dd.elements.theCrop.y;
	}
	
	function my_Submit()
	{
        
        var form = document.forms.crop;
        form[0].value = (dd.elements.theCrop.x - dd.elements.theImage.x);
        form[1].value = (dd.elements.theCrop.y - dd.elements.theImage.y);
        form[2].value = ((dd.elements.theCrop.x - dd.elements.theImage.x) + dd.elements.theCrop.w);
        form[3].value = ((dd.elements.theCrop.y - dd.elements.theImage.y) + dd.elements.theCrop.h);
		
	}
	
	function my_SetResizingType(proportional)
	{
		if (proportional)
		{
			dd.elements.theCrop.scalable  = 1;
			dd.elements.theCrop.resizable = 0;
		}
		else
		{
			dd.elements.theCrop.scalable  = 0;
			dd.elements.theCrop.resizable = 1;
		}
	}
	
//-->
</script>
EOT;
		}
}

?>
