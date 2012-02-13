<?php
class cache_lib{
    private $fragment_path; // make sure this is a valid dir
    private $fragment_name;
    private $newly_cached = false;

    function __construct(){
        $this->fragment_path = APP.'/cache/fragment/';
    }

    function destroy($name){
        if(strpos($name,'*')==-1){
            @unlink($this->fragment_path.$name);
        }else{
            foreach (glob($this->fragment_path.$name) as $filename) {
                @unlink($filename);
            }
        }
    }

    function start($lifespan, $name=''){
        if ($this->fragment_name!=''){die('Nested fragment cache not supported.');}
        $x = debug_backtrace();

        if($name==''){
            $this->fragment_name = md5(lib('uri')->_selfURL().'||'.$x[0]['line']);
        }else{
            $this->fragment_name = $name;
        }
        ?><!-- START Fragment <?=$this->fragment_name?>--><?

        // if file does not exist, make preparations to cache and return true, so segment is executed
        if(!file_exists($this->fragment_path . $this->fragment_name)){
            $this->newly_cached = true;
            ob_start();
            return true;
        }else{
            // cache exists, let's see if it is still valid by checking it's age against the $lifespan variable
            $fModify = filemtime($this->fragment_path . $this->fragment_name);
            $fAge = time() - $fModify;
            if ($fAge > ($lifespan * 60)){
                // file is old, let's re-cache
                $this->newly_cached = true;
                ob_start();
                return true;
            }
            // no need to redo
            return false;
        }
    }
    
    function end(){
        if($this->newly_cached==true){
            $new_cache = ob_get_clean();
            
            $fname = $this->fragment_path . $this->fragment_name;
            $fhandle = $this->fopen_recursive($fname,"w+");
            $content = $new_cache;
            fwrite($fhandle,$content);
            fclose($fhandle);
        }
        echo file_get_contents ($this->fragment_path . $this->fragment_name);

        $this->newly_cached = false;
        ?><!-- END Fragment <?=$this->fragment_name?>--><?
        $this->fragment_name = null;
    }

    function fopen_recursive($path, $mode, $chmod=0755)
    {
        $directory = dirname($path);
        $file = basename($path);
        if (!is_dir($directory)) {
            if (!mkdir($directory, $chmod, 1)) {
                return false;
            }
        }
        return fopen ($path, $mode);
    }
}
