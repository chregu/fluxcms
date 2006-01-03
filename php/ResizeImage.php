<?php

class ImageResize {

    public $imagename = "";
    public $endsize = 0;
    public $oriPath = "";
    public $oriRef = "";
    public $fp;
    public $oriImgRatio =0;
    public $oriImgDesc = '';
    public $rectSuff = 'rFuerWasWarDas';
    public $convert = 'convert';
    public $tmpdir = '/tmp';
    
    
    function ImageResize ($imagename,$allowed_sizes, $pathReplaceBy = NULL) {
        
        error_reporting(E_ALL);
        $this->endImgFile = $imagename;   
        $this->allowedSizes = $allowed_sizes;
        $this->pathReplaceBy = $pathReplaceBy;
        $this->tmpdir = BX_TEMP_DIR;
        $this->defImageInfo();
        
        $this->checkIfExists();
        
        $this->defImageDim();
        
        $methodname = $this->method."Image";
        
        $this->$methodname();
        $this->printImage();
        
    }
      
   function checkIfExists() {
       if (! in_array($this->endsize,$this->allowedSizes,true)) {
           $this->send404();
       }
       else if (file_exists($this->oriImgFile)) {
           return true;
       } else {
           $this->send404();
           return false;
       }
   }
   
   function defImageInfo() {
          $this->endImgPath = dirname($this->endImgFile);
          $lastpos = strrpos( $this->endImgPath,"/");
          $this->endsize =  substr($this->endImgPath,$lastpos+1);
          if (!preg_match("#[0-9]#",$this->endsize)) {
              preg_match("#/([0-9][^/]*)/#",$this->endImgFile,$matches);
              $this->endsize = $matches[1];
          }
          if (strpos($this->endsize,",")) {
              list($this->endImgWidth, $this->endImgHeight, $this->method) = explode(",",$this->endsize);
          } 
	 /*
          if (!is_numeric($this->endsize)) {
             
              if (strpos($this->endsize, $this->rectSuff)) {
                  $this->method = 'rect';
                  $this->endsize = substr($this->endsize, 0, strpos($this->endsize, $this->rectSuff));
                  
              } else {
                  $this->method = $this->endsize;
              }
          }*/
          if (! isset($this->method)) {
               $this->method = "resize";
          }
          
          $this->oriImgPath = str_replace('/'.$this->endsize,'',$this->endImgPath)."/";
          
          if ($this->pathReplaceBy) {
               $this->oriImgPath =str_replace($this->pathReplaceBy[0],$this->pathReplaceBy[1],$this->oriImgPath);
          }
          $this->oriImgFile = $this->oriImgPath . basename($this->endImgFile);
          
   }
   
   function defImageDim() {
       
       $imginfo = getimagesize($this->oriImgFile);
       $this->oriImgWidth = $imginfo[0];
       $this->oriImgHeight = $imginfo[1];
       $this->oriImgFormat = $imginfo[2];
       $this->oriImgMime = $imginfo['mime'];
       
       $this->oriImgRatio = round($this->oriImgWidth / $this->oriImgHeight,3);
       $this->oriImgDesc = ($this->oriImgRatio > 1) ? 'Landscape':'Portrait';
       
       
       if (!file_exists($this->endImgPath)){
           if (!mkdir($this->endImgPath,0777,true)) {
               
          
               die ($this->endImgPath ." is not writable ");
           }
       }
   }

  function asciiImage() {
	require_once ("../lib/ASCIIArtist.php");
	$DemoPicture = new ASCIIArtist($this->oriImgFile);
	$this->oriImgMime = "text/html";
        $flip_h = false;

        $flip_v = false;

    	$DemoPicture->setImageCSS("
        color           : #000000;
        background-color: #FFFFFF;
        font-size       : 6px;
        font-family     : \"Courier New\", Courier, mono;
        line-height     : 4px;
        letter-spacing  : -1px;
    ");
$DemoPicture->renderHTMLImage(1, 4, "W", $flip_h, $flip_v);
$this->endImgFile .= ".html";
$fd = fopen ($this->endImgFile,"w");
	fwrite($fd,$DemoPicture->getHTMLImage());
	fclose($fd);
}

    function rectImage() {
        // echo "rect: ".$this->endsize;
        $endfile = $this->endImgFile;
        $tmpfile = sprintf("%s/%s",$this->tmpdir, basename($this->endImgFile));
        $aspect = $this->endImgWidth;
       
        if ($this->oriImgRatio >= 1) {
            
            $this->endImgWidth = round($aspect * ($this->oriImgWidth / $this->oriImgHeight));
            $this->endImgHeight = $aspect;
        
        } else {
            
            $this->endImgWidth = $aspect;
            $this->endImgHeight = round($aspect * ($this->oriImgHeight / $this->oriImgWidth));
            
        }
        
        $this->endImgFile = $tmpfile;
        $this->resizeImageImagickShell();
        chmod($tmpfile, 0777);
        
        $this->oriImgFile = $tmpfile;
        $this->endImgFile = $endfile;
        
        $offsetX = ($this->endImgWidth / 2) - ($this->endsize / 2);
        $offsetY = ($this->endImgHeight /2) - ($this->endsize / 2); 
        
        $this->cropImage($offsetX, $offsetY, $aspect, $aspect);
        
    }

   function resizeImage() {
        if (!isset($this->endImgHeight)) {
        
            // We have a Landscape (width > height)
            if ($this->oriImgRatio >= 1) {
                $this->endImgWidth = $this->endsize;
                $this->endImgHeight = round($this->endImgWidth / $this->oriImgRatio);
            }
            
            // We have a Portrait (width < height)
            else {
                $this->endImgHeight = $this->endsize;
                $this->endImgWidth = round($this->endImgHeight * $this->oriImgRatio);
            }
            
            // Use Imagick or fallback to shell-exec'ed
            if (function_exists("imagick_readimage")) {
                $this->resizeImageImagick();
            } else {
                $this->resizeImageImagickShell();
            }
        }
   }
   
   function cropMiddleImage() {
     if (function_exists("imagecopyresampled")) {
           
            $this->cropMiddleImageGD();   
       }
       else {
           $this->endImgFile = $this->oriImgFile;
       } 
   }
       
   function cropmiddleImageGD() {
       $new_image = imagecreatetruecolor($this->endImgWidth, $this->endImgHeight);
       $ori_image = imageCreateFromJpeg($this->oriImgFile);
       imagecopy($new_image,$ori_image, 0, 0, ($this->oriImgWidth - $this->endImgWidth) / 2,($this->oriImgHeight - $this->endImgHeight) / 2 , $this->endImgWidth,$this->endImgHeight);
       
       imageJpeg($new_image, $this->endImgFile, 75); 
       imagedestroy($new_image);
       imagedestroy($ori_image);
       
   }
   
   function resizeImageGD() {
       
       $new_image = imagecreatetruecolor($this->endImgWidth, $this->endImgHeight);
       $ori_image = imageCreateFromJpeg($this->oriImgFile);
       
       imagecopyresampled($new_image,$ori_image, 0, 0, 0, 0, $this->endImgWidth,$this->endImgHeight, $this->oriImgWidth, $this->oriImgHeight);
       
       imageJpeg($new_image, $this->endImgFile, 75); 
       imagedestroy($new_image);
       imagedestroy($ori_image);
   }

   function resizeImageImagick() {
       

       $handle = imagick_readimage( $this->oriImgFile) ;
       
       if ( imagick_iserror( $handle ) )
       {
           $reason      = imagick_failedreason( $handle ) ;
           $description = imagick_faileddescription( $handle ) ;
           
           print "handle failed!<BR>\nReason: $reason<BR>\nDescription: $description<BR>\n" ;
           exit ;
       }
	   
       imagick_setcompressionquality($handle,90);
       if ( !imagick_scale( $handle, $this->endImgWidth, $this->endImgHeight, "!" ) )
       {
           $reason      = imagick_failedreason( $handle ) ;
           $description = imagick_faileddescription( $handle ) ;
           
           print "imagick_resize() failed<BR>\nReason: $reason<BR>\nDescription: $description<BR>\n" ;
           exit ;
       }
       
       if ( !imagick_writeimage( $handle, $this->endImgFile ) )
       {
           $reason      = imagick_failedreason( $handle ) ;
           $description = imagick_faileddescription( $handle ) ;
           
           print "imagick_writeimage() failed<BR>\nReason: $reason<BR>\nDescription: $description<BR>\n" ;
           exit ;
       }
       
       
       
    }
    
    
    function resizeImageImagickShell() {
        
        $prefx = $this->_getImagickPrefix($this->oriImgMime);
        $command = $this->convert." ";
        $command.= $this->oriImgFile.' -quality 90 -scale '.$this->endImgWidth.'x'.$this->endImgHeight;
        $command.= '! '.$prefx.':'.$this->endImgFile;
        if (($return = shell_exec($command)) === '') {
            return TRUE;
        } else {
            return $return;
        }
    }
    
    
    function cropImage($offX, $offY, $width, $height) {
        
        $prefx = $this->_getImagickPrefix($this->oriImgMime);
        $command = $this->convert;
        $command.= " ".$this->oriImgFile;
        $command.= " -crop ".$width."x".$height."+".$offX."+".$offY;
        $command.= " ".$prefx.":".$this->endImgFile;
        error_log($command);
        if (($return = shell_exec($command)) === "") {
            return TRUE;
        } else {
            return $return;
        }
        
    }
    
    function _getImagickPrefix($mime) {
        
        switch($mime) {
            
            case ((stristr($mime,'jpeg')||stristr($mime,'jpg')) !== FALSE):
                return 'JPEG';
            break;
            
            case (stristr($mime,'gif') !== FALSE):
                return 'GIF87';
            break;
            
            case (stristr($mime,'png') !== FALSE):
                return 'PNG';
            break;
            
        }
   }
   
   function printImage() {
      header("Content-Type: ". $this->oriImgMime);
      print file_get_contents($this->endImgFile);
      exit;
   }
   
   function send404() {
       header("Not Found",true,404);
       print '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<HTML><HEAD>
<TITLE>404 Not Found</TITLE>
</HEAD><BODY>
<H1>Not Found</H1>
The requested URL '.$_SERVER['REQUEST_URI'].' was not found on this server.<P>

</BODY></HTML>';
           die();   
   }
   

   
}



?>
