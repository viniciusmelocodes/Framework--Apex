<?
class app_lib{
    function isLocal(){
        if($_SERVER['SERVER_ADDR']==$_SERVER['REMOTE_ADDR']){
            return true;
        }else{
            return false;
        }
    }
    
    private $accessed = false;
    private $changed = false;
    private $appVar = array();
    private $path;

    function __construct(){
        $this->path = realpath(".").'/app.xml';
    }
    
    function __get($key){
        if ($this->accessed == false){$this->_firstAccess();}
        if(isset($this->appVar->$key)==true){
            return $this->appVar->$key;
        }else{
            return '';
        }
    }
    
    function __set($key, $value){
        if ($this->accessed == false){$this->_firstAccess();}
        $this->appVar->$key = $value;
        $this->changed = true;
    }

    function _firstAccess(){
        if(file_exists($this->path)==true){
            $this->appVar = simplexml_load_file($this->path);
        }else{
            $this->appVar = simplexml_load_string('<app></app>');
        }
        $this->accessed = true;
    }

    function __destruct(){
        if ($this->changed == true){
            $fp = fopen($this->path, 'w');
            fwrite($fp, $this->appVar->asXML());
            fclose($fp);
        }
    }


}
