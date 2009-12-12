<?php
//
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This code is released under the GNU LGPL Go read it over here:       |
// | http://www.gnu.org/copyleft/lesser.html                              |
// +----------------------------------------------------------------------+
// | Authors: Sandy McArthur Jr. <Leknor@Leknor.com>                      |
// +----------------------------------------------------------------------+
//
// $Id$
//

// Uncomment the folling define if you want the class to automatically
// read the MPEG frame info to get bitrate, mpeg version, layer, etc.
//
// NOTE: This is needed to maintain pre-version 1.0 behavior which maybe
// needed if you are using info that is from the mpeg frame. This includes
// the length of the song.
//
// This is discouraged because it will siginfincantly lengthen script
// execution time if all you need is the ID3 tag info.
// define('ID3_AUTO_STUDY', true);

// Uncomment the following define if you want tons of debgging info.
// Tip: make sure you use a <PRE> block so the print_r's are readable.
// define('ID3_SHOW_DEBUG', true);

require_once "PEAR.php" ;

/**
* File not opened
* @const PEAR_MP3_ID_FNO
*/
define('PEAR_MP3_ID_FNO', 1);

/**
* Read error
* @const PEAR_MP3_ID_RE
*/
define('PEAR_MP3_ID_RE', 2);

/**
* Tag not found
* @const PEAR_MP3_ID_TNF 
*/
define('PEAR_MP3_ID_TNF', 3);

/**
* File is not a MP3 file (corrupted?)
* @const PEAR_MP3_ID_NOMP3 
*/
define('PEAR_MP3_ID_NOMP3', 4);

/**
 * A Class for reading/writing MP3 ID3 tags
 * 
 * Note: This code doesn't try to deal with corrupt mp3s. So if you get
 * incorrect length times or something else it may be your mp3. To fix just
 * re-enocde from the CD. :~)
 *
 * eg:
 * require_once("MP3/Id.php");
 * $file = "Some Song.mp3";
 *
 * $id3 = &new MP3_Id();
 * $id3->read($file);
 * print_r($id3);
 *
 * echo $id3->getTag('artists');
 *
 * $id3->comment = "Be gentle with that file.";
 * $id3->write();
 * $id3->read($file);
 * print_r($id3 );
 * 
 * @package MP3_Id
 * @author Sandy McArthur Jr. <Leknor@Leknor.com>
 * @version $Version$
 */
class MP3_Id {

    /**
    * mp3/mpeg file name
    * @var boolean
    */
    var $file = false;      
    /**
    * ID3 v1 tag found? (also true if v1.1 found)
    * @var boolean
    */
    var $id3v1 = false;     
    /**
    * ID3 v1.1 tag found? 
    * @var boolean
    */    
    var $id3v11 = false;
    /**
    * ID3 v2 tag found? (not used yet)
    * @var boolean
    */        
    var $id3v2 = false;

    // ID3v1.1 Fields:
    /**
    * trackname
    * @var string
    */        
    var $name = '';
    /**
    * artists
    * @var string
    */            
    var $artists = '';
    /**
    * album
    * @var string
    */                
    var $album = '';
    /**
    * year
    * @var string
    */                
    var $year = '';  
    /**
    * comment
    * @var string
    */                
    var $comment = '';
    /**
    * track number
    * @var integer
    */                
    var $track = 0;
    /**
    * genre name
    * @var string
    */
    var $genre = '';
    /**
    * genre number
    * @var integer
    */                    
    var $genreno = 255;

    // MP3 Frame Stuff
    /**
    * Was the file studied to learn more info?
    * @var boolean
    */
    var $studied = false;
    
    /**
    * version of mpeg
    * @var integer
    */    
    var $mpeg_ver = 0;
    /**
    * version of layer
    * @var integer
    */        
    var $layer = 0;
    /**
    * version of bitrate
    * @var integer
    */        
    var $bitrate = 0;
    /**
    * Frames are crc protected?
    * @var boolean
    */            
    var $crc = false;
    /**
    * frequency
    * @var integer
    */                
    var $frequency = 0;
    /**
    * Frames padded
    * @var boolean
    */                
    var $padding = false;
    /**
    * private bit set
    * @var boolean
    */                    
    var $private = false;
    /**
    * Mode (Stero etc)
    * @var string
    */                    
    var $mode = '';
    /**
    * Copyrighted
    * @var string
    */                        
    var $copyright = false; 
    /**
    * On Original Media? (never used)
    * @var boolean
    */                        
    var $original = false;
    /**
    * Emphasis (also never used)
    * @var boolean
    */                        
    var $emphasis = '';     
    /**
    * Bytes in file 
    * @var integer
    */
    var $filesize = -1;
    /**
    * Byte at which the first mpeg header was found
    * @var integer
    */                            
    var $frameoffset = -1;
    /**
    * length of mp3 format hh:ss
    * @var string
    */
    var $length = false; 
    /**
    * length of mp3 in seconds
    * @var string
    */                            
    var $lengths = false;

    /**
    * if any errors they will be here
    * @var string
    */
    var $error = false;    

    /**
    * print debugging info?
    * @var boolean
    */
    var $debug = false;
    /**
    * print debugg
    * @var string
    */    
    var $debugbeg = '<DIV STYLE="margin: 0.5 em; padding: 0.5 em; border-width: thin; border-color: black; border-style: solid">';
    /**
    * print debugg
    * @var string
    */
    var $debugend = '</DIV>';
    
    var $mp3fmode = 'w';
    
    /*
     * creates a new id3 object
     * and loads a tag from a file.
     *
     * @param string    $study  study the mpeg frame to get extra info like bitrate and frequency
     *                          You should advoid studing alot of files as it will siginficantly
     *                          slow this down.
     * @access public
     */
    function MP3_Id($study = false) {
        if(defined('ID3_SHOW_DEBUG')) $this->debug = true;
        $this->study=($study || defined('ID3_AUTO_STUDY'));

    } // id3()

    /**
    * reads the given file and parse it
    *
    * @param    string  $file the name of the file to parse
    * @return   mixed   PEAR_Error on error
    * @access   public
    */
    function read( $file="") {
        if ($this->debug) print($this->debugbeg . "id3('$file')<HR>\n");

        if(!empty($file))$this->file = $file;
        if ($this->debug) print($this->debugend);
        
        if (file_exists($file)) {
            $this->mp3infile = $file;
            $this->_getTagVersion($this->mp3infile);
            if ($this->id3v2) {
                return $this->_read_v2();    
            }
        }
        
         
        
        return $this->_read_v1();
    }
    
    function _getTagVersion($mp3file) {
        if (file_exists($mp3file)) {
            $fp = fopen($mp3file, 'rb');
            // check for ID3v2
            if (fread($fp, 3) == "ID3") {
                $this->id3v2 = True;
            }
            
            fclose($fp);
        }

    }
    
    /**
    * sets a field
    * 
    * possible names of tags are:
    * artists   - Name of band or artist
    * album     - Name of the album
    * year      - publishing year of the album or song
    * comment   - song comment
    * track     - the number of the track
    * genre     - genre of the song
    * genreno   - Number of the genre
    *
    * @param    mixed   $name   Name of the tag to set or hash with the key as fieldname
    * @param    mixed   $value  the value to set 
    *
    * @access   public
    */
    function setTag($name, $value) {
        if( is_array($name)) {
            foreach( $name as $n => $v) {
                $this -> $n = $v ;
                }
        } else {
            $this -> $name = $value ;        
        }
    }
    
    /**
    * get the value of a tag
    * 
    * @param    string  $name       the name of the field to get
    * @param    mixed   $default    returned if the field not exists
    * 
    * @return   mixed   The value of the field
    * @access   public 
    * @see      setTag
    */
    function getTag($name, $default = 0) {
        if(empty($this -> $name)) {
            return $default ;
        } else {
            return $this -> $name ;
        }
    }        
    
    /**
     * update the id3v1 tags on the file.
     * Note: If/when ID3v2 is implemented this method will probably get another
     *       parameters.
     *     
     * @param boolean $v1   if true update/create an id3v1 tag on the file. (defaults to true)
     * 
     * @access public
     */
    function write($v1 = true) {
    if ($this->debug) print($this->debugbeg . "write()<HR>\n");
    if ($v1) {
        $this->_write_v1();
    }
    if ($this->debug) print($this->debugend);
    } // write()

    /**
     * study() - does extra work to get the MPEG frame info.
     * 
     * @access public
     */
    function study() {
    $this->studied = true;
    $this->_readframe();
    } // study()

    /**
     * copy($from) - set's the ID3 fields to the same as the fields in $from
     * 
     * @param string    $from   fields to copy
     * @access public
     */
    function copy($from) {
    if ($this->debug) print($this->debugbeg . "copy(\$from)<HR>\n");
    $this->name = $from->name;
    $this->artists  = $from->artists;
    $this->album    = $from->album;
    $this->year = $from->year;
    $this->comment  = $from->comment;
    $this->track    = $from->track;
    $this->genre    = $from->genre;
    $this->genreno  = $from->genreno;
    if ($this->debug) print($this->debugend);
    } // copy($from)

    /**
     * remove - removes the id3 tag(s) from a file.
     *
     * @param boolean   $id3v1  true to remove the tag
     * @param boolean   $id3v2  true to remove the tag (Not yet implemented)
     *
     * @access public
     */
    function remove($id3v1 = true, $id3v2 = true) {
    if ($this->debug) print($this->debugbeg . "remove()<HR>\n");

    if ($id3v1) {
        $this->_remove_v1();
    }

    if ($id3v2) {
        // TODO: write ID3v2 code
    }

    if ($this->debug) print($this->debugend);
    } // remove


    /**
     * read a ID3 v1 or v1.1 tag from a file
     *
     * $file should be the path to the mp3 to look for a tag.
     * When in doubt use the full path.
     *
     * @return mixed    PEAR_Error if fails
     * @access private
     */
    function _read_v1() {
    if ($this->debug) print($this->debugbeg . "_read_v1()<HR>\n");

    if (! ($f = @fopen($this->file, 'rb')) ) {
        return PEAR::raiseError( "Unable to open " . $this->file, PEAR_MP3_ID_FNO);
    }

    if (fseek($f, -128, SEEK_END) == -1) {
        return PEAR::raiseError( 'Unable to see to end - 128 of ' . $this->file, PEAR_MP3_ID_RE);
    }

    $r = fread($f, 128);
    fclose($f);

    if ($this->debug) {
        $unp = unpack('H*raw', $r);
        print_r($unp);
    }

    $id3tag = $this->_decode_v1($r);

    if(!PEAR::isError( $id3tag)) {
        $this->id3v1 = true;

        $tmp = explode(Chr(0), $id3tag['NAME']);
        $this->name = $tmp[0];

        $tmp = explode(Chr(0), $id3tag['ARTISTS']);
        $this->artists = $tmp[0];

        $tmp = explode(Chr(0), $id3tag['ALBUM']);
        $this->album = $tmp[0];

        $tmp = explode(Chr(0), $id3tag['YEAR']);
        $this->year = $tmp[0];

        $tmp = explode(Chr(0), $id3tag['COMMENT']);
        $this->comment = $tmp[0];

        if (isset($id3tag['TRACK'])) {
        $this->id3v11 = true;
        $this->track = $id3tag['TRACK'];
        }

        $this->genreno = $id3tag['GENRENO'];
        $this->genre = $id3tag['GENRE'];
    } else {
        return $id3tag ;
        }

    if ($this->debug) print($this->debugend);
    } // _read_v1()

    /**
     * decodes that ID3v1 or ID3v1.1 tag
     *
     * false will be returned if there was an error decoding the tag
     * else an array will be returned
     *
     * @param   string  $rawtag    tag to decode
     * @return  string  decoded tag
     * @access  private
     */
    function _decode_v1($rawtag) {
    if ($this->debug) print($this->debugbeg . "_decode_v1(\$rawtag)<HR>\n");

    if ($rawtag[125] == Chr(0) and $rawtag[126] != Chr(0)) {
        // ID3 v1.1
        $format = 'a3TAG/a30NAME/a30ARTISTS/a30ALBUM/a4YEAR/a28COMMENT/x1/C1TRACK/C1GENRENO';
    } else {
        // ID3 v1
        $format = 'a3TAG/a30NAME/a30ARTISTS/a30ALBUM/a4YEAR/a30COMMENT/C1GENRENO';
    }

    $id3tag = unpack($format, $rawtag);
    if ($this->debug) print_r($id3tag);

    if ($id3tag['TAG'] == 'TAG') {
        $id3tag['GENRE'] = $this->getgenre($id3tag['GENRENO']);
    } else {
        $id3tag = PEAR::raiseError( 'TAG not found', PEAR_MP3_ID_TNF);
    }
    if ($this->debug) print($this->debugend);
    return $id3tag;
    } // _decode_v1()


    /**
     * writes a ID3 v1 or v1.1 tag to a file
     *
     * @return mixed    returns PEAR_Error when fails
     * @access private
     */
    function _write_v1() {
    if ($this->debug) print($this->debugbeg . "_write_v1()<HR>\n");

    $file = $this->file;

    if (! ($f = @fopen($file, 'r+b')) ) {
        return PEAR::raiseError( "Unable to open " . $file, PEAR_MP3_ID_FNO);
    }

    if (fseek($f, -128, SEEK_END) == -1) {
//        $this->error = 'Unable to see to end - 128 of ' . $file;
        return PEAR::raiseError( "Unable to see to end - 128 of " . $file, PEAR_MP3_ID_RE);
    }

    $this->genreno = $this->getgenreno($this->genre, $this->genreno);

    $newtag = $this->_encode_v1();

    $r = fread($f, 128);

    if ( !PEAR::isError( $this->_decode_v1($r))) {
        if (fseek($f, -128, SEEK_END) == -1) {
//        $this->error = 'Unable to see to end - 128 of ' . $file;
        return PEAR::raiseError( "Unable to see to end - 128 of " . $file, PEAR_MP3_ID_RE);
        }
        fwrite($f, $newtag);
    } else {
        if (fseek($f, 0, SEEK_END) == -1) {
//        $this->error = 'Unable to see to end of ' . $file;
        return PEAR::raiseError( "Unable to see to end of " . $file, PEAR_MP3_ID_RE);
        }
        fwrite($f, $newtag);
    }
    fclose($f);


    if ($this->debug) print($this->debugend);
    } // _write_v1()

    /*
     * encode the ID3 tag
     *
     * the newly built tag will be returned
     *
     * @return string the new tag
     * @access private
     */
    function _encode_v1() {
    if ($this->debug) print($this->debugbeg . "_encode_v1()<HR>\n");

    if ($this->track) {
        // ID3 v1.1
        $id3pack = 'a3a30a30a30a4a28x1C1C1';
        $newtag = pack($id3pack,
            'TAG',
            $this->name,
            $this->artists,
            $this->album,
            $this->year,
            $this->comment,
            $this->track,
            $this->genreno
              );
    } else {
        // ID3 v1
        $id3pack = 'a3a30a30a30a4a30C1';
        $newtag = pack($id3pack,
            'TAG',
            $this->name,
            $this->artists,
            $this->album,
            $this->year,
            $this->comment,
            $this->genreno
              );
    }

    if ($this->debug) {
        print('id3pack: ' . $id3pack . "\n");
        $unp = unpack('H*new', $newtag);
        print_r($unp);
    }

    if ($this->debug) print($this->debugend);
    return $newtag;
    } // _encode_v1()

    /**
     * if exists it removes an ID3v1 or v1.1 tag
     *
     * returns true if the tag was removed or none was found
     * else false if there was an error
     * 
     * @return boolean true, if the tag was removed
     * @access private
     */
    function _remove_v1() {
    if ($this->debug) print($this->debugbeg . "_remove_v1()<HR>\n");

    $file = $this->file;

    if (! ($f = fopen($file, 'r+b')) ) {
        return PEAR::raiseError( "Unable to open " . $file, PEAR_MP3_ID_FNO);
    }

    if (fseek($f, -128, SEEK_END) == -1) {
        return PEAR::raiseError( 'Unable to see to end - 128 of ' . $file, PEAR_MP3_ID_RE);
    }

    $r = fread($f, 128);

    $success = false;
    if ( !PEAR::isError( $this->_decode_v1($r))) {
        $size = filesize($this->file) - 128;
        if ($this->debug) print('size: old: ' . filesize($this->file));
        $success = ftruncate($f, $size);    
        clearstatcache();
        if ($this->debug) print(' new: ' . filesize($this->file));
    }
    fclose($f);
    if ($this->debug) print($this->debugend);
    return $success;
    } // _remove_v1()

    /**
    * reads a frame from the file
    *
    * @return mixed PEAR_Error when fails
    * @access private
    */
    function _readframe() {
    if ($this->debug) print($this->debugbeg . "_readframe()<HR>\n");

    $file = $this->file;

    if (! ($f = fopen($file, 'rb')) ) {
        if ($this->debug) print($this->debugend);
        return PEAR::raiseError( "Unable to open " . $file, PEAR_MP3_ID_FNO) ;
    }

    $this->filesize = filesize($file);

    do {
        while (fread($f,1) != Chr(255)) { // Find the first frame
        if ($this->debug) echo "Find...\n";
        if (feof($f)) {
            if ($this->debug) print($this->debugend);
            return PEAR::raiseError( "No mpeg frame found", PEAR_MP3_ID_NOMP3) ;
        }
        }
        fseek($f, ftell($f) - 1); // back up one byte

        $frameoffset = ftell($f);

        $r = fread($f, 4);
        // Binary to Hex to a binary sting. ugly but best I can think of.
        $bits = unpack('H*bits', $r);
        $bits =  base_convert($bits['bits'],16,2);
    } while (!$bits[8] and !$bits[9] and !$bits[10]); // 1st 8 bits true from the while
    if ($this->debug) print('Bits: ' . $bits . "\n");

    $this->frameoffset = $frameoffset;

    fclose($f);

    if ($bits[11] == 0) {
        $this->mpeg_ver = "2.5";
        $bitrates = array(
            '1' => array(0, 32, 48, 56, 64, 80, 96, 112, 128, 144, 160, 176, 192, 224, 256, 0),
            '2' => array(0,  8, 16, 24, 32, 40, 48,  56,  64,  80,  96, 112, 128, 144, 160, 0),
            '3' => array(0,  8, 16, 24, 32, 40, 48,  56,  64,  80,  96, 112, 128, 144, 160, 0),
                 );
    } else if ($bits[12] == 0) {
        $this->mpeg_ver = "2";
        $bitrates = array(
            '1' => array(0, 32, 48, 56, 64, 80, 96, 112, 128, 144, 160, 176, 192, 224, 256, 0),
            '2' => array(0,  8, 16, 24, 32, 40, 48,  56,  64,  80,  96, 112, 128, 144, 160, 0),
            '3' => array(0,  8, 16, 24, 32, 40, 48,  56,  64,  80,  96, 112, 128, 144, 160, 0),
                 );
    } else {
        $this->mpeg_ver = "1";
        $bitrates = array(
            '1' => array(0, 32, 64, 96, 128, 160, 192, 224, 256, 288, 320, 352, 384, 416, 448, 0),
            '2' => array(0, 32, 48, 56,  64,  80,  96, 112, 128, 160, 192, 224, 256, 320, 384, 0),
            '3' => array(0, 32, 40, 48,  56,  64,  80,  96, 112, 128, 160, 192, 224, 256, 320, 0),
                 );
    }
    if ($this->debug) print('MPEG' . $this->mpeg_ver . "\n");

    $layer = array(
        array(0,3),
        array(2,1),
              );
    $this->layer = $layer[$bits[13]][$bits[14]];
    if ($this->debug) print('layer: ' . $this->layer . "\n");

    if ($bits[15] == 0) {
        // It's backwards, if the bit is not set then it is protected.
        if ($this->debug) print("protected (crc)\n");
        $this->crc = true;
    }

    $bitrate = 0;
    if ($bits[16] == 1) $bitrate += 8;
    if ($bits[17] == 1) $bitrate += 4;
    if ($bits[18] == 1) $bitrate += 2;
    if ($bits[19] == 1) $bitrate += 1;
    $this->bitrate = $bitrates[$this->layer][$bitrate];

    $frequency = array(
        '1' => array(
            '0' => array(44100, 48000),
            '1' => array(32000, 0),
                ),
        '2' => array(
            '0' => array(22050, 24000),
            '1' => array(16000, 0),
                ),
        '2.5' => array(
            '0' => array(11025, 12000),
            '1' => array(8000, 0),
                  ),
          );
    $this->frequency = $frequency[$this->mpeg_ver][$bits[20]][$bits[21]];

    $this->padding = $bits[22];
    $this->private = $bits[23];

    $mode = array(
        array('Stereo', 'Joint Stereo'),
        array('Dual Channel', 'Mono'),
             );
    $this->mode = $mode[$bits[24]][$bits[25]];

    // XXX: I dunno what the mode extension is for bits 26,27

    $this->copyright = $bits[28];
    $this->original = $bits[29];

    $emphasis = array(
        array('none', '50/15ms'),
        array('', 'CCITT j.17'),
             );
    $this->emphasis = $emphasis[$bits[30]][$bits[31]];

    if ($this->bitrate == 0) {
        $s = -1;
    } else {
        $s = ((8*filesize($this->file))/1000) / $this->bitrate;        
    }
    $this->length = sprintf('%02d:%02d',floor($s/60),floor($s-(floor($s/60)*60)));
    $this->lengths = (int)$s;

    if ($this->debug) print($this->debugend);
    } // _readframe()
    
    
    /**
    * read ID3v2 tag from a file
    * 
    * @access private
    */
    function _read_v2() {
        if (!file_exists($this->mp3infile)) {
            return 0;
        }
        $tagOffset =0;
        $frames = array();
        
        // Bad Frames - (courtesy of eyeD3 http://eyed3.nicfit.net/ )
        $knownBadFrames = array("\x00\x00MP",
                                "\x00MP3",
                                " MP3",
                                "MP3e",
                                "\x00MP",
                                " MP",
                                "MP3",
                                "COM ",
                                "TCP ", // iTunes
                                "CM1 "  // Script kiddie
                                );
        
        $fp = fopen($this->mp3infile, 'rb');
        if (!$fp) {
            return PEAR::raiseError( "Unable to open " . $this->mp3infile, PEAR_MP3_ID_FNO);
        }
        
        // skip first 3 bytes 
        fseek($fp, 3);
        
        // get version/revision numbers
        $major = 2;
        $minor = ord(fread($fp, 1));
        $rev   = ord(fread($fp, 1));
        
        if ($this->debug) print "found tag version ".$major.".".$minor.".".$rev."\n";
        
        // flags (3 bits for 2.3.0 and 4 for 2.4.0 respectively)
        $flags = array();
        $flagbyte = ord(fread($fp, 1));
        $flags['unsync']        = (bool) ($flagbyte & 64);
        $flags['extended']      = (bool) ($flagbyte & 32);
        $flags['experimental']  = (bool) ($flagbyte & 16);
        $flags['footer']        = (bool) ($flagbyte & 8);
        
        // 4 bytes synchsafe int for tagsize
        // (extended header, frames and padding)
        $tagSize = $this->_BigEnd2Int(fread($fp, 4), 1) + 10;
        if ($this->debug) print "tag size: ".$tagSize."\n";
        
        // parse extended header 
        $extHeaderLen = 0;
        $extHeaders = array();
        if ($flags['extended']==True) {
            // FIXME: we should parse ext. header but for now we just skip ...
            switch($minor) {
                case 3:
                    $extHeaderLen = $this->_BigEnd2Int(fread($fp, 1));
                    $extHeaderBytes = fread($fp, $extHeaderLen -1);
                break;
                case 4:
                    $extHeaderLen = $this->_BigEnd2Int(fread($fp, 4), 1);
                    $extHeaderBytes = fread($extHeaderLen -4);
                break;
                default:
                break;
            }
        }
        
        if ($this->debug) print "external headersize: ".$extHeaderLen."\n";
        $frameLen = $tagSize - 10 - $extHeaderlen;
        if ($flags['footer']==True) {
            $frameLen -= 10;
        }
        
        // read framedata
        // FIXME: check whether pointer is on right position
        $framedata = fread($fp, $frameLen);
        if (($flags['unsync']==True) && ($minor <= 3)) {
            $framedata = $this->_DeUnsynchronise($framedata);
        }
        
        
        while(isset($framedata) && strlen($framedata) > 0) {
            if (strlen($framedata) <= 10) {
                // Out of range .. padding .. whatever
                break;
            }
            
            if ($minor > 2) {
                // Frame ID  $xx xx xx xx (four characters)
                // Size      $xx xx xx xx (32-bit integer in v2.3, 28-bit synchsafe in v2.4+)
                // Flags     $xx xx
                
                $frameHeader = substr($framedata, 0, 10);
                $framedata = substr($framedata, 10);
                $frameName = substr($frameHeader, 0, 4);
                $frameSizeBytes = substr($frameHeader, 4, 4);
                if ($minor == 3) {
                    // 2.3
                    $frameSize = $this->_BigEnd2Int($frameSizeBytes);
                } else {
                    // 2.4
                    $frameSize = $this->_BigEnd2Int($frameSizeBytes, 1);
                }
                
                if ($frameName == "\x00\x00\x00" || $frameName== "\x00\x00\x00\x00") {
                    // padding
                    break;
                }
                
                if (!$this->_isValidID3v2FrameName($frameName, $minor) ||
                    in_array($frameName, $knownBadFrames)) {
                    continue;
                }
                
                $frameInfo = $this->_v2LookupFrame($frameName, $minor);
                if ($frameInfo) {
                    $data = unpack("a*", substr($framedata, 0, $frameSize));
                    $frameInfo['data'] = trim(str_replace("\x00","", $data[1]));
                    $frames[$frameName] = $frameInfo;
                }
                
                
                $framedata = substr($framedata, $frameSize);
                
            } else {
            
                break;
            }
        }
        
        $this->_map2V1($frames);
        
        fclose($fp);
    }
    
    
    function _map2V1($frames) {
        $conv = array('name' => 'TIT2', 'artists'=>'TOPE','album'=>'TALB',
                      'year' => 'TYER', 'comment'=>'COMM','track'=>'TRCK',
                      'genre'=> 'TCON');
        foreach($conv as $v1name=>$v2name) {
            if (isset($frames[$v2name])) {
                $this->$v1name = $frames[$v2name]['data'];
            }
        }
    }
    
    
    /**
    *
    * @access private
    */
    function _BigEnd2Int($bytestr, $synchsafe=False) {
        $intv = 0;
        $bytestrlen = strlen($bytestr);
        for($i=0; $i<$bytestrlen; $i++) {
            if ($synchsafe) {
                $intv = $intv | (ord($bytestr{$i}) & 127) << (($bytestrlen - 1 - $i) * 7);
            } else {
                $intv += ord($bytestr{$i}) * pow(256, ($bytestrlen - 1 - $i));
            }
        }
        
        return $intv;
    }
    
    function _DeUnsynchronise($data) {
        return str_replace("\xFF\x00", "\xFF", $data);
    }
    
    
    function _isValidID3v2FrameName($name, $minor) {
        switch($minor) {
            case 2:
                return preg_match("#^[A-Z][A-Z0-9]{2}#", $name);
            break;
            case 3:
            case 4:
                return preg_match("#^[A-Z][A-Z0-9]{3}#", $name);
            break;
        }
        
        return False;
    }
    
    
    
    function _v2LookupFrame($frameName, $minor) {
        // acc. to http://www.id3.org/id3v2.4.0-frames.txt
        $default = array('AENC' => array('desc'=>'Audio encryption'),
                         'APIC' => array('desc'=>'Attached picture'),
                         'ASPI' => array('desc'=>'Audio seek point index'),
                         'COMM' => array('desc'=>'Comments'),
                         'COMR' => array('desc'=>'Commercial frame'),
                         'ENCR' => array('desc'=>'Encryption method registration'),
                         'EQU2' => array('desc'=>'Equalisation (2)'),
                         'ETCO' => array('desc'=>'Event timing codes'),
                         'GEOB' => array('desc'=>'General encapsulated object'),
                         'GRID' => array('desc'=>'Group identification registration'),
                         'LINK' => array('desc'=>'Linked information'),
                         'MCDI' => array('desc'=>'Music CD identifier'),
                         'MLLT' => array('desc'=>'MPEG location lookup table'),
                         'OWNE' => array('desc'=>'Ownership frame'),
                         'PRIV' => array('desc'=>'Private Frame'),
                         'PCNT' => array('desc'=>'Play counter'),
                         'POPM' => array('desc'=>'Popularimeter'),
                         'POSS' => array('desc'=>'Position synchronisation frame'),
                         'RBUF' => array('desc'=>'Recommended buffer size'),
                         'RVA2' => array('desc'=>'Relative volume adjustment (2)'),
                         'RVRB' => array('desc'=>'Reverb'),
                         'SEEK' => array('desc'=>'Seek frame'),
                         'SIGN' => array('desc'=>'Signature frame'),
                         'SYLT' => array('desc'=>'Synchronized lyric/text'),
                         'SYTC' => array('desc'=>'Synchronized tempo codes'),
                         'TALB' => array('desc'=>'Album/Movie/Show title'),
                         'TBPM' => array('desc'=>'BPM (Beats per minute)'),
                         'TCOM' => array('desc'=>'Composer'),
                         'TCON' => array('desc'=>'Content type'),
                         'TCOP' => array('desc'=>'Copyright message'),
                         'TDEN' => array('desc'=>'Encoding time'),
                         'TDLY' => array('desc'=>'Playlist delay'),
                         'TDOR' => array('desc'=>'Original release time'),
                         'TDRC' => array('desc'=>'Recording time'),
                         'TDRL' => array('desc'=>'Release time'),
                         'TDTG' => array('desc'=>'Tagging time'),
                         'TENC' => array('desc'=>'Encoded by'),
                         'TEXT' => array('desc'=>'Lyricist/Text writer'),
                         'TFLT' => array('desc'=>'File type'),
                         'TIPL' => array('desc'=>'Involved people list'),
                         'TIT1' => array('desc'=>'Content group description'),
                         'TIT2' => array('desc'=>'Title/songname/content description'),
                         'TIT3' => array('desc'=>'Subtitle/Description refinement'),
                         'TKEY' => array('desc'=>'Initial key'),
                         'TLAN' => array('desc'=>'Language(s)'),
                         'TLEN' => array('desc'=>'Length'),
                         'TMCL' => array('desc'=>'Musician credit list'),
                         'TMED' => array('desc'=>'Media type'),
                         'TMOO' => array('desc'=>'Mood'),
                         'TOAL' => array('desc'=>'Original album/movie/show title'),
                         'TOFN' => array('desc'=>'Original filename'),
                         'TOLY' => array('desc'=>'Original lyricist(s)/text writer(s)'),
                         'TOPE' => array('desc'=>'Original artist(s)/ performer(s)'),
                         'TOWN' => array('desc'=>'File owner/licensee'),
                         'TPE1' => array('desc'=>'Lead performer(s)/Soloist(s)'),
                         'TPE2' => array('desc'=>'Band/orchestra/accompainment'),
                         'TPE3' => array('desc'=>'Conductor/performer refinement'),
                         'TPE4' => array('desc'=>'Interpreted, remixed or otherwise modified by'),
                         'TPOS' => array('desc'=>'Part of a set'),
                         'TPRO' => array('desc'=>'Produced notice'),
                         'TPUB' => array('desc'=>'Publisher'),
                         'TRCK' => array('desc'=>'Track number/Position in set'),
                         'TRSN' => array('desc'=>'Internet radio station name'),
                         'TRSO' => array('desc'=>'Internet radio station owner'),
                         'TSOA' => array('desc'=>'Album sort order'),
                         'TSOP' => array('desc'=>'Performer sort order'),
                         'TSOT' => array('desc'=>'Title sort order'),
                         'TSRC' => array('desc'=>'ISRC (international standard recording code)'),
                         'TSSE' => array('desc'=>'Software/Hardware and settings used for encoding'),
                         'TSST' => array('desc'=>'Set subtitle'),
                         'TYER' => array('desc'=>'Year'), // 2.3
                         'TXXX' => array('desc'=>'User defined Text information frame'),
                         'UFID' => array('desc'=>'Unique file identifier'),
                         'USER' => array('desc'=>'Terms of use'),
                         'USLT' => array('desc'=>'Unsynchronized lyric/text transcription'),
                         'WCOM' => array('desc'=>'Commercial information'),
                         'WCOP' => array('desc'=>'Copyright/Legal information'),
                         'WOAF' => array('desc'=>'Official audio file webpage'),
                         'WOAR' => array('desc'=>'Official artist/performer website'),
                         'WOAS' => array('desc'=>'Official audio source webpage'),
                         'WORS' => array('desc'=>'Official internet radio station homepage'),
                         'WPAY' => array('desc'=>'Payment'),
                         'WPUB' => array('desc'=>'Publishers official webpage'),
                         'WXXX' => array('desc'=>'User defined URL link frame')
                         );
        if (array_key_exists($frameName, $default)) {
            return $default[$frameName];
        }
        
        return False;
    }
    
    
    /**
     * getGenre - return the name of a genre number
     *
     * if no genre number is specified the genre number from
     * $this->genreno will be used.
     *
     * the genre is returned or false if an error or not found
     * no error message is ever returned
     *
     * @param   integer $genreno Number of the genre
     * @return  mixed   false, if no genre found, else string
     *
     * @access public     
     */
    function getGenre($genreno) {
    if ($this->debug) print($this->debugbeg . "getgenre($genreno)<HR>\n");

    $genres = $this->genres();
    if (isset($genres[$genreno])) {
        $genre = $genres[$genreno];
        if ($this->debug) print($genre . "\n");
    } else {
        $genre = '';
    }

    if ($this->debug) print($this->debugend);
    return $genre;
    } // getGenre($genreno)

    /*
     * getGenreNo - return the number of the genre name
     *
     * the genre number is returned or 0xff (255) if a match is not found
     * you can specify the default genreno to use if one is not found
     * no error message is ever returned
     *
     * @param   string  $genre      Name of the genre
     * @param   integer $default    Genre number in case of genre not found
     *
     * @access public
     */
    function getGenreNo($genre, $default = 0xff) {
    if ($this->debug) print($this->debugbeg . "getgenreno('$genre',$default)<HR>\n");

    $genres = $this->genres();
    $genreno = false;
    if ($genre) {
        foreach ($genres as $no => $name) {
        if (strtolower($genre) == strtolower($name)) {
            if ($this->debug) print("$no:'$name' == '$genre'");
            $genreno = $no;
        }
        }
    }
    if ($genreno === false) $genreno = $default;
    if ($this->debug) print($this->debugend);
    return $genreno;
    } // getGenreNo($genre, $default = 0xff)

    /*
     * genres - returns an array of the ID3v1 genres
     *
     * @return array
     *
     * @access public
     */
    function genres() {
    return array(
        0   => 'Blues',
        1   => 'Classic Rock',
        2   => 'Country',
        3   => 'Dance',
        4   => 'Disco',
        5   => 'Funk',
        6   => 'Grunge',
        7   => 'Hip-Hop',
        8   => 'Jazz',
        9   => 'Metal',
        10  => 'New Age',
        11  => 'Oldies',
        12  => 'Other',
        13  => 'Pop',
        14  => 'R&B',
        15  => 'Rap',
        16  => 'Reggae',
        17  => 'Rock',
        18  => 'Techno',
        19  => 'Industrial',
        20  => 'Alternative',
        21  => 'Ska',
        22  => 'Death Metal',
        23  => 'Pranks',
        24  => 'Soundtrack',
        25  => 'Euro-Techno',
        26  => 'Ambient',
        27  => 'Trip-Hop',
        28  => 'Vocal',
        29  => 'Jazz+Funk',
        30  => 'Fusion',
        31  => 'Trance',
        32  => 'Classical',
        33  => 'Instrumental',
        34  => 'Acid',
        35  => 'House',
        36  => 'Game',
        37  => 'Sound Clip',
        38  => 'Gospel',
        39  => 'Noise',
        40  => 'Alternative Rock',
        41  => 'Bass',
        42  => 'Soul',
        43  => 'Punk',
        44  => 'Space',
        45  => 'Meditative',
        46  => 'Instrumental Pop',
        47  => 'Instrumental Rock',
        48  => 'Ethnic',
        49  => 'Gothic',
        50  => 'Darkwave',
        51  => 'Techno-Industrial',
        52  => 'Electronic',
        53  => 'Pop-Folk',
        54  => 'Eurodance',
        55  => 'Dream',
        56  => 'Southern Rock',
        57  => 'Comedy',
        58  => 'Cult',
        59  => 'Gangsta',
        60  => 'Top 40',
        61  => 'Christian Rap',
        62  => 'Pop/Funk',
        63  => 'Jungle',
        64  => 'Native US',
        65  => 'Cabaret',
        66  => 'New Wave',
        67  => 'Psychadelic',
        68  => 'Rave',
        69  => 'Showtunes',
        70  => 'Trailer',
        71  => 'Lo-Fi',
        72  => 'Tribal',
        73  => 'Acid Punk',
        74  => 'Acid Jazz',
        75  => 'Polka',
        76  => 'Retro',
        77  => 'Musical',
        78  => 'Rock & Roll',
        79  => 'Hard Rock',
        80  => 'Folk',
        81  => 'Folk-Rock',
        82  => 'National Folk',
        83  => 'Swing',
        84  => 'Fast Fusion',
        85  => 'Bebob',
        86  => 'Latin',
        87  => 'Revival',
        88  => 'Celtic',
        89  => 'Bluegrass',
        90  => 'Avantgarde',
        91  => 'Gothic Rock',
        92  => 'Progressive Rock',
        93  => 'Psychedelic Rock',
        94  => 'Symphonic Rock',
        95  => 'Slow Rock',
        96  => 'Big Band',
        97  => 'Chorus',
        98  => 'Easy Listening',
        99  => 'Acoustic',
        100 => 'Humour',
        101 => 'Speech',
        102 => 'Chanson',
        103 => 'Opera',
        104 => 'Chamber Music',
        105 => 'Sonata',
        106 => 'Symphony',
        107 => 'Booty Bass',
        108 => 'Primus',
        109 => 'Porn Groove',
        110 => 'Satire',
        111 => 'Slow Jam',
        112 => 'Club',
        113 => 'Tango',
        114 => 'Samba',
        115 => 'Folklore',
        116 => 'Ballad',
        117 => 'Power Ballad',
        118 => 'Rhytmic Soul',
        119 => 'Freestyle',
        120 => 'Duet',
        121 => 'Punk Rock',
        122 => 'Drum Solo',
        123 => 'Acapella',
        124 => 'Euro-House',
        125 => 'Dance Hall',
        126 => 'Goa',
        127 => 'Drum & Bass',
        128 => 'Club-House',
        129 => 'Hardcore',
        130 => 'Terror',
        131 => 'Indie',
        132 => 'BritPop',
        133 => 'Negerpunk',
        134 => 'Polsk Punk',
        135 => 'Beat',
        136 => 'Christian Gangsta Rap',
        137 => 'Heavy Metal',
        138 => 'Black Metal',
        139 => 'Crossover',
        140 => 'Contemporary Christian',
        141 => 'Christian Rock',
        142 => 'Merengue',
        143 => 'Salsa',
        144 => 'Trash Metal',
        145 => 'Anime',
        146 => 'Jpop',
        147 => 'Synthpop'
            );
    } // genres
} // end of id3

?>
