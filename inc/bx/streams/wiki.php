<?php


class bx_streams_wiki extends bx_streams_buffer {
    
    
    function contentOnWrite($content) {
        require_once 'Text/Wiki.php';
        
        $options = array();
        $options['view_url'] = "index.php?page=";
        
        $options['pages'] = array();
        $wiki = new Text_Wiki($options);
        $output = $wiki->transform($content);
        $html = '<html>
        
        <head>
        <meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
        <title>Text_Wiki::' . $page .'</title>
        <link rel="stylesheet" href="stylesheet.css" type="text/css" />
        </head>
        
        <body>'.
        str_replace("&nbsp;","&#160;",$output) 
        .'
        </body></html>
        ';
       return $html;
    }
    
    function contentOnRead($path) {
            include_once("Text/Wiki.php");
            
            $content = file_get_contents($path);
            
            $wiki = new Text_Wiki();
            $wiki->setRuleConf('wikilink', 'view_url', '/');
          
            
            $output = $wiki->transform($content) ;
            $content = '<html  xmlns="http://www.w3.org/1999/xhtml">
            
            <head>
            <meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
            <title>Text_Wiki::'  .'</title>
            <link rel="stylesheet" href="stylesheet.css" type="text/css" />
            </head>
            
            <body>'.
            str_replace("&nbsp;","&#160;",$output) 
            .'
            </body></html>
            ';  
            return $content;
    }
}
?>
