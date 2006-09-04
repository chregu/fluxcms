<?php

class bx_config_generate {
    
    
    /**
     * merges 2 different XML files
     *  the 1st one overrides the 2nd one
     *  useful for merging config/default.xml with conf/config.xml
     */
        
    static function mergeXML($xml1, $xml2) {
        
        $dom1 = new domdocument();
        $dom1->load($xml1);
        $dom2 = new domdocument();
        $dom2->load($xml2);
        
        $xp1 = new domxpath($dom1);
        $xp2 = new domxpath($dom2);
        foreach($xp2->query("//*[(not(*) and local-name() != 'item') or (item)]") as $node) {
               $pp = self::parentPath($node);
               $res = $xp1->query($pp);
               //if the node already exists in dom1, move forward
               if ($res->length == 1) {
                   continue;
               }               
               //otherwise try to find a matching node
               $parent = $node->parentNode;
               $oldnode = $node;
               while($parent->nodeType == 1) {
                   $parentpath = self::parentPath($parent);
                   $res = $xp1->query($parentpath);
                   if ($res->length >= 1) {
                       $newnode = $dom1->importNode($oldnode,true);
                       $res->item(0)->appendChild($newnode);
                       break;
                   }
                   $oldnode = $parent;
                   $parent = $parent->parentNode;
               }
        }
  
        return $dom1;        
    }
    //generates an xpath to this node with all the attributes defined
    static function parentPath($node) {
        $path ="";
        $parent = $node;
        while ($parent->nodeType == 1) {
            $attrs = array();
            foreach($parent->attributes as $attr) {
                if ($attr->name == 'allowFromDB') {
                    continue;
                }
                $attrs[] = '@'.$attr->name . ' = "'.$attr->value .'"'; 
            }
            $newpath = "/".$parent->localName;
            
            if (count($attrs) > 0) {
                $newpath .= '['. implode(" and ",$attrs) .']';   
            }
            $path = $newpath.$path;
            
            $parent = $parent->parentNode;
        }
        return $path;
    }
    
    
    static function generateCachedConfigFile($configfile, $bxdir, $tmpdir) {
        //constants
        $dom = self::mergeXML($configfile,$bxdir."/config/default.xml");
        $sxe = simplexml_import_dom($dom);
        
        if (!file_exists($tmpdir)) {
            mkdir($tmpdir);
        }
        $fd = fopen($tmpdir.'/config.inc.php',"w");
        
        
        fwrite($fd,'<?php'."\n");
         //staging
        $_staging = false;
        
        fwrite($fd,'$_staging =array();'."\n");
            
        foreach($sxe->staging->stage as $n) {
            $stage = trim((string) $n);
            
            fwrite($fd,'$_staging[\''.$stage.'\']= array();'."\n"); 
            $de = dom_import_simplexml($n);
            foreach($de->attributes as $a) {
                fwrite($fd,'$_staging[\''.$stage.'\'][\''.$a->localName.'\'] = \''. $a->value."';\n");
            }
            if (!$_staging) {
                $_staging_first = $stage;
                $_staging = true;
            }
            
        }
        
        if (!$_staging) {
            fwrite($fd, '$_staging = false;'."\n");
            fwrite($fd, '$_stage = "";'."\n");
        } else {
            fwrite($fd,'@list($firsthost,$resthost) = explode(".",$_SERVER[\'HTTP_HOST\'],2)'.";\n");
            fwrite($fd,'if ($resthost && !isset($_staging[$firsthost])) {'."\n");
            fwrite($fd,'   $_stage = $firsthost'.";\n");
            fwrite($fd,'}'."\n");
            fwrite($fd,' else {'."\n");
            fwrite($fd,' $_stage = \''.$_staging_first."';\n");
            fwrite($fd,'}'."\n");
        }
        fwrite($fd,"define('BX_STAGE',"); 
        fwrite($fd,'$_stage');
        fwrite($fd,");\n");        
        
        $const = array();
        foreach ($sxe->constants->constant as $node) {
            if ( (string) $node != 'none')  {
                $const[(string) $node['name']] = (string) $node;
            }
        }
        if (isset($const['BX_WEBROOT'])) { 
            if ($const['BX_WEBROOT'] == 'auto') {
                $const['BX_WEBROOT'] = 'http(s)://'.$_SERVER['HTTP_HOST'].'/';
            }
        }
        
        if (isset($const['BX_PROJECT_DIR']) && $const['BX_PROJECT_DIR'] == 'auto') {
            $const['BX_PROJECT_DIR'] = substr(__FILE__,0,-26);
        }
        
        // write constants 
    
        
        foreach($sxe->files->before as $file) {
            fwrite($fd,"include_once('".self::replaceConstants(str_replace('\\','/',$file))."');\n");
        }
        
        // the function check can be removed, once 5.1.0 is really released
            fwrite($fd, 'if (version_compare(phpversion(),"5.0.99",">") && function_exists("libxml_use_internal_errors")) {');
            fwrite($fd,'libxml_use_internal_errors(TRUE);'."\n");
            fwrite($fd,'}'."\n");
        

        foreach ($const as $name => $c) {
            $c  = "'".preg_replace("/\{([a-zA-Z0-9_\$'\[\]]*)\}/","'.$1.'",str_replace('\\','/',$c))."'";
            $c = str_replace("'http(s)://","((!empty(\$_SERVER['HTTPS']))?'https':'http').'://", $c);
            fwrite($fd,"define('".$name."',"); 
            fwrite($fd,$c);
            fwrite($fd,");\n");
        }
        fwrite($fd,"define('BX_OS_WIN',"); 
        
        if (stripos(PHP_OS,"win") === 0) {
        	fwrite($fd,"true"); 
        } else {
        	fwrite($fd,"false"); 
        }
        fwrite($fd,")\n;");
        	
        fclose ($fd);
        //open post config file (after we initialized some stuff.
        $fd = fopen($tmpdir.'/config.inc.php.post',"w");
        fwrite($fd,'<?php'."\n");
        fwrite($fd,'$bx_config->staging = $_staging;'."\n");
        // database connections
        $bxc = array();
        foreach($sxe->connections->db as $db) {
            $type = (string) $db['type'];
            if ($db['copy']) {
                $bxc[$type] = $bxc[(string) $db['copy']];
            } else {               
                $bxc[$type] = array();
            }
            foreach ($db->children() as $child) {
                
//                $name =  dom_import_simplexml($child)->localName;
                $bxc[$type][ dom_import_simplexml($child)->localName] = (string) $child;
            }
            
            fwrite ($fd, '$'."bx_config['".$type."'] = ");
            
            fwrite($fd,var_export($bxc[$type], true).";\n");
            if ($type == 'dsn') {
                $dsn = $bxc[$type];
            }
        }
        
        
        // permissionmanager connections
        
        foreach($sxe->connections->permm as $perm) {
            $type = (string) $perm['type'];
            if ($perm['copy']) {
                $bxc[$type] = $bxc[(string) $perm['copy']];
            } else {               
                $bxc[$type] = array();
            }
            if (count($perm->xpath('permModule')) > 0) {
                foreach ($perm->permModule->children() as $child) {
                    if ($child['copy']) {
                        $bxc[$type]['permModule'][ dom_import_simplexml($child)->localName] = $bxc[(string) $child['copy']]; 
                    } else {
                        $bxc[$type]['permModule'][ dom_import_simplexml($child)->localName] = (string) $child;
                    }
                }
                
                
            }
            if (count($perm->xpath('authModule')) > 0) {
                foreach ($perm->authModule->children() as $child) {
                    if ($child['copy']) {
                        $bxc[$type]['authModule'][ dom_import_simplexml($child)->localName] = $bxc[(string) $child['copy']]; 
                    } else {
                        
                        $bxc[$type]['authModule'][ dom_import_simplexml($child)->localName] = (string) $child;
                    }
                }
            }
            
            fwrite ($fd, '$'."bx_config['".$type."'] = ");
            fwrite($fd,var_export($bxc[$type], true).";\n");
        }
        //general options      
        
        //try to get options from db
        
        // we have to fiddle with the include path here... 
        // it's an assumption, that MDB2 is 2 levels above here...
        // FIXME: if that's not the case for some
         $optionsFromDB = array();
         $cacheDBOptions = ($sxe->options['cacheDBOptions'] != 'false');
         $optionsMergeArray = array();
        if ($cacheDBOptions) {
            $oldinc = ini_get("include_path");
            ini_set("include_path",realpath(dirname(__FILE__)."/../../"));
            @include_once("MDB2.php");
            $db = @MDB2::connect($dsn);
            if (@MDB2::isError($db)) {
                print $db->getMessage();
                print "<br/>";
                die ("no DB connection possible. please check your permissions");  
            }
            @$db->query("set names 'utf8'");
            $res = @$db->query("select * from ".$dsn['tableprefix']."options");
           
            if (!@MDB2::isError($res)) { 
                while ($row = @$res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
                    //only use them, if they have a value
                    if ($row['value']) {
                        if ($row['isarray']) {
                            $optionsFromDB[$row['name']]  = explode(";",html_entity_decode($row['value'],ENT_COMPAT,'UTF-8'));
                        } else {
                            $optionsFromDB[$row['name']] = html_entity_decode($row['value'],ENT_COMPAT,'UTF-8');
                        }
                    }
                }
                include("popoon/pool.php");
                include("bx/helpers/debug.php");
                
                fwrite ($fd,'$bx_config->dbIsFourOne = ' . var_export(@popoon_pool::isMysqlFourOne($dsn,$db),true) . ";\n");
                fwrite ($fd,'$bx_config->dbIsUtf8 = ' . var_export((@popoon_pool::isMysqlFourOne($dsn,$db) && @popoon_pool::isMysqlUTF8($dsn,$db)),true) . ";\n");
            } 
            
            ini_set("include_path",$oldinc); 
        } else {
            $notAllowedDBOptions = array();
        }
        
        foreach($sxe->options->children() as $child) {
            $childName = dom_import_simplexml($child)->localName ;
            
            // if not allowed to be overwritten, remove it
            if ((string) $child['allowFromDB'] != "true") {
                if (!$cacheDBOptions ) {
                    array_push($notAllowedDBOptions,$childName);     
                } else if ( isset($optionsFromDB[$childName])) {
                    unset ($optionsFromDB[$childName]);
                }
            } 
            //check if we want to merge them otherwise
            else if ((string) $child['mergeArray'] == 'true') {
                    array_push($optionsMergeArray,$childName);
            }
            // if we want to cache the dboptions, get them here
            if ($cacheDBOptions && isset($optionsFromDB[$childName])) {
                $bxc = $optionsFromDB[$childName];
                //if we want to merge the array values, do that here
                if (count($child->xpath('item')) > 0 && (string) $child['mergeArray']== 'true' ) {
                     foreach ($child->item as $item) {
                        $bxc[]=(string) $item;
                    }
                    $bxc = array_unique($bxc);
                }
                unset ($optionsFromDB[$childName]);
            } else {
                if (count($child->xpath('item')) > 0) {
                    $bxc = array();
                    foreach ($child->item as $item) {
                        $bxc[]=(string) $item;
                    }
                } else {
                    $bxc = (string) $child;
                }
            }
        
            fwrite($fd,'$bx_config->'.$childName ."=".self::replaceConstants(var_export($bxc,true)).";\n");
        }
     
        // print the other options...
        
        foreach ($optionsFromDB as $name => $value) {
             fwrite($fd,'$bx_config->'.$name ."=".var_export($value,true).";\n");
        }
        
        //streams
        foreach($sxe->streams->stream as $stream) {
            fwrite($fd,"bx_global::registerStream('".(string) $stream."');\n");
        }
        
        if ($sxe->cache) {
            fwrite($fd,"\$bx_config->cache = '".$sxe->cache['driver']."';\n");
            $options = array();
            if ($sxe->cache->option) {
                foreach($sxe->cache->option as $o) {
                    $o = dom_import_simplexml($o);
                    $option = array();
                    foreach($o->childNodes as $node) {
                        if($node->nodeType == 1) {
                            $option[$node->localName] = $node->textContent;
                        }
                    }
                    $options[] = $option;
                }
            }
            fwrite($fd,"\$bx_config->cacheOptions = ".var_export($options,true).";\n");
            
        } else {
            
            fwrite($fd,"\$bx_config->cache = 'dummy';\n");
        }
        
        
        //notifications
        $_notification = false;
        
        fwrite($fd,'$bx_config->notifications =array();'."\n");
        if ($sxe->notifications->transport) {       
            fwrite($fd, '$bx_config->notifications[\'default\'] = \''.(string) $sxe->notifications['default'] ."';\n");
            
            foreach($sxe->notifications->transport as $n) {
                $type = (string) $n;
                
                fwrite($fd,'$bx_config->notifications[\''.$type.'\']= array();'."\n"); 
                $de = dom_import_simplexml($n);
                foreach($de->attributes as $a) {
                    fwrite($fd,'$bx_config->notifications[\''.$type.'\'][\''.$a->localName.'\'] = \''. $a->value."';\n");
                }
                $_notification = true;
                
            }
        }
        
        if (!$_notification) {
            fwrite($fd, '$bx_config->notifications[\'default\'] = \'mail\''.";\n");
            fwrite($fd,'$bx_config->notifications[\'mail\']= array();'."\n");
        }
        
        
       
        
        //additional files to be included
        foreach($sxe->files->include as $file) {
            fwrite($fd,"include_once('".preg_replace("/\{([a-zA-Z0-9_\$'\[\]]*)\}/","'.$1.'",str_replace('\\','/',$file))."');\n");
        }
        if (!$cacheDBOptions) {
            fwrite($fd,"bx_init::initDBOptions(".var_export($notAllowedDBOptions,true).",".var_export($optionsMergeArray,true).");\n");
            fwrite($fd,'$bx_config->cacheDBOptions = false;'."\n");
        } else {
            fwrite($fd,'$bx_config->cacheDBOptions = true;'."\n");
        }
        
        fwrite($fd,'$bx_config->magicKey = "'.md5(time() . rand(0,1000000)).'";'."\n");
        fclose($fd);
    }
    static function replaceConstants($input) {
        return preg_replace("/\{([a-zA-Z0-9_\$'\[\]]*)\}/","'.$1.'",$input);
    }
    
   
}
