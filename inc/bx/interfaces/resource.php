<?php
/**
* @package bx_interfaces
*/
interface bxIresource {
    
    /**
    * returns all Properties
    * if $namespace is not set, all properties are reported back
    * if set, only the properties with that namespace
    *
    * Return value is an array with all properties
    * array ( 
    *      array ("name" => foo,
    *             "namespace" => namespace
    *             "value" => value
    *             )
    *         )
    * 
    * @return array
    */
    public function getAllProperties($namespace = NULL);
    
    /**
    * Gets a property  
    *
    * @param string $name name of the property
    * @param string $namespace namespace of the property
    * @return string value of the property
    */
    
    public function getProperty($name, $namespace = BX_PROPERTY_DEFAULT_NAMESPACE);
    
    /**
    * Sets a property  
    *
    * @param string $value value of the property
    * @param string $name name of the property
    * @param string $namespace namespace of the property
    * @return void
    */
    public function setProperty($name, $value, $namespace = BX_PROPERTY_DEFAULT_NAMESPACE);
    
    public function getId();
    
    public function getMimeType();
    
    public function getLastModified();
    
    public function getCreationDate();
    
    public function getDisplayName();
    
    public function getContentLength();
    // Localname is the part of fulluri without the directories is especially used in WebDav...
    public function getLocalName();
 
    
    public function getEditors();
    
    /**
    * saves the file found in $filename, as the resource content.
    * This comes usually from a Fileupload.
    * $_filesArray is optional and has the same format as the array from  $_FILES
    */
    
    public function saveFile($filename, $_filesArray = NULL);

    /*public function saveContent($content);*/

    public function getContentUri();

    public function getContentUriSample();

    public function getResourceName();    

}

?>
