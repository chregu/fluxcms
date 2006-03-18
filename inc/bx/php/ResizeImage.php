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
	public $eps = false;
    public $oriImgPath = null;
    public $deleteFileAfter = false;
    
    function __construct ($imagename,$allowed_sizes = array(), $pathReplaceBy = NULL, $noStartUp = false) {
        $this->tmpdir = BX_TEMP_DIR;
        if ($imagename) {
            error_reporting(E_ALL);
            $this->endImgFile = $imagename;  
            $this->allowedSizes = $allowed_sizes;
            $this->pathReplaceBy = $pathReplaceBy;
            $this->defImageInfo();
            
            if ($this->method == 'captchas') {
                if (!$this->captcha()) {
                    $this->send404();
                    return;
                }
            }
            else if ($this->method == 'gravatar') {
                if (!$this->gravatar()) {
                    $this->send404();
                    return;
                }
            }
            else if (!$this->check304etAl()) {                
                $this->defImageDim();
				$this->checkIfExists();
                $methodname = $this->method."Image";
                $this->$methodname();
            }
            $this->printImage();
        }
    }
    
    function resizeImageOnly ($imagename,$width = null ,$height = null) {
        $this->endImgFile = $imagename; 
        $this->endImgPath = dirname($this->endImgFile);
         $this->oriImgFile = $imagename;
        if ($height) {
            $this->endImgHeight = $height;
            $this->endsize = $height;
        }
        if ($width) {
             $this->endImgWidth = $width;
              $this->endsize = $width;
        }
        
        $this->defImageDim();
        $this->resizeImage();
    }
    
    function check304etAl() {
        $this->lastModified = filemtime($this->oriImgFile) ;
        if (file_exists($this->endImgFile) && (filemtime($this->endImgFile) >= $this->lastModified )) {
            $this->oriImgMime = popoon_helpers_mimetypes::getFromFileLocation($this->endImgFile);
            
            return true;
        } 
        return false;
            
    }
      
   function checkIfExists() {
       if (! in_array($this->oriEndsize,$this->allowedSizes,true)) {
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
          $this->oriEndsize = $this->endsize;
          if (!preg_match("#^[0-9]+#",$this->endsize)) {
              preg_match("#/([0-9][^/]*)/#",str_replace(BX_OPEN_BASEDIR,"",$this->endImgFile),$matches);
              if(count($matches) > 0) {
                  $this->endsize = $matches[1];
                  $this->oriEndsize = $this->endsize;
              }
          } 
          if (strpos($this->endsize,",")) {
              list($this->endImgWidth, $this->endImgHeight, $this->method) = explode(",",$this->endsize);
              if ($this->endImgHeight == 0) {
                  unset ($this->endImgHeight);
                  $this->oriImgPath = str_replace('/'.$this->endsize,'',$this->endImgPath)."/";
                  $this->endsize = $this->endImgWidth;
				  $this->oriEndsize = $this->endImgWidth;
                  unset ($this->endImgWidth);
              }
          } 
		  else if (!preg_match("#^[0-9]+#",$this->endsize)) {
				$this->method = $this->endsize;
          }

          if (!$this->oriImgPath) {
                $this->oriImgPath = str_replace('/'.$this->endsize,'',$this->endImgPath)."/";
          }

          if (! isset($this->method)) {
               $this->method = "resize";
          }
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
	   
	   if(!$imginfo){
		   $this->oriImgMime = popoon_helpers_mimetypes::getFromFileLocation($this->oriImgFile);  
		 
		   if( stristr($this->oriImgMime,'postscript') !== FALSE){
				$this->eps = true;
		   }
	   }
	   
       $this->oriImgRatio = round($this->oriImgWidth / $this->oriImgHeight,3);
       $this->oriImgDesc = ($this->oriImgRatio > 1) ? 'Landscape':'Portrait';
       
       if (!file_exists($this->endImgPath)){
           if (!mkdir($this->endImgPath,0777,true)) {
               die ($this->endImgPath ." is not writable ");
           }
       }
       
       // check whether the new image would be larger than the existing one
       if( ($this->oriImgWidth < $this->endsize) AND !($this->eps) ) {
           // overwrite method with copy
           $this->method = 'copy';
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

    function copyImage() {
        copy($this->oriImgFile, $this->endImgFile);
    }
    
    function grayImage() {
        $this->resizeImage("-colorspace gray ");
    }
    
    function scaleImage() {
        
        // nn,0,scale or nn,,scale
        if(!isset($this->endImgWidth) && is_numeric($this->endsize)) {
            $this->endImgWidth = (int) $this->endsize;
            $this->endImgHeight = (int) round($this->endImgWidth * $this->oriImgHeight / $this->oriImgWidth);
            if($this->endImgWidth >= $this->oriImgWidth) {
                $this->copyImage();
            } else {
                $this->resizeImage();
            }
        }
        // 0,nn,scale
        else if(isset($this->endImgHeight) && ($this->endImgWidth == 0)) {
            $this->endImgWidth = (int) round($this->oriImgWidth / $this->oriImgHeight * $this->endImgHeight);
            if($this->endImgHeight >= $this->oriImgHeight) {
                $this->copyImage();
            } else {
                $this->resizeImage();
            }
        }
        
        
        
    }
    
    function resizeImage($additionalOptions = "") {
        if (!isset($this->endImgHeight) ) {
            
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
        } else if (!isset($this->endImgWidth) && isset($this->endImgHeight)  ) {
            
            // We have a Landscape (width > height)
            if ($this->oriImgRatio <= 1) {
                $this->endImgHeight = $this->endsize;
                $this->endImgWidth = round($this->endImgHeight * $this->oriImgRatio);
            }
            
            // We have a Portrait (width < height)
            else {
                $this->endImgHeight = $this->endsize;
                $this->endImgWidth = round($this->endImgHeight * $this->oriImgRatio);
            }
            
            // Use Imagick or fallback to shell-exec'ed
        }
        /*
        if (!$additionalOptions && function_exists("imagick_readimage")) {
            $this->resizeImageImagick();
        } else */ 
        if ($GLOBALS['POOL']->config->getConfProperty("useGD") == 'true') {
            $this->resizeImageGD($additionalOptions);
        } else {
            $this->resizeImageImagickShell($additionalOptions);
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
    
    
    function resizeImageImagickShell($additionalOptions = "") {
        $prefx = $this->_getImagickPrefix($this->oriImgMime);

        $command = $this->convert." ";
		$size = '-scale '.escapeshellarg($this->endImgWidth . 'x'.$this->endImgHeight.'!' );
		if($this->eps){
			$size = '-resize '.escapeshellarg($this->endsize . 'x'.$this->endsize );
		}
        $command.= escapeshellarg($this->oriImgFile).' -colorspace rgb -quality 90 '.$additionalOptions. ' '.$size.' ';
        $command.= escapeshellarg($prefx.':'.$this->endImgFile);   
        $output = array();
        exec($command,$output, $exitcode);
		
        if ($exitcode === 0) {
            return TRUE;
        } else {
            print "error";
            error_log( "$command could not be run");
	    //sometimes, there's an error with the dir of the image.. delete it and try again next time
	   unlink(dirname($this->endImgFile));	
            return $exitcode;
        }
    }
    
    
    function cropImage($offX, $offY, $width, $height) {
        
        $prefx = $this->_getImagickPrefix($this->oriImgMime);
        
        $command = $this->convert;
        $command.= " ". escapeshellarg($this->oriImgFile);
        $command.= " -crop ".escapeshellarg($width."x".$height."+".$offX."+".$offY);
        $command.= " ".escapeshellarg($prefx.":".$this->endImgFile);        
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
                return 'GIF';
            break;
            
            case (stristr($mime,'png') !== FALSE):
                return 'PNG';
            break;
			default:
				return 'JPEG';
			break;
            
        }
   }
   function captcha() {
       $this->deleteFileAfter = true;
       $this->lastModified = time();
       if (file_exists($this->endImgFile)) {
           return true;
       } else {
           return false;
       }
   }
   function gravatar() {
        $grav_url = 'http://www.gravatar.com/avatar.php?'.$_SERVER['QUERY_STRING'];
        $imageName = $this->endImgFile;
        $imageDir = $this->endImgPath;
        $expireTime = 86400;
        $ok = false;
        $lastM = @filemtime($imageName);
        $this->lastModified = $lastM;
        if ($lastM && (time() -  $lastM) < $expireTime) {
            $ok = true;
        } else {
            if ( basename($imageName) != md5($_SERVER['QUERY_STRING'])) {
                return false;
            }
            if (!file_exists($imageDir)) {
                mkdir($imageDir, 0755, true);
            }
            include_once("HTTP/Request.php");
            $req = new HTTP_Request($grav_url,array("timeout" => 5));
            $req->addHeader("User-Agent",'Flux CMS HTTP Fetcher+Cacher $Rev: 2815 $ (http://flux-cms.org)');

            $req->sendRequest();
            if( $req->getResponseCode() == 200) {
                $img = $req->getResponseBody();
                $fs = strlen($img);
                if ($fs > 0) {
                    
                    if ($fs > 250 && $lastM) {
                        file_put_contents($imageName,$img);
                        $ok = true;
                    } else {
                        if (!$lastM) {
                             file_put_contents($imageName,$img);
                        }
                        $ok = false;
                    }
                } 
            } 
            if (!$ok) {
                //if not ok (could not really download from gravatar)
                //  and file exists and filesize is > 0
                // touch file, so it tries again only a day later
                $fs =  filesize($imageName);
                if ($fs > 0) {
                    $imgData = getimagesize($imageName);
                    //if width == 1, then gravatar does not exist
                    // we check again in a day
                    if ($imgData[0] == '1') {
                        touch($imageName);
                    } else if ($fs <= 250) {
                        // if filesize < 250 it's most presumably an error picture
                        //  check again in a minute
                        $this->lastModified = time() - ($expireTime - 60);
                        touch($imageName, $this->lastModified);
                    } else {
                        touch($imageName);
                    }
                } else {
                    //try again in 5 minutes
                    $this->lastModified = time() - ($expireTime - 300);
                    touch($imageName, $this->lastModified);
                }
            }
     
        }
        if (!isset($imgData)) {
            $imgData = getimagesize($imageName);
        }
        $this->contentType = $imgData['mime'];
        return true;
   }
   
   function printImage() {
      if (!isset($this->contentType)) {
          $this->contentType = popoon_helpers_mimetypes::getFromFileLocation($this->endImgFile);
      }
      header("Content-Type: ". $this->contentType );
      header("Last-Modified: ".  date('r',$this->lastModified));
      $now = time();
      header("Expires: ".  date('r',$now + ($now - $this->lastModified)));
      if (isset($_SERVER["HTTP_IF_MODIFIED_SINCE"])) {
          $lastMod304 =  strtotime($_SERVER["HTTP_IF_MODIFIED_SINCE"]);
          if ($lastMod304 >= $this->lastModified) {
                header("Not Modified",true,304);
                exit;
          }
      }
      print file_get_contents($this->endImgFile);
      if ($this->deleteFileAfter) {
          @unlink($this->endImgFile);
      }
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
