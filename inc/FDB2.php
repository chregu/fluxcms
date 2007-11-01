<?php


class FDB2  {
    
     static function connect($dsn,$options = false) {
         return MDB2::connect($dsn,$options);
     }   
     
     static function isError($date,$code = null) {
         return MDB2::isError($date,$code = null);
     }
    
}