<?
class model{
    public $inner = null;
    public $cache = 0;
    public $cachename = null;
    public $classname = null;
    
    function __construct(){
    }

    function __call($func, $args){
        if( $this->classname == null ){
            $this->classname = str_replace('_model','',get_class($this->inner));
            $this->inner->classname = $this->classname;
        }
        
        event(get_class($this->inner).'->'.$func.'('.str_replace(array("\r", "\r\n", "\n"), '', print_r($args,true)).')');
        //$this->classname = str_replace('_model','',get_class($this->inner));
        //$cacheName = APP.'/cache/models/'.$this->classname.'_'.$func.'_'.str_replace(array('{','}',':'),array('','',','),serialize($args));
        if($this->cachename==null){
            $cacheName = APP.'/cache/models/'.$this->classname.'_'.$func.'_'.md5(serialize($args));
        }else{
            $cacheName = APP.'/cache/models/'.$this->cachename;
            $this->cachename=null;
        }

        if(method_exists($this->inner, $func)){
            if($this->cache==0){return call_user_func_array(array($this->inner,$func), $args);}

            $cache_age = @$this->_fileAge($cacheName);

            if(($this->cache>0 && $cache_age>$this->cache*60) || !file_exists($cacheName)){

                $rt = call_user_func_array(array($this->inner,$func), $args);
                if($this->cache>0){

                    $fh = $this->fopen_recursive($cacheName, 'w+') or die("can't open file");
                    fwrite($fh, serialize($rt));
                    fclose($fh);

                }
            }else{
                lib('db');
                $rt = unserialize(file_get_contents($cacheName));
            }
            return $rt;
        }else{
            die("Method <b>{$func}</b> does not exist in Model <b>{$this->classname}</b>");
        }
    }
    
    function _fileAge($fileName){
        $filetime = filemtime($fileName);
        $timenow = time();
        return $timenow - $filetime;
    }
    
    function destroy($name){
        $path = APP.'/cache/models/';
        if(!strpos($name,'*')){
            @unlink($path.$name);
        }else{
            foreach (glob($path.$name) as $filename) {
                @unlink($filename);
            }
        }
    }

    function fopen_recursive($path, $mode, $chmod=0755)
    {
        $directory = dirname($path).'/';
        $file = basename($path);
        if (!is_dir($directory)) {
            if (!mkdir($directory, $chmod, 1)) {
                return false;
            }
        }
        return fopen ($path, $mode);
    }}