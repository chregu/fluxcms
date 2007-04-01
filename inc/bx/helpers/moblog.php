<?php
/*
* Takes a string and extracts the image, rewrites the path to dynimage
* Is used to display the latest moblog
*
*   The xsl-Part would look like this:
*
*    <xsl:for-each 
*        select="php:function('bx_helpers_simplecache::staticHttpReadAsDom',
*        'http://www.sequenz.ch/moblog/rss.xml')/rss/channel/item[position() &lt; 2]">
*      <p id="latest_moblog">
*         <a title="{title}" href="{link}">
*             <img src="{php:functionString('bx_helpers_moblog::getLatestMoblog',content:encoded)}" 
*             border="0" 
*             title="{title}" 
*             alt="latest moblog"/>
*          </a>
*       </p>
*    </xsl:for-each>
*
* Please import the namespace as well:
* xmlns:content="http://purl.org/rss/1.0/modules/content/"
* within the parent-element
*
* (Comment by chregu)
*  The above has it's own blog on /moblog/, if you don't have your own moblog-blog, but
*  only a category called for example "moblog", you can use the url
*  http://yourdomain.com/blog/moblog/rss.xml
*  which has the same effect (it returns all posts only from the moblog category
*  you can do that with any other categeroy as well)
* 
* 
* @param string xmlstring some string which contains an image
* @param int imgwidth
* 
* @return string Modified image path, dynimage with the width set 
* */
 class bx_helpers_moblog{
 		
 	    static function getLatestMoblog($xmlstring, $width = 160){
 	    	/*
 	    	 * Fetches the image-URL
 	    	 * */
			$matches = array();
			
 	    	preg_match("/src=\"(.*?)\"/i", $xmlstring, $matches);
			/* <pre>
			Array
			(
			    [0] => src="http://www.sequenz.ch/files/moblogs/picture00664.jpg"
			    [1] => http://www.sequenz.ch/files/moblogs/picture00664.jpg
			)
			</pre> */
			$imgurl = $matches[1];
			if(!strpos($imgurl, 'dynimages')){
 				$imgurlmod = str_replace("/files/", "/dynimages/$width/files/", $imgurl);
			}else{
				$tmp = explode('/', $imgurl);
				$tmp[5] = $width;
				$imgurlmod = implode('/', $tmp);
			}
 			return $imgurlmod;			
 	}
 }
?>
