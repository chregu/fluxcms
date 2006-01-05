<?php

class bx_editors_blog extends bx_editor implements bxIeditor {

    public function getPipelineName($path,$id) {
        return "blog";
    }
    
    public function getDisplayName() {
        return "Blog";
    }
    
    public function getStylesheetNameById($path,$id) {
        
        switch ($id) {
            case ".":
            return "start.xsl";

            case "uploadimage.xml":
            return "uploadimage.xsl";
        }
        
        if(($subEditor = $this->getSubEditorNameById($id)) !== FALSE) {
            if(($se = $this->getSubEditorInstance($subEditor)) !== FALSE)
                return $this->getStylesheetNameBySubEditor($subEditor);
                
        }
        return "post-fck.xsl";
        
    }
    
    public function handlePOST($path, $id, $data) {
        
        // pass request to a subeditor if required
        if(($subEditor = $this->getSubEditorNameById($id)) !== FALSE) {
            return $this->getSubEditorInstance($subEditor)->handlePOST($path, $id, $data);
        }
        // delete selected comments
        if(!empty($data['deletecomments'])) {
            
            $this->deleteComments($data['deletecomments'],bx_streams_blog::getTablePrefix($id));
        }
        
        if(!empty($data['deleteposts'])) {
            
            $this->deletePosts($data['deleteposts'],bx_streams_blog::getTablePrefix($id));
        }
        
        if(!empty($data['uri'])) {
            if ($data['delete'] == 1 && $data['id']) {
                   bx_streams_blog::deleteEntryDirect($data['id'],$id);
                   header("Location: ./newpost.xml");
                   die();
            } else {
                if(!empty($data['newcategory'])) {
                    $dbwrite = $GLOBALS['POOL']->dbwrite;
                    $tableprefix = $GLOBALS['POOL']->config->getTablePrefix();

                    $quoted = array();
                    
                    $dataN['name'] = $data['newcategory'];
                    $dataN['uri'] = bx_helpers_string::makeUri($data['newcategory']);
                    $quoted = bx_helpers_sql::quotePostData($dataN);
                    $quoted['parentid'] = 1;
                    $query = bx_helpers_sql::getInsertQuery('blogcategories', $quoted, array('name', 'uri', 'parentid'));
                    
                    $res = $dbwrite->query($query);
                    if($res) {
                        bx_helpers_sql::updateCategoriesTree();
                    }
                    //set checkbox
                    $data['categories'][$data['newcategory']] = "on";
                }
                bx_global::registerStream("blog");
                
                // remove  html enitities sometimes sent by fckeditor
                foreach ($data as $key => $value) {
                    if (!is_array($data[$key])) {
                        $data[$key] = html_entity_decode(str_replace("&amp;","&amp;amp;",$data[$key]),ENT_NOQUOTES,"UTF-8");
                    }
                }
                
                $fd = fopen("blog://".$id,"w");
                $data['uri'] = bx_helpers_string::makeUri($data['uri']);
                if (!isset($data['id']) || !($data['id'])) {
                    $data['uri'] =  bx_streams_blog::getUniqueUri($data['uri'],$id);
                }
                if (isset($data['nl2br']) && $data['nl2br'] == 1) {
                    //our own nl2br
                    $data['content'] = preg_replace("#\r#","",$data['content']);
                    $data['content'] = preg_replace("#([^>])[\n]{2,}#","$1<br/>\n<br/>\n",$data['content']);
                    $data['content'] = preg_replace("#([^>])\s*([\n])#","$1<br/>$2",$data['content']);
                    
                    $data['content_extended'] = preg_replace("#\r#","",$data['content_extended']);
                    $data['content_extended'] = preg_replace("#([^>])[\n]{2,}#","$1<br/>\n<br/>\n",$data['content_extended']);
                    $data['content_extended'] = preg_replace("#([^>])\s*([\n])#","$1<br/>$2",$data['content_extended']);
                }
                
                if (isset($data['autodiscovery'])) {
                    $data['autodiscovery'] = 1;
                } else {
                    $data['autodiscovery'] = 0;
                }
                
                
                fwrite($fd, '<entry xmlns="http://purl.org/atom/ns#">');
                fwrite($fd, '<title>'.$data['title'].'</title>');
                fwrite($fd, '<id>'.$data['id'].'</id>');
                fwrite($fd, '<uri>'.$data['uri'].'</uri>');
                fwrite($fd, '<created>'.$data['created'].'</created>');
                fwrite($fd, '<expires>'.$data['expires'].'</expires>');
                fwrite($fd, '<tags>'.trim($data['tags']).'</tags>');
                fwrite($fd, '<trackback>'.$data['trackback'].'</trackback>');
                fwrite($fd, '<autodiscovery>'.$data['autodiscovery'].'</autodiscovery>');
                fwrite($fd, '<status>'.$data['status'].'</status>');
                fwrite($fd, '<comment_mode>'.$data['comment_mode'].'</comment_mode>');
                //fwrite($fd, '<link>'.$data['link'].'</link>');
                
                fwrite($fd, '<atom:content type="application/xhtml+xml" xmlns:atom="http://purl.org/atom/ns#" xmlns="http://www.w3.org/1999/xhtml">'.$data['content'].'</atom:content>');
                fwrite($fd, '<atom:content_extended type="application/xhtml+xml" xmlns:atom="http://purl.org/atom/ns#" xmlns="http://www.w3.org/1999/xhtml">'.$data['content_extended'].'</atom:content_extended>');
                fwrite($fd, '<categories xmlns="http://sixapart.com/atom/category#"  xmlns:dc="http://purl.org/dc/elements/1.1/">');
                if (isset($data['categories']) && is_array($data['categories'])) { 
                    foreach ($data['categories'] as $value => $cat) {
                        fwrite ($fd, '<dc:subject>'.$value.'</dc:subject>');
                    }
                }
                fwrite ($fd, '</categories>');
                
                fwrite($fd, '</entry>');
                fclose($fd);
                if ("/".$data['uri'].".html" != $id) {
                    header("Location: ".$data['uri'] .".html");
                }
            }
        }
    }
    
    public function getEditContentById($id) {
        if(($subEditor = $this->getSubEditorNameById($id)) !== FALSE) {
            if(($se = $this->getSubEditorInstance($subEditor)) !== FALSE) {
                return $se->getEditContentById($id);
            }
        }
    }
    
    protected function getSubEditorNameById($id) {
        if(preg_match('#sub/([^\/]+)\/#', $id, $m)) {
            return $m[1];
        }
        return FALSE;
    }
    
    protected function getStylesheetNameBySubEditor($editor) {
        return "sub/xsl/$editor.xsl";
    }
    
    protected function getSubEditorInstance($editor) {
        
        $class = "bx_editors_blog_sub_$editor";
        
        if(class_exists($class)) {
            $editor = new $class();
            $editor->blogEditor = $this;
            return $editor;
        }
        return FALSE;
        
    }
    
    protected function deleteComments($idArray, $tablePrefix) {
        $ids = implode($idArray, ',');
        
        $res = $GLOBALS['POOL']->dbwrite->query("delete from ". $tablePrefix."blogcomments where id in ($ids)");
    }
    
    protected function deletePosts($idArray, $tablePrefix) {
        $ids = implode($idArray, ',');
        $res = $GLOBALS['POOL']->dbwrite->query("delete from ". $tablePrefix."blogposts where id in ($ids)");
    }
    
}

?>
