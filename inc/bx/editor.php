<?php

class bx_editor {
    
    public function handlePOST($path, $id, $data) {
		die("here");
        /*$parts = bx_collections::getCollectionAndFileParts($id,"admin");
        var_dump($parts['coll']->resourceExistsById($parts['rawname']));
        */    
        return FALSE;
    }

    public function uploadFile($path, $id, $data) {
       
        $keys = array_keys($data);
        // we can not currently handle more than one file here
        if(!empty($data[$keys[0]])) {
            $file = $data[$keys[0]];
            $parts = bx_collections::getCollectionAndFileParts($id,"admin");
            $res = $parts['coll']->getPluginResourceById($parts['rawname']);
            $res->saveFile($file['tmp_name'], $file);
        }
        return FALSE;
    }

    public function getEditContentById($id) {
        return null;
    }

    protected function getFullPath($path, $name, $ext) {
        $path = str_replace('admin/edit/', '', $path);
        return $path.$name.'.'.$ext;
    }
		
		/**
		 * Returns editor-dependant parameters to pass into pipeline for specified resource.
		 * The same as for bx_plugins.
		 *
		 * @param path collection path
		 * @param id resource id 
		 * @return array of parameters
		 */
		public function getPipelineParametersById($path, $id) {
				return array();
		}
}

?>
