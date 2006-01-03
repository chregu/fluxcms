<?php
   class TestOfBxCollection extends UnitTestCase {
        function __construct() {
            $this->UnitTestCase('bx_collection');
        }
        
        function testSanitizeUrl() {
            $this->sanitizeUrl("/foo/","/foo/");
            $this->sanitizeUrl("/foo/","/foo/");
            $this->sanitizeUrl("/foo","/foo/");
            $this->sanitizeUrl("//foo","/foo/");
            $this->sanitizeUrl("//foo//","/foo/");
            $this->sanitizeUrl("///foo//","/foo/");
            $this->sanitizeUrl("foo//","/foo/");
            $this->sanitizeUrl("foo","/foo/");
            $this->sanitizeUrl("/foo/bar/","/foo/bar/");
            $this->sanitizeUrl("/foo//bar/","/foo/bar/");
            $this->sanitizeUrl("/foo/bar","/foo/bar/");
            $this->sanitizeUrl("foo/bar","/foo/bar/");
            $this->sanitizeUrl("//foo//bar","/foo/bar/");
            $this->sanitizeUrl("//foo//bar//","/foo/bar/");
            $this->sanitizeUrl("","/");
            $this->sanitizeUrl("//","/");
            $this->sanitizeUrl("///","/");
             $this->sanitizeUrl(".","/");
             $this->sanitizeUrl("./","/");
             $this->sanitizeUrl("/.","/");
        }
        
        function sanitizeURL($url,$exp) {
              $this->assertEqual(bx_collections::sanitizeUrl($url),$exp);
        }
        
        function testGetFileParts() {
            $this->getFileParts("dummy.html","dummy","html");
            $this->getFileParts("dummy","dummy","");
            $this->getFileParts("dir/dummy","dir/dummy","");
            $this->getFileParts("dir/dummy.html","dir/dummy","html");
        }
        
        function getFileParts($url,$name,$ext) {
            $p = bx_collections::getFileParts($url);
            $p = $p['name'] . "::". $p['ext'];
            $this->assertEqual($p,"$name::$ext");
        }
        
    }
    
?>