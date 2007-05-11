<?php
// +----------------------------------------------------------------------+
// | Bitflux CMS                                                          |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2006 Liip AG                                      |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License (GPL)    |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// | The GPL can be found at http://www.gnu.org/licenses/gpl.html         |
// +----------------------------------------------------------------------+
// | Author: Liip AG      <devel@liip.ch>                              |
// +----------------------------------------------------------------------+
//
// $Id$


class bx_helpers_pager {

    protected static $pagers = array();    

    public static function initPager($id) {
        self::$pagers[$id] = array(
            'numberOfEntries' => 1,
            'currentPage' => 1,
            'entriesPerPage' => 1,
            'numberOfPages' => 1
        );
    }
    
    public static function setNumberOfEntries($id, $num) {
        self::$pagers[$id]['numberOfEntries'] = $num;
    }

    public static function setCurrentPage($id, $num) {
        self::$pagers[$id]['currentPage'] = $num;
    }

    public static function getCurrentPage($id) {
        return self::$pagers[$id]['currentPage'];
    }

    public static function setEntriesPerPage($id, $num) {
        self::$pagers[$id]['entriesPerPage'] = $num;
    }
    
    public static function setValues($max,$currpage, $entriesperpage,$id) {
        self::initPager($id);
        self::$pagers[$id]['numberOfEntries'] = $max;
        self::$pagers[$id]['currentPage'] = $currpage;
        self::$pagers[$id]['entriesPerPage'] = $entriesperpage;
    }
    
    public static function getValues($id) {
        return self::$pagers[$id];
    }

    public static function getNumberOfPages($id) {
        return (self::$pagers[$id]['numberOfEntries'] > 0) ? ceil(self::$pagers[$id]['numberOfEntries'] / self::$pagers[$id]['entriesPerPage']) : 1;
    }
    
    public static function getNextPage($id) {
        if(!isset(self::$pagers[$id]['currentPage'])) {
            return FALSE;
        }
        return (self::$pagers[$id]['currentPage'] + 1 <= self::getNumberOfPages($id)) ? (self::$pagers[$id]['currentPage'] + 1) : self::getNumberOfPages($id);
    }

    public static function getNextPageFixed($id) {
        if(!isset(self::$pagers[$id]['currentPage'])) {
            return FALSE;
        }
        return (self::$pagers[$id]['currentPage'] + 1 < self::getNumberOfPages($id)) ? (self::$pagers[$id]['currentPage'] + 1) : null;
    }
    
    public static function getPrevPage($id) {
        if(!isset(self::$pagers[$id]['currentPage'])) {
            return FALSE;
        }
        return (self::$pagers[$id]['currentPage'] - 1 >= 1) ? (self::$pagers[$id]['currentPage'] - 1) : 1;
    }
    
    public static function getPrevPageFixed($id) {
        if(!isset(self::$pagers[$id]['currentPage'])) {
            return FALSE;
        }
        return (self::$pagers[$id]['currentPage'] - 1 >= 0) ? (self::$pagers[$id]['currentPage'] - 1) : null;
    }
    
    public static function getLastDoc($id) {
        if(!isset(self::$pagers[$id]['currentPage'])) {
            return FALSE;
        }
        return (self::$pagers[$id]['currentPage'] + 1 <  self::getNumberOfPages($id)) ? 
        ((self::$pagers[$id]['currentPage'] + 1 )* self::$pagers[$id]['entriesPerPage']) : self::$pagers[$id]['numberOfEntries'];            
    }
   
     public static function getFirstDoc($id) {
        if(!isset(self::$pagers[$id]['currentPage'])) {
            return FALSE;
        }
        return (self::$pagers[$id]['currentPage']  * self::$pagers[$id]['entriesPerPage']) + 1;            
    }
}

?>
