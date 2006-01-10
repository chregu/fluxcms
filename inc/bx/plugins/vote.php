<?php
/*
<?xml version="1.0"?>
<bxcms xmlns="http://bitflux.org/config">
    <plugins>
<extension type="xml"/>
        <parameter name="xslt" type="pipeline" value="../standard/plugins/vote/vote.xsl"/>
        <plugin type="vote">
        <parameter name="configfile" value="files/vote/vote.xml"/>
        <parameter name="magickey" value="jflsjflkluoirjj"/>
        </plugin>
    </plugins>

    <plugins>
        <parameter name="xslt" type="pipeline" value="vote.xsl"/>
<plugin type="navitree">
        </plugin>
        <plugin type="vote">
        </plugin>
    </plugins>
</bxcms>

--------------------------------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `bxcms_vote`
-- 

CREATE TABLE IF NOT EXISTS `bxcms_vote` (
  `id` int(11) NOT NULL auto_increment,
  `voteid` int(11) NOT NULL default '0',
  `useragent` text NOT NULL,
  `ip` varchar(255) NOT NULL default '0',
  `datum` mediumint(9) NOT NULL default '0',
  `antw` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=78 ;



*/
session_start();

class bx_plugins_vote extends bx_plugin implements bxIplugin {
    
    static public $instance = array();
    protected $res = array();
    
    protected $db = null;
    protected $tablePrefix = null;
    
    public static function getInstance($mode) {
        
        if (!isset(self::$instance[$mode])) {
            self::$instance[$mode] = new bx_plugins_vote($mode);
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
        /*$votexml = "<vote collectionUri='".$path."'>";
        $votexml .= "<question> Finden Sie el fnord gnarf gut? </question>";
        $votexml .= "<answer key='1'>yes</answer>";
        $votexml .= "<answer key='2'>no</answer>";
        $votexml .= "<answer key='3'>nevermind</answer>";
        $votexml .= "<response>Danke für Ihre Teilnahme!</response>";
        $votexml .= "</vote>";*/
        $countall = null;
        
        $file = $this->getParameter($path,"configfile");
        $votexml = file_get_contents(BX_OPEN_BASEDIR.$file);
        
        $date1 = floor(time()/1800);
        $date2 = floor((time()/1800))-1;
       
        $dom = new domDocument();
        $dom->loadXML($votexml);
        $voteId = $dom->documentElement->getAttribute("id");
        $dom->documentElement->setAttribute("collectionUri",$path);
        
       
        $magickey =  $this->getParameter($path,"magickey");
        if (!$magickey) {
            $magickey = __FILE__. __LINE__.$voteId;
        }
        
        $cookiename = "VoteFluxcms".$voteId;
        $cookiemd5 = md5($_SERVER['HTTP_USER_AGENT'].$_SERVER['REMOTE_ADDR'].$magickey.$date1);
        $cookiemd52 = md5($_SERVER['HTTP_USER_AGENT'].$_SERVER['REMOTE_ADDR'].$magickey.$date2);
        $showResults = false;
        $thanks = false;
        // do i want to vote?
        if (isset($_POST['votesubmit']) && isset($_POST['selection']) && (!$_POST['selection'] || $_POST['selection'] == 'null')) {
            $showResults = true;
        } else if (isset($_POST['votesubmit']) && isset($_COOKIE[$cookiename])) {
            // Am I allowed to vote?
            if ($_COOKIE[$cookiename] == $cookiemd5 || $_COOKIE[$cookiename] == $cookiemd52) {
                // vote
                $query = "insert into ".$GLOBALS['POOL']->config->getTablePrefix()."vote (voteid, useragent, ip, datum, antw) values($voteId, ".$GLOBALS['POOL']->db->quote($_SERVER['HTTP_USER_AGENT']).", ".$GLOBALS['POOL']->db->quote($_SERVER['REMOTE_ADDR']).", now(), '". (int) $_POST['selection']."')" or die("no way");
                $GLOBALS['POOL']->db->query($query);
                setcookie($cookiename, "voted", time()+60480,'/');
            } else {
                print "Sorry, you already voted.";
            }
            $showResults = true;
            $thanks = true;

        } else if (isset($_COOKIE[$cookiename]) && $_COOKIE[$cookiename] == 'voted') {
            $showResults = true;
        } else {
            setcookie($cookiename,$cookiemd5,null,'/');
        }
        
        if ($showResults) {
            $query = "select antw , count(*) as c from ".$GLOBALS['POOL']->config->getTablePrefix()."vote where voteid = ".$voteId." group by antw;";
            
            
            $res = $GLOBALS['POOL']->db->query($query);
            $rslts = $res->fetchAll(MDB2_FETCHMODE_ASSOC);
            
            $nr = count($rslts);
            foreach ($rslts as $key => $value) {
                @$countall += $value['c'];
            }
            $xp = new domxpath($dom);
            foreach($rslts as $rslt) {
                $results = $xp->query("/vote/answer[@key = '".$rslt['antw']."']");
                if ($results->length > 0 ) {
                    $results->item(0)->setAttribute('count',floor($rslt['c']/$countall*100));
                }
                
            }
            $dom->documentElement->setAttribute("results","true") ;
            if ($thanks) {
                $dom->documentElement->setAttribute("thanks","true") ;
            }
            return $dom;
        } else {
            return $dom;
        }
        
    }
    
    public function adminResourceExists($path, $id, $ext=null, $sample = false) {
        return true;
    }
    
}
?>
