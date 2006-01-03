<?php

class bx_editors_image extends bx_editor implements bxIeditor {    
    
    public function getPipelineName() {
        return "image";
    }
    
    public function getDisplayName() {
        return "Image";
    }
    
    public function handlePOST($path, $id, $data) {
        $id = str_replace("..","", $data['dataUri']);
        if (isset ($data['rotate'])) {
            if (isset($data['rotate_value']) && $data['rotate_value'] != 0) {
                $this->rotate_image($id,$data['rotate_value']);
            }
            else if (isset($data['rotate_radio'])) {
                $this->rotate_image($id,$data['rotate_radio']);
            }
        }
        else if (isset($data['resize'])) {
            include_once(BX_LIBS_DIR.'/php/ResizeImage.php');
            $im = new ImageResize(false);
            if ($data['resize_width'] && $data['resize_height']) {
                $im->resizeImageOnly($id,$data['resize_width'],$data['resize_height']);
            } else if ($data['resize_width'] ) {
                $im->resizeImageOnly($id,$data['resize_width']);
            } else if ($data['resize_height'] ) {
                $im->resizeImageOnly($id,null,$data['resize_height']);
            } 
        }
        
        else if (isset($data['crop'])) {
            $image = $id;
            $ci = new Image_CropInterface();
            /** http://localhost:93/wp-admin/edit-images.php?cmd=crop&item=9ba435818171aebe7d5c881dd9b86307
            *
            * Requires: class.cropcanvas.php, class.cropinterface.php, wz_dragdrop.js, transparentpixel.gif
            */
            $ci->loadImage($image);
            $ci->cropToDimensions($data['sx'], $data['sy'], $data['ex'], $data['ey']);
            $ci->saveImage($image, 100);
            
            /*  echo "<table>\n";
            printf("<tr><td><img src=\"%s\" /><br /></td></tr>\n", $portion['href']  );  
            printf("<tr><td><a href=\"%s\">Back</a></td></tr>\n", $_SERVER['PHP_SELF']  );  
            echo "</table>";*/  
        }
        
    }
    
    /**
    * Rotate an image by a given angle.
    *
    * @param string $image Filename
    * @param integer $angle
    * @return mixed
    */
    function rotate_image($image, $angle=90) {
        
        //require_once('Image/Transform.php');
        
        $cmdline = trim(shell_exec("which convert"));
        $arr = getimagesize($image );
        
        if(stristr($cmdline, "/convert")) {
            // Use imagemagick from the commandline    
            $cmd = sprintf("convert -verbose -rotate %s %s %s", $angle, $image, $image );
            $return = false;
            return ($return = shell_exec($cmd) === '' ? TRUE : $return );
            
        } elseif (function_exists("ImageCreateFromJPEG")) {
            // Should work for most common file types
            if (@file_exists($image)) {
                $chmod = @chmod($image, 0775);
            }
            list($width, $height, $type, $attr) = getimagesize($image );
            
            $functionName = 'ImageCreateFrom' . substr(strrchr($arr['mime'], "/"), 1);
            $imageHandle = $functionName($image);
            
            $angle = $angle * -1;
            $rotatedImage = ImageRotate($imageHandle, $angle, 0);
            
            $functionName = 'Image' . substr(strrchr($arr['mime'], "/"), 1);
            $res = $functionName($rotatedImage, $image);
            return $res;
        }
            else {
            return false;
        }
    }
}



?>
