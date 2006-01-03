<?php


class button_aqua extends  button_threeparts {
	

	private $template	= 'aquabutton.png';
	private $background	= '';
	private	$btn_x		= 0;
	private	$btn_y		= 0;

	
	/**
	 * create tmp-image with the backgroudcolor
	 * create tmp-image with the hue rotation from original
	 * combine the two images
	 * resize the image
	 * cut lef-, right-cap and centerpiece
	 */
	public function createButton_aqua(){
		//original size
		$size 	= getimagesize($this->templatedir.$this->template);
		$x 		= $size[0];
		$y 		= $size[1];
		$tpl 	= $this->templatedir.$this->template;
		
		$hue = $this->options['hue'];
		
		//background
		$this->newImage($size[0],$size[1]);
		$img1 = $this->writeTmp();
		
		//hue rotation on original image
		$img2 = tempnam($this->tmpdir, "btn_");
		$cmd = "convert -modulate 100,80,$hue $tpl $img2";
		exec($cmd);
		
		//combine the two pictures
		$img3 = tempnam($this->tmpdir, "btn_");
		$cmd = "composite $img2 $img1 $img3";
		exec($cmd);
		
		//resize to target height
		$cmd = "mogrify -geometry '".$x."x".$this->target_y."' $img3";
		exec($cmd);
				
		//clean up tmp_pictures
		unlink($img1);
		unlink($img2);
		
		$this->newImage();	
		
		$tpl 	= $img3;
		$left 	= $tpl.'left.png';
		$center = $tpl.'center.png';
		$right 	= $tpl.'right.png';
		
		
		$size = getimagesize($tpl);
		//caps are 60% of height
		$caps = round($size[1]*0.6);
		
		//create left cap
		//–crop widthxheight{+-}x{+-}y{%}
		$cmd = "convert -crop '".$caps."x".$this->target_y." +0 +0' $tpl $left";
		exec($cmd);	
		
		//create right cap
		$cmd = "convert -crop '".$caps."x".$this->target_y." +".($size[0]-$caps)." +0' $tpl $right";
		exec($cmd);	

		//create center piece
		$cmd = "convert -crop '".($size[0]-2*$caps)."x".$this->target_y." +".$caps." +0' $tpl $center";
		exec($cmd);
				
		$this->setTemplates($left, $center, $right);
		$this->montageTemplates();

		//clean up tmp_pictures
		unlink($img3);
		unlink($left);
		unlink($center);
		unlink($right);
				
	} 


   


}


?>