#!/usr/local/bin/php -q
<?php
/**
*
* A quick and dirty script to fetch delicious links via cron and insert them into the linklog plugin
*
* $Id$
*/


include_once("../../inc/bx/init.php");

bx_init::start('conf/config.xml',"../../");
$db = $GLOBALS['POOL']->db;


define('MAGPIE_CACHE_DIR',BX_TEMP_DIR.'magpie/');
include_once('magpie/rss_fetch.inc');

$feeduris[] = $myuri = 'http://del.icio.us/rss/alain';
$feeduris[] = 'http://del.icio.us/rss/chregu';
$allentries = array();

foreach($feeduris as $feeduri){
    $rss = fetch_rss($feeduri);
    foreach($rss->items as $feed)
    {           
        $feed['date']    = bx_plugins_aggregator::getDcDate($feed);
        $feed['dateiso'] = gmdate("Y-m-d\TH:i:s\Z",strtotime($feed['date']));
        $feed['name']    = $rss->channel['title'];
        $links[]    = $feed;
    }
}

usort($links,  array(bx_plugins_aggregator,"sortByDate"));

$editor = new bx_editors_linklog();

$mycleaneduri = simpleCleanUri($myuri);

// print $mycleaneduri;

foreach($links as $link){
    
    $data = array(
        'title' => $link['title'],
        'url'   => $link['link'],
        'description' => $link['description'],        
        'tags' => $link['dc']['subject'],
        'time' => $link['date'],
        // time 2007-06-04 22:12:50
    );
    
    if( ! strpos($link['name'], $mycleaneduri) ){
        $data['via'] .= '' . end( explode ("/", simpleCleanUri($link['name']) ) ) . '';
    }
    
    $res = $editor->insertLink($data);

}

function simpleCleanUri($myuri){
    return str_replace(array('http://', 'rss'), array('',''), $myuri);
}



?>