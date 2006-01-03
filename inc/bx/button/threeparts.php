<?php



class button_threeparts extends button {
	
	private $img_center	= '';
	private $img_left	= '';
	private $img_right	= '';
	
	/**
	 * 
	 */	
	public function setTemplates($left, $center, $right){		
	    if(!$this->img_left = ImageCreateFromPNG($left)) {
			return false;
	    }
	    if(!$this->img_right = ImageCreateFromPNG($right)) {
			return false;
	    }	    
		if(!$this->img_center = ImageCreateFromPNG($center)) {
			return false;
	    }		
		$this->getInfo();	
		return true;	
	}
	
	/**
	 * 
	 */
	private function getInfo(){
		$this->tpl_height 		= imagesy($this->img_center);	// how tall is the button
		$this->tpl_cap_width 	= imagesx($this->img_left);		// how wide are the left and right rounded caps
		$this->img_center_width	= imagesx($this->img_center);	// how wide is the stretchable center part
	}
	  
    /** 
     * Fill the empty image canvas by tiling the center image
     * and add the left and right cap
     */
	public function montageTemplates(){
	    for ($i = 0; $i < $this->target_x/$this->img_center_width; $i++ ) {
	        ImageCopy($this->out, $this->img_center, $i*$this->img_center_width , 0, 0, 0, $this->img_center_width, $this->tpl_height);
	    }
	    ImageCopy($this->out, $this->img_left, 0, 0, 0, 0, $this->tpl_cap_width, $this->tpl_height);
	    ImageCopy($this->out, $this->img_right, $this->target_x - $this->tpl_cap_width, 0, 0, 0, $this->tpl_cap_width, $this->tpl_height);
	}
   


}


?>