<?php


abstract class bx_streams_buffer {

    protected $html;
    protected $position = 0;
    protected $parameters = array();
    protected $originalPath = null;

    function __construct() {
    }

    function stream_open ($path, $mode, $options, &$opened_path) {
        //for some strange reason, error_reporting level is set to 0 here, set it to the standard level
        error_reporting(bx_errorhandler::$standardLevel);
        print error_reporting();
        $this->originalPath = $path;
        $this->mode = substr($mode,0,1);
        //parse_url can't handle scheme:///
        $path = str_replace(":///","://",$path);
        $url = parse_url($path);
        if (isset($url['query'])) {
            parse_str($url['query'],$this->parameters);
            //stupid magic quotes
            if (get_magic_quotes_gpc()) {
                foreach ($this->parameters as $key => $value) {
                    if (is_string($value)) {
                        $this->parameters[$key] = stripslashes($value);
                    }
                }
            }

            //strip query strings from path
            $path = str_replace("?".$url['query'],"",$path);
        }

        $this->path  = str_replace($url['scheme'].":/","",$path);
        /* "fix" for windows
            not sure anymore, why we needed to distinct between
            windows and the rest
            If it breaks something investigate later

        if (stripos(PHP_OS,"Win") === 0) {
            $this->path  = str_replace($url['scheme']."://","",$path);
        } else {
            $this->path  = str_replace($url['scheme'].":/","",$path);
        }
        */
        if ($this->mode == "r") {
            $this->html = $this->contentOnRead($this->path);
        }
        return true;
    }

    function getParameter($name) {
        if (isset($this->parameters[$name])) {
            return $this->parameters[$name];
        } else {
            return NULL;
        }
    }

    function setParameter($name,$value) {
         $this->parameters[$name] = $value;
    }


    function stream_eof() {
        return ($this->position >= strlen($this->html));
    }

    function stream_stat() {
        return array("size"=>strlen($this->html));
    }

    function stream_read($count) {
        $this->position += $count;

        return substr($this->html,$this->position - $count, $count);
    }

    function stream_write($data) {
        $this->html .= $data;
        $this->position += strlen($data);
        return strlen($data);
    }


    function stream_tell() {
        return $this->position;
    }

    function stream_seek($offset, $whence) {
        switch ($whence) {
            case SEEK_SET:
            if ($offset < strlen($this->html) && $offset >= 0) {
                $this->position = $offset;
                return true;
            } else {
                return false;
            }
            break;

            case SEEK_CUR:
            if ($offset >= 0) {
                $this->position += $offset;
                return true;
            } else {
                return false;
            }
            break;

            case SEEK_END:
            if (strlen($this->html) + $offset >= 0) {
                $this->position = strlen($this->html) + $offset;
                return true;
            } else {
                return false;
            }
            break;

            default:
            return false;
        }
    }


    function stream_close() {
        if ($this->mode == 'w') {
           $content = $this->contentOnWrite($this->html);
            $fp = fopen($this->path,"w");
            fwrite($fp, $content);
            fclose($fp);
        }
        return true;
    }
    function url_stat() {
        return array();
    }

    abstract function contentOnWrite($content) ;
    abstract function contentOnRead($path) ;
}



?>
