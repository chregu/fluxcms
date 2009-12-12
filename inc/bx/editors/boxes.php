<?php



class bx_editors_boxes extends bx_editor implements bxIeditor {

	protected $tablePrefix;
	protected $db;
    protected $boxesTable = 'boxes';
    protected $boxes2pageTable = 'boxes2page';
    protected $boxesTableScope = 'boxes_scope';
    protected $lang;
    protected $defaultLang;
    protected $langsAvail;
    protected $allScopes;
    protected $scope;

    protected $propertyname = 'set-id';
    protected $namespace = 'box:';

    protected $setId;

    protected $usedBoxes = array();

    public function __construct(){
        $this->tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        $this->db = $GLOBALS['POOL']->db;

        //some lang stuff
        $this->defaultLang = $GLOBALS['POOL']->config['defaultLanguage'];
        $this->langsAvail = $GLOBALS['POOL']->config['outputLanguages'];
        if(isset($_GET['lang']) && in_array($_GET['lang'], $this->langsAvail)){
            $this->lang = $_GET['lang'];
        }
        else{
            $this->lang = $this->defaultLang;
        }

        //and the scope
        $this->allScopes = $this->getScopes();
        //print_r($this->allScopes);
        if(isset($_GET['scope']) && isset($this->allScopes[$_GET['scope']]) ){
            $this->scope = $_GET['scope'];
        }
        else{
            $this->scope = 0;
        }

    }
    public function getDisplayName() {
        return 'Boxes Editor';
    }

    public function getPipelineParametersById($path, $id) {
        return array('pipelineName'=>'boxes');
    }


    public function handlePOST($path, $id, $data) {
       if(isset($data['boxes']['list'])){
            foreach($data['boxes']['list'] as $key => $list){
                // list 0 are the available boxes
                // no action required
                if($key == 0){
                    return;
                }
                $this->saveCol($key,$list,$data['boxes']);
                return;
            }
       }
    }

    public function getEditContentById($id) {
        //check for the boxes set id in the properties table
        //echo $id;


        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $dom = new DomDocument();
            if (strpos($id,"/box-list-update/") !== false) {
                $dom->appendChild($dom->createElement("ajaxpost"));
                return $dom;
            }
        }

        $this->setId = $this->checkPropertiesId($id);
        //echo $id;
        $xml  = '<boxes>';
        $xml .= '<setId>'.$this->setId.'</setId>';
        $xml .= '<path>'.$id.'</path>';
        $xml .= $this->langInformation();
        $xml .= $this->getUsedBoxes(1);
        $xml .= $this->getUsedBoxes(2);
        $xml .= $this->getAllBoxes();
        $xml .= $this->getScopesXML();
        $xml .= '</boxes>';
        $dom = new DomDocument();
        $dom->loadXML($xml);
        //echo $xml;
        return $dom;
        //return $xml;
    }


    public function getMimeType(){

    }

    //internal functions -----------------------------------------------------------------------
    //------------------------------------------------------------------------------------------

    private function saveCol($col, $list, $data) {
        print_r($data);
        print_r($list);

        $nm = $this->tablePrefix.$this->boxes2pageTable;

        $lang = $data['lang'];
        $scope = $data['scope'];
        $setid = $data['setid'];

        $query  = " DELETE FROM $nm ";
        $query .= " WHERE lang = '$lang' AND scope = '$scope' AND col = '$col' AND setid = '$setid'  ";

        $res = $this->db->query($query);
        if (MDB2::isError($res)) {
             throw new PopoonDBException($res);
        }

        $rang = 0;
        foreach($list as $boxid){
            $query  = " INSERT INTO  $nm ";
            $query .= " (lang,scope,col,setid,boxid,rang)  ";
            $query .= " VALUES ('$lang','$scope','$col','$setid','$boxid','$rang')  ";

            $res = $this->db->query($query);
            $rang++;
            if (MDB2::isError($res)) {
                throw new PopoonDBException($res);
            }
        }

    }

    /**
    * searches the id from the properties table
    * or creates one, if not found
    */
    private function checkPropertiesId($path) {
        $id = bx_resourcemanager::getProperty($path, $this->propertyname, $this->namespace);
        if(!$id){
            $id = $this->db->nextID("_sequences");
            bx_resourcemanager::setProperty($path, $this->propertyname, $id, $this->namespace);
        }
        return $id;
    }


    /**
    * return language informations
    */
    private function langInformation() {
        $xml = '';
        foreach($this->langsAvail as $lang){
            $xml .= '<lang value="'.$lang.'" ';
            if($lang == $this->defaultLang){
                $xml .= 'default="true" ';
            }
            if($lang == $this->lang){
                $xml .= 'selected="true" ';
            }
            $xml .= '/>';
        }
        return $xml;
    }

    /**
    * return language informations
    */
    private function getScopesXML() {
        $xml = '';
        foreach($this->allScopes as $key => $name){
            $xml .= '<scope id="'.$key.'" name="'.$name.'" ';

            if($key == $this->scope){
                $xml .= 'selected="true" ';
            }

            $xml .= '/>';
        }
        return $xml;
    }

    /**
    * returns scope
    */
    private function getScopes() {
        $query = 'SELECT * from '.$this->tablePrefix.$this->boxesTableScope;
        //echo $query;
        $res = $this->db->query($query);
        if (MDB2::isError($res)) {
             throw new PopoonDBException($res);
        }
        $arr = array('0' => 'all');
        while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)){
            $arr[$row['id']] = $row['name'];
        }
        return $arr;
    }

    /**
    * returns all boxes from the boxes table
    */
    private function getAllBoxes() {
        $query = 'SELECT * from '.$this->tablePrefix.$this->boxesTable.' WHERE lang = "'.$this->lang.'" ';
        //echo $query;
        $res = $this->db->query($query);
        if (MDB2::isError($res)) {
             throw new PopoonDBException($res);
        }
        $xml = '<allboxes>';
        while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)){
            if(in_array($row['id'],$this->usedBoxes)){
                continue;
            }
            $xml .= '<box id="box_'.$row['id'].'">';
            $xml .= '<title>'.$row['title'].'</title>';
            $xml .= '<id>'.$row['id'].'</id>';
            $xml .= '</box>';
        }
        $xml .= '</allboxes>';
        return $xml;
    }

    /**
    * returns all used  boxes in this page
    */
    private function getUsedBoxes($col = 0){
        $nm = $this->tablePrefix.$this->boxes2pageTable;
        $box = $this->tablePrefix.$this->boxesTable;

        $query  = " SELECT $box.* FROM $nm ";
        $query .= " JOIN $box ON ( $nm.boxid  = $box.id) ";
        $query .= ' WHERE '.$nm.'.lang = "'.$this->lang.'" AND '.$nm.'.scope = "'.$this->scope.'" AND '.$nm.'.col = "'.$col.'" ';
        $query .= " AND $nm.setid = ".$this->setId." ";
        $query .= " ORDER BY $nm.rang ";

        $res = $this->db->query($query);
        if (MDB2::isError($res)) {
             throw new PopoonDBException($res);
        }
        $xml = "<box_$col>";
        while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)){
            $this->usedBoxes[] = $row['id'];
            $xml .= '<box id="box_'.$row['id'].'">';
            $xml .= '<title>'.$row['title'].'</title>';
            $xml .= '<id>'.$row['id'].'</id>';
            $xml .= '</box>';
        }
        $xml .= "</box_$col>";
        return $xml;
    }




}

?>
