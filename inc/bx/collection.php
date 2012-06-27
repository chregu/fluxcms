<?php
// +----------------------------------------------------------------------+
// | Bx                                                                   |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2007 Liip AG                                      |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License (GPL)    |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// | The GPL can be found at http://www.gnu.org/licenses/gpl.html         |
// +----------------------------------------------------------------------+
// | Author: Liip AG      <devel@liip.ch>                              |
// +----------------------------------------------------------------------+


class bx_collection implements bxIresource {


    protected $pluginMap = array();
    protected $plugins = array();
    protected $filters = array();
    protected $allproperties = array();
    protected $properties = array();
    protected $mimetypeMapping =array("text/html" => "xhtml" , "text/wiki" => "wiki");
    protected $outputLanguage = 'de';
    protected $lastModifiedResource = NULL;
    protected $config;

    /*** magic methods ***/

    public function __get($name) {

    }

    public function __construct($url, $mode, $new = false) {
        if ($url == "") {
            $url ="/";
        }
        $url = str_replace("//","/",$url);
        $this->uri = $url;
        $this->id = $url;
        $this->mode = $mode;
        if ($new) {
            $this->init();
        }
        $this->config = new bx_collectionconfig($this->uri, $mode);
        $this->outputLanguage = $GLOBALS['POOL']->config->getOutputLanguage();
    }


    /** general methods **/

    private function init() {
        //bx_log::log('bx_collection::init');
        $this->uri = str_replace("..","",$this->uri);
        $parent = BX_DATA_DIR.bx_collections::sanitizeUrl(dirname($this->uri));

        if (!file_exists($parent)) {
           throw new PopoonFileNotFoundException(dirname($this->uri));
        }

        $name = basename($this->uri);
        // create directory on filesystem for new collection
        $collectionDirName = $parent.'/'.$name;

        if(!file_exists($collectionDirName)) {
            $stat = mkdir ( $collectionDirName, 0755);
        }
        // do nothing, if we have a .children file here
        if (file_exists($parent.'/'.BX_CONFIGXML_FILENAME.'.children')) {

        }
        // copy .configxml, if it exists, otherwise we assume, that there's a .children somewhere up
        else if (file_exists($parent.'/'.BX_CONFIGXML_FILENAME )) {
            copy($parent.'/'.BX_CONFIGXML_FILENAME,$parent.'/'.$name.'/'.BX_CONFIGXML_FILENAME);
        }
        $this->setProperty("mimetype","httpd/unix-directory");
        $this->setProperty("output-mimetype","httpd/unix-directory");
        $parentUri = bx_collections::sanitizeUrl(dirname($this->uri));

        $this->setProperty("parent-uri",$parentUri);
        $this->setProperty("display-name",$name, BX_PROPERTY_DEFAULT_NAMESPACE.BX_DEFAULT_LANGUAGE);
        $this->setProperty("display-order",0);
        $this->setProperty("unique-id", bx_helpers_sql::nextSequence() );
        return true;
    }

    public function resourceExistsByRequest($filename,$ext) {
         $map = $this->getPluginMapByRequest($filename,$ext);
         if (count($map) > 0) {
             foreach ($map as $p) {
                 if (isset($p['plugin']) && $p['plugin']->isRealResource($this->uri,$p['id'])) {
                     return true;
                 }
             }
         }
        return false;
    }

    public function resourceExistsById($id) {
         $map = $this->getPluginMapById($id);
         return $map['plugin']->resourceExistsById($this->uri, $id);
    }

  /**
    * gets the content of all relevent output plugins
    *
    * @param string $filename filename of the request
    * @param string $ext extension of the request
    * @param DomDocument A Dom Object
    */

    public function getContentByRequest($filename, $ext) {

        return $this->getContentByPluginMap($this->getPluginMapByRequest($filename, $ext , false) );
    }

    public function getContentById($id) {
        $p = array();

        $p[0] = $this->getPluginMapById($id);
        return $this->getContentByPluginMap($p);

    }

    public function getChildResourceById($id) {

         $plugin = $this->getPluginById($id);
         if ($plugin instanceof bxIplugin) {
                return $plugin->getResourceById($this->uri,$id);
         }

         return null;

    }

    protected function getContentByPluginMap($pluginMap) {
        $xml = new DomDocument();
        $xml->appendChild($xml->createElement("bx"));
        if (is_array($pluginMap)) {
            $javascripts= array();
        foreach ($pluginMap as $p) {

            if(isset($p['plugin']) && $p['plugin'] instanceof bxIplugin) {
                $plugin = $xml->createElement("plugin");
                $plugin->setAttribute("name", $p['plugin']->name);

                $p['plugin']->setCurrentRequest($this->uri,$p['id']);

                $pluginXML =$p['plugin']->getContentById($this->uri,$p['id']);
                if (method_exists($p['plugin'],"getLastModifiedById")) {
                    $this->lastModifiedResource = max($this->lastModifiedResource,$p['plugin']->getLastModifiedById($this->uri, $p['id']));
                }
                $p['plugin']->removeCurrentRequest($this->uri,$p['id']);

                if ($pluginXML instanceof DomDocument && $pluginXML->documentElement) {
                    if ($p['plugin']->stripRoot()) {
                        foreach ($pluginXML->documentElement->childNodes as $node) {
                            $import =  $xml->importNode($node ,true);
                            $plugin->appendChild($import );
                        }
                    }
                    else {
                        $import =  $xml->importNode($pluginXML->documentElement ,true);
                        $plugin->appendChild($import );
                    }

                    $xml->documentElement->appendChild($plugin);
                    unset($pluginXML);

                } else {
                    $xml->documentElement->appendChild($plugin);
                }

                if (method_exists($p['plugin'],"getJavaScriptSources")) {
                   $javascripts =
array_merge($javascripts,$p['plugin']->getJavaScriptSources());
                }
            }
        }
        if (count($javascripts) > 0) {
            $jsroot = $xml->documentElement->appendChild($xml->createElement("javascripts"));
            foreach($javascripts as $js) {
                $jsroot->appendChild($xml->createElement("src",$js));
            }

        }
        }



       return $xml;
    }

    public function getPluginMapByRequest($filename,$ext,$first = false ) {

        if (!isset($this->pluginMap[$filename.$ext])) {
            $plugins = $this->getPluginsByRequest($filename,$ext ,$first);

             foreach ($plugins as $p) {
                $id = str_replace("//","/",$p->getIdByRequest($this->uri,$filename,$ext));
                if ($id) {
                    $map = Array();
                    $map['plugin'] = $p;
                    $map['id'] = $id;
                    $this->pluginMap[$filename.$ext][$id] = $map;
                } else {
                    //$this->pluginMap[$filename.$ext][$id] = array();
                }
            }
            if (!isset($this->pluginMap[$filename.$ext])) {
                $this->pluginMap[$filename.$ext] = null;
            }
        }

        return $this->pluginMap[$filename.$ext];
    }

    /**
    * gets all the outputplugins associated to a $name
    *  $name is usually the filename part of an uri
    *
    * @param string $name the name of the request
    * @param string $ext the extension part of the request
    * @return array all outputplugins
    */


    protected function getPluginsByRequest($name, $ext, $first=false) {
        if (empty($this->plugins[$name.$ext])) {
            $this->plugins[$name.$ext] = $this->config->getPlugins($name, $ext,$first);
        }
        return $this->plugins[$name.$ext];
    }

    public function getFirstPluginMapByRequest($filename,$ext) {
        $map = $this->getPluginMapByRequest($filename,$ext);
        if(is_array($map))
            return array_shift($map);
    }

    protected function getPluginMapById($id) {

        $map = NULL;
        if (isset($this->pluginMap[$id]) and isset($this->pluginMap[$id][$id])) {
            $map = $this->pluginMap[$id][$id];
        }

        // try to find it...
        if (!$map) {

            $map = array();

            $map['plugin'] = $this->getPluginById($id);
            $map['id'] = $id;


            $this->pluginMap[$id][$id]=$map;
        }

        return $map;
    }

    public function getContentUriById($id, $sample = false) {
        $p = $this->getPluginMapById($id);
        if (is_object($p['plugin'])) {
            return $p['plugin']->getContentUriById($this->uri, $id, $sample);

        } else {

            return false;
        }
    }


    public function getPluginResourceById($id) {
        $p = $this->getPluginMapById($id);
        if ($p['plugin'] instanceof bxIplugin) {
            return $p['plugin']->getResourceById($this->uri, $id);
        } else {
            return null;
        }
    }


    public function handlePostById($id,$data, $mode = null) {
        $p = $this->getPluginMapById($id);

        $old = file_get_contents($this->getContentUriById($id,$this->uri));
        $return = $p['plugin']->handlePOST($this->uri,$id,$data,$mode);
        $r = $this->getChildResourceById($id);
        if ($r) {
            $r->onSave($old);
        }

        return $return;
    }



    public function getPluginById($id, $sample = false) {
        $parts = bx_collections::getFileParts($id);
        $ret = $this->config->getAdminPlugin($parts['name'], $parts['ext'],$sample);
        return $ret;

    }

    public function getPluginMimeTypes() {

        $ps = $this->getChildrenPlugins();
        $mimetypes = array();
        foreach ($ps as $p) {
            $mimetypes = array_merge_recursive($mimetypes,$p->getMimeTypes());
        }

        $mimetypeMap = array();
        foreach ($mimetypes as $mimetype) {
            //FIXME: the mimetype mapping is hardcoded for the time being
            // should be changeable in .configxml
            if (isset($this->mimetypeMapping[$mimetype])) {
                $mimetypeMap[$this->mimetypeMapping[$mimetype]] = $mimetype;
            }

        }
        return $mimetypeMap;
    }

    public function getPluginResourceTypes() {
        $ps = $this->getChildrenPlugins();
        $resourceTypes = array();
        foreach ($ps as $p) {
            $resourceTypes = array_merge_recursive($resourceTypes, $p->getResourceTypes());
        }
        return $resourceTypes;
    }

    public function getPluginByResourceType($type) {
         $ps = $this->getChildrenPlugins();
         foreach ($ps as $p) {
             if (in_array($type,$p->getResourceTypes())) {
                 return $p;
             }
         }
         return null;
    }

    /**
     *  returns all Output Plugins associated to this collections
     *
     *  useful for the tree plugin (if you need for example all
     *   the resources of all plugins for navigation
     *
     * @returns array all plugins associated to this children
     */

    public function getChildrenPlugins() {
        if (count($this->childrenPlugins) == 0) {
            $this->childrenPlugins =  $this->config->getChildrenPlugins();
        }
        return $this->childrenPlugins;
    }

    /** pipeline methods **/

    public function getRequestById($id) {
       $p = $this->getPluginById($id);

       if ($p) {
           return $p->getRequestById($this->uri,$id);
       }
       return null;
    }

    /**
     * Returns general and plugin/request - dependant parameters.
     * Required parameters:
     *   'pipelineName' - returned by FIRST plugin (is it logical?)
     *   'filters' - configured in .configxml
     *
     * @param name filename/path of request
     * @ext extension of request
     * @return array of parameters
     *
     */
    public function getPipelineParametersByRequest($filename, $ext) {
      $map = $this->getPluginMapByRequest($filename, $ext);
      $a = array();
      $a['pipelineName'] = 'standard'; /// default
      $a['filters'] = $this->getFiltersByRequest($filename, $ext);
      // merge other parameters from all plugins
      if (is_array($map)) {
        foreach ($map as $p) {
          if(isset($p['plugin']) && $p['plugin'] instanceof bxIplugin) {
            $params = $p['plugin']->getPipelineParametersById($this->uri, $p['id']);
            $a = array_merge($a, $params);
            }
          }
        }
      return $a;
     }

    protected function getFiltersByRequest($name, $ext) {
        if (empty($this->filters[$name.$ext])) {
            $this->filters[$name.$ext] = $this->config->getFilters($name, $ext);
        }
        return $this->filters[$name.$ext];
    }

   public function getParentCollection() {
        if ($parent = $this->getProperty("parent-uri")) {
            return bx_collections::getCollection($parent,$this->mode);
        }
        return NULL;
    }


    public function getSubCollection($name, $mode) {
        return bx_collections::getCollection($name, $mode);
    }


    public function getEditorsById($id) {
        $p = $this->getPluginMapById($id);
        if ($p['plugin'] instanceof bxIplugin) {
            return $p['plugin']->getEditorsById($this->uri, $id);
        }

        return array();

    }

    public function copyResourceById($id, $to) {
        // id $id is empty, it's a collection
        if ($id == "") {
            return $this->copy($to);
        }
        $p = $this->getPluginMapById($id);
        if ($p['plugin'] instanceof bxIplugin) {
            return $p['plugin']->copyResourceById($this->uri, $id, $to);
        }
    }

    public function moveResourceById($id, $to) {
        // id $id is empty, it's a collection
        if ($id == "") {
            if ($this->copy($to, true)) {
                return $this->delete();
            } else {
                return false;
            }
        }
        $p = $this->getPluginMapById($id);
        if ($p['plugin'] instanceof bxIplugin) {
            if ($p['plugin']->copyResourceById($this->uri, $id, $to, true)) {
                return $p['plugin']->deleteResourceById($this->uri, $id);
            } else {
                return false;
            }
        }
    }



    public function deleteResourceById( $id) {
        // id $id is empty, it's a collection



        if ($id == "") {
            return $this->delete();

        }

        bx_metaindex::removeAllTagsById($this->uri . $id);

        $p = $this->getPluginMapById($id);
        if ($p['plugin'] instanceof bxIplugin) {
            return $p['plugin']->deleteResourceById($this->uri, $id);
        }
    }

    public function copy($to, $move = false) {

        $to = str_replace("//","/",$to);
        if (substr($to , -1) != "/") {
            $to .= "/";
        }
        // if enddestination is a directory, adjust accordingly
        // meaning copy it into that directory instead of replace it
/*
        DOES NOT WORK YET :)
        if (is_dir(BX_DATA_DIR.$to)) {
            $to = $to . basename($this->uri);
        }
        */
        new bx_collection($to, $this->mode, true);
        foreach (bx_resourcemanager::getAllProperties($this->uri) as $key => $value) {
            // do not copy the unique-id
            // when copying a collection
            if(!$move AND $value['name'] == 'unique-id') {
                $value['value'] = bx_helpers_sql::nextSequence();
            }
            if ($value['name'] != 'parent-uri') {
                bx_resourcemanager::setProperty($to,$value['name'],$value['value'],$value['namespace']);
            }
        }
        $ps = $this->getChildrenPlugins();
        foreach ($ps as $p ) {
            $p->collectionCopy('before',$this->uri, $to, $move);
        }
        // copy .configxml
        @copy(BX_DATA_DIR.$this->uri.BX_CONFIGXML_FILENAME,BX_DATA_DIR.$to.BX_CONFIGXML_FILENAME);
        @copy(BX_DATA_DIR.$this->uri.BX_CONFIGXML_FILENAME.'.children',BX_DATA_DIR.$to.BX_CONFIGXML_FILENAME.'.children');
        if (!$move) {
               bx_resourcemanager::setProperty($to,"display-order","0");
        }
        foreach($this->getChildren() as $res) {
            if (method_exists($res,"copy")) {
                $res->copy($to . $res->getBaseName(), $move);
            }
        }

        //inform plugins, that collection moved

        // inform permission management that collection has beem moved:
        $permObj = bx_permm::getInstance(bx_config::getInstance()->getConfProperty('permm'));
        $permObj->movePermissions($this->uri, $to);

        return true;
    }


    public function delete() {

        if($this->uri == '/')
            return FALSE;

        foreach($this->getChildren() as $res) {
            $res->delete();
        }

        bx_resourcemanager::removeAllProperties( $this->uri);
        // delete all files in that dir
        $d = new DirectoryIterator(BX_DATA_DIR.$this->uri);
        foreach ($d as $file) {
            $filename = $file->getFileName();
            if ($filename == '.' || $filename == '..') {
                continue;
            }
            if ($file->isDir()) {
                bx_helpers_file::rmdir(BX_DATA_DIR.$this->uri.$filename);
            } else {
                unlink(BX_DATA_DIR.$this->uri.$filename);
            }
        }
        bx_helpers_file::rmdir(BX_DATA_DIR.$this->uri);
        $ps = $this->getChildrenPlugins();
        foreach ($ps as $p ) {
            $p->collectionDelete('after',$this->uri);
        }
        return true;
    }


    public function getResourceIdsByRequest($name, $ext) {
        $map = $this->getPluginMapByRequest($name,$ext);
        return array_keys($map);
    }

      // Resource Interface stuff

    public function getAllProperties($namespace = null) {
        if (!isset($this->allproperties[$namespace])) {
            $this->allproperties[$namespace] =  bx_resourcemanager::getAllProperties($this->uri, $namespace);

            foreach ($this->allproperties[$namespace]  as $key => $value) {
                $this->properties[$key] = $value['value'];
            }
        }
        return $this->allproperties[$namespace];
    }

    public function setProperty($name, $value, $namespace = BX_PROPERTY_DEFAULT_NAMESPACE) {
        $fullname = $namespace .":".$name;
        bx_resourcemanager::setProperty($this->uri, $name, $value, $namespace);

        $this->properties[$fullname] = $value;
         $this->allproperties = array();
    }

    public function getProperty($name, $namespace = BX_PROPERTY_DEFAULT_NAMESPACE) {
        if ($name == "output-mimetype") {
            return "httpd/unix-directory";
        }
        $fullname = $namespace .":".$name;
        //var_duop($this->properties);
        //var_dump($this->properties[$fullname]);
        if (!isset($this->properties[$fullname])) {
           $this->getAllProperties($namespace);
        }
        if (!isset($this->properties[$fullname])) {
            return NULL;
        }
        return $this->properties[$fullname];
    }

    public function removeProperty($name, $namespace = BX_PROPERTY_DEFAULT_NAMESPACE) {
        bx_resourcemanager::removeProperty($this->uri, $name, $namespace);
    }

    public function removeAllProperties($namespace = NULL) {
        bx_resourcemanager::removeAllProperties($this->uri, $namespace);
    }

    public function getId() {
        return $this->uri;
    }

    public function getMimeType() {
        return  "httpd/unix-directory";
    }

    public function getLastModified() {
       return filemtime(BX_DATA_DIR.$this->uri);
    }

    public function getLastModifiedResource() {
        return $this->lastModifiedResource;
    }

    public function getCreationDate() {
       return filectime(BX_DATA_DIR.$this->uri);
    }

    public function getDisplayName($lang = null) {
        if (!$lang) {
            $lang = $this->outputLanguage;
        }
        if ($d = $this->getProperty("display-name", sprintf("bx:%2s", $lang))) {
            return $d;
        } else {
            return preg_replace("#/(.*)/$#","$1",$this->id);
        }
    }

    public function getDisplayImage($lang = null) {
        if (!$lang) {
            $lang = $this->outputLanguage;
        }
        if (($o = $this->getProperty('display-image', sprintf("bx:%2s", $lang))) !== NULL) {
            return $o;
        }
    }

    public function getDisplayOrder() {
        if (($o = $this->getProperty('display-order')) !== NULL) {
            return $o;
        } else {
            return 0;
        }
    }

    public function getContentLength() {
       return 0;
    }

    public function getLocalName() {
        return $this->uri;
    }

    public function getLanguage() {
        return $GLOBALS['POOL']->config->getOutputLanguage();
    }

    public function getEditors() {
        return array();
    }

    public function saveFile($file, $uploadInfo = NULL) {
        return false;
    }


    public function getContentUri($sample = false) {
        return NULL;
    }

    public function getContentUriSample() {
        return NULL;
    }

    public function getResourceById($id) {
       return bx_collections::getCollection($id, $this->mode);
    }

    public function getResourceName() {
        return get_class($this);
    }

    /**
    * returns all Children of All Outputplugins belonging to this collection
    *
    * The returned Children are resources
    *
    * @return array with resources of all Children belonging to this section
    */

    public function getChildren($id = "") {
        $children = array();
        $p = $this->getChildrenPlugins();
        foreach($p as  $plugin) {
            $plugin->setCurrentRequest($this->uri,"");
            $children = array_merge_recursive($children,$plugin->getChildren($this,$id));
            $plugin->removeCurrentRequest($this->uri,"");
        }
        // for the collection itself
        if ($id == "" || substr($id,0,1) == "/") {
            $ch = bx_resourcemanager::getChildrenByMimeType($this,$this->getMimetype());
            foreach( $ch as $path) {
                $ch = $this->getResourceById($path);
                /*check if uri is child of $coll->uri
                */
                if( strpos($ch->uri,$this->uri) === 0 && $this->uri != $ch->uri) {
                     $children[] = $ch;
                }

            }
        }
        return $children;
    }

    public function freePlugins() {
        foreach ($this->plugins as $p) {
            unset($p);
        }
    }

    public function getPipelineProperties() {

        $props = $this->getAllProperties(BX_PROPERTY_PIPELINE_NAMESPACE);
        $params = array();
        foreach ($props as $p ) {
            $params[$p['name']] = $p['value'];
        }
        $params = array_merge( $params, $this->config->getParameters("pipeline"));
        return $params;
    }

    public function getBaseName() {
        return basename($this->uri);
    }

    public function getAdminMasterPlugin() {
        if(($plugin = $this->config->getAdminMasterPlugin()) != NULL) {
            return $plugin;
        }
        return $this->getPluginById("");
    }

    public function getAllOverviewSections($mainOverview) {

        $ps = $this->getChildrenPlugins();

        $sections = array();

        foreach ($ps as $name =>$p) {
            if ($s = $p->getOverviewSections($this->uri,$mainOverview)) {
                $sections[] = $s;
            }
        }
        if (!$mainOverview) {
            $sections[] = $this->getOverviewSections($mainOverview);
        }
        return $sections;
    }

    public function getOverviewSections($mainOverview) {
        $i18n = $GLOBALS['POOL']->i18nadmin;

        $permObj = bx_permm::getInstance(bx_config::getInstance()->getConfProperty('permm'));

        if(!$permObj->isAllowed('/',array('admin'))) {
            return;
        }

        $perm = bx_permm::getInstance();
        $sections = array();
        $dom = new bx_domdocs_overview();
        $dom->setTitle("Collection" ,"Create/Edit");
        $dom->setType("collection");
        $dom->setIcon("collection");
        $dom->setPath($this->uri);
          if ($perm->isAllowed($this->uri,array('collection-back-properties'))) {
              $dom->addLink("Properties", "properties/".$this->uri);
          }
            if ($perm->isEditable()) {
              if ($perm->isAllowed('/permissions/',array('permissions-back-manage'))) {
                $dom->addLink("Edit Permissions", "edit/permissions/".$this->uri);
              }
            }
            $dom->addSeperator();
            if ($perm->isAllowed($this->uri, array('collection-back-create'))) {
              $dom->addLink("Create new Collection", 'collection'.$this->uri);
            }
            $resourceTypes = $this->getPluginResourceTypes();
            if(!empty($resourceTypes)) {
                foreach($resourceTypes as $resourceType) {

                  if($resourceType == "xhtml") {
                    if (!$perm->isAllowed($this->uri, array('xhtml-back-create'))) {
                  continue;
              }
                  } else if($resourceType == "gallery") {

                    if (!$perm->isAllowed($this->uri, array('gallery-back-gallery'))) {
                  continue;
              }
                  } else if($resourceType == "file" or $resourceType == "archive") {

                    if (!$perm->isAllowed($this->uri, array('file-back-upload')) and !$perm->isAllowed($this->uri, array('gallery-back-upload'))) {
                  continue;
              }
                  }

                    $dom->addLink($i18n->translate2("Create new {resourcetype}", array('resourcetype'=>$resourceType)), 'addresource'. $this->uri.'?type='.$resourceType);
                }
            }

        $dom->addTab("Configuration");

        $this->overviewAddEditConfigXML($dom,".configxml");
        $this->overviewAddEditConfigXML($dom,".configxml.children");


        if ($this->uri != "/") {
            $dom->addTab("Operations");
            if ($perm->isAllowed($this->uri,array('collection-back-copy'))) {
              $dom->addLink("Copy",'javascript:parent.navi.admin.copyResource("'.$this->uri.'");');
            }
            if ($perm->isAllowed($this->uri,array('collection-back-copy', 'collection-back-delete'))) {
              $dom->addLink("Move/Rename",'javascript:parent.navi.admin.copyResource("'.$this->uri.'",true);');
            }
            if ($perm->isAllowed($this->uri,array('collection-back-delete'))) {
              $dom->addLink("Delete",'javascript:parent.navi.admin.deleteResource("'.$this->uri.'",true);');
            }
        }


        return $dom;
    }

    protected function overviewAddEditConfigXML($dom, $file) {

       $perm = bx_permm::getInstance();
    if ($perm->isAllowed($this->uri,array('collection-back-configxml'))) {
          if (file_exists(BX_DATA_DIR.$this->uri.$file)) {
              $dom->addLink("Edit $file", 'edit'.$this->uri.$file.'?editor=oneform');
          } else {
              $dom->addLink( "Add $file", 'edit'.$this->uri.$file.'?editor=oneform');
          }
    }
    }

    public function getJavaScriptSources() {
  return array();
  }
}

?>
