<?
class visual_lib{
    function gravatar($email, $size=24){
        ?>http://www.gravatar.com/avatar.php?gravatar_id=<?=md5($email)?>&default=&size=<?=$size?>&d=identicon<?
    }
    
    function date($timestamp){
        return date('d/m/Y H:i',$timestamp);
    }

    function urlheb($str){
        $str = str_replace('"','',$str);
        $str = str_replace(' ','_',$str);
        return $str;
    }

}