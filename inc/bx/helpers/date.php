<?php

class bx_helpers_date {
    
    static function getWeekOfYear($stamp = NULL) {
        return date('W', $stamp);
    }

    static function getWeeksOfYear($in_year) {
        // copied from user notes at http://php.net/manual/en/function.date.php
        $in_starttime = mktime(0, 0, 0, 1, 1, $in_year);
        $in_endtime = mktime(0, 0, 0, 12, 31, $in_year);
        while($in_starttime <= $in_endtime){
           $in_week = date("W", $in_starttime);
           $weeks = $in_week;
           $in_starttime += 604800;
        }
        if ($weeks == 1){
           $weeks = date("W", $in_starttime-604800*2);
        }
        return $weeks;
   }    
    
    static function getWeekOfMonth($stamp = NULL) {
        //$stamp = mktime(0, 0, 0, 2, 8, 2005);
        if(!isset($stamp)) {
            $stamp = time();
        }
        $d = getdate($stamp);
        $firstDayOfMonth = mktime(0, 0, 0, $d['mon'], 1, $d['year']);
        $woyStamp = self::getWeekOfYear($stamp);
        $woyFirst = self::getWeekOfYear($firstDayOfMonth);
        $wom =  ($woyStamp - $woyFirst) + 1;

        // check for underflow and fix (1st of January may still be the 53rd week)
        // see http://www.iso.org/iso/en/prods-services/popstds/datesandtime.html for
        // more information
        if($wom < 0) {
            $wom = $wom + self::getWeeksOfYear($d['year']);
        }
        
        return $wom > 4 ? 4 : $wom;
    }
    

    static function getDateFormatted($f=Null) {
    	if ($f==Null) {
	    $f="d-m-Y H:i:s";
	}
	
	return date($f);
    }

    static function normalizeDate($timestr) {
        // FIXME: ugly regex
        preg_match("#^([0-9]{2}).([0-9]{2}).([0-9]{4})\s(([0-9]{2}:?){3})#", $timestr, $matches);
        if (sizeof($matches)==6) {
            $isodate = sprintf("%d-%s-%d %s", $matches[3], $matches[2], $matches[1], $matches[4]);
            return $isodate;
        }
        
        return $timestr;
    
    }

    static function getRFCDate($date) {
        if (($ts = strtotime($date)) === -1) {
            return $date;
        } else {
            return date("r", $ts);
        }
    }
    
    static function getDateFormattedFromString($format,$date) {
        $ts = strtotime($date);
        return date($format,$ts);
    }
}
