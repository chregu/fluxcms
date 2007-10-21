<?php
// 
// add this to blog.xsl:     
// <xsl:template match="tags" mode="xhtml">
// <h3>Tags</h3>
// <xsl:copy-of select="php:functionString('bx_helpers_tags::getTags', @entries, $collectionUri)"/>
// </xsl:template>
// 
// and this to the sidebar editor. 
// Name is tags
// and content is (0 == nothing will be displayed)
// <tags entries="10"/> 
// 
class bx_helpers_tags {
    
    public static function getTags($entries, $path) {
        $tableprefix = $GLOBALS['POOL']->config->getTableprefix();
        $query = "select DISTINCT count(tag) as c, tag from ".$tableprefix."tags left join ".$tableprefix."properties2tags on ".$tableprefix."tags.id = ".$tableprefix."properties2tags.tag_id left join ".$tableprefix."blogposts on ".$tableprefix."properties2tags.path = concat('".$blogpath."',".$tableprefix."blogposts.post_uri,'.html') group by tag order by c Desc LIMIT ".$entries;
        $res = $GLOBALS['POOL']->db->query($query);
	if(MDB2::isError($res)){
            throw new PopoonDBException($res);
	}
        $text = "<ul>";
        while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
                $text .= "<li><a rel='tag' href='".BX_WEBROOT_W.$path."archive/tag/".$row['tag']."/'>".$row['tag']." (".$row['c'].")</a></li>\n";
        }
        
        $text .= "</ul>";
        $xml = new DomDocument();
        $xml->loadXML($text);
        return $xml;
    }
}
