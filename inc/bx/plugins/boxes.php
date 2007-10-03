<?php


class bx_plugins_boxes extends bx_plugin implements bxIplugin {

    /**
    * a static var to to save the instances of this plugin
    */
    static public $instance = array();
    protected $res = array();
    public $name = "boxes";

    protected $boxesTable = 'boxes';
    protected $boxes2pageTable = 'boxes2page';
    protected $boxesTableScope = 'boxes_scope';

    protected $db = null;
    protected $tablePrefix = null;

    protected $lang;
    protected $scope;

    protected $propertyname = 'set-id';
    protected $namespace = 'box:';

    /**
    * flag to indicate, that there is no content on selected scope
    * , but on default scope (o)
    */
    private $defaultScope = false;

    protected $setId;

    public static function getInstance($mode) {
        if (!isset(self::$instance[$mode])) {
            self::$instance[$mode] = new bx_plugins_boxes($mode);
        }
        return self::$instance[$mode];
    }

    protected function __construct($mode) {
        // Get the global table prefix
        $this->tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        // get the db object
        $this->db = $GLOBALS['POOL']->db;
        $this->mode = $mode;
        //parameters for boxes
        $this->lang = $GLOBALS['POOL']->config->getOutputLanguage();
    }

    public function isRealResource($path , $id) {
        return false;
    }

    public function getIdByRequest($path, $name = NULL, $ext = NULL) {
        return $this->name.'.'.$path;
    }

    public function getContentById($path, $id) {
        $this->defaultScope = false;
        $this->scope = $this->getScope();
		$this->setId = $this->findProperties($path);
		if(!$this->setId){
		    return;
		}
		$xml = '<boxes>';
		$xml .= $this->getBoxes(1);
		$xml .= $this->getBoxes(2);
		$xml .= '</boxes>';
        $dom = new DomDocument();
        $dom->loadXML($xml);
        return $dom;
    }

	private function findProperties($path){
		$id = false;
		$data = false;
		$parts = explode('/',$path);
		//first and last are allways empty
		array_shift($parts);
		array_pop($parts);
		while(!$id){
			$path = '/'.implode('/',$parts).'/';
			$id = $this->hasBoxes($path);
			array_pop($parts);
			if(count($parts) == 0 AND !$id){
				$id = $this->hasBoxes('/');
			    break;
			}
		}
		return $id;
	}

	private function hasBoxes($path, $scope = false){
		if(!$id = bx_resourcemanager::getProperty($path, $this->propertyname, $this->namespace)) {
		    return false;
		}
        $nm = $this->tablePrefix.$this->boxes2pageTable;
        $lang = $this->lang;
        $scope = ($scope !== false)?$scope:$this->scope;
        $setid = $id;
        $query  = " SELECT COUNT(*) AS total FROM $nm ";
        $query .= " WHERE lang = '$lang' AND scope = '$scope' AND setid = '$setid'  ";

        $res = $this->db->queryone($query);
        if (MDB2::isError($res)) {
             throw new PopoonDBException($res);
        }
        if($res == 0){
            if($scope !== 0){
               if($id = $this->hasBoxes($path,0) ){
                    $this->defaultScope = true;
                    return $id;
               }
            }
            return false;
        }
        return $id;
	}

	/**
	 * must be extended if used
	 */
	protected function getScope(){
	    return 0;
	}

    private function getBoxes($col = 0){
        $nm = $this->tablePrefix.$this->boxes2pageTable;
        $box = $this->tablePrefix.$this->boxesTable;

           $scope = ($this->defaultScope) ? 0 : $this->scope ;

        $query  = " SELECT $box.* FROM $nm ";
        $query .= " JOIN $box ON ( $nm.boxid  = $box.id) ";
        $query .= ' WHERE '.$nm.'.lang = "'.$this->lang.'" AND '.$nm.'.scope = "'.$scope.'" AND '.$nm.'.col = "'.$col.'" ';
        $query .= " AND $nm.setid = ".$this->setId." ";
        $query .= " ORDER BY $nm.rang ";

        $res = $this->db->query($query);
        if (MDB2::isError($res)) {
             throw new PopoonDBException($res);
        }
        $xml = "<group id=\"$col\">";
		$xml .= "<contentBoxes>";
        while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)){
            $this->usedBoxes[] = $row['id'];
            $xml .= '<box id="box_'.$row['id'].'">';
            $xml .= '<title>'.$row['title'].'</title>';
            $xml .= '<content>'.$row['content'].'</content>';
            $xml .= '<link>'.$row['link'].'</link>';
            $xml .= '<linktext>'.$row['linktext'].'</linktext>';
            $xml .= '<id>'.$row['id'].'</id>';
            $xml .= '</box>';
        }
		$xml .= "</contentBoxes>";
        $xml .= "</group>";
        return $xml;
    }

    public function adminResourceExists($path, $id, $ext=null, $sample = false) {
        if($ext == 'xhtml'){
            return false;
        }
        return true;
    }

    public function getEditorsById($path, $id) {
        return array("boxes");
    }

    protected function getEmptyPage($id) {
        $xml = '<p>
        <i18n:text>Called boxes plugin with id: '.$id.'</i18n:text></p>';
        return $xml;
    }

    public function getMimeType(){

    }

    protected function getScopes() {
        $query = 'SELECT * from '.$this->tablePrefix.$this->boxesTableScope;
        //echo $query;
        $res = $this->db->query($query);
        if (MDB2::isError($res)) {
             throw new PopoonDBException($res);
        }
        $arr = array('0' => 'all');
        while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)){
            $arr[$row['name']] = $row['id'];
        }
        return $arr;
    }
}
