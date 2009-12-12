<?php

// +----------------------------------------------------------------------+
// | PHP version 4.0                                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997, 1998, 1999, 2000, 2001 The PHP Group             |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Christian Stocker <chregu@liip.ch>                         |
// |          Kristian K�hntopp (initial write)                           |
// +----------------------------------------------------------------------+
//
// $Id$


/*
 * Set Based SQL Trees
 *
 * for theoretical details see:
 * http://www.koehntopp.de/kris/artikel/sql-self-references/
 *
 */



class SQL_Tree {

    public $db;

    // Name of Table and L and R columns.
    public $tablename = "Section";
    public $lname     = "l";
    public $rname     = "r";
    // for import/export Stuff
    public $idField   = "ID";
    public $referenceField = "foreignsectionid";
    public $fullnameSeparator = " -> ";
    public $where = '';

    // Constructor
    function SQL_Tree($db) {
        $this->db = $db;

    }

    // Private functions for internal use

    // Given a hash, will make an insert statement
    //  to insert this hash into a database table.
    function make_insert_fragment(&$data) {
        $query = sprintf("insert into %s set ", $this->tablename);
        reset($data);
        while(list($k, $v) = each($data)) {
            $query .= sprintf("%s='%s', ",$k,$v);
        }

        return $query;
    }


    function make_update_fragment(&$data) {
        $query = sprintf("update %s set ", $this->tablename);
        reset($data);
        while(list($k, $v) = each($data)) {
            $query .= sprintf("%s='%s', ",$k,$v);
        }

        return $query;
    }

    // Given a hash with column names and contents,
    // will find an L value matching all this data.
    function name_to_lpos($n) {
        if (!is_array($n))
            return false;

        $query = sprintf("select %s from %s where ",$this->lname,$this->tablename);

        $sep = "";
        reset($n);
        while(list($name, $value) = each($n)) {
            $query .= $sep . "$name = '$value'";
            $sep = " and ";
        }

        $result = $this->db->query($query);
        $row = $result->fetchrow(MDB2_FETCHMODE_ASSOC);
        //debug::print_rp( $row);
        return $row[$this->lname];
    }

    // Public functions

    // Will clean the table and set up an empty tree.
    function rootnode(&$data) {
        $query = sprintf("delete from %s", $this->tablename);
        $this->db->query($query);

        $query  = $this->make_insert_fragment($data);
        $query .= sprintf("%s=1, %s=2", $this->lname, $this->rname);
        $this->db->query($query);

        return 1;
    }

    // All functions take an L column value as the first
    // parameter. All functions are available in a
    // _byname() form which will try to find this L
    // using name_to_lpos() first.

    // insert_below() will branch out below and insert
    // a new node below the current one as the leftmost
    // member.
    function insert_below_byname($name, &$data) {
        if (!$pos = $this->name_to_lpos($name))
            return false;

        return $this->insert_below($pos, $data);
    }

    function insert_below($lpos, &$data) {
        $this->nifty_update($lpos);
        $query  = $this->make_insert_fragment($data);
        $query .= sprintf("%s=%s, %s=%s",$this->lname,$lpos+1,$this->rname,$lpos+2);
//        print "$query<br>";
        $this->db->query($query);

        return $lpos+1;
    }

    function update_below_byname($name, &$data) {
        if (!$pos = $this->name_to_lpos($name))
            return false;
            return $this->update_below($pos, $data);
    }

    function update_below($lpos, &$data) {
        // nifty use of if() ahead, single statement "update"

        $this->nifty_update($lpos);

        //        $query  = $this->make_update_fragment($data);
        $query  = "update $this->tablename set ";
        $query .= sprintf("%s=%s, %s=%s where ",$this->lname,$lpos+1,$this->rname,$lpos+2);
        reset($data);
        $sep = "";
        while(list($name, $value) = each($data)) {
            $query .= $sep . "$name = '$value'";
            $sep = " and ";
        }
//        print "$query<br>";

        $this->db->query($query);
        return $lpos+1;
    }
    function nifty_update ($lpos)
    {

        $query = sprintf("update %s set %s=if(%s>%s,%s+2,%s), %s=if(%s>%s,%s+2,%s) where %s>%s",$this->tablename,$this->lname,$this->lname,$lpos,$this->lname,$this->lname,$this->rname,$this->rname,$lpos,$this->rname,$this->rname,$this->rname,$lpos);
        $this->db->query($query);
//        print "$query<br>";
    }
    // insert_right will add a node just right
    // of the current one.
    function insert_right_byname($name, &$data) {
        if (!$pos = $this->name_to_lpos($name))
            return false;

        return $this->insert_right($pos, $data);
    }

    function insert_right($lpos, &$data) {
        $query = sprintf("select %s from %s where %s=%s",$this->rname,$this->tablename,$this->lname,$lpos);
        $result=$this->db->query($query);
        $row = $result->fetchrow(MDB2_FETCHMODE_ASSOC);
        $rpos = $row[$this->rname];
        // again the update using our if() trick.
        $query = sprintf("update %s set %s=if(%s>%s,%s+2,%s), %s=if(%s>%s,%s+2,%s) where %s>%s",$this->tablename,$this->lname,$this->lname,$rpos,$this->lname,$this->lname,$this->rname,$this->rname,$rpos,$this->rname,$this->rname,$this->rname,$rpos);
//        print "$query<br>";
        $this->db->query($query);

        $query  = $this->make_insert_fragment($data);
//        print "$query<br>";
        $query .= sprintf("%s=%s, %s=%s",$this->lname,$rpos+1,$this->rname,$rpos+2);
//        print "$query<br>";
        $this->db->query($query);

        return $rpos+1;
    }

    // delete will delete a subtree, removing
    // the current node and all nodes dangling
    // from it.
    function delete_byname($name) {
        if (!$pos = $this->name_to_lpos($name))
            return false;

        return $this->deleteSubTree($pos);
    }

    function deleteSubTree($lpos) {
        $query = sprintf("select %s from %s where %s=%s",$this->rname,$this->tablename,$this->lname,$lpos);
        $result = $this->db->query($query);
        $row = $result->fetchrow(MDB2_FETCHMODE_ASSOC);


        $rpos = $row[$this->rname];
        $len = $rpos - $lpos + 1;

        $query = sprintf("delete from %s where %s between %s and %s",$this->tablename,$this->lname,$lpos,$rpos);
        $this->db->query($query);

        $query = sprintf("update %s set %s=if(%s>%s,%s-%s,%s), %s=if(%s>%s,%s-%s,%s) where %s>%s or %s>%s",$this->tablename,$this->lname,$this->lname,$lpos,$this->lname,$len,$this->lname,$this->rname,$this->rname,$lpos,$this->rname,$len,$this->rname,$this->lname,$lpos,$this->rname,$rpos);
        $this->db->query($query);

        return $lpos;
    }

    // level will return the absolute depth of the
    // current node within the tree.
    function level_byname($name) {
        if (!$pos = $this->name_to_lpos($name))
            return false;

        return $this->level($pos);
    }

    function level($lpos) {
        $query = sprintf("SELECT count(a.%s) as level
                         FROM    %s as a,
                         %s as b
                         WHERE   b.%s BETWEEN a.%s AND a.%s
                         AND b.%s = '%s'
                         GROUP BY b.%s ",$this->lname,$this->tablename,$this->tablename,$this->lname,$this->lname,$this->rname,$this->lname,$lpos,$this->lname);
        $result = $this->db->query($query);
        $row = $result->fetchrow(MDB2_FETCHMODE_ASSOC);

        return $row["level"];
    }

    // supers() will return the L numbers of
    // all nodes above the current node,
    // starting with the root (first array element)
    // down to and including the current node (last array element)
    function supers_byname($name,&$data) {
        if (!$pos = $this->name_to_lpos($name))
            return false;
		$sql = "";
        return $this->supers($pos,$data,$sql);
    }


    function supers_query_byname($name,&$data) {
        if (!$pos = $this->name_to_lpos($name))
            return false;

        return $this->supers_query($pos,$data);
    }




    function supers_query($lpos,&$data) {

        if (is_array($data))
        {
        	$fields = "";
            foreach ($data as $field)
            {
                if (! preg_match ("/\(/",$field))
                {
                    $fields .= " , ".$this->tablename.".$field ";
                }
                else {
                    $fields .= ", $field ";
                }
            }
        }
        $query = ("select ".$this->tablename.".$this->lname as pos $fields  from $this->tablename as a , $this->tablename as ".$this->tablename." where a.$this->lname between ".$this->tablename.".$this->lname and ".$this->tablename.".$this->rname and a.$this->lname= '$lpos' order by pos");
        return $query;

    }

    function supers($lpos,&$data) {
        $query = $this->supers_query($lpos,$data);
        $result = $this->db->query($query);

        if (PEAR::isError($result->result))
        {
            print "The given query valid in file ".__FILE__." at line ".__LINE__."<br>\n".$result->result->userinfo."<br>\n";
            return new MDB2_Error($result->result->code,PEAR_ERROR_DIE);

        }
		$z = 0;
        while($row = $result->fetchrow(MDB2_FETCHMODE_ASSOC)) {
        if (is_array($data))
            {   $z++;
                $result2[$z]['lpos'] = $row['pos'];
                foreach ($data as $field)
                {
                    $result2[$z][$field] = $row[$field];

                }
            }
            else
            {
                $result2[] = $row["pos"];
            }

        }

        return $result2;
    }

    // children() will return the L numbers
    // of all immediate children of the current
    // node, not including the current node.
    function children_byname($name,&$data,$wholeTree=False ) {
        if (!$pos = $this->name_to_lpos($name))
            return false;

        return $this->children($pos,$data,$wholeTree);
    }
    function children_query_byname($name,&$data,$wholeTree=False, $leftJoin=FALSE) {
        if (!$pos = $this->name_to_lpos($name))
            return false;

        return $this->children_query($pos,$data,$wholeTree,$leftJoin);
    }
    
    function children_query($lpos,&$data,$wholeTree=False,$leftJoin=FALSE) {

        $query = sprintf("SELECT  count(a.%s) as level,
                         a.%s as r
                         FROM    %s as a,
                         %s as b
                         WHERE   a.%s BETWEEN b.%s
                         AND   b.%s and a.%s = %s
                         GROUP BY a.%s"
                         , $this->lname, $this->rname, $this->tablename, $this->tablename, $this->lname, $this->lname, $this->rname, $this->lname, $lpos, $this->lname);
        $result = $this->db->query($query);
        $row = $result->fetchrow(MDB2_FETCHMODE_ASSOC);
        $cutoff = $row["level"]+1;
        $rpos   = $row["r"];
        if ($data)
        {
        	$fields=""; //E_ALL fix
            foreach ($data as $field)
            {
                if (strstr($field,"("))
                {
                   $fields .= $field. " ,";

               }
               else 
               {
                   $fields .= "".$this->tablename.".$field as $field ,";
               }
                   
            }
        }
        
        $lj = '';
        if($leftJoin !== FALSE) {
            $lj = " LEFT JOIN $leftJoin[table] ON $leftJoin[on] ";
            foreach($leftJoin['fields'] as $field) {
                $fields .= "$leftJoin[table].$field, ";
            }
        }
        
        $query = "select  %s count(".$this->tablename.".%s) as level, ".$this->tablename.".%s as l, ".$this->tablename.".%s as r from %s as ".$this->tablename.", %s as b $lj where ".$this->tablename.".%s between b.%s and b.%s and ".$this->tablename.".%s between '%s' and '%s' $this->where group by ".$this->tablename.".%s";

        $query = sprintf( $query, $fields, $this->lname, $this->lname, $this->rname, $this->tablename, $this->tablename, $this->lname, $this->lname, $this->rname, $this->lname, $lpos, $rpos, $this->lname,$cutoff);

        if (!$wholeTree)
        {
            $query .= " having level=$cutoff";
        }
         return $query;
    }




    function children($lpos,&$data) {
        $query = $this->children_query($lpos,$data);

        $result = $this->db->query($query);

        while( $row = $result->fetchrow(MDB2_FETCHMODE_ASSOC))
        {
            if ($data)
            {   $z++;
                $result2[$z][lpos] = $row["l"];
                foreach ($data as $field)
                {
                    $result2[$z][$field] = $row[$field];

                }
            }
            else
            {
                $result2[] = $row["l"];
            }
        }

        return $result2;
    }

    function printTree ($lpos,$data=Null,$indent=0) {
        $children = $this->children($lpos,$data);

        if ($indent == 0) {
            print str_repeat(" ",$indent);
            print "$lpos " ;
            foreach ($data as $name) {

                print $child[$name]." ";
            }
            print "<br>";

        }

        if (is_array($children))
        {
            foreach ($children as $child)
            {
                print str_repeat(" ",$indent+2);
                print "$lpos " ;
                foreach ($data as $name) {

                    print $child[$name]." ";
                }
                print "<br>";

                $this->printTree($child[lpos],$data,$indent+2);
            }
        }

    }
    function importTree ($startID,$first = True, $order = "rang",$ParentPath ="",$ParentTitlePath = "")
    {
        
        /* DIeser ganze Code kann noch optimiert werden, sind wohl ein paar abfragen zuviel
        *  aber solange sich die updates im section bereich in grenzen h�lt, lohnt sich das
        * nicht wirklich...
        */
         
        if ($first) {
            $r = array($this->idField=>"$startID");
            
            if ($this->FullTitlePath ) {
                $fTP = "$this->FullTitlePath = 'root', ";
            }
            $query = "  update $this->tablename
            set l=1, r=2, $fTP $this->FullPath = 'root'
            WHERE   $this->idField = $startID ";
            $this->db->query($query);
        }
       	if (!isset($FullPath))
        {
        	$FullPath = "";
		}
       	if (!isset($FullTitlePath))
        {
        	$FullTitlePath = "";
		}
       	if (!isset($Path))
        {
        	$Path = "";
		}        
       	if (!isset($Title))
        {
        	$Title= "";
		}        

        if (isset($this->FullPath))
        {
          $FullPath .= ", $this->FullPath";
          $Path .= ", $this->Path";
       }
       if (isset($this->FullTitlePath))
        {
          $FullTitlePath .= ", $this->FullTitlePath";
          $Title .= ", $this->Title";
       }
      
        $result = $this->db->query("SELECT  $this->idField $FullPath $Path $FullTitlePath $Title
                                   FROM    $this->tablename
                                   WHERE   $this->referenceField = $startID 
                                    order by $order Desc"  );

                                    
        while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC))
        {

            
                $ThisParentPath = $ParentPath;
                if (!(empty($ParentPath) ) )
                {
                    $ThisParentPath .= "/";
                }
                $ThisParentPath .= $row[$this->Path];
                            

                $ThisTitlePath = $ParentTitlePath;

                if (strlen($ParentTitlePath) > 1 )                            
                    $ThisTitlePath .= $this->fullnameSeparator;
                
                $ThisTitlePath .= $row[$this->Title];
                $ftp = !empty($this->FullTitlePath) ? ", ".$this->FullTitlePath." = '".$ThisTitlePath ."' ": "";
                $q = "update $this->tablename
                     set 
                        $this->FullPath = '" .$ThisParentPath ."'
                        $ftp
                     where $this->idField = ".$row[$this->idField];
            $pathupdate = $this->db->query($q);
            $r = array("$this->idField" => $row[$this->idField]);
            $node = $this->update_below_byname(array("$this->idField" => "$startID"), $r);
            $this->importTree($row[$this->idField],False,$order,$ThisParentPath,$ThisTitlePath );

        }

    }
}
?>
