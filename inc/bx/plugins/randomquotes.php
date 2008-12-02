<?php
/**
 * Simple testplugin
 *
 * To use this plugin in a collection, put the following into .configxml
 ***
 <bxcms xmlns="http://bitflux.org/config">
 <plugins>
 <parameter name="xslt"type="pipeline"value="randomquotes.
 xsl"/>
 <extension type="html"/>
 <plugin type="randomquotes">
 </plugin>
 <plugin type="navitree"></plugin>
 </plugins>
 </bxcms>
 ***
 * This plugin spits out randomquotes out of a simple db table.
 *
 * DB Dump:
 *<pre>
 CREATE TABLE ‘randomquotes‘ (
 ‘quote‘ varchar(255) NOT NULL default ’’,
 ‘author‘ varchar(30) default NULL,
 ‘status‘ tinyint(1) NOT NULL default ’0’,
 ‘id‘ int(10) unsigned NOT NULL auto increment,
 PRIMARY KEY (‘id‘)
 ) ENGINE=MyISAM;
 *</pre>
 *
 * It can be called two ways:
 * www.example.com/$collection/
 * and
 * www.example.com/$collection/all/
 *
 * The second one spits all the quotes in the db
 *
 * It gets editored through the dbforms2. Just call
 * www.example.com/admin/dbforms2/randomquotes/
 * and to debug the query:
 * www.example.com/admin/dbforms2/randomquotes/chooser?q=
 *
 * For the dbforms2 editor have a look at
 * BX ROOT DIR/dbforms2/randomquotes.xml
 *
 * Futher Documentation:
 * https://svn.sequenz.ch/pa-ws-05/wiki/Randomquotes
 *
 * @author Alain Petignat <alain@flux-cms.org>
 * @todo alles
 */
class bx_plugins_randomquotes extends bx_plugin implements bxIplugin {

    public static $instance = array();
    protected $res = array();

    /**
 The table names
     */
    public $randomquotesTable = "randomquotes";
    protected $db = null;
    protected $tablePrefix = null;

    public static function getInstance($mode) {
        if (! isset(self::$instance[$mode])) {
            self::$instance[$mode] = new bx_plugins_randomquotes($mode);

        }
        return self::$instance[$mode];

    }

    protected function construct($mode) {
        $this->tablePrefix = $GLOBALS[POOL]->config->getTablePrefix();
        $this->db = $GLOBALS[POOL]->db;
        $this->mode = $mode;

    }

    public function getContentById($path, $id) {
        $dirname = dirname($id);
        switch ($dirname) {
            case "all" :
                return $this->getQuote(all);
            default :
                return $this->getQuote();

        }

    }

    public function isRealResource($path, $id) {
        return true;

    }

    /**
     * @param path string
     * @param id string
     * @return false
     * */
    public function getResourceById($path, $id) {
        return false;

    }

    public function getIdByRequest($path, $name = NULL, $ext = NULL) {
        return $name . '.test';

    }

    public function getContentUriById($path, $id) {
        return false;

    }

    public function adminResourceExists($path, $id, $ext = null, $sample = false) {
        return true;

    }

    /**
     * we need to "register"what editors are beeing able to handle this
 plugin
     */
    public function getEditorsById($path, $id) {
        return false;

    }

    /**
     * getQuote
     *
     * Retuns a random Quote in the DB-Table. Either a single, or,
     * when used with the attribute "all"returns all, quotes as a
     * dom-object
     *
     * @input string "all"for all quotes
     * @retuns object Dom Object with DB-Results
     * @todo set and check status,to be sure, all quotes appear equally
     */
    private function getQuote($set = false) {

        /**
         * This is a nice class to get xml out of a db-table
         */
        $db2xml = new XML_db2xml($this->db, "randomquotes");

        if ($set === all) {
            $query = "SELECT * FROM " . $this->tablePrefix . $this->randomquotesTable . " AS quotes ORDER BY rand()";

        } else {
            $query = "SELECT * FROM " . $this->tablePrefix . $this->randomquotesTable . " AS quotes ORDER BY rand() LIMIT 0,1";

        }

        $res = $this->db->query($query);
        $dom = $db2xml->getXMLObject($res);

        return $dom;

    }

}
