<?php
/*
<?xml version="1.0"?>
<bxcms xmlns="http://www.flux-cms.org/config">
    <plugins>
<extension type="xml"/>
        <parameter name="xslt" type="pipeline" value="../standard/plugins/vote/vote.xsl"/>
        <plugin type="vote">
        <parameter name="configfile" value="files/vote/vote.xml"/>
        <parameter name="magickey" value="jflsjflkluoirjj"/>
        <parameter name="currentVoteId" value="1" />
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



----------------------------------------------------------------------------------

save to "/files/vote/vote.xml"

<votes>
<vote collectionUri="/vote" id="1">
    <question>Your first question</question>
    <answer key="1">Answer One</answer>
    <answer key="2">Answer Two</answer>
    <answer key="3">Answer Three</answer>
    <response>Thanks for your response</response>
</vote>
<vote collectionUri="/vote" id="2">
    <question>Your second question</question>
    <answer key="1">Answer One</answer>
    <answer key="2">Answer Two</answer>
    <answer key="3">Answer Three</answer>
    <response>Thanks for your response</response>
</vote>
<!--

copy the following for new questions:

<vote collectionUri="/vote" id="">
    <question></question>
    <answer key="1"></answer>
    <answer key="2"></answer>
    <answer key="3"></answer>
    <response>T</response>
</vote>

-->
</votes>


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

        $countall = null;
        
        $file = $this->getParameter($path,"configfile");
        $votexml = file_get_contents(BX_OPEN_BASEDIR.$file);
 
        $date1 = floor(time()/1800);
        $date2 = floor((time()/1800))-1;
       
        $dom = new domDocument();
        $dom->loadXML($votexml);
        $rootVoteNode = $dom->documentElement;
 
        $xp = new domxpath($dom);
        
        //
        // for backward-compatibility her we check if the root-node is
        // called "votes" (means more then one vote per file)
        //

        if ($rootVoteNode->nodeName == "votes") {
        
            $voteId = $this->getParameter($path, "currentVoteId");

            if (empty($voteId)) throw new Exception('you have to set parameter "currentVoteId" for the vote-plugin in your configxml');
            
            $votePath = '/votes/vote';
            $query = $votePath . '[@id = '.$voteId.']';
            
            $result = $xp->query($query);
           
            if ($result->length == 1) {
                $voteNode = $result->item(0);                    
                
            }else {
                throw new Exception('no vote with voteId: '.$voteId.' found in: '. $file);
            }
            
            $singleVote = false;
            
        }else {
            $voteNode = $rootVoteNode;
            $voteId = $voteNode->getAttribute("id");
            
            $singleVote = true;
            
            $votePath = '/vote';
        }

        $voteNode->setAttribute("collectionUri",$path);

        
        $magickey =  $this->getParameter($path,"magickey");
        if (!$magickey) {
            $magickey = __FILE__. __LINE__.$voteId;
        }
        
        $cookiename = "VoteFluxcms".$voteId;
        $cookiemd5 = md5($_SERVER['HTTP_USER_AGENT'].$_SERVER['REMOTE_ADDR'].$magickey.$date1);
        $cookiemd52 = md5($_SERVER['HTTP_USER_AGENT'].$_SERVER['REMOTE_ADDR'].$magickey.$date2);
        $showResults = false;

        // do i want to vote?
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
       if (isset($_POST['votesubmit']) && isset($_POST['selection']) && (!$_POST['selection']  || $_POST['selection'] == 'null')) {
            $showResults = true;
        } else if (isset($_POST['votesubmit']) && isset($_COOKIE[$cookiename])) {
       
            // Am I allowed to vote?
            if (($_COOKIE[$cookiename] == $cookiemd5 || $_COOKIE[$cookiename] == $cookiemd52) && isset($_POST['selection'])) {
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
          
            foreach($rslts as $rslt) {
            $path = $votePath . "/answer[@key = '".$rslt['antw']."']";

                $results = $xp->query($path);
                if ($results->length > 0 ) {
                    $results->item(0)->setAttribute('count',floor($rslt['c']/$countall*100));
                    $results->item(0)->setAttribute('quantity',$rslt['c']);
                }
            }
            $voteNode->setAttribute("results","true") ;
            if ($thanks) {
                $voteNode->setAttribute("thanks","true") ;
            }
        }
        
        if (!$singleVote) {
            $dom2 = new domDocument;
            $domNode = $dom2->importNode($voteNode, true);
            $dom2->appendChild($domNode);
            
            $dom = $dom2;
        }else {
            $dom->appendChild($voteNode);
        }
        
        return $dom;
        
    }
    
    public function adminResourceExists($path, $id, $ext=null, $sample = false) {
        return true;
    }
    
}
?>
