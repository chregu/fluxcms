<?php


class bx_indexer_image_jpeg {
    
    
    public function getMetadataForFile($file) {
        if (function_exists('exif_read_data')) {
            if (!defined('EXIF_NS')) {
                define('EXIF_NS','http://www.w3.org/2003/12/exif/ns#');
            }
            
            $exif = exif_read_data($file);
            
            $props[EXIF_NS]['make'] = $exif['Make'];
            $props[EXIF_NS]['model'] = $exif['Model'];
            $props[EXIF_NS]['apertureValue'] = $exif['COMPUTED']['ApertureFNumber'];
            $props[EXIF_NS]['maxApertureValue'] = $exif['MaxApertureValue'];
            
            $props[EXIF_NS]['exposureTime'] = $exif['ExposureTime'];
            $props[EXIF_NS]['focalLength'] = $exif['FocalLength'];
            $props[EXIF_NS]['focalLengthIn35mmFilm'] = $exif['FocalLengthIn35mmFilm'];
            $props[EXIF_NS]['fNumber'] = $exif['FNumber'];
            $props[EXIF_NS]['isoSpeedRatings'] = $exif['ISOSpeedRatings'];
            /*$props[EXIF_NS]['ResolutionHeightDPI'] = $exif['YResolution'];
            $props[EXIF_NS]['ResolutionWidthDPI'] = $exif['XResolution'];*/
            $props[EXIF_NS]['imageLength'] = $exif['COMPUTED']['Height'];
            $props[EXIF_NS]['imageWidth'] = $exif['COMPUTED']['Width'];
            $props[EXIF_NS]['flash'] = $exif['Flash'] ;
            $props[EXIF_NS]['flashOnOff'] = $exif['Flash'] & 1;
            $props[EXIF_NS]['redEyeOnOff'] = $exif['Flash'] & 64;
            $props[EXIF_NS]['dateTimeOriginal'] = $exif['DateTimeOriginal'];
            
            $props[EXIF_NS]['whiteBalance'] =  ($exif['WhiteBalance'] === 1) ? 'Manual' : 'Auto';
            
            $meteringmode = array( 0 => 'Unknown',
            1 => 'Average',
            2 => 'CenterWeightedAverage',
            3 => 'Spot',
            4 => 'MultiSpot',
            5 => 'Pattern',
            6 => 'Partial',
            255 => 'other');
            $props[EXIF_NS]['meteringMode'] = $meteringmode[$exif['MeteringMode']];
            
            $exposureprogram = array(
            0 => 'Not defined',
            1 => 'Manual',
            2 => 'Normal program',
            3 => 'Aperture priority',
            4 => 'Shutter priority',
            5 => 'Creative program (biased toward depth of field)',
            6 => 'Action program (biased toward fast shutter speed)',
            7 => 'Portrait mode (for closeup photos with the background out of focus)',
            8 => 'Landscape mode (for landscape photos with the background in focus)');
            
            $props[EXIF_NS]['exposureProgram'] = $exposureprogram[$exif['ExposureProgram']];
            
            $exposuremode = array (
            0 => 'Auto exposure',
            1 => 'Manual exposure',
            2 => 'Auto bracket');
            
            $props[EXIF_NS]['exposureMode'] = $exposuremode[$exif['ExposureMode']];
            
            return $props;
        }
        return array();
    }
    
}