<?php
/*
CREATE TABLE `bxcms_events` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) default NULL,
  `link` varchar(255) default NULL,
  `von` datetime default NULL,
  `bis` datetime default NULL,
  `description` text,
  `uri` varchar(255) default NULL,
  `location` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) ;

and put in .configxml:

<bxcms xmlns="http://bitflux.org/config">
    <plugins>
        <parameter name="xslt" type="pipeline" value="events.xsl"/>
        <extension type="html"/>
        <plugin type="events">
            <parameter name="dateformat" value="d/m/Y H:i"/>
        </plugin>
        <plugin type="navitree"></plugin>
    </plugins>

    <plugins>
        <parameter name="xslt" type="pipeline" value="../standard/text.xsl"/>
        <parameter name="output-mimetype" type="pipeline" value="text/plain"/>
        <extension type="ics"/>
        <plugin type="events">
            <parameter name="calid" value="somerandomid"/>
            <parameter name="calname" value="Test Events2"/>
            <parameter name="caldesc" value="This is a test"/>
            <parameter name="dateformat" value="This is a test"/>
        </plugin>
    </plugins>
</bxcms>


*/
class bx_plugins_events extends bx_plugin implements bxIplugin {

    static public $instance = array();
    protected $res = array();

    public $eventTable = "events";

    protected $db = null;
    protected $tablePrefix = null;

    public static function getInstance($mode) {
        
        if (!isset(self::$instance[$mode])) {
            self::$instance[$mode] = new bx_plugins_events($mode);
        } 
        return self::$instance[$mode];
    }
    
    protected function __construct($mode) {
        $this->tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        $this->db = $GLOBALS['POOL']->db;
        $this->mode = $mode;
    }

    public function isRealResource($path , $id) {
        return true;
    }
    
    public function getIdByRequest($path, $name = NULL, $ext = NULL) {
        if ($ext == 'ics') {
                return "__ics.events";
        }
        return $name.'.'.$this->name;
       
    }

    public function getContentById($path, $id){
        if ($id == "__ics.events") {
            return $this->getICalContent($path);   
        }
        
        $filename=preg_replace("#\.events$#","",$id);
        $prefix =  $GLOBALS['POOL']->config->getTablePrefix();
        $dateformat = $this->getParameter($path,"dateformat",BX_PARAMETER_TYPE_DEFAULT,"d/m/Y H:i");
        $attrFields = array('title','link', 'von', 'bis', 'uri');
        
        $sqlWhere = $this->getParameter($path, "sqlwhere", BX_PARAMETER_TYPE_DEFAULT); 
        $sqlOrder = $this->getParameter($path, "sqlorder", BX_PARAMETER_TYPE_DEFAULT);
        if($filename == "index"){
            
            $query="select * from ".$prefix."events"; 
            if ($sqlWhere && !empty($sqlWhere)) {
                $query.= " WHERE ".$sqlWhere;    
            }
            
            if ($sqlOrder && !empty($sqlOrder)) {
                $query.= " ORDER BY ".$sqlOrder;
            } else {
                $query.= " order by von asc";
            }
             
            $res =  $GLOBALS['POOL']->db->query($query);
            $dom = new DomDocument();
            $root=$dom->createElement("events");
            $root->setAttribute('single','false');
            $dom->appendChild($root); // root node
            
            
            while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
                
                $child = $dom->createElement("event");
                $child->setAttribute("title", $row['title']);
                $dom->documentElement->appendChild($child);
                $child->setAttribute("link", $row['link']);
                $dom->documentElement->appendChild($child);
                
                $time = strtotime($row['von']);
                $von = date($dateformat, $time);
                $child->setAttribute("von", $von);
                $dom->documentElement->appendChild($child);
                
                $time = strtotime($row['bis']);
                $bis = date($dateformat, $time);
                $child->setAttribute("bis", $bis);
                $dom->documentElement->appendChild($child);
                //$child->setAttribute("description", $row['description']);
                //$dom->documentElement->appendChild($child);
                
                $child->setAttribute("uri", $row['uri']);
                $dom->documentElement->appendChild($child);  
            
                foreach($row as $fieldn => $fieldv) {
                    if (!in_array($fieldn,$attrFields)) {
                        $fieldNode = $dom->createElement($fieldn);
                        if ($fieldNode instanceof DOMNode) {
                            $fieldNode->appendChild($dom->createTextNode($fieldv));
                        
                            $child->appendChild($fieldNode);
                        
                        }
                    }
                }
            
            
            }

            
            return $dom;
        } else {
                $query="select * from ".$prefix."events where uri = '".$filename."'";
                $res =  $GLOBALS['POOL']->db->query($query);
                $dom = new DomDocument();
                $root=$dom->createElement("events");
                $root->setAttribute('single','true');
                
                $dom->appendChild($root); // root 
                $row = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
                $child = $dom->createElement("event");
                $child->setAttribute("title", $row['title']);
                $dom->documentElement->appendChild($child);
                $child->setAttribute("link", $row['link']);
                $dom->documentElement->appendChild($child);
                
                $time = strtotime($row['von']);
                $von = date($dateformat, $time);
                $child->setAttribute("von", $von);
                $dom->documentElement->appendChild($child);
                
                $time = strtotime($row['bis']);
                $bis = date($dateformat, $time);
                $child->setAttribute("bis", $bis);
                $dom->documentElement->appendChild($child);
                
                $child->setAttribute("uri", $row['uri']);
                $dom->documentElement->appendChild($child);                  
                
                foreach($row as $fieldn => $fieldv) {
                    if (!in_array($fieldn,$attrFields)) {
                        $fieldNode = $dom->createElement($fieldn);
                        if ($fieldNode instanceof DOMNode) {
                            $fieldNode->appendChild($dom->createTextNode($fieldv));
                        
                            $child->appendChild($fieldNode);
                        
                        }
                    }
                }
                
                return $dom;
        }
    }

    protected function getIcalContent($uri) {
        $timezone = bx_helpers_config::getTimezoneAsSeconds();
        $tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        $res = $GLOBALS['POOL']->db->query("select location, id, description, uri, title, 
        bis, 
        date_format(von,'%Y%m%dT%H%i%SZ') as von_format, 
        date_format(bis,'%Y%m%dT%H%i%SZ') as bis_format,
        date_format(date_add(von,INTERVAL ".$timezone." SECOND),'%H%i') as von_localtime, 
        date_format(date_add(von,INTERVAL ".$timezone." SECOND),'%Y%m%d') as von_localdate ,
        date_format(date_add(bis,INTERVAL ".$timezone." SECOND),'%H%i') as bis_localtime, 
        date_format(date_add(bis,INTERVAL ".$timezone." SECOND),'%Y%m%d') as bis_localdate 
        
        from ".$tablePrefix."events");
        $dom = new domdocument();
        $root = $dom->appendChild($dom->createElement("text"));
        $ical = "BEGIN:VCALENDAR
        VERSION:2.0
        X-WR-CALNAME:".$this->getParameter($uri,"calname"). "
        X-WR-RELCALID:".$this->getParameter($uri,"calid") . "
        CALSCALE:GREGORIAN
        X-WR-CALDESC:".$this->getParameter($uri,"caldesc") . "\n";
                            
        while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)){
        $ical .= "BEGIN:VEVENT\n";
        
        if ($row['von_localtime'] != '0000') {
            $ical .= "DTSTART;VALUE=DATE:".$row['von_format']."\n";
        } else {
            $ical .="DTSTART;VALUE=DATE:".$row['von_localdate']."\n";
        }
        $ical .= "LOCATION:".$row['location']."\n";
        if ($row['bis'] != 0) {
             if ($row['bis_localtime'] != '0000') {
                 $ical .= "DTEND;VALUE=DATE:".$row['bis_format']."\n";
             } else {
                 $ical .="DTEND;VALUE=DATE:".$row['bis_localdate']."\n";
             }
        }
        $ical .= "SUMMARY:".$row['title']."\n".
        "UID:".$row['id'].$row['uri']."\n".
        "DTSTAMP:20050408T124824Z"."\n".
        "DESCRIPTION:".str_replace(array("\n","\r"), "", $row['description'])."\n".
        "END:VEVENT\n";
        }
        $ical .="END:VCALENDAR";
        $root->nodeValue = $ical;
        return $dom;
    }
    
    public function adminResourceExists($path, $id, $ext=null, $sample = false) {
        return true;
    }

}
?>
