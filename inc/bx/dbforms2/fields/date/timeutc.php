<?php
// +----------------------------------------------------------------------+
// | Flux CMS                                                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2007 Liip AG                                      |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License (GPL)    |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// | The GPL can be found at http://www.gnu.org/licenses/gpl.html         |
// +----------------------------------------------------------------------+
// | Author: Liip AG      <contact@liip.ch>                               |
// +----------------------------------------------------------------------+
//
// $Id: date.php 4898 2005-07-05 08:52:18Z philipp $

/**
 * DOCUMENT_ME
 *
 * @package bx_dbforms2
 * @category 
 * @author Liip AG      <contact@liip.ch>
 */
class bx_dbforms2_fields_date_timeutc extends bx_dbforms2_fields_date {

    
    /**
     *  DOCUMENT_ME
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    public function getSQLValue() {
        return $this->fixDate($this->value);
    }
    
    public function getSQLName() {
        $timezone = bx_helpers_config::getTimezoneAsSeconds();
        if ($this->parentForm->queryMode == bx_dbforms2::QUERYMODE_SELECT) {
            return "date_format(date_add(".$this->name.", INTERVAL ".$timezone." SECOND),'%Y-%m-%d %H:%i:%S+".($timezone / 3600).":00') as " . $this->name;
        } else {
            return $this->name;
        }
    }
    
    protected function fixDate($date) {
        if (!$date ) {
            return "";
        }
        $date =  preg_replace("/([0-9]{1,2})\.([0-9]{1,2})\.([0-9]{2,4})/","$3-$2-$1",$date);
        $date =  preg_replace("/([0-9])T([0-9])/","$1 $2",$date);
        $date =  preg_replace("/([\+\-][0-9]{2}):([0-9]{2})/","$1$2",$date);
        $date = strtotime($date);
       
        return  gmdate("Y-m-d H:i:s",$date);
    }
    
   protected function getXMLAttributes() {
        return array(
            'size' => 30,
            'maxlength' => 50
        );
    }
    
    
    
}

?>