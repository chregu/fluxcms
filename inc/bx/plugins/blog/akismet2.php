<?php

include_once(BX_LIBS_DIR.'plugins/blog/akismet.php');
                
class Akismet2 extends Akismet {
    
    public function __construct($blogURL, $wordPressAPIKey)
    {
        $this->blogURL = $blogURL;
        $this->wordPressAPIKey = $wordPressAPIKey;
        
        // Set some default values
        $this->apiPort = 80;
        $this->akismetServer = 'rest.flux-cms.org';
        $this->akismetVersion = '1.1';
        
        // Start to populate the comment data
        $this->comment['blog'] = $blogURL;
        $this->comment['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        $this->comment['referrer'] = $_SERVER['HTTP_REFERER'];
        $this->comment['user_ip'] = $_SERVER['REMOTE_ADDR'] ;
        
    }
    
    
    public function isCommentSpam() {
        $response = $this->http_post($this->getQueryString(), $this->akismetServer, '/' . $this->akismetVersion . '/comment-check');
        return $response[1];
    }
    
}
