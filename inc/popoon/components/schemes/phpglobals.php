<?php
// +----------------------------------------------------------------------+
// | popoon                                                               |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001,2002,2003,2004 Bitflux GmbH                       |
// +----------------------------------------------------------------------+
// | Licensed under the Apache License, Version 2.0 (the "License");      |
// | you may not use this file except in compliance with the License.     |
// | You may obtain a copy of the License at                              |
// | http://www.apache.org/licenses/LICENSE-2.0                           |
// | Unless required by applicable law or agreed to in writing, software  |
// | distributed under the License is distributed on an "AS IS" BASIS,    |
// | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
// | implied. See the License for the specific language governing         |
// | permissions and limitations under the License.                       |
// +----------------------------------------------------------------------+
// | Author: Christian Stocker <chregu@bitflux.ch>                        |
// +----------------------------------------------------------------------+
//
// $Id: phpglobals.php 5093 2005-07-20 10:25:57Z silvan $

/**
* Return a phpglobals 
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: phpglobals.php 5093 2005-07-20 10:25:57Z silvan $
* @package  popoon
* @module   schemes_phpglobals
*/
function scheme_phpglobals($value)
{
    if (strpos($value,"[")) {
        preg_match_all("/(.*)(\[['\"]*([^'\"]*)['\"]*\])/U",$value,$matches);
        preg_match("/\|(.*)$/", $value, $defaults);
        $depth = count($matches[3]);
        $return = null;
        if ($matches[1][0] == '_SESSION') {
            session_start();
        }
        if ($depth == 1 && isset($GLOBALS[$matches[1][0]][$matches[3][0]])) {
            $return = $GLOBALS[$matches[1][0]][$matches[3][0]];
        } 
        else if ($depth == 2 && isset($GLOBALS[$matches[1][0]][$matches[3][0]][$matches[3][1]])) {
            $return = $GLOBALS[$matches[1][0]][$matches[3][0]][$matches[3][1]];
        } 
         
        if (($return == null) && (isset($defaults[1]))) {
            return $defaults[1];
        } else {
            
            return $return;
        }

        
    } else {
        return $GLOBALS[$value];
    }
}

function scheme_phpglobals_onSitemapGeneration($value) {
        $var = '$'.str_replace("[","['",str_replace("]","']",$value));
        $value = "'. isset(".$var.") ? " . $var . " : NULL .'";
        return $value;
    
    
}
