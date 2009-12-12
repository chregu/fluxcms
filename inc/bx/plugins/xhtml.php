<?php

class bx_plugins_xhtml extends bx_plugin implements bxIplugin {
    protected $res = array();

    static public $instance = array();
    protected $idMapper = null;


    /*** magic methods and functions ***/

    public static function getInstance($mode) {

        if (!isset(bx_plugins_xhtml::$instance[$mode])) {
            bx_plugins_xhtml::$instance[$mode] = new bx_plugins_xhtml($mode);
        }
        return bx_plugins_xhtml::$instance[$mode];
    }

    public function __construct($mode  = "output") {
         $this->mode = $mode;

    }

    public function getPermissionList() {
    	return array(
    					"xhtml-back-edit_bxe",
    					"xhtml-back-edit_fck",
    					"xhtml-back-edit_kupu",
                        "xhtml-back-edit_assets",
                        "xhtml-back-edit_oneform", 
                        "xhtml-back-create" 
                        );
    }

    /**
    * gets the unique id of a resource associated to a request triple
    *
    * @param string $path the collection uri path
    * @param string $name the filename part
    * @param string $ext the extension
    * @return string id
    */
    public function getIdByRequest($path, $name = NULL, $ext  = NULL) {
        $lang = $GLOBALS['POOL']->config->getOutputLanguage();
        $name = "$name.$lang";
        $perm = bx_permm::getInstance();
        if (!isset($this->idMapper[$path.$name])) {
            $id = str_replace($path,"",bx_resourcemanager::getResourceId($path,$name,$ext));


            //
            // fixing problem when using e.g. gallery-plugin and xhtml-plugin in one .configxml
            //
            // when calling foobar.jpg the xhtml-plugin looks for foobar.<lang>.xhtml and doesn't
            // find this file, in this case use "defaultFilename"-parameter
            //
            // this is just a HACK, someone have to look for a more generic way
            //
            if ($id == "") {

                if (!is_null($this->getParameter($path, "defaultFilename"))) {
                    $name = $this->getParameter($path, "defaultFilename").".".$lang;
                    $ext = "html";

                    $id = str_replace($path,"",bx_resourcemanager::getResourceId($path,$name,$ext));
                } else if (!empty($_GET['admin'])) {
                    if ($perm->isAllowed($path.$id,array('admin'))) {
                        $id = $name.".".$ext;
                    }
                }


            }
            $this->idMapper[$path.$name] = $id;
        }
        if (!$perm->isAllowed($path.$id,array('read'))) {
           throw new BxPageNotAllowedException();
        }
        return $this->idMapper[$path.$name];
    }

    public function getRequestById($path, $id) {
        return ($path."index.html");
    }


   public function getContentById($path, $id) {
        $dom = new domDocument();
        $res = $this->getResourceById($path,$id);
        if (!$res && !empty($_GET['admin'])) {
            $res = $this->getResourceById($path,$id,true);
            $dom->load($res->getContentUriSample());
        } else {
            $dom->load($res->getContentUri());
        }
        $dom->xinclude();
        return $dom;
    }



   public function getChildren($coll, $id) {
      if ($id != ""  && $id != $coll->uri) {
           return array();
       }
       $children = $this->getChildrenByMimeType($coll,"text/html");

       /* $children = array_merge($children,$this->getChildrenByMimeType($coll,"text/wiki"));
        */
        return $children;
    }

    protected function getChildrenByMimeType($coll, $mimetype) {
        $res = array();
        $ch = bx_resourcemanager::getChildrenByMimeType($coll,$mimetype);
        foreach( $ch as $path ) {

            $r = $this->getResourceById($coll->uri,str_replace($coll->uri,"", $path));
            if ($r) {
                $res[] = $r;
            }

        }

        return $res;
    }



    /**
    * gets the resource object associated to an id
    *
    * this is the preferred method of doing things ;)
    *
    * @param string $id the id of the resource
    * @return object resource
    */

    public function getResourceById($path, $id, $mock = false) {
        $perm = bx_permm::getInstance();
        if($id == "thisfiledoesnotexist.xhtml") {
            if (!$perm->isAllowed($path, array('xhtml-back-create'))) {
                throw new BxPageNotAllowedException();
            }
        }

        $id = $path.$id;
        if (!isset($this->res[$id])) {
            $mimetype = bx_resourcemanager::getMimeType($id);
            if ($mimetype == "text/html") {
                $this->res[$id] = new bx_resources_text_html($id);
            } else if ($mimetype == "text/wiki") {
                $this->res[$id] = new bx_resources_text_wiki($id);
            } else if ($mock) {
                $this->res[$id] = new bx_resources_text_html($id,true);
            } else {
                $this->res[$id] =  null;
            }
        }
        return $this->res[$id];
    }


    public function handlePOST($path, $id, $data, $mode = null) {
        if ($mode == "XPathInsert") {

            $file = $this->getContentUriById($path,$id);

            $dom = new DomDocument();
            $dom->load($file);
            $xp = new DomXPath($dom);
            $xp->registerNamespace("xhtml","http://www.w3.org/1999/xhtml");

            foreach($data as $xpath => $value) {
                $res = $xp->query($xpath);
                if ($res && $res->item(0)) {
                    bx_helpers_xml::innerXML($res->item(0),$value);
                }
            }
            $dom->save($file);
        } elseif ($mode == "FullXML") {

           $res = $this->getResourceById($path,$id);
           $ret = 204;
            if ($res->mock) {

                $ret = 201;
                $res->create();
            }

           $file = $this->getContentUriById($path,$id);
           //FIXME: resource should handle the save, not the plugin, actually..


            if (!file_put_contents($file,($data['fullxml']))) {

                print '<span style="color: red;">File '.$file.' could not be written</span><br/>';
                print 'Here is your modified content (it was not saved...):<br/>';
                print '<div style="border: 1px black solid; white-space: pre;">'.(htmlentities(($data['fullxml']))).'</div>';
                return false;
            }


            return $ret;
        }


    }

    public function getAddResourceParams($type,$uri) {

        $dom = new domDocument();

        $fields = $dom->createElement('fields');

        $nameNode = $dom->createElement('field');
        $nameNode->setAttribute('name', 'name');
        $nameNode->setAttribute('required', 'yes');

        $nameNode->setAttribute('type', 'text');

        if(!empty($_REQUEST['name'])) {
            $nameNode->setAttribute('value', $_REQUEST['name']);
        }

        $langNode = $dom->createElement('field');
        $langNode->setAttribute('name', 'lang');
        $langNode->setAttribute('type', 'select');

        $typeNode = $dom->createElement('field');
        $typeNode->setAttribute('name', 'type');
        $typeNode->setAttribute('type', 'hidden');
        $typeNode->setAttribute('value', $type);

        foreach(bx_config::getConfProperty('outputLanguages') as $l =>$lang) {
            $langopt = $dom->createElement('option');
            $langopt->setAttribute('name', $lang);
            $langopt->setAttribute('value', $lang);
        if ($lang == BX_DEFAULT_LANGUAGE) {
        $langopt->setAttribute('selected','selected');
        }
            $langNode->appendChild($langopt);

        }

        $templNode = $dom->createElement('field');
        $templNode->setAttribute("name", 'template');
        $templNode->setAttribute("type", 'select');

        $templateDir = sprintf("%s/%s/templates", BX_THEMES_DIR, bx_config::getConfProperty('theme'));
        foreach($this->getAddResourceTemplates($templateDir) as $t => $template) {
            $templ = $dom->createElement('option');
            $templ->setAttribute("name", $template);
            $templ->setAttribute("value", $template);
            $templNode->appendChild($templ);
        }

        $editorNode = $dom->createElement('field');
        $editorNode->setAttribute('name', 'editor');
        $editorNode->setAttribute('type', 'select');
        switch($type) {
            case 'xhtml':
                $id = 'thisfiledoesnotexist.xhtml';
            break;
        }
        foreach($this->getEditorsById($uri, $id) as $editor) {
            $editorOpt = $dom->createElement('option');
            $editorOpt->setAttribute("name", $editor);
            $editorOpt->setAttribute("value", $editor);
            $editorNode->appendChild($editorOpt);
        }

        $fields->appendChild($langNode);
        $fields->appendChild($templNode);
        if(isset($id)) {
            $fields->appendChild($editorNode);
        }
        $fields->appendChild($nameNode);
        $fields->appendChild($typeNode);
        $dom->appendChild($fields);

        return $dom;
    }


    public function getAddResourceTemplates($templatePath) {

        $templates = array();
        if (is_dir($templatePath)) {

            $td = opendir($templatePath);
            if ($td) {

                while($f = readdir($td)) {

                    if (is_file(sprintf("%s/%s", $templatePath, $f))) {

                        array_push($templates, $f);
                    }
                }
            }
        }
        asort($templates);

        return $templates;

    }


    /**
    * implements addResource
    * Instantiates ressource class and calls its addResource() method
    * FIXME: no dynamic resource lookup yet - just instantiates text/html resource
    *
    * @param    $name       string      ressourcename
    * @param    $parentUri  string      Parent-uri of new resource
    * @param    $options    array       options (lang, template, ...)
    * @return   bool        true|false
    * @access   public
    */
    public function addResource($name, $parentUri, $options=array(), $resourceType = null) {

        $type = (isset($options['type'])) ? $options['type'] : "xhtml";
        $lang = (isset($options['lang'])) ? $options['lang'] : BX_DEFAULT_LANGUAGE;
        $id = sprintf("%s%s.%2s.xhtml", $parentUri, $name, $lang);
        $res = null;

        switch($type) {

            case "xhtml":
                $res = new bx_resources_text_html($id, true);
            break;


        }

        if (is_object($res)) {
            return $res->addResource($name, $parentUri, $options);
        }


        return false;
    }

   /** pipeline methods **/


    public function getMimeTypes() {
        //return array("text/html","text/wiki");
        return array("text/html");
    }

    public function getResourceTypes() {
        return array("xhtml");
    }




    public function isRealResource($path , $id) {
        return true;
    }

    public function adminResourceExists($path, $id, $ext=null, $sample = false) {
        if ($ext == "xhtml") {
        $res = $this->getResourceById($path, $id.".".$ext,$sample);
        if ($res) {
            return $this;
        }
        } else {

            return null;
        }

    }

    public function getLastModifiedById($path, $id) {
        if ($this->getParameter($path, "lastmodified") == "now") {
            return time();
        }
        return parent::getLastModifiedById($path,$id);
    }


}
?>
