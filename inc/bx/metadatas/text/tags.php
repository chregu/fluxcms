<?php

class bx_metadatas_text_tags extends bx_metadatas_text_textfield {

  function getPropertyValueFromPOSTValue($value,$res) {
      
      bx_metaindex::setTags($res->getId(),$value);
      
      
      return $value;
  }
}

?>