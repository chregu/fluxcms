<?php
// +----------------------------------------------------------------------+
// | Bitflux CMS                                                          |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2007 Liip AG                                      |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License (GPL)    |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// | The GPL can be found at http://www.gnu.org/licenses/gpl.html         |
// +----------------------------------------------------------------------+
// | Author: Christian Stocker <chregu@liip.ch>                        |
// +----------------------------------------------------------------------+
//
// $Id$

/**
* this file contains a set of function which are used in
*    different classes/files.
*
* TODO:
*       Documentation, Error Checking, Examples
*
*    @author   Christian Stocker <chregu@liip.ch>
*    @version  $Id$
*    @package  functions
*    @access   public
*/


class bx_helpers_db {

    static function getConfigClass ($configFile,$options=Null)
    {
        if (is_string($configFile) || is_array($configFile))
        {
         
            include_once("Config.php");
            if (is_array($configFile))
            {
                foreach ($configFile as $file) {
                    if (!file_exists($file)) {
                        throw new PopoonFileNotFoundException($file);
                    }
                }
            }
            elseif  (!file_exists($configFile) )
            {
                      throw new PopoonFileNotFoundException($configFile);
            }
            $config = new Config("xml");

            if ($options == Null)
            {
                $options= array(    "TakeContent"=>False,
                                    "MasterAttribute"=>False,
                                    "PrintMasterAttribute"=>False,
                                    "IncludeChildren"=>True,
                                    "KeyAttribute"=>"name"
                               );
            }

            $ret = $config->parseInput( $configFile,$options);
            return $config;


        }

        elseif ( get_class ($configFile) == "Config") {
            return $configFile;

        }

        else {
            
            self::raiseError("$configFile is neither a string (filename) nor a config-class-object", __LINE__,__FILE__,$configFile);

        }
    }



    function raiseError(   $msg,$line,$file,$variable)
    {
//        include_once("functions/common.php");
        print "IBA Error<br>";
        print "$msg<br>";
        print "File: $file<br>";
        print "Line: $line<br>";

        print "<hr>";
        debug::print_rp($variable);
        die;
    }
    function getDbFromConfig ($config,$path=Null) {
        $config = bx_plugins_dbform_common::getConfigClass($config);
        return bx_plugins_dbform_common::getDBFromDsn(bx_plugins_dbform_common::getDsnFromConfig($config,$path));
    }
    function getDbFromDsn  ($dsn, $dboptions = array()) {
        
        if (is_string($dsn) || is_array($dsn))
        {
            include_once ("MDB2.php");
            PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, 'handle_pear_error');
            $db = MDB2::Connect($dsn,$dboptions);
            if (MDB2::isError($db))
            {
                print "The given dsn was not valid in file ".__FILE__." at line ".__LINE__."<br>\n";
                return new MDB2_Error($db->code,PEAR_ERROR_DIE);
            }

        }
        
            
        elseif (is_object($dsn) && MDB2::isError($dsn))
        {
            print "The given param  was not valid in file ".__FILE__." at line ".__LINE__."<br>\n";
            return new MDB2_Error($dsn->code,PEAR_ERROR_DIE);
        }

        // if parent class is dbform_common, then it's already a connected identifier
        elseif ($dsn instanceof MDB2_Driver_Common )
        {
            $db = $dsn;
        }
        return $db;
    }
    
    function getDsnFromConfig ($config,$path = Null )
    {
        $config = bx_plugins_dbform_common::getConfigClass($config);
        
        if (! $path ) { $path = "/config/db";}
        $db = $config->getValues( $path);
        if (PEAR::isError($db)) {
            print "Config Error:<br/>";
            print $db->getMessage();
            print "<br/>";
            print $db->getUserInfo();
            die();
        }
        return  $db['dsn'];
    } //end func getDsn

    function read_file ($file)
    {

        $fd = fopen( $file, "r" );
        $content = fread( $fd, filesize( $file ) );
        fclose( $fd );
        return $content;
    }
    
    function getDb($dsn) {
        return bx_plugins_dbform_common::getDBFromDsn($dsn);
    }

}


//haha, aus dem genialen buch "PHP de Luxe"
function handle_pear_error ($error_obj) {
  if (get_class($error_obj) == "dbform_error")
  {
    print "Datenbank-Fehler:<br>\n";
  }
  else
  {
    print "Sonstiger PEAR-Fehler:<br>\n";
  }
  die ($error_obj->getMessage()."\n<br>".$error_obj->getDebugInfo());
}


