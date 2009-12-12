<?php
/**
 * This plugin implements creates additional navigation elements.
 *
 * Output contains menu with references to the same uri with additional parameters.
 *
 * Configurated in plugin parametsrs in xml:
 * <parameter type="uriparams" name="foo" key="_title_" value="FOO_TITLE"/>
 * <parameter type="uriparams" name="foo" key="_default_" value="default_title"/>
 * <parameter type="uriparams" name="foo" key="foo_value1" value="value1_title"/>
 * <parameter type="uriparams" name="foo" key="foo_value2" value="value2_title"/>
 *
 * Here key _title_ is title for values menu (a parameter description)
 * Key _default_ is for references w/out parameter.
 *
 * Will create:
 * <ul class="uriparams_menu" xmlns:alef="http://mediasoft.ru/alef">
 *   <li class="uriparams_param" alef:uriparam_name="foo">
 *     <h6>FOO_TITLE</h6>
 *     <ul class="uriparams_items">
 *       <li class="uriparams_item">
 *         <a href="blablabla.html">default_title</a>
 *       </li>
 *       <li class="uriparams_item" alef:uriparam_value="foo_value1">
 *         <a href="blablabla.html?foo_param_name=foo_value1">value1_title</a>
 *       </li>
 *       <li class="uriparams_item" alef:uriparam_value="foo_value2">
 *         <a href="blablabla.html?foo_param_name=foo_value2">value2_title</a>
 *       </li>
 *     </ul>
 *   </li>
 * </ul>
 *
 * Multiple parameters are combined.
 * All stuff in xhtml namespace with prefix 'xhtml'
 *
 */
class bx_plugins_uparamsmenu extends bx_plugin implements bxIplugin {
    
    private static $instance = array();
    
        /*** magic methods and functions ***/
    
    public static function getInstance($mode) {
        
        if (!isset(self::$instance[$mode])) {
            self::$instance[$mode] = new bx_plugins_uparamsmenu($mode);
        } 
        return self::$instance[$mode];
    }
    
    protected function __construct($mode) {
         $this->mode = $mode;
    }
    
    public function getIdByRequest($path, $name = NULL, $ext =NULL) {
				return "$name.$ext";
    }
        
    
     public function getContentById($path, $id) {
        $dom = new domDocument();
				$dom->loadXML('<xhtml:ul xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:alef="http://mediasoft.ru/alef" />');
        $dom->documentElement->setAttribute('class','uriparams_menu');
				
				$params = $this->getParameterAll($path, 'uriparams');
				foreach ($params as $name=>$pkeys) {
					$param_li = $dom->createElementNS("http://www.w3.org/1999/xhtml","xhtml:li");
					$param_li->setAttribute("alef:uriparam_name",$name);
					$param_li->setAttribute('class','uriparams_param');
					if( isset($pkeys['_title_']) ) {
						$param_h = $dom->createElementNS("http://www.w3.org/1999/xhtml","xhtml:h6");
						$param_h->appendChild($dom->createTextNode($pkeys['_title_']));
						$param_li->appendChild($param_h);
						}
					$values_ul = $dom->createElementNS("http://www.w3.org/1999/xhtml","xhtml:ul");
					$values_ul->setAttribute('class','uriparams_items');
					foreach ($pkeys as $key=>$value) {
						if( $key != '_title_' ) {
							$uriparams = $_GET;
							unset($uriparams['path']); // where the hell it came from ?

							$value_li = $dom->createElementNS("http://www.w3.org/1999/xhtml","xhtml:li");
							$value_li->setAttribute("alef:uriparam_value",$key);
							
							$class = 'uriparams_item';
							if( ( isset($uriparams[$name]) and $uriparams[$name] == $key) or
									( !isset($uriparams[$name]) and $key == '_default_' ) ) {
								$class = "uriparams_item-current";
								}
							$value_li->setAttribute('class',$class);
							$href_uri = "$path$id?";	
							if( $key != '_default_' )	$uriparams[$name]=$key;
							else unset($uriparams[$name]);
							foreach($uriparams as $k=>$v) {
								$href_uri .= "&$k=$v";
								}
							$href = $dom->createElementNS("http://www.w3.org/1999/xhtml","xhtml:a");
							$href->setAttribute("href",$href_uri);
							$href->appendChild($dom->createTextNode($value));
							$value_li->appendChild($href);
							$values_ul->appendChild($value_li);
							}
						}
					$param_li->appendChild($values_ul);
          $dom->documentElement->appendChild($param_li);
					}
        return $dom;
    }
    
    public function isRealResource($path , $id) {
        return false;
    }
}
