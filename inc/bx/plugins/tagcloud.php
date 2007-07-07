<?php

/*

<?xml version="1.0"?>
<bxcms xmlns="http://bitflux.org/config">

    <plugins>
        <parameter name="xslt" type="pipeline" value="tagcloud.xsl"/>
<plugin type="navitree">
        </plugin>
        <plugin type="tagcloud">
            <parameter name="locations" value="/blog/"/>
            <parameter name="maxfontsize" value="36"/>
            <parameter name="minfontsize" value="12"/>
        </plugin>
    </plugins>
</bxcms>

*/

class bx_plugins_tagcloud extends bx_plugin implements bxIplugin {
    
    static public $instance = array();
    protected $res = array();
    
    protected $db = null;
    protected $tablePrefix = null;
    
    public static function getInstance($mode) {
        
        if (!isset(self::$instance[$mode])) {
            self::$instance[$mode] = new bx_plugins_tagcloud($mode);
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
        
        return $name.'.'.$this->name;
        
    }
    
    public function getContentById($path, $id){
        $tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        $tags = array();
        $locations = $this->getParameter($path,"locations");
        
        if($this->getParameter($path,"maxfontsize")) {
            $this->maxFontSize = $this->getParameter($path,"maxfontsize");
        } else {
            $this->maxFontSize = 36;
        }
        if($this->getParameter($path,"minfontsize")) {
            $this->minFontSize = $this->getParameter($path,"minfontsize");
        } else {
            $this->minFontSize = 8;
        }
        $query="select count(".$tablePrefix."tags.tag) as tagcount, tag from ".$tablePrefix."tags left join ".$tablePrefix."properties2tags on ".$tablePrefix."properties2tags.tag_id = ".$tablePrefix."tags.id where ".$tablePrefix."tags.id <> '11' and ".$tablePrefix."properties2tags.path like '".$locations."%' and tag <> '' group by ".$tablePrefix."tags.tag";
        $res = $GLOBALS['POOL']->db->query($query);
        
        while($row = $res->fetchAll(MDB2_FETCHMODE_ASSOC)) {
            $tags = $row;
        }
        
        $max = $this->maxof($tags);
        
        $res = $GLOBALS['POOL']->db->query($query);
        $xml = "<tagcloud>";
        while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            $xml .= "<tag>";
            $xml .= "<name>".$row['tag']."</name>";
            $size = $this->getFontSize($row['tagcount'], $max, $path);
            $xml .= "<size>".$size."</size>";
            $xml .= "<path>".$locations."</path>";
            $xml .= "</tag>";
        }
        $xml .= "</tagcloud>";
        
        $dom = new DomDocument();
        $dom->loadXML($xml);
        return $dom;
        
    }
    
    public function getFontSize($count, $max, $path) {
        $count = $count-1;
        $max = $max-1;
        $diff = $this->maxFontSize - $this->minFontSize;
        $percent = $count / $max * 100;
        $size = $percent / 100 * $diff;
        $res = $this->minFontSize + $size;
        
        return round($res);
    }
    
    public function maxof($array) {
         $max = 0;
         foreach($array as $element) {
             if ($element['tagcount'] > $max) {
                 $max = $element['tagcount'];
             }
             
         }
         return $max;
    }
    
    public function adminResourceExists($path, $id, $ext=null, $sample = false) {
        return true;
    }
    
}
?>
