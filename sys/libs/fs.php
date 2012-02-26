<?
class fs_lib{
    function dir($dir){
        $rt = array();
        // from: http://php.net/manual/en/function.readdir.php
        if ($handle = opendir($dir)) {

            /* This is the correct way to loop over the directory. */
            while (false !== ($file = readdir($handle))) {
                if($file!='.' && $file!='..' && !is_dir($dir.'/'.$file)){
                    $rt[] = $file;
                }
            }
            closedir($handle);
        }
        sort($rt);
        return $rt;
    }
}