<?php


class Test_of_bx_helpers_sql extends UnitTestCase {
    function __construct() {
        $this->UnitTestCase();
    }
    
    function test_quotePostData() {
        $this->assertEqual(bx_helpers_sql::quotePostData(array("foo","bar")),array("'foo'","'bar'"),"Not quoted");
        $this->assertEqual(bx_helpers_sql::quotePostData(array("foo1","bar2")),array("'foo1'","'bar2'"),"Not quoted");
        $this->assertEqual(bx_helpers_sql::quotePostData(array("foo1.txt","bar2_txt")),array("'foo1.txt'","'bar2_txt'"),"Not quoted");
  //      $this->assertEqual(bx_helpers_sql::quotePostData(array("föö","bär")),array("'föö'","'bär'"),"Not quoted ae,oe,ue");
        $this->assertEqual(bx_helpers_sql::quotePostData(array("!foo%","bar#")),array("'!foo%'","'bar#'"),"Not quoted secialchars");
    }
    
    function test_getInsertQuery() {
        $this->tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        $data = array("name"=>"'name'","uri"=>"'uri'","parentid" => 1,"another" => "'bar'");
        $this->assertEqual(bx_helpers_sql::getInsertQuery("foobar", $data, array("name", "uri", "parentid"),1),"INSERT INTO ".$this->tablePrefix."foobar (name,uri,parentid,id) VALUES ('name','uri',1,1)","false insert query");
        $this->assertEqual(bx_helpers_sql::getInsertQuery("foobar", $data, array(),1),"INSERT INTO ".$this->tablePrefix."foobar (name,uri,parentid,another,id) VALUES ('name','uri',1,'bar',1)","false insert query");
    }
    
    function test_getDeleteQuery() {
        $this->tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();                                                                          
        $this->assertEqual(bx_helpers_sql::getDeleteQuery("foobar", "999"),"DELETE FROM ".$this->tablePrefix."foobar WHERE id=999","false delete query");
        $this->assertEqual(bx_helpers_sql::getDeleteQuery("foobär", "999"),"DELETE FROM ".$this->tablePrefix."foobär WHERE id=999","false delete query");
        $this->assertEqual(bx_helpers_sql::getDeleteQuery("foobar#", "999"),"DELETE FROM ".$this->tablePrefix."foobar# WHERE id=999","false delete query");
    }
    
    function test_getUpdateQuery() {
        $this->tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        $data = array("name"=>"'name'","uri"=>"'uri'","parentid" => 1,"another" => "'bar'");
        $this->assertEqual(bx_helpers_sql::getUpdateQuery("foobar", $data, array("name", "uri", "parentid"),1),"UPDATE ".$this->tablePrefix."foobar SET name = 'name',uri = 'uri',parentid = 1 WHERE id=1","false update query");
        $this->assertEqual(bx_helpers_sql::getUpdateQuery("foobar", $data, array(),1),"UPDATE ".$this->tablePrefix."foobar SET name = 'name',uri = 'uri',parentid = 1,another = 'bar' WHERE id=1","false update query");
    }
}
